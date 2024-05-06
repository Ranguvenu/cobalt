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
global $CFG, $USER, $PAGE, $OUTPUT,$DB;
$PAGE->set_url(new moodle_url('/my/blocks/hod/view.php#revised'));
$PAGE->set_context(\context_system::instance());
// $PAGE->set_title('Proposals');
// $PAGE->set_heading('Proposals');
require_login();
$PAGE->requires->js('/blocks/hod/js/jquery.min.js',TRUE);
$PAGE->requires->js('/blocks/hod/js/Data.js',TRUE);
$PAGE->requires->js('/blocks/hod/js/jquery.dataTables.min.js',TRUE);
$PAGE->requires->css('/blocks/hod/css/jquery.dataTables.min.css');
echo $OUTPUT->header();
$url = $CFG->wwwroot;
$downloadurl = $CFG->wwwroot.'/blocks/hod/download.php';

$submissionscount = $DB->count_records_sql("SELECT COUNT(s.id) FROM {submissions} AS s JOIN {user} AS u ON s.departmentname = u.deptid
     WHERE u.id = $USER->id AND status = 0 AND (draft = 'f1' OR draft ='n1')");

$approvalscount = $DB->count_records_sql("SELECT COUNT(s.id) FROM {submissions} AS s JOIN {user} AS u ON 
    s.departmentname = u.deptid WHERE u.id = $USER->id AND status = 1 ");


$rejectedcount = $DB->count_records_sql("SELECT COUNT(s.id) FROM {submissions} AS s JOIN {user} AS u ON s.departmentname = u.deptid WHERE u.id = $USER->id AND status = 2");

$revisedcount = $DB->count_records_sql("SELECT COUNT(s.id) FROM {submissions} AS s JOIN {user} AS u ON s.departmentname = u.deptid WHERE u.id = $USER->id AND status = 3 ");

$submissions = $DB->get_records_sql("SELECT s.* FROM {submissions} AS s JOIN {user} AS u ON s.departmentname = u.deptid WHERE u.id = $USER->id AND status = 0 AND (draft = 'f1' OR draft ='n1') ORDER BY id DESC");
$submissionsdatatable = array();
$submissionsdatatable['aheadings'] = ['Name' , 'Title',  'Status' , 'Details'];
foreach ($submissions as $submission) {
    $usernname = $DB->get_record('user' , [ id => $submission->userid] , $fields = 'firstname');
    $tabledata = ['atitle' => $submission->title,
                    'id' => $submission->id,
                    'aname' => $usernname->firstname,
                  'astatus' => "Waiting For Approval"];
    $actiondata = $OUTPUT->render_from_template('block_hod/actions',
                    (object)['id' => $submission->id]);
    $tabledata['actions'] = $actiondata;
    $submissionsdatatable['arows'][] = $tabledata;
}

// $approvals = $DB->get_records('submissions' , [ status => 1]);

$approvals = $DB->get_records_sql("SELECT s.* FROM {submissions} AS s JOIN {user} AS u ON 
   s.departmentname = u.deptid  WHERE u.id = $USER->id AND status = 1 ORDER BY id DESC");


// $approvals = $DB->get_records_sql("SELECT s.* FROM {submissions} AS s JOIN {user} AS u ON  FIND_IN_SET(s.applicationtype, u.applicationid) WHERE u.id = $USER->id AND status = 1 ");


$approvalsdatatable = array();
$approvalsdatatable['apheadings'] = [ 'Name' , 'Title', 'Status' , 'Details' ];
foreach ($approvals as $approval) {
    $usernname = $DB->get_record('user' , [ id => $approval->userid] , $fields = 'firstname');
    $approvaldata = ['aptitle' => $approval->title,
                    'id' => $approval->id,
                    'apname' => $usernname->firstname,
                    'apstatus' => "Approved"];
    $actiondata = $OUTPUT->render_from_template('block_hod/actions',
                  (object)['id' => $approval->id]);
    $approvaldata['actions'] = $actiondata;
    $approvalsdatatable['aprows'][] = $approvaldata;
}


$rejected = $DB->get_records_sql("SELECT s.* FROM {submissions} AS s JOIN {user} AS u ON s.departmentname = u.deptid WHERE u.id = $USER->id AND status = 2 ORDER BY id DESC ");

$rejecteddatatable = array();
$rejecteddatatable['rheadings'] = ['Name' , 'Title', 'Status' , 'Details'];
foreach ($rejected as $reject) {
    $usernname = $DB->get_record('user' , [ id => $reject->userid] , $fields = 'firstname');
    $rejecteddata = ['rtitle' => $reject->title,
                    'id' => $reject->id,
                    'rname' => $usernname->firstname,
                    'rstatus' => "Rejected"];
    $actiondata = $OUTPUT->render_from_template('block_hod/actions',
                    (object)['id' => $reject->id]);
    $rejecteddata['actions'] = $actiondata;
    $rejecteddatatable['rrows'][] = $rejecteddata;
}
// $revised = $DB->get_records('submissions' , [ status => 3] );

$revised = $DB->get_records_sql("SELECT s.* FROM {submissions} AS s JOIN {user} AS u ON s.departmentname = u.deptid WHERE u.id = $USER->id AND status = 3 ORDER BY id DESC");

$reviseddatatable = array();
$reviseddatatable['reheadings'] = ['Name' , 'Title', 'Status' , 'Details'];
foreach ($revised as $revise) {
    $usernname = $DB->get_record('user' , [ id => $revise->userid] , $fields = 'firstname');
    $reviseddata = ['retitle' => $revise->title,
                    'rename' => $usernname->firstname,
                    'id' => $revise->id,
                  'restatus' => "Under revision"];
    $actiondata = $OUTPUT->render_from_template('block_hod/actions',
                  (object)['id' => $revise->id]);
    $reviseddata['actions'] = $actiondata;
    
    $reviseddatatable['rerows'][] = $reviseddata;
}

$details = $DB->get_record('user',array('id'=>$USER->id));

if($details->levelofapprove == 0){

$submissionscount = $DB->count_records_sql("SELECT COUNT(s.id) FROM {submissions} AS s JOIN {user} AS u ON s.departmentname = u.deptid
     WHERE u.id = $USER->id AND status = 0 AND (draft = 'f1' OR draft ='n1' OR draft ='nres1')");

$approvalscount = $DB->count_records_sql("SELECT COUNT(s.id) FROM {submissions} AS s JOIN {user} AS u ON 
    s.departmentname = u.deptid WHERE u.id = $USER->id AND status = 1 ");


$rejectedcount = $DB->count_records_sql("SELECT COUNT(s.id) FROM {submissions} AS s JOIN {user} AS u ON s.departmentname = u.deptid WHERE u.id = $USER->id AND status = 2");

$revisedcount = $DB->count_records_sql("SELECT COUNT(s.id) FROM {submissions} AS s JOIN {user} AS u ON s.departmentname = u.deptid WHERE u.id = $USER->id AND status = 3 ");

$submissions = $DB->get_records_sql("SELECT s.* FROM {submissions} AS s JOIN {user} AS u ON s.departmentname = u.deptid WHERE u.id = $USER->id AND status = 0 AND (draft = 'f1' OR draft ='n1' OR draft = 'nres1') ORDER BY id DESC");

$submissionsdatatable = array();
$submissionsdatatable['aheadings'] = ['Name' , 'Title',  'Status' , 'Details'];
foreach ($submissions as $submission) {
    $usernname = $DB->get_record('user' , [ id => $submission->userid] , $fields = 'firstname');
    $tabledata = ['atitle' => $submission->title,
                    'id' => $submission->id,
                    'aname' => $usernname->firstname,
                  'astatus' => "Waiting For Approval"];
    $actiondata = $OUTPUT->render_from_template('block_hod/actions',
                    (object)['id' => $submission->id]);
    $tabledata['actions'] = $actiondata;
    $submissionsdatatable['arows'][] = $tabledata;
}

// $approvals = $DB->get_records('submissions' , [ status => 1]);

$approvals = $DB->get_records_sql("SELECT s.* FROM {submissions} AS s JOIN {user} AS u ON 
   s.departmentname = u.deptid  WHERE u.id = $USER->id AND s.status = 1 ORDER BY id DESC");


// $approvals = $DB->get_records_sql("SELECT s.* FROM {submissions} AS s JOIN {user} AS u ON  FIND_IN_SET(s.applicationtype, u.applicationid) WHERE u.id = $USER->id AND status = 1 ");

$approvalsdatatable = array();
$approvalsdatatable['apheadings'] = [ 'Name' , 'Title', 'Status' , 'Details' ];
foreach ($approvals as $approval) {
    $usernname = $DB->get_record('user' , [ id => $approval->userid] , $fields = 'firstname');
    $approvaldata = ['aptitle' => $approval->title,
                    'id' => $approval->id,
                    'apname' => $usernname->firstname,
                    'apstatus' => "Approved"];
    $actiondata = $OUTPUT->render_from_template('block_hod/actions',
                  (object)['id' => $approval->id]);
    $approvaldata['actions'] = $actiondata;
    $approvalsdatatable['aprows'][] = $approvaldata;
} 
$rejected = $DB->get_records_sql("SELECT s.* FROM {submissions} AS s JOIN {user} AS u ON s.departmentname = u.deptid WHERE u.id = $USER->id AND status = 2 ORDER BY id DESC ");

$rejecteddatatable = array();
$rejecteddatatable['rheadings'] = ['Name' , 'Title', 'Status' , 'Details'];
foreach ($rejected as $reject) {
    $usernname = $DB->get_record('user' , [ id => $reject->userid] , $fields = 'firstname');
    $rejecteddata = ['rtitle' => $reject->title,
                    'id' => $reject->id,
                    'rname' => $usernname->firstname,
                    'rstatus' => "Rejected"];
    $actiondata = $OUTPUT->render_from_template('block_hod/actions',
                    (object)['id' => $reject->id]);
    $rejecteddata['actions'] = $actiondata;
    $rejecteddatatable['rrows'][] = $rejecteddata;
}
// $revised = $DB->get_records('submissions' , [ status => 3] );

$revised = $DB->get_records_sql("SELECT s.* FROM {submissions} AS s JOIN {user} AS u ON s.departmentname = u.deptid WHERE u.id = $USER->id AND status = 3 ORDER BY id DESC");

$reviseddatatable = array();
$reviseddatatable['reheadings'] = ['Name' , 'Title', 'Status' , 'Details'];
foreach ($revised as $revise) {
    $usernname = $DB->get_record('user' , [ id => $revise->userid] , $fields = 'firstname');
    $reviseddata = ['retitle' => $revise->title,
                    'rename' => $usernname->firstname,
                    'id' => $revise->id,
                  'restatus' => "Under revision"];
    $actiondata = $OUTPUT->render_from_template('block_hod/actions',
                  (object)['id' => $revise->id]);
    $reviseddata['actions'] = $actiondata;
    
    $reviseddatatable['rerows'][] = $reviseddata;
}
    if(!is_siteadmin()){
        $download = true;
    }
    else{
        $download =false;
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
        'url' => $url,
        'downloadurl' => $downloadurl,
        'download' =>$download,
    ];
    echo $OUTPUT->render_from_template('block_hod/view' , $templatecontext);
}
//approver1
else if($details->levelofapprove == 1){

$submissionscount = $DB->count_records_sql("SELECT COUNT(id) FROM {submissions}  WHERE status = 1 AND approveronestatus = 0");

$approvalscount = $DB->count_records_sql("SELECT COUNT(id) FROM {submissions}  WHERE status = 1 AND approveronestatus = 1");


$rejectedcount = $DB->count_records_sql("SELECT COUNT(id) FROM {submissions}  WHERE status = 1 AND approveronestatus = 2");

$submissions = $DB->get_records_sql("SELECT * FROM {submissions} WHERE status = 1 AND approveronestatus = 0 ORDER BY id DESC");

$submissionsdatatable = array();
$submissionsdatatable['aheadings'] = ['Name' , 'Title',  'Status' , 'Details'];
foreach ($submissions as $submission) {
    $usernname = $DB->get_record('user' , [ id => $submission->userid] , $fields = 'firstname');
    $tabledata = ['atitle' => $submission->title,
                    'id' => $submission->id,
                    'aname' => $usernname->firstname,
                  'astatus' => "Waiting For Approval"];
    $actiondata = $OUTPUT->render_from_template('block_hod/actions',
                    (object)['id' => $submission->id]);
    $tabledata['actions'] = $actiondata;
    $submissionsdatatable['arows'][] = $tabledata;
}

$approvals = $DB->get_records_sql("SELECT * FROM {submissions} WHERE status = 1 AND approveronestatus = 1 ORDER BY id DESC");

$approvalsdatatable = array();
$approvalsdatatable['apheadings'] = [ 'Name' , 'Title', 'Status' , 'Details' ];
foreach ($approvals as $approval) {
    $usernname = $DB->get_record('user' , [ id => $approval->userid] , $fields = 'firstname');
    $approvaldata = ['aptitle' => $approval->title,
                    'id' => $approval->id,
                    'apname' => $usernname->firstname,
                    'apstatus' => "Approved"];
    $actiondata = $OUTPUT->render_from_template('block_hod/actions',
                  (object)['id' => $approval->id]);
    $approvaldata['actions'] = $actiondata;
    $approvalsdatatable['aprows'][] = $approvaldata;
}

$rejected = $DB->get_records_sql("SELECT * FROM {submissions} WHERE status = 1 AND approveronestatus = 2 ORDER BY id DESC");

$rejecteddatatable = array();
$rejecteddatatable['rheadings'] = ['Name' , 'Title', 'Status' , 'Details'];
foreach ($rejected as $reject) {
    $usernname = $DB->get_record('user' , [ id => $reject->userid] , $fields = 'firstname');
    $rejecteddata = ['rtitle' => $reject->title,
                    'id' => $reject->id,
                    'rname' => $usernname->firstname,
                    'rstatus' => "Rejected"];
    $actiondata = $OUTPUT->render_from_template('block_hod/actions',
                    (object)['id' => $reject->id]);
    $rejecteddata['actions'] = $actiondata;
    $rejecteddatatable['rrows'][] = $rejecteddata;
}
    $templatecontext = (object)[
        'open' => $submissionscount,
        'approved' => $approvalscount,
        'rejected' => $rejectedcount,
        // 'revised' => $revisedcount,
        'allform_headings' => array_values($submissionsdatatable['aheadings']),
        'allform_rows' => array_values($submissionsdatatable['arows']),
        'ap_headings' => array_values($approvalsdatatable['apheadings']),
        'ap_rows' => array_values($approvalsdatatable['aprows']),
        'r_headings' => array_values($rejecteddatatable['rheadings']),
        'r_rows' => array_values($rejecteddatatable['rrows']),
        // 're_headings' => array_values($reviseddatatable['reheadings']),
        // 're_rows' => array_values($reviseddatatable['rerows']),
        'url' => $url,
        'downloadurl' => $downloadurl,
    ];
    echo $OUTPUT->render_from_template('block_hod/approverone' , $templatecontext);
}


//approver2
else if($details->levelofapprove == 2){

// $approvals = $DB->get_records_sql("SELECT s.* FROM {submissions} AS s JOIN {user} AS u ON  FIND_IN_SET(s.applicationtype, u.applicationid) WHERE u.id = $USER->id AND status = 1 ");

// $submissionscount = $DB->count_records_sql("SELECT COUNT(s.id) FROM {submissions} AS s JOIN {user} AS u ON s.applicationtype=u.applicationid WHERE status = 1 AND approveronestatus = 1 AND approvertwostatus = 0");

$submissionscount = $DB->count_records_sql("SELECT COUNT(id) FROM {submissions}  WHERE status = 1 AND approveronestatus = 1 AND approvertwostatus =0");
 
$approvalscount = $DB->count_records_sql("SELECT COUNT(id) FROM {submissions}  WHERE status = 1 AND approveronestatus = 1 AND approvertwostatus =1");


// $approvals = $DB->count_records_sql("SELECT COUNT(s.id) FROM {submissions} AS s WHERE s.status = 1 AND s.approvertwostatus = 1");


$rejectedcount = $DB->count_records_sql("SELECT COUNT(id) FROM {submissions}  WHERE status = 1 AND approvertwostatus = 2");

$submissions = $DB->get_records_sql("SELECT * FROM {submissions} WHERE status = 1 AND approveronestatus = 1 AND approvertwostatus = 0 ORDER BY id DESC");

// $approvalscount = $DB->count_records_sql("SELECT COUNT(s.id) FROM {submissions} AS s JOIN {user} AS u ON s.applicationtype=u.applicationid WHERE status = 1 AND approvertwostatus = 1");

// $rejectedcount = $DB->count_records_sql("SELECT COUNT(s.id) FROM {submissions} AS s JOIN {user} AS u ON s.applicationtype=u.applicationid  WHERE status = 1 AND approvertwostatus = 2");

// $submissions = $DB->get_records_sql("SELECT s.* FROM {submissions} AS s JOIN {user} AS u ON s.applicationtype=u.applicationid WHERE status = 1 AND approveronestatus = 1 AND approvertwostatus = 0 ORDER BY id DESC");


$submissions = $DB->get_records_sql("SELECT * FROM {submissions} WHERE status = 1 AND approveronestatus = 1 AND approvertwostatus = 0 ORDER BY id DESC");

$submissionsdatatable = array();
$submissionsdatatable['aheadings'] = ['Name' , 'Title',  'Status' , 'Details'];
foreach ($submissions as $submission) {
    $usernname = $DB->get_record('user' , [ id => $submission->userid] , $fields = 'firstname');
    $tabledata = ['atitle' => $submission->title,
                    'id' => $submission->id,
                    'aname' => $usernname->firstname,
                  'astatus' => "Waiting For Approval"];
    $actiondata = $OUTPUT->render_from_template('block_hod/actions',
                    (object)['id' => $submission->id]);
    $tabledata['actions'] = $actiondata;
    $submissionsdatatable['arows'][] = $tabledata;
}


$approvals = $DB->get_records_sql("SELECT * FROM {submissions} WHERE status = 1 AND approveronestatus = 1 AND approvertwostatus = 1  ORDER BY id DESC");

// $approvals = $DB->get_records_sql("SELECT s.* FROM {submissions} AS s JOIN {user} AS u ON s.applicationtype=u.applicationid WHERE status = 1 AND approvertwostatus = 1 ORDER BY id DESC");

$approvalsdatatable = array();
$approvalsdatatable['apheadings'] = [ 'Name' , 'Title', 'Status' , 'Details' ];
foreach ($approvals as $approval) {
    $usernname = $DB->get_record('user' , [ id => $approval->userid] , $fields = 'firstname');
    $approvaldata = ['aptitle' => $approval->title,
                    'id' => $approval->id,
                    'apname' => $usernname->firstname,
                    'apstatus' => "Approved"];
    $actiondata = $OUTPUT->render_from_template('block_hod/actions',
                  (object)['id' => $approval->id]);
    $approvaldata['actions'] = $actiondata;
    $approvalsdatatable['aprows'][] = $approvaldata;
}
$rejected = $DB->get_records_sql("SELECT * FROM {submissions} WHERE status = 1 AND approvertwostatus = 2 ORDER BY id DESC");

// $rejected = $DB->get_records_sql("SELECT s.* FROM {submissions} AS s JOIN {user} AS u ON s.applicationtype=u.applicationid WHERE status = 1 AND approvertwostatus = 2 ORDER BY id DESC");

$rejecteddatatable = array();
$rejecteddatatable['rheadings'] = ['Name' , 'Title', 'Status' , 'Details'];
foreach ($rejected as $reject) {
    $usernname = $DB->get_record('user' , [ id => $reject->userid] , $fields = 'firstname');
    $rejecteddata = ['rtitle' => $reject->title,
                    'id' => $reject->id,
                    'rname' => $usernname->firstname,
                    'rstatus' => "Rejected"];
    $actiondata = $OUTPUT->render_from_template('block_hod/actions',
                    (object)['id' => $reject->id]);
    $rejecteddata['actions'] = $actiondata;
    $rejecteddatatable['rrows'][] = $rejecteddata;
}
    $templatecontext = (object)[
        'open' => $submissionscount,
        'approved' => $approvalscount,
        'rejected' => $rejectedcount,
        // 'revised' => $revisedcount,
        'allform_headings' => array_values($submissionsdatatable['aheadings']),
        'allform_rows' => array_values($submissionsdatatable['arows']),
        'ap_headings' => array_values($approvalsdatatable['apheadings']),
        'ap_rows' => array_values($approvalsdatatable['aprows']),
        'r_headings' => array_values($rejecteddatatable['rheadings']),
        'r_rows' => array_values($rejecteddatatable['rrows']),
        // 're_headings' => array_values($reviseddatatable['reheadings']),
        // 're_rows' => array_values($reviseddatatable['rerows']),
        'url' => $url,
        'downloadurl' => $downloadurl,
    ];
    echo $OUTPUT->render_from_template('block_hod/approvertwo' , $templatecontext);
}


echo $OUTPUT->footer();
