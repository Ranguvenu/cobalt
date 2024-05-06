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

require_once(dirname(__FILE__) . '/../../config.php');

require_login();
global $DB, $OUTPUT, $PAGE, $USER;
$PAGE->set_url('/local/program/request.php');
$PAGE->set_pagelayout('incourse');

// Print the header.
$strplural = get_string("pluginname", "local_program");
$PAGE->set_title($strplural);

$checkboxval = $_REQUEST['chekboxval'];
$programid = $_REQUEST['prgmid'];
$userid = $_REQUEST['usrid'];

$data = new \stdClass();
$data->completionstatus = $checkboxval;
$data->programid = $programid;
$data->userid = $userid;
$data->timecreated = time();
$data->usercreated = $USER->id;

$programcompletionstatus = $DB->get_record_sql("SELECT * FROM {local_programcompletions} WHERE userid = $userid AND programid = $programid AND completionstatus = $checkboxval");
if ($programcompletionstatus->completionstatus == $checkboxval) {
	$programcompletionstatus->completionstatus = $checkboxval;
	$programcompletionstatus->usermodified = $USER->id;
	$programcompletionstatus->timemodified = time();
	$updatedata = $DB->update_record('local_programcompletions', $programcompletionstatus);
}else{
	$insertdata = $DB->insert_record('local_programcompletions', $data);
	if($insertdata){
		$templateid = $DB->get_field('local_program', 'certificateid', array('id'=> $programid));
		$programname = $DB->get_field('local_program', 'shortname', array('id'=> $programid));
		$certificatedata = new \stdClass();
		$certificatedata->userid = $userid;
		$certificatedata->templateid = $templateid;
		$certificatedata->moduleid = $programid;
		$certificatedata->code = $programname.$userid;
		$certificatedata->moduletype = 'program';
		$certificatedata->timecreated = time();
		$certificatedata->emailed = 1;
		$DB->insert_record('tool_certificate_issues', $certificatedata);
	}

}

