<?php

/**
 * This file is part of eAbyas
 *
 * Copyright eAbyas Info Solutons Pvt Ltd, India
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author eabyas  <info@eabyas.in>
 * @subpackage block_queries
 */
// Function for check the login user instructor or registrar or student.
use \block_queries\form\queries_form as queries_form;
function block_queries_getrole_user ($courses = null, $rolename) {
    global $CFG, $USER, $PAGE, $DB;
    $registrarlogin = array();
    if (!$courses) {
        $sql = "SELECT u.id, u.email, u.firstname, u.lastname
                FROM {role} AS role
                JOIN {role_assignments} AS ra ON ra.roleid = role.id
                JOIN {user} AS u ON ra.userid = u.id
                WHERE role.shortname = '$rolename' AND ra.contextid = 1 AND u.id = {$USER->id}";
        $registrarrecord = $DB->get_record_sql($sql);
        if ($registrarrecord) {
            $registrarlogin[] = $registrarrecord->id;
        }
    } else {
        foreach ($courses as $course) {
            $sql = "SELECT u.id, u.email, u.firstname, u.lastname
                    FROM {context} AS cxt
                    JOIN {role} AS role
                    JOIN {role_assignments} AS ra
                    ON cxt.id = ra.contextid 
                    JOIN {user} AS u
                    ON ra.userid = u.id
                    WHERE cxt.instanceid = $course->id AND ra.roleid = role.id AND role.shortname = '$rolename'
                    AND cxt.contextlevel = 50 AND u.id = {$USER->id}";
            $registrarrecord = $DB->get_record_sql($sql);
            if ($registrarrecord) {
                $registrarlogin[] = $registrarrecord->id;
            }
        }
    }
    return $registrarlogin;
}
// Function to diplay the data in block according to the user role.
function block_queries_display_view ($USER = false, $instructorlogin = false, $registrarlogin = false, $studentlogin = false) {
    global $CFG, $USER, $PAGE, $DB;

    $systemcontext = context_system::instance();
    $blockqueriesreturncontent = array();
    $sql = "SELECT id, subject, userid, postedby FROM {block_queries} WHERE userid = $USER->id";
    // If manager or school admin login this is the query block.
    if (has_capability('block/queries:manager', $systemcontext)) {
        $sqlformanagar = "SELECT bq.id AS id, bq.subject AS subject, u.firstname AS postedto, uu.firstname AS postedby
                            FROM {user} AS u 
                            JOIN {block_queries} AS bq ON bq.userid = u.id 
                            JOIN {user} AS uu ON uu.id = bq.postedby 
                            ORDER BY id DESC LIMIT 5";

        $blockqueriesreturncontent[] = blockqueries_tablecontent($sqlformanagar);
        $blockqueriesreturncontent = implode('', $blockqueriesreturncontent);

    } else if ($instructorlogin) {  // If Teacher login this is the query block.
            $value = '';
            $value .= "
            <ul class='nav nav-tabs coursestabs'>
            <li><a data-toggle='tab' href='#newdataone'  class='active'>".get_string('raisedqueries', 'block_queries')."</a></li>
            <li><a data-toggle='tab' href='#newdatatwo'>".get_string('askquerie', 'block_queries')."</a></li>
            </ul>
            ";
            $value .= '<div class="tab-content">';
            $value .= "<div id='newdataone' class='tab-pane fade in active col-md-12 col-12'>";
            $sql .= " AND userrole = 'employee' ORDER BY id DESC LIMIT 3";
            $blockqueriesdisplaycontent = array();
            $querieslists = $DB->get_records_sql($sql);
            $data = array();
        foreach ($querieslists as $querieslist) {
            $row = array();
            $adminqueryid = $querieslist->id;
            $subject = html_writer::link(new moodle_url('/blocks/queries/display_queries.php#query'.$querieslist->id),
            $querieslist->subject);
            $row[] = html_writer:: tag('p', $subject, array());
            $commentimage = html_writer:: empty_tag('img', array('src' => $CFG->wwwroot.'/pix/t/message.svg',
            "id" => "showDialog$adminqueryid", 
            'title' => get_string('addacomment', 'block_queries'), 
            'onclick' => "mycommentpopupform($adminqueryid)", 'class' => 'queries_iconclass'));

            $commentpopup = commenthtmlform($adminqueryid);
            $row[] = $commentimage.$commentpopup;
            $data[] = $row;
        }
        $table = new html_table();
        $table->head = array(get_string('subjectt', 'block_queries'), get_string('reply', 'block_queries'));
        $table->data = $data;
        $table->width = '100%';
        if (!$data) {
            $value .= get_string('noqueries', 'block_queries');
        } else {
            $value .= html_writer::table($table);
            $value .= html_writer::tag('a', get_string('allqueries', 'block_queries'),
            array('href' => $CFG->wwwroot.'/blocks/queries/display_queries.php?teacherid='.$USER->id, "class" => "f13 pull-right"));
        }

        $value .= "</div>";

        $value .= "<div id='newdatatwo' class='tabcontent col-md-12 col-12'>";
                            $formdata = new stdClass();
                            $actionpage = $CFG->wwwroot.'/blocks/queries/sendingemail.php';
                            $mform = new queries_form($actionpage);
                            $value .= $mform->render(); // To display form in block.
        $value .= "</div>
        </div>";
        $value .= '
            <script>
                function togglef(name) {
                if(name == "firsttabli") {
                    if($(".firsttabli").hasClass("active")){
                    $(".secondli").removeClass("active");
                    }else{
                    $(".firsttabli").addClass("active");
                    }

                    if($(".secondli").hasClass("active")){
                    $(".secondli").removeClass("active");
                    }
                    $("#tabuser1").css("display", "block");
                    $("#tabuser2").css("display", "none");
                }
                if(name == "secondli") {
                    if($(".secondli").hasClass("active")){
                    $(".firsttabli").removeClass("active");
                    }else{
                    $(".secondli").addClass("active");
                    }

                    if($(".firsttabli").hasClass("active")){
                    $(".firsttabli").removeClass("active");
                    }
                    $("#tabuser2").css("display", "block");
                    $("#tabuser1").css("display", "none");
                }
                $("#tabuser2").css("display", "block");
                $("#tabuser1").css("display", "none");
                }
            </script>';
        return $value;
    } else if ($studentlogin) { // If student login this is the query block.
            $formdata = new stdClass();
            $actionpage = $CFG->wwwroot.'/blocks/queries/sendingemail.php';
            $mform = new queries_form($actionpage);
            $blockqueriesreturncontent[] = $mform->render(); // To display form in block.
            $blockqueriesreturncontent = implode('', $blockqueriesreturncontent);
    } else {
            $sql .= " ORDER BY id DESC LIMIT 5";
            $blockqueriesreturncontent[] = blockqueries_tablecontent($sql);
            $blockqueriesreturncontent = implode('', $blockqueriesreturncontent);
    }
    return $blockqueriesreturncontent;
}

function blockqueries_tablecontent($sql) {
    global $CFG, $USER, $PAGE, $DB;

    $blockqueriesdisplaycontent = array();
    $querieslists = $DB->get_records_sql($sql);
    $ifteacher = $DB->get_field('user', 'id', array('open_type' => '0', 'id' => $USER->id));
    $ifstudent = $DB->get_field('user', 'id', array('open_type' => '1', 'id' => $USER->id));
    $ifadmin = $DB->get_field('user', 'id', array('id' => '2', 'id' => $USER->id));
    $data = array();
    foreach ($querieslists as $querieslist) {
        $row = array();
        $adminqueryid = $querieslist->id;

        $sub = strlen($querieslist->subject) > 20 ? substr($querieslist->subject, 0, 20)."..." : $querieslist->subject;

        if ($ifteacher) {
            $subject = html_writer::link(new 
            moodle_url('/blocks/queries/display_queries.php?userid='.$querieslist->userid.'&querieid='.$adminqueryid),
            $sub, array('title'=>$querieslist->subject));

        } else if ($ifstudent) {
            $subject = html_writer::link(new 
            moodle_url('/blocks/queries/display_queries.php?studentid='.$querieslist->postedby.'&querieid='.$adminqueryid),
            $sub, array('title'=>$querieslist->subject));
        } else if ($ifadmin) {
            $subject = html_writer::link(new moodle_url('/blocks/queries/display_queries.php?querieid='.$adminqueryid),
            $sub, array('title'=>$querieslist->subject));
        }
        
        $row[] = html_writer:: tag('p', $subject, array());
        $commentimage = html_writer::empty_tag('img', 
        array('src' => $CFG->wwwroot.'/pix/t/message.svg', "id" => "showDialog$adminqueryid", "data-id" => "$adminqueryid", 
        'title' => get_string('addacomment', 'block_queries'), 'class' => 'queries_iconclass commenticonpostion'));
        $commentpopup = commenthtmlform($adminqueryid);
        $row[] = $commentimage.$commentpopup;
        $data[] = $row;
    }
    $table = new html_table();
    $table->head = array(get_string('subjectt', 'block_queries'), get_string('reply', 'block_queries'));
    $table->width = '100%';
    $table->size = array('95%', '5%');
    $table->align = array('left', 'center');
    $table->data = $data;
    if (!$data) {
        $blockqueriesdisplaycontent[] = get_string('noqueries', 'block_queries');
    } else {
        $blockqueriesdisplaycontent[] = html_writer::table($table);
        $blockqueriesdisplaycontent[] = "<script>
                    function formvalidate(queryid){
                    var comment = $('#comments'+queryid).val();
                    if(comment == undefined || comment == ''){
                        $('.error_box'+queryid).html('<p style=\'color:red;\'>Please fill the required fields</p>');
                        return false;
                    }
                    }

                </script>";
    }
    $blockqueriesdisplaycontent = implode('', $blockqueriesdisplaycontent);
    return $blockqueriesdisplaycontent;
}
function queriesuser_details ($postedby) {
    global $DB;

    $sql = "SELECT u.username, u.id, u.firstname AS fullname, bq.postedby AS posteduser FROM {user}
             AS u JOIN {block_queries} AS bq ON u.id = bq.postedby
             WHERE u.id = {$postedby} 
             GROUP BY u.id";
    $serviceid = $DB->get_record_sql($sql);
    return $serviceid;
}
