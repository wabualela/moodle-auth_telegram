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

namespace auth_telegram\output;

use html_table;
use html_table_cell;
use html_table_row;
use html_writer;
use auth_telegram\linked_login;
use moodle_url;
use plugin_renderer_base;

/**
 * Output renderer for auth_telegram.
 *
 * @package    auth_telegram
 * @copyright  2026 Wail Abualela <wailabualela@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {

    /**
     * Render the linked logins management table.
     *
     * @param linked_login[] $linkedlogins
     * @return string HTML
     */
    public function linked_logins_table(array $linkedlogins): string {
        if (empty($linkedlogins)) {
            return $this->notification(get_string('nolinkedlogins', 'auth_telegram'), 'info');
        }

        $table                      = new html_table();
        $table->head                = [
            get_string('telegramid', 'auth_telegram'),
            get_string('linkedsince', 'auth_telegram'),
            get_string('edit'),
        ];
        $table->attributes['class'] = 'admintable table generaltable table-hover';
        $table->data                = [];

        foreach ($linkedlogins as $linkedlogin) {
            $idcell = new html_table_cell(s($linkedlogin->get('telegramid')));

            $linkeddate = userdate($linkedlogin->get('timecreated'));
            $datecell   = new html_table_cell($linkeddate);

            $deleteparams = [
                'action'         => 'delete',
                'linkedloginid'  => $linkedlogin->get('id'),
                'sesskey'        => sesskey(),
            ];
            $deleteurl  = new \moodle_url('/auth/telegram/linkedlogins.php', $deleteparams);
            $deletelink = html_writer::link(
                $deleteurl,
                $this->pix_icon('t/delete', get_string('delete')),
                ['title' => get_string('delete')]
            );
            $editcell = new html_table_cell($deletelink);

            $table->data[] = new html_table_row([$idcell, $datecell, $editcell]);
        }

        return html_writer::table($table);
    }
}
