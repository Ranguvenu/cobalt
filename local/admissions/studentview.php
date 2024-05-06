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
global $CFG, $USER, $PAGE, $OUTPUT,$DB;
require_login();
$PAGE->set_url(new moodle_url('/local/admissions/studentview.php'));
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);

$PAGE->set_heading(get_string('myapplication', 'local_admissions'));
$PAGE->navbar->add(get_string('index', 'local_admissions'), new moodle_url('/my/'));
$PAGE->navbar->add(get_string('home', 'local_admissions'), new moodle_url('/my'));

$PAGE->requires->js('/blocks/hod/js/jquery.min.js',TRUE);
$PAGE->requires->js('/blocks/hod/js/Data.js',TRUE);
$PAGE->requires->js('/blocks/hod/js/jquery.dataTables.min.js',TRUE);
$PAGE->requires->css('/blocks/hod/css/jquery.dataTables.min.css');
$PAGE->requires->js_call_amd('local_admissions/newadmission', 'load', array());
$PAGE->requires->js_call_amd('theme_bloom/quickactions', 'quickactionsCall');

$headings = array(
    get_string('username','local_admissions'),
    get_string('email','local_admissions'),
    get_string('programname','local_admissions'),
    get_string('viewdetails','local_admissions'),
    get_string('status','local_admissions')
);
$statusheadings = array(
    get_string('username','local_admissions'),
    get_string('email','local_admissions'),
    get_string('programname','local_admissions'),
    get_string('viewdetails','local_admissions'),
    get_string('status','local_admissions'),
    get_string('reason', 'local_admissions')
);

$programheadings = array(
    get_string('programname','local_admissions'),
    get_string('viewfiles','local_admissions'),
    get_string('status','local_admissions'),
    get_string('reason', 'local_admissions')
);

$lib = new lib();
if ($USER->id > 2) {
    $sql = "SELECT u.*, lu.*, p.name, la.admissionid
             FROM {user} u
             JOIN {local_users} lu ON u.id = lu.userid
             JOIN {local_program} p ON lu.programid = p.id
             JOIN {local_admissions} la ON lu.id = la.admissionid
             WHERE u.id = $USER->id";
    $userdata = $DB->get_record_sql($sql);
    $upload = $lib->get_uploadeddocument_src($userdata->admissionid);
    if ($userdata->status == 1) {
        $approvedinfo = $userdata;
        $approvedcount = count($userdata->id);
        $uploaddocs = $upload;
    } else if ($userdata->status == 2) {
        $rejectedinfo = $userdata;
        $rejectedcount = count($userdata->id);
        $uploaddocs = $upload;
    } else if ($userdata->status == 3) {
        $revisedinfo = $userdata;
        $revisedcount = count($userdata->id);
        $uploaddocs = $upload;
    }
    if ($userdata->revisecnt == 1) {
        $revisecnt = $userdata->revisecnt;
    }
}

$templatecontext = (object)[
    'headings' => array_values($headings),
    'statusheadings' => array_values($statusheadings),
    'approvedinfo' => $approvedinfo,
    'rejectedinfo' => $rejectedinfo,
    'revisedinfo' => $revisedinfo,
    'approvedcount' => $approvedcount,
    'rejectedcount' => $rejectedcount,
    'revisedcount' => $revisedcount,
    'userinfo' => $userdata,
    'revisecnt' => $revisecnt,
    'admissionid' => $userdata->admissionid,
    'upload' => $uploaddocs,
    'configwwwroot' => $CFG->wwwroot,
];
$loginusers = $DB->get_record_sql("SELECT DISTINCT(r.id),r.shortname FROM {role} r
                JOIN {role_assignments} ra ON ra.roleid = r.id
                JOIN {user} u ON u.id = ra.userid
                WHERE u.id = {$USER->id} AND r.shortname = 'student'");
echo $OUTPUT->header();
if (!is_siteadmin() && $loginusers) {
    echo $OUTPUT->render_from_template('local_admissions/studentview' , $templatecontext);
} else {
    throw new moodle_exception(get_string('permissiondenied', 'local_admissions'));
}
echo $OUTPUT->footer();
