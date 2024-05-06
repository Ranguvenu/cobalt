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
 * Class containing data to get the curriculum assigned to the program block.
 *
 * @package    block_mycurriculum
 * @copyright  diksha@eabyas.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_mycurriculum\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;

class main implements renderable, templatable {
    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output
     * @return \stdClass|array
     */
    public function export_for_template(renderer_base $output) {
       global $USER, $DB,$CFG;
        $userid = $USER->id;

        $config = get_config('block_mycurriculum');

        //Get curriculum assigned to perticulur program
        $sql = $DB->get_record_sql("SELECT lc.name, lp.id, lp.curriculumid FROM {local_program} lp JOIN {local_program_users} lpu ON lp.id = lpu.programid JOIN {local_curriculum} lc ON lc.id = lp.curriculumid WHERE lpu.userid =".$userid."");

        $programid = $sql->id;
        $curriculumid = $sql->curriculumid;
        $curriculumname = $sql->name;

        //Get the Semesters
        $semisters_sql = "SELECT DISTINCT lcsc.semesterid, lcs.semester FROM {local_cc_semester_courses} lcsc LEFT JOIN {local_curriculum} lc ON lc.id = lcsc.curriculumid LEFT JOIN {local_curriculum_semesters} lcs
            ON lcs.id = lcsc.semesterid
            JOIN {local_program} p ON p.curriculumid = lc.id WHERE p.id = $programid";

            $semisters = $DB->get_records_sql($semisters_sql);

            $semarray = array();
            foreach($semisters as  $key => $semister) {
                $data = array();
                $courses = array();

                //Get the courses based on semester
                $cousresql = $DB->get_records_sql("SELECT  DISTINCT(c.id),c.fullname FROM {local_curriculum} lc JOIN {local_cc_semester_courses} lcsc ON lcsc.curriculumid=lc.id JOIN {course} c ON c.id=lcsc.courseid WHERE lcsc.semesterid =$semister->semesterid");

                $data['name'] = $semister->semester;
                $data['semister_id'] = $semister->semesterid;

                foreach($cousresql as $result){
                    $course = array();
                    $course['coursename']  =$result->fullname;
                    $course['courseid']  =$result->id;
                    $courses[] = $course;
                }

                $data['courses'] = $courses;
                $semarray[] = $data;
        }

        $data = [
            'userid' => $USER->id,
            'semname' => $semarray,
            'url' => $CFG->wwwroot,
            'curriculumid' => $curriculumid,
            'curriculumname' => $curriculumname,
        ];
        return $data;

    }
}
