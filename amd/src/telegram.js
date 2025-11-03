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

import $ from 'jquery';

/**
 * Telegram authentication widget
 *
 * @module     auth_telegram/telegram
 * @copyright  2025 Wail Abualela
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Validates the required parameters.
 * @param {string} botusername - The Telegram bot username
 * @param {string} wwwroot - The Moodle root URL
 * @return {boolean} True if parameters are valid, false otherwise
 */
const validateParameters = (botusername, wwwroot) => {
    if (!botusername || botusername.trim() === '') {
        return false;
    }

    if (!wwwroot || wwwroot.trim() === '') {
        return false;
    }

    return true;
};

/**
 * Creates and adds the Telegram widget script to the document.
 * @param {string} botusername - The Telegram bot username
 * @param {string} wwwroot - The Moodle root URL
 * @return {Object} The created script element
 */
const createTelegramWidgetScript = (botusername, wwwroot) => {
    const script = $("<script>").attr({
        "async": true,
        "src": "https://telegram.org/js/telegram-widget.js?21",// Updated to latest version
        "data-telegram-login": botusername,
        "data-size": "large",
        "data-radius": "5",
        "data-auth-url": wwwroot + "/auth/telegram/index.php",
        "data-request-access": "write"
    });
    return script;
};


/**
 * Creates a container for identity provider buttons styled for the Telegram authentication method.
 * The container is a div element with specific Bootstrap classes and custom styling.
 * @return {jQuery} A jQuery object representing a div element styled as a button
 *                  with appropriate classes and custom background color for Telegram.
 */
const createIdentityProvidersContainer = () => {
    return $('<div>')
        .addClass('btn btn-secondary login-identityprovider-btn rui-potentialidp w-100 mt-1')
        .css({
            'background-color': '#54a9eb'
        });
};

/**
 * Initialize the Telegram authentication widget.
 *
 * @param {string} botusername - The Telegram bot username
 * @param {string} wwwroot - The Moodle root URL
 */
export const init = (botusername, wwwroot) => {
    // Validate required parameters
    if (!validateParameters(botusername, wwwroot)) {
        return;
    }

    // Find the identity providers container
    const identityproviders = $('.login-identityproviders');
    if (identityproviders.length === 0) {
        return;
    }
    identityproviders.append(
        createIdentityProvidersContainer().append(
            createTelegramWidgetScript(botusername, wwwroot)
        )
    );
};