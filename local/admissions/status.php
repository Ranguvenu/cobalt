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
use local_admissions\form\admissionstatus_form;
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/admissions/lib.php');
require_once($CFG->dirroot . '/local/admissions/classes/form/admissionstatus_form.php');
global $CFG, $USER, $PAGE, $OUTPUT, $DB;
$PAGE->set_url(new moodle_url('/local/admissions/status.php'));
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_pagelayout('secure');
$PAGE->set_title(get_string('applicationstatus','local_admissions'));
$PAGE->set_heading(get_string('applicationstatus','local_admissions'));

$form = new admissionstatus_form();
$status = '';
$approved = '';
$reject = '';
$revised = '';
$revisednext = '';

if ($form->is_cancelled()) {
    redirect($CFG->wwwroot);
} else if ($fromform = $form->get_data()) {
    $record = new stdClass();
    $record->registrationid = $fromform->registrationid;
    $record->dob = $fromform->dob;

    $lib = new lib();
    $userinfo = $lib->user_info($record);
    
    if ($userinfo->id) {
        $admissionsdetails = new \stdClass();
        $admissionsdetails->studentname = $userinfo->fullname;
        $admissionsdetails->registrationid = $record->registrationid;
        $admissionsdetails->programname = $userinfo->name;
        $status = $userinfo->status;
        $reason = $userinfo->reason;
    }
    switch ($status) {
        case 1:
            $approved = get_string('approved', 'local_admissions', $admissionsdetails);
            $registrationid = $record->registrationid;
        break;
        case 2:
            $reject = get_string('rejected', 'local_admissions', $admissionsdetails);
            $registrationid = $record->registrationid;
        break;
        case 3:
            if ($userinfo->revisecnt == 1) {
                $revised = get_string('revised', 'local_admissions', $admissionsdetails);
                $registrationid = $record->registrationid;
            }
        break;
        case 0:
            if ($userinfo->revisecnt == 1) {
                $revisednext = get_string('revisednext', 'local_admissions', $admissionsdetails);
                $registrationid = $record->registrationid;
            }
            if ($userinfo->revisecnt == 0) {
                $wronginputfirst = get_string('nodatafoundfirst', 'local_admissions', $admissionsdetails);
                $wronginputsecond = get_string('nodatafoundsecond', 'local_admissions', $admissionsdetails);
                $wronginputthird = get_string('nodatafoundthird', 'local_admissions');
                $status = [
                    'wronginputfirst' => $wronginputfirst,
                    'wronginputsecond' => $wronginputsecond,
                    'wronginputthird' => $wronginputthird,
                ];
            }
        break;
    }
    // if ($userinfo->status == 0 && $userinfo->revisecnt == 0) {
            
    // } else {
        $statusset = [
            'status' => $status,
            'approved' => $approved,
            'rejected' => $reject,
            'revised' => $revised,
            'revisednext' => $revisednext,
            'reason' => $reason,
            'id' => $registrationid,
            'admissionid' => $userinfo->admissionid,
            'programid' => $userinfo->programid,
            'configwwwroot' => $CFG->wwwroot,
        ];
    // }

}
echo $OUTPUT->header();
$form->display();
echo $OUTPUT->render_from_template('local_admissions/status', $statusset);
echo $OUTPUT->footer();
