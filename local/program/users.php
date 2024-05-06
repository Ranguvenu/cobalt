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
 * @package
 * @author     eAbyas Info Solutions
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->dirroot.'/local/program/lib.php');
use local_program\program;

$programid = required_param('bcid', PARAM_INT);
$download = optional_param('download', 0, PARAM_INT);
$type = optional_param('type', '', PARAM_RAW);
$search = optional_param_array('search', '', PARAM_RAW);

require_login();
$context = context_system::instance();
$program = $DB->get_record('local_program', array('id' => $programid));
if (empty($program)) {
    print_error('noprograms', 'local_program');
}
if ((has_capability('local/program:manageprogram', $context)) && (!is_siteadmin()
    && (!has_capability('local/program:manage_multiorganizations', $context)
        && !has_capability('local/costcenter:manage_multiorganizations', $context)))) {
        if($program->costcenter!=$USER->open_costcenterid){
         print_error("You donot have permissions");
        }

        if (/*(has_capability('local/program:manage_owndepartments', $context)
         || */!is_siteadmin() && has_capability('local/costcenter:manage_ownorganization', $context)) {
            if($program->costcenter!=$USER->open_costcenterid){
                print_error("You donot have permissions");
            }
        }
}
$PAGE->set_context($context);
$url = new moodle_url($CFG->wwwroot . '/local/program/users.php', array('bcid' => $programid));
$PAGE->set_title($program->name. ': '. get_string('users'));
$PAGE->requires->js_call_amd('local_program/program', 'UsersDatatable',
                    array(array('programid' => $programid)));
$renderer = $PAGE->get_renderer('local_program');
$PAGE->set_url($url);
$PAGE->navbar->add(get_string("pluginname", 'local_program'), new moodle_url('/local/program/index.php'));
$PAGE->navbar->add($program->name, new moodle_url('/local/program/view.php', array('bcid' => $programid)));
$PAGE->navbar->add(get_string("users", 'local_program'));
$PAGE->set_heading($program->name);

$batch=$DB->get_record_sql("SELECT c.id,c.name FROM {cohort} c 
                            JOIN {local_program} p ON c.id=p.batchid WHERE p.id=$program->id");
if(!$download) {
	echo $OUTPUT->header();
	$stable = new stdClass();
	$stable->thead = true;
	$stable->start = 0;
	$stable->length = -1;
	$stable->search = '';
        $stable->batchid = $batch->id;
	$stable->programid = $programid;
	echo $renderer->viewprogramusers($stable);
	echo $OUTPUT->footer();
} else {
     $exportplugin = $CFG->dirroot . '/local/program/export_xls.php';
     if (file_exists($exportplugin)) {
         require_once($exportplugin);
         if(!empty($programid)){
         	$stable = new stdClass();
			$stable->thead = true;
			$stable->start = 0;
			$stable->length = -1;
			$stable->search = '';
			$stable->programid = $programid;
         	export_report($programid, $stable, $type);
         }
     }
     die;
}
