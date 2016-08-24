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
 * Search and replace http -> https throughout all texts in the whole database
 *
 * @package    tool_httpsreplace
 * @copyright Copyright (c) 2016 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('NO_OUTPUT_BUFFERING', true);

require_once('../../../config.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('toolhttpsreplace');

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('pageheader', 'tool_httpsreplace'));

if (!$DB->replace_all_text_supported()) {
    echo $OUTPUT->notification(get_string('notimplemented', 'tool_httpsreplace'));
    echo $OUTPUT->footer();
    die;
}

echo $OUTPUT->box_start();
echo $OUTPUT->notification(get_string('notsupported', 'tool_httpsreplace'));
echo $OUTPUT->notification(get_string('excludedtables', 'tool_httpsreplace'));
echo $OUTPUT->box_end();


$form = new tool_httpsreplace_form();

if (!$data = $form->get_data()) {
    $finder = new \tool_httpsreplace\url_finder();
    $results = $finder->http_link_stats();

    echo '<h2>'.get_string('domainexplain', 'tool_httpsreplace').'</h2>';
    if (empty($results)) {
        echo get_string('allclear', 'tool_httpsreplace');
    } else {
        echo '<p>'.get_string('domainexplainhelp', 'tool_httpsreplace').'</p>';
        arsort($results);
        foreach ($results as $domain => $count) {
            echo $domain . ' ' . $count . "<br>";
        }
    }
    $form->display();
    echo $OUTPUT->footer();
    die();
}

// Scroll to the end when finished.
$PAGE->requires->js_init_code("window.scrollTo(0, 5000000);");

echo $OUTPUT->box_start();
$replace = new \tool_httpsreplace\url_replace();
$replace->upgrade_http_links();
echo $OUTPUT->box_end();

echo '<p>'.get_string('replacing', 'tool_httpsreplace').'</p>';
echo $OUTPUT->continue_button(new moodle_url('/admin/index.php'));

echo $OUTPUT->footer();
