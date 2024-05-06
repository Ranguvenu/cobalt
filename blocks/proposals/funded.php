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
// require_once('fpdf_tpl.php');
require_once($CFG->dirroot. '/lib/tcpdf/tcpdf.php');
// require_once('fpdi_pdf_parser.php');
 
require_once($CFG->libdir.'/pdflib.php');
 
require_once($CFG->dirroot.'/blocks/proposals/classes/form/fundedform.php');
global $DB , $USER,$PAGE;
$PAGE->requires->css('/blocks/proposals/css/styles.css');

defined('MOODLE_INTERNAL') || die();
require_login(); 
$PAGE->set_url(new moodle_url('/blocks/proposals/funded.php'));
$PAGE->requires->js('/blocks/proposals/js/toggle.js',true); 
 

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

$type  = $DB->get_record('applicationtable',array('id'=>1));

$mform = new funded();

if ($mform->is_cancelled()) {
        redirect($CFG->wwwroot, 'You cancelled the submission');
} else if ($fromform = $mform->get_data()) { 

        if ($fromform->submitbutton == 'Submit') {
         $item = $fromform->attachments;
    if ($draftitemid = file_get_submitted_draft_itemid('attachments')) {
        file_save_draft_area_files($draftitemid,
                                   $context->id,
                                   'block_proposals',
                                   'attachment',
                                   $item,
                                   array('subdirs' => 0,
                                         'maxfiles' => 1,
                                         'accepted_types' => '*'));
    }
            $recordtoinsert = new stdclass();
            // $date = date("l jS \of F Y h:i:s A");
             $recordtoinsert->title = $fromform->title;
            $recordtoinsert->applicationtype = $type->id;
            $recordtoinsert->rationale = $fromform->rationale;
            $recordtoinsert->departmentname = $fromform->departmentname;
            $recordtoinsert->projectdescription = $fromform->projectdescription;
            $recordtoinsert->noveltyinnovation = $fromform->noveltyinnovation;
            $recordtoinsert->strength = $fromform->strength;
            $recordtoinsert->departmentsupport = $fromform->departmentsupport;
            $recordtoinsert->financialsupport = $fromform->financialsupport;
            $recordtoinsert->attachments = $fromform->attachments;
            $recordtoinsert->draft = f1;
            $recordtoinsert->userid = $userid;
            $recordtoinsert->time = date("l jS \of F Y h:i:s A");
            $DB->insert_record('submissions', $recordtoinsert);
                
        redirect($CFG->wwwroot . '/blocks/proposals/view.php', 'work submitted successfully');
    }
}
    if ($data = $mform->get_submitted_data()) {
         if ($data->submitbutton == 'Save_to_Draft') {
        $item = $data->attachments; 
    if ($draftitemid = file_get_submitted_draft_itemid('attachments')) {
        file_save_draft_area_files($draftitemid,
                                   $context->id,
                                   'block_proposals',
                                   'attachment',
                                   $item,
                                   array('subdirs' => 0,
                                         'maxfiles' => 1,
                                         'maxaccepted_types' => 'zip'));
    }
            $recordtoinsert = new stdclass();
            $recordtoinsert->title = $data->title;
            $recordtoinsert->applicationtype = $type->id;
            $recordtoinsert->rationale = $data->rationale;
            $recordtoinsert->departmentname = $data->departmentname;
            $recordtoinsert->projectdescription = $data->projectdescription;
            $recordtoinsert->noveltyinnovation = $data->noveltyinnovation;
            $recordtoinsert->strength = $data->strength;
            $recordtoinsert->departmentsupport = $data->departmentsupport;
            $recordtoinsert->financialsupport = $data->financialsupport;
            $recordtoinsert->attachments = $item;
            $recordtoinsert->draft = f0;
            $recordtoinsert->time = date("l jS \of F Y h:i:s A");
            $recordtoinsert->userid = $userid;
            $DB->insert_record('submissions', $recordtoinsert);
        redirect($CFG->wwwroot . '/blocks/proposals/view.php', 'Successfully saved in draft');
        }
    }
$templatecontext = (object)[ 
        'apptype'=>$type->applicationtype,
        'back' => $url,
        'user' => $username->firstname,
        'school' => $schoolname, 
        'email' => $email,
        'program'=> $programname,
        'fundurl' =>$fundurl,
        'nonfundurl' =>$nonfundurl,
    ];
    echo $OUTPUT->header();
    echo $OUTPUT->render_from_template('block_proposals/form' , $templatecontext);
$mform->display();
echo $OUTPUT->footer();
