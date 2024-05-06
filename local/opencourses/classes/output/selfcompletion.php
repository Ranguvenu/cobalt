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
 * local courses
 *
 * @package    local_courses
 * @copyright  2022 eAbyas <eAbyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

namespace local_courses\output;

use local_courses\plugin;
use context_course;
use renderable;
use renderer_base;
use stdClass;
use templatable;
use moodle_url;
use moodle_exception;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/completionlib.php');

final class selfcompletion implements renderable, templatable {

    /** @var stdClass|null */
    private $courseid;

    private $userid;

    /**
     * blockview constructor.
     * @param stdClass|null $config
     */
    public function __construct($courseid,$userid) {
        $this->courseid = $courseid;
        $this->userid = $userid;
    }

    /**
     * Generate template
     * @param renderer_base $output
     * @return array
     * @throws moodle_exception
     */
    public function export_for_template(renderer_base $output) {
        global $DB, $CFG, $USER;

        $course = $DB->get_record('course', array('id' => $this->courseid));
        
        $context = array('courseid' => $this->courseid, 'userid' => $this->userid, 'fullname' => format_string($course->fullname), 'disabled' => true);

        // Get course completion data.
        $info = new \completion_info($course);
        // Get this user's data.
        $completion = $info->get_completion($this->userid, COMPLETION_CRITERIA_TYPE_SELF);
        // Check if self completion is one of this course's criteria.
        if (empty($completion)) {
            $context['tittle'] = get_string('selfcompletionnotenabled', 'block_selfcompletion');
            return $context;
        }
        // Check this user is enroled.
        if (!$info->is_tracked_user($this->userid)) {

            $context['tittle'] = get_string('nottracked', 'completion');
            return $context;
        }

        // Is course complete?.
        if ($info->is_course_complete($this->userid)) {

            $context['tittle'] = get_string('coursealreadycompleted', 'completion');
            return $context;
        // Check if the user has already marked themselves as complete.
        } else if ($completion->is_complete()) {

            $context['tittle'] = get_string('alreadyselfcompleted', 'block_selfcompletion');
            $context['iscompleted'] = true;
            return $context;
        // If user is not complete, or has not yet self completed.
        } else {
            $context['tittle'] = get_string('selfcompletion', 'local_courses');
            $context['disabled'] = false;
            return $context;
        }
    }
}
