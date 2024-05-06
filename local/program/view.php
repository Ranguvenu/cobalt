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
require_once(dirname(__FILE__) . '/../../config.php');
use local_program\program;
$programid = required_param('bcid', PARAM_INT);
$systemcontext = context_system::instance();
require_login();
$PAGE->set_url('/local/program/view.php', array('bcid' => $programid));
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('programs', 'local_program'));

$renderer = $PAGE->get_renderer('local_program');
$program=$renderer->programview_check($programid);

if(empty($programid)){
    $PAGE->navbar->add(get_string("dashboard", 'local_program'), new moodle_url('/my/index.php')); 
    echo $OUTPUT->header();
    echo '<h4>you are not assigned to any program</h4>';
    echo '<h5><a class="btn pull-right" href="'.$CFG->wwwroot.'">back</a></h5>';
    echo $OUTPUT->footer();
}else{
    $PAGE->navbar->ignore_active();
    if ((has_capability('local/program:manageprogram', context_system::instance()) || is_siteadmin())) {
        $PAGE->navbar->add(get_string("pluginname", 'local_program'), new moodle_url('index.php'));
        $PAGE->navbar->add(get_string("programname", 'local_program',$program->name), new moodle_url('index.php'));
    }
    $PAGE->set_heading($program->name);
    $PAGE->requires->jquery_plugin('ui-css');
    $PAGE->requires->css('/local/program/css/jquery.dataTables.min.css', true);
    $PAGE->requires->js_call_amd('local_program/ajaxforms', 'load');
    $PAGE->requires->js_call_amd('theme_bloom/quickactions', 'quickactionsCall');
    $renderer = $PAGE->get_renderer('local_program');
    $content = $renderer->viewprogram($programid);
    echo $OUTPUT->header();
    echo $content;
    echo $OUTPUT->footer();
}
