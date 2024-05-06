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
 * Attendance module renderable component.
 *
 * @package    local_attendance
 * @copyright  2023 Dipanshu Kasera
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_attendance\output;

use renderable;
use local_attendance_structure;

/**
 * Output a selector to change between status sets.
 *
 * @copyright  2023 Dipanshu Kasera
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class set_selector implements renderable {
    /** @var int  */
    public $maxstatusset;
    /** @var local_attendance_structure  */
    private $att;

    /**
     * set_selector constructor.
     * @param local_attendance_structure $att
     * @param int $maxstatusset
     */
    public function __construct(local_attendance_structure $att, $maxstatusset) {
        $this->att = $att;
        $this->maxstatusset = $maxstatusset;
    }

    /**
     * url helper
     * @param array $statusset
     * @return moodle_url
     */
    public function url($statusset) {
        $params = array();
        $params['statusset'] = $statusset;

        return $this->att->url_preferences($params);
    }

    /**
     * get current statusset.
     * @return int
     */
    public function get_current_statusset() {
        if (isset($this->att->pageparams->statusset)) {
            return $this->att->pageparams->statusset;
        }
        return 0;
    }

    /**
     * get statusset name.
     * @param int $statusset
     * @return string
     */
    public function get_status_name($statusset) {
        return local_attendance_get_setname($this->att->id, $statusset, true);
    }
}
