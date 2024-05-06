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

global $DB , $USER;
require_login();
$PAGE->set_url(new moodle_url('/blocks/proposals/edit.php'));

$context = context_system::instance();

$formid = optional_param('formid' , null , PARAM_INT);



if($formid){
    $formdata = $DB->get_record('submissions', ['id' => $formid]);

    if ($formdata->applicationtype == 1 && $formdata->draft == 'f1') {
        redirect($CFG->wwwroot . '/blocks/proposals/fundededit.php?formid='.$formid);
    }
    else if($formdata->applicationtype == '' && $formdata->draft == 'f0'){
    	redirect($CFG->wwwroot . '/blocks/proposals/fundededit.php?formid='.$formid);
    }
    else if($formdata->applicationtype == 1 && $formdata->draft == 'f0'){
        redirect($CFG->wwwroot . '/blocks/proposals/fundededit.php?formid='.$formid);
    }
    // else if($formdata->applicationtype <> 1 && $formdata->draft == 'n1'){
    // 	redirect($CFG->wwwroot . '/blocks/proposals/nonfundededit.php?formid='.$formid);
    // }
    else if($formdata->applicationtype == 6 && $formdata->draft == 'nres0' || $formdata->draft == 'nres1' ){
        redirect($CFG->wwwroot . '/blocks/proposals/fundedresedit.php?formid='.$formid);
    }
    else if($formdata->applicationtype == 2 || $formdata->applicationtype == 3 || $formdata->applicationtype == 4|| $formdata->applicationtype == 5 || $formdata->applicationtype == null && $formdata->draft == 'n0'){
    	redirect($CFG->wwwroot . '/blocks/proposals/nonfundededit.php?formid='.$formid);
    }
}

echo $OUTPUT->header();

echo $OUTPUT->footer();
