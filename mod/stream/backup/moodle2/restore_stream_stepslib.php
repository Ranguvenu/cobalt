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
 * @package   mod_stream
 * @category  backup
 * @copyright 2021 eAbyas Info Solutions
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the restore steps that will be used by the restore_stream_activity_task
 */
// require_once($CFG->dirroot.'/repository/stream/contentlib.php');
/**
 * Structure step to restore one content activity
 */
class restore_stream_activity_structure_step extends restore_activity_structure_step {

	protected function define_structure() {

        $paths = array();
        $paths[] = new restore_path_element('stream', '/activity/stream');

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_stream($data) {
        global $DB;
        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();
        $newitemid = $DB->insert_record('stream', $data);
       
        $this->apply_activity_instance($newitemid);
    }

    protected function after_execute() {
        // Add content related files, no need to match by itemname (just internally handled context)
     
    }
}