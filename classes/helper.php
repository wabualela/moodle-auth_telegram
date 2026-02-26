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

/**
 * Utility helpers for Telegram auth field-completion logic.
 *
 * @package    auth_telegram
 * @copyright  2026 Wail Abualela <wailabualela@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper {
    /** @var string Session key used to store the pending user ID during missing-fields collection. */
    public const SESSION_PENDING_USERID = 'auth_telegram_pending_userid';

    /** @var array Core user fields available to mark as required. */
    public const CORE_FIELDS = ['firstname', 'lastname', 'email', 'city', 'country', 'phone1'];

    /**
     * Build options array for the admin configmultiselect setting.
     *
     * Returns keys in the format `core:<fieldname>` or `custom:<shortname>`.
     *
     * @return array  key => display label
     */
    public static function get_available_field_options(): array {
        global $CFG;
        require_once($CFG->dirroot . '/user/profile/lib.php');

        $options = [];
        foreach (self::CORE_FIELDS as $fieldname) {
            $options['core:' . $fieldname] = get_string('fieldkeycore', 'auth_telegram', get_string($fieldname));
        }

        $customfields = profile_get_custom_fields(false);
        foreach ($customfields as $customfield) {
            $a = (object) [
                'name'      => format_string($customfield->name),
                'shortname' => $customfield->shortname,
            ];
            $options['custom:' . $customfield->shortname] = get_string('fieldkeycustom', 'auth_telegram', $a);
        }

        return $options;
    }

    /**
     * Return the validated list of configured required field keys.
     *
     * Reads `auth_telegram/requiredfields` from config (stored as comma-separated string
     * by admin_setting_configmultiselect) and returns only valid, recognised keys.
     *
     * @return string[]
     */
    public static function get_configured_fieldkeys(): array {
        $raw  = get_config('auth_telegram', 'requiredfields');
        $keys = preg_split('/\s*,\s*/', (string) $raw, -1, PREG_SPLIT_NO_EMPTY);

        $validated = [];
        foreach ($keys as $key) {
            $key = trim($key);
            if ($key !== '' && self::is_valid_fieldkey($key)) {
                $validated[] = $key;
            }
        }

        return array_unique($validated);
    }

    /**
     * Validate a single field key string.
     *
     * @param  string $key
     * @return bool
     */
    public static function is_valid_fieldkey(string $key): bool {
        if (preg_match('/^core:([a-z0-9_]+)$/', $key, $m)) {
            return in_array($m[1], self::CORE_FIELDS, true);
        }

        if (preg_match('/^custom:([a-z0-9_]+)$/', $key)) {
            return true;
        }

        return false;
    }

    /**
     * Return the missing required fields for a user.
     *
     * Each entry in the returned array has the shape:
     * ```
     * [
     *   'key'       => 'core:email',
     *   'type'      => 'core',          // or 'custom'
     *   'fieldname' => 'email',         // core only
     *   'shortname' => 'myfield',       // custom only
     *   'label'     => 'Email address',
     * ]
     * ```
     *
     * @param  \stdClass $user  Full user record (from get_complete_user_data()).
     * @return array            Keyed by field key; empty if nothing is missing.
     */
    public static function get_missing_fields(\stdClass $user): array {
        global $CFG;
        require_once($CFG->dirroot . '/user/profile/lib.php');

        $missing = [];

        $configured = self::get_configured_fieldkeys();
        if (empty($configured)) {
            return $missing;
        }

        // Pre-load custom profile field data once.
        $profilefields = [];
        foreach ($configured as $fieldkey) {
            if (str_starts_with($fieldkey, 'custom:')) {
                foreach (profile_get_user_fields_with_data((int) $user->id) as $pf) {
                    $profilefields[$pf->field->shortname] = $pf;
                }
                break;
            }
        }

        foreach ($configured as $fieldkey) {
            if (str_starts_with($fieldkey, 'core:')) {
                $fieldname = substr($fieldkey, 5);
                $value     = isset($user->{$fieldname}) ? trim((string) $user->{$fieldname}) : '';
                if ($value === '') {
                    $missing[$fieldkey] = [
                        'key'       => $fieldkey,
                        'type'      => 'core',
                        'fieldname' => $fieldname,
                        'label'     => get_string($fieldname),
                    ];
                }
                continue;
            }

            if (str_starts_with($fieldkey, 'custom:')) {
                $shortname = substr($fieldkey, 7);
                if (!isset($profilefields[$shortname])) {
                    continue;
                }
                $pf = $profilefields[$shortname];
                if ($pf->is_empty()) {
                    $missing[$fieldkey] = [
                        'key'       => $fieldkey,
                        'type'      => 'custom',
                        'shortname' => $shortname,
                        'label'     => format_string($pf->field->name),
                    ];
                }
            }
        }

        return $missing;
    }
}
