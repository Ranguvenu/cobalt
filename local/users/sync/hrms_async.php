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
 * @subpackage local_users
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

$returnurl = new moodle_url('/local/users/index.php');
if (!has_capability('local/users:manage',$systemcontext) || !has_capability('local/users:create', $systemcontext) ) {  
	print_error('You dont have permission');
}

$PAGE->set_url('/local/users/sync/hrms_async.php');
$strheading = get_string('pluginname', 'local_users') . ' : ' . get_string('uploadusers', 'local_users');
$PAGE->set_title($strheading);
$PAGE->navbar->add(get_string('pluginname', 'local_users'), new moodle_url('/local/users/index.php'));
$PAGE->navbar->add(get_string('uploadusers', 'local_users'));
$returnurl = new moodle_url('/local/users/index.php');

// array of all valid fields for validation
// $STD_FIELDS = array('organization','username','password','employee_name', 'first_name', 'last_name', 'department', 'address',
//                     'zone_region', 'area', 'city', 'role_designation', 'group', 'level', 'team', 'client', 'gender', 'email','marital_status','dob',
//                     'doj','student_status','reportingmanager_code','reportingmanager_name','reportingmanager_email', 'reportingmanager_empid','dol','dor','country','officialmail', 'subdepartment', 'location', 'password', 'timezone', 'role', 'notify_logins', 'force_password_change','phone','department','subdepartment','rollno');
$labelstring = get_config('local_costcenter');
$firstlevel = strtolower($labelstring->firstlevel);
$secondlevel = strtolower($labelstring->secondlevel);
$thirdlevel = strtolower($labelstring->thirdlevel);

$STD_FIELDS = array($firstlevel,'username','password','employee_name', 'first_name', 'last_name', 'address','zone_region', 'area', 'city', 'role_designation', 'group', 'level', 'team', 'client', 'gender', 'email','marital_status','dob','doj','student_status','reportingmanager_code','reportingmanager_name','reportingmanager_email', 'reportingmanager_empid','dol','dor','country','officialmail', 'subdepartment', 'location', 'password', 'timezone', 'role', 'notify_logins', 'force_password_change','phone',$secondlevel,$thirdlevel,'rollno');

$PRF_FIELDS = array();
//-------- if variable $iid equal to zero,it allows enter into the form 
$mform1 = new local_users\forms\hrms_async();
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
	// init upload progress tracker------this class used to keeping track of code(each rows and columns)-------------
	
	$progresslibfunctions = new local_users\cron\progresslibfunctions();
	$filecolumns = $progresslibfunctions->uu_validate_user_upload_columns($cir, $STD_FIELDS, $PRF_FIELDS, $returnurl);

	$hrms= new local_users\cron\cronfunctionality();
	$hrms->main_hrms_frontendform_method($cir,$filecolumns, $formdata);
	 echo $OUTPUT->footer();
}
else{
	echo $OUTPUT->header();
	echo $OUTPUT->heading(get_string('uploadusers', 'local_users'));
      echo html_writer::link(new moodle_url('/local/users/index.php'),get_string('back','local_users'),array('id'=>'download_users', 'class' => 'btn btn-primary mr-2'));
	echo html_writer::link(new moodle_url('/local/users/sample.php?format=csv'),get_string('sample','local_users'),array('id'=>'download_users', 'class' => 'btn btn-primary mr-2'));
	echo html_writer::link(new moodle_url('/local/users/help.php'),get_string('help_manual','local_users'),array('id'=>'download_users','class' => 'btn btn-primary mr-2', 'target'=>'__blank'));
	echo html_writer::tag('div', '', array('class'=>'clearfix'));
	$mform1->display();
	echo $OUTPUT->footer();
	die;
} 
