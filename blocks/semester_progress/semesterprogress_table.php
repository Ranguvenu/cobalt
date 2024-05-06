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
 * Competencies to review page.
 *
 * @package    block_assignments
 * @copyright  Moodle India
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

global $PAGE, $OUTPUT, $DB, $USER, $CFG;
require_login();
$url = new moodle_url('/blocks/semester_progress/semesterprogress_table.php');
$PAGE->requires->jquery();
// $PAGE->requires->js_call_amd('block_semester_progress/table', 'CustomClassDatatable', array());
$PAGE->set_url($url);
$PAGE->set_title(get_string('pluginname', 'block_semester_progress'));
$PAGE->set_heading(get_string('pluginname', 'block_semester_progress'));
$PAGE->navbar->add(get_string('home', 'block_semester_progress'), new moodle_url('/my'));
$PAGE->navbar->add(get_string('pluginname', 'block_semester_progress'));
require_once($CFG->dirroot . '/blocks/semester_progress/lib.php');
require_once($CFG->dirroot . '/blocks/semester_progress/filters_form.php');
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);

    $programenrolusers = "SELECT plc.courseid, pl.programid, pl.id as levelid, pl.level, pl.active, plc.mandatory, plc.parentid, c.fullname, pl.startdate, pl.enddate, plc.parentid 
                        FROM {local_program_users} pu
                        JOIN {local_program_levels} pl ON pl.programid = pu.programid 
                        JOIN {local_program_level_courses} plc ON plc.levelid = pl.id
                        JOIN {course} c ON c.id = plc.courseid 
                        JOIN {user_lastaccess} ul ON ul.courseid = c.id 
                        WHERE pu.userid = :userid AND pl.active = :active 
                        ORDER BY ul.id";

    $course = $DB->get_records_sql($programenrolusers, array('userid'=>$USER->id, 'active'=>1));

    $line = array();
    $params = array();
    foreach($course as $key) {
        $coursedetails = $DB->get_record('course', array('id' => $key->courseid));

        $params['id'] = $key->courseid;
        $totalmodules = "SELECT COUNT(*) FROM {course_modules} cm WHERE cm.course = :id";
        $totalmodulescount = $DB->count_records_sql($totalmodules, $params);
        $completedmodules = "SELECT COUNT(cmc.id) FROM {course_modules_completion} cmc LEFT JOIN {course_modules} cm ON cmc.coursemoduleid = cm.id WHERE cm.course = :id AND cmc.userid = $USER->id";
        $completedmodulescount = $DB->count_records_sql($completedmodules, $params);

        $courseprogress = ($completedmodulescount/$totalmodulescount)*100;
            
        $coursecontext = $DB->get_field('context','id', array('instanceid' => $coursedetails->id, 'contextlevel' =>50));
        $params['id'] = $coursedetails->id;
        $semesterdate = $DB->get_record('local_program_levels', array('id'=>$key->levelid));
        $countoftopics = "SELECT COUNT(id) FROM {course_sections} WHERE course = :id AND section >= 1";
        $nooftopics = $DB->count_records_sql($countoftopics, $params);

        $line['fullname'] = $coursedetails->fullname;

        $line['courseprogress'] = floor($courseprogress);
        $line['nooftopics'] = $nooftopics;
 
        $data[] = $line;
    }

        $usercourses = ['course' => $data, 'configwwwroot'=>$CFG->wwwroot];
        $users = $DB->get_records_sql("SELECT DISTINCT(r.id),r.shortname FROM {role} r
                            JOIN {role_assignments} ra ON ra.roleid = r.id
                            JOIN {user} u ON u.id = ra.userid
                            WHERE u.id = {$USER->id} AND r.shortname = 'student'");
echo $OUTPUT->header();

    if($users) {
        $renderer = $PAGE->get_renderer('block_semester_progress');
        $filterparams = $renderer->semester_content(true);
        $mform = new filters_form(null, array('filterlist' => array('semester'), 'filterparams' => $filterparams));
        if ($mform->is_cancelled()) {
            redirect($CFG->wwwroot . '/blocks/semester_progress/semesterprogress_table.php');
        }

     echo '<a class="btn-link btn-sm d-flex align-items-center filter_btn" href="javascript:void(0);" data-toggle="collapse" data-target="#local_users-filter_collapse" aria-expanded="false" aria-controls="local_users-filter_collapse">
                <span class="filter mr-2">Filters</span>
            <i class="m-0 fa fa-sliders fa-2x" aria-hidden="true"></i>
          </a>';
    echo  '<div class="mt-2 mb-2 collapse '.$show.'" id="local_users-filter_collapse">
                <div id="filters_form" class="card card-body p-2">';
                    $mform->display();
    echo        '</div>
            </div><br>';

        echo $OUTPUT->render_from_template('block_semester_progress/global_filter', $filterparams);
        echo $renderer->semester_content();
    } else {
        throw new moodle_exception(get_string('permissiondenied', 'block_semester_progress'));
    }

echo $OUTPUT->footer();
