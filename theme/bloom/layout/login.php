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

/**
 * A login page layout for the bloom theme.
 *
 * @package   theme_bloom
 * @copyright 2016 Damyon Wiese
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $CFG;
$bodyattributes = $OUTPUT->body_attributes();
$url = $OUTPUT->get_logo_url();
if ($url) {
    $url = $url->out(false);
}
$admissionsstatus = $CFG->wwwroot. '/local/admissions/status.php';
$admissionsprograms = $CFG->wwwroot. '/local/admissions/programs.php?format=card';
$logourl = $url;
$sitename = format_string($SITE->fullname, true,
        ['context' => context_course::instance(SITEID), "escape" => false]);

$templatecontext = [
    'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
    'output' => $OUTPUT,
    'bodyattributes' => $bodyattributes,
    'logourl'=>$logourl,
    'applicationstatus' => $admissionsstatus,
    'admissionsindex' => $admissionsprograms,
    'sitename'=>$sitename
];

echo $OUTPUT->render_from_template('theme_bloom/login', $templatecontext);

