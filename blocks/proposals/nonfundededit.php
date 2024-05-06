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
require_once($CFG->dirroot . '/blocks/proposals/classes/form/nonfundedform.php');
global $DB , $USER;
require_login();
$PAGE->set_url(new moodle_url('/blocks/proposals/nonfundededit.php'));
$PAGE->requires->css('/blocks/proposals/css/styles.css');
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_title('Edit');
$formid = optional_param('formid' , $_REQUEST['id'] , PARAM_INT);

$userid = $USER->id;
$url = $CFG->wwwroot.'/blocks/proposals/view.php';
$username = $DB->get_record('user' , [ 'id' => $userid]);
$email = $username->email;
$program = $DB->get_record_sql("SELECT name FROM {local_program} as lp JOIN {local_costcenter} as lc ON lp.costcenter = lc.id");

$programname = $program->name;
$schoolname = $DB->get_field('local_costcenter','fullname',array('id'=>$username->open_costcenterid));
$mform = new nonfunded();
$refid = $DB->get_records('submissions',array('id' =>$formid));
$editoroptions = array(
                    'maxfiles' => EDITOR_UNLIMITED_FILES,
                    'maxbytes' => $CFG->maxbytes,
                    'trusttext' => false,
                    'forcehttps' => false,
                    'context' => $systemcontext,
                    );
 if ($formid) {
        $formdata = $DB->get_record('submissions', ['id' => $formid]);
        $mform->set_data($formdata);
    }

if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot . '/blocks/proposals/view.php', 'Submission Cancelled');
} else if ($fromform = $mform->get_data()) {
    if ($fromform->submitbutton == 'Submit') {
            
    if ($fromform->id) {
        if ($fromform->status == 0) {
            $status = '0';
        }
        if ($fromform->status == 3) {

            $status = '3';
        }
        $informedconsentform = $fromform->informedconsentform;
        $patientinformationsheet = $fromform->patientinformationsheet;
        $casereportform = $fromform->casereportform; 
        $waiverofconsentform = $fromform->waiverofconsentform;
        $otherquestionnaires = $fromform->otherquestionnaires;

        $samplesizejustrificationfile = $fromform->samplesizejustrificationfile;
        $studyprocedurefile =$fromform->studyprocedurefile;

        $otherquestionnairesone =$fromform->otherquestionnairesone;
        $otherquestionnairestwo =$fromform->otherquestionnairestwo;
        $otherquestionnairesthr =$fromform->otherquestionnairesthr;
        $otherquestionnairesfou =$fromform->otherquestionnairesfou;
        $otherquestionnairesfiv =$fromform->otherquestionnairesfiv;
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
                                   array('subdirs' => 0,'maxfiles' => 5,
                                    'accepted_types' => array('.doc','.pdf','.jpg')));
    }

 
        $recordtoupdate = new stdClass();
        $recordtoupdate->id = $fromform->id;
        $recordtoupdate->title = $fromform->title;
        $recordtoupdate->applicationtype = $fromform->applicationtype;
        $recordtoupdate->scientifictitleofthestudy = $fromform->scientifictitleofthestudy;
        $recordtoupdate->principleinvestigator = $fromform->principleinvestigator;
        $recordtoupdate->nameofthecoguide = $fromform->nameofthecoguide;
        $recordtoupdate->contactperson = $fromform->contactperson;
        $recordtoupdate->funding = $fromform->funding;
        $recordtoupdate->sponsorss = $fromform->sponsorss;
        $recordtoupdate->departmentname = $fromform->departmentname;
        $recordtoupdate->studysite = $fromform->studysite;
        $recordtoupdate->healthcondition = $fromform->healthcondition;
        $recordtoupdate->population = $fromform->population;
        $recordtoupdate->typeofstudy = $fromform->typeofstudy;
        $recordtoupdate->sponsor = $fromform->sponsor;
        $recordtoupdate->interventionother = $fromform->interventionother;
        $recordtoupdate->methodofsamplingother = $fromform->methodofsamplingother;
        $recordtoupdate->startdate = $fromform->startdate;
        $recordtoupdate->enddate = $fromform->enddate;


        $recordtoupdate->guidename = $fromform->guidename;
         
        $recordtoupdate->studyprocedure = $fromform->studyprocedure;

        $recordtoupdate->studyprocedurefile = $fromform->studyprocedurefile;
        $recordtoupdate->samplesizejustrificationfile = $fromform->samplesizejustrificationfile;
        $recordtoupdate->samplesizejustrification = $fromform->samplesizejustrification;
        $recordtoupdate->time = date("l jS \of F Y h:i:s A");
        $recordtoupdate->coinvestigator1 = $fromform->coinvestigator1;
        $recordtoupdate->coinvestigator2 = $fromform->coinvestigator2;
        $recordtoupdate->coinvestigator3 = $fromform->coinvestigator3;
        $recordtoupdate->coinvestigator4 = $fromform->coinvestigator4;
         $recordtoupdate->coinvestigator5 = $fromform->coinvestigator5;
        $recordtoupdate->randomizedradio = $fromform->randomizedradio;
        $recordtoupdate->biologicalhandling = $fromform->biologicalhandling;
        $recordtoupdate->randomized = $fromform->randomized;
        $recordtoupdate->intervention = $fromform->intervention;
        $recordtoupdate->blindingyes = $fromform->blindingyes;
        $recordtoupdate->blinding = $fromform->blinding;
        $recordtoupdate->completestudydesign = $fromform->completestudydesign;
        $recordtoupdate->inclusioncriteria = $fromform->inclusioncriteria;
        $recordtoupdate->exclusioncriteria = $fromform->exclusioncriteria;
        $recordtoupdate->aimobjectives = $fromform->aimobjectives;
        $recordtoupdate->primaryobjective = $fromform->primaryobjective;
        $recordtoupdate->secondaryobjective = $fromform->secondaryobjective;
        $recordtoupdate->primaryoutcome = $fromform->primaryoutcome;
        $recordtoupdate->secondaryoutcome = $fromform->secondaryoutcome;
        $recordtoupdate->studygroups = $fromform->studygroups;
        $recordtoupdate->enrollmentprocess = $fromform->enrollmentprocess;
        $recordtoupdate->methodofsampling = $fromform->methodofsampling;
        $recordtoupdate->biologicalmaterialhandling = $fromform->biologicalmaterialhandling;
        $recordtoupdate->phaseoftrial = $fromform->phaseoftrial;
        $recordtoupdate->studyperiodandduration = $fromform->studyperiodandduration;
        $recordtoupdate->briefsummary = $fromform->briefsummary;
        $recordtoupdate->groupradio = $fromform->groupradio;
        $recordtoupdate->control = $fromform->control;
        $recordtoupdate->comparator = $fromform->comparator;
        $recordtoupdate->userid = $userid;
        $recordtoupdate->studyarm = $fromform->studyarm;
        $recordtoupdate->vancouverstyleonly = $fromform->vancouverstyleonly;
        $recordtoupdate->informedconsentform = $fromform->informedconsentform;
        $recordtoupdate->patientinformationsheet = $fromform->patientinformationsheet;
        $recordtoupdate->casereportform = $fromform->casereportform;
        $recordtoupdate->waiverofconsentform = $fromform->waiverofconsentform;
        $recordtoupdate->otherquestionnaires = $fromform->otherquestionnaires;

        $recordtoupdate->otherquestionnairesone = $fromform->otherquestionnairesone;
        $recordtoupdate->otherquestionnairestwo= $fromform->otherquestionnairestwo;
        $recordtoupdate->otherquestionnairesthr= $fromform->otherquestionnairesthr;
        $recordtoupdate->otherquestionnairesfou= $fromform->otherquestionnairesfou;
        $recordtoupdate->otherquestionnairesfiv= $fromform->otherquestionnairesfiv;

        $recordtoupdate->draft = n1;
        $DB->update_record('submissions', $recordtoupdate);
        redirect($CFG->wwwroot . '/blocks/proposals/view.php', 'Succesfully Updated');
    } else {
        $recordtoinsert = new stdclass();

        $recordtoinsert->title = $fromform->title;

        $recordtoinsert->applicationtype = $fromform->applicationtype;
        $recordtoinsert->scientifictitleofthestudy = $fromform->scientifictitleofthestudy;
        $recordtoinsert->principleinvestigator = $fromform->principleinvestigator;
        $recordtoinsert->nameofthecoguide = $fromform->nameofthecoguide;
        $recordtoinsert->contactperson = $fromform->contactperson;
        $recordtoinsert->funding = $fromform->funding;
        $recordtoinsert->sponsorss = $fromform->sponsorss;
        $recordtoinsert->randomizedradio = $fromform->randomizedradio;
        $recordtoinsert->studysite = $fromform->studysite;
        $recordtoinsert->healthcondition = $fromform->healthcondition;
        $recordtoinsert->population = $fromform->population;
        $recordtoinsert->biologicalhandling = $fromform->biologicalhandling;
        $recordtoinsert->departmentname = $fromform->departmentname;
        $recordtoinsert->typeofstudy = $fromform->typeofstudy;
        $recordtoinsert->sponsor = $fromform->sponsor;
        $recordtoinsert->randomized = $fromform->randomized;
        $recordtoinsert->intervention = $fromform->intervention;
        $recordtoinsert->blindingyes = $fromform->blindingyes;
        $recordtoinsert->studyarm = $fromform->studyarm;
        $recordtoinsert->blinding = $fromform->blinding;
        $recordtoinsert->completestudydesign = $fromform->completestudydesign;
        $recordtoinsert->inclusioncriteria = $fromform->inclusioncriteria;
        $recordtoinsert->exclusioncriteria = $fromform->exclusioncriteria;
        $recordtoinsert->aimobjectives = $fromform->aimobjectives;
        $recordtoinsert->primaryobjective = $fromform->primaryobjective;
        $recordtoinsert->secondaryobjective = $fromform->secondaryobjective;
        $recordtoinsert->primaryoutcome = $fromform->primaryoutcome;
        $recordtoinsert->secondaryoutcome = $fromform->secondaryoutcome;
        $recordtoinsert->studygroups = $fromform->studygroups;
        $recordtoinsert->enrollmentprocess = $fromform->enrollmentprocess;
        $recordtoinsert->groupradio = $fromform->groupradio;
        $recordtoinsert->control = $fromform->control;
        $recordtoinsert->comparator = $fromform->comparator;
        
        $recordtoinsert->guidename = $fromform->guidename;

        $recordtoinsert->studyprocedure = $fromform->studyprocedure;
        $recordtoinsert->time = date("l jS \of F Y h:i:s A");
        $recordtoinsert->studyprocedurefile = $fromform->studyprocedurefile;
        $recordtoinsert->samplesizejustrificationfile = $fromform->samplesizejustrificationfile;
        $recordtoinsert->samplesizejustrification = $fromform->samplesizejustrification;
        $recordtoinsert->interventionother = $fromform->interventionother;
        
        $recordtoinsert->coinvestigator1 = $fromform->coinvestigator1;
        $recordtoinsert->coinvestigator2 = $fromform->coinvestigator2;
        $recordtoinsert->coinvestigator3 = $fromform->coinvestigator3;
        $recordtoinsert->coinvestigator4 = $fromform->coinvestigator4;
        $recordtoinsert->coinvestigator5 = $fromform->coinvestigator5;

        $recordtoinsert->methodofsamplingother = $fromform->methodofsamplingother;
        $recordtoinsert->startdate = $fromform->startdate;
        $recordtoinsert->enddate = $fromform->enddate;

        $recordtoinsert->methodofsampling = $fromform->methodofsampling;
        $recordtoinsert->biologicalmaterialhandling = $fromform->biologicalmaterialhandling;
        $recordtoinsert->phaseoftrial = $fromform->phaseoftrial;
        $recordtoinsert->studyperiodandduration = $fromform->studyperiodandduration;
        $recordtoinsert->briefsummary = $fromform->briefsummary;
        $recordtoinsert->vancouverstyleonly = $fromform->vancouverstyleonly;
        $recordtoinsert->informedconsentform = $fromform->informedconsentform;
        $recordtoinsert->userid = $userid;
        $recordtoinsert->patientinformationsheet = $fromform->patientinformationsheet;
        $recordtoinsert->casereportform = $fromform->casereportform;
        $recordtoinsert->waiverofconsentform = $fromform->waiverofconsentform;
        $recordtoinsert->otherquestionnaires = $fromform->otherquestionnaires;

        $recordtoinsert->otherquestionnairesone= $fromform->otherquestionnairesone;
        $recordtoinsert->otherquestionnairestwo= $fromform->otherquestionnairestwo;
        $recordtoinsert->otherquestionnairesthr= $fromform->otherquestionnairesthr;
        $recordtoinsert->otherquestionnairesfou= $fromform->otherquestionnairesfou;
        $recordtoinsert->otherquestionnairesfiv= $fromform->otherquestionnairesfiv;
        
        $DB->insert_record('submissions', $recordtoinsert);
        redirect($CFG->wwwroot . '/blocks/proposals/view.php', 'Succesfully submitted');
    } 
}
}
if ($data = $mform->get_submitted_data()) { 
    if($data->submitbutton == 'Save_to_Draft'){
        $informedconsentform = $data->informedconsentform;
        $patientinformationsheet = $data->patientinformationsheet;
        $casereportform = $data->casereportform; 
        $waiverofconsentform = $data->waiverofconsentform;
        $otherquestionnaires = $data->otherquestionnaires;

        $samplesizejustrificationfile = $data->samplesizejustrificationfile;
        $studyprocedurefile =$data->studyprocedurefile;
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
                                   array('subdirs' => 0,'maxfiles' => 5,
                                    'accepted_types' => array('.doc','.pdf','.jpg')));
    }
  
        if ($data->id) {
        if ($data->status == 0) {
            $status = '0';
        }
        if ($data->status == 3) {

            $status = '3';
        }
        $recordtoupdate = new stdClass();
        $recordtoupdate->id = $data->id;
        $recordtoupdate->title = $data->title;
        $recordtoupdate->applicationtype = $data->applicationtype;
        $recordtoupdate->scientifictitleofthestudy = $data->scientifictitleofthestudy;
        $recordtoupdate->principleinvestigator = $data->principleinvestigator;
        $recordtoupdate->nameofthecoguide = $data->nameofthecoguide;
        $recordtoupdate->contactperson = $data->contactperson;
        $recordtoupdate->funding = $data->funding;
        $recordtoupdate->sponsorss = $data->sponsorss;
        $recordtoupdate->departmentname = $data->departmentname;
        $recordtoupdate->studysite = $data->studysite;
        $recordtoupdate->healthcondition = $data->healthcondition;
        $recordtoupdate->population = $data->population;
        $recordtoupdate->typeofstudy = $data->typeofstudy;
        $recordtoupdate->sponsor = $data->sponsor;
        $recordtoupdate->randomizedradio = $data->randomizedradio;
        $recordtoupdate->biologicalhandling = $data->biologicalhandling;
        $recordtoupdate->randomized = $data->randomized;
        $recordtoupdate->intervention = $data->intervention;
        $recordtoupdate->blindingyes = $data->blindingyes;
        $recordtoupdate->blinding = $data->blinding;
        $recordtoupdate->completestudydesign = $data->completestudydesign;
        $recordtoupdate->inclusioncriteria = $data->inclusioncriteria;
        $recordtoupdate->exclusioncriteria = $data->exclusioncriteria;
        $recordtoupdate->aimobjectives = $data->aimobjectives;
        $recordtoupdate->primaryobjective = $data->primaryobjective;
        $recordtoupdate->secondaryobjective = $data->secondaryobjective;
        $recordtoupdate->primaryoutcome = $data->primaryoutcome;
        $recordtoupdate->secondaryoutcome = $data->secondaryoutcome;
        $recordtoupdate->studygroups = $data->studygroups;
        $recordtoupdate->enrollmentprocess = $data->enrollmentprocess;
        $recordtoupdate->groupradio = $data->groupradio;
        $recordtoupdate->control = $data->control;
        $recordtoupdate->comparator = $data->comparator;
        $recordtoupdate->studyarm = $data->studyarm;
        $recordtoupdate->interventionother = $data->interventionother;
        $recordtoupdate->studyprocedure = $data->studyprocedure;
        $recordtoupdate->time = date("l jS \of F Y h:i:s A");
        $recordtoupdate->studyprocedurefile = $data->studyprocedurefile;
        $recordtoupdate->samplesizejustrificationfile = $data->samplesizejustrificationfile;
        $recordtoupdate->samplesizejustrification = $data->samplesizejustrification;

        $recordtoupdate->guidename = $data->guidename;

        $recordtoupdate->methodofsampling = $data->methodofsampling;
        $recordtoupdate->biologicalmaterialhandling = $data->biologicalmaterialhandling;

        $recordtoupdate->coinvestigator1 = $data->coinvestigator1;
        $recordtoupdate->coinvestigator2 = $data->coinvestigator2;
        $recordtoupdate->coinvestigator3 = $data->coinvestigator3;
        $recordtoupdate->coinvestigator4 = $data->coinvestigator4;
        $recordtoupdate->coinvestigator5 = $data->coinvestigator5;

        $recordtoupdate->methodofsamplingother = $data->methodofsamplingother;
        $recordtoupdate->startdate = $data->startdate;
        $recordtoupdate->enddate = $data->enddate;

        $recordtoupdate->phaseoftrial = $data->phaseoftrial;
        $recordtoupdate->studyperiodandduration = $data->studyperiodandduration;
        $recordtoupdate->briefsummary = $data->briefsummary;
        $recordtoupdate->userid = $userid;
        $recordtoupdate->vancouverstyleonly = $data->vancouverstyleonly;
        $recordtoupdate->informedconsentform = $data->informedconsentform;
        $recordtoupdate->patientinformationsheet = $data->patientinformationsheet;
        $recordtoupdate->casereportform = $data->casereportform;
        $recordtoupdate->waiverofconsentform = $data->waiverofconsentform;
        $recordtoupdate->otherquestionnaires = $data->otherquestionnaires;

        $recordtoupdate->otherquestionnairesone = $data->otherquestionnairesone;
        $recordtoupdate->otherquestionnairestwo= $data->otherquestionnairestwo;
        $recordtoupdate->otherquestionnairesthr= $data->otherquestionnairesthr;
        $recordtoupdate->otherquestionnairesfou= $data->otherquestionnairesfou;
      
        $recordtoupdate->otherquestionnairesfiv= $data->otherquestionnairesfiv;
        $recordtoupdate->draft = n0; 
        $DB->update_record('submissions', $recordtoupdate);
        redirect($CFG->wwwroot . '/blocks/proposals/view.php', 'Succesfully Updated to draft');
    }else {
            $recordtoinsert = new stdclass();
        $recordtoinsert->title = $data->title;

        $recordtoinsert->applicationtype = $data->applicationtype;
        $recordtoinsert->scientifictitleofthestudy = $data->scientifictitleofthestudy;
        $recordtoinsert->principleinvestigator = $data->principleinvestigator;
        $recordtoinsert->nameofthecoguide = $data->nameofthecoguide;
        $recordtoinsert->contactperson = $data->contactperson;
        $recordtoinsert->funding = $data->funding;
        $recordtoinsert->sponsorss = $data->sponsorss;
        $recordtoinsert->randomizedradio = $data->randomizedradio;
        $recordtoinsert->studysite = $data->studysite;
        $recordtoinsert->healthcondition = $data->healthcondition;
        $recordtoinsert->population = $data->population;
        $recordtoinsert->biologicalhandling = $data->biologicalhandling;
        $recordtoinsert->departmentname = $data->departmentname;
        $recordtoinsert->typeofstudy = $data->typeofstudy;
        $recordtoinsert->sponsor = $data->sponsor;
        $recordtoinsert->randomized = $data->randomized;
        $recordtoinsert->intervention = $data->intervention;
        $recordtoinsert->blindingyes = $data->blindingyes;
        $recordtoinsert->blinding = $data->blinding;
        $recordtoinsert->studyarm = $data->studyarm;
        $recordtoinsert->completestudydesign = $data->completestudydesign;
        $recordtoinsert->inclusioncriteria = $data->inclusioncriteria;
        $recordtoinsert->exclusioncriteria = $data->exclusioncriteria;
        $recordtoinsert->aimobjectives = $data->aimobjectives;
        $recordtoinsert->primaryobjective = $data->primaryobjective;
        $recordtoinsert->secondaryobjective = $data->secondaryobjective;
        $recordtoinsert->primaryoutcome = $data->primaryoutcome;
        $recordtoinsert->secondaryoutcome = $data->secondaryoutcome;
        $recordtoinsert->studygroups = $data->studygroups;
        $recordtoinsert->enrollmentprocess = $data->enrollmentprocess;
        $recordtoinsert->interventionother = $data->interventionother;
        $recordtoinsert->studyprocedure = $data->studyprocedure;
        $recordtoinsert->time = date("l jS \of F Y h:i:s A");
        $recordtoinsert->studyprocedurefile = $data->studyprocedurefile;
        $recordtoinsert->samplesizejustrificationfile = $data->samplesizejustrificationfile;
        $recordtoinsert->samplesizejustrification = $data->samplesizejustrification;

        $recordtoinsert->guidename = $data->guidename;

        $recordtoinsert->methodofsampling = $data->methodofsampling;
        $recordtoinsert->biologicalmaterialhandling = $data->biologicalmaterialhandling;

        $recordtoinsert->coinvestigator1 = $data->coinvestigator1;
        $recordtoinsert->coinvestigator2 = $data->coinvestigator2;
        $recordtoinsert->coinvestigator3 = $data->coinvestigator3;
        $recordtoinsert->coinvestigator4 = $data->coinvestigator4;
        $recordtoinsert->coinvestigator5 = $data->coinvestigator5;

        $recordtoinsert->methodofsamplingother = $data->methodofsamplingother;
        $recordtoinsert->startdate = $data->startdate;
        $recordtoinsert->enddate = $data->enddate;

        $recordtoinsert->phaseoftrial = $data->phaseoftrial;
        $recordtoinsert->groupradio = $data->groupradio;
        $recordtoinsert->control = $data->control;
        $recordtoinsert->comparator = $data->comparator;
        $recordtoinsert->studyperiodandduration = $data->studyperiodandduration;
        $recordtoinsert->briefsummary = $data->briefsummary;
        $recordtoinsert->vancouverstyleonly = $data->vancouverstyleonly;
        $recordtoinsert->informedconsentform = $data->informedconsentform;
        $recordtoinsert->userid = $userid;
        $recordtoinsert->patientinformationsheet = $data->patientinformationsheet;
        $recordtoinsert->casereportform = $data->casereportform;
        $recordtoinsert->waiverofconsentform = $data->waiverofconsentform;
        $recordtoinsert->otherquestionnaires = $data->otherquestionnaires;

        $recordtoinsert->otherquestionnairesone = $data->otherquestionnairesone;
        $recordtoinsert->otherquestionnairestwo= $data->otherquestionnairestwo;
        $recordtoinsert->otherquestionnairesthr= $data->otherquestionnairesthr;
        $recordtoinsert->otherquestionnairesfou= $data->otherquestionnairesfou;
        $recordtoinsert->otherquestionnairesfiv= $data->otherquestionnairesfiv;
        
        $DB->insert_record('submissions', $recordtoinsert);
        redirect($CFG->wwwroot . '/blocks/proposals/view.php', 'Succesfully submitted to draft');
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
        $recordtoupdate = new stdClass();
        $recordtoupdate->id = $tofrom->id;
        $recordtoupdate->title = $tofrom->title;
        $recordtoupdate->applicationtype = $tofrom->applicationtype;
        $recordtoupdate->scientifictitleofthestudy = $tofrom->scientifictitleofthestudy;
        $recordtoupdate->principleinvestigator = $tofrom->principleinvestigator;
        $recordtoupdate->nameofthecoguide = $tofrom->nameofthecoguide;
        $recordtoupdate->contactperson = $tofrom->contactperson;
        $recordtoupdate->funding = $tofrom->funding;
        $recordtoupdate->sponsorss = $tofrom->sponsorss;
        $recordtoupdate->departmentname = $tofrom->departmentname;
        $recordtoupdate->studysite = $tofrom->studysite;
        $recordtoupdate->healthcondition = $tofrom->healthcondition;
        $recordtoupdate->population = $tofrom->population;
        $recordtoupdate->typeofstudy = $tofrom->typeofstudy;
        $recordtoupdate->sponsor = $tofrom->sponsor;
        $recordtoupdate->randomizedradio = $tofrom->randomizedradio;
        $recordtoupdate->biologicalhandling = $tofrom->biologicalhandling;
        $recordtoupdate->randomized = $tofrom->randomized;
        $recordtoupdate->intervention = $tofrom->intervention;
        $recordtoupdate->blindingyes = $tofrom->blindingyes;
        $recordtoupdate->blinding = $tofrom->blinding;
        $recordtoupdate->completestudydesign = $tofrom->completestudydesign;
        $recordtoupdate->inclusioncriteria = $tofrom->inclusioncriteria;
        $recordtoupdate->exclusioncriteria = $tofrom->exclusioncriteria;
        $recordtoupdate->aimobjectives = $tofrom->aimobjectives;
        $recordtoupdate->primaryobjective = $tofrom->primaryobjective;
        $recordtoupdate->secondaryobjective = $tofrom->secondaryobjective;
        $recordtoupdate->primaryoutcome = $tofrom->primaryoutcome;
        $recordtoupdate->secondaryoutcome = $tofrom->secondaryoutcome;
        $recordtoupdate->studygroups = $tofrom->studygroups;
        $recordtoupdate->enrollmentprocess = $tofrom->enrollmentprocess;
        $recordtoupdate->studyarm = $data->studyarm;
        $recordtoupdate->groupradio = $tofrom->groupradio;
        $recordtoupdate->control = $tofrom->control;

        $recordtoupdate->guidename = $tofrom->guidename;

        $recordtoupdate->comparator = $tofrom->comparator;
    
        $recordtoupdate->coinvestigator1 = $tofrom->coinvestigator1;
        $recordtoupdate->coinvestigator2 = $tofrom->coinvestigator2;
        $recordtoupdate->coinvestigator3 = $tofrom->coinvestigator3;
        $recordtoupdate->coinvestigator4 = $tofrom->coinvestigator4;
        $recordtoupdate->coinvestigator5 = $tofrom->coinvestigator5;
        $recordtoupdate->interventionother = $tofrom->interventionother;
        $recordtoupdate->methodofsamplingother = $tofrom->methodofsamplingother;
        $recordtoupdate->startdate = $tofrom->startdate;
        $recordtoupdate->enddate = $tofrom->enddate;
        $recordtoupdate->casereportform = $tofrom->casereportform;
        $recordtoupdate->time = date("l jS \of F Y h:i:s A");
        $recordtoupdate->studyprocedure = $tofrom->studyprocedure;

        $recordtoupdate->studyprocedurefile = $tofrom->studyprocedurefile;
        $recordtoupdate->samplesizejustrificationfile = $tofrom->samplesizejustrificationfile;
        $recordtoupdate->samplesizejustrification = $tofrom->samplesizejustrification;
        $recordtoupdate->methodofsampling = $tofrom->methodofsampling;
        $recordtoupdate->biologicalmaterialhandling = $tofrom->biologicalmaterialhandling;
        $recordtoupdate->phaseoftrial = $tofrom->phaseoftrial;
        $recordtoupdate->studyperiodandduration = $tofrom->studyperiodandduration;
        $recordtoupdate->briefsummary = $tofrom->briefsummary;
        $recordtoupdate->userid = $userid;
        $recordtoupdate->vancouverstyleonly = $tofrom->vancouverstyleonly;
        $recordtoupdate->informedconsentform = $tofrom->informedconsentform;
        $recordtoupdate->patientinformationsheet = $tofrom->patientinformationsheet;
        $recordtoupdate->patientproforma = $tofrom->patientproforma;
        $recordtoupdate->waiverofconsentform = $tofrom->waiverofconsentform;
        $recordtoupdate->otherquestionnaires = $tofrom->otherquestionnaires;


        $recordtoupdate->otherquestionnairesone = $tofrom->otherquestionnairesone;
        $recordtoupdate->otherquestionnairestwo= $tofrom->otherquestionnairestwo;
        $recordtoupdate->otherquestionnairesthr= $tofrom->otherquestionnairesthr;
        $recordtoupdate->otherquestionnairesfou= $tofrom->otherquestionnairesfou;
        $recordtoupdate->otherquestionnairesfiv= $tofrom->otherquestionnairesfiv;
        $DB->update_record('submissions', $recordtoupdate);
    }
}
        echo '<script>window.open("preview.php?id='.$tofrom->id.'","_blank")</script>';
        redirect($CFG->wwwroot . '/blocks/proposals/view.php', 'Pdf will display in the new tab ');
        // redirect($CFG->wwwroot . '/blocks/proposals/preview.php?id='.$tofrom->id, 'Redirecting to pdf');
    }

$formdata = $DB->get_record('submissions', ['id' => $formid]);
if($formdata){

$applicationdata = $DB->get_record('applicationtable',['id' =>$formdata->applicationtype]);
        $var = 'DYPU/';
        $var .= $applicationdata->code.'/';
        $date = date('Y');
        $var.=$date;
 if ($formdata->status ==  0) {
        $status = "Waiting For Approval";
        
        $refid = $var.'/O/';
        $referenceid = $refid.$formdata->id;
        $insertrefid = new stdclass();
        $insertrefid->id = $formdata->id;
        $insertrefid->referenceid = $referenceid;
        
         $DB->update_record('submissions',$insertrefid);
}

if ($formdata->status ==  3) {
        $revisecount = 1;
        $countrev = $DB->get_field('submissions','countrev',array('id'=>$formid));
        $status = "Revision";
        $refid = $var.'/R'.$countrev.'/';
        $referenceid = $refid.$formdata->id;
        $insertrefid = new stdclass();
        $insertrefid->id = $formdata->id;
        $insertrefid->revisecount = $revisecount;
        $insertrefid->referenceid = $referenceid;

         $DB->update_record('submissions',$insertrefid);
 
}
}
$refid = $DB->get_field('submissions','referenceid',array('id'=>$formid));
$templatecontext = (object)[
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
