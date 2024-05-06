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
 * program Capabilities
 *
 * program - A Moodle plugin for managing ILT's
 * @package
 * @author     eAbyas Info Solutions
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
define('AJAX_SCRIPT', true);
require_once(dirname(__FILE__) . '/../../config.php');
global $DB, $CFG, $USER, $PAGE;

$action = required_param('action', PARAM_ACTION);
$programid = optional_param('programid', 0, PARAM_INT);
$cohortid = optional_param('cohortid', 0, PARAM_INT);
$levelid = optional_param('levelid', 0, PARAM_INT);
$start = optional_param('start', 0, PARAM_INT);
$length = optional_param('length', 10, PARAM_INT);
$search = optional_param_array('search', '', PARAM_RAW);
$programstatus = optional_param('programstatus', -1, PARAM_INT);
$programmodulehead = optional_param('programmodulehead', false, PARAM_BOOL);
$cat = optional_param('categoryname', '', PARAM_RAW);
$context = context_system::instance();
$subdepts = optional_param('subdepts', null, PARAM_RAW);
$costcenterid = optional_param('costcenterid', null, PARAM_RAW);
$departmentid = optional_param('departmentid', null, PARAM_RAW);
$program = optional_param('program', null, PARAM_RAW);
$status = optional_param('status', null, PARAM_RAW);
$groups = optional_param('groups', null, PARAM_RAW);
require_login();
$PAGE->set_context($context);
$renderer = $PAGE->get_renderer('local_program');
try{
    switch ($action) {
        case 'viewprograms':
            $stable = new stdClass();
            $stable->thead = false;
            $stable->search = $search['value'];
            $stable->start = $start;
            $stable->length = $length;
            $stable->programstatus = $programstatus;
            $return = $renderer->viewprograms($stable,$subdepts,$costcenterid,$departmentid,$program,$status,$groups);
        break;
        case 'viewprogramcourses':
            $return = $renderer->viewprogramcourses($programid);
        break;
        case 'viewprogramusers':
            $stable = new stdClass();
            $stable->search = $search['value'];
            $stable->start = $start;
            $stable->length = $length;
            $stable->programid = $programid;
            if ($programmodulehead) {
                $stable->thead = true;
            } else {
                $stable->thead = false;
            }
            $return = $renderer->viewprogramusers($stable);
        break;
         case 'viewgroupusers':
            $stable = new stdClass();
            $stable->search = $search['value'];
            $stable->start = $start;
            $stable->length = $length;
            $stable->programid = $programid;
            if ($programmodulehead) {
                $stable->thead = true;
            } else {
                $stable->thead = false;
            }
            $return = $renderer->viewprogramusers($stable,true);
        break;
          case 'viewgroupusersdata':
            $stable = new stdClass();
            $stable->search = $search['value'];
            $stable->start = $start;
            $stable->length = $length;
            $stable->cohortid = $cohortid;
 
            $return = $renderer->viewgroupusers($stable,true);
        break;
        case 'manageprogramcategory':
        $rec = new stdClass();
        $rec->fullname = $cat;
        $rec->shortname = $cat;
        if ($rec->id) {
            $DB->update_record('local_program_categories', $rec);
        } else {
            $DB->insert_record('local_program_categories', $rec);
        }
        break;
        case 'programlastchildpopup':
            $stable = new stdClass();
            $stable->search = $search['value'];
            $stable->start = $start;
            $stable->length = $length;
            if ($programmodulehead) {
                $stable->thead = true;
            } else {
                $stable->thead = false;
            }
            $return = $renderer->viewprogramlastchildpopup($programid, $stable);
        break;
        case 'viewprogramrequested_users_tab':
             $program = $DB->get_records('local_request_records', array('compname' => 'program','componentid' =>
                $programid));

            $output = $PAGE->get_renderer('local_request');
            $component = 'program';
            if ($program) {
                $return = $output->render_requestview(new local_request\output\requestview($program, $component));
            } else {
                $return = '<div class="alert alert-info">'.get_string('requestavail', 'local_program').'</div>';
            }
        break;
        case 'programlevelcourses':
             $return = $renderer->viewprogramcourses($programid, $levelid);
        break;
    }

    echo json_encode($return);
} catch (Execption $e) {
    throw new moodle_exception(get_string('programerror_in_fetching_data', 'local_program'));
}
