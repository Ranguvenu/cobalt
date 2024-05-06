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
require_once($CFG->dirroot. '/lib/tcpdf/tcpdf.php');

require_once($CFG->libdir.'/pdflib.php');


global $DB , $USER;
require_login();
$PAGE->set_url(new moodle_url('/blocks/proposals/preview.php'));

$PAGE->requires->css('/blocks/proposals/css/styles.css');
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_title('Edit');
$formid = optional_param('formid' , null , PARAM_INT);
$userid = $USER->id;
$url = $CFG->wwwroot.'/blocks/proposals/view.php';
$username = $DB->get_record('user' , [ 'id' => $userid]);
$email = $username->email;
$program = $DB->get_record_sql("SELECT name FROM {local_program} as lp JOIN {local_costcenter} as lc ON lp.costcenter = lc.id");

$programname = $program->name;
$schoolname = $DB->get_field('local_costcenter','fullname',array('id'=>$username->open_costcenterid));

        $data = $DB->get_record('submissions',array('id'=>$formid));
        if ($data->applicationtype ==1) {
             $applicationdata = $DB->get_record('applicationtable',['id' =>$data->applicationtype]);
        $revise = $DB->get_field('submissions','revisecount',array('id' =>$formid));
        $item = $data->attachments;
        
            $pdf = new TCPDF();
            $pdf->setHeaderData('','','PROJECT_DATA','','',array(120,150,14));
          
             $attachmentsql = "SELECT filename FROM {files} WHERE filesize > 0 and itemid = $data->attachments" ;

            $attachment = $DB->get_field_sql($attachmentsql);
           
            if (!$attachment) {
              $attachment = 'NA';
            }
            if (!$data->rationale) {
              $data->rationale = 'NA';
            }
            if (!$data->departmentname) {
              $data->departmentname = 'NA';
            }
            if (!$data->projectdescription) {
              $data->projectdescription = 'NA';
            }
            if (!$data->noveltyinnovation) {
              $data->noveltyinnovation = 'NA';
            }
            if (!$data->strength) {
              $data->strength = 'NA';
            }
            if (!$data->departmentsupport) {
              $data->departmentsupport = 'NA';
            }
            if (!$data->financialsupport) {
              $data->financialsupport = 'NA';
            }

            $departname = $DB->get_field('department','departmentname',array('id'=>$data->departmentname));
            if(!$departname){
              $departname = 'NA';
            }

            $pdf->SetMargins(20,20,10,true);
            $pdf->AddPage();
            $pdf->SetFont('','B',10);
            $pdf->Cell(20,10,'Unique Applicatiion Number ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$data->referenceid,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Name ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$username->firstname,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Email ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$email,0,0,'L');
            $pdf->Ln(9,false);
            // $pdf->Cell(20,10,'Department ',0,0,'L');
            // $pdf->Cell(60,10,':',0,0,'C');
            // $pdf->Cell(1,10,$schoolname,0,0,'L');
            // $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Course ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$programname,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Title ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$data->title,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Applicationtype ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$applicationdata->applicationtype,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Rationale ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$data->rationale,0,0,'L');
            $pdf->Ln(9,false);
             $pdf->Cell(20,10,'Departmentname ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$departname,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Projectdescription ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$data->projectdescription,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Noveltyinnovation ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$data->noveltyinnovation,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Strength ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$data->strength,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Departmentsupport ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$data->departmentsupport,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Financialsupport ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$data->financialsupport,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Attachments ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$attachment,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Ln(60,false);
            $pdf->Cell(20,10,'',0,0,'L');
            $pdf->Ln(5,false);
            $pdf->Cell(10,10,'',0,0,'R');
            $pdf->Cell(40,10,'Signature & stamp of HOD ',0,0,'L');
            $pdf->Cell(60,10,' ',0,0,'R');
            $pdf->Cell(1,10,'Signature & stamp of Guide',0,0,'L');

            $pdf->Ln(15,false);
            $br = '<hr style="border:1px solid">';
            $pdf->writeHTML($br);
            $pdf->Output('download.pdf','D');
        }
        else
        {
          
          $startdate = date("d-m-Y", $data->startdate);
          
         $enddate = date("d-m-Y", $data->enddate);
            $appdata = $DB->get_record('applicationtable',array('id'=>$data->applicationtype));
             $revise = $DB->get_field('submissions','revisecount',array('id' =>$formid));
        $item = $data->attachments;
        $departname = $DB->get_field('department','departmentname',array('id'=>$data->departmentname));        
        $pdf = new TCPDF();
        $pdf->setHeaderData('','','PROJECT_DATA','','',array(120,150,14));
            $pdf->Cell(20,10,'Unique Applicatiion Number',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$referenceid,0,0,'L');
            $pdf->Ln(9,false);
            if (!$data->title) {
              $data->title = 'NA';
            }
            if (!$data->scientifictitleofthestudy) {
              $data->scientifictitleofthestudy = 'NA';
            }
            if (!$appdata->applicationtype) {
              $appdata->applicationtype = 'NA';
            }

            if (!$data->principleinvestigator) {
              $data->principleinvestigator = 'NA';
            }
            if (!$data->nameofthecoguide) {
              $data->nameofthecoguide = 'NA';
            }
            if ($data->biologicalhandling == 1) {
              $data->biologicalhandling = 'YES';
            }
            else{
              $data->biologicalhandling = 'NO';
            }

            if (!$data->biologicalmaterialhandling) {
              $data->biologicalmaterialhandling = 'NA';
            }
            if (!$data->contactperson) {
              $data->contactperson = 'NA';
            }
            if (!$data->studyarm) {
              $data->studyarm = 'NA';
            }
            if (!$data->departmentname) {
              $data->departmentname = 'NA';
            }
            if (!$data->guidename) {
                $data->guidename ='NA';
            }

            if (!$data->studysite) {
              $data->studysite = 'NA';
            }
            if (!$data->healthcondition) {
              $data->healthcondition = 'NA';
            }
            if (!$data->population) {
              $data->population = 'NA';
            }
            if (!$data->typeofstudy) {
              $data->typeofstudy = 'NA';
            }

             if ($data->population == 1) {
              $data->population = 'YES';
            }
            else {
              $data->population = 'NO';
            }
            if ($data->groupradio == 1) {
              $data->groupradio = 'YES';
            }
            else{
              $data->groupradio = 'NO';
            }
            if ($data->control == 1) {
              $data->control = 'YES';
            }else{
              $data->control = 'NO';
            }
            if (!$data->sponsorss) {
              $data->sponsorss = 'NA';
            }
            if ($data->sponsorss == 0) {
                $data->sponsorss ='NO';
            }
            else{
                $data->sponsorss ='YES';
            }
            if (!$data->intervention) {
              $data->intervention = 'NA';
            }
            if (!$data->blindingyes) {
              $data->blindingyes = 'NA';
            }
            if (!$data->completestudydesign) {
              $data->completestudydesign = 'NA';
            }
            if (!$data->inclusioncriteria) {
              $data->inclusioncriteria = 'NA';
            }
            if (!$data->exclusioncriteria) {
              $data->exclusioncriteria = 'NA';
            }
            if (!$data->aimobjectives) {
              $data->aimobjectives = 'NA';
            }
            if (!$data->primaryobjective) {
              $data->primaryobjective = 'NA';
            }
            if (!$data->secondaryobjective) {
              $data->secondaryobjective = 'NA';
            }
            if (!$data->primaryoutcome) {
              $data->primaryoutcome = 'NA';
            }
            if (!$data->secondaryoutcome) {
              $data->secondaryoutcome = 'NA';
            }
            if (!$data->studygroups) {
              $data->studygroups = 'NA';
            }
            if (!$data->enrollmentprocess) {
              $data->enrollmentprocess = 'NA';
            }
            if (!$data->studyprocedure) {
              $data->studyprocedure = 'NA';
            }
            // if (!$data->samplesizejustrification) {
            //   $data->samplesizejustrification = 'NA';
            // }
            if (!$data->methodofsampling) {
              $data->methodofsampling = 'NA';
            }
            if (!$data->phaseoftrial) {
              $data->phaseoftrial = 'NA';
            }
        
            if (!$data->briefsummary) {
              $data->briefsummary = 'NA';
            }
            if (!$data->vancouverstyleonly) {
              $data->vancouverstyleonly = 'NA';
            }

            
            if (!$data->comparator) {
              $data->comparator = 'NA';
            }
            if (!$data->coinvestigator1) {
              $data->coinvestigator1 = 'NA';
            }
            if (!$data->coinvestigator2) {
              $data->coinvestigator2 = 'NA';
            }
            if (!$data->coinvestigator3) {
              $data->coinvestigator3 = 'NA';
            }
            if (!$data->coinvestigator4) {
              $data->coinvestigator4 = 'NA';
            }
            if (!$data->coinvestigator5) {
              $data->coinvestigator5 = 'NA';
            }
           
       

        $studyprocedurefilesql = "SELECT filename FROM {files} WHERE filesize > 0 and itemid = $data->studyprocedurefile" ;

            $studyprocedurefile = $DB->get_field_sql($studyprocedurefilesql);


            
            $samplesizejustrificationfilesql = "SELECT filename FROM {files} WHERE filesize > 0 and itemid = $data->samplesizejustrificationfile" ;

            $samplesizejustrificationfile = $DB->get_field_sql($samplesizejustrificationfilesql);





            $informedsql = "SELECT filename FROM {files} WHERE filesize > 0 and itemid = $data->informedconsentform" ;

            $informed = $DB->get_field_sql($informedsql);





        $patientsql = "SELECT filename FROM {files} WHERE filesize > 0 and itemid = $data->patientinformationsheet" ;

            $patient = $DB->get_field_sql($patientsql);




        $proformasql = "SELECT filename FROM {files} WHERE filesize > 0 and itemid = $data->casereportform" ;

            $proforma = $DB->get_field_sql($proformasql);






$waiversql = "SELECT filename FROM {files} WHERE filesize > 0 and itemid = $data->waiverofconsentform" ;

            $waiver = $DB->get_field_sql($waiversql);






        $othersql = "SELECT filename FROM {files} WHERE filesize > 0 and itemid = $data->otherquestionnaires" ;

            $other = $DB->get_field_sql($othersql);



    $other1sql = "SELECT filename FROM {files} WHERE filesize > 0 and itemid = $data->otherquestionnairesone" ;

            $other1 = $DB->get_field_sql($other1sql);
        
        $other2sql = "SELECT filename FROM {files} WHERE filesize > 0 and itemid = $data->otherquestionnairesone" ;

            $other2 = $DB->get_field_sql($other2sql);


    $other3sql = "SELECT filename FROM {files} WHERE filesize > 0 and itemid = $data->otherquestionnairesthr" ;

            $other3 = $DB->get_field_sql($other3sql);




            $other4sql = "SELECT filename FROM {files} WHERE filesize > 0 and itemid = $data->otherquestionnairesfou" ;

            $other4 = $DB->get_field_sql($other4sql);



        $other5sql = "SELECT filename FROM {files} WHERE filesize > 0 and itemid = $data->otherquestionnairesfiv" ;

            $other5 = $DB->get_field_sql($other5sql);



         if (!$informed) {
              $informed = 'NA';
            }
            if (!$patient) {
              $patient = 'NA';
            }
            if (!$other) {
              $other = 'NA';
            }

            if (!$other1) {
              $other1 = 'NA';
            }
            if (!$other2) {
              $other2 = 'NA';
            }
            if (!$other3) {
              $other3 = 'NA';
            }
            if (!$other4) {
              $other4 = 'NA';
            }
            if (!$other5) {
              $other5 = 'NA';
            }



            if (!$proforma) {
              $proforma = 'NA';
            }
            if (!$waiver) {
              $waiver = 'NA';
            }
            if(!$departname){
              $departname = 'NA';
            }
            $pdf->AddPage();
            $pdf->SetLeftMargin(18.5);
            $pdf->SetRightMargin(12.5);
            $pdf->SetTopMargin(18.5);
            $pdf->SetFont('','B',10);
            $pdf->Cell(20,10,'Unique Applicatiion Number ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$data->referenceid,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Name ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$username->firstname,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Email ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$email,0,0,'L');
            $pdf->Ln(9,false);
            // $pdf->Cell(20,10,'Department ',0,0,'L');
            // $pdf->Cell(60,10,':',0,0,'C');
            // $pdf->Cell(1,10,$schoolname,0,0,'L');
            // $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Course ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$programname,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Title ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$data->title,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Applicationtype ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$appdata->applicationtype,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Scientifictitleofthestudy ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$data->scientifictitleofthestudy,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Principleinvestigator ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
             $pdf->Cell(1,10,$data->principleinvestigator,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Guide Name ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$data->guidename,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Co-Guide Name ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$data->nameofthecoguide,0,0,'L');
            $pdf->Ln(9,false);

            
            $pdf->Cell(20,10,'Contactperson ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$data->contactperson,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Studyarm ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$data->studyarm,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Departmentname ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$departname,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Studysite ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$data->studysite,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Healthcondition ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$data->healthcondition,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Population ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$data->population,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Typeofstudy ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$data->typeofstudy,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Sponsor ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$data->sponsorss,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Intervention ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$data->intervention,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Blindingyes ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$data->blindingyes,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Biologicalhandling ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$data->biologicalhandling,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Biologicalmaterialhandling ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$data->biologicalmaterialhandling,0,0,'L');
            $pdf->Ln(9,false);

            $pdf->Cell(20,10,'Completestudydesign ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$data->completestudydesign,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Inclusioncriteria ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$data->inclusioncriteria,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Exclusioncriteria ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$data->exclusioncriteria,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Aimobjectives ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$data->aimobjectives,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Primaryobjective ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$data->primaryobjective,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Groupradio ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$data->groupradio,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Control ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$data->control,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Comparator ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$data->comparator,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Secondaryobjective ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$data->secondaryobjective,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Primaryoutcome ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$data->primaryoutcome,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Secondaryoutcome ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$data->secondaryoutcome,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Co-Investigator1 ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$data->coinvestigator1,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Co-Investigator2 ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$data->coinvestigator2,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Co-Investigator3 ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$data->coinvestigator3,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Co-Investigator4 ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$data->coinvestigator4,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Co-Investigator5 ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$data->coinvestigator5,0,0,'L');
            $pdf->Ln(9,false);
            

            $pdf->Cell(20,10,'Studygroups ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$data->studygroups,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Enrollmentprocess ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$data->enrollmentprocess,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Studyperiodstartdate ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$startdate,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Studyperiodenddate ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$enddate,0,0,'L');
            $pdf->Ln(9,false);
            // $pdf->Cell(20,10,'Samplesizejustrification ',0,0,'L');
            // $pdf->Cell(60,10,':',0,0,'C');
            // $pdf->Cell(1,10,$data->samplesizejustrification,0,0,'L');
            // $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Methodofsampling ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$data->methodofsampling,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Phaseoftrial ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$data->phaseoftrial,0,0,'L');
            $pdf->Ln(9,false);
            // $pdf->Cell(20,10,'Studyperiodandduration ',0,0,'L');
            // $pdf->Cell(60,10,':',0,0,'C');
            // $pdf->Cell(1,10,$data->studyperiodandduration,0,0,'L');
            // $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Briefsummary ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$data->briefsummary,0,0,'L');
            $pdf->Ln(9,false);   
            $pdf->Cell(20,10,'Vancouverstyleonly ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$data->vancouverstyleonly,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Informedconsentform ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$informed,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Participantinformationsheet ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$patient,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Casereportform ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$proforma,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Waiverofconsentform ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$waiver,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Otherquestionnaires ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$other,0,0,'L');
            $pdf->Ln(9,false);

            $pdf->Cell(20,10,'Otherquestionnaires ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$other1,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Otherquestionnaires ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$other2,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Otherquestionnaires ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$other3,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Otherquestionnaires ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$other4,0,0,'L');
            $pdf->Ln(9,false);
            $pdf->Cell(20,10,'Otherquestionnaires ',0,0,'L');
            $pdf->Cell(60,10,':',0,0,'C');
            $pdf->Cell(1,10,$other5,0,0,'L');
            $pdf->Ln(9,false);

            $pdf->Cell(20,10,'',0,0,'L');
            $pdf->Ln(35,false);
            $pdf->Cell(20,10,' ',0,0,'R');
            $pdf->Cell(10,10,'Signature & stamp of HOD ',0,0,'L');
            $pdf->Cell(80,10,' ',0,0,'R');
            $pdf->Cell(1,10,'Signature & stamp of Guide',0,0,'L');
            $pdf->Ln(10,false);
            $br = '<hr style="border:1px solid">';
            $pdf->writeHTML($br);
            $pdf->Output('download.pdf','I'); 
        }

