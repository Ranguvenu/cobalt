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
 * Version details.
 *
 * @package    block_proposals
 * @copyright  moodle
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/blocks/proposals/classes/form/fundedform.php');
global $DB , $USER;
require_login();
$PAGE->set_url(new moodle_url('/blocks/proposals/fundededit.php'));

$PAGE->requires->css('/blocks/proposals/css/styles.css');
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_title('Edit');
$formid = optional_param('formid' , $_REQUEST['id'] , PARAM_INT);
$userid = $USER->id;
$url = $CFG->wwwroot.'/blocks/proposals/view.php';
$username = $DB->get_record('user' , [ 'id' => $userid]);
$email = $username->email;
$schoolname = $DB->get_field('local_costcenter','fullname',array('id'=>$username->open_costcenterid));
$program = $DB->get_record_sql("SELECT name FROM {local_program} as lp JOIN {local_costcenter} as lc ON lp.costcenter = lc.id");

$programname = $program->name;
// $userdetails = $DB->get_record_sql("SELECT * FROM {user} us WHERE us.userid=$username->id");
// $school = $DB->get_record_sql("SELECT fullname FROM {local_school} ls JOIN {local_userdata} lu WHERE ls.id=lu.schoolid");
// $program = $DB->get_record_sql("SELECT fullname FROM {local_program} lp JOIN {local_userdata} lu WHERE lp.id=lu.programid");
// $programname = $program->fullname;
// $schoolname = $school->fullname;
// $rollnumber = $userdetails->serviceid;
$type  = $DB->get_record('applicationtable',array('id'=>1));
$mform = new funded();
 
 if ($formid) {
        $formdata = $DB->get_record('submissions', ['id' => $formid]);
       
        $mform->set_data($formdata);
    }

if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot . '/blocks/proposals/view.php', 'Submission Cancelled');
} else if ($fromform = $mform->get_data()) {

    if($fromform->submitbutton == 'Submit'){
    $item = $fromform->attachments;
    if ($draftitemid = file_get_submitted_draft_itemid('attachments')) {
        file_save_draft_area_files($draftitemid,
                                   $context->id,
                                   'block_proposals',
                                   'attachment',
                                   $item,
                                   array('subdirs' => 0,
                                         'maxfiles' => 1));

    } 
    if ($fromform->id) {
        if ($fromform->status == 0) {
            $status = '0';
        }
        if ($fromform->status == 3) {

            $status = '3';
        }
        $record = new stdClass();
        
        $record->id = $fromform->id;
        $record->title = $fromform->title;
        $record->applicationtype = $type->id;
        $record->rationale = $fromform->rationale;
        $record->departmentname = $fromform->departmentname;
        $record->projectdescription = $fromform->projectdescription;
        $record->noveltyinnovation = $fromform->noveltyinnovation;
        $record->strength = $fromform->strength;
        $record->departmentsupport = $fromform->departmentsupport;
        $record->financialsupport = $fromform->financialsupport;
        $record->status = $status;
        $record->userid = $userid;
        $record->draft = f1;
        $record->time = date("l jS \of F Y h:i:s A");
        $record->attachments = $fromform->attachments;
        $DB->update_record('submissions', $record);
        redirect($CFG->wwwroot . '/blocks/proposals/view.php', 'Submission Updated');
    } else {
            $recordtoinsert = new stdclass();
            $recordtoinsert->title = $fromform->title;
            // $recordtoinsert->applicationtype = $fromform->applicationtype;
            $recordtoinsert->rationale = $fromform->rationale;
            $recordtoinsert->departmentname = $fromform->departmentname;
            $recordtoinsert->projectdescription = $fromform->projectdescription;
            $recordtoinsert->noveltyinnovation = $fromform->noveltyinnovation;
            $recordtoinsert->strength = $fromform->strength;
            $recordtoinsert->departmentsupport = $fromform->departmentsupport;
            $recordtoinsert->financialsupport = $fromform->financialsupport;
            $recordtoinsert->attachments = $fromform->attachments;
            
            $recordtoinsert->draft = f1;
            $recordtoinsert->userid = $userid;
            $DB->insert_record('submissions', $recordtoinsert);
        redirect($CFG->wwwroot . '/blocks/proposals/view.php', 'Succesfully submitted');
        }
   
    }
}
    if ($data = $mform->get_submitted_data()) { 
         if($data->submitbutton == 'Save_to_Draft'){
       
        $item = $data->attachments;
    if ($draftitemid = file_get_submitted_draft_itemid('attachments')) {
        file_save_draft_area_files($draftitemid,
                                   $context->id,
                                   'block_proposals',
                                   'attachment',
                                   $item,
                                   array('subdirs' => 0,
                                         'maxfiles' => 1,
                                         'maxaccepted_types' => 'zip'));
    }
        if ($data->id) {
        if ($data->status == 0 ) {
            $status = '0';
        }
        if ($data->status == 3) {

            $status = '3';
        }
        $record = new stdClass();
        $date = date("l jS \of F Y h:i:s A");

        $record->id = $data->id;
        $record->title = $data->title;
        $record->applicationtype = $type->id;
        $record->rationale = $data->rationale;
        $record->departmentname = $data->departmentname;
        $record->projectdescription = $data->projectdescription;
        $record->noveltyinnovation = $data->noveltyinnovation;
        $record->strength = $data->strength;
        $record->departmentsupport = $data->departmentsupport;
        $record->financialsupport = $data->financialsupport;
        $record->status = $status;
        $record->userid = $userid;
        $record->draft = f0;
        $record->time = date("l jS \of F Y h:i:s A");
        $record->attachments = $data->attachments;
        $DB->update_record('submissions', $record);
        redirect($CFG->wwwroot . '/blocks/proposals/view.php', 'Your application is getting Saved as Draft,you may edit it further by opening and do final submission');
    }else {
            $recordtoinsert = new stdclass();
            $recordtoinsert->title = $data->title;
            // $recordtoinsert->applicationtype = $data->applicationtype;
            $recordtoinsert->rationale = $data->rationale;
            $recordtoinsert->departmentname = $data->departmentname;
            $recordtoinsert->projectdescription = $data->projectdescription;
            $recordtoinsert->noveltyinnovation = $data->noveltyinnovation;
            $recordtoinsert->strength = $data->strength;
            $recordtoinsert->departmentsupport = $data->departmentsupport;
            $recordtoinsert->financialsupport = $data->financialsupport;
            $recordtoinsert->attachments = $data->attachments;
            $recordtoinsert->userid = $userid;
            $recordtoinsert->draft = f0;
            $DB->insert_record('submissions', $recordtoinsert);
        redirect($CFG->wwwroot . '/blocks/proposals/view.php', 'Succesfully submitted to draft ');

    }
}
}
if ($tofrom = $mform->get_data()) {
    
    if($tofrom->submitbutton=='Previewpdf'){
        
         if ($tofrom->id) {
        if ($tofrom->status == 0 ) {
            $status = '0';
        }
        if ($tofrom->status == 3) {

            $status = '3';
        }
        $record = new stdClass();
        $record->id = $tofrom->id;
        $record->title = $tofrom->title;
        $record->applicationtype = $type->id;
        $record->rationale = $tofrom->rationale;
        $record->departmentname = $tofrom->departmentname;
        $record->projectdescription = $tofrom->projectdescription;
        $record->noveltyinnovation = $tofrom->noveltyinnovation;
        $record->strength = $tofrom->strength;
        $record->departmentsupport = $tofrom->departmentsupport;
        $record->financialsupport = $tofrom->financialsupport;
        $record->status = $status;
        $record->userid = $userid;
        
        $record->attachments = $tofrom->attachments;
        $DB->update_record('submissions', $record);
    }
}
        echo '<script>window.open("preview.php?id='.$tofrom->id.'","_blank")</script>';
        redirect($CFG->wwwroot . '/blocks/proposals/view.php', 'Pdf will display in the new tab ');
    }
$tabledata = $DB->get_record('submissions', ['id' => $formid]);

if ($tabledata) {

$applicationdata = $DB->get_record('applicationtable',['id' =>$tabledata->applicationtype]);
        $var = 'DYPU/';
        $var .= $applicationdata->code.'/';
        $date = date('Y');
        $var.=$date;
 if ($tabledata->status ==  0) {
        $status = "Waiting For Approval";
        $refid = $var.'/O/';
        $referenceid = $refid.$tabledata->id;
        $insertrefid = new stdclass();
        $insertrefid->id = $tabledata->id;
        $insertrefid->referenceid = $referenceid;

         $DB->update_record('submissions',$insertrefid);
}

 if ($tabledata->status ==  3) {
        $revisecount = 1;
        $countrev = $DB->get_field('submissions','countrev',array('id'=>$formid));
        $status = "Revision";
        $refid = $var.'/R'.$countrev.'/';
        $referenceid = $refid.$tabledata->id;
        $insertrefid = new stdclass();
        $insertrefid->id = $tabledata->id;
        $insertrefid->revisecount = $revisecount;
        $insertrefid->referenceid = $referenceid;

         $DB->update_record('submissions',$insertrefid);
 
}

}
$refid = $DB->get_field('submissions','referenceid',array('id'=>$formid));
$templatecontext = (object)[
    'apptype'=>$type->applicationtype,
    'refid' =>$refid,
    'back' => $url,
    'url' => $editurl,
    'user' => $username->firstname,
    'rollnumber' =>$rollnumber,
    'school' => $schoolname,
    'email' => $email,
    'program'=> $programname,
];
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('block_proposals/form1' , $templatecontext);
$mform->display();
echo $OUTPUT->footer();
