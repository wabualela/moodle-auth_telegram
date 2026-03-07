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
 * Dual-purpose page for the Telegram account-linking email confirmation.
 *
 * Without URL parameters: shows the "check your email" information message.
 * With token + userid + telegramid parameters: validates the token and
 * activates the Telegram → Moodle account link.
 *
 * @package    auth_telegram
 * @copyright  2026 Wail Abualela <wailabualela@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

$PAGE->set_url(new moodle_url('/auth/telegram/confirm-link.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('login');

$token      = optional_param('token', '', PARAM_ALPHANUMEXT);
$userid     = optional_param('userid', 0, PARAM_INT);
$telegramid = optional_param('telegramid', '', PARAM_ALPHANUMEXT);

$isconfirmation = ($token !== '' && $userid > 0 && $telegramid !== '');

if ($isconfirmation) {
    $confirmed = \auth_telegram\api::confirm_link_login($userid, $telegramid, $token);

    $PAGE->set_heading(get_string('confirmlinkedheader', 'auth_telegram'));
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('confirmlinkedheader', 'auth_telegram'));

    if ($confirmed) {
        echo html_writer::tag('p', get_string('confirmlinkedmessage', 'auth_telegram'));
    } else {
        echo html_writer::tag('p', get_string('confirmationinvalid', 'auth_telegram'));
    }
    echo $OUTPUT->single_button(new moodle_url('/login/index.php'), get_string('login'), 'get');
    echo $OUTPUT->footer();
} else {
    $email = isset($_SESSION['auth_telegram_confirm_email'])
        ? $_SESSION['auth_telegram_confirm_email']
        : '';

    unset($_SESSION['auth_telegram_confirm_email']);

    if (empty($email)) {
        redirect(new moodle_url('/'));
    }

    $PAGE->set_heading(get_string('confirmlinksent', 'auth_telegram'));
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('confirmlinksent', 'auth_telegram'));
    echo html_writer::tag('p', get_string(
        'emailexistsmessage',
        'auth_telegram',
        html_writer::tag('strong', s($email))
    ));
    echo $OUTPUT->single_button(new moodle_url('/login/index.php'), get_string('login'), 'get');
    echo $OUTPUT->footer();
}
