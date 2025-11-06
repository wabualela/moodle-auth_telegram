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
require_once($CFG->libdir . '/formslib.php');

$PAGE->set_url(new moodle_url('/auth/telegram/missingfields.php'));
$PAGE->set_context(context_system::instance());

$user = $_SESSION['auth_telegram_pending_user'] ?? null;
$missing = $_SESSION['auth_telegram_missing_fields'] ?? [];
$profilefields = $user ? profile_get_user_fields_with_data($user->id) : [];

if (!$user || empty($missing)) {
    redirect('/');
}

class auth_telegram_missing_fields_form extends moodleform {
    public function definition() {
        $mform = $this->_form;
        $missing = $this->_customdata['missing'];
        $profilefields = $this->_customdata['profilefields'];

        foreach ($missing as $key => $label) {
            if (strpos($key, 'profile_') === 0) {
                $shortname = substr($key, 8);
                foreach ($profilefields as $field) {
                    if ($field->field->shortname === $shortname) {
                        $field->edit_field_add($mform);
                        $field->edit_field_set_default($mform);
                        $mform->addRule('profile_field_' . $shortname, null, 'required', null, 'client');
                        break;
                    }
                }
            } else {
                switch ($key) {
                    case 'firstname':
                        $mform->addElement('text', 'firstname', $label);
                        $mform->setType('firstname', PARAM_NOTAGS);
                        $mform->addRule('firstname', null, 'required', null, 'client');
                        break;
                    case 'lastname':
                        $mform->addElement('text', 'lastname', $label);
                        $mform->setType('lastname', PARAM_NOTAGS);
                        $mform->addRule('lastname', null, 'required', null, 'client');
                        break;
                    case 'email':
                        $mform->addElement('text', 'email', $label);
                        $mform->setType('email', PARAM_EMAIL);
                        $mform->addRule('email', null, 'required', null, 'client');
                        break;
                    case 'city':
                        $mform->addElement('text', 'city', $label);
                        $mform->setType('city', PARAM_TEXT);
                        $mform->addRule('city', null, 'required', null, 'client');
                        break;
                    case 'country':
                        $countries = get_string_manager()->get_list_of_countries();
                        $mform->addElement('select', 'country', $label, ['' => ''] + $countries);
                        $mform->addRule('country', null, 'required', null, 'client');
                        break;
                    default:
                        $mform->addElement('text', $key, $label);
                        $mform->setType($key, PARAM_TEXT);
                        $mform->addRule($key, null, 'required', null, 'client');
                }
            }
        }

        $this->add_action_buttons(false, get_string('continue'));
    }
}

$form = new auth_telegram_missing_fields_form(null, [
    'missing' => $missing,
    'profilefields' => $profilefields,
]);

if ($data = $form->get_data()) {
    foreach ($missing as $key => $label) {
        if (strpos($key, 'profile_') === 0) {
            $shortname = substr($key, 8);
            $elementname = 'profile_field_' . $shortname;
            $user->$elementname = $data->$elementname;
        } else {
            $user->$key = $data->$key;
        }
    }
    user_update_user($user, false);
    profile_save_data($user);
    \auth_telegram\telegram::user_login($user);
    $_SESSION['logged-in'] = true;
    $_SESSION['telegram_id'] = $user->username;
    unset($_SESSION['auth_telegram_pending_user']);
    unset($_SESSION['auth_telegram_missing_fields']);
    redirect('/');
}

$PAGE->set_heading(get_string('missingfieldsheader', 'auth_telegram'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('missingfieldsheader', 'auth_telegram'));
echo html_writer::tag('p', get_string('missingfieldsmessage', 'auth_telegram'));
$form->display();
echo $OUTPUT->footer();
