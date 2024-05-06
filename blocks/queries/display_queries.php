<?php

/**
 * This file is part of eAbyas
 *
 * Copyright eAbyas Info Solutons Pvt Ltd, India
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author eabyas  <info@eabyas.in>
 * @subpackage block_queries
 */

require_once(dirname(__FILE__).'/../../config.php');
use block_queries\local\lib;
require_once($CFG->dirroot.'/local/lib.php');
require_once($CFG->dirroot.'/blocks/queries/lib.php');
require_once($CFG->dirroot.'/blocks/queries/commentform.php');
require_login();

global $PAGE, $USER, $DB, $CFG, $OUTPUT;
$studentid = optional_param('studentid', null, PARAM_INT);
$querieuserid = optional_param('userid', null, PARAM_INT);
$querieid = optional_param('querieid', null, PARAM_INT);

$PAGE->set_url('/blocks/queries/display_queries.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('myqueries', 'block_queries'));
$PAGE->navbar->add(get_string('myqueries', 'block_queries'));

$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('myqueries', 'block_queries'));

// $PAGE->navbar->add(get_string('dashboard', 'block_queries'), new moodle_url('/my/index.php'), array());
$PAGE->navbar->add(get_string('askaquestion', 'block_queries'), new moodle_url('/blocks/queries/view.php'), array());
$PAGE->navbar->add(get_string('myqueries', 'block_queries'));

$systemcontext = context_system::instance();
$renderer = $PAGE->get_renderer('block_queries');
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->js_call_amd('block_queries/form_popup', 'init');
$PAGE->requires->js_call_amd('block_queries/confirm');
$PAGE->requires->js_call_amd('block_queries/queriedatatable', 'queriedatatable', array());

$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/blocks/queries/js/queryResponse.js'));

$studentrole = identify_role($USER->id);
$teacherrole = identify_teacher_role($USER->id);
if (!is_siteadmin()) {
   if ($teacherrole->shortname == 'editingteacher' && $querieuserid != $USER->id) {
      throw new Exception("You dont have permission to access");
   } else if ($studentrole->shortname == 'student' && $studentid != $USER->id && !$teacherrole->shortname == 'editingteacher') {
      throw new Exception("You dont have permission to access");
   }   
}

echo $OUTPUT->header();

echo $renderer->querie_recordstodisplay($studentid, $querieid);
echo $renderer->faculty_records($querieuserid, $querieid);

if ((is_siteadmin() || has_capability('block/queries:manage', $systemcontext))) {
   echo $renderer->get_adminquerie_records($querieid);
}
echo $OUTPUT->footer();
