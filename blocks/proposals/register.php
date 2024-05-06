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
 * Version details.
 *
 * @package    block_proposals
 * @copyright  moodle
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot. '/blocks/proposals/classes/form/form.php');
$PAGE->requires->css('/blocks/proposals/css/styles.css');
global $DB , $USER;
defined('MOODLE_INTERNAL') || die();
require_login();
$PAGE->set_url(new moodle_url('/blocks/proposals/register.php'));
$PAGE->requires->js('/blocks/proposals/js/toggle.js');
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_title('Forms');
$PAGE->set_heading('Submissions');
$userid = $USER->id;
$url = $CFG->wwwroot;
$fundurl = $CFG->wwwroot.'/blocks/proposals/view.php';
$nonfundurl = $CFG->wwwroot.'/blocks/proposals/view.php';
$username = $DB->get_record('user' , [ 'id' => $userid]);
$email = $username->email;
$schoolname = $DB->get_field('local_costcenter','fullname',array('id'=>$username->open_costcenterid));
$program = $DB->get_record_sql("SELECT name FROM {local_program} as lp JOIN {local_costcenter} as lc ON lp.costcenter = lc.id");

$programname = $program->name;
$mform = new newform();
 
$templatecontext = (object)[
        // 'url' => $editurl,
        
        'back' => $url,
        'user' => $username->firstname,
        // 'rollnumber' =>$rollnumber,
        'school' => $schoolname,
        'email' => $email,
        'program'=> $programname,
        'fundurl' =>$fundurl,
        'nonfundurl' =>$nonfundurl,
    ];
    echo $OUTPUT->header();
    echo $OUTPUT->render_from_template('block_proposals/form2' , $templatecontext);
$mform->display();
echo $OUTPUT->footer();
