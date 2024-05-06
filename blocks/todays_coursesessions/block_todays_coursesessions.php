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
 * displaying scheduled todays session and previous session
 *
 * @package    block_todays_coursesessions
 * @copyright  2015 hemalatha c arun {hemalatha@eabyas.in}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_todays_coursesessions extends block_base {
    // Initialisation of block.
    public function init() {
        $this->title = get_string('todaycoursesessions', 'block_todays_coursesessions');
        // .'<span style="color: #C8243F;">' . date('D, d M Y').'</span>';
    }
    // Return the content of this block.
    public function get_required_javascript() {
        $this->page->requires->jquery();
        $this->page->requires->js('/blocks/todays_coursesessions/js/jqueryy1.js', true);
        $this->page->requires->js('/blocks/todays_coursesessions/js/jquery10.js');
        $this->page->requires->js('/blocks/todays_coursesessions/js/tabs.js');
        $this->page->requires->js_call_amd('block_todays_coursesessions/table', 'load', array());
    }
    public function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }
        global $CFG, $USER, $DB, $OUTPUT;
        require_once($CFG->dirroot.'/blocks/todays_coursesessions/lib.php');
        $costcenterid = optional_param('costcenterid', '', PARAM_INT);
        $params = array();
        $params['editingteacher'] = 'editingteacher';
        $params['userid'] = $USER->id;
        $teacherdetails = "SELECT DISTINCT(r.id),r.shortname
                              FROM {local_costcenter} c
                              JOIN {user} u ON u.open_costcenterid = c.id
                              JOIN {role_assignments} ra ON ra.userid = u.id
                              JOIN {role} r ON r.id=ra.roleid
                             WHERE ra.userid = :userid AND r.shortname = :editingteacher";
        $teachers = $DB->get_records_sql($teacherdetails, $params);
        if ($teachers) {
            $this->content = new \stdClass();
            $this->content->text[] = block_todayssession_instructor_sessions_view();
            $this->content->text = implode('', $this->content->text);
            return $this->content;
        }
    }
}

