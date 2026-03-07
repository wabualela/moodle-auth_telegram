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

$string['botusername']              = 'Bot username';
$string['botusername_help']         = 'The Telegram bot username (without @) used to display the Login Widget.';
$string['confirmationinvalid']      = 'The confirmation link is invalid or has expired. Please try again.';
$string['confirmlinkemail']         = 'Hi {$a->firstname},

A request was made to link your Moodle account on {$a->sitename} to a Telegram account.

To confirm the link, click the following address:

{$a->link}

This link is valid for 30 minutes.

{$a->admin}';
$string['confirmlinkemail_subject'] = 'Confirm Telegram account link on {$a}';
$string['confirmlinkedheader']      = 'Telegram account linked';
$string['confirmlinkedmessage']     = 'Your Telegram account has been successfully linked. You can now sign in using Telegram.';
$string['confirmlinksent']          = 'Confirmation email sent';
$string['continuewith']             = 'Click the button below to sign in with your Telegram account.';
$string['emailexistsmessage']       = 'An account with the email address {$a} already exists in Moodle. A confirmation email has been sent to that address. Please click the link in the email to link your Telegram account.';
$string['missingtelegramid']        = 'Missing Telegram hash — the login request is invalid.';
$string['notenabled']               = 'Sorry, Telegram authentication plugin is not enabled.';
$string['pluginname']               = 'Telegram';
$string['signup']                   = 'Complete your registration';
$string['signupdesc']               = 'Your Telegram account has been verified. Please provide your email address to complete your Moodle account registration.';
$string['telegrambottoken']         = 'Bot token';
$string['telegrambottoken_help']    = 'The secret token provided by BotFather. Used to verify Telegram Login Widget signatures.';
