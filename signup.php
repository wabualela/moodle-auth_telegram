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
 * Registration page for new Telegram-authenticated users.
 *
 * Shown after HMAC verification when no existing Moodle account is linked
 * to the Telegram ID.  Collects email and phone, then either creates a new
 * account or starts the email-confirmation flow to link an existing one.
 *
 * @package    auth_telegram
 * @copyright  2026 Wail Abualela <wailabualela@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/user/profile/lib.php');
require_once($CFG->libdir . '/formslib.php');

$PAGE->set_url(new moodle_url('/auth/telegram/signup.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('login');

// Require pending Telegram data placed by index.php.
$telegramdata = isset($_SESSION[\auth_telegram\helper::SESSION_PENDING_TELEGRAM_DATA])
    ? $_SESSION[\auth_telegram\helper::SESSION_PENDING_TELEGRAM_DATA]
    : null;

if (empty($telegramdata)) {
    redirect(new moodle_url('/'));
}

/**
 * Signup form for new Telegram users — collects email and phone.
 *
 * @package    auth_telegram
 * @copyright  2026 Wail Abualela <wailabualela@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auth_telegram_signup_form extends moodleform {
    /**
     * Form definition.
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('text', 'email', get_string('email'), ['size' => 40]);
        $mform->setType('email', PARAM_RAW_TRIMMED);
        $mform->addRule('email', get_string('missingemail'), 'required', null, 'client');

        $mform->addElement('text', 'phone1', get_string('phone1'), ['size' => 20]);
        $mform->setType('phone1', PARAM_NOTAGS);
        $mform->addRule('phone1', get_string('missingphone'), 'required', null, 'client');

        $this->add_action_buttons(false, get_string('signup'));
    }

    /**
     * Validate email format.
     *
     * @param array $data  Form field values.
     * @param array $files Uploaded files (unused).
     * @return array Validation errors.
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if (!validate_email($data['email'])) {
            $errors['email'] = get_string('invalidemail');
        }
        return $errors;
    }
}

$form = new auth_telegram_signup_form();

if ($data = $form->get_data()) {
    $email  = core_text::strtolower(trim($data->email));
    $phone  = trim($data->phone1);

    // Check whether an existing Moodle account uses this email.
    $existinguser = \core_user::get_user_by_email($email, '*', null, IGNORE_MULTIPLE);

    if (empty($existinguser)) {
        // No existing account — create a new one, link it, then log in.
        $user = \auth_telegram\telegram::create_user($email, $phone, $telegramdata);
        \auth_telegram\api::link_login($user->id, $telegramdata['id']);

        unset($_SESSION[\auth_telegram\helper::SESSION_PENDING_TELEGRAM_DATA]);

        // Redirect to missingfields if there are other required fields outstanding.
        $missing = \auth_telegram\helper::get_missing_fields($user);
        if (!empty($missing)) {
            $_SESSION[\auth_telegram\helper::SESSION_PENDING_USERID] = (int) $user->id;
            redirect(new moodle_url('/auth/telegram/missingfields.php'));
        }

        \auth_telegram\telegram::user_login($user);
    } else {
        // Email belongs to an existing account — send a confirmation link.
        \auth_telegram\api::send_confirm_link_login_email($telegramdata, $existinguser);

        unset($_SESSION[\auth_telegram\helper::SESSION_PENDING_TELEGRAM_DATA]);

        // Store the email so the info page can display it.
        $_SESSION['auth_telegram_confirm_email'] = $existinguser->email;
        redirect(new moodle_url('/auth/telegram/confirm-link.php'));
    }
}

$PAGE->set_heading(get_string('signup', 'auth_telegram'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('signup', 'auth_telegram'));
echo html_writer::tag('p', get_string('signupdesc', 'auth_telegram'));
$form->display();
echo $OUTPUT->footer();
