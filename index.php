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

require_once('../../config.php');
require_once($CFG->dirroot . '/user/profile/lib.php');
require_once($CFG->dirroot . '/user/lib.php');

/**
 * Handle telegram auth
 * @package    auth_telegram
 * @copyright  2023 Mortada ELgaily <mortada.elgaily@gmail.com>
 * @copyright  2024 Wail Abualela <wailabualela@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('BOT_TOKEN', get_config('auth_telegram', 'bottoken'));

$PAGE->set_context(context_system::instance());

if (!isset($_GET['hash'])) { // The Telegram hash is required to authorize.
    die('Telegram hash not found');
}

try {
    $userinfo = check_tel_authorization($_GET);
    user_authentication($userinfo);
} catch (Exception $e) {
    throw new moodle_exception($e->getMessage());
}

/**
 * Check telegram auth data
 * @param array $userinfo
 * @throws \Exception
 * @return array
 */
function check_tel_authorization($userinfo): array {
    $checkhash = $userinfo['hash'];
    unset($userinfo['hash']);
    $datacheckarr = [];
    foreach ($userinfo as $key => $value) {
        $datacheckarr[] = $key . '=' . $value;
    }
    sort($datacheckarr);
    $datacheckstring = implode("\n", $datacheckarr);
    $secretkey = hash('sha256', BOT_TOKEN, true);
    $hash = hash_hmac('sha256', $datacheckstring, $secretkey);
    if (strcmp($hash, $checkhash) !== 0) {
        throw new Exception('Data is NOT from Telegram');
    }
    if ((time() - $userinfo['auth_date']) > 86400) {
        throw new Exception('Data is outdated');
    }
    return $userinfo;
}

/**
 * Authenticates a user
 * @param array $userinfo
 * @throws \Exception
 * @return void
 */
function user_authentication($userinfo) {
    $user = \auth_telegram\telegram::user_exists($userinfo['username'])
        ? \auth_telegram\telegram::get_user($userinfo['username'])
        : \auth_telegram\telegram::create_user($userinfo);

    $missing = auth_telegram_get_missing_fields($user);
    if (!empty($missing)) {
        $_SESSION['auth_telegram_pending_user'] = $user;
        $_SESSION['auth_telegram_missing_fields'] = $missing;
        redirect(new moodle_url('/auth/telegram/missingfields.php'));
    }

    \auth_telegram\telegram::user_login($user);

    // Mark the session as logged in via Telegram without overwriting existing data.
    $_SESSION['logged-in'] = true;
    $_SESSION['telegram_id'] = $userinfo['id'];
}

/**
 * Check required and custom profile fields for missing values.
 *
 * @param \stdClass $user
 * @return array Associative array of missing field identifiers mapped to labels.
*/
function auth_telegram_get_missing_fields($user): array {
    $missingfields = [];

    $requiredfields = ['firstname', 'lastname', 'email', 'city', 'country'];
    foreach ($requiredfields as $field) {
        if (empty($user->$field)) {
            $missingfields[$field] = get_string($field);
        }
    }

    $profilefields = profile_get_user_fields_with_data($user->id);
    foreach ($profilefields as $field) {
        if ($field->is_required() && empty($field->data)) {
            $missingfields['profile_' . $field->field->shortname] = format_string($field->field->name);
        }
    }

    return $missingfields;
}


