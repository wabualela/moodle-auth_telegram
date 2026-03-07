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
 * Email-collection form for new Telegram-authenticated users.
 *
 * Shown after HMAC verification when no existing Moodle account is linked to
 * the Telegram ID.  Collects the user's email, then either:
 *  - Creates a new Moodle account and logs the user in directly, or
 *  - Sends a confirmation email to an existing Moodle account asking them
 *    to confirm the Telegram link.
 *
 * @package    auth_telegram
 * @copyright  2026 Wail Abualela <wailabualela@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir . '/formslib.php');

$PAGE->set_url(new moodle_url('/auth/telegram/signup.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('login');

$wantsurl     = optional_param('wantsurl', '', PARAM_LOCALURL);
$telegramdata = $_SESSION['auth_telegram_pending_data'] ?? null;

if (empty($telegramdata)) {
    redirect(new moodle_url('/'));
}

/**
 * Email-collection form for new Telegram users.
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

        $this->add_action_buttons(false, get_string('continue'));
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
    $email      = core_text::strtolower(trim($data->email));
    $telegramid = $telegramdata['id'];

    unset($_SESSION['auth_telegram_pending_data']);

    $moodleuser = \core_user::get_user_by_email($email);

    if ($moodleuser) {
        // Existing Moodle account — send confirmation email and show info page.
        $_SESSION['auth_telegram_confirm_email'] = $email;
        \auth_telegram\api::send_confirm_link_login_email($telegramdata, $moodleuser);
        redirect(new moodle_url('/auth/telegram/confirm-link.php'));
    }

    // New email — create Moodle account, link to Telegram, and log in.
    $newuser = \auth_telegram\telegram::create_user($email, $telegramdata);
    \auth_telegram\api::link_login($newuser->id, $telegramid);
    \auth_telegram\telegram::user_login($newuser, $wantsurl ?: null);
    // Execution does not continue; user_login() always redirects.
}

$PAGE->set_heading(get_string('signup', 'auth_telegram'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('signup', 'auth_telegram'));
echo html_writer::tag('p', get_string('signupdesc', 'auth_telegram'));
$form->display();
echo $OUTPUT->footer();
