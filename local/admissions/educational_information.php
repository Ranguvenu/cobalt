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
 * @subpackage local_admissions
 */

require(dirname(dirname(dirname(__FILE__))) . '/config.php');

use \local_admissions\action\admissions as admissions;
use \local_admissions\form\educational_informationform as educational_informationform;
use \local_admissions\local\lib;

global $USER, $CFG, $DB, $PAGE;
$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/local/admissions/js/copytext.js'));

$id = optional_param('id', 0, PARAM_INT);
$programid = optional_param('programid', 0, PARAM_INT);

$returnurl = new moodle_url('/local/admissions/educational_information.php');

$url_id = $DB->record_exists('local_users', array('id' => $id));
$url_programid = $DB->record_exists('local_program', array('id' => $programid));
if (!isloggedin() || is_siteadmin()) {
	$params = array();
	if ($id) {
		$params['id'] = $url_id;
		$PAGE->set_url('/local/admissions/educational_information.php', $params);
		if (!$url_id) {
			throw new moodle_exception(get_string('invalidid', 'local_admissions'));
		}
	} else if ($programid) {
		$params['programid'] = $url_programid;
		$PAGE->set_url('/local/admissions/educational_information.php', $params);
		if (!$url_programid) {
			throw new moodle_exception(get_string('invalidprogramid', 'local_admissions'));
		}
	}

	// if (!isloggedin() || is_siteadmin()) {
	$PAGE->set_url('/local/admissions/educational_information.php', array('id' => $id));
	$title = get_string('educationalinfo', 'local_admissions');
	$PAGE->set_title($title);
	$context =  \context_system::instance();
	$PAGE->requires->jquery();
	$PAGE->set_context($context);
	$admissionsprograms = get_string('viewadmission', 'local_admissions');
	$programbadges = get_string('index', 'local_admissions');
	$PAGE->navbar->add($admissionsprograms, new moodle_url('/local/admissions/index.php'));
	$PAGE->navbar->add($programbadges, new moodle_url('/local/admissions/index.php'), array('id' => $id));
	$PAGE->navbar->add($title);
	$PAGE->set_heading($title);
	$PAGE->set_pagelayout('secure');

	$uploaddocsfile = $_FILES;
	$_REQUEST['files'] = $_FILES;

	$batchid = $DB->get_field('local_program', 'batchid', array('id' => $programid));
	echo $OUTPUT->header();

	$returnurl = new moodle_url('/local/admissions/contact_information.php', array());
	$actionurl = new moodle_url('/local/admissions/personal_information.php');
	$adminreturnurl = new moodle_url('/local/admissions/view.php');
	$mform = new educational_informationform(null, array('id' => $id, 'programid' => $programid, 'batchid' => $batchid));

	if ($mform->is_cancelled()) {
		if (is_siteadmin() || isloggedin()) {
			redirect($adminreturnurl);
		} else {
			redirect($returnurl);
		}
	} else if ($formdata = $mform->get_data()) {
		if (isset($uploaddocsfile['uploaddocs']) && ($uploaddocsfile['uploaddocs']['name'] != "")) {
			$formdata->uploaddocs = admissions::uploaddocs_store($uploaddocsfile);
			if (is_siteadmin() || isloggedin()) {
				$ruleid = (new lib)->save_adminapprovels_educationaldetails($formdata);
			} else {
				$ruleid = (new lib)->save_educationaldetails($formdata);
			}
		}
		if (is_siteadmin() || isloggedin()) {

			redirect(new moodle_url('/local/admissions/adminsubmitdata.php', array('id' => $formdata->id)));
		} else {

			redirect(new moodle_url('/local/admissions/submitteddata.php', array('id' => $formdata->id)));
		}
	} else {
		if ($id) {
			$uploaddocs = (new lib)->get_uploadeddocument_src($id);

			$data = $DB->get_record('local_users', array('id' => $id));

			$localuserinfo = $DB->get_records('local_admissions', array('admissionid' => $id));

			if ($localuserinfo) {
				$i = 0;
				foreach ($localuserinfo as $localuser) {
					$edudetails->coursename[$i] = $localuser->coursename;
					$edudetails->university[$i] = $localuser->university;
					$edudetails->yearofpassing[$i] = $localuser->yearofpassing;
					$edudetails->percentage[$i] = $localuser->percentage;
					$i++;
				}
			}

			$setdata = (object) array_merge((array) $data, (array) $edudetails);

			$mform->set_data($setdata);
		}
		$mform->display();
	}

	echo $OUTPUT->footer();
} else {
	throw new moodle_exception(get_string('permissiondenied', 'local_admissions'));
}
