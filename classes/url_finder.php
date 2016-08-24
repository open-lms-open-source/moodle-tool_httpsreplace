<?php

namespace tool_httpsreplace;

/**
 * Examines DB for non-https src or data links that will cause trouble
 * when embedded in HTTPS sites.
 *
 * @package tool_httpsreplace
 * @copyright Copyright (c) 2016 Moodlerooms Inc. (http://www.moodlerooms.com)
 */
class url_finder {

    /**
     * Originally forked from core function db_search().
     */
    public function http_link_stats() {
        global $DB, $CFG;

        require_once($CFG->libdir.'/filelib.php');

        $search  = "(src|data)\ *=\ *[\\\"\']http://";

        // TODO: block_instances have HTML content as base64, need to decode then
        // search, currently just skipped.
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
        if (!$tables = $DB->get_tables() ) {    // No tables yet at all.
            return false;
        }

        $urls = array();
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
                        $rs = $DB->get_recordset_select($table, $select, [$search]);

                        foreach ($rs as $record) {
                            // Regex to match src=http://etc. and data=http://etc.urls.
                            // Standard warning on expecting regex to perfectly parse HTML
                            // read http://stackoverflow.com/a/1732454 for more info.
                            $regex = '#(src|data)\ *=\ *[\'\"]http://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))[\'\"]#';
                            preg_match_all($regex, $record->$columnname, $match);
                            foreach ($match[0] as $url) {
                                if (strpos($url, $CFG->wwwroot) === true) {
                                    continue;
                                }
                                $entry["table"] = $table;
                                $entry["columnname"] = $columnname;
                                $entry["url"] = str_replace(array("'", '"'), "", substr($url, ((int) strpos($url, "=") + 1) ));
                                $entry["host"] = parse_url($entry["url"], PHP_URL_HOST);
                                $entry["raw"] = $record->$columnname;
                                $entry["ssl"] = '';
                                $urls[] = $entry;
                            }
                        }
                        $rs->close();
                    }
                }
            }
        }

        $domains = array_map(function ($i) {
            return $i['host'];
        }, $urls);

        $uniquedomains = array_unique($domains);

        $sslfailures = array();
        $knownsupported = array(
            'amazon.com',
            'www.amazon.com',
            'dropbox.com',
            'www.dropbox.com',
        );

        foreach ($uniquedomains as $domain) {
            if (in_array($domain, $knownsupported)) {
                continue;
            }
            $url = "https://$domain/";
            $curl = new \curl();
            $curl->head($url);
            $info = $curl->get_info();
            if (empty($info['http_code']) or ($info['http_code'] >= 400)) {
                $sslfailures[] = $domain;
            }
        }

        $results = array();
        foreach ($urls as $url) {
            $host = $url['host'];
            foreach ($sslfailures as $badhost) {
                if ($host == $badhost) {
                    if (!isset($results[$host])) {
                        $results[$host] = 1;
                    } else {
                        $results[$host]++;
                    }
                }
            }
        }
        return $results;
    }

}
