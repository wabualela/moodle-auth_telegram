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

require_once($CFG->libdir . '/authlib.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/user/profile/lib.php');

/**
 * Telegram authentication class.
 *
 * @package    auth_telegram
 * @copyright  2026 Wail Abualela <wailabualela@email.com>
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
     * User login verification.
     *
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function user_login($username, $password) {
        global $DB;
        return $DB->record_exists('user', [
            'username' => $username,
            'auth' => 'telegram',
            'deleted' => 0,
            'confirmed' => 1,
        ]);
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
        $params = ['wantsurl' => $wantsurl, 'sesskey' => sesskey()];
        $url = new \moodle_url('/auth/telegram/test.php', $params);
        $icon = new \moodle_url('/auth/telegram/pix/telegram_icon.png');
        $result[] = ['url' => $url, 'iconurl' => $icon, 'name' => 'telegram'];

        return $result;
    }
}
