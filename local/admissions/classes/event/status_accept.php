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
 * The local_admissions post users_created event.
 *
 * @package    local_admissions
 * @copyright  2018 Arun Kumar M <arun@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_admissions\event;
use stdclass;
defined('MOODLE_INTERNAL') || die();

/**
 * The local_admissions post users_created event class.
 *
 * @package    local_admissions
 * @since      Moodle 4.0
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class status_accept extends \core\event\base {
    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'local_users';
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        global $DB;
        
        $stringHelpers = new stdClass();
        $stringHelpers->userid = $this->userid;
        $stringHelpers->objectid = $this->objectid;
        $stringHelpers->other = $this->other['programid, email, status'];
        return get_string('acceptinfo', 'local_admissions', $stringHelpers);
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventadmission_users_accepetd', 'local_admissions');
    }

    /**
     * Get URL related to the action
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/local/program/enrollusers.php', array('bcid' => $this->other['programid']));
    }
}
