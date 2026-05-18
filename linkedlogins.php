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
 * Telegram linked login management page.
 *
 * Allows users to view and remove their Telegram account links,
 * mirroring the auth_oauth2/linkedlogins.php pattern.
 *
 * @package    auth_telegram
 * @copyright  2026 Wail Abualela <wailabualela@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

$PAGE->set_url(new moodle_url('/auth/telegram/linkedlogins.php'));
$PAGE->set_context(context_user::instance($USER->id));
$PAGE->set_pagelayout('standard');

$strheading = get_string('linkedlogins', 'auth_telegram');
$PAGE->set_title($strheading);
$PAGE->set_heading($strheading);

require_login();

if (!is_enabled_auth('telegram')) {
    throw new moodle_exception('notenabled', 'auth_telegram');
}

$action = optional_param('action', '', PARAM_ALPHAEXT);

if ($action === 'delete') {
    require_sesskey();
    $linkedloginid = required_param('linkedloginid', PARAM_INT);

    \auth_telegram\api::delete_linked_login($linkedloginid);
    redirect($PAGE->url, get_string('changessaved'), null, \core\output\notification::NOTIFY_SUCCESS);
}

$renderer    = $PAGE->get_renderer('auth_telegram');
$linkedlogins = \auth_telegram\api::get_linked_logins();

// Build the "Link a new Telegram account" URL — sends the user through the
// Telegram Login Widget on index.php. wantsurl brings them back here after
// auth_telegram_link_account() creates the link.
$addurl = new moodle_url('/auth/telegram/index.php', [
    'wantsurl' => (new moodle_url('/auth/telegram/linkedlogins.php'))->out(false),
]);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('linkedlogins', 'auth_telegram'));

echo $renderer->linked_logins_table($linkedlogins);

echo $OUTPUT->single_button($addurl, get_string('addlinkedlogin', 'auth_telegram'), 'get');

echo $OUTPUT->footer();
