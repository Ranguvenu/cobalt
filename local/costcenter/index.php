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
 * Version information
 *
 * @package    local_costcenter
 * @copyright  2022 eAbyas Info Solutions Pvt. Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');

global $CFG, $USER, $PAGE, $OUTPUT;
require_once($CFG->dirroot . '/local/costcenter/lib.php');

$PAGE->requires->css('/local/costcenter/css/jquery.dataTables.min.css');
$PAGE->requires->js_call_amd('local_costcenter/costcenterdatatables', 'costcenterDatatable', array());
$PAGE->requires->js_call_amd('theme_bloom/quickactions', 'quickactionsCall');
require_login();

$systemcontext = context_system::instance();
if (!has_capability('local/costcenter:view', $systemcontext)) {
    throw new moodle_exception('nopermissiontoviewpage');
}

if (!((is_siteadmin()) || !has_capability('local/costcenter:manage_multiorganizations', $systemcontext))) {
    if (has_capability('local/costcenter:manage_ownorganization', $systemcontext)) {
        redirect($CFG->wwwroot . '/local/costcenter/costcenterview.php?id='.$USER->open_costcenterid);
    } else {
        redirect($CFG->wwwroot . '/local/costcenter/costcenterview.php?id='.$USER->open_departmentid);
    }
}

$PAGE->set_pagelayout("standard");
$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/costcenter/index.php');
$labelstring = get_config('local_costcenter');
// print_object($labelstring);exit;
$PAGE->set_heading(get_string('orgmanage', 'local_costcenter', $labelstring));
$PAGE->set_title(get_string('orgmanage', 'local_costcenter', $labelstring));
$PAGE->navbar->add(get_string('orgmanage', 'local_costcenter', $labelstring));
$PAGE->requires->js_call_amd('local_costcenter/newcostcenter', 'load', array());

$PAGE->requires->js_call_amd('local_costcenter/newsubdept', 'load', array());

echo $OUTPUT->header();

$renderer = $PAGE->get_renderer('local_costcenter');
echo $renderer->get_dept_view_btns();
echo $renderer->departments_view();

echo $OUTPUT->footer();
