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
 * Configurable Reports
 * A Moodle block for creating customizable reports
 * @package blocks
 * @author: Juan leyva <http://www.twitter.com/jleyvadelgado>
 * @date: 2009
 */

class report_enrolments extends report_base {

    public function init() {
        $this->components = array('columns', 'conditions', 'ordering', 'filters', 'template', 'permissions', 'calcs', 'plot');
    }

    public function get_all_elements() {
        global $DB;

        $elements = array();
        $rs = $DB->get_recordset('user_enrolments', null, '', 'id');
        foreach ($rs as $result) {
            $elements[] = $result->id;
        }
        $rs->close();
        return $elements;
    }

    public function get_rows($elements, $sqlorder = '') {
        global $DB, $CFG;
$filter_courses = optional_param('filter_courses', 0, PARAM_RAW);
$filter_username = optional_param('filter_username', 0, PARAM_RAW);
        print_object($filter_username);

        // $sql = "SELECT u.id,ue.id, u.firstname AS username,(c.shortname) as coursename
        //           FROM {user} as u,{course} as c 
        //           JOIN {enrol} as e ON e.courseid=c.id 
        //           JOIN {user_enrolments} as ue ON ue.enrolid=e.id 
        //           WHERE u.id=ue.userid AND u.id>2";

  $sql = "SELECT u.id,ue.id, u.firstname AS username,c.shortname,GROUP_CONCAT(c.fullname) as coursename
                  FROM {user} as u,{course} as c 
                  JOIN {enrol} as e ON e.courseid=c.id 
                  JOIN {user_enrolments} as ue ON ue.enrolid=e.id 
                  ";
                  $wheresql = " WHERE u.id=ue.userid AND u.id>2 ";
                  $groupsql = " GROUP BY u.id";
        if(!empty($filter_courses)) {
            $wheresql .=" AND c.id = $filter_courses ";
        }
        if(!empty($filter_username)){
        
            $wheresql .=" AND u.id = $filter_username ";
        }
            $records = $DB->get_records_sql($sql .$wheresql .$groupsql);

        return $records;


 // $sql = "SELECT COUNT(u.id), c.fullname,u.firstname as coursename 
 //            FROM {user} as u,{course} as c 
 //            JOIN {enrol} as e ON e.courseid=c.id 
 //            JOIN {user_enrolments} as ue ON e.id=ue.enrolid 
 //            JOIN {role_assignments} as ra ON ue.userid=ra.userid 
 //            JOIN {role} as r ON r.id=ra.roleid 
 //            WHERE r.shortname='editingteacher' AND ue.userid=u.id";
 //            $records = $DB->get_records_sql($sql);

 // // print_object($records);exit;
 //        return $records;

    }
}

