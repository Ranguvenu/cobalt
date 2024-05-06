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
 * List the tool provided in a course
 *
 * @package    local
 * @subpackage Timetable
 * @copyright  2023 Dipanshu Kasera
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/timetable/lib.php');
require_once($CFG->dirroot . '/local/timetable/classes/form/session_type.php');
require_once($CFG->dirroot.'/mod/attendance/locallib.php');
require_once($CFG->dirroot.'/local/timetable/lib.php');
require_once($CFG->dirroot.'/lib/datalib.php');

global $CFG, $DB, $USER, $OUTPUT;
$systemcontext =  context_system::instance();
$id = optional_param('id', '', PARAM_INT);
$PAGE->set_pagelayout('standard');
$PAGE->set_context($systemcontext);
require_login();
$PAGE->set_url('/local/timetable/session_type.php');
$PAGE->set_title(get_string('set_session_type', 'local_timetable'));
$PAGE->set_heading(get_string('set_session_type', 'local_timetable'));
$PAGE->navbar->add(get_string('timetablelayout', 'local_timetable'), new moodle_url('/local/timetable/timelayoutview.php'));
$PAGE->navbar->add(get_string('set_session_type', 'local_timetable'), new moodle_url('/local/timetable/timelayout.php'));
$PAGE->requires->js_call_amd('local_timetable/ajaxforms', 'load');
$PAGE->requires->js_call_amd('local_timetable/dataTable', 'load');

$formparams['id'] = $id;

$role = identify_teacher_role($USER->id);
$stdrole = identify_role($USER->id);

$mform = new session_type(array(), $formparams);

if (is_siteadmin()
    || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)
    || has_capability('local/costcenter:manage_ownorganization', $systemcontext)
    ) {


    echo $OUTPUT->header();

    if ($mform->is_cancelled()) {
        redirect($CFG->wwwroot .'/local/timetable/timelayoutview.php');
    } else if ($formdata = $mform->get_data()) {

        if ($formdata->new_organization) {
            $formdata->organization = $formdata->new_organization;
        }

        if ($formdata->id > 0) {
            $updatedata = new \stdClass();
            $updatedata->id = $formdata->id;
            $updatedata->session_type = $formdata->session_type[0];
            $updatedata->timemodified = time();
            $updatedata->id = $DB->update_record('local_session_type', $updatedata);

            if($updatedata->id) {
                redirect($CFG->wwwroot .'/local/timetable/session_type.php');
            }
        } else {
            for($i = 0; ($i < $formdata->option_repeats); $i++){
                $insertdata = new \stdClass();
                $insertdata->session_type = $formdata->session_type[$i];
                $insertdata->organization = $formdata->organization;
                $insertdata->usercreated = $USER->id;
                $insertdata->timecreated = time();
                $insertdata->id = $DB->insert_record('local_session_type', $insertdata);
            }

            if($insertdata->id) {
                redirect($CFG->wwwroot .'/local/timetable/session_type.php');
            }
        }
    }
    echo html_writer::link(new moodle_url('/local/timetable/timelayoutview.php'),''.get_string('back', 'local_timetable').'',array('id'=>'local_timetable_batchwisebu', 'class' => 'btn btn-primary'));

    $mform->display();

    $params = array();
    $sql = "SELECT * ";
    $sql .= "FROM {local_session_type} ";
    $sql .= "WHERE 1=1 ";
    if (is_siteadmin()
        || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)
        ) {
        $sql .= "AND usercreated > 0 ";
    } else if (has_capability('local/costcenter:manage_ownorganization', $systemcontext)) {
        $params['costcenterid'] = $USER->open_costcenterid;
        $sql .= "AND organization = :costcenterid";
    }
    $sessionsql = $DB->get_records_sql($sql, $params);
    $lablestring = get_config('local_costcenter');
    $i = 1;
    $list = array();
    foreach ($sessionsql as $sessionvalue) {
        $data = array();
        $institutename = $DB->get_field('local_costcenter', 'fullname', ['id' => $sessionvalue->organization]);
        $typeexists = $DB->record_exists('attendance_sessions', ['session_type' => $sessionvalue->id]);
        if ($typeexists) {
            $exists = true;
        } else {
            $exists = false;
        }
        if ($sessionvalue->session_type == 'Examination') {
            $cannotedit = true;
        } else {
            $cannotedit = false;
        }
        $data['id'] = $sessionvalue->id;
        $data['session_type'] = $sessionvalue->session_type;
        $data['institutename'] = $institutename;
        $data['session_type_exists'] = $exists;
        $data['cannotedit'] = $cannotedit;
        $data['count'] = $i;
        $list[] = $data;
        $i++;
    }

    $data = [
        'data' => array_values($list),
        'configwwwroot' => $CFG->wwwroot,
        'firstlevel' => $lablestring->firstlevel,
    ];

    if (!empty($sessionsql)) {
        echo $OUTPUT->render_from_template('local_timetable/session_type', $data);
    }

    echo $OUTPUT->footer();
} else {
    throw new Exception("You dont have permission to access");
}
