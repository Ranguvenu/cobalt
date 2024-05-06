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
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * Version information
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author eabyas  <info@eabyas.in>
 * @package ODL
 * @package    local_admissions
 * @copyright  2023 eAbyas Info Solutions Pvt. Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
global $CFG, $DB, $PAGE, $OUTPUT;
        require_login();
        $id = required_param('id', PARAM_INT);
        $context = context_system::instance();
        $PAGE->set_context($context);
        $url = new moodle_url($CFG->wwwroot . '/local/admissions/adminsubmitdata.php', array());

        $admissionsprograms = get_string('viewadmission', 'local_admissions');
        $programbadges = get_string('adminsubmit', 'local_admissions');
        $PAGE->set_title(get_string('manage_admissions', 'local_admissions'));
        $PAGE->navbar->add($admissionsprograms, new moodle_url('/local/admissions/view.php'));
        $PAGE->navbar->add($programbadges, new moodle_url('/local/admissions/view.php'), array('id'=>$id));
        // $PAGE->navbar->add($title);
        $PAGE->set_heading($title);
        $PAGE->set_pagelayout('standard');

        $sql = "SELECT u.id, CONCAT(u.firstname, ' ',u.lastname) as fullname, p.name, u.registrationid
                 FROM {local_users} u
                 JOIN {local_program} p ON u.programid = p.id
                WHERE u.id = $id";
        $studentdetails = $DB->get_record_sql($sql);
        $studentname = $studentdetails->fullname;
        $data = new \stdClass();
        $data->studentname = $studentdetails->fullname;
        $data->programname = $studentdetails->name;
        $data->registrationid = $studentdetails->registrationid;

        if (is_siteadmin()) {
                $label = true;
                $fourthmessage = get_string('fourthmessage', 'local_admissions', $data);
        }

        $successmessage = [

                'fourthmessage' => $fourthmessage,
                'label' => $label,
                'viewurl' => $CFG->wwwroot. '/local/admissions/view.php',

        ];
        echo $OUTPUT->header();
        echo $OUTPUT->render_from_template('local_admissions/submitteddata', $successmessage);
        echo $OUTPUT->footer();

