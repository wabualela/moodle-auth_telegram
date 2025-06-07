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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/authlib.php');

/**
 * Telegram authentication plugin.
 * @package    auth_telegram
 * @copyright  2023 Mortada ELgaily <mortada.elgaily@gmail.com>
 * @copyright  2024 Wail Abualela <wailabualela@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auth_plugin_telegram extends auth_plugin_base
{
    /**
     * telegram componenet name
     * @var string
     */
    const COMPONENT_NAME = 'auth_telegram';

    /**
     * telegram auth constructor
     */
    public function __construct()
    {
        $this->authtype = 'telegram';
        $this->config   = get_config(self::COMPONENT_NAME);
    }

    /**
     * Login page Hook overrides
     * @return void
     */
    public function loginpage_hook(): void
    {
        global $PAGE, $CFG;
        echo "<script type='text/javascript'>
                var botusername = " . json_encode(get_config('auth_telegram', 'botusername')) . ";
            </script>";
        $PAGE->requires->jquery();
        $PAGE->requires->js(new moodle_url("$CFG->wwwroot/auth/telegram/script.js"));

    }

    public function loginpage_idp_list($wantsurl) {
        $result = [];
        if (empty($wantsurl)) {
            $wantsurl = '/';
        }
        $params   = [ 'id' => 1, 'wantsurl' => $wantsurl, 'sesskey' => sesskey()];
        $url      = new moodle_url('/auth/telegram/login.php', $params);
        $icon     = new moodle_url('/auth/telegram/pix/telegram.png');
        $result[] = [ 'url' => $url, 'iconurl' => $icon, 'name' => get_string('pluginname', 'auth_telegram') ];

        return $result;
    }

}
