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
 * Collect required profile fields that were absent when the Telegram user was created.
 *
 * @package    auth_telegram
 * @copyright  2026 Wail Abualela <wailabualela@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/user/profile/lib.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->libdir . '/formslib.php');

$PAGE->set_url(new moodle_url('/auth/telegram/missingfields.php'));
$PAGE->set_context(context_system::instance());

// Retrieve pending user ID stored by index.php after Telegram verification.
$userid = isset($_SESSION[\auth_telegram\helper::SESSION_PENDING_USERID])
    ? (int) $_SESSION[\auth_telegram\helper::SESSION_PENDING_USERID]
    : 0;

if (!$userid) {
    redirect(new moodle_url('/'));
}

$user = get_complete_user_data('id', $userid);
if (!$user) {
    redirect(new moodle_url('/'));
}

$missing = \auth_telegram\helper::get_missing_fields($user);
if (empty($missing)) {
    // All fields filled — proceed to login.
    unset($_SESSION[\auth_telegram\helper::SESSION_PENDING_USERID]);
    \auth_telegram\telegram::user_login($user);
}

// Load profile field objects for custom fields.
$profilefields = [];
foreach (profile_get_user_fields_with_data((int) $user->id) as $pf) {
    $profilefields[$pf->field->shortname] = $pf;
}

/**
 * Form to collect the missing required profile fields for a Telegram-authenticated user.
 *
 * @package    auth_telegram
 * @copyright  2026 Wail Abualela <wailabualela@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auth_telegram_missingfields_form extends moodleform {
    /**
     * Form definition — builds inputs for each missing field.
     */
    public function definition() {
        $mform         = $this->_form;
        $missing       = $this->_customdata['missing'];
        $profilefields = $this->_customdata['profilefields'];

        foreach ($missing as $fieldkey => $fieldinfo) {
            if ($fieldinfo['type'] === 'custom') {
                $shortname = $fieldinfo['shortname'];
                if (isset($profilefields[$shortname])) {
                    $pf = $profilefields[$shortname];
                    $pf->edit_field_add($mform);
                    $pf->edit_field_set_default($mform);
                    $mform->addRule('profile_field_' . $shortname, null, 'required', null, 'client');
                }
                continue;
            }

            // Core field.
            $fieldname = $fieldinfo['fieldname'];
            $label     = $fieldinfo['label'];

            switch ($fieldname) {
                case 'email':
                    $mform->addElement('text', 'email', $label);
                    $mform->setType('email', PARAM_EMAIL);
                    $mform->addRule('email', null, 'required', null, 'client');
                    break;

                case 'country':
                    $countries = get_string_manager()->get_list_of_countries();
                    $mform->addElement('select', 'country', $label, ['' => ''] + $countries);
                    $mform->addRule('country', null, 'required', null, 'client');
                    break;

                default:
                    $mform->addElement('text', $fieldname, $label);
                    $mform->setType($fieldname, PARAM_NOTAGS);
                    $mform->addRule($fieldname, null, 'required', null, 'client');
                    break;
            }
        }

        $this->add_action_buttons(false, get_string('continue'));
    }
}

$form = new auth_telegram_missingfields_form(null, [
    'missing'       => $missing,
    'profilefields' => $profilefields,
]);

if ($data = $form->get_data()) {
    foreach ($missing as $fieldkey => $fieldinfo) {
        if ($fieldinfo['type'] === 'custom') {
            $elementname        = 'profile_field_' . $fieldinfo['shortname'];
            $user->$elementname = $data->$elementname ?? null;
        } else {
            $fieldname       = $fieldinfo['fieldname'];
            $user->$fieldname = $data->$fieldname ?? '';
        }
    }

    user_update_user($user, false);
    profile_save_data($user);

    unset($_SESSION[\auth_telegram\helper::SESSION_PENDING_USERID]);
    \auth_telegram\telegram::user_login($user);
}

$PAGE->set_heading(get_string('missingfieldsheader', 'auth_telegram'));
$PAGE->set_pagelayout('login');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('missingfieldsheader', 'auth_telegram'));
echo html_writer::tag('p', get_string('missingfieldsmessage', 'auth_telegram'));
$form->display();
echo $OUTPUT->footer();
