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

namespace tool_httpsreplace;

/**
 * Examines DB for non-https src or data links that will cause trouble
 * when embedded in HTTPS sites and changes them to https links.
 *
 * @package tool_httpsreplace
 */
class url_replace {

    /**
     * Find http urls used in src and data attributes and switch them to https.
     *
     * Originally forked from core function db_replace().
     *
     */
    public function upgrade_http_links() {
        global $DB, $CFG, $OUTPUT;

        $sqlregex  = "(src|data)\ *=\ *[\\\"\']http://";

        $skiptables = array(
            'block_instances',
            'config',
            'config_log',
            'config_plugins',
            'events_queue',
            'files',
            'filter_config',
            'grade_grades_history',
            'grade_items_history',
            'log',
            'logstore_standard_log',
            'repository_instance_config',
            'sessions',
            'upgrade_log',
            'grade_categories_history',
            '',
        );

        // Turn off time limits.
        \core_php_time_limit::raise();

        if (!$tables = $DB->get_tables() ) { // No tables yet at all.
            return false;
        }

        $texttypes = array (
            'text',
            'mediumtext',
            'longtext',
            'varchar',
        );

        foreach ($tables as $table) {

            if (in_array($table, $skiptables)) {
                continue;
            }

            if ($columns = $DB->get_columns($table)) {
                $regexp = $DB->sql_regex();
                foreach ($columns as $column) {

                    if (in_array($column->type, $texttypes)) {
                        $columnname = $column->name;
                        $select = "$columnname $regexp ?";
                        $rs = $DB->get_recordset_select($table, $select, [$sqlregex]);

                        $found = array();
                        foreach ($rs as $record) {
                            // Regex to match src=http://etc. and data=http://etc.urls.
                            // Standard warning on expecting regex to perfectly parse HTML
                            // read http://stackoverflow.com/a/1732454 for more info.
                            $regex = '#(src|data)\ *=\ *[\'\"]http://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))[\'\"]#';
                            preg_match_all($regex, $record->$columnname, $match);
                            foreach ($match[0] as $src) {
                                if (strpos($src, $CFG->wwwroot) === true) {
                                    continue;
                                }
                                $url = substr($src, strpos($src, 'http'), -1);
                                $host = parse_url($url, PHP_URL_HOST);
                                $found[] = $host;
                            }
                        }
                        $rs->close();

                        $found = array_unique($found);
                        foreach ($found as $domain) {
                            $search = "http://$domain";
                            $replace = "https://$domain";
                            $DB->set_debug(true);
                            // Note, this search is case sensitive.
                            $DB->replace_all_text($table, $column, $search, $replace);
                            $DB->set_debug(false);
                        }
                    }
                }
            }
        }

        // Delete modinfo caches.
        rebuild_course_cache(0, true);

        purge_all_caches();

        return true;
    }

}
