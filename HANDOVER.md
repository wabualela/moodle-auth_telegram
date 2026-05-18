# auth_telegram — Plugin Handover

**Package:** `auth_telegram`  
**Version:** 2.2.0 (2026051800)  
**Moodle:** 4.1+  
**Maintainer:** Wail Abualela <wailabualela@email.com>

---

## What the plugin does

Allows users to sign in to Moodle using the **Telegram Login Widget** (HMAC-verified, no OAuth 2.0 server required).

### Login flow

```
User clicks "Login with Telegram"
  └─ index.php (no hash) — shows Telegram widget
       └─ Telegram redirects back with signed params
            └─ index.php (hash present) — verifies HMAC
                 ├─ Existing confirmed link  → complete_user_login() → redirect
                 ├─ Suspended account        → deny, redirect to /login/index.php
                 ├─ Deleted account          → fall through ↓
                 └─ No link / deleted        → signup.php (collect email)
                      ├─ New email           → create user, link, login
                      └─ Existing email      → send confirmation email
                           └─ confirm-link.php (token in URL) → login automatically
```

### Account-linking flow (already logged in)

```
linkedlogins.php → "Link a Telegram account"
  └─ index.php (logged-in user, no hash) — shows widget with standard layout
       └─ Telegram redirects back
            └─ index.php (logged-in + hash) → auth_telegram_link_account()
                 ├─ Already linked to this user  → info notification
                 ├─ Linked to different account  → error notification
                 └─ Not linked yet               → api::link_login() → success
```

---

## File structure

```
auth/telegram/
├── auth.php                        Entry-point shim (extends auth_telegram\auth)
├── index.php                       Login widget + Telegram callback handler
├── signup.php                      Email-collection form for new users
├── confirm-link.php                Token confirmation + auto-login
├── linkedlogins.php                User linked-login management page
├── lib.php                         Navigation hook → adds item to user preferences
├── settings.php                    Admin settings (bot token, bot username)
├── version.php                     Plugin version (2026051800 / 2.2.0)
├── styles.css
├── classes/
│   ├── auth.php                    auth_plugin_base subclass (loginpage_idp_list)
│   ├── api.php                     Static API: link/unlink/confirm/events
│   ├── telegram.php                User creation, login, profile picture
│   ├── linked_login.php            Persistent model for auth_telegram_linked_login
│   └── output/
│       └── renderer.php            HTML renderer for linked_logins_table()
├── amd/
│   └── src/telegram.js             Telegram widget JS
├── db/
│   ├── install.xml                 DB schema (auth_telegram_linked_login table)
│   ├── upgrade.php                 DB upgrade steps
│   ├── events.php                  Observer: user_deleted → api::user_deleted()
│   └── access.php                  Capability: auth/telegram:managelinkedlogins
├── lang/
│   ├── en/auth_telegram.php
│   └── ar/auth_telegram.php
├── templates/
│   └── script.mustache             Telegram Login Widget HTML
└── tests/
    ├── api_test.php                Unit tests for api class (17 cases)
    └── telegram_test.php           Unit tests for telegram class (9 cases)
```

---

## Database

**Table:** `{auth_telegram_linked_login}`

| Column | Type | Notes |
|---|---|---|
| `id` | INT | PK |
| `userid` | INT | FK → `{user}.id` |
| `telegramid` | BIGINT (as VARCHAR) | Telegram numeric user ID — unique index |
| `confirmtoken` | VARCHAR | Empty = confirmed; non-empty = pending |
| `confirmtokenexpires` | INT | Unix timestamp; 0 when confirmed |
| `timecreated` | INT | |
| `timemodified` | INT | |

**Unique index** on `telegramid` (one Telegram account → one Moodle account).

---

## Key design decisions

### HMAC verification (no OAuth server)
The Telegram Login Widget sends user data signed with `HMAC-SHA256(data, SHA256(bot_token))`. We verify this in `auth_telegram_verify()` in `index.php`. Data older than 24 hours is rejected.

### Dual-mode index.php
`index.php` handles both rendering the widget (no `hash` param) and processing the Telegram callback (`hash` param present). This keeps the callback URL simple and avoids a separate endpoint.

### `confirmtoken` design
A `linked_login` row with `confirmtoken = ''` is a live link. A row with a non-empty token is pending email confirmation. `api::get_linked_userid()` explicitly filters `confirmtoken = ''` so pending records never grant access.

### wantsurl propagation
1. `require_login()` stores the user's target URL in `$SESSION->wantsurl` before redirecting to login.
2. `login/index.php` passes this as `?wantsurl=` to the Telegram IDP button.
3. `index.php` Mode 1 persists it back to `$SESSION->wantsurl` to survive the Telegram widget redirect.
4. `telegram::user_login()` reads `$SESSION->wantsurl` as a fallback after `complete_user_login()`, then clears it.

### Deleted users
When a Moodle user is deleted the `user_deleted` event fires and `api::user_deleted()` removes their `linked_login` row. This allows the same Telegram identity to re-link to a new account without hitting the unique-key constraint.

---

## Admin configuration

**Site administration → Plugins → Authentication → Telegram**

| Setting | Key | Description |
|---|---|---|
| Bot token | `auth_telegram/bottoken` | From BotFather; used to verify HMAC |
| Bot username | `auth_telegram/botusername` | Displayed in the Login Widget |

---

## Running tests

Add to `config.php`:
```php
$CFG->phpunit_prefix   = 'phpu_';
$CFG->phpunit_dataroot = '/path/to/moodledata_phpunit';
```

Initialise once:
```bash
php admin/cli/init_phpunit.php
```

Run the plugin suite:
```bash
php vendor/bin/phpunit --filter auth_telegram --testdox
```

Run individual test files:
```bash
php vendor/bin/phpunit public/auth/telegram/tests/api_test.php --testdox
php vendor/bin/phpunit public/auth/telegram/tests/telegram_test.php --testdox
```

---

## Working history (2026-05-18 session)

All issues found during live testing and fixed in this session:

| # | Issue | File(s) | Fix |
|---|---|---|---|
| 1 | `signup.php` hangs on email submit | `classes/telegram.php` | `download_file_content()` called with no timeout (default 300 s). Added `timeout=10, connecttimeout=5`. |
| 2 | Deleted user still authenticates | `index.php` | `core_user::get_user()` returns deleted rows. Replaced with `get_complete_user_data()` which has `AND deleted <> 1`. Deleted users now fall through to new-user signup. Suspended users get an `invalidlogin` redirect. |
| 3 | `Undefined constant AUTH_LOGIN_NOUSER` | `index.php` | Removed event constants not available outside `authlib.php` context. Simplified to `get_string('invalidlogin')` redirect. |
| 4 | `Call to undefined function auth_telegram\clean_username()` | `classes/telegram.php` | `clean_username()` does not exist in Moodle. Replaced with `clean_param($email, PARAM_USERNAME)`. |
| 5 | Duplicate key on re-link after delete | `db/events.php`, `classes/api.php` | `linked_login` row was never cleaned up on user delete. Added `user_deleted` event observer + `api::user_deleted()` to remove rows. |
| 6 | Logged-in users not redirected from login pages | `index.php`, `signup.php`, `confirm-link.php` | Added `isloggedin() && !isguestuser()` guard. `confirm-link.php` info-only branch guarded; confirmation branch auto-logs in instead. |
| 7 | No auto-login after email confirmation | `confirm-link.php` | After valid token: `get_complete_user_data()` + `complete_user_login()` + `apply_concurrent_login_limit()` + redirect. No manual login step needed. |
| 8 | `Undefined property: stdClass::$policyagreed` | `signup.php` | `create_user()` returned the hand-built `stdClass` which lacks DB-default columns. Added `get_complete_user_data('id', $newuser->id)` reload before `user_login()`. |
| 9 | "Link account" redirects to home for logged-in users | `index.php`, `linkedlogins.php` | Global guard blocked the linking flow. Removed it; Mode 1 shows widget with `standard` layout for logged-in users. Mode 2 routes to new `auth_telegram_link_account()` when `$loggedin`. |
| 10 | `wantsurl` not honoured after login | `index.php`, `classes/telegram.php` | Widget page now saves `$wantsurl` to `$SESSION->wantsurl`. `user_login()` reads session fallback after `complete_user_login()` and clears it. |

### New features added

- **Linked login management** (`linkedlogins.php`) — users can view and remove their Telegram links from a dedicated page, identical in UX to `auth/oauth2/linkedlogins.php`.
- **User preferences hook** (`lib.php`) — "Telegram linked logins" appears under *User account* in the preferences sidebar.
- **Capability** `auth/telegram:managelinkedlogins` — mirrors `auth/oauth2:managelinkedlogins`; granted to all users by default.
- **Unit tests** — 26 PHPUnit cases covering `api` and `telegram` classes.
