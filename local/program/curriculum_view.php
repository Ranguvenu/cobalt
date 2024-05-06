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
 * curriculum View
 *
 * @package  local_curriculum
 * @license  http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');

require_once($CFG->dirroot.'/local/curriculum/program.php');
$curriculumid = required_param('ccid', PARAM_INT);
$type = optional_param('type',1, PARAM_INT);

$systemcontext = context_system::instance();
require_login();

if (empty($curriculumid)) {
  $PAGE->navbar->add(get_string("dashboard", 'local_program'), new moodle_url('/my/index.php')); 
   echo $OUTPUT->header();
   echo '<h4>you are not assigned to any curriculum</h4>';
   echo '<h5><a class="btn pull-right" href="'.$CFG->wwwroot.'">back</a></h5>';
   echo $OUTPUT->footer();
} else {
  $PAGE->set_url('/local/program/curriculum_view.php', array('ccid' => $curriculumid,'type'=>$type));
  $PAGE->set_context($systemcontext);
  $PAGE->set_title(get_string('curriculums', 'local_curriculum'));
  $PAGE->requires->js_call_amd('local_curriculum/ajaxforms', 'load', array());
  $PAGE->requires->js_call_amd('local_curriculum/program', 'load', array());
  $curriculum = $DB->get_record('local_curriculum', array('id' => $curriculumid));
  if(has_capability('moodle/category:manage', $systemcontext) || is_siteadmin()) {
      $navbarurl = new moodle_url('/local/curriculum/index.php');

  $PAGE->navbar->add(get_string("pluginname", 'local_curriculum'), $navbarurl);
  }
  $PAGE->navbar->add($curriculum->name);
  $PAGE->set_heading($curriculum->name);

  $PAGE->requires->jquery_plugin('ui-css');
  $PAGE->requires->css('/local/curriculum/css/jquery.dataTables.min.css', true);

  $PAGE->requires->js_call_amd('theme_bloom/quickactions', 'quickactionsCall');

  $renderer = $PAGE->get_renderer('local_curriculum');

  echo $OUTPUT->header();
  echo $renderer->curriculumview($curriculumid);
  echo $OUTPUT->footer();
}
