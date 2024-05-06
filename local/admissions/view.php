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
use local_admissions\local\lib;
require_once(__DIR__ . '/../../config.php');
global $CFG, $USER, $PAGE, $OUTPUT, $DB;
require_login();
$PAGE->set_url(new moodle_url('/local/admissions/view.php'));
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('manage_admissions', 'local_admissions'));
$PAGE->set_heading(get_string('manage_admissions', 'local_admissions'));
$PAGE->navbar->add(get_string('home', 'local_admissions'), new moodle_url('/my'));
$PAGE->navbar->add(get_string('admissionview', 'local_admissions'));

$PAGE->requires->js('/blocks/hod/js/jquery.min.js',TRUE);
$PAGE->requires->js('/blocks/hod/js/Data.js',TRUE);
$PAGE->requires->js('/blocks/hod/js/jquery.dataTables.min.js',TRUE);
$PAGE->requires->css('/blocks/hod/css/jquery.dataTables.min.css');

$PAGE->requires->js_call_amd('theme_bloom/quickactions', 'quickactionsCall');
$PAGE->requires->js_call_amd('local_admissions/admissionstatus');
$PAGE->requires->js_call_amd('local_admissions/confirm', 'load', array());
$PAGE->requires->js_call_amd('local_admissions/rejectstatus', 'load', array());

$headings = array(
                get_string('username','local_admissions'),
                get_string('email','local_admissions'),
                get_string('programname','local_admissions'),
                get_string('viewfiles','local_admissions'),
                get_string('status','local_admissions')
            );

$programheadings = array(
                    get_string('programname','local_admissions'),
                    get_string('groupname','local_admissions'),
                    get_string('adduser','local_admissions')
                   );

$lib = new lib();

$admissionuserinfo = $lib->admission_userinfo();
// $programinfo = $lib->programinfo();
$approvedinfo = $lib->approved_userinfo();
$countadmissionuserinfo = $lib->count_admission_userinfo();
$rejectedinfo = $lib->rejected_userinfo();
$reviseinfo = $lib->revise_userinfo();
$countprograminfo = $lib->count_programinfo();
$countapproveduserinfo = $lib->count_approved_userinfo();
$countrejecteduserinfo = $lib->count_rejected_userinfo();
$countreviseuserinfo = $lib->count_revise_userinfo();

$renderer = $PAGE->get_renderer('local_admissions');
$currenttime = time();
$programidsarray = array();
$levelidsarray = array();

$progidssql = "SELECT id FROM {local_program} WHERE enddate > {$currenttime}";
$programidsarray = $DB->get_fieldset_sql($progidssql);
if ($programidsarray) {
    $programids = implode(',', $programidsarray);
    $levelidssql = "SELECT id FROM {local_program_levels} WHERE programid IN($programids)";
    $levelidsarray = $DB->get_fieldset_sql($levelidssql);
}

$programinfo = $renderer->adminprogramsdata($programidsarray, $levelidsarray);
foreach($programinfo as $pinfo){
    $pinfo['countprograminfo'];
}
$clickcount = 1;
$templatecontext = (object)[
    'headings' => array_values($headings),
    'userinfo' => array_values($admissionuserinfo),
    'programinfo' => array_values($programinfo),
    'approvedinfo' => array_values($approvedinfo),
    'rejectedinfo' => array_values($rejectedinfo),
    'reviseinfo' => array_values($reviseinfo),
    'programheadings' => $programheadings,
    'context' => $systemcontext->id,
    'countadmissionuserinfo' => array_values($countadmissionuserinfo),
    'countprograminfo' => $pinfo['countprograminfo'],
    'countapproveduserinfo' => array_values($countapproveduserinfo),
    'countrejecteduserinfo' => array_values($countrejecteduserinfo),
    'countreviseuserinfo' => array_values($countreviseuserinfo),
    'configwwwroot' => $CFG->wwwroot, 
    'clickcount' => $clickcount,
    'revcnt' =>$revisecount,
];
echo $OUTPUT->header();
if ((is_siteadmin() || has_capability('local/admissions:viewadmissions', $systemcontext))){
    echo $OUTPUT->render_from_template('local_admissions/view' , $templatecontext);
}else{
    throw new moodle_exception(get_string('permissiondenied', 'local_admissions'));
}
echo $OUTPUT->footer();
