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
 * Telegram authentication plugin version information
 *
 * @package    auth_telegram
 * @copyright  2021 Brain Station 23 ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2025061700;        // The current plugin version (Date: YYYYMMDDXX).
$plugin->requires  = 2018120304;        // Requires this Moodle version.
$plugin->component = 'auth_telegram';        // Full name of the plugin (used for diagnostics).
$plugin->maturity  = MATURITY_STABLE;
$plugin->release   = '1.0.3';
