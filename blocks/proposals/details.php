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
global $DB , $USER;
require_login();
$PAGE->set_title('Submissions');
$PAGE->set_heading('Proposal Details');
$context = context_system::instance();
$PAGE->requires->css('/blocks/hod/css/details.css');

$formid = optional_param('formid' , null , PARAM_INT);
echo $OUTPUT->header();
$url = $CFG->wwwroot.'/blocks/proposals/view.php';
$pdfurl = $CFG->wwwroot.'/blocks/proposals/preview1.php';
$form = $DB->get_record('submissions' , ['id' => $formid]);


$applicationdata = $DB->get_records('applicationtable',['id' =>$form->applicationtype]);

$username = $DB->get_record('user' , [ 'id' => $form->userid]);
$refid = '';
$email = $username->email;
$program = $DB->get_record_sql("SELECT name FROM {local_program} as lp JOIN {local_costcenter} as lc ON lp.costcenter = lc.id");

$programname = $program->name;

if ($form->status == 0) {
    $status = "Waiting For Approval";
}
$res = $DB->get_field('submissions','revisecount',array('id'=>$formid));

if ($res == null) {
if ($form->status == 1) {
    $status = "Approved";
    $var = 'DYPU/';
    $var .= $applicationdata->code;
    $date = date('Y');
    foreach ($applicationdata as $value) {

            $var .= $value->code.'/';
            $var.=$date.'/';

            $refid = $var.'O/';
        }
}
if ($form->status == 2) {
    $status = "Rejected";
    $var = 'DYPU/';
    $var .= $applicationdata->code;
    $date = date('Y');
    foreach ($applicationdata as $value) {

            $var .= $value->code.'/';
            $var.=$date.'/';
            $refid = $var.'O/';  
        }
    }

        $referenceid = $refid.$form->id;
        $insertrefid = new stdclass();
        $insertrefid->id = $form->id;
        $insertrefid->referenceid = $referenceid;
        $DB->update_record('submissions',$insertrefid);
}
if ($res == 1) {
    $res =1;
    $countrev = $DB->get_field('submissions','countrev',array('id'=>$formid));
if ($form->status == 1) {
    $status = "Approved";
    $var = 'DYPU/';
    $var .= $applicationdata->code;
    $date = date('Y');
    foreach ($applicationdata as $value) {

            $var .= $value->code.'/';
            $var.=$date.'/';

            $refid = $var.'R'.$countrev.'/';
        }
} 
if ($form->status == 2) {
    $status = "Rejected";
    $var = 'DYPU/';
    $var .= $applicationdata->code;
    $date = date('Y');
    foreach ($applicationdata as $value) {

            $var .= $value->code.'/';
            $var.=$date.'/';
            $refid = $var.'R'.$countrev.'/';  
        }
    } 
        $referenceid = $refid.$form->id;
        $insertrefid = new stdclass();
        $insertrefid->id = $form->id;
        $insertrefid->revisecount = $res;
        $insertrefid->referenceid = $referenceid;

        $DB->update_record('submissions',$insertrefid);
}

$data = $DB->get_record('submissions',array('id'=>$formid));

// $userdetails = $DB->get_record_sql("SELECT * FROM {local_userdata} us WHERE us.userid=$username->id");
// $school = $DB->get_record_sql("SELECT fullname FROM {local_school} ls JOIN {local_userdata} lu WHERE ls.id=lu.schoolid");
// $program = $DB->get_record_sql("SELECT fullname FROM {local_program} lp JOIN {local_userdata} lu WHERE lp.id=lu.programid");
// $programname = $program->fullname;
// $schoolname = $school->fullname;
$schoolname = $DB->get_field('local_costcenter','fullname',array('id'=>$username->open_costcenterid));
$refid = '';
$applicationname = $DB->get_records('applicationtable',array('id' => $form->applicationtype));
foreach ($applicationname as $value) {
    $applicationtype = $value->applicationtype;
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
        $downloadurl = $fileurl->out();
    }
    $fs1 = get_file_storage();
    $informedconsentform = $fs1->get_area_files($context->id, 'block_proposals', 'informedconsentform',
        $informedconsentform, 'sortorder', false);
    foreach ($informedconsentform as $file) {
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
    $casereportform1 = $fs3->get_area_files($context->id, 'block_proposals', 'casereportform', $casereportform, 'sortorder', false);
    foreach ($casereportform1 as $file) {
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
    $otherquestionnaires = $fs5->get_area_files($context->id, 'block_proposals', 'otherquestionnaires',
        $otherquestionnaires, 'sortorder', false);
    foreach ($otherquestionnaires as $file) {
        $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(),
                                                    $file->get_component(),
                                                    $file->get_filearea(),
                                                    $file->get_itemid(),
                                                    $file->get_filepath(),
                                                    $file->get_filename());
        $downloadurlother = $fileurl->out();
    } 

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
if ($form->funding == 0) {
    $form->funding ='Self';
}
else{
    $form->funding ='Organization';
}
if ($form->sponsorss == 0) {
    $form->sponsorss ='NO';
}
else{
    $form->sponsorss ='YES';
}
if ($form->population == 0) {
    $form->population ='NO';
}
else{
    $form->population ='YES';
}
if ($form->groupradio == 0) {
    $form->groupradio ='NO';
}else if ($form->groupradio == 1){
     $form->groupradio ='YES';
}else{
    $form->groupradio ='NA';
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

if ($form->guidename == null) {
    $form->guidename ='NA';
}
if ($form->noveltyinnovation == null) {
    $form->noveltyinnovation ='NA';
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

if (!$form->scientifictitleofthestudy) {
  $form->scientifictitleofthestudy = 'NA';
}
if (!$form->principleinvestigator) {
  $form->principleinvestigator = 'NA';
}
if (!$form->nameofthecoguide) {
  $form->nameofthecoguide = 'NA';
}
if (!$form->contactperson) {
  $form->contactperson = 'NA';
}
if (!$form->studyarm) {
  $form->studyarm = 'NA';
}
if (!$form->departmentname) {
  $form->departmentname = 'NA';
}
if (!$form->studysite) {
  $form->studysite = 'NA';
}
if (!$form->healthcondition) {
  $form->healthcondition = 'NA';
}
if (!$form->population) {
  $form->population = 'NA';
}
if (!$form->typeofstudy) {
  $form->typeofstudy = 'NA';
}
if (!$form->sponsor) {
  $form->sponsor = 'NA';
}
if (!$form->sponsorss) {
  $form->sponsorss = 'NA';
}
if (!$form->intervention) {
  $form->intervention = 'NA';
}
if (!$form->blindingyes) {
  $form->blindingyes = 'NA';
}
if (!$form->completestudydesign) {
  $form->completestudydesign = 'NA';
}
if (!$form->inclusioncriteria) {
  $form->inclusioncriteria = 'NA';
}
if (!$form->exclusioncriteria) {
  $form->exclusioncriteria = 'NA';
}
if (!$form->aimobjectives) {
  $form->aimobjectives = 'NA';
}
if (!$form->primaryobjective) {
  $form->primaryobjective = 'NA';
}
if (!$form->secondaryobjective) {
  $form->secondaryobjective = 'NA';
}
if (!$form->primaryoutcome) {
  $form->primaryoutcome = 'NA';
}
if (!$form->secondaryoutcome) {
  $form->secondaryoutcome = 'NA';
}
if (!$form->studygroups) {
  $form->studygroups = 'NA';
}
if (!$form->enrollmentprocess) {
  $form->enrollmentprocess = 'NA';
}
if (!$form->studyprocedure) {
  $form->studyprocedure = 'NA';
}
if (!$form->samplesizejustrification) {
  $form->samplesizejustrification = 'NA';
}
if (!$form->methodofsampling) {
  $form->methodofsampling = 'NA';
}
if (!$form->phaseoftrial) {
  $form->phaseoftrial = 'NA';
}
if (!$form->studyperiodandduration) {
  $form->studyperiodandduration = 'NA';
}
if (!$form->briefsummary) {
  $form->briefsummary = 'NA';
}
if (!$form->vancouverstyleonly) {
  $form->vancouverstyleonly = 'NA';
}
if (!$form->randomized) {
  $form->randomized = 'NA';
}
if (!$form->secondaryobjective) {
  $form->secondaryobjective = 'NA';
}
if (!$form->noveltyinnovation) {
  $form->noveltyinnovation= 'NA';
}
 if (!$form->projectdescription) {
  $form->projectdescription= 'NA';
}
 if (!$form->title) {
  $form->title= 'NA';
}
if (!$form->biologicalmaterialhandling) {
  $form->biologicalmaterialhandling= 'NA';
}

$startdate = date("d-m-Y", $form->startdate);
if(!$form->comment){
    $form->comment='NA';
}
$departname = $DB->get_field('department','departmentname',array('id'=>$form->departmentname));

$enddate = date("d-m-Y", $form->enddate);
if ($form->applicationtype == 1) {
    $templatecontext = (object)[
    'pdfurl'=>$pdfurl,
    'formid' =>$formid,
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
    'departmentname' => $departname,
    'url' => $url,
    'comment' => $form->comment,
    'rollnumber' =>$rollnumber,
    'school' => $schoolname,
    'email' => $email,
    'program'=> $programname,
];

echo $OUTPUT->render_from_template('block_proposals/details' , $templatecontext);
// $mform->display();
echo $OUTPUT->footer();
}
else{
      $templatecontext = (object)[
    'pdfurl'=>$pdfurl,
    'formid' =>$formid,
    'attachment' => $downloadurl,
    'refid' => $data->referenceid,
    'user' => $username->firstname,
    'type' => $applicationtype,
    'title' => $form->title,
    'status' => $status,
    'url' => $url,
    'rollnumber' =>$rollnumber,
    'school' => $schoolname,
    'guidename'=>$form->guidename,
    'downloadurlsamplesize'=>$downloadurlsamplesize,
    'downloadurlstudyprocedure'=>$downloadurlstudyprocedure,

    'groupradio'=>$form->groupradio,
    'control'=>$form->control,
    'comparator'=>$form->comparator,
    
    'coinvestigator1' =>$form->coinvestigator1,
    'coinvestigator2'=>$form->coinvestigator2,
    'coinvestigator3'=>$form->coinvestigator3,
    'coinvestigator4'=>$form->coinvestigator4,
    'coinvestigator5'=>$form->coinvestigator5,
   
    'startdate' =>$startdate,
    'enddate' =>$enddate,
    'email' => $email,
    'program'=> $programname,

    'strength' =>$form->strength,
    'userid' => $form->userid,
    'comment' => $form->comment,
    'applicationtype' => $form->applicationtype,
    'casereportform' => $downloadurlpatient,
    'otherquestionnaires' => $downloadurlother,

    'otherquestionnairesone' => $downloadurlother1,
    'otherquestionnairestwo' => $downloadurlother2,
    'otherquestionnairesthr' => $downloadurlother3,
    'otherquestionnairesfou' => $downloadurlother4,
    'otherquestionnairesfiv' => $downloadurlother5,


    'scientifictitleofthestudy' => $form->scientifictitleofthestudy,
    'principleinvestigator' => $form->principleinvestigator,
    'nameoftheguide' => $form->nameofthecoguide,
    'contactperson' => $form->contactperson,
    'funding' => $form->funding,
    'sponsorss' => $form->sponsorss,
    'studysite' => $form->studysite,
    'healthcondition' => $form->healthcondition,
    'population' => $form->population,
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
    'departmentname' => $departname,
    'blinding' => $form->blinding,
    'biologicalhandling' => $form->biologicalhandling,
    'randomizedradio' => $form->randomizedradio,
    'methodofsampling' =>$form->methodofsampling,
    'methodofsamplingother' =>$form->methodofsamplingother,
    ];
echo $OUTPUT->render_from_template('block_proposals/nonform' , $templatecontext);
// $mform->display();
echo $OUTPUT->footer();
}
