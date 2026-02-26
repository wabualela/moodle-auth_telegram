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
 * Strings for component 'auth_telegram', language 'en'.
 *
 * @package    auth_telegram
 * @copyright  2023 Mortada ELgaily <mortada.elgaily@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['botusername']           = 'Bot username';
$string['botusername_help']      = 'The Telegram bot username (without @) used to display the Login Widget.';
$string['continuewith']          = 'Click the button below to sign in with your Telegram account.';
$string['fieldkeycore']          = 'Core: {$a}';
$string['fieldkeycustom']        = 'Custom: {$a->name} ({$a->shortname})';
$string['missingfieldsheader']   = 'Additional profile information required';
$string['missingfieldsmessage']  = 'Please fill in the following required profile fields to continue:';
$string['missingtelegramid']     = 'Missing Telegram hash — the login request is invalid.';
$string['notenabled']            = 'Sorry, Telegram authentication plugin is not enabled.';
$string['pluginname']            = 'Telegram';
$string['requiredfields']        = 'Required profile fields';
$string['requiredfields_help']   = 'Select the profile fields that users must fill in after their first Telegram login. Leave empty to skip the missing-fields step entirely.';
$string['telegrambottoken']      = 'Bot token';
$string['telegrambottoken_help'] = 'The secret token provided by BotFather. Used to verify Telegram Login Widget signatures.';
