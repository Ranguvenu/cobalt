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
 * local courses
 *
 * @package    local_courses
 * @copyright  2022 eAbyas <eAbyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');
global $CFG,$PAGE;
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/csvlib.class.php');
require_once($CFG->dirroot . '/local/opencourses/upload/uploadforms.php');
require_once($CFG->dirroot . '/local/opencourses/upload/processor.php');
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/opencourses/upload/index.php'));
$PAGE->set_title(get_string('uploadcourses','local_courses'));
$PAGE->requires->jquery();
$PAGE->set_heading(get_string('uploadcourses', 'local_courses'));  

$PAGE->navbar->add(get_string('manage_courses','local_courses'), new moodle_url('/local/opencourses/opencourses.php'));

$PAGE->navbar->add(get_string('uploadcourses', 'local_courses'));
if ((!has_capability('local/costcenter:create', $context) || !has_capability('local/courses:bulkupload', $context) || !has_capability('local/courses:manage', $context) || !has_capability('moodle/course:create', $context) || !has_capability('moodle/course:update', $context)) && !is_siteadmin()){
    print_error('no access to upload courses');
    exit;
}
$importid = optional_param('importid', 0, PARAM_INT);
$previewrows = optional_param('previewrows', 10, PARAM_INT);

$returnurl = new moodle_url('/local/opencourses/upload/index.php');

$STD_FIELDS = array('fullname', 'course-code',/*'category_code',*/ 'coursetype', 'summary', 'department', 
                'subdepartment', 'completiondays', 'format');

$PRF_FIELDS = array();

if (empty($importid)) {
    $mform1 = new local_uploadcourse_step1_form();
    if ($form1data = $mform1->get_data()) {
        $importid = csv_import_reader::get_new_iid('uploadcourse');
        $cir = new csv_import_reader($importid, 'uploadcourse');

        $content = $mform1->get_file_content('coursefile');
        $readcount = $cir->load_csv_content($content, $form1data->encoding, $form1data->delimiter_name);
              
        $cir->init();
        $linenum = 1;
        $emptycsv = 0;
        $progresslibfunctions = new local_courses\cron\progresslibfunctions();
        $filecolumns = $progresslibfunctions->uu_validate_course_upload_columns($cir, $STD_FIELDS, $PRF_FIELDS, $returnurl);
        
        if (empty($filecolumns[0])) {
            echo'<div class="critera_error">'.\core\notification::add(get_string('fullnamemissing','local_courses'), 
            \core\output\notification::NOTIFY_ERROR).'</div>';
            $emptycsv = 2;
        }
        if (empty($filecolumns[1]) || $filecolumns[1] == $filecolumns[0]) {
            echo'<div class="critera_error">'.\core\notification::add(get_string('coursecodemissing','local_courses'), 
            \core\output\notification::NOTIFY_ERROR).'</div>';
            $emptycsv = 2;
        }
        // if (empty($filecolumns[2]) || $filecolumns[2] == $filecolumns[1] || $filecolumns[2] == $filecolumns[0]) {
        //     echo'<div class="critera_error">'.\core\notification::add(get_string('categorymissing','local_courses'), 
        //     \core\output\notification::NOTIFY_ERROR).'</div>';
        //     $emptycsv = 2;
        // }
        if (empty($filecolumns[3]) || $filecolumns[3] == $filecolumns[2] || $filecolumns[3] == $filecolumns[1] || $filecolumns[3] == $filecolumns[0]) {
            echo '<div class="critera_error">'.\core\notification::add(get_string('coursetypemissing', 'local_courses'), 
            \core\output\notification::NOTIFY_ERROR).'</div>';
            $emptycsv = 2;
        }
        // if (empty($filecolumns[6])) {
        //     echo'<div class="critera_error">'.\core\notification::add(get_string('formatmissing','local_courses'), 
        //     \core\output\notification::NOTIFY_ERROR).'</div>';
        //     $emptycsv = 2;
        // }
        if ($emptycsv === 0) {
            if ($readcount === false) {
                print_error('csvfileerror', 'tool_uploadcourse', $returnurl, $cir->get_error());
            } else if ($readcount == 1) {

                $emptycsv = 1;
            }
        }
    } else {
        echo $OUTPUT->header();
        echo html_writer::link(new moodle_url('/local/opencourses/opencourses.php'), 'Back', array('id'=>'download_courses','class' => 'btn btn-primary '));
        echo html_writer::link(new moodle_url('/local/opencourses/upload/sample.php?format=csv'), 'Sample', 
                array('id'=>'download_courses','class' => 'btn btn-primary '));
        echo html_writer::link(new moodle_url('/local/opencourses/upload/coursehelp.php'), 'Help manual', 
                                array('id'=>'download_courses', 'class' => 'btn btn-primary ','target'=>'__blank'));
        $mform1->display();
        echo $OUTPUT->footer();
        die();
    }
} else {
    $cir = new csv_import_reader($importid, 'uploadcourse');
}

// Data to set in the form.
$data = array('importid' => $importid, 'previewrows' => $previewrows);
if (!empty($form1data)) {
    // Get options from the first form to pass it onto the second.
    foreach ($form1data->options as $key => $value) {
        $data["options[$key]"] = $value;
    }
}
$context = context_system::instance();

$mform2 = new local_uploadcourse_step2_form(null, array('contextid' => $context->id, 'columns' => $cir->get_columns(),
    'data' => $data, 'importid' => $importid,));

// If a file has been uploaded, then process it.
if ($form2data = $mform2->is_cancelled()) {
    $cir->cleanup(true);
    redirect($returnurl);
} else if ($form2data = $mform2->get_data()) {
    $options = (array) $form2data->options;
    $data = (object)$form2data->defaults;
    $data->open_coursecreator = $USER->id;
    $data->format = 'tabtopics';
    $data->enablecompletion  = 1;
    $defaults = (array) $data;

    // Restorefile deserves its own logic because formslib does not really appreciate.
    // when the name of a filepicker is an array.
    $options['restorefile'] = '';
    if (!empty($form2data->restorefile)) {
        $options['restorefile'] = $mform2->save_temp_file('restorefile');
    }
    $processor = new local_uploadcourse_processor1($cir, $options, $defaults);


    echo $OUTPUT->header();
    if (isset($form2data->showpreview)) {
        echo $OUTPUT->heading(get_string('uploadcoursespreview', 'local_courses'));
        $processor->preview($previewrows, new tool_uploadcourse_tracker(tool_uploadcourse_tracker::OUTPUT_HTML));
        $mform2->display();
    } else {
        echo $OUTPUT->heading(get_string('uploadcoursesresult', 'local_courses'));
        $processor->execute(new tool_uploadcourse_tracker(tool_uploadcourse_tracker::OUTPUT_HTML));
        echo $OUTPUT->continue_button($returnurl);
    }

    // Deleting the file after processing or preview.
    if (!empty($options['restorefile'])) {
        @unlink($options['restorefile']);
    }

} else {
    
    if (!empty($form1data)) {
        $options = $form1data->options;
    } else if ($submitteddata = $mform2->get_submitted_data()) {
        $options = (array)$submitteddata->options;

    } else {
        // Weird but we still need to provide a value, setting the default step1_form one.
        $options = array('mode' => local_uploadcourse_processor1::MODE_CREATE_NEW);
    }
    $processor = new local_uploadcourse_processor1($cir, $options, array());
   
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('uploadcoursespreview', 'local_courses'));
    if ($emptycsv == 1) {
        echo'<div class="critera_error">'.\core\notification::add(get_string('emptycsverror', 'local_courses'), 
            \core\output\notification::NOTIFY_ERROR).'</div>';
    } else if($emptycsv == 2) {
        
    } else {
        $processor->preview($previewrows, new tool_uploadcourse_tracker(tool_uploadcourse_tracker::OUTPUT_HTML));
    }
    $mform2->display();
}

echo $OUTPUT->footer();
