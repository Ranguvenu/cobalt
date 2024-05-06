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
 * This file contains the news item block class, based upon block_base.
 *
 * @package    block_semester_progress
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot . '/blocks/semester_progress/lib.php');

class block_semester_progress extends block_base {
    function init() {
        $this->title = get_string('pluginname', 'block_semester_progress');
    }
    public function get_content() {
        global $CFG, $USER, $PAGE, $OUTPUT, $DB;
        require_login();
        if ($this->content !== null) {
            return $this->content;
        }

        $systemcontext = context_system::instance();
              
        /* Completed semester code starts*/
        $pid = $DB->get_field('local_program_users', 'programid', ['userid' => $USER->id]);
        $cmdata = new \block_semester_progress\semester_progress_lib();
        $completedsem = $cmdata->cm_semester($pid, $semid = false);
        $currentsem = $cmdata->current_semester();

        if (!isset($completedsem['empty'])) {
            $data = 1;
        } else {
            $data = 0;
        }

        $usercourses = [
            'currentsem' => $currentsem,
            'completedsem' => $completedsem,
            'data' => $data
        ];

        $users = $DB->get_records_sql("SELECT DISTINCT(r.id),r.shortname FROM {role} r
                                JOIN {role_assignments} ra ON ra.roleid = r.id
                                JOIN {user} u ON u.id = ra.userid
                                WHERE u.id = {$USER->id} AND r.shortname = 'student'");
        if ($users) {
            $this->content = new \stdClass();
            // $this->content->text = $OUTPUT->render_from_template('block_semester_progress/block_semester_progress', $usercourses);
            $this->content->text = $OUTPUT->render_from_template('block_semester_progress/tabs', $usercourses);
        }
        return $this->content;
    }
}
