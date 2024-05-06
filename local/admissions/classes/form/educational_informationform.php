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

namespace local_admissions\form;

defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->dirroot . '/local/costcenter/lib.php');

use moodleform;
use context_system;
use costcenter;
use events;
use context_user;
use core_user;
use local_admissions\local\lib as lib;

class educational_informationform extends moodleform
{

    public function definition()
    {
        global $USER, $CFG, $DB, $PAGE;

        $mform = $this->_form;
        $context = context_system::instance();

        $id = $this->_customdata['id'];
        $userid = isset($this->_customdata['userid']);
        $programid = $this->_customdata['programid'];
        $status = isset($this->_customdata['status']);
        $batchid = $this->_customdata['batchid'];
        $mform->addElement('html', '<div class="row"><div class="col-md-8">');
        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'userid', $userid);
        $mform->setType('userid', PARAM_INT);

        $mform->addElement('hidden', 'programid', $programid);
        $mform->setType('programid', PARAM_INT);

        $mform->addElement('hidden', 'status', $status);
        $mform->setType('status', PARAM_INT);

        $mform->addElement('hidden', 'batchid', $batchid);
        $mform->setType('batchid', PARAM_INT);

        // Educational Info.
        $mform->addElement('html', '<div class = "form">');
        // $mform->addElement('html', '<diV class = "heading">'.get_string('educationalinformation', 'local_admissions').'</diV>');
        $mform->addElement('html', get_string('educationalnote', 'local_admissions'));

        $mform->addElement('html', '<div class= "fields">');

        $repeatarray = array();

        $repeatarray[] = $mform->createElement('hidden', 'courseseq', 'courseseq');
        $repeatarray[] = $mform->createElement('text', 'coursename', get_string('coursename', 'local_admissions'));
        $repeatarray[] = $mform->createElement('text', 'university', get_string('university', 'local_admissions'));
        $repeatarray[] = $mform->createElement('text', 'yearofpassing', get_string('yearofpassing', 'local_admissions'));
        $repeatarray[] = $mform->createElement('text', 'percentage', get_string('percentage', 'local_admissions'));
        $repeateloptions = array();
        $repeateloptions['courseseq']['default'] = '{no}';
        $repeateloptions['courseseq']['rule'] = 'numeric';
        $repeateloptions['courseseq']['type'] = PARAM_INT;

        $repeateloptions['coursename']['rule'] = 'required';
        $repeateloptions['coursename']['type'] = PARAM_RAW;

        $repeateloptions['university']['rule'] = 'required';
        $repeateloptions['university']['type'] = PARAM_RAW;

        $repeateloptions['yearofpassing']['rule'] = 'required';
        $repeateloptions['yearofpassing']['rule'] = 'numeric';
        $repeateloptions['yearofpassing']['type'] = PARAM_RAW;

        $repeateloptions['percentage']['rule'] = 'required';
        $repeateloptions['percentage']['rule'] = 'numeric';
        $repeateloptions['percentage']['type'] = PARAM_RAW;
        $repeatno = 1;

        $sql = "SELECT lu.id, la.admissionid 
                FROM {local_users} lu 
                JOIN {local_admissions} la ON la.admissionid = lu.id
                WHERE lu.id = :id";

        $admissionData = $DB->get_record_sql($sql, array('id' => $id));

        // Show repeated element rows based on level(courseseq) in $DB while updating/editing.

        $sql = "SELECT COUNT(level) as levelcnt FROM {local_admissions} WHERE admissionid = :admissionid";
        $levelcnt = $DB->get_record_sql($sql, array('admissionid' => $id));
        if (!empty($admissionData)) {
            $this->repeat_elements($repeatarray, $levelcnt->levelcnt, $repeateloptions, 'option_repeats', 'option_add_fields', 1, get_string('addmore', 'local_admissions'), true);
        } else {
            $this->repeat_elements($repeatarray, $repeatno, $repeateloptions, 'option_repeats', 'option_add_fields', 1, get_string('addmore', 'local_admissions'), true);
        }

        $mform->addElement('static', 'pre_requisites', get_string('program_pre_requisites', 'local_admissions'), get_string('prg_pre_requisites', 'local_admissions'));

        $groupelemnts = array();
        $groupelemnts[] = $mform->createElement('file', 'uploaddocs', '', array('placeholder' => get_string('uploaddocs', 'local_admissions'), 'class' => 'dynamic_form_id_uploaddocs'));
        $mform->addGroup($groupelemnts, 'uploaddocs_group', get_string('uploaddocs', 'local_admissions'), array('class' => 'orgcontactinfo'), false);
        $mform->addElement('html', '<div class="previewbox"></div>');
        $mform->setType('uploaddocs', PARAM_INT);

        $mform->addElement('html', '</div>');
        $mform->addElement('html', '</div>');

        $mform->addElement('html', '<div class="row mb-2 form_btn_row">');
        $mform->addElement('html', '<div class="col-md-3"> </div>');


        if (!empty($admissionData)) {
            $mform->addElement('html', '<div class="buttons-container col-md-9 btncontainer">');
            $mform->addElement('html', '<input type="submit" class="btn btn-primary mr-2" name="submitbutton" id="id_submitbutton" value="Update" data-initial-value="Update">');
            $mform->addElement('html', '<a class="btn btn-primary cont-prev-btn mr-2" href=' . $CFG->wwwroot . '/local/admissions/contact_information.php?id=' . $id . '>Previous</a>');
            $mform->addElement('html', '<a class="btn btn-primary cont-nxt-btn" href=' . $CFG->wwwroot . '/local/admissions/view.php>Cancel</a>');
            $mform->addElement('html', '</div>');
        } else {
            $mform->addElement('html', '<div class="buttons-container col-md-9 edu-mar">');
            $mform->addElement('html', '<input type="submit" class="btn btn-primary mr-2" name="submitbutton" id="id_submitbutton" value="Submit" data-initial-value="Save and continue">');
            $mform->addElement('html', '<a class="btn btn-primary prev-per-btn mr-2" href=' . $CFG->wwwroot . '/local/admissions/contact_information.php?id=' . $id . '>Previous</a>');
            $mform->addElement('html', '<a class="btn btn-primary cont-nxt-btn mr-2" href=' . $CFG->wwwroot . '/local/admissions/view.php>Cancel</a>');
            $mform->addElement('html', '</div>');
        }

        $mform->addElement('html', '</div>');
        $mform->addElement('html', '</div>');
        $mform->addElement('html', '</div></div>');
    }
    public function validation($data, $files)
    {
        $errors = array();
        global $DB, $CFG;
        $sub_data = data_submitted();
        $errors = parent::validation($data, $files);
        $email = $data['email'];
        $employeeid = $data['open_employeeid'];
        $id = $data['id'];

        $docsexist = $DB->get_field('local_users', 'uploaddocs', array('id' => $id));
        if (!$docsexist) {
            if (empty($_REQUEST['files']['uploaddocs']['name'])) {
                $errors['uploaddocs_group'] = get_string('uploadfiles', 'local_admissions');
            }
        }
        if (!empty($_REQUEST['files']['uploaddocs']['name'])) {

            $file_name = $_REQUEST['files']['uploaddocs']['name'];
            $file_size = $_REQUEST['files']['uploaddocs']['size'];
            $file_tmp = $_REQUEST['files']['uploaddocs']['tmp_name'];
            $file_type = $_REQUEST['files']['uploaddocs']['type'];
            $file_ext = strtolower(end(explode('.', $_REQUEST['files']['uploaddocs']['name'])));

            $extensions = array("zip");

            if (in_array($file_ext, $extensions) === false) {
                $errors['uploaddocs_group'] = "Extension not allowed, please choose a Zip file.";
            }
        }
        return $errors;
    }
}
