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

namespace auth_telegram;

defined('MOODLE_INTERNAL') || die();

use moodle_url;

require_once($CFG->libdir . '/authlib.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/user/profile/lib.php');

/**
 * Class auth
 *
 * @package    auth_telegram
 * @copyright  2025 Wail Abualela
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auth extends \auth_plugin_base
{

    /**
     * Constructor.
     */
    public function __construct() {
        $this->authtype = 'telegram';
        $this->config   = get_config('auth_telegram');
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
        if ($user = $DB->get_record('user', ['username' => $username], '*', MUST_EXIST)) {
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


        $result = [];
        if (empty($wantsurl)) {
            $wantsurl = '/';
        }
        $params   = ['wantsurl' => $wantsurl, 'sesskey' => sesskey()];
        $url      = new moodle_url('/auth/telegram/test.php', $params);
        $icon     = new moodle_url('/auth/telegram/pix/telegram_icon.png');
        $result[] = ['url' => $url, 'iconurl' => $icon, 'name' => 'telegram'];

        return $result;
    }
}
