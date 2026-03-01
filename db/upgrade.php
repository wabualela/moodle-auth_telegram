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
 * Upgrade steps for auth_telegram.
 *
 * @package    auth_telegram
 * @copyright  2026 Wail Abualela <wailabualela@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade the auth_telegram plugin.
 *
 * @param int $oldversion The old version of the plugin.
 * @return bool
 */
function xmldb_auth_telegram_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026030100) {
        // Define table auth_telegram_linked_login.
        $table = new xmldb_table('auth_telegram_linked_login');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('telegramid', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, '');
        $table->add_field('confirmtoken', XMLDB_TYPE_CHAR, '64', null, XMLDB_NOTNULL, null, '');
        $table->add_field('confirmtokenexpires', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);

        $table->add_index('telegramid', XMLDB_INDEX_NOTUNIQUE, ['telegramid']);
        $table->add_index('confirmtoken', XMLDB_INDEX_NOTUNIQUE, ['confirmtoken']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Migrate existing telegram_<id> users to the link table.
        $rs = $DB->get_recordset_select(
            'user',
            "username LIKE 'telegram_%' AND auth = 'telegram' AND deleted = 0",
            [],
            '',
            'id, username'
        );
        foreach ($rs as $u) {
            $tid = substr($u->username, strlen('telegram_'));
            if (ctype_digit($tid)) {
                if (
                    !$DB->record_exists(
                        'auth_telegram_linked_login',
                        ['userid' => $u->id, 'telegramid' => $tid]
                    )
                ) {
                    $DB->insert_record('auth_telegram_linked_login', [
                        'userid'              => $u->id,
                        'telegramid'          => $tid,
                        'confirmtoken'        => '',
                        'confirmtokenexpires' => 0,
                        'timecreated'         => time(),
                        'timemodified'        => time(),
                    ]);
                }
            }
        }
        $rs->close();

        upgrade_plugin_savepoint(true, 2026030100, 'auth', 'telegram');
    }

    return true;
}
