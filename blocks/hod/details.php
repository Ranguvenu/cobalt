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
 * @package    block_hod
 * @copyright  moodle
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/blocks/hod/classes/form/form.php');
global $DB , $USER;

require_login();


$PAGE->set_title('Submissions');
$PAGE->set_heading('Proposal Details');
$PAGE->set_url(new moodle_url('/blocks/hod/details.php'));
// $PAGE->set_url(new moodle_url('/blocks/hod/details.php?f));
$context = context_system::instance();
$PAGE->requires->css('/blocks/hod/css/details.css');
$formid = optional_param('formid' , null , PARAM_INT);
$USER;
echo $OUTPUT->header();
$url = $CFG->wwwroot.'/blocks/hod/view.php';


$form = $DB->get_record('submissions' , ['id' => $formid]);

 $deptname = $DB->get_field('department','departmentname',array('id'=>$form->departmentname));
 

if ($form->rationale == '') {
    $form->rationale = 'NA';
}
if ($form->projectdescription == '') {
    $form->projectdescription = 'NA';
}
if ($form->noveltyinnovation == '') {
   $form->noveltyinnovation = 'NA';
}
if ($form->strength == '') {
    $form->strength = 'NA';
}
if ($form->departmentsupport == '') {
    $form->departmentsupport = 'NA';
}
if ($form->financialsupport == '') {
     $form->financialsupport = 'NA';
}
if ($form->comment == '') {
    $form->comment = 'NA';
}
if ($deptname == '') {
     $deptname = 'NA';
}

$applicationdata = $DB->get_records('applicationtable',['id' =>$form->applicationtype]);
foreach ($applicationdata as $value) {
    $applicationtype = $value->applicationtype;
}

$username = $DB->get_record('user' , [ 'id' => $form->userid]);
$revisecount;
$email = $username->email;
$refid = '';

if ($form->status == 0) {
    $status = "Waiting For Approval";
    $var = 'DYPU/';
    
    $date = date('Y');
    foreach ($applicationdata as $value) {

            $var .= $value->code.'/';
            $var.=$date.'/';

            $refid = $var.'O/';
        }
        $referenceid = $refid.$form->id;
        $insertrefid = new stdclass();
        $insertrefid->id = $form->id;

        $insertrefid->referenceid = $referenceid;

        $DB->update_record('submissions',$insertrefid);
 
}
if ($form->status == 3) {
    $revisecount = 1;
    $status = "Revision";
    $countrev = $DB->get_field('submissions','countrev',array('id'=>$formid));
    $var = 'DYPU/';

    $date = date('Y');
    foreach ($applicationdata as $value) {

            $var .= $value->code.'/';
            $var.=$date.'/';

            $refid = $var.'R'.$countrev.'/';
        }

        $referenceid = $refid.$form->id;
        $insertrefid = new stdclass();
        $insertrefid->id = $form->id;
        $insertrefid->revisecount = $revisecount;
        $insertrefid->referenceid = $referenceid;

        $DB->update_record('submissions',$insertrefid);
    }


$data = $DB->get_record('submissions',array('id'=>$formid));

$schoolname = $DB->get_field('local_costcenter','fullname',array('id'=>$username->open_costcenterid));
$program = $DB->get_record_sql("SELECT name FROM {local_program} as lp JOIN {local_costcenter} as lc ON lp.costcenter = lc.id");

$programname = $program->name;

$currentsubmissionrecord=$DB->get_record('submissions',array('id'=>$formid));
$args = array(
    'submission' => $currentsubmissionrecord,

);
$mform = new form(null,$args);

if ($formid) {
    $formdata = $DB->get_record('submissions' , ['id' => $formid] , $fields = 'id , comment , status');
   
    
    $mform->set_data($formdata);
}

if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot . '/blocks/hod/view.php', get_string('SubmissionCancelled','block_hod'));
} else if ($fromform = $mform->get_data()) {
    
    $date = date("l jS \of F Y h:i:s A"); 
    $recordtoinsert = new \stdclass(); 
    $recordtoinsert->id = $fromform->formid;
    $recordtoinsert->status = $fromform->status;
    if($fromform->status<>1){
        $recordtoinsert->comment = implode(',',$fromform->comment);
    }
    $recordtoinsert->revisecount = $revisecount;
    $recordtoinsert->time = $date;


    if ($fromform->status == 3) {
        $countrev = $DB->get_field('submissions','countrev',array('id'=>$formid));
        $countrev = $countrev+1;
    }
    else if($fromform->status == 1 || $fromform->status == 2 && $countrev == 0){

        $countrev = $DB->get_field('submissions','countrev',array('id'=>$formid));
    }
    $res = $DB->get_record('user',array('id'=>$USER->id));
    
    if($res->levelofapprove == 1){
        $recordtoinsert = new \stdclass(); 
        $recordtoinsert->id = $fromform->formid;
        // $recordtoinsert->comment = implode(',',$fromform->comment);
        $recordtoinsert->approveronestatus = $fromform->approveronestatus;
        $DB->update_record('submissions' , $recordtoinsert);
        redirect($CFG->wwwroot . '/blocks/hod/view.php', get_string('StatusUpdated','block_hod'));
    }
    if($res->levelofapprove == 2){
        $recordtoinsert = new \stdclass(); 
        $recordtoinsert->id = $fromform->formid;
        // $recordtoinsert->comment = implode(',',$fromform->comment);
        $recordtoinsert->approvertwostatus = $fromform->approvertwostatus;
        $DB->update_record('submissions' , $recordtoinsert);
        redirect($CFG->wwwroot . '/blocks/hod/view.php', get_string('StatusUpdated','block_hod'));
    }
    $recordtoinsert->countrev = $countrev; 
    $DB->update_record('submissions' , $recordtoinsert);
    redirect($CFG->wwwroot . '/blocks/hod/view.php', get_string('StatusUpdated','block_hod'));
} 


$results = $DB->get_records('submissions',array('id'=>$formid));

foreach ($results as $result) {
    $itemid = $result->attachments;
    $casereportform =$result->casereportform;
    $otherquestionnaires =$result->otherquestionnaires;
    $informedconsentform =$result->informedconsentform;
    $patientinformationsheet=$result->patientinformationsheet;
    $waiverofconsentform =$result->waiverofconsentform;
    $samplesizejustrificationfile = $result->samplesizejustrificationfile;
    $studyprocedurefile = $result->studyprocedurefile;
 
    $otherquestionnairesone =$result->otherquestionnairesone;
    $otherquestionnairestwo =$result->otherquestionnairestwo;
    $otherquestionnairesthr =$result->otherquestionnairesthr;
    $otherquestionnairesfou =$result->otherquestionnairesfou;
    $otherquestionnairesfiv =$result->otherquestionnairesfiv;

    $fs6 = get_file_storage();
    $files = $fs6->get_area_files($context->id, 'block_proposals', 'samplesizejustrificationfile', $samplesizejustrificationfile, 'sortorder', false);
    foreach ($files as $file) {
        $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(),
                                                    $file->get_component(),
                                                    $file->get_filearea(),
                                                    $file->get_itemid(),
                                                    $file->get_filepath(),
                                                    $file->get_filename());
        $downloadurlsamplesize = $fileurl->out();
    }
    $fs7 = get_file_storage();
    $files = $fs7->get_area_files($context->id, 'block_proposals', 'studyprocedurefile', $studyprocedurefile, 'sortorder', false);
    foreach ($files as $file) {
        $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(),
                                                    $file->get_component(),
                                                    $file->get_filearea(),
                                                    $file->get_itemid(),
                                                    $file->get_filepath(),
                                                    $file->get_filename());
        $downloadurlstudyprocedure = $fileurl->out();
    }

    $fs8 = get_file_storage();
    $otherquestionnairesone = $fs8->get_area_files($context->id, 'block_proposals', 'otherquestionnairesone',
        $otherquestionnairesone, 'sortorder', false);
    foreach ($otherquestionnairesone as $file) {
        $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(),
                                                    $file->get_component(),
                                                    $file->get_filearea(),
                                                    $file->get_itemid(),
                                                    $file->get_filepath(),
                                                    $file->get_filename());
        $downloadurlother1 = $fileurl->out();
    }
    $fs9 = get_file_storage();
    $otherquestionnairestwo = $fs9->get_area_files($context->id, 'block_proposals', 'otherquestionnairestwo',
        $otherquestionnairestwo, 'sortorder', false);
    foreach ($otherquestionnairestwo as $file) {
        $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(),
                                                    $file->get_component(),
                                                    $file->get_filearea(),
                                                    $file->get_itemid(),
                                                    $file->get_filepath(),
                                                    $file->get_filename());
        $downloadurlother2 = $fileurl->out();
    }
    $fs10 = get_file_storage();
    $otherquestionnairesthr = $fs10->get_area_files($context->id, 'block_proposals', 'otherquestionnairesthr',
        $otherquestionnairesthr, 'sortorder', false);
    foreach ($otherquestionnairesthr as $file) {
        $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(),
                                                    $file->get_component(),
                                                    $file->get_filearea(),
                                                    $file->get_itemid(),
                                                    $file->get_filepath(),
                                                    $file->get_filename());
        $downloadurlother3 = $fileurl->out();
    }
    $fs11 = get_file_storage();
    $otherquestionnairesfou = $fs11->get_area_files($context->id, 'block_proposals', 'otherquestionnairesfou',
        $otherquestionnairesfou, 'sortorder', false);
    foreach ($otherquestionnairesfou as $file) {
        $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(),
                                                    $file->get_component(),
                                                    $file->get_filearea(),
                                                    $file->get_itemid(),
                                                    $file->get_filepath(),
                                                    $file->get_filename());
        $downloadurlother4 = $fileurl->out();
    }
    $fs12 = get_file_storage();
    $otherquestionnairesfiv = $fs12->get_area_files($context->id, 'block_proposals', 'otherquestionnairesfiv',
        $otherquestionnairesfiv, 'sortorder', false);
    foreach ($otherquestionnairesfiv as $file) {
        $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(),
                                                    $file->get_component(),
                                                    $file->get_filearea(),
                                                    $file->get_itemid(),
                                                    $file->get_filepath(),
                                                    $file->get_filename());
        $downloadurlother5 = $fileurl->out();
    }



    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'block_proposals', 'attachment', $itemid, 'sortorder', false);
    foreach ($files as $file) {
        $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(),
                                                    $file->get_component(),
                                                    $file->get_filearea(),
                                                    $file->get_itemid(),
                                                    $file->get_filepath(),
                                                    $file->get_filename());
        // $downloadurl = $fileurl->get_port() ?
        //                 $fileurl->get_scheme() . '://' .
        //                 $fileurl->get_host() .
        //                 $fileurl->get_path() . ':' .
        //                 $fileurl->get_port() :
        //                 $fileurl->get_scheme() . '://' .
        //                 $fileurl->get_host() .
        //                 $fileurl->get_path();
        $downloadurl = $fileurl->out();
    }
    $fs1 = get_file_storage();
    $informedconsentform1 = $fs1->get_area_files($context->id, 'block_proposals', 'informedconsentform',
        $informedconsentform, 'sortorder', false);
    foreach ($informedconsentform1 as $file) {
        $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(),
                                                    $file->get_component(),
                                                    $file->get_filearea(),
                                                    $file->get_itemid(),
                                                    $file->get_filepath(),
                                                    $file->get_filename());
        $downloadconsentformurl = $fileurl->out();
    }
    $fs2 = get_file_storage();
    $patientinformationsheet1 = $fs2->get_area_files($context->id, 'block_proposals', 'patientinformationsheet',
        $patientinformationsheet, 'sortorder', false);
    foreach ($patientinformationsheet1 as $file) {
        $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(),
                                                    $file->get_component(),
                                                    $file->get_filearea(),
                                                    $file->get_itemid(),
                                                    $file->get_filepath(),
                                                    $file->get_filename());
        $downloadurlsheet = $fileurl->out();
    }
    $fs3 = get_file_storage();
    $casereportform = $fs3->get_area_files($context->id, 'block_proposals', 'casereportform', $casereportform, 'sortorder', false);
    foreach ($casereportform as $file) {
        $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(),
                                                    $file->get_component(),
                                                    $file->get_filearea(),
                                                    $file->get_itemid(),
                                                    $file->get_filepath(),
                                                    $file->get_filename());
        $downloadurlpatient = $fileurl->out();
    }
    $fs4 = get_file_storage();
    $waiverofconsentform1 = $fs4->get_area_files($context->id, 'block_proposals', 'waiverofconsentform', $waiverofconsentform, 'sortorder', false);
    foreach ($waiverofconsentform1 as $file) {
        $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(),
                                                    $file->get_component(),
                                                    $file->get_filearea(),
                                                    $file->get_itemid(),
                                                    $file->get_filepath(),
                                                    $file->get_filename());
        $downloadurlwaiver = $fileurl->out();
    }
    $fs5 = get_file_storage();
    $otherquestionnairesone = $fs5->get_area_files($context->id, 'block_proposals', 'otherquestionnaires',
        $otherquestionnaires, 'sortorder', false);
    foreach ($otherquestionnairesone as $file) {
        $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(),
                                                    $file->get_component(),
                                                    $file->get_filearea(),
                                                    $file->get_itemid(),
                                                    $file->get_filepath(),
                                                    $file->get_filename());
        $downloadurlother = $fileurl->out();
    }

}

if ($form->blinding == 0) {
    $form->blinding ='NO';
}
else{
     $form->blinding ='YES';
}
if ($form->biologicalhandling == 0) {
    $form->biologicalhandling ='NO';
}
else{
    $form->biologicalhandling ='YES';
}
if ($form->randomizedradio == 0) {
    $form->randomizedradio ='NO';
}
else{
    $form->randomizedradio ='YES';
}
if ($form->funding == 0) {
    $form->funding ='Self';
}
else{
    $form->funding ='Organization';
}
if ($form->sponsorss == 0) {
    $form->sponsorss ='NO';
}else{
    $form->sponsorss ='YES';
}
if ($form->sponsor == null) {
    $form->sponsor ='NA';
}
if ($form->sponsor == 0) {
    $form->sponsor ='NO';
}else{
    $form->sponsor ='YES';
}

if ($form->population == 0) {
    $form->population ='NO';
}else{
     $form->population ='YES';
}
if ($form->randomized == 0) {
    $form->randomized ='NO';
}else{
     $form->randomized ='YES';
}
if ($form->biologicalmaterialhandling == 0) {
    $form->biologicalmaterialhandling ='NO';
}
else{
    $form->biologicalmaterialhandling ='YES';
}

if ($form->coinvestigator1 == null) {
    $form->coinvestigator1 ='NA';
}
if ($form->coinvestigator2 == null) {
    $form->coinvestigator2 ='NA';
}
if ($form->coinvestigator3 == null) {
    $form->coinvestigator3 ='NA';
}
if ($form->coinvestigator4 == null) {
    $form->coinvestigator4 ='NA';
}
if ($form->coinvestigator5 == null) {
    $form->coinvestigator5 ='NA';
}
if ($form->coguidename == null) {
    $form->coguidename ='NA';
} 
if ($form->groupradio == 0) {
    $form->groupradio ='NO';
}else if ($form->groupradio == 1){
     $form->groupradio ='YES';
}else{
    $form->groupradio ='NA';
}
if (!$form->methodofsampling) {
    $form->methodofsampling = 'NA';
}
if (!$form->methodofsamplingother) {
    $form->methodofsamplingother = 'NA';
}
if (!$form->intervention) {
    $form->intervention = 'NA';
}

if ($form->guidename == null) {
    $form->guidename ='NA';
}
if (!$form->interventionother) {
    $form->interventionother = 'NA';
}
if ($form->control == 0) {
    $form->control ='NO';
}else if ($form->control == 1){
     $form->control ='YES';
}else{
    $form->control ='NA';
}
if ($form->comparator == null) {
    $form->comparator ='NA';
}

if ($form->projectdescription == null) {
    $form->projectdescription ='NA';
}
if ($form->noveltyinnovation == null) {
    $form->noveltyinnovation ='NA';
}
if ($form->blindingyes == null) {
    $form->blindingyes ='NA';
}

if ($form->biologicalhandling == 0) {
    $form->biologicalhandling ='NO';
}
else{
    $form->biologicalhandling ='YES';
}
if (!$form->biologicalmaterialhandling) {
              $form->biologicalmaterialhandling= 'NA';
            }

$startdate = date("d-m-Y", $form->startdate);
 if($form->status == 1 AND $form->approveronestatus == 1){
    $status = 'Approved';
}
else if($form->status == 1 AND $form->approveronestatus == 2){
    $status = 'Rejected';
}
if($form->status == 1 AND $form->approvertwostatus == 1){
    $status = 'Approved';
}
else if($form->status == 1 AND $form->approvertwostatus == 2){
    $status = 'Rejected';
} 
if ($form->status == 1 AND $form->approveronestatus == 0) {
    $status = "Waiting For Approval";
}
else if($form->status == 1 AND $form->approveronestatus == 1 AND $form->approvertwostatus == 0){
 $status = "Waiting For Approval";
}

$enddate = date("d-m-Y", $form->enddate);
if ($form->applicationtype == 1) {
    $templatecontext = (object)[
    'attachment' => $downloadurl,
    'refid' => $data->referenceid,
    'user' => $username->firstname,
    'type' => $applicationtype,
    'title' => $form->title,
    'rationale' => $form->rationale,
    'innovation' => $form->noveltyinnovation,
    'strength' => $form->strength,
    'support' => $form->departmentsupport,
    'financial' => $form->financialsupport,
    'description' => $form->projectdescription,
    'status' => $status,
    'comment' => $form->comment,
    'url' => $url,
    'rollnumber' =>$rollnumber,
    'school' => $schoolname,
    'email' => $email,
    'program'=> $programname,
    'departmentname' => $deptname,
]; 
echo $OUTPUT->render_from_template('block_hod/form' , $templatecontext);
$mform->display();
echo $OUTPUT->footer();
}
else{
    $templatecontext = (object)[
    'refid' => $data->referenceid,
    'user' => $username->firstname,
    'type' => $applicationtype,
    'title' => $form->title,
    'status' => $status,
    'url' => $url,
    'rollnumber' =>$rollnumber,
    'school' => $schoolname,
    'email' => $email,
    'program'=> $programname,
    'downloadurlsamplesize'=>$downloadurlsamplesize,
    'downloadurlstudyprocedure'=>$downloadurlstudyprocedure,
    'strength' =>$form->strength,
    'userid' => $form->userid,
    'comment' => $form->comment,
    'applicationtype' => $form->applicationtype,
    'patientproforma' => $downloadurlpatient,
    'otherquestionnaires' => $downloadurlother, 
    'otherquestionnairesone' => $downloadurlother1,
    'otherquestionnairestwo' => $downloadurlother2,
    'otherquestionnairesthr' => $downloadurlother3,
    'otherquestionnairesfou' => $downloadurlother4,
    'otherquestionnairesfiv' => $downloadurlother5, 
    'scientifictitleofthestudy' => $form->scientifictitleofthestudy,
    'principleinvestigator' => $form->principleinvestigator,
    'nameofthecoguide' => $form->nameofthecoguide,
    'contactperson' => $form->contactperson,
    'funding' => $form->funding,
    'sponsorss' => $form->sponsorss,
    'studysite' => $form->studysite,
    'healthcondition' => $form->healthcondition,
    'population' => $form->population, 
    'coinvestigator1' =>$form->coinvestigator1,
    'coinvestigator2'=>$form->coinvestigator2,
    'coinvestigator3'=>$form->coinvestigator3,
    'coinvestigator4'=>$form->coinvestigator4,
    'coinvestigator5'=>$form->coinvestigator5,
    'coguidename'=>$form->coguidename,
    'guidename'=>$form->guidename,
    'startdate' =>$startdate,
    'enddate' =>$enddate, 
    'groupradio'=>$form->groupradio,
    'control'=>$form->control,
    'comparator'=>$form->comparator, 
    'typeofstudy' => $form->typeofstudy,
    'sponsor' => $form->sponsor,
    'randomized' => $form->randomized,
    'intervention' => $form->intervention,
    'interventionother' => $form->interventionother,
    'blindingyes' => $form->blindingyes,
    'completestudydesign' => $form->completestudydesign,
    'inclusioncriteria' => $form->inclusioncriteria,
    'exclusioncriteria' => $form->exclusioncriteria,
    'aimobjectives' => $form->aimobjectives,
    'primaryobjective' => $form->primaryobjective,
    'secondaryobjective' => $form->secondaryobjective,
    'primaryoutcome' => $form->primaryoutcome,
    'secondaryoutcome' => $form->secondaryoutcome,
    'studygroups' => $form->studygroups,
    'enrollmentprocess' => $form->enrollmentprocess,
    'studyprocedure' => $form->studyprocedure,
    'samplesizejustrification' => $form->samplesizejustrification,
    'biologicalmaterialhandling' => $form->biologicalmaterialhandling,
    'phaseoftrial' => $form->phaseoftrial,
    'studyperiodandduration' => $form->studyperiodandduration,
    'briefsummary' => $form->briefsummary,
    'vancouverstyleonly' => $form->vancouverstyleonly,
    'informedconsentform' => $downloadconsentformurl,
    'patientinformationsheet' => $downloadurlsheet,
    'waiverofconsentform' => $downloadurlwaiver,
    'studyarm' => $form->studyarm,
    'departmentname' => $deptname,
    'blinding' => $form->blinding,
    'biologicalhandling' => $form->biologicalhandling,
    'randomizedradio' => $form->randomizedradio,
    'methodofsampling' =>$form->methodofsampling,
    'methodofsamplingother' =>$form->methodofsamplingother,
    ];
echo $OUTPUT->render_from_template('block_hod/nonform' , $templatecontext);
$mform->display();
echo $OUTPUT->footer();
}



