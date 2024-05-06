<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or localify
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
 * block_students_attendance external API
 *
 * @package    block_students_attendance
 * @category   external
 * @copyright  eAbyas <www.eabyas.in>
 */

defined('MOODLE_INTERNAL') || die;
require_once("$CFG->libdir/externallib.php");

class block_students_attendance_external extends external_api {

     /**
     * Describes the parameters for submit_create_course_form webservice.
     * @return external_function_parameters
     */
    public static function student_courses_view_parameters() {
        return new external_function_parameters(
            array(
                'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
                'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
                'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                    VALUE_DEFAULT, 0),
                'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                    VALUE_DEFAULT, 0),
                'contextid' => new external_value(PARAM_INT, 'contextid'),
                'filterdata' => new external_value(PARAM_RAW, 'filters applied'),
            )
        );
    }

     /**
       * lists all courses of a student
       *
       * @param array $options
       * @param array $dataoptions
       * @param int $offset
       * @param int $limit
       * @param int $contextid
       * @param array $filterdata
       * @return array courses list.
       */
    public static function student_courses_view($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $DB, $CFG, $USER, $PAGE;
        require_login();
        $PAGE->set_url('/blocks/students_attendance/studentcourses.php', array());
        $PAGE->set_context($contextid);
        require_once($CFG->dirroot . '/blocks/students_attendance/lib.php');

        $params = self::validate_parameters(
            self::student_courses_view_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'contextid' => $contextid,
                'filterdata' => $filterdata
            ]
        );
        $offset = $params['offset'];
        $limit = $params['limit'];
        $decodedata = json_decode($params['dataoptions']);
        $filtervalues = json_decode($filterdata);

        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $stable->studentid = $decodedata->studentid;
        $data = get_student_courses($stable, $filtervalues);
        $totalcount = $data['count'];
        return [
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' => $data,
            'totalcount' => $totalcount,
            'options' => $options,
            'dataoptions' => $dataoptions,
        ];
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description
     * @since Moodle 3.0
     */
    public static function student_courses_view_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'records' => new external_single_structure(
                array(
                    'length' => new external_value(PARAM_RAW, 'length', VALUE_OPTIONAL),
                    'hascourses' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'cid' => new external_value(PARAM_RAW, 'cid', VALUE_OPTIONAL),
                                'fullname' => new external_value(PARAM_RAW, 'fullname', VALUE_OPTIONAL),
                                'fullnames' => new external_value(PARAM_RAW, 'fullnames', VALUE_OPTIONAL),
                                'userpresented' => new external_value(PARAM_RAW, 'userpresented'),
                                'userabsented' => new external_value(PARAM_RAW, 'userabsented'),
                                'totalattdence' => new external_value(PARAM_RAW, 'totalattdence'),
                                'pecentage' => new external_value(PARAM_RAW, 'pecentage'),
                                'cfg_url' => new external_value(PARAM_RAW, 'cfg_url'),
                            )
                        )
                    ),
                )
            )
        ]);
    }
}
