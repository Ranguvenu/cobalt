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
 * @package    local_admissions
 * @copyright  moodle
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use \local_admissions\local\lib;
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/blocks/proposals/classes/form/fundedform.php');
require_once($CFG->dirroot. '/lib/tcpdf/tcpdf.php');
require_once($CFG->libdir.'/pdflib.php');

global $DB , $USER, $CFG;
$PAGE->set_url(new moodle_url('/local/admissions/download.php'));
$PAGE->requires->css('/blocks/proposals/css/styles.css');
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_title('Download');
$id = optional_param('id' , null , PARAM_RAW);

$sql = "SELECT la.*, u.status, lp.name as programname, c.name as batchname, lc.fullname as collegename
         FROM {local_admissions} la
         JOIN {local_users} u ON la.admissionid = u.id
          JOIN {local_program} lp ON u.programid = lp.id
         JOIN {cohort} c ON u.batchid = c.id
         JOIN {local_costcenter} lc ON lp.costcenter = lc.id
        WHERE u.registrationid = '{$id}' ";
$admissiondata = $DB->get_records_sql($sql);

foreach ($admissiondata as $key => $value) {
  $admissionid = $value->admissionid;
  $collegename = $value->collegename;
  $programname = $value->programname;
  $batchname = $value->batchname;
}

$studentdata = $DB->get_record('local_users', array('id' => $admissionid));
$studentdata->dob = date('d-M-Y', $studentdata->dob);

$lib = new lib();
$uploaddocs = $lib->get_uploadeddocslist($admissionid);

$pdf = new TCPDF();

$pdf->setHeaderMargin(10);
$pdf->setHeaderFont(array('','',18));
$title = 'Application '.'"'.$programname.'"';
$pdf->setHeaderData('', 30, $title, '', '', array(120,150,14));

if (!$studentdata->firstname) {
  $studentdata->firstname = 'NA';
}
if (!$studentdata->lastname) {
  $studentdata->lastname = 'NA';
}
if (!$studentdata->email) {
  $studentdata->email = 'NA';
}
if (!$programname) {
  $programname = 'NA';
}
if (!$batchname) {
  $batchname = 'NA';
}
if (!$studentdata->status) {
  $studentdata->status = 'NA';
}
if ($studentdata->gender == 0) {
  $studentdata->gender = "Male";
} else {
  $studentdata->gender = "Female";
}
if ($studentdata->maritalstatus == 0) {
  $studentdata->maritalstatus = "Married";
} else {
  $studentdata->maritalstatus = "Unmarried";
}

$pdf->SetMargins(20,20,10,true);
$pdf->AddPage('P');
$pdf->SetFont('','B',10);

// Personal Information
$header = '<h6 style="font-size: 20px; color:#800080">'.get_string('personalinformation', 'local_admissions').'</h6>';
$pdf->Ln(9,false);
$pdf->writeHTMLCell('', '', '', '', $header, '', '', '', true, 'L');
$pdf->Ln(13,false);
$br .= ''.$pdf->Cell(20,10,'Registration No ',0,0,'L').'';
$br .= ''.$pdf->Cell(60,10,':',0,0,'C').'';
$br .= ''.$pdf->Cell(1,10,$id,0,0,'L').'';
$pdf->Ln(9,false);
if ($studentdata->status == 1) {
  $br .= ''.$pdf->Cell(20,10,'College Name ',0,0,'L').'';
  $br .= ''.$pdf->Cell(60,10,':',0,0,'C').'';
  $br .= ''.$pdf->Cell(1,10,$collegename,0,0,'L').'';  
  $pdf->Ln(9,false);
}
// $br .= ''.$pdf->Cell(20,10,'Program Name ',0,0,'L').'';
// $br .= ''.$pdf->Cell(60,10,':',0,0,'C').'';
// $br .= ''.$pdf->Cell(1,10,$programname,0,0,'L').'';
// $pdf->Ln(9,false);
$br .= ''.$pdf->Cell(20,10,'Batch Name ',0,0,'L').'';
$br .= ''.$pdf->Cell(60,10,':',0,0,'C').'';
$br .= ''.$pdf->Cell(1,10,$batchname,0,0,'L').'';
$pdf->Ln(9,false);
$br .= ''.$pdf->Cell(20,10,'Fullname ',0,0,'L').'';
$br .= ''.$pdf->Cell(60,10,':',0,0,'C').'';
$br .= ''.$pdf->Cell(1,10,$studentdata->firstname.' '.$studentdata->lastname,0,0,'L').'';
$pdf->Ln(9,false);
$br .= ''.$pdf->Cell(20,10,'Date Of Birth ',0,0,'L').'';
$br .= ''.$pdf->Cell(60,10,':',0,0,'C').'';
$br .= ''.$pdf->Cell(1,10,$studentdata->dob,0,0,'L').'';
$pdf->Ln(9,false);
$br .= ''.$pdf->Cell(20,10,'Gender ',0,0,'L').'';
$br .= ''.$pdf->Cell(60,10,':',0,0,'C').'';
$br .= ''.$pdf->Cell(1,10,$studentdata->gender,0,0,'L').'';
$pdf->Ln(9,false);
$br .= ''.$pdf->Cell(20,10,'Father Name ',0,0,'L').'';
$br .= ''.$pdf->Cell(60,10,':',0,0,'C').'';
$br .= ''.$pdf->Cell(1,10,$studentdata->fathername,0,0,'L').'';
$pdf->Ln(9,false);
$br .= ''.$pdf->Cell(20,10,'Mother Name ',0,0,'L').'';
$br .= ''.$pdf->Cell(60,10,':',0,0,'C').'';
$br .= ''.$pdf->Cell(1,10,$studentdata->mothername,0,0,'L').'';
$pdf->Ln(9,false);
$br .= ''.$pdf->Cell(20,10,'Marital Status ',0,0,'L').'';
$br .= ''.$pdf->Cell(60,10,':',0,0,'C').'';
$br .= ''.$pdf->Cell(1,10,$studentdata->maritalstatus,0,0,'L').'';
$pdf->Ln(9,false);
$br .= ''.$pdf->Cell(20,10,'Nationality ',0,0,'L').'';
$br .= ''.$pdf->Cell(60,10,':',0,0,'C').'';
$br .= ''.$pdf->Cell(1,10,$studentdata->nationality,0,0,'L').'';
$pdf->Ln(9,false);
$br .= ''.$pdf->Cell(20,10,'Religion ',0,0,'L').'';
$br .= ''.$pdf->Cell(60,10,':',0,0,'C').'';
$br .= ''.$pdf->Cell(1,10,$studentdata->religion,0,0,'L').'';
$pdf->Ln(9,false);
$br .= ''.$pdf->Cell(20,10,'Occupation ',0,0,'L').'';
$br .= ''.$pdf->Cell(60,10,':',0,0,'C').'';
$br .= ''.$pdf->Cell(1,10,$studentdata->occupation,0,0,'L').'';
$pdf->Ln(18,false);

// Contact Information
$header = '<h6 style="font-size: 20px; color:#800080">'.get_string('contactdetails', 'local_admissions').'</h6>';
$pdf->writeHTMLCell('', '', '', '', $header, '', '', '', true, 'L');
$pdf->Ln(13,false);
$br .= ''.$pdf->Cell(20,10,'Email ',0,0,'L').'';
$br .= ''.$pdf->Cell(60,10,':',0,0,'C').'';
$br .= ''.$pdf->Cell(1,10,$studentdata->email,0,0,'L').'';
$pdf->Ln(9,false);
$br .= ''.$pdf->Cell(20,10,'Mobile No ',0,0,'L').'';
$br .= ''.$pdf->Cell(60,10,':',0,0,'C').'';
$br .= ''.$pdf->Cell(1,10,$studentdata->mobile,0,0,'L').'';
$pdf->Ln(9,false);
$br .= ''.$pdf->Cell(20,10,'Alternative Mobile No ',0,0,'L').'';
$br .= ''.$pdf->Cell(60,10,':',0,0,'C').'';
$br .= ''.$pdf->Cell(1,10,$studentdata->altermobileno,0,0,'L').'';
$pdf->Ln(9,false);
$br .= ''.$pdf->Cell(20,10,'Permanent Address ',0,0,'L').'';
$br .= ''.$pdf->Cell(60,10,':',0,0,'C').'';
$br .= ''.$pdf->Cell(1,10,$studentdata->paddressline1.', '.$studentdata->paddressline2,0,0,'L').'';
$pdf->Ln(9,false);
$br .= ''.$pdf->Cell(20,10,'',0,0,'L').'';
$br .= ''.$pdf->Cell(60,10,'',0,0,'C').'';
$br .= ''.$pdf->Cell(1,10,$studentdata->pcity.', '.$studentdata->pstate.', '.$studentdata->pcountry.', '.$studentdata->ppincode,0,0,'L').'';
$pdf->Ln(9,false);
$br .= ''.$pdf->Cell(20,10,'Current Address ',0,0,'L').'';
$br .= ''.$pdf->Cell(60,10,':',0,0,'C').'';
$br .= ''.$pdf->Cell(1,10,$studentdata->addressline1.', '.$studentdata->addressline2.', '.$studentdata->city,0,0,'L').'';
$pdf->Ln(9,false);
$br .= ''.$pdf->Cell(20,10,'',0,0,'L').'';
$br .= ''.$pdf->Cell(60,10,'',0,0,'C').'';
$br .= ''.$pdf->Cell(1,10,$studentdata->pcity.', '.$studentdata->state.', '.$studentdata->country.', '.$studentdata->pincode,0,0,'L').'';

// Educational Information
$pdf->AddPage('P');
$header = '<h6 style="font-size: 20px; color:#800080">'.get_string('educationalinfo', 'local_admissions').'</h6>';
$pdf->Ln(9,false);
$pdf->writeHTMLCell('', '', '', '', $header, '', '', '', true, 'L');
$pdf->Ln(13,false);
foreach ($admissiondata as $key => $value) {
  $br .= ''.$pdf->Cell(20,10,'Course Name ',0,0,'L').'';
  $br .= ''.$pdf->Cell(60,10,':',0,0,'C').'';
  $br .= ''.$pdf->Cell(1,10,$value->coursename,0,0,'L').'';
  $pdf->Ln(9,false);
  $br .= ''.$pdf->Cell(20,10,'University/Board Name ',0,0,'L').'';
  $br .= ''.$pdf->Cell(60,10,':',0,0,'C').'';
  $br .= ''.$pdf->Cell(1,10,$value->university,0,0,'L').'';
  $pdf->Ln(9,false);
  $br .= ''.$pdf->Cell(20,10,'Year of Passing ',0,0,'L').'';
  $br .= ''.$pdf->Cell(60,10,':',0,0,'C').'';
  $br .= ''.$pdf->Cell(1,10,$value->yearofpassing,0,0,'L').'';
  $pdf->Ln(9,false);
  $br .= ''.$pdf->Cell(20,10,'Percentage ',0,0,'L').'';
  $br .= ''.$pdf->Cell(60,10,':',0,0,'C').'';
  $br .= ''.$pdf->Cell(1,10,$value->percentage,0,0,'L').'';
  $pdf->Ln(9,false);
}
$br .= ''.$pdf->Cell(20,10,'Attachments ',0,0,'L').'';
$br .= ''.$pdf->Cell(60,10,':',0,0,'C').'';
$br .= ''.$pdf->Cell(1,10,$uploaddocs[$admissionid],0,0,'L').'';
$pdf->Ln(9,false);
$pdf->writeHTML($br);
$pdf->Output($studentdata->registrationid.'.pdf', 'D');


