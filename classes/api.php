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

/**
 * API methods for the Telegram auth plugin.
 *
 * Handles linking Moodle users to Telegram accounts, including the
 * email-based confirmation flow for accounts that already exist in Moodle.
 *
 * @package    auth_telegram
 * @copyright  2026 Wail Abualela <wailabualela@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class api {
    /**
     * Return the Moodle user ID linked to the given Telegram ID, or 0 if none.
     *
     * @param string $telegramid Telegram numeric user ID.
     * @return int Moodle user ID, or 0 if not found.
     */
    public static function get_linked_userid(string $telegramid): int {
        global $DB;

        $record = $DB->get_record_select(
            linked_login::TABLE,
            "telegramid = :telegramid AND confirmtoken = ''",
            ['telegramid' => $telegramid],
            'userid'
        );

        return $record ? (int) $record->userid : 0;
    }

    /**
     * Create a confirmed link between a Moodle user and a Telegram account.
     *
     * @param int    $userid     Moodle user ID.
     * @param string $telegramid Telegram numeric user ID.
     * @return void
     */
    public static function link_login(int $userid, string $telegramid): void {
        if (self::get_linked_userid($telegramid) === $userid) {
            return;
        }

        $record = new \stdClass();
        $record->userid              = $userid;
        $record->telegramid          = $telegramid;
        $record->confirmtoken        = '';
        $record->confirmtokenexpires = 0;

        $login = new linked_login(0, $record);
        $login->create();
    }

    /**
     * Send an email to an existing Moodle user asking them to confirm
     * linking their account to the supplied Telegram identity.
     *
     * Creates a pending linked_login record with a 30-minute confirmation token.
     *
     * @param array     $telegramdata Verified Telegram user data (must include 'id').
     * @param \stdClass $moodleuser   The existing Moodle user whose email we send to.
     * @param string    $wantsurl     Optional redirect URL to preserve after confirmation.
     * @return bool True on success.
     */
    public static function send_confirm_link_login_email(
        array $telegramdata,
        \stdClass $moodleuser,
        string $wantsurl = ''
    ): bool {
        $token   = random_string(32);
        $expires = new \DateTime('NOW');
        $expires->add(new \DateInterval('PT30M'));

        $record = new \stdClass();
        $record->userid              = $moodleuser->id;
        $record->telegramid          = $telegramdata['id'];
        $record->confirmtoken        = $token;
        $record->confirmtokenexpires = $expires->getTimestamp();

        $login = new linked_login(0, $record);
        $login->create();

        $params = [
            'token'      => $token,
            'userid'     => $moodleuser->id,
            'telegramid' => $telegramdata['id'],
        ];
        if (!empty($wantsurl)) {
            $params['wantsurl'] = $wantsurl;
        }
        $confirmurl = new \moodle_url('/auth/telegram/confirm-link.php', $params);

        $site        = get_site();
        $supportuser = \core_user::get_support_user();

        $data            = new \stdClass();
        $data->firstname = $moodleuser->firstname;
        $data->sitename  = format_string($site->fullname);
        $data->admin     = generate_email_signoff();
        $data->link      = $confirmurl->out(false);

        $subject     = get_string('confirmlinkemail_subject', 'auth_telegram', format_string($site->fullname));
        $message     = get_string('confirmlinkemail', 'auth_telegram', $data);
        $messagehtml = text_to_html($message, false, false);

        $moodleuser->mailformat = 1;

        return email_to_user($moodleuser, $supportuser, $subject, $message, $messagehtml);
    }

    /**
     * Validate a pending confirmation token and activate the Telegram link.
     *
     * @param int    $userid     Moodle user ID.
     * @param string $telegramid Telegram numeric user ID.
     * @param string $token      32-character confirmation token from the email link.
     * @return bool True if the link was confirmed, false if invalid or expired.
     */
    public static function confirm_link_login(int $userid, string $telegramid, string $token): bool {
        if (empty($token) || !$userid || empty($telegramid)) {
            return false;
        }

        $login = linked_login::get_record([
            'userid'       => $userid,
            'telegramid'   => $telegramid,
            'confirmtoken' => $token,
        ]);

        if (empty($login)) {
            return false;
        }

        if (time() > $login->get('confirmtokenexpires')) {
            $login->delete();
            return false;
        }

        $login->set('confirmtoken', '');
        $login->set('confirmtokenexpires', 0);
        $login->update();

        return true;
    }
}
