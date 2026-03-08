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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/authlib.php');

/**
 * Telegram authentication plugin.
 *
 * Acts as a login entry point: renders the Telegram Login Widget and processes
 * its HMAC-verified callback.  All account creation, linking, and session
 * management is handled by index.php / signup.php using the self-contained
 * auth_telegram classes (api, telegram, linked_login).
 *
 * @package    auth_telegram
 * @copyright  2026 Wail Abualela <wailabualela@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auth extends \auth_plugin_base {
    /**
     * Constructor.
     */
    public function __construct() {
        $this->authtype = 'telegram';
        $this->config   = get_config('auth_telegram');
    }

    /**
     * Telegram users have no password — deny all password-based logins.
     *
     * Authentication is handled via the Telegram Login Widget flow in index.php.
     *
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function user_login($username, $password) {
        return false;
    }

    /**
     * Return a list of identity providers to display on the login page.
     *
     * The rendered link acts as the anchor point that loginpage_hook() replaces
     * with the live Telegram widget when JavaScript is available. It also serves
     * as a functional fallback when JavaScript is disabled.
     *
     * @param string|\moodle_url $wantsurl The requested URL.
     * @return array List of arrays with keys url, iconurl and name.
     */
    public function loginpage_idp_list($wantsurl) {
        if (!$this->is_ready()) {
            return [];
        }
        if (empty($wantsurl)) {
            $wantsurl = '/';
        }
        $params = ['wantsurl' => $wantsurl];
        $url    = new \moodle_url('/auth/telegram/index.php', $params);
        $icon   = new \moodle_url('/auth/telegram/pix/telegram_icon.png');

        return [['url' => $url, 'iconurl' => $icon, 'name' => 'Telegram']];
    }

    /**
     * Inject the Telegram Login Widget directly on the login page.
     *
     * Loads the auth_telegram/login AMD module which finds the IDP button
     * rendered by loginpage_idp_list() and replaces it with the live widget.
     */
    public function loginpage_hook() {
        global $PAGE;

        if (!$this->is_ready()) {
            return;
        }

        $wantsurl    = optional_param('wantsurl', '', PARAM_LOCALURL) ?: '/';
        $authurl     = (new \moodle_url('/auth/telegram/index.php', ['wantsurl' => $wantsurl]))->out(false);
        $botusername = $this->config->botusername;

        $PAGE->requires->js_call_amd('auth_telegram/login', 'init', [$authurl, $botusername]);
    }

    /**
     * Return true when the plugin has both bot credentials configured.
     *
     * @return bool
     */
    private function is_ready(): bool {
        return !empty($this->config->bottoken) && !empty($this->config->botusername);
    }
}
