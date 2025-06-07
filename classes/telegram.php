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

use stdClass;

/**
 * Class telegram
 *
 * @package    auth_telegram
 * @copyright  2024 Wail Abualela <wailabualela@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class telegram {
    /**
     * create a new user
     * @param array $data
     * @return stdClass
     */
    public static function create_user($data): stdClass {
        global $CFG, $DB;

        $user               = new stdClass();
        $user->auth         = "telegram";
        $user->username     = $data['telegramid'];
        $user->firstname    = $data['firstname'];
        $user->lastname     = $data['lastname'];
        $user->confirmed    = 1;
        $user->mnethostid   = 1;
        $user->firstaccess  = time();
        $user->timecreated  = time();
        $user->lastlogin    = time();
        $user->lastaccess   = time();
        $user->currentlogin = time();
        $user->lastip       = getremoteaddr();
        $user->password     = '';
        $user->email        = $data['email'];
        $user->phone1       = $data['phone'];
        $user->calendartype = $CFG->calendartype;
        $user->id           = user_create_user($user, false, false);
        profile_save_data($user);

        return $user;
    }

    /**
     * Check if a user exists for the provided Telegram identifier.
     *
     * @param string $telegramid Telegram user ID used as the Moodle username.
     * @return bool True if the user exists, false otherwise.
     */
    public static function user_exists($telegramid): bool {
        global $DB;
        return $DB->record_exists(
            'user',
            array(
                'username'  => $telegramid,
                'deleted'   => false,
                'confirmed' => true,
            ),
        );
    }


    /**
     * Retrieve a user by Telegram identifier used as username.
     *
     * @param string $telegramid Telegram user ID used as the Moodle username.
     * @return stdClass User record matching the Telegram ID.
     */
    public static function get_user($telegramid): stdClass {
        global $DB;
        return $DB->get_record(
            'user',
            array(
                'username'  => $telegramid,
                'deleted'   => false,
                'confirmed' => true,
            ),
        );
    }

    /**
     * authenticate the user
     * @param \stdClass $user
     * @param string $wantsurl
     * @throws \Exception
     * @return void
     */
    public static function user_login($user, $wantsurl = null) {
        complete_user_login($user);
        \core\session\manager::apply_concurrent_login_limit($user->id, session_id());

        if ($wantsurl) {
            redirect($wantsurl);
        } else {
            redirect('/');
        }
    }
}
