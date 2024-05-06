<?php
// This file is part of Moodle Course Rollover Plugin
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
 * @package     block_queries
 * @author
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_queries\local;
use context_system;
use plugin_renderer_base;
use render_from_template;
use moodle_url;
use stdClass;
use tabobject;
use dml_exception;
use html_table;
use html_writer;
use single_button;

class lib {
    /**
     * Get replay records from block_query_response.
     * int querieid.
     * @param int $querieid.
     * @return response records.
     */
    public function get_responseinfo($querieid) {
        global $DB, $USER;

        $sql = "SELECT qbr.*, u.firstname, u.lastname
                FROM {block_query_response} qbr
                JOIN {user} u ON u.id = qbr.responduser
                WHERE qbr.queryid = :querieid
                ORDER BY postedtime DESC ";
        $queryresponses = $DB->get_records_sql($sql, array('querieid' => $querieid));

        return $queryresponses;

    }

    /**
     * Get replay records from block_querie.
     * @return all records from block_querie.
     */
    public function get_adminquerie_records() {
        global $DB;

        $sql = "SELECT bq.id AS id, bq.subject AS subject,
                u.firstname AS postedto, u.id AS postedid, uu.firstname AS postedby,
                bq.postedby AS posteduserid, bq.status AS status, bq.timecreated,
                bq.description
                FROM {user} AS u
                JOIN {block_queries} AS bq ON bq.userid = u.id
                JOIN {user} AS uu ON uu.id = bq.postedby ORDER BY id DESC ";

        $adminqueryrecords = $DB->get_records_sql($sql);

        return $adminqueryrecords;
    }

    /**
     * Get replay records from block_querie.
     * Get records if pass querie id as param.
     * @param int $id.
     * @return all records from block_querie.
     */
    public function get_adminquerie_records_id($id) {
        global $DB;

        $sql = "SELECT bq.id AS id, bq.subject AS subject, u.firstname AS postedto, u.id AS postedid, uu.firstname AS postedby, bq.postedby AS posteduserid, bq.status AS status, bq.timecreated, bq.description
                FROM {user} AS u
                JOIN {block_queries} AS bq ON bq.userid = u.id
                JOIN {user} AS uu ON uu.id = bq.postedby WHERE bq.id = :id ORDER BY id DESC ";

        $adminqueryrecords = $DB->get_records_sql($sql, array('id' => $id));

        return $adminqueryrecords;

    }

    /**
     * for login user is a registrar.
     * @param int $userid.
     */
    public function getrole_user_register($userid) {
        global $DB;

        $sql = "SELECT qbr.*, u.firstname, u.lastname
                FROM {block_queries} qbr
                JOIN {user} u ON u.id = qbr.userid
                WHERE qbr.userid = :userid 
                ORDER BY u.id DESC";

        $adminqueryrecords = $DB->get_records_sql($sql, array('userid' => $userid));

        return $adminqueryrecords;

    }

    /**
     * Get replay mapped user data.
     * Get records by passing userid as param.
     * @param int $userid.
     * @return course participants.
     */
    public function get_facultyrecords($userid) {
        global $DB;

        $sql = "SELECT lc.courseid, pu.programid
                FROM {local_program_users} pu
                JOIN {local_program} p on p.id = pu.programid
                JOIN {local_program_level_courses} lc on lc.programid = p.id
                WHERE pu.userid = :userid";

        $program = $DB->get_records_sql($sql, array('userid' => $userid));

        return $program;

    }

    /**
     * Get replay mapped user data.
     * Get records by passing userid as param.
     * @param int $userid.
     * @return course participants.
     */
    public function get_facultycoursewise($courseid) {
        global $DB;

        $sql = "SELECT u.id, u.firstname, u.lastname FROM {user} u
                JOIN {user_enrolments} ue ON u.id = ue.userid
                JOIN {enrol} e on ue.enrolid = e.id
                JOIN {course} c on c.id = e.courseid
                JOIN {role_assignments} ra ON ue.userid = ra.userid
                JOIN {context} AS cxt ON cxt.id = ra.contextid
                AND cxt.contextlevel = 50
                AND cxt.instanceid = c.id
                JOIN {role} as r ON r.id = ra.roleid
                AND r.shortname = 'editingteacher'
                WHERE  e.enrol = 'manual' AND ue.userid = u.id
                AND c.id = :courseid";

        $records = $DB->get_records_sql($sql, array('courseid' => $courseid));

        return $records;

    }

    /**
     * Get records block_queries.
     * Get records by passing studentid(postedby) as param.
     * @param int $postedby.
     * @return block_queries postedby.
     */
    public function get_student_qurierecords($postedby) {
        global $DB;

        $sql = "SELECT * FROM {block_queries} WHERE postedby = :postedby ORDER BY id DESC";

        $studentpostedqueries = $DB->get_records_sql($sql, array('postedby' => $postedby));

        return $studentpostedqueries;

    }

    /**
     * Get records block_queries.
     * Get records by passing id, studentid(postedby) as param.
     * @param int $id, $postedby.
     * @return block_queries id, postedby.
     */
    public function get_student_qurierecordsquerieid($id, $postedby) {
        global $DB;

        $sql = "SELECT * FROM {block_queries} WHERE id = :id AND postedby = :postedby ORDER BY id DESC";

        $studentpostedqueries = $DB->get_records_sql($sql, array('id' => $id, 'postedby' => $postedby));

        return $studentpostedqueries;

    }

    /**
     * Get records block_queries.
     * Get records by passing faculty(postedby) as param.
     * @param int $userid.
     * @return block_queries userid.
     */
    public function get_faculty_qurierecords($userid) {
        global $DB;

        $sql = "SELECT q.*, u.firstname, u.lastname
                FROM {block_queries} q
                JOIN {user} u ON u.id = q.userid
                WHERE q.userid = :userid ORDER BY q.id DESC";

        $studentpostedqueries = $DB->get_records_sql($sql, array('userid' => $userid));

        return $studentpostedqueries;

    }

    /**
     * Get records block_queries.
     * Get records by passing id, faculty(userid) as param.
     * @param int $id, $postedby.
     * @return block_queries id, postedby.
     */
    public function get_faculty_qurierecordsquerieid($id, $userid) {
        global $DB;

        $sql = "SELECT q.*, u.firstname, u.lastname
                FROM {block_queries} q
                JOIN {user} u ON u.id = q.userid 
                WHERE q.id = :id AND q.userid = :userid ORDER BY q.id DESC";
        $studentpostedqueries = $DB->get_records_sql($sql, array('id' => $id, 'userid' => $userid));

        return $studentpostedqueries;

    }
    public function querydata($data) {
        global $CFG;
        $table = new html_table();
        $table->head = array('');
        $table->width = '100%';
        $table->id = 'queryresponse';
        $table->data = $data;
        $string = '';
        $string .= html_writer::table($table);
        $string .= html_writer::link(new moodle_url($CFG->wwwroot.'/my'),get_string('backtohome', 'block_queries'));
        return $string;
    }
}
