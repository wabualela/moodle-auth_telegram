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
 *  1. No 'hash' param  → render the Telegram Login Widget (was test.php).
 *  2. 'hash' param     → verify HMAC signature, resolve or start account flow:
 *       - Existing linked user: look up their Moodle account and log them in.
 *       - New / unlinked user: store Telegram data in session and redirect to
 *         signup.php to collect an email address.
 *
 * @package    auth_telegram
 * @copyright  2026 Wail Abualela <wailabualela@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

$PAGE->set_context(context_system::instance());

$hash     = optional_param('hash', '', PARAM_ALPHANUMEXT);
$wantsurl = optional_param('wantsurl', '', PARAM_LOCALURL);

// ── Mode 1: No hash — render the Telegram Login Widget ──────────────────────
if (empty($hash)) {
    $PAGE->set_url(new moodle_url('/auth/telegram/index.php', ['wantsurl' => $wantsurl]));
    $PAGE->set_heading($SITE->fullname);
    $PAGE->set_pagelayout('login');

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

// ── Mode 2: Telegram callback — verify HMAC and route ───────────────────────

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
    auth_telegram_authenticate($userinfo, $wantsurl);
} catch (Exception $e) {
    throw new moodle_exception($e->getMessage());
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
 *
 * For new / unlinked users: stores Telegram data in session and redirects
 * to signup.php to collect an email address.
 *
 * @param  array  $userinfo Verified Telegram user data.
 * @param  string $wantsurl Redirect URL after successful login.
 * @return void
 */
function auth_telegram_authenticate(array $userinfo, string $wantsurl): void {
    $telegramid = $userinfo['id'];
    $userid     = \auth_telegram\api::get_linked_userid($telegramid);

    if ($userid) {
        $moodleuser = \core_user::get_user($userid);
        if ($moodleuser) {
            \auth_telegram\telegram::user_login($moodleuser, $wantsurl ?: null);
            // user_login() redirects; execution does not continue.
        }
    }

    // New or unlinked user — collect email via signup form.
    $_SESSION['auth_telegram_pending_data'] = $userinfo;
    redirect(new moodle_url('/auth/telegram/signup.php', ['wantsurl' => $wantsurl]));
}
