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
 * Strings for component 'tool_httpsreplace'
 *
 * @package    tool
 * @subpackage httpsreplace
 * @copyright Copyright (c) 2016 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['allclear'] = 'No domains found that are incompatible with https. If you are ready to switch to using https then you can proceed.';
$string['disclaimer'] = 'I understand the risks of this operation';
$string['doit'] = 'Yes, do it!';
$string['domainexplain'] = 'Potential problem domains';
$string['domainexplainhelp'] = 'These domains are found in your content, but do not appear to support https links. After switching to https, the content from these sites will no longer display in secure modern browsers. It is possible that these sites are temporarily or permanently unavailable and so will not work with either security setting. After reviewing these results you may still wish to proceed if you consider this externally hosted content non-essential.';
$string['excludedtables'] = 'For greater speed, only tables and columns likely to contain links are updated by this tool, so some links may be missed.';
$string['invalidcharacter'] = 'Invalid characters were found in the search or replace text.';
$string['notifyfinished'] = '...finished';
$string['notifyrebuilding'] = 'Rebuilding course cache...';
$string['notimplemented'] = 'Sorry, this feature is not implemented in your database driver.';
$string['notsupported'] = 'Changes made cannot be reverted, thus a complete backup should be made before running this script!';
$string['pageheader'] = 'Search and replace http links in content';
$string['pluginname'] = 'HTTPS Replace';
$string['replacing'] = 'Replacing http links with https...';
