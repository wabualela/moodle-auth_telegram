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
 * Telegram authentication callback — validates the Telegram Login Widget response,
 * creates or retrieves the Moodle user, collects any missing required fields, then
 * completes the login.
 *
 * @package    auth_telegram
 * @copyright  2026 Wail Abualela <wailabualela@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/user/lib.php');

$PAGE->set_context(context_system::instance());

$hash = optional_param('hash', '', PARAM_ALPHANUMEXT);
if (empty($hash)) {
    throw new moodle_exception('missingtelegramid', 'auth_telegram');
}

// Build userinfo from only the fields Telegram actually sent.
// Empty values are excluded because Telegram omits optional fields from its hash
// computation — including them would cause a hash mismatch.
$rawfields = [
    'id'         => optional_param('id', '', PARAM_ALPHANUMEXT),
    'first_name' => optional_param('first_name', '', PARAM_NOTAGS),
    'last_name'  => optional_param('last_name', '', PARAM_NOTAGS),
    'username'   => optional_param('username', '', PARAM_ALPHANUMEXT),
    'photo_url'  => optional_param('photo_url', '', PARAM_URL),
    'auth_date'  => optional_param('auth_date', '', PARAM_ALPHANUMEXT),
];
$userinfo = [];
foreach ($rawfields as $key => $value) {
    if ($value !== '') {
        $userinfo[$key] = $value;
    }
}
$userinfo['hash'] = $hash;

try {
    $userinfo = auth_telegram_verify($userinfo);
    auth_telegram_authenticate($userinfo);
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
 * Create or retrieve the Moodle user, then log them in (or redirect to missing-fields).
 *
 * @param  array $userinfo Verified Telegram user data.
 * @return void
 */
function auth_telegram_authenticate(array $userinfo): void {
    $telegramid = $userinfo['id'];

    if (\auth_telegram\telegram::user_exists($telegramid)) {
        $user = \auth_telegram\telegram::get_user($telegramid);
    } else {
        $user = \auth_telegram\telegram::create_user($userinfo);
    }

    $missing = \auth_telegram\helper::get_missing_fields($user);
    if (!empty($missing)) {
        $_SESSION[\auth_telegram\helper::SESSION_PENDING_USERID] = (int) $user->id;
        redirect(new moodle_url('/auth/telegram/missingfields.php'));
    }

    \auth_telegram\telegram::user_login($user);
}
