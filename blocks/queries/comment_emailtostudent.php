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

require_once(dirname(__FILE__) . '/../../config.php');
global $DB, $USER, $CFG, $PAGE;
$studentid = optional_param('studentid', null, PARAM_INT);
require_login();
$PAGE->set_url('/blocks/queries/comment_emailtostudent.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_title('Email to student');
$PAGE->set_pagelayout('standard');
echo $OUTPUT->header();
require_sesskey();

if ($commentformdata = data_submitted()) {
    $commentdata = new stdclass();
    $commentdata->queryid = $commentformdata->queryid;
    $commentdata->responduser = $USER->id;
    $commentdata->summary = '';
    // End here.
    $commentdata->comment = strip_tags($commentformdata->comment);
    $commentdata->postedtime = time();
    /********object for change status in queries table from 0 to 1********/
    $toupdate = new stdclass();
    $toupdate->id = $commentformdata->queryid;
    $toupdate->status = 1;
    $queryresponse = $DB->insert_record('block_query_response', $commentdata);
    $update = $DB->update_record('block_queries', $toupdate);
    $ifteacher = $DB->get_field('user', 'id', array('open_type' => '0', 'id' => $USER->id));
    $ifstudent = $DB->get_field('user', 'id', array('open_type' => '1', 'id' => $USER->id));

    $queryrecord = $DB->get_record_sql("SELECT id, postedby, subject, userid FROM {block_queries}
     WHERE id = {$commentformdata->queryid} ");
    $postedby = $queryrecord->postedby;
    $userid = $queryrecord->userid;
    $sql = "SELECT id, firstname, phone1, email FROM {user} ";
    if ($ifstudent) {
        $sql .= " WHERE id = {$postedby} ";
    } else if ($ifteacher) {
        $sql .= " WHERE id = {$userid} ";
    }
    $records = $DB->get_record_sql($sql);
    $emailobject = new stdClass();
    $emailobject->fullname = $records->firstname;
    $emailobject->summary = $commentformdata->summary;
    $emailobject->comment = strip_tags($commentformdata->comment);
    $instructorid = new stdClass();
    $instructorid->fullname = fullname($USER);

    $emailobject->regards = get_string('lecture_regards', 'block_queries', $instructorid);
    $subject = get_string('subjectforemailtostudent', 'block_queries', $records->subject);
    $emailtext = get_string('replytostudenttext', 'block_queries', $emailobject);
    $emailhtml = get_string('replytostudenthtml', 'block_queries', $emailobject); // Email html body.
    email_to_user($records, $USER, $subject, $emailtext, $emailhtml);

    // Send sms to instructor.
    if ($ifstudent) {
        redirect($CFG->wwwroot.'/blocks/queries/display_queries.php?studentid='.$records->id.'');
    } else if ($ifteacher) {
        redirect($CFG->wwwroot.'/blocks/queries/display_queries.php?userid='.$records->id.'');
    } else {
        redirect($CFG->wwwroot.'/blocks/queries/display_queries.php');
    }
}
echo $OUTPUT->footer();
