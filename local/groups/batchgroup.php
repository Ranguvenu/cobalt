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
 * Version information
 *
 * @package    local_groups
 * @copyright  2022 eAbyas Info Solutions Pvt. Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once("../../config.php");
require_once("lib.php");
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot . '/local/groups/classes/form/batchgroup.php');
global $PAGE, $USER, $DB;
require_login();
$systemcontext = context_system::instance();
$batchid = optional_param('batchid', '', PARAM_INT);
$id = optional_param('id', '', PARAM_INT);
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_title(get_string('manage_groups', 'local_groups'));
$PAGE->set_heading(get_string('manage_groups', 'local_groups'));
$PAGE->navbar->add(get_string('cohorts', 'local_groups'), new moodle_url('/local/groups/index.php'));
$PAGE->navbar->add(get_string('manage_groups', 'local_groups'));
$PAGE->requires->js_call_amd('local_groups/subgroupDT', 'load');
$PAGE->requires->js_call_amd('local_groups/newgroup', 'load', array());
$baseurl = $CFG->wwwroot. '/local/groups/batchgroup.php?batchid='.$batchid;
$PAGE->set_url($baseurl);

$formparams['batchid'] = $batchid;
$formparams['id'] = $id;

echo $OUTPUT->header();

$mform = new batchgroup(null, $formparams);

if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot .'/local/groups/index.php');
} else if ($formdata = $mform->get_data()) {

    if ($formdata->id > 0) {
        $group = $DB->get_field('local_sub_groups', 'groupid', ['id' => $formdata->id]);
        $updategroup = new \stdClass();
        $updategroup->id = $group;
        $updategroup->name = $formdata->name;
        $updategroup->idnumber = $formdata->idnumber;
        $updategroup->timemodified = time();

        $updatedata->id = $DB->update_record('cohort', $updategroup);

        if($updatedata->id) {
            redirect($CFG->wwwroot .'/local/groups/batchgroup.php?batchid='.$formdata->batchid);
        }

    } else {
        $insertgroup = new \stdClass();
        $insertgroup->contextid = $formdata->contextid;
        $insertgroup->name = $formdata->name;
        $insertgroup->idnumber = $formdata->idnumber;
        $insertgroup->description = '';
        $insertgroup->descriptionformat = 1;
        $insertgroup->visible = $formdata->visible;
        $insertgroup->timecreated = time();
        $insertgroup->timemodified = '';

        $data->id = $DB->insert_record('cohort', $insertgroup);

        $insertdata = new \stdClass();
        $insertdata->groupid = $data->id;
        $insertdata->parentid = $formdata->batchid;
        $insertdata->usercreated = $USER->id;
        $insertdata->usermodified = '';
        $insertdata->timecreated = time();
        $insertdata->timemodified = '';

        $insert->id = $DB->insert_record('local_sub_groups', $insertdata);

        if($insert->id) {
            redirect($CFG->wwwroot .'/local/groups/batchgroup.php?batchid='.$formdata->batchid);
        }
    }    
}

$p_data = $DB->get_record('local_groups', ['cohortid' => $batchid]);

if ($batchid > 0) {
    if (is_siteadmin()
      || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)
      || (has_capability('local/costcenter:manage_ownorganization', $systemcontext) && $p_data->costcenterid == $USER->open_costcenterid)
      || (has_capability('local/costcenter:manage_owndepartments', $systemcontext) && ($p_data->costcenterid == $USER->open_costcenterid && $p_data->departmentid == $USER->open_departmentid))
      || (has_capability('local/costcenter:manage_ownsubdepartments', $systemcontext) && ($p_data->costcenterid == $USER->open_costcenterid && $p_data->departmentid == $USER->open_departmentid && $p_data->subdepartmentid == $USER->open_subdepartment))) {
        echo html_writer::link(new moodle_url('/local/groups/index.php'),''.get_string('back', 'local_timetable').'',array('id'=>'local_timetable_batchwisebu', 'class' => 'btn btn-primary'));

        $mform->display();

        $groupsql = $DB->get_records('local_sub_groups', ['parentid' => $batchid]);

        $i = 1;
        $list = array();
        foreach ($groupsql as $groupvalue) {
            $data = array();
            $groupdata = $DB->get_record('cohort', ['id' => $groupvalue->groupid]);
            $usersexists = $DB->record_exists('cohort_members', ['cohortid' => $groupvalue->groupid]);
            $groupexists = $DB->record_exists('attendance_sessions', ['batch_group' => $groupvalue->groupid]);
            if (!empty($groupexists) || !empty($usersexists)) {
                $exists = 'disabled';
            } else {
                $exists = 'enabled';
            }
            $data['id'] = $groupvalue->id;
            $data['name'] = $groupdata->name;
            $data['idnumber'] = $groupdata->idnumber;
            $data['batchid'] = $groupvalue->parentid;
            $data['groupid'] = $groupvalue->groupid;
            $data['actionstatusmsg'] = get_string('delconfirm', 'local_groups', $groupdata->name);
            $data['users_and_group'] = $exists;
            $data['count'] = $i;
            $list[] = $data;
            $i++;
        }

        $data = [
            'data' => array_values($list),
            'configwwwroot' => $CFG->wwwroot,
            'actionstatus' => get_string('confirm')
        ];

        if (!empty($groupsql)) {
            echo $OUTPUT->render_from_template('local_groups/sub_group', $data);
        }
        // $mform->display();
    } else {
        throw new Exception('You dont have permission to access this page');
    }
}

echo $OUTPUT->footer();
