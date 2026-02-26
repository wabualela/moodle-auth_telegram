# Moodle Telegram Authentication Plugin

Authenticate Moodle users with the official [Telegram Login Widget](https://core.telegram.org/widgets/login). Users click **Continue with Telegram** on the Moodle login page, authorise via Telegram, and land directly on their dashboard — no password required.

---

## What's new in v1.2

- **Clean page-based login flow** — replaced the old modal/hook approach with a dedicated widget page (`test.php`) that fits naturally into Moodle's login layout.
- **Configurable required fields** — admins choose which profile fields (core + custom) users must fill in after their first Telegram login.
- **Security improvements** — timing-safe HMAC comparison (`hash_equals`), session stores only the user ID (not the full user object), proper `AUTH_PASSWORD_NOT_CACHED` password constant.
- **Bug fixes** — HMAC verification no longer fails when Telegram omits optional fields (`last_name`, `photo_url`, `username`); `update_picture()` now correctly returns `bool` on error.
- **Moodle coding standard** — zero errors reported by `local/codechecker`.

---

## Login flow

```
Moodle login page
  └─ "Continue with Telegram" button (IDP list)
       └─ test.php  (Telegram Login Widget)
            └─ index.php  (HMAC verification + user create/retrieve)
                 ├─ missingfields.php  (if required fields are empty)
                 │    └─ Dashboard
                 └─ Dashboard
```

---

## Requirements

- Moodle 4.2 or later
- PHP 7.4 or later
- A Telegram bot created via [@BotFather](https://t.me/BotFather)

---

## Installation

1. Copy the `telegram` folder into `<moodleroot>/auth/`.
2. Log in as administrator and go to **Site Administration → Notifications** to run the upgrade.
3. Navigate to **Site Administration → Plugins → Authentication → Manage authentication** and enable **Telegram**.

---

## Configuration

Go to **Site Administration → Plugins → Authentication → Telegram → Settings**.

| Setting | Description |
|---------|-------------|
| **Bot username** | Your bot's username without the `@` (e.g. `MyLoginBot`). Displayed on the widget page. |
| **Bot token** | The secret token from BotFather. Used to verify Login Widget signatures server-side. |
| **Required profile fields** | Profile fields users must complete after their first Telegram login. Leave empty to skip the missing-fields step entirely. |

### Telegram bot setup

1. Open Telegram and start a chat with [@BotFather](https://t.me/BotFather).
2. Send `/newbot` and follow the prompts. Copy the **token**.
3. Send `/setdomain` to BotFather, choose your bot, and enter your Moodle site's domain (e.g. `moodle.example.com`). This authorises the Login Widget for your domain.
4. Paste the token and username into the plugin settings.

---

## How it works

1. The IDP button on the Moodle login page links to `test.php`, which renders the Telegram Login Widget.
2. After the user authorises, Telegram redirects to `index.php` with signed query parameters.
3. `index.php` verifies the HMAC signature using `SHA256(bottoken)` as the secret key and checks the data is less than 24 hours old.
4. A Moodle user is created (username `telegram_<id>`) or retrieved if they have logged in before.
5. If any configured required fields are empty, the user is redirected to `missingfields.php` to fill them in.
6. `complete_user_login()` is called and the user is redirected to the dashboard.

---

## Files

| File | Purpose |
|------|---------|
| `auth.php` | Root wrapper — registers the `auth_plugin_telegram` class. |
| `classes/auth.php` | Auth plugin class; provides `user_login()` and `loginpage_idp_list()`. |
| `classes/telegram.php` | Static helpers: `create_user()`, `user_exists()`, `get_user()`, `user_login()`, `update_picture()`. |
| `classes/helper.php` | Field key constants, `get_available_field_options()`, `get_configured_fieldkeys()`, `get_missing_fields()`. |
| `test.php` | Renders the Telegram Login Widget page. |
| `index.php` | HMAC callback — verifies signature, creates/retrieves user, redirects. |
| `missingfields.php` | Collects missing required profile fields after first login. |
| `settings.php` | Admin settings (bot username, bot token, required fields multiselect). |
| `templates/script.mustache` | Mustache template for the widget page. |

---

## License

GNU GPL v3 or later — see [COPYING](http://www.gnu.org/licenses/gpl-3.0.html).
