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
 * Inline Telegram Login Widget on the Moodle login page.
 *
 * Finds the "Continue with Telegram" IDP link rendered by loginpage_idp_list()
 * and replaces it with the official Telegram Login Widget, shortening the flow
 * from two pages to one.
 *
 * Falls back gracefully to the standard link when JavaScript is unavailable.
 *
 * @module     auth_telegram/login
 * @copyright  2026 Wail Abualela <wailabualela@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([], function() {
    return {
        /**
         * Replace the IDP button with the live Telegram Login Widget.
         *
         * @param {string} authurl     Callback URL (index.php with wantsurl) for Telegram.
         * @param {string} botusername Telegram bot username (without @).
         */
        init: function(authurl, botusername) {
            document.addEventListener('DOMContentLoaded', function() {
                // Find the IDP link Moodle rendered for auth_telegram.
                var link = document.querySelector('a[href*="/auth/telegram/index.php"]');
                if (!link) {
                    return;
                }

                // Build a centred wrapper and the Telegram widget <script> tag.
                var container = document.createElement('div');
                container.className = 'd-flex justify-content-center';

                var script = document.createElement('script');
                script.async = true;
                script.src = 'https://telegram.org/js/telegram-widget.js?22';
                script.setAttribute('data-telegram-login', botusername);
                script.setAttribute('data-size', 'large');
                script.setAttribute('data-userpic', 'true');
                script.setAttribute('data-auth-url', authurl);
                container.appendChild(script);

                // Replace the IDP button's wrapper element with the widget.
                var parent = link.closest('li') ||
                             link.closest('.potentialidp') ||
                             link.parentElement;
                parent.replaceWith(container);
            });
        }
    };
});
