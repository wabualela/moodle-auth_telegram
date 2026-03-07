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

namespace auth_telegram;

use core\persistent;

/**
 * Persistent class for the auth_telegram_linked_login table.
 *
 * A record with an empty confirmtoken is a fully confirmed (active) link.
 * A record with a non-empty confirmtoken is pending email confirmation.
 *
 * @package    auth_telegram
 * @copyright  2026 Wail Abualela <wailabualela@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class linked_login extends persistent {
    /** @var string The table name. */
    const TABLE = 'auth_telegram_linked_login';

    /**
     * Define the properties of this model.
     *
     * @return array
     */
    protected static function define_properties() {
        return [
            'userid' => [
                'type' => PARAM_INT,
            ],
            'telegramid' => [
                'type' => PARAM_RAW,
            ],
            'confirmtoken' => [
                'type' => PARAM_RAW,
                'default' => '',
            ],
            'confirmtokenexpires' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
        ];
    }
}
