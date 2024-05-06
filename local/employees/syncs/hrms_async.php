<?php
/**
 * This file is part of eAbyas
 *
 * Copyright eAbyas Info Solutons Pvt Ltd, India
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author eabyas  <info@eabyas.in>
 * @package 
 * @subpackage local_employees
 */

require('../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/csvlib.class.php');
$iid = optional_param('iid', '', PARAM_INT);
$previewrows = optional_param('previewrows', 10, PARAM_INT);
@set_time_limit(60 * 60); // 1 hour should be enough
raise_memory_limit(MEMORY_HUGE);

require_login();
$errorstr = get_string('error');
$stryes = get_string('yes');
$strno = get_string('no');
$stryesnooptions = array(0 => $strno, 1 => $stryes);
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_pagelayout('standard');
global $USER, $DB , $OUTPUT;

$returnurl = new moodle_url('/local/employees/index.php');
if (!has_capability('local/employees:manage',$systemcontext) || !has_capability('local/employees:create', $systemcontext) ) {
	print_error('You dont have permission');
}

$PAGE->set_url('/local/employees/syncs/hrms_async.php');
$strheading = get_string('pluginname', 'local_employees') . ' : ' . get_string('uploadusers', 'local_employees');
$PAGE->set_title($strheading);
$PAGE->navbar->add(get_string('manage_employees', 'local_employees'), new moodle_url('/local/employees/index.php'));
$PAGE->navbar->add(get_string('uploadusers', 'local_employees'));
$returnurl = new moodle_url('/local/employees/index.php');
$labelstring = get_config('local_costcenter');
$firstlevel = strtolower($labelstring->firstlevel);
$secondlevel = strtolower($labelstring->secondlevel);
$thirdlevel = strtolower($labelstring->thirdlevel);
// array of all valid fields for validation
$STD_FIELDS = array($firstlevel,'username','first_name','last_name','password','email','staff_status','open_type','gender',
                        $secondlevel,'learner_id','teamlead','expertise','designation','fieldofstudy','institution','teamname','year','role','zone'
                        ,'region','graduationlevel','competitiondivision','city','state','contactnumber','address','language','linkedin','facebook','twitter','instagram','phone','department',$thirdlevel, 'role');

// $STD_FIELDS = array('organization','username','first_name','last_name','password','email','employee_status','open_type','gender',
//                         'department','learner_id','teamlead','expertise','designation','fieldofstudy','institution','teamname','year','role','zone'
//                         ,'region','graduationlevel','competitiondivision','city','state','contactnumber','address','language','linkedin','facebook','twitter','instagram','phone','department','subdepartment');
$PRF_FIELDS = array();
// if variable $iid equal to zero,it allows enter into the form 
$mform1 = new local_employees\forms\hrms_async();
if ($mform1->is_cancelled()) {
	redirect($returnurl);
}
if ($formdata = $mform1->get_data()) {
      echo $OUTPUT->header();
	$iid = csv_import_reader::get_new_iid('userfile');
	$cir = new csv_import_reader($iid, 'userfile'); 
	//this class fromcsvlib.php(includes csv methods and classes)
	$content = $mform1->get_file_content('userfile');
	$readcount = $cir->load_csv_content($content, $formdata->encoding, $formdata->delimiter_name);
	$cir->init();
	$linenum = 1; //column header is first line
	// init upload progress tracker------this class used to keeping track of code(each rows and columns)----
	$progresslibfunctions = new local_employees\cron\progresslibfunctions();
	$filecolumns = $progresslibfunctions->uu_validate_user_upload_columns($cir, $STD_FIELDS, $PRF_FIELDS, $returnurl);
	$hrms= new local_employees\cron\cronfunctionality();
	$hrms->main_hrms_frontendform_method($cir,$filecolumns, $formdata);
	 echo $OUTPUT->footer();
}
else{
	echo $OUTPUT->header();
	echo $OUTPUT->heading(get_string('uploadusers', 'local_employees'));

    	echo html_writer::link(new moodle_url('/local/employees/'),'Back',array('id'=>'download_employees','class' => 'btn btn-primary mr-2'));
	echo html_writer::link(new moodle_url('/local/employees/employeessample.php?format=csv'),'Sample',array('id'=>'download_employees','class' => 'btn btn-primary mr-2'));
	echo html_writer::link(new moodle_url('/local/employees/help.php'),'Help manual' ,array('id'=>'download_employees','class' => 'btn btn-primary mr-2','target'=>'__blank'));
	$mform1->display();
	echo $OUTPUT->footer();
	die;
}
