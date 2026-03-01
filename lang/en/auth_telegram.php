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
 * @copyright  2026 Wail Abualela <wailabualela@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['botusername']             = 'Bot username';
$string['botusername_help']        = 'The Telegram bot username (without @) used to display the Login Widget.';
$string['confirmationinvalid']     = 'The confirmation link is invalid or has expired. Please try again.';
$string['confirmlinkedheader']     = 'Accounts linked';
$string['confirmlinkedmessage']    = 'Your Telegram account has been successfully linked to your Moodle account. You can now log in using Telegram.';
$string['confirmlinkemail']        = 'Hi {$a->firstname},

A request has been made to link a Telegram account to your Moodle account at \'{$a->sitename}\'.

To confirm this request and link the accounts, please click the link below:

{$a->link}

If you did not make this request, please contact the site administrator immediately.

{$a->admin}';
$string['confirmlinkemail_subject'] = '{$a}: Confirm Telegram account link';
$string['confirmlinksent']         = 'Check your email';
$string['continuewith']            = 'Click the button below to sign in with your Telegram account.';
$string['emailexistsheader']       = 'Account already exists';
$string['emailexistsmessage']      = 'An existing account was found with the email address {$a}. A confirmation email has been sent to that address with instructions to link your Telegram account.';
$string['fieldkeycore']            = 'Core: {$a}';
$string['fieldkeycustom']          = 'Custom: {$a->name} ({$a->shortname})';
$string['missingfieldsheader']     = 'Additional profile information required';
$string['missingfieldsmessage']    = 'Please fill in the following required profile fields to continue:';
$string['missingphone']            = 'Please enter your phone number.';
$string['missingtelegramid']       = 'Missing Telegram hash — the login request is invalid.';
$string['notenabled']              = 'Sorry, Telegram authentication plugin is not enabled.';
$string['pluginname']              = 'Telegram';
$string['requiredfields']          = 'Required profile fields';
$string['requiredfields_help']     = 'Select the profile fields that users must fill in after their first Telegram login. Leave empty to skip the missing-fields step entirely.';
$string['signup']                  = 'Complete your registration';
$string['signupdesc']              = 'Your Telegram account has been verified. Please provide your email address and phone number to complete your Moodle account registration.';
$string['telegrambottoken']        = 'Bot token';
$string['telegrambottoken_help']   = 'The secret token provided by BotFather. Used to verify Telegram Login Widget signatures.';
