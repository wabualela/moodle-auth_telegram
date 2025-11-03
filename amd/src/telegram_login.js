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
 * Telegram Login Widget Handler
 *
 * @module     auth_telegram/telegram_login
 * @copyright  2025 Wail Abualela
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import ModalFactory from 'core/modal_factory';
import Templates from 'core/templates';

export const init = async () => {
    $('a.login-identityprovider-btn:has(img[src*="telegram"])').on('click', function (e) {
        e.preventDefault();
        modal.show();
        window.console.log('Telegram login clicked');
    });

    const modal = await ModalFactory.create({
        title: 'Telegram Account',
        body: Templates.render('auth_telegram/script', {}),
        footer: 'An example footer content',
    });
};
