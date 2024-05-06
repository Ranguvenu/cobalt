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
 * Class preferences data.
 *
 * @copyright  2023 Dipanshu Kasera
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class preferences_data implements renderable {
    /** @var array  */
    public $statuses;
    /** @var local_attendance_structure  */
    private $att;
    /** @var array  */
    public $errors;

    /**
     * preferences_data constructor.
     * @param local_attendance_structure $att
     * @param array $errors
     */
    public function __construct(local_attendance_structure $att, $errors) {
        $this->statuses = $att->get_statuses(false);
        $this->errors = $errors;

        foreach ($this->statuses as $st) {
            $st->haslogs = local_attendance_has_logs_for_status($st->id);
        }

        $this->att = $att;
    }

    /**
     * url helper function
     * @param array $params
     * @param bool $significantparams
     * @return moodle_url
     */
    public function url($params=array(), $significantparams=true) {
        if ($significantparams) {
            $params = array_merge($this->att->pageparams->get_significant_params(), $params);
        }

        return $this->att->url_preferences($params);
    }
}
