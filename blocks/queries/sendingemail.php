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
 * Version details
 *
 * @package    block_queries
 * @author eabyas  <info@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
global $CFG, $PAGE, $DB, $USER;

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/blocks/queries/lib.php');
$PAGE->set_url('/blocks/queries/sendingemail.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_title('sending email');
$PAGE->set_pagelayout('standard');

if ($formdata = data_submitted()) {
    $toinsertrecord = new stdclass();
    $useridandtype = explode(',', $formdata->usertype);
    $toinsertrecord->userid = $useridandtype[0];
    $toinsertrecord->userrole = $useridandtype[1];
    $toinsertrecord->subject = $formdata->subject;
    $toinsertrecord->description = $formdata->description;
    $toinsertrecord->postedby = $USER->id;
    $toinsertrecord->status = 0;
    $toinsertrecord->viewed = 0;
    $toinsertrecord->timecreated = time();
    $DB->insert_record('block_queries', $toinsertrecord);
    // Send email code.
    $tosenduser = $toinsertrecord->userid;
    $touser = $DB->get_record_sql("SELECT * FROM {user} WHERE id = $tosenduser");
    // For email body.
    $emailbodyobject = new stdClass();
    $emailbodyobject->fullname = $touser->firstname;
    $emailbodyobject->link = $CFG->wwwroot.'/blocks/queries/display_queries.php?query1='.$tosenduser;
    $emailbodyobject->description = $toinsertrecord->description;
    $stuid = new stdClass();
    $studetails = queriesuser_details($USER->id);
    $stuid->fullname = $USER->username;
    $stuid->batchname = $studetails->batchname;
    $stuid->rollno = $studetails->rollnumber;
    $stuid->fullname = $USER->firstname;
    $stuid->batchname = $studetails->batchname;
    $stuid->rollno = $studetails->rollnumber;
    $sql = "SELECT u.*, u.username AS fullname, u.id
            FROM {user} u
            JOIN {block_queries} bq ON u.id = bq.userid
            WHERE u.id = bq.userid GROUP BY u.id";

    $schoolname = $DB->get_record_sql($sql);
    $stuid->schoolname = $schoolname->fullname;
    $emailbodyobject->regards = get_string('student_regards', 'block_queries', $stuid);
    $messagetext = get_string('emailtext', 'block_queries', $emailbodyobject);
    // Email text.
    $messagehtml = get_string('emailhtmlbody', 'block_queries', $emailbodyobject);
    // Email html body.
    email_to_user($touser, $USER, $toinsertrecord->subject, $messagetext, $messagehtml);

    // Sms to instuctor and student.
    $url = $CFG->wwwroot.'/blocks/queries/display_queries.php?studentid='.$USER->id;
    redirect($url);
}
