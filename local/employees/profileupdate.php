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
 * @package
 * @subpackage local_employees
 */

require(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->dirroot . '/lib/formslib.php');
$SESSION->profileupdate = false;
$id = optional_param('id', $USER->id, PARAM_INT);
$userdata = $DB->get_record('user', array('id'=>$USER->id));
$context =  context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('incourse');
    if($userdata->profileupdate == 1){
    $PAGE->set_url('/local/employees/profileupdated.php');
    }else{
    $PAGE->set_url('/local/employees/profileupdate.php');
    }
    $strinscriptions = get_string('profileupdate', 'local_employees');

    $PAGE->set_title($strinscriptions);
    $PAGE->set_heading($strinscriptions);
    $params=array();

    if($userdata->profileupdate == 1){
    $PAGE->requires->js_call_amd('local_employees/profileupdate', 'thankyou', array(array('context' => $context->id, 'id' => $USER->id)));
    }
    else{
    $PAGE->requires->js_call_amd('local_employees/profileupdate', 'init', array(array('selector' => 'createnotificationmodal','context' => $context->id, 'id' => $USER->id, 'form_status' => 0)));
}
    echo $OUTPUT->header();
    echo $OUTPUT->footer();
