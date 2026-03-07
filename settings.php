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
 * Admin settings for the Telegram authentication plugin.
 *
 * @package    auth_telegram
 * @copyright  2026 Wail Abualela <wailabualela@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    // Bot credentials.
    $settings->add(new admin_setting_heading(
        'auth_telegram/credentials',
        new lang_string('security', 'admin'),
        ''
    ));

    $settings->add(new admin_setting_configtext(
        'auth_telegram/botusername',
        get_string('botusername', 'auth_telegram'),
        get_string('botusername_help', 'auth_telegram'),
        '',
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_configtext(
        'auth_telegram/bottoken',
        get_string('telegrambottoken', 'auth_telegram'),
        get_string('telegrambottoken_help', 'auth_telegram'),
        '',
        PARAM_TEXT
    ));
}
