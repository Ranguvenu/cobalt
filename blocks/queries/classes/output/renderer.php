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
 *
 * @package    block_queries
 * @copyright  2022 eAbyas info solutions
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_queries\output;
defined('MOODLE_INTERNAL') || die();
use block_queries\local\lib;
use plugin_renderer_base;
use context_system;
use html_table;
use html_writer;
use moodle_url;
use stdClass;
use single_button;
use costcenter;
require_once($CFG->dirroot.'/local/costcenter/lib.php');

class renderer extends plugin_renderer_base {
    /**
     * Get Querie records mapped postedby(studentid) data.
     * Get records by passing postedby(studentid), id(Querieid) as param.
     * @param int $postedby, $id.
     * @return Querie postedby users.
     */
    public function querie_recordstodisplay ($studentid, $querieid) {
        global $DB, $USER, $OUTPUT, $CFG;
        
        $lib = new lib();
        if ($studentid) {
            $studentpostedqueries = $lib->get_student_qurierecords($studentid);
            if ($studentid && $querieid) {
                $studentpostedqueries = $lib->get_student_qurierecordsquerieid($querieid, $studentid);
            }
            if ($studentpostedqueries) {
                $data = array();
                foreach ($studentpostedqueries as $studentpostedquerie) {
                    $row = array();
                    $ifstudent = $DB->get_field('user', 'id', array('open_type' => '1', 'id' => $USER->id));
                    if ($ifstudent) {
                        $postedby = $studentpostedquerie->userid;
                        $postedstudent = $DB->get_record_sql("SELECT * FROM {user} WHERE id = {$postedby}");
                        if (!$postedby) {
                            continue;
                        }
                    }
                    $studentfullname = fullname($postedstudent);
                    $datestudent = html_writer::tag('span', date("d/m/Y h:i a", $studentpostedquerie->timecreated),
                    array('class' => 'date'));
                    if ($studentpostedquerie->status == 0) {
                        $ifelsestudent = '<span style="color: red"><b>'.get_string('notresponded', 'block_queries').'</b></span>';
                    } else {
                        $ifelsestudent = '<span style="color: green"><b>'.get_string('responded', 'block_queries').'</b></span>';
                    }
                    // Here we are calling js fuction to get the toggle.
                    $comment = '<a href="javascript:void(0)" onclick="view('.$studentpostedquerie->id.')">'.get_string('viewreplies', 
                    'block_queries').'</a>';
                    $click = html_writer::tag('a', get_string('reply', 'block_queries'),
                            array("id" => "showDialog$studentpostedquerie->id",
                            "class" => "commenticonpostion", 'data-id' => $studentpostedquerie->id));
                    $popup = commenthtmlform($studentpostedquerie->id, $USER->id);
                    // Here we are showing the table content in a list for student.
                    $sprofilepicture = $OUTPUT->user_picture($postedstudent, array('size' => 35));
                    $test = '<div class="user_query_block">
                    <div class = "profilepicture ">'.$sprofilepicture.'</div>
                    <p class = "subjectclass">'.format_text($studentpostedquerie->subject).'<span>'.get_string('askingquerieto', 'block_queries').' :<span class="name">'.$studentfullname.'</span></span>
                        <span class = "askingquerietoleft ml-2">'.get_string('postedon', 'block_queries').' : '.''.$datestudent.'</span></p>
                    <hr class = "horizontalline">
                    <div class = "informationdiv">
                    <span class="status">'.get_string('status', 'block_queries').': '.$ifelsestudent.'</span><span class = "responded ml-1 mr-1"> | '.$click.' | </span>
                    <span class="viewreplies">'.$comment.'</span>
                    </div>
                    <p class = "comment_summary">'.format_text($studentpostedquerie->description).'</p>
                    <hr class = "horizontalline">
                    <div class = "toggle_style"><span class="toggle'.$studentpostedquerie->id.'">';
                    // Comments in toggle-starts here student {student}.
                    $qid = $studentpostedquerie->id;
                    $queryresponses = $lib->get_responseinfo($qid);
                    if ($queryresponses) {
                        foreach ($queryresponses as $queryresponse) {
                            $colorclass = '';
                            $responduserid = $queryresponse->responduser;
                            $ffirstname = $queryresponse->firstname;
                            $flastname = $queryresponse->lastname;
                            $respondedusername = ($ffirstname.' '.$flastname);
                            $postedby = html_writer::tag('b', get_string('postedby', 'block_queries'), array());
                            $date = html_writer::tag('span', date("d/m/y h:i a", $queryresponse->postedtime), array('class' => 'queries_postedtime', 'style' => 'color: #424242; font-size: 11px;'));
                            $byposted = html_writer::tag('p', $date, array('class' => 'posted_by'));
                                if ($queryresponse->responduser == $USER->id)
                                    $colorclass = 'usersreply';
                                    $comments = html_writer::start_tag('div', array( 'class' => 'togglediv '.$colorclass.''));
                                    $comments .= html_writer::tag('p', get_string('repliedby', 'block_queries').'&nbsp:&nbsp;'.$respondedusername, array('class' => 'replied_user mr-1', 'style' => 'float:right;'));
                                    $comments .= '<span style="color: #424244;"><b>'.$byposted.get_string('reply', 'block_queries').' :</b></span>'.html_writer::tag('span', $queryresponse->comment, array('class' => 'toggle_comment mx-1'));
                                    $comments .= html_writer::end_tag('div', array());
                                    $comments .= html_writer::start_tag('div', array( 'class' => 'toggledate'));
                                    $comments .= html_writer::end_tag('div', array());
                                    $test .= $comments;
                        }
                    } else {
                        $test .= html_writer::tag('p', get_string('nocomments', 'block_queries'));
                    }
                    // Code for display comments in toggle-ended here;.
                    $test .= $popup;
                    $test .='</div>';
                    $row[] = $test;
                    $data[] = $row;
                }
                echo $lib->querydata($data);
            } else {
                return "<div class='alert alert-info w-100 text-center'>".get_string('nodataavailable', 'block_queries')."</div>";
            }
        }
    }

    /**
     * Get Querie records mapped user(faculty) data.
     * Get records by passing Querieid,userid(faculty) as param.
     * @param int $userid, $id.
     * @return Querie users.
     */
    public function faculty_records ($querieuserid, $querieid) {
        global $DB, $USER, $OUTPUT, $CFG;
        $lib = new lib();
        if ($querieuserid) {
            $studentpostedqueries = $lib->get_faculty_qurierecords($querieuserid);

            if ($querieuserid && $querieid) {
                $studentpostedqueries = $lib->get_faculty_qurierecordsquerieid($querieid, $querieuserid);
            }
            if ($studentpostedqueries) {
                $data = array();
                foreach ($studentpostedqueries as $studentpostedquerie) {
                    $row = array();
                    $postedby = $studentpostedquerie->postedby;
                    if (!$postedby) {
                        continue;
                    }
                    $postedfaculty = $DB->get_record_sql("SELECT * FROM {user} WHERE id = $postedby");
                    $studentfullname = fullname($postedfaculty);
                    $datestudent = html_writer::tag('span', date("d/m/Y h:i a", $studentpostedquerie->timecreated),
                    array('class' => 'date'));

                    if ($studentpostedquerie->status == 0) {
                        $ifelsestudent = '<span style="color: red"><b>'.get_string('notresponded', 'block_queries').'</b></span>';
                    } else {
                        $ifelsestudent = '<span style="color: green"><b>'.get_string('responded', 'block_queries').'</b></span>';
                    }
                    // Here we are calling js fuction to get the toggle.
                    $comment = '<a href="javascript:void(0)" onclick="view('.$studentpostedquerie->id.')">'.get_string('viewreplies',
                    'block_queries').'</a>';
                    $click = html_writer::tag('a', get_string('reply', 'block_queries'), array("id" => "showDialog$studentpostedquerie->id", "class" => "commenticonpostion", 'data-id' => $studentpostedquerie->id));
                    $popup = commenthtmlform($studentpostedquerie->id, $USER->id);
                    // Here we are showing the table content in a list for faculty.
                    $fprofilepicture = $OUTPUT->user_picture($postedfaculty, array('size' => 35));
                    $test = '
                    <div class="profilepicture">'.$fprofilepicture.'</div>
                    <p class="subjectclass">'.format_text($studentpostedquerie->subject).'<br>'.get_string('postedby', 'block_queries').' : '.$studentfullname.'<span class="mr-1 ml-2">'.get_string('postedon', 'block_queries').' :</span>'.$datestudent.'</p>
                    <hr class="horizontalline">
                    <p class="comment_summary">'.format_text($studentpostedquerie->description).'</p>
                    <div class="informationdiv">'.get_string('status', 'block_queries').' : '.$ifelsestudent.' |
                    '.$click.' | '.$comment.'</div>
                    <div class="toggle_style"><span style="display:none;" class="toggle'.$studentpostedquerie->id.'">';
                    // Comments in toggle-starts here faculty.

                    $queryresponses = $lib->get_responseinfo($studentpostedquerie->id);
                    if ($queryresponses) {
                        foreach ($queryresponses as $queryresponse) {
                            $colorclass = '';
                            $responduserid = $queryresponse->responduser;
                            $fname = $queryresponse->firstname;
                            $lname = $queryresponse->lastname;

                            $fuserid = $DB->get_record_sql("SELECT * FROM {user} WHERE id = $responduserid");
                            $respondedusername = ($fname.' '.$lname);
                            $postedby = html_writer::tag('b', get_string('postedby', 'block_queries'), array());
                            $date = html_writer::tag('span', date("d/m/y h:i a", $queryresponse->postedtime), array('class' => 'queries_postedtime', 'style' => 'color: #424242; font-size: 11px;'));
                            $byposted = html_writer::tag('p', $date, array('class' => 'posted_by'));
                            if ($queryresponse->responduser == $USER->id)
                                $colorclass = 'usersreply';
                                $comments = html_writer::start_tag('div', array( 'class' => 'togglediv '.$colorclass.''));
                                $comments .= html_writer::tag('p', get_string('repliedby', 'block_queries').'&nbsp:&nbsp;'.$respondedusername, array('class' => 'replied_user', 'style' => 'float:right;'));
                                $comments .= '<span style="color: #424244;"><b>'.$byposted.get_string('reply', 'block_queries').'&nbsp:&nbsp</b></span>'.html_writer::tag('span', $queryresponse->comment, array('class' => 'toggle_comment mx-1'));
                                $comments .= html_writer::end_tag('div', array());
                                $comments .= html_writer::start_tag('div', array( 'class' => 'toggledate'));
                                $comments .= html_writer::end_tag('div', array());
                                $test .= $comments;
                        }
                    } else {
                        $test .= html_writer::tag('p', get_string('nocomments', 'block_queries'));
                    }
                    // Code for display comments in toggle-ended here.
                    $test .= '</span></div></div>';
                    $test .= $popup;
                    $row[] = $test;
                    $data[] = $row;
                }
                echo $lib->querydata($data);
            } else {
                return "<div class='alert alert-info w-100 text-center'>".get_string('nodataavailable', 'block_queries')."</div>";
            }
        }
    }

    /**
     * Get all Querie records to admin.
     * Get student,faculty and admin posted queries by passing Querieid as a param.
     * @param int $id(querieid).
     * @return Querie users.
     */
    public function get_adminquerie_records ($querieid) {
        global $DB, $USER, $CFG, $OUTPUT;
        $systemcontext = context_system::instance();
        if (has_capability('block/queries:manager', $systemcontext)) { // Manager capability starts here.
            $lib = new lib();
            if ($querieid) {
                $adminqueries = $lib->get_adminquerie_records_id($querieid);
            } else {
                $adminqueries = $lib->get_adminquerie_records();
            }
            if ($adminqueries) {
                $data = array();
                foreach ($adminqueries as $adminquery) {
                    $row = array();
                    $queryid = $adminquery->id;
                    $postedby = $adminquery->posteduserid;
                    $postedusername = $adminquery->postedby;
                    $postedto = $adminquery->postedid;
                    $status = $adminquery->status;
                    $userid = $DB->get_record_sql("SELECT * FROM {user} WHERE id = $postedby");
                    $postedbytheuser = fullname($userid);
                    $postedadmin = $DB->get_record_sql("SELECT * FROM {user} WHERE id = $postedby");

                    $studentname = html_writer::tag('a', fullname($postedadmin), 
                                    array('href' => $CFG->wwwroot.'/user/profile.php?id='.$postedadmin->id));
                    // Student name.
                    $teacher = $DB->get_record_sql("SELECT * FROM {user} WHERE id = $postedto");
                    // Faculty name.
                    $teachername = html_writer::tag('a', fullname($teacher), 
                                    array('href' => $CFG->wwwroot.'/user/profile.php?id='.$teacher->id));
                    // Admin.
                    $dateadmin = date("d/m/y h:i a", $adminquery->timecreated);
                    if ($adminquery->status == 0) {
                        $ifelseadmin = '<span style="color: red"><b>'.get_string('notresponded', 'block_queries').'</b></span>';
                    } else {
                        $ifelseadmin = '<span style="color: green"><b>'.get_string('responded', 'block_queries').'</b></span>';
                    }
                    $clickadmin = html_writer::tag('a', get_string('reply', 'block_queries'), array("id" => "showDialog$queryid", "data-id" => "$queryid", "class" => "commenticonpostion"));
                    $popupadmin = commenthtmlform($queryid);
                    // Here we are calling js fuction to get the toggle.
                    $comment = '<a href="javascript:void(0)" onclick="view('.$adminquery->id.')">'.get_string('viewreplies', 
                                'block_queries').'</a>';
                    // Here we are showing the table content in a list.
                    $aprofilepicture = $OUTPUT->user_picture($postedadmin, array('size' => 35));

                    $deletepost = html_writer::tag('span', ''.get_string('delete'), array("class" => "local_queryid{$queryid} local_message_delete_response text-danger ", "style" => ' margin-left: 10px; color: white;'));
                    $test = '<div id="query'.$adminquery->id.'"><div class="query_block">
                        <div class="profilepicture">'.$aprofilepicture.'</div>
                        <p class="subjectclass">'.format_text($adminquery->subject).'
                        <span class="small">'.get_string("raisedby", "block_queries").''.$studentname.'</span><span class = "subjectclassteacher mx-2 small">
                        '.get_string('askingquerieto', 'block_queries').':
                        '.$teachername.''.''.'</span>
                        <b><span class="mx-2 small">'.get_string('postedon', 'block_queries').'
                        : </b> <span class="ml-1 posted_date">'.$dateadmin.'</span></span>
                        <span class="mx-2 small">'.$deletepost.'</span>'.'</span>
                        </p>

                    <hr class="horizontalline">
                    <div class="informationdiv"><span class="view_status">'.get_string('status', 'block_queries').' :<span class="mx-1">'.$ifelseadmin.'</span></span>'.'<span class="mx-1">| '.$clickadmin.' | </span><span class="replies">'.$comment.'</span></div>
                    <p class="comment_summary">'.format_text($adminquery->description).'</p>
                    <hr class="horizontalline">
                    <div class="toggle_style"><span style="display:block;" class="toggle'.$adminquery->id.'">';

                    // Code for display comments in toggle-started here.
                    $adminqueryresponse = $lib->get_responseinfo($adminquery->id);
                    if ($adminqueryresponse) {
                        foreach ($adminqueryresponse as $adminqueryresponses) {
                            $colorclass = '';
                            $responduserid = $adminqueryresponses->responduser;
                            $responseid = $adminqueryresponses->id;
                            $firstname = $adminqueryresponses->firstname;
                            $lastname = $adminqueryresponses->lastname;
                            $teacherreply = ($firstname.' '.$lastname);
                            if ($adminqueryresponses->responduser != $USER->id)
                                $colorclass = 'usersreply';
                                $comments = html_writer::start_tag('div', array( 'class' => 'togglediv '.$colorclass.''));
                                $time = html_writer::tag('span', date("d/m/y h:i a", $adminqueryresponses->postedtime), 
                                        array('style' => 'color: #424244; font-size: 11px;'));
                                // For replied by the admin.
                                $comments .= html_writer::tag('span', ''.get_string('delete'), array("class" => "local_responseid{$responseid} local_message_delete_button text-danger"));
                                $comments .= html_writer::tag('p', get_string('repliedby', 'block_queries').'&nbsp:&nbsp;'.$teacherreply, array('class' => 'replied_teacher', 'style' => 'float:right;'));
                                // For replied by the admin.
                                $comments .= html_writer::tag('p', $time, array('class' => 'posted_by', 
                                            'style' => 'font-weight: bold;'));
                                $comments .= '<span style="color: #424244;"><b>'.get_string('reply', 'block_queries').'&nbsp:&nbsp</b></span>'.html_writer::tag('span', $adminqueryresponses->comment,
                                array('class' => 'toggle_comment mx-1'));
                                $comments .= html_writer::end_tag('div', array());
                                $test .= $comments;
                        }
                    } else {
                        $test .= html_writer::tag('p', get_string('nocomments', 'block_queries'));
                    }
                    // End of code for display comments in toggle-started here.
                    $test .= '</span></div></div>';
                    $test .= $popupadmin;
                    $row[] = $test;
                    $data[] = $row;
                }
                echo $lib->querydata($data);
            } else {
                return "<div class='alert alert-info w-100 text-center'>".get_string('nodataavailable', 'block_queries')."</div>";
            }
        }

    }
}
