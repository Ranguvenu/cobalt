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
 * @copyright  2023 Dipanshu kasera
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_attendance\local;

use local_attendance_structure;

/**
 * Url helpers
 *
 * @copyright  2023 Dipanshu kasera
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class url_helpers {
    /**
     * Url take.
     * @param mod_attendance_structure $att
     * @param int $sessionid
     * @param int $grouptype
     * @return mixed
     */
    public static function url_take($att, $sessionid, $grouptype) {
        $params = array('sessionid' => $sessionid);
        if (isset($grouptype)) {
            $params['grouptype'] = $grouptype;
        }

        return $att->url_take($params);
    }

    /**
     * Must be called without or with both parameters
     * @param mod_attendance_structure $att
     * @param null $sessionid
     * @param null $action
     * @return mixed
     */
    public static function url_sessions($att, $sessionid=null, $action=null) {
        if (isset($sessionid) && isset($action)) {
            $params = array('sessionid' => $sessionid, 'action' => $action);
        } else {
            $params = array();
        }

        return $att->url_sessions($params);
    }

    /**
     * Url view helper.
     * @param mod_attendance_structure $att
     * @param array $params
     * @return mixed
     */
    public static function url_view($att, $params=array()) {
        return $att->url_view($params);
    }

    /*Dipanshu Kasera code starts: To display grid view as default added url_att_take*/
    /**
     * url_att_take.
     * @param mod_attendance_structure $att
     * @param int $sessionid
     * @param int $grouptype
     * @param int $viewmode
     * @return mixed
     */
    public static function url_att_take($att, $sessionid, $grouptype, $viewmode) {
        $params = array('sessionid' => $sessionid);
        if (isset($grouptype)) {
            $params['grouptype'] = $grouptype;
        }
        if (isset($viewmode)) {
            $params['viewmode'] = $viewmode;
        }

        return $att->url_att_take($params);
    }
    /*Dipanshu Kasera code ends: To display grid view as default added url_att_take*/
}