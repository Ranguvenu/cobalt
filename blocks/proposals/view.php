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
global $DB , $USER, $PAGE;
require_login();
$PAGE->set_url(new moodle_url('/block/proposals/view.php'));
$PAGE->set_heading(get_string('submissions', 'block_proposals'));
// $PAGE->set_title('Submissions');
// $PAGE->set_heading('Submissions');
$PAGE->requires->js('/blocks/hod/js/jquery.min.js',TRUE);

$PAGE->requires->js('/blocks/proposals/js/Data.js',TRUE); 
$PAGE->requires->js('/blocks/hod/js/jquery.dataTables.min.js',TRUE);
$PAGE->requires->js_call_amd('block_proposals/deleterecord','load',array());
$PAGE->requires->css('/blocks/proposals/css/jquery.dataTables.min.css');
// $PAGE->requires->css(new moodle_url('https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css'));
$editurl = $CFG->wwwroot.'/blocks/proposals/edit.php';
$url = $CFG->wwwroot;
$downloadurl = $CFG->wwwroot.'/blocks/proposals/downloads.php';
$formurl = $CFG->wwwroot.'/blocks/proposals/register.php';
$userid = $USER->id;

$submissionscount = $DB->count_records_sql("SELECT COUNT(DISTINCT(s.id)) FROM {submissions} as s WHERE s.userid = $userid AND s.status = 0 OR s.status =1 AND (s.approveronestatus = 0 OR s.approveronestatus=1) AND s.approvertwostatus = 0");

$approvalscount = $DB->count_records_sql("SELECT COUNT(DISTINCT(s.id))
                                            FROM {submissions} s
                                            WHERE s.status = 1
                                            AND s.approveronestatus =1 AND s.approvertwostatus=1 AND s.userid = $USER->id");

$rejectedcount = $DB->count_records_sql("SELECT COUNT(DISTINCT(s.id))
                                            FROM {submissions} s
                                            WHERE (s.status = 2
                                            OR s.approveronestatus = 2 OR s.approvertwostatus=2)
                                            AND s.userid = $USER->id");

$revisedcount = $DB->count_records_sql("SELECT COUNT(DISTINCT(s.id))
                                        FROM {submissions} as s
                                        WHERE s.status = 3 AND s.userid = $USER->id");

// $submissions = $DB->get_records('submissions' , [ status => 0 , userid => $USER->id ]);
$submissions = $DB->get_records_sql("SELECT s.* FROM {submissions} as s WHERE s.userid = $userid AND s.status = 0 OR s.status =1 AND (s.approveronestatus = 0 OR s.approveronestatus=1) AND s.approvertwostatus = 0 ORDER BY id DESC");


 $submissionsdatatable = array();
$submissionsdatatable['aheadings'] = ['Title' , 'Status' , 'Actions' ];
foreach ($submissions as $submission) {
    if ($submission->status == 0 && $submission->approveronestatus==0 && $submission->approvertwostatus==0 && $submission->draft == 'f1' || $submission->draft == 'n1' || $submission->draft == 'nres1') {
        $status = "Waiting For Approval";
    }
    else if ($submission->status == 0 && $submission->draft == 'f0') {
        $status = "Saved In Draft";
    }
    else if ($submission->status == 0 && $submission->draft == 'n0') {
        $status = "Saved In Draft";
    }
    else if ($submission->status == 0 && $submission->draft == 'nres0') {
        $status = "Saved In Draft";
    }
    else if ($submission->status == 1 AND $submission->approveronestatus==0 and $submission->approvertwostatus==0) {
        $status = "Approved by Dept HOD and waiting for approval by Approver1 & Approver2";
    }
    else if ($submission->status == 1 AND $submission->approveronestatus==1 AND $submission->approvertwostatus==0) {
        $status = "Approved by Dept HOD , Approver1 and waiting for approval by Approver2";
    }
    else if ($submission->status == 1 && $submission->approveronestatus==1 AND $submission->approvertwostatus==1 ) {
        $status = "Approved";
    }
    else if ($submission->status == 2) {
        $status = "Rejected";
    }
    else if ($submission->status == 3) {
        $status = "Under revision";
    }
   
    $tabledata = ['atitle' => $submission->title,
                  'id' => $submission->id,
                  'astatus' => $status];
    if ($submission->status == 3 || $submission->status == 0) {
        $actiondata = $OUTPUT->render_from_template('block_proposals/actions',
                        (object)['id' => $submission->id , ]);
    }
    else if($submission->status == 1 AND $submission->approveronestatus == 0 || 
            $submission->approveronestatus == 1 AND $submission->approvertwostatus == 0){

            $actiondata = $OUTPUT->render_from_template('block_proposals/actionsrevise',
                        (object)['id' => $submission->id , ]);
    }else { 
         $actiondata = 'N/A';
    }
    $tabledata['actions'] = $actiondata;
    $submissionsdatatable['arows'][] = $tabledata;
}
// $approvals = $DB->get_records('submissions' , [status => 1 , userid => $USER->id ]);
$approvals = $DB->get_records_sql("SELECT * FROM {submissions}  WHERE status = 1 AND approveronestatus =1 AND approvertwostatus=1 AND userid = $USER->id  ORDER BY id DESC");
$approvalsdatatable = array();
$approvalsdatatable['apheadings'] = ['Title', 'Status' , 'Details'];
foreach ($approvals as $approval) {
    if ($approval->status == 0) {
        $status = "Waiting For Approval";
    }
    if ($approval->status == 1) {
        $status = "Approved";
    }
    if ($approval->status == 2) {
        $status = "Rejected";
    }
    if ($approval->status == 3) {
        $status = "Under revision";
    }
    $approvaldata = ['aptitle' => $approval->title,
                    'id' => $approval->id,
                  'apstatus' => $status];
    $approvalsdatatable['aprows'][] = $approvaldata;
}
// $rejected = $DB->get_records('submissions' , [ status => 2 , userid => $USER->id  ]);
$rejected = $DB->get_records_sql("SELECT * FROM {submissions} WHERE (status = 2 OR approveronestatus =2 OR approvertwostatus=2) AND userid = $USER->id ORDER BY id DESC");

$rejecteddatatable = array();
$rejecteddatatable['rheadings'] = ['Title', 'Status' , 'Comment'];
foreach ($rejected as $reject) {
    if ($reject->status == 0) {
        $status = "Waiting For Approval";
    }
    if ($reject->status == 1 OR $reject->approveronestatus == 1 AND $reject->approvertwostatus ==1) {
        $status = "Approved";
    }
    if ($reject->status == 2 OR $reject->approveronestatus == 2 OR $reject->approvertwostatus == 2 ) {
        $status = "Rejected";
    }
    if ($reject->status == 3) {
        $status = "Under revision";
    }
    $rejecteddata = ['rtitle' => $reject->title,
                    'rcomment' => $reject->comment,
                    'rstatus' => $status];
    $rejecteddatatable['rrows'][] = $rejecteddata;
}
// $revised = $DB->get_records('submissions' , [ status => 3 , userid => $USER->id]);
$revised = $DB->get_records_sql("SELECT s.* FROM {submissions} as s WHERE s.status = 3 AND s.userid = $USER->id ORDER BY id DESC");


$reviseddatatable = array();
$reviseddatatable['reheadings'] = ['Title', 'Status' , 'Actions'];
foreach ($revised as $revise) {
    if ($revise->status == 0) {
        $status = "Waiting For Approval";
    }
    if ($revise->status == 1) {
        $status = "Approved";
    }
    if ($revise->status == 2) {
        $status = "Rejected";
    }
    if ($revise->status == 3 or $revise->status ==1 AND $revise->approveronestatus == 1 or $revise->approveronestatus=0 AND $revise->approvertwostatus=0) {
        $status = "Under revision";
    }
    $reviseddata = ['retitle' => $revise->title,
                    'recomment' => $revise->comment,
                    'id' => $revise->id,
                    'restatus' => $status];
    $actiondata = $OUTPUT->render_from_template('block_proposals/actionsrevise',
                    (object)['id' => $revise->id , ]);
    $reviseddata['actions'] = $actiondata;
    $reviseddatatable['rerows'][] = $reviseddata;
}
$templatecontext = (object)[
    'open' => $submissionscount,
    'approved' => $approvalscount,
    'rejected' => $rejectedcount,
    'revised' => $revisedcount,
    'allform_headings' => array_values($submissionsdatatable['aheadings']),
    'allform_rows' => array_values($submissionsdatatable['arows']),
    'ap_headings' => array_values($approvalsdatatable['apheadings']),
    'ap_rows' => array_values($approvalsdatatable['aprows']),
    'r_headings' => array_values($rejecteddatatable['rheadings']),
    'r_rows' => array_values($rejecteddatatable['rrows']),
    're_headings' => array_values($reviseddatatable['reheadings']),
    're_rows' => array_values($reviseddatatable['rerows']),
    'url' => $editurl,
    'back' => $url,
    'downloadurl' => $downloadurl,
    'formurl' => $formurl
];
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('block_proposals/view' , $templatecontext);
echo $OUTPUT->footer();


