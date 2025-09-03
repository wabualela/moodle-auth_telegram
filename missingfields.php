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

$PAGE->set_url(new moodle_url('/auth/telegram/missingfields.php'));
$PAGE->set_context(context_system::instance());

$user = $_SESSION['auth_telegram_pending_user'] ?? null;
$missing = $_SESSION['auth_telegram_missing_fields'] ?? [];

if (!$user || empty($missing)) {
    redirect('/');
}

if (optional_param('continue', 0, PARAM_BOOL)) {
    \auth_telegram\telegram::user_login($user);
    $_SESSION['logged-in'] = true;
    $_SESSION['telegram_id'] = $user->username;
    unset($_SESSION['auth_telegram_pending_user']);
    unset($_SESSION['auth_telegram_missing_fields']);
    exit;
}

$PAGE->set_heading(get_string('missingfieldsheader', 'auth_telegram'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('missingfieldsheader', 'auth_telegram'));
echo html_writer::tag('p', get_string('missingfieldsmessage', 'auth_telegram'));
echo html_writer::alist($missing);
echo $OUTPUT->single_button(
    new moodle_url('/auth/telegram/missingfields.php', ['continue' => 1]),
    get_string('continue')
);
echo $OUTPUT->footer();
