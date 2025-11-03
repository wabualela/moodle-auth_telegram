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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/authlib.php');

/**
 * Telegram authentication plugin.
 * @package    auth_telegram
 * @copyright  2023 Mortada ELgaily <mortada.elgaily@gmail.com>
 * @copyright  2024 Wail Abualela <wailabualela@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auth_plugin_telegram extends auth_plugin_base {
    /**
     * telegram componenet name
     * @var string
     */
    const COMPONENT_NAME = 'auth_telegram';

    /**
     * telegram auth constructor
     */
    public function __construct() {
        $this->authtype = 'telegram';
        $this->config   = get_config(self::COMPONENT_NAME);
    }

    /**
     * Login page Hook overrides
     * @return void
     */
    public function loginpage_hook(): void {
        global $PAGE;

        $PAGE->requires->js_call_amd('auth_telegram/telegram_login', 'init', [
            get_config('auth_telegram', 'bot_username') ?: get_config('auth_telegram', 'botusername'),
            get_config('auth_telegram', 'auth_url') ?: 'https://nl.moddaker.com'
        ]);

    }

    public function user_login($username, $password) {
        global $DB;

        // Check if the user is already logged in.
        if (isloggedin() && !isguestuser()) {
            return true;
        }

        // Check if the user exists in the database.
        if ($user = $DB->get_record('user', [ 'username' => $username ], '*', MUST_EXIST)) {
            // Set the user as logged in.
            complete_user_login($user);
            return true;
        }

        return false;
    }

    /**
     * Return a list of identity providers to display on the login page.
     *
     * @param string|moodle_url $wantsurl The requested URL.
     * @return array List of arrays with keys url, iconurl and name.
     */
    public function loginpage_idp_list($wantsurl) {

        return [
            [
                'id'      => 'telegram',
                'url'     => new moodle_url('/auth/telegram/login.php', [ 'id' => 'telegram', 'wantsurl' => $wantsurl, 'sesskey' => sesskey()]),
                'iconurl' => new moodle_url('/auth/telegram/pix/telegram_icon.png'),
                'name'    => 'telegram',
            ],
        ];
    }

}
