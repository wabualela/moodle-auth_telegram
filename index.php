<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Telegram authentication entry point.
 *
 * Two modes depending on whether Telegram's signed callback parameters are present:
 *
 *  1. No 'hash' param  → render the Telegram Login Widget.
 *       - Guest / not logged in: login flow.
 *       - Already logged in: account-linking flow (widget shown with standard layout).
 *  2. 'hash' param     → verify HMAC signature and route:
 *       - Already logged in: link the Telegram identity to the current account.
 *       - Not logged in, existing link: log the user in.
 *       - Not logged in, no link: redirect to signup.php to collect an email.
 *
 * @package    auth_telegram
 * @copyright  2026 Wail Abualela <wailabualela@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

$PAGE->set_context(context_system::instance());

$hash     = optional_param('hash', '', PARAM_ALPHANUMEXT);
$wantsurl = optional_param('wantsurl', '', PARAM_LOCALURL);

$loggedin = isloggedin() && !isguestuser();

// Mode 1: No hash — render the Telegram Login Widget.
if (empty($hash)) {
    $PAGE->set_url(new moodle_url('/auth/telegram/index.php', ['wantsurl' => $wantsurl]));
    $PAGE->set_title(get_string('pluginname', 'auth_telegram'));
    $PAGE->set_heading($SITE->fullname);
    // Use standard layout for logged-in users (linking), login layout for guests.
    $PAGE->set_pagelayout($loggedin ? 'standard' : 'login');

    // Persist wantsurl to session so it survives the Telegram widget redirect,
    // mirroring the standard Moodle login/index.php pattern.
    if (!empty($wantsurl)) {
        $SESSION->wantsurl = (new moodle_url($wantsurl))->out(false);
    }

    // Build the callback URL that the widget will redirect to (this same page).
    $authurl = new moodle_url('/auth/telegram/index.php', ['wantsurl' => $wantsurl]);

    echo $OUTPUT->header();
    echo $OUTPUT->render_from_template('auth_telegram/script', [
        'botusername' => get_config('auth_telegram', 'botusername'),
        'authurl'     => $authurl->out(false),
    ]);
    echo $OUTPUT->footer();
    exit;
}

// Mode 2: Telegram callback — verify HMAC and route.

// Build userinfo from only the fields Telegram actually sent (empty values would
// break the hash check because Telegram omits optional fields from its computation).
$rawfields = [
    'id'         => optional_param('id', '', PARAM_ALPHANUMEXT),
    'first_name' => optional_param('first_name', '', PARAM_NOTAGS),
    'last_name'  => optional_param('last_name', '', PARAM_NOTAGS),
    'username'   => optional_param('username', '', PARAM_ALPHANUMEXT),
    'photo_url'  => optional_param('photo_url', '', PARAM_URL),
    'auth_date'  => optional_param('auth_date', '', PARAM_ALPHANUMEXT),
];
$userinfo = array_filter($rawfields, fn($v) => $v !== '');
$userinfo['hash'] = $hash;

try {
    $userinfo = auth_telegram_verify($userinfo);
    if ($loggedin) {
        auth_telegram_link_account($userinfo, $wantsurl);
    } else {
        auth_telegram_authenticate($userinfo, $wantsurl);
    }
} catch (Exception $e) {
    throw new moodle_exception($e->getMessage());
}

/**
 * Link a verified Telegram identity to the currently logged-in Moodle account.
 *
 * Called in Mode 2 when the user is already authenticated. Covers three cases:
 *  - Already linked to this user: show info, redirect to linkedlogins.php.
 *  - Linked to a different user: show error, redirect to linkedlogins.php.
 *  - Not linked yet: create the link, redirect to linkedlogins.php with success.
 *
 * @param array  $userinfo Verified Telegram user data.
 * @param string $wantsurl Redirect URL after linking (falls back to linkedlogins.php).
 * @return void
 */
function auth_telegram_link_account(array $userinfo, string $wantsurl): void {
    global $USER;

    $telegramid   = $userinfo['id'];
    $linkeduserid = \auth_telegram\api::get_linked_userid($telegramid);
    $returnurl    = new moodle_url($wantsurl ?: '/auth/telegram/linkedlogins.php');

    if ($linkeduserid === (int) $USER->id) {
        redirect($returnurl, get_string('alreadylinked', 'auth_telegram'), null,
            \core\output\notification::NOTIFY_INFO);
    }

    if ($linkeduserid !== 0) {
        redirect($returnurl, get_string('alreadylinkedother', 'auth_telegram'), null,
            \core\output\notification::NOTIFY_ERROR);
    }

    \auth_telegram\api::link_login((int) $USER->id, $telegramid);
    redirect($returnurl, get_string('changessaved'), null,
        \core\output\notification::NOTIFY_SUCCESS);
}

/**
 * Verify the Telegram Login Widget HMAC signature and data freshness.
 *
 * @param  array $userinfo All parameters from the Telegram redirect (including 'hash').
 * @throws \Exception      When signature is invalid or data is older than 24 hours.
 * @return array           Verified userinfo (hash key removed).
 */
function auth_telegram_verify(array $userinfo): array {
    $checkhash = $userinfo['hash'];
    unset($userinfo['hash']);

    $datacheckarr = [];
    foreach ($userinfo as $key => $value) {
        $datacheckarr[] = $key . '=' . $value;
    }
    sort($datacheckarr);
    $datacheckstring = implode("\n", $datacheckarr);

    $bottoken  = get_config('auth_telegram', 'bottoken');
    $secretkey = hash('sha256', $bottoken, true);
    $hash      = hash_hmac('sha256', $datacheckstring, $secretkey);

    if (!hash_equals($hash, $checkhash)) {
        throw new Exception('Data is NOT from Telegram');
    }

    if ((time() - (int) $userinfo['auth_date']) > 86400) {
        throw new Exception('Data is outdated');
    }

    return $userinfo;
}

/**
 * Route the verified Telegram user to the appropriate next step.
 *
 * For existing linked users: looks up their Moodle account and logs them in.
 * - Suspended accounts are rejected back to the login page.
 * - Deleted accounts fall through to the new-user signup flow so the
 *   Telegram identity can be re-linked to a fresh Moodle account.
 *
 * For new / unlinked users: stores Telegram data in session and redirects
 * to signup.php to collect an email address.
 *
 * @param  array  $userinfo Verified Telegram user data.
 * @param  string $wantsurl Redirect URL after successful login.
 * @return void
 */
function auth_telegram_authenticate(array $userinfo, string $wantsurl): void {
    global $SESSION;

    $telegramid = $userinfo['id'];
    $userid     = \auth_telegram\api::get_linked_userid($telegramid);

    if ($userid) {
        // get_complete_user_data() excludes deleted users (AND deleted <> 1).
        $moodleuser = get_complete_user_data('id', $userid);

        if ($moodleuser && !$moodleuser->suspended) {
            \auth_telegram\telegram::user_login($moodleuser, $wantsurl ?: null);
            // Execution does not continue; user_login() always redirects.
        }

        if ($moodleuser && $moodleuser->suspended) {
            // Suspended — deny access.
            $SESSION->loginerrormsg = get_string('invalidlogin');
            redirect(new moodle_url('/login/index.php'));
        }

        // Linked Moodle account was deleted — fall through to new-user signup flow.
    }

    // New, unlinked, or previously-deleted user — collect email via signup form.
    $_SESSION['auth_telegram_pending_data'] = $userinfo;
    redirect(new moodle_url('/auth/telegram/signup.php', ['wantsurl' => $wantsurl]));
}
