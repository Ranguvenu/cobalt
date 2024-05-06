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
$string['pluginname'] = 'Queries';
$string['askaquestion'] = 'Ask a Question';
$string['subject'] = '<small>Subject</small>';
$string['instructor'] = 'Admin';
$string['admin'] = 'Site Admin';
$string['itadmin'] = 'IT Admin';
$string['registrar'] = 'Registrar';
$string['reply'] = 'Reply';
$string['description'] = '<small>Description</small>';
$string['nocomments'] = 'No replies to display';
$string['time'] = 'Time';
$string['postquery'] = 'Post a query';
$string['usertype'] = '<small>Select a teacher to ask a query</small>';
$string['addacomment'] = 'Add a comment';
$string['summary'] = 'Summary';
$string['subjectt'] = 'Subject';
$string['noprevioussubjects'] = 'No previous subjects';
$string['descriptionn'] = 'Description';
$string['postedby'] = 'Posted By';
$string['askingquerieto'] = 'Posted To';
$string['rno'] = 'R.No:';
$string['postedtime'] = 'Posted time';
$string['status'] = 'Status';
$string['comment'] = 'Comment';
$string['comments'] = 'Comments';
$string['myqueries'] = 'Queries';
$string['allqueries'] = 'All Queries';
$string['noqueries'] = 'You do not have any queries';
$string['noclassenrolled'] = 'No Courses Enrolled';
$string['notresponded'] = '<small>No response</small>';
$string['responded'] = '<small>Responded</small>';
$string['backtohome'] = 'Back to Home';
$string['mypreviewqueries'] = 'Queries';
$string['viewcomment'] = 'View';
$string['viewcomments'] = 'View comments';
$string['select'] = '--Select--';
$string['selectuser'] = 'Recipient';
$string['raisedqueries'] = 'Student Raised Queries';
$string['askquerie'] = 'Ask a query';
$string['viewreplies'] = 'View replies';
$string['sendingemail'] = 'sending email';
$string['emailtext'] = 'Hi {$a->fullname},<br>
{$a->description}.<br>
{$a->regards}';
$string['student_regards'] = '<span><b>Regards</b></span><br>
{$a->fullname},<br>
{$a->class},<br>
{$a->organisation},<br>
{$a->schoolname}.';
$string['lecture_regards'] = '<span><b>Regards</b></span><br>
{$a->fullname}.';
$string['dr_faculty'] = 'Dear Admin,
You have got a new query from the student. Please click on the below link to view and reply.
{$a->fac_link}';

$string['dr_student'] = 'Dear Student,
Your Query has been submitted to the admin. You will be get notified when admin responds to your query.';

$string['dr1_faculty'] = 'Dear Admin,
Thank you for your response to student query. Your reply has been submitted successfully.
Student will be notified regarding your reply.';

$string['dr1_student'] = 'Dear Student,
Your query has been answered. Please login to LMS to see the reply. Keep posting on the LMS for any further queries. Thank you.
{$a->stu_link}';
$string['emailhtmlbody'] = '<div style="margin:auto;"><p>Hello Admin,</p>
		<p>{$a->description}</p>
		<span style="font-size:11px;">Please click on below link to reply this query.</span>
		<br>
		<span style="font-size:11px;">{$a->link}.</span><br>
		<p>{$a->regards}</p>
</div>';
// End here.
$string['subjectforemailtostudent'] = 'Response of the "{$a}"';

$string['replytostudenttext'] = 'Hi {$a->fullname},
<p>{$a->summary}</p>
<p>{$a->comment}</p><br>,
<span style = "font-size:11px;">Please click on below link to view the admin reply.</span><br>
<span style = "font-size:11px;">{$a->link}.</span><br>
{$a->regards}';

$string['replytostudenthtml'] = '<div style="margin:auto;"><p>Hi {$a->fullname},</p>
<p>{$a->summary}</p>
<p>{$a->comment}</p>
<span style = "font-size:11px;">Please click on below link to view the admin reply.</span><br>
<span style = "font-size:11px;">{$a->link}.</span><br>
<p>{$a->regards}</p>
</div>';

$string['askaquestion_help'] = 'You can ask a question here to admin.';
$string['postedon'] = 'Posted On';
$string['askaquestiontoauthorities'] = 'You can ask a question here to your admin.';
$string['raisedby'] = '<span>Query Raised By : </span>';
$string['messageprovider:block_queries'] = 'Sending Queries';
$string['subject'] = 'Subject';
$string['description'] = 'Description';
$string['block/queries:addinstance'] = 'Queries Block Access';
$string['repliedby'] = 'Replied by';
$string['class'] = 'Class';
$string['nocourses'] = 'No Courses Enrolled';
$string['nodataavailable'] = 'No Queries Available';
$string['queries:manage'] = 'Manage Queries ';
$string['queries:manager'] = 'Manager For Queries';
$string['queries:studentroles'] = 'Queries Student Role';
$string['queries:teacheraccess'] = 'Queries Teacher Access';
$string['queries:addinstance'] = 'Queries Add Instance';

// DeleteQuery's.
$string['deletequerybtn'] = 'Delete';
$string['deleteresponseconfirm'] = 'Are You Sure Want To Delete Querie';
$string['deletequeryconfirm'] = 'Are You Sure Want To Delete Response Querie';
$string['deletequeryfailed'] = 'Failed to delete';
$string['deleting'] = 'Deleting...';
$string['deletequery'] = 'Confirmation';
$string['previousQueries'] = 'All Previous Queries';
$string['back'] = 'Back';
$string['dashboard'] = 'Dashboard';
