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
 * @package    block_todays_timetable
 * @copyright  2015 hemalatha c arun {hemalatha@eabyas.in}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_todays_timetable extends block_base {
    // Initialisation of block.
    public function init() {
        $this->title = get_string('todays_sessions', 'block_todays_timetable');
    }
    // Return the content of this block.
    public function get_required_javascript() {
        $this->page->requires->jquery();
        $this->page->requires->js('/blocks/todays_timetable/js/jqueryy1.js', true);
        $this->page->requires->js('/blocks/todays_timetable/js/jquery10.js');
        $this->page->requires->js('/blocks/todays_timetable/js/tabs.js');
    }
    public function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }
        global $CFG, $USER, $DB, $OUTPUT;
        require_once($CFG->dirroot.'/blocks/todays_timetable/lib.php');
        $costcenterid = optional_param('costcenterid', '', PARAM_INT);
        $teachers = $DB->get_records_sql("SELECT DISTINCT(r.id),r.shortname
                                    FROM {role} r
                                    JOIN {role_assignments} ra ON ra.roleid = r.id
                                    JOIN {user} u ON u.id = ra.userid
                                    JOIN {user_enrolments} ue on ue.userid = u.id 
                                    JOIN {enrol} e on e.id = ue.enrolid 
                                    WHERE u.id = {$USER->id}
                                    AND r.shortname = 'editingteacher'");
        if ($teachers) {
            $this->content = new \stdClass();
            $this->content->text[] = block_todaystimetable_instructor_sessions_view();
            $this->content->text = implode('', $this->content->text);
            return $this->content;
        }
    }
}

