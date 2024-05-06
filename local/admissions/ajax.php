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
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.
define('AJAX_SCRIPT', true);
require_once('../../config.php');

global $DB, $PAGE, $USER;
$PAGE->set_url('/local/admissions/ajax.php');
$levelid = optional_param('levelid', '', PARAM_INT);
$programid = optional_param('programid', '', PARAM_INT);

$levelcoursesmandatory = $DB->get_records_sql("SELECT bclc.courseid AS courseid,
                                        bclc.programid, bclc.id AS bclevelcourseid,
                                    bclc.levelid, c.fullname AS course, c.*
                                      FROM {local_program_level_courses} bclc
                                      JOIN {course} c ON c.id = bclc.courseid
                                     WHERE bclc.programid = {$programid}
                                     AND bclc.levelid = {$levelid} AND bclc.mandatory = 1");
$levelcoursesoptional = $DB->get_records_sql("SELECT bclc.courseid AS courseid,
                                        bclc.programid, bclc.id AS bclevelcourseid,
                                    bclc.levelid, c.fullname AS course, c.*
                                      FROM {local_program_level_courses} bclc
                                      JOIN {course} c ON c.id = bclc.courseid
                                     WHERE bclc.programid = {$programid}
                                     AND bclc.levelid = {$levelid} AND bclc.mandatory = 0");

// $semestercredits = $DB->get_field('local_program_levels', 'semister_credits', array('id' => $levelid, 'programid' => $programid));
$noofelectives = $DB->get_field('local_program_levels', 'course_elective', array('id' => $levelid, 'programid' => $programid));
if ($noofelectives) {
    $countelectives = $noofelectives;
} else {
    $countelectives = 0;
}

// if ($semestercredits) {
//     $semcredits = $semestercredits;
// } else {
//     $semcredits = "NA";
// }

$resultcontent = json_encode(['dataresult' => array_values($levelcoursesmandatory),
                            'electives' => array_values($levelcoursesoptional),
                            // 'semestercredits' => $semcredits,
                            'countelectives' => $countelectives
                            ]);
echo $resultcontent;
