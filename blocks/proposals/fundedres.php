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
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot. '/blocks/proposals/classes/form/fundedresform.php');
$PAGE->requires->css('/blocks/proposals/css/styles.css');
require_once($CFG->dirroot. '/lib/tcpdf/tcpdf.php');

require_once($CFG->libdir.'/pdflib.php');

global $DB , $USER;
defined('MOODLE_INTERNAL') || die();
require_login();
$PAGE->set_url(new moodle_url('/blocks/proposals/fundedres.php'));
$PAGE->requires->js('/blocks/proposals/js/toggle.js',true);

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_title('Forms');
$PAGE->set_heading('Submissions');
$userid = $USER->id;

$url = $CFG->wwwroot;
$fundurl = $CFG->wwwroot.'/blocks/proposals/view.php';
$nonfundurl = $CFG->wwwroot.'/blocks/proposals/view.php';
$username = $DB->get_record('user' , [ 'id' => $userid]);
$email = $username->email;
$schoolname = $DB->get_field('local_costcenter','fullname',array('id'=>$username->open_costcenterid));
$program = $DB->get_record_sql("SELECT name FROM {local_program} as lp JOIN {local_costcenter} as lc ON lp.costcenter = lc.id");

$programname = $program->name;
// $userdetails = $DB->get_record_sql("SELECT * FROM {local_userdata} us WHERE us.userid=$username->id");
// $school = $DB->get_record_sql("SELECT fullname FROM {local_school} ls JOIN {local_userdata} lu ON ls.id=lu.schoolid");
// $program = $DB->get_record_sql("SELECT fullname FROM {local_program} lp JOIN {local_userdata} lu ON lp.id=lu.programid");
// $programname = $program->fullname;
// $schoolname = $school->fullname;
// $rollnumber = $userdetails->serviceid;
$type  = $DB->get_record('applicationtable',array('id'=>6));
$mform = new fundedresform();

if ($mform->is_cancelled()) {
        redirect($CFG->wwwroot, 'You cancelled the submission');
} else if ($fromform = $mform->get_data()) {
if ($fromform->submitbutton =='Submit') {  
             
        
        $informedconsentform = $fromform->informedconsentform;
        $patientinformationsheet = $fromform->patientinformationsheet;
        $casereportform = $fromform->casereportform; 
        $waiverofconsentform = $fromform->waiverofconsentform;
        $otherquestionnaires = $fromform->otherquestionnaires;
        $otherquestionnairesone =$fromform->otherquestionnairesone;
        $otherquestionnairestwo =$fromform->otherquestionnairestwo;
        $otherquestionnairesthr =$fromform->otherquestionnairesthr;
        $otherquestionnairesfou =$fromform->otherquestionnairesfou;
        $otherquestionnairesfiv =$fromform->otherquestionnairesfiv;
        $samplesizejustrificationfile = $fromform->samplesizejustrificationfile;
        $studyprocedurefile =$fromform->studyprocedurefile;

        if ($draftitemid = file_get_submitted_draft_itemid('otherquestionnairesone')) {
        file_save_draft_area_files($draftitemid,
                                   $context->id,
                                   'block_proposals',
                                   'otherquestionnairesone',
                                   $otherquestionnairesone,
                                   array('subdirs' => 0,'maxfiles' => 1,
                                    'accepted_types' => array('.doc','.pdf','.jpg')));
    }
    if ($draftitemid = file_get_submitted_draft_itemid('otherquestionnairestwo')) {
        file_save_draft_area_files($draftitemid,
                                   $context->id,
                                   'block_proposals',
                                   'otherquestionnairestwo',
                                   $otherquestionnairestwo,
                                   array('subdirs' => 0,'maxfiles' => 1,
                                    'accepted_types' => array('.doc','.pdf','.jpg')));
    }
    if ($draftitemid = file_get_submitted_draft_itemid('otherquestionnairesthr')) {
        file_save_draft_area_files($draftitemid,
                                   $context->id,
                                   'block_proposals',
                                   'otherquestionnairesthr',
                                   $otherquestionnairesthr,
                                   array('subdirs' => 0,'maxfiles' => 1,
                                    'accepted_types' => array('.doc','.pdf','.jpg')));
    }
    if ($draftitemid = file_get_submitted_draft_itemid('otherquestionnairesfou')) {
        file_save_draft_area_files($draftitemid,
                                   $context->id,
                                   'block_proposals',
                                   'otherquestionnairesfou',
                                   $otherquestionnairesfou,
                                   array('subdirs' => 0,'maxfiles' => 1,
                                    'accepted_types' => array('.doc','.pdf','.jpg')));
    }
    if ($draftitemid = file_get_submitted_draft_itemid('otherquestionnairesfiv')) {
        file_save_draft_area_files($draftitemid,
                                   $context->id,
                                   'block_proposals',
                                   'otherquestionnairesfiv',
                                   $otherquestionnairesfiv,
                                   array('subdirs' => 0,'maxfiles' => 1,
                                    'accepted_types' => array('.doc','.pdf','.jpg')));
    }

    if ($draftitemid = file_get_submitted_draft_itemid('samplesizejustrificationfile')) {
        file_save_draft_area_files($draftitemid,
                                   $context->id,
                                   'block_proposals',
                                   'samplesizejustrificationfile',
                                   $samplesizejustrificationfile,
                                   array('subdirs' => 0,'maxfiles' => 1,
                                    'accepted_types' => array('*')));
    }
    if ($draftitemid = file_get_submitted_draft_itemid('studyprocedurefile')) {
        file_save_draft_area_files($draftitemid,
                                   $context->id,
                                   'block_proposals',
                                   'studyprocedurefile',
                                   $studyprocedurefile,
                                   array('subdirs' => 0,'maxfiles' => 1,
                                    'accepted_types' => array('*')));
    }
    if ($draftitemid = file_get_submitted_draft_itemid('informedconsentform')) {
        file_save_draft_area_files($draftitemid,
                                   $context->id,
                                   'block_proposals',
                                   'informedconsentform',
                                   $informedconsentform,
                                   array('subdirs' => 0,'maxfiles' => 1,
                                    'accepted_types' => array('.doc','.pdf')));
    }
    if ($draftitemid = file_get_submitted_draft_itemid('patientinformationsheet')) {
        file_save_draft_area_files($draftitemid,
                                   $context->id,
                                   'block_proposals',
                                   'patientinformationsheet',
                                   $patientinformationsheet,
                                   array('subdirs' => 0,'maxfiles' => 1,
                                    'accepted_types' => array('.doc','.pdf')));
    }
    if ($draftitemid = file_get_submitted_draft_itemid('casereportform')) {
        file_save_draft_area_files($draftitemid,
                                   $context->id,
                                   'block_proposals',
                                   'casereportform',
                                   $casereportform,
                                   array('subdirs' => 0,'maxfiles' => 1,
                                    'accepted_types' => array('.doc','.pdf')));
    }
    if ($draftitemid = file_get_submitted_draft_itemid('waiverofconsentform')) {
        file_save_draft_area_files($draftitemid,
                                   $context->id,
                                   'block_proposals',
                                   'waiverofconsentform',
                                   $waiverofconsentform,
                                   array('subdirs' => 0,'maxfiles' => 1,
                                    'accepted_types' => array('.doc','.pdf')));
    }
    if ($draftitemid = file_get_submitted_draft_itemid('otherquestionnaires')) {
        file_save_draft_area_files($draftitemid,
                                   $context->id,
                                   'block_proposals',
                                   'otherquestionnaires',
                                   $otherquestionnaires,
                                   array('subdirs' => 0,'maxfiles' => 1,
                                    'accepted_types' => array('.doc','.pdf','.jpg')));
    }
        $recordtoinsert = new stdclass();
        $date = date("l jS \of F Y h:i:s A");
         $recordtoinsert->title = $fromform->title;

        $recordtoinsert->applicationtype = $type->id;
        $recordtoinsert->scientifictitleofthestudy = $fromform->scientifictitleofthestudy;
        $recordtoinsert->principleinvestigator = $fromform->principleinvestigator;
         $recordtoinsert->nameofthecoguide = $fromform->nameofthecoguide;
        $recordtoinsert->guidename = $fromform->guidename; 
            
        $recordtoinsert->userid = $userid;
        $recordtoinsert->draft = $draft;
        
        $recordtoinsert->interventionother = $fromform->interventionother;

        $recordtoinsert->contactperson = $fromform->contactperson;
        $recordtoinsert->funding = $fromform->funding;
        $recordtoinsert->biologicalhandling = $fromform->biologicalhandling;
        $recordtoinsert->studyarm = $fromform->studyarm;
        $recordtoinsert->departmentname = $fromform->departmentname;
        $recordtoinsert->studysite = $fromform->studysite;
        $recordtoinsert->healthcondition = $fromform->healthcondition;
        $recordtoinsert->population = $fromform->population;
        $recordtoinsert->typeofstudy = $fromform->typeofstudy;
        $recordtoinsert->sponsor = $fromform->sponsor;
        $recordtoinsert->sponsorss = $fromform->sponsorss;
        $recordtoinsert->randomized = $fromform->randomized;
        $recordtoinsert->intervention = $fromform->intervention;
        $recordtoinsert->blindingyes = $fromform->blindingyes;
        $recordtoinsert->blinding = $fromform->blinding;
        $recordtoinsert->completestudydesign = $fromform->completestudydesign;
        $recordtoinsert->inclusioncriteria = $fromform->inclusioncriteria;
        $recordtoinsert->exclusioncriteria = $fromform->exclusioncriteria;
        $recordtoinsert->aimobjectives = $fromform->aimobjectives;
        $recordtoinsert->time = $date;
        $recordtoinsert->methodofsamplingother = $fromform->methodofsamplingother;
        $recordtoinsert->startdate = $fromform->startdate;
        $recordtoinsert->enddate = $fromform->enddate;
        
        $recordtoinsert->coinvestigator1 = $fromform->coinvestigator1;
        $recordtoinsert->coinvestigator2 = $fromform->coinvestigator2;
        $recordtoinsert->coinvestigator3 = $fromform->coinvestigator3;
        $recordtoinsert->coinvestigator4 = $fromform->coinvestigator4;
        $recordtoinsert->coinvestigator5 = $fromform->coinvestigator5;
        


        $recordtoinsert->groupradio = $fromform->groupradio;
        $recordtoinsert->control = $fromform->control;
        $recordtoinsert->comparator = $fromform->comparator;

        $recordtoinsert->primaryobjective = $fromform->primaryobjective;
        $recordtoinsert->secondaryobjective = $fromform->secondaryobjective;
        $recordtoinsert->primaryoutcome = $fromform->primaryoutcome;
        $recordtoinsert->secondaryoutcome = $fromform->secondaryoutcome;
        $recordtoinsert->studygroups = $fromform->studygroups;
        $recordtoinsert->enrollmentprocess = $fromform->enrollmentprocess;
        $recordtoinsert->studyprocedure = $fromform->studyprocedure;

        $recordtoinsert->studyprocedurefile = $fromform->studyprocedurefile;
        
        
        $recordtoinsert->samplesizejustrificationfile = $fromform->samplesizejustrificationfile;
        $recordtoinsert->samplesizejustrification = $fromform->samplesizejustrification;
        $recordtoinsert->methodofsampling = $fromform->methodofsampling;
        $recordtoinsert->biologicalmaterialhandling = $fromform->biologicalmaterialhandling;
        $recordtoinsert->phaseoftrial = $fromform->phaseoftrial;
        $recordtoinsert->studyperiodandduration = $fromform->studyperiodandduration;
        $recordtoinsert->briefsummary = $fromform->briefsummary;
        $recordtoinsert->randomizedradio = $fromform->randomizedradio;
        
        $recordtoinsert->vancouverstyleonly = $fromform->vancouverstyleonly;

        $recordtoinsert->informedconsentform = $fromform->informedconsentform;
        $recordtoinsert->patientinformationsheet = $fromform->patientinformationsheet;
        $recordtoinsert->casereportform = $fromform->casereportform;
        $recordtoinsert->waiverofconsentform = $fromform->waiverofconsentform;
        $recordtoinsert->otherquestionnaires = $fromform->otherquestionnaires;

        $recordtoinsert->otherquestionnairesone = $fromform->otherquestionnairesone;
        $recordtoinsert->otherquestionnairestwo= $fromform->otherquestionnairestwo;
        $recordtoinsert->otherquestionnairesthr= $fromform->otherquestionnairesthr;
        $recordtoinsert->otherquestionnairesfou= $fromform->otherquestionnairesfou;
        $recordtoinsert->otherquestionnairesfiv= $fromform->otherquestionnairesfiv;

        $recordtoinsert->draft = nres1;
        $DB->insert_record('submissions', $recordtoinsert);
        redirect($CFG->wwwroot . '/blocks/proposals/view.php', 'work submitted successfully');
}
}
 if ($data = $mform->get_submitted_data()) {
 
   if ($data->submitbutton =='Save_to_Draft') {


        $samplesizejustrificationfile = $data->samplesizejustrificationfile;
        $studyprocedurefile =$data->studyprocedurefile;
        $informedconsentform = $data->informedconsentform;
        $patientinformationsheet = $data->patientinformationsheet;
        $casereportform = $data->casereportform; 
        $waiverofconsentform = $data->waiverofconsentform;
        $otherquestionnaires = $data->otherquestionnaires;
       
        $otherquestionnairesone =$data->otherquestionnairesone;
        $otherquestionnairestwo =$data->otherquestionnairestwo;
        $otherquestionnairesthr =$data->otherquestionnairesthr;
        $otherquestionnairesfou =$data->otherquestionnairesfou;
        $otherquestionnairesfiv =$data->otherquestionnairesfiv;
if ($draftitemid = file_get_submitted_draft_itemid('otherquestionnairesone')) {
        file_save_draft_area_files($draftitemid,
                                   $context->id,
                                   'block_proposals',
                                   'otherquestionnairesone',
                                   $otherquestionnairesone,
                                   array('subdirs' => 0,'maxfiles' => 1,
                                    'accepted_types' => array('.doc','.pdf','.jpg')));
    }
    if ($draftitemid = file_get_submitted_draft_itemid('otherquestionnairestwo')) {
        file_save_draft_area_files($draftitemid,
                                   $context->id,
                                   'block_proposals',
                                   'otherquestionnairestwo',
                                   $otherquestionnairestwo,
                                   array('subdirs' => 0,'maxfiles' => 1,
                                    'accepted_types' => array('.doc','.pdf','.jpg')));
    }
    if ($draftitemid = file_get_submitted_draft_itemid('otherquestionnairesthr')) {
        file_save_draft_area_files($draftitemid,
                                   $context->id,
                                   'block_proposals',
                                   'otherquestionnairesthr',
                                   $otherquestionnairesthr,
                                   array('subdirs' => 0,'maxfiles' => 1,
                                    'accepted_types' => array('.doc','.pdf','.jpg')));
    }
    if ($draftitemid = file_get_submitted_draft_itemid('otherquestionnairesfou')) {
        file_save_draft_area_files($draftitemid,
                                   $context->id,
                                   'block_proposals',
                                   'otherquestionnairesfou',
                                   $otherquestionnairesfou,
                                   array('subdirs' => 0,'maxfiles' => 1,
                                    'accepted_types' => array('.doc','.pdf','.jpg')));
    }
    if ($draftitemid = file_get_submitted_draft_itemid('otherquestionnairesfiv')) {
        file_save_draft_area_files($draftitemid,
                                   $context->id,
                                   'block_proposals',
                                   'otherquestionnairesfiv',
                                   $otherquestionnairesfiv,
                                   array('subdirs' => 0,'maxfiles' => 1,
                                    'accepted_types' => array('.doc','.pdf','.jpg')));
    }

    if ($draftitemid = file_get_submitted_draft_itemid('samplesizejustrificationfile')) {
        file_save_draft_area_files($draftitemid,
                                   $context->id,
                                   'block_proposals',
                                   'samplesizejustrificationfile',
                                   $samplesizejustrificationfile,
                                   array('subdirs' => 0,'maxfiles' => 1,
                                    'accepted_types' => array('*')));
    }
    if ($draftitemid = file_get_submitted_draft_itemid('studyprocedurefile')) {
        file_save_draft_area_files($draftitemid,
                                   $context->id,
                                   'block_proposals',
                                   'studyprocedurefile',
                                   $studyprocedurefile,
                                   array('subdirs' => 0,'maxfiles' => 1,
                                    'accepted_types' => array('*')));
    }
    if ($draftitemid = file_get_submitted_draft_itemid('informedconsentform')) {
        file_save_draft_area_files($draftitemid,
                                   $context->id,
                                   'block_proposals',
                                   'informedconsentform',
                                   $informedconsentform,
                                   array('subdirs' => 0,'maxfiles' => 1,
                                    'accepted_types' => array('.doc','.pdf')));
    }
    if ($draftitemid = file_get_submitted_draft_itemid('patientinformationsheet')) {
        file_save_draft_area_files($draftitemid,
                                   $context->id,
                                   'block_proposals',
                                   'patientinformationsheet',
                                   $patientinformationsheet,
                                   array('subdirs' => 0,'maxfiles' => 1,
                                    'accepted_types' => array('.doc','.pdf')));
    }
    if ($draftitemid = file_get_submitted_draft_itemid('casereportform')) {
        file_save_draft_area_files($draftitemid,
                                   $context->id,
                                   'block_proposals',
                                   'casereportform',
                                   $casereportform,
                                   array('subdirs' => 0,'maxfiles' => 1,
                                    'accepted_types' => array('.doc','.pdf')));
    }
    if ($draftitemid = file_get_submitted_draft_itemid('waiverofconsentform')) {
        file_save_draft_area_files($draftitemid,
                                   $context->id,
                                   'block_proposals',
                                   'waiverofconsentform',
                                   $waiverofconsentform,
                                   array('subdirs' => 0,'maxfiles' => 1,
                                    'accepted_types' => array('.doc','.pdf')));
    }
    if ($draftitemid = file_get_submitted_draft_itemid('otherquestionnaires')) {
        file_save_draft_area_files($draftitemid,
                                   $context->id,
                                   'block_proposals',
                                   'otherquestionnaires',
                                   $otherquestionnaires,
                                   array('subdirs' => 0,'maxfiles' => 1,
                                    'accepted_types' => array('.doc','.pdf','.jpg')));
    }
    
        $recordtoinsert = new stdclass();
        $date = date("l jS \of F Y h:i:s A");
        $recordtoinsert->title = $data->title;

        $recordtoinsert->applicationtype = $type->id;
        $recordtoinsert->scientifictitleofthestudy = $data->scientifictitleofthestudy;
        $recordtoinsert->principleinvestigator = $data->principleinvestigator;
        $recordtoinsert->nameoftheguide = $data->nameoftheguide;
        $recordtoinsert->userid = $userid;

        $recordtoinsert->contactperson = $data->contactperson;
        $recordtoinsert->funding = $data->funding;
        $recordtoinsert->biologicalhandling = $data->biologicalhandling;
        $recordtoinsert->studyarm = $data->studyarm;
        $recordtoinsert->departmentname = $data->departmentname;
        $recordtoinsert->studysite = $data->studysite;
        $recordtoinsert->healthcondition = $data->healthcondition;
        $recordtoinsert->population = $data->population;
        $recordtoinsert->typeofstudy = $data->typeofstudy;
        $recordtoinsert->sponsor = $data->sponsor;
        $recordtoinsert->interventionother = $data->interventionother;
        $recordtoinsert->sponsorss = $data->sponsorss;
        $recordtoinsert->randomized = $data->randomized;
        $recordtoinsert->intervention = $data->intervention;
        $recordtoinsert->blindingyes = $data->blindingyes;
        $recordtoinsert->Blinding = $data->Blinding;
        $recordtoinsert->completestudydesign = $data->completestudydesign;
        $recordtoinsert->inclusioncriteria = $data->inclusioncriteria;
        $recordtoinsert->exclusioncriteria = $data->exclusioncriteria;
        $recordtoinsert->aimobjectives = $data->aimobjectives;
        $recordtoinsert->primaryobjective = $data->primaryobjective;
        $recordtoinsert->secondaryobjective = $data->secondaryobjective;
        $recordtoinsert->groupradio = $data->groupradio;
        $recordtoinsert->control = $data->control;
         $recordtoinsert->comparator = $data->comparator;
        $recordtoinsert->primaryoutcome = $data->primaryoutcome;
        $recordtoinsert->secondaryoutcome = $data->secondaryoutcome;
        $recordtoinsert->studygroups = $data->studygroups;
        $recordtoinsert->enrollmentprocess = $data->enrollmentprocess;
        $recordtoinsert->studyprocedure = $data->studyprocedure;
        $recordtoinsert->studyprocedurefile = $data->studyprocedurefile;
        $recordtoinsert->samplesizejustrification = $data->samplesizejustrification;
        $recordtoinsert->samplesizejustrificationfile = $data->samplesizejustrificationfile;
        
        $recordtoinsert->methodofsampling = $data->methodofsampling;
        $recordtoinsert->biologicalmaterialhandling = $data->biologicalmaterialhandling;
        $recordtoinsert->phaseoftrial = $data->phaseoftrial;
        $recordtoinsert->studyperiodandduration = $data->studyperiodandduration;
        $recordtoinsert->briefsummary = $data->briefsummary;
        $recordtoinsert->randomizedradio = $data->randomizedradio;
        
        $recordtoinsert->methodofsamplingother = $data->methodofsamplingother;
        $recordtoinsert->startdate = $data->startdate;
        $recordtoinsert->enddate = $data->enddate;
        $recordtoinsert->time = $date;
        $recordtoinsert->nameofthecoguide = $data->nameofthecoguide;
        $recordtoinsert->guidename = $data->guidename;
        $recordtoinsert->coinvestigator1 = $data->coinvestigator1;
        $recordtoinsert->coinvestigator2 = $data->coinvestigator2;
        $recordtoinsert->coinvestigator3 = $data->coinvestigator3;
        $recordtoinsert->coinvestigator4 = $data->coinvestigator4;
        $recordtoinsert->coinvestigator5 = $data->coinvestigator5;

        $recordtoinsert->vancouverstyleonly = $data->vancouverstyleonly;

        $recordtoinsert->informedconsentform = $data->informedconsentform;
        $recordtoinsert->patientinformationsheet = $data->patientinformationsheet;
        $recordtoinsert->casereportform = $data->casereportform;
        $recordtoinsert->waiverofconsentform = $data->waiverofconsentform;
        $recordtoinsert->otherquestionnaires = $data->otherquestionnaires;

        $recordtoinsert->otherquestionnairesone = $data->otherquestionnairesone;
        $recordtoinsert->otherquestionnairestwo= $data->otherquestionnairestwo;
        $recordtoinsert->otherquestionnairesthr= $data->otherquestionnairesthr;
        $recordtoinsert->otherquestionnairesfou= $data->otherquestionnairesfou;
        $recordtoinsert->otherquestionnairesfiv= $data->otherquestionnairesfiv;
        $recordtoinsert->draft = nres0;
        $DB->insert_record('submissions', $recordtoinsert);
        redirect($CFG->wwwroot .'/blocks/proposals/view.php',  'Successfully saved in draft');
        }
    }
$templatecontext = (object)[
        'apptype'=>$type->applicationtype,
        'back' => $url,
        'user' => $username->firstname,
        'rollnumber' =>$rollnumber,
        'school' => $schoolname,
        'email' => $email,
        'program'=> $programname,
        'fundurl' =>$fundurl,
        'nonfundurl' =>$nonfundurl,
    ];
    echo $OUTPUT->header();
    echo $OUTPUT->render_from_template('block_proposals/form' , $templatecontext);
$mform->display();
echo $OUTPUT->footer();
