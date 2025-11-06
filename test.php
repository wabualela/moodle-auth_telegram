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
 * TODO describe file test
 *
 * @package    auth_telegram
 * @copyright  2025 Wail Abualela <wailabualela@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

$PAGE->set_url(new moodle_url('/auth/telegram/test.php', []));
$PAGE->set_context(context_system::instance());
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('login');

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('auth_telegram/script', [
  'botusername' => get_config('auth_telegram', 'bot_username') ?: get_config('auth_telegram', 'botusername'),
  'authurl'     => new moodle_url('/auth/telegram/index.php') ?: (get_config('auth_telegram', 'auth_url') ?: 'https://nl.moddaker.com')
]);
echo $OUTPUT->footer();
