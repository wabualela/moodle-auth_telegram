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

use stdClass;

/**
 * Telegram user management helper.
 *
 * @package    auth_telegram
 * @copyright  2026 Wail Abualela <wailabualela@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class telegram {
    /**
     * Create a new Moodle user from signup form data and Telegram identity.
     *
     * The caller is responsible for calling api::link_login() afterwards to
     * record the Telegram→Moodle mapping in auth_telegram_linked_login.
     *
     * @param string $email       Verified email address from the signup form.
     * @param string $phone       Phone number from the signup form.
     * @param array  $telegramdata Verified Telegram user data (id, first_name, last_name, photo_url).
     * @return stdClass Newly created user record with ->id populated.
     */
    public static function create_user(string $email, string $phone, array $telegramdata): stdClass {
        global $CFG;

        $user                    = new stdClass();
        $user->auth              = 'telegram';
        $user->username          = clean_username($email);
        $user->email             = $email;
        $user->phone1            = $phone;
        $user->firstname         = $telegramdata['first_name'] ?? '';
        $user->lastname          = $telegramdata['last_name'] ?? '';
        $user->confirmed         = 1;
        $user->mnethostid        = $CFG->mnet_localhost_id;
        $user->firstaccess       = time();
        $user->timecreated       = time();
        $user->lastlogin         = time();
        $user->lastaccess        = time();
        $user->currentlogin      = time();
        $user->lastip            = getremoteaddr();
        $user->password          = AUTH_PASSWORD_NOT_CACHED;
        $user->calendartype      = $CFG->calendartype;
        $user->firstnamephonetic = '';
        $user->lastnamephonetic  = '';
        $user->middlename        = '';
        $user->alternatename     = '';
        $user->lang              = $CFG->lang;
        $user->timezone          = $CFG->timezone;

        $user->id = user_create_user($user, false, false);

        profile_save_data($user);

        self::update_picture($user, $telegramdata['photo_url'] ?? '');

        return $user;
    }

    /**
     * Complete the Moodle login session for the given user.
     *
     * @param \stdClass   $user     The user to log in.
     * @param string|null $wantsurl Optional redirect URL after login.
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

    /**
     * Update a user's profile picture from a Telegram photo URL.
     *
     * Returns false early when the user already has a picture, gravatar is
     * enabled, or no photo URL was supplied.
     *
     * @param \stdClass $user     User whose picture should be updated.
     * @param string    $photourl Telegram photo URL (may be empty).
     * @return bool True on success, false otherwise.
     */
    public static function update_picture($user, $photourl): bool {
        global $CFG, $USER;

        if (!empty($user->picture) || !empty($CFG->enablegravatar)) {
            return false;
        }

        if (empty($photourl)) {
            return false;
        }

        // Download image content from URL.
        $imagedata = download_file_content($photourl);
        if (empty($imagedata)) {
            return false;
        }

        // Create temporary storage for the new image.
        $context = \context_user::instance($user->id);
        $fs      = get_file_storage();
        $fs->delete_area_files($context->id, 'user', 'newicon');

        // Store the image data in a temporary file area.
        $filerecord = [
            'contextid' => $context->id,
            'component' => 'user',
            'filearea'  => 'newicon',
            'itemid'    => 0,
            'filepath'  => '/',
            'filename'  => 'image',
        ];

        // Process the image and set it as the user's picture.
        try {
            $fs->create_file_from_string($filerecord, $imagedata);
            // Get the stored file and copy to a temporary path.
            $iconfile = $fs->get_area_files($context->id, 'user', 'newicon', false, 'itemid', false);
            $iconfile = reset($iconfile);
            $iconfile = $iconfile->copy_content_to_temp();

            // Process the image into the proper user icon format.
            $newpicture = (int) process_new_icon($context, 'user', 'icon', 0, $iconfile);

            // Clean up temporary files.
            @unlink($iconfile);
            $fs->delete_area_files($context->id, 'user', 'newicon');

            // Update the user record with the new picture ID.
            $updateuser          = new stdClass();
            $updateuser->id      = $user->id;
            $updateuser->picture = $newpicture;
            $USER->picture       = $newpicture;
            user_update_user($updateuser);
            return true;
        } catch (\file_exception $e) {
            return false;
        }
    }
}
