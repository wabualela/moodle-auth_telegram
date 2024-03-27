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
 * Handle telegram auth
 * @package    auth_telegram
 * @copyright  2023 Mortada ELgaily <mortada.elgaily@gmail.com>
 * @copyright  2024 Wail Abualela <wailabualela@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once ('../../config.php');
require_once ($CFG->dirroot . '/user/profile/lib.php');
require_once ($CFG->dirroot . '/user/lib.php');

define('BOT_TOKEN', get_config('auth_telegram', 'bottoken'));

if (!isset ($_GET['hash'])) { // The Telegram hash is required to authorize.
    die ('Telegram hash not found');
}

try {
    $data = check_tel_authorization($_GET);
    user_authentication($data);
} catch (Exception $e) {
    throw new moodle_exception($e->getMessage());
}

/**
 * Check telegram auth data
 * @param array $data
 * @throws \Exception
 * @return array
 */
function check_tel_authorization($data): array {
    $checkhash = $data['hash'];
    unset($data['hash']);
    $datacheckarr = [];
    foreach ($data as $key => $value) {
        $datacheckarr[] = $key . '=' . $value;
    }
    sort($datacheckarr);
    $datacheckstring = implode("\n", $datacheckarr);
    $secretkey       = hash('sha256', BOT_TOKEN, true);
    $hash            = hash_hmac('sha256', $datacheckstring, $secretkey);
    if (strcmp($hash, $checkhash) !== 0) {
        throw new Exception('Data is NOT from Telegram');
    }
    if ((time() - $data['auth_date']) > 86400) {
        throw new Exception('Data is outdated');
    }
    return $data;
}

/**
 * Authenticates a user
 * @param array $data
 * @throws \Exception
 * @return void
 */
function user_authentication($data) {
    $user = \auth_telegram\telegram::user_exists($data['id'])
        ? \auth_telegram\telegram::get_user($data['id'])
        : \auth_telegram\telegram::create_user([
            'telegramid' => $data['id'],
            'firstname'  => $data['first_name'],
            'lastname'   => $data['last_name'],
            'email'      => $data['id'] . '@example.com',
            'phone'      => ''
        ]);

    \auth_telegram\telegram::user_login($user);

    // Create logged in user session.
    $_SESSION = [
        'logged-in'   => true,
        'telegram_id' => $data['id'],
    ];
}

