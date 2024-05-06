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
 * Class containing data for the Recently accessed courses block.
 *
 * @package    block_recentlyaccessedcourses
 * @copyright  2018 Victor Deniz <victor@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_myprograms\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;

/**
 * Class containing data for Recently accessed courses block.
 *
 * @package    block_recentlyaccessedcourses
 * @copyright  2018 Victor Deniz <victor@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
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

        $nocoursesurl = $output->image_url('courses', 'block_myprograms')->out(false);
        $config = get_config('block_myprograms');
        $sql = $DB->get_records_sql("SELECT me.name,me.id,me.description
              FROM {local_program_users} AS mue
             INNER JOIN {local_program} AS me
        WHERE mue.programid = me.id AND mue.userid =".$userid."");
        foreach ($sql as $program) {
            $programname= $program->name;
            $levels = 'level1';
            $programid= $program->id;
            $des = strip_tags($program->description);

         }
         $semesterscountsql = "SELECT count(level) FROM {local_program_levels} WHERE programid = $programid";
         $programsemesterscount = $DB->count_records_sql($semesterscountsql);
         $semcoursessql = "SELECT count(courseid) FROM {local_program_level_courses} WHERE programid = $programid";
         $semcoursescount = $DB->count_records_sql($semcoursessql);
         if (!$semcoursescount) {
              $semcoursescount = "N/A";
         }
         else {
              $semcoursescount;
         }
         $semestersql = "SELECT level,active FROM {local_program_levels} WHERE programid = $programid";
         $semestersresult = $DB->get_records_sql($semestersql);

        return [
            'userid' => $USER->id,
            'programname' => $programname,
            'levels' => $levels,
            'semcount' => $programsemesterscount,
            'semcoursescount' => $semcoursescount,
            'semesters' => array_values($semestersresult),
            'programid' => $programid,
            'description' =>$des,
            'url' => $CFG->wwwroot,
            'nocoursesimgurl' => $nocoursesurl,
            'pagingbar' => [
                'next' => true,
                'previous' => true
            ],
            'displaycategories' => !empty($config->displaycategories)
        ];
    }
}
