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
 * local local_costcenter
 *
 * @package    local_costcenter
 * @copyright  2019 eAbyas <eAbyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_costcenter\form;
use core;
use moodleform;
use context_system;
use core_component;
defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot . '/lib/formslib.php');
require_once($CFG->dirroot . '/local/costcenter/lib.php');
class organization_form extends moodleform {

    public function definition() {
        global $USER, $CFG, $DB;
        $costcenter = new \costcenter();
        $corecomponent = new core_component();

        $mform = $this->_form;
        $id = $this->_customdata['id'];

        $parentid = $this->_customdata['parentid'];
        $formtype = $this->_customdata['formtype'];
        $headstring = $this->_customdata['headstring'];

        $systemcontext = context_system::instance();
        $labelstring = get_config('local_costcenter');
        if ($formtype != 'organization') {
            if ($formtype == 'department') {
                $parentlabel = get_string('organization', 'local_costcenter', $labelstring->firstlevel);
                $departmentsql = "SELECT lc.id, lc.fullname
                    FROM {local_costcenter} lc WHERE lc.depth = 1 AND visible = 1";
            } else if ($formtype == 'subdepartment') {
                $parentlabel = get_string('department', 'local_costcenter', $labelstring->secondlevel);
                $subdepartmentsql = "SELECT lc.id, CONCAT(llc.fullname,' / ',lc.fullname) AS fullname
                    FROM {local_costcenter} lc
                    JOIN {local_costcenter} llc ON llc.id = lc.parentid
                    WHERE lc.depth = 2 AND lc.visible = 1";
            }
            if ($id) {
                $parentid = $DB->get_field('local_costcenter', 'parentid', array('id' => $id));
                $departmentsql .= " AND lc.id = {$parentid} ";
                $subdepartmentsql .= " AND lc.id = {$parentid} ";
            }
            if ((!is_siteadmin() || !has_capability('local/costcenter:manage_multiorganizations',
                    $systemcontext)) && has_capability('local/costcenter:manage_ownorganization',
                        $systemcontext)) {
                $departmentsql .= " AND lc.id = {$USER->open_costcenterid} ";
                $subdepartmentsql .= " AND llc.id = {$USER->open_costcenterid} ";
            } else if ((!is_siteadmin() || !has_capability('local/costcenter:manage_multiorganizations',
                            $systemcontext)) && has_capability('local/costcenter:manage_owndepartments',
                                $systemcontext)) {
                $departmentsql .= " AND lc.id = {$USER->open_costcenterid} ";
                $subdepartmentsql .= " AND lc.id = {$USER->open_departmentid} ";
            }
            if ($formtype == 'department') {
                $options = $DB->get_records_sql_menu($departmentsql);
            } else if ($formtype == 'subdepartment') {
                $options = $DB->get_records_sql_menu($subdepartmentsql);
            }

            if (count($options) > 1) {
                $mform->addElement('select', 'parentid', $parentlabel, $options);
                $mform->setType('parentid', PARAM_INT);
                $mform->addRule('parentid', get_string('orgemptymsg', 'local_costcenter'), 'required', null, 'client');
            } else {
                $parentid = array_keys($options)[0];
                $parentname = $options[$parentid];
                $mform->addElement('static',  'parentname', $parentlabel, $parentname);
                $mform->addElement('hidden',  'parentid', $parentid);
                $mform->setType('parentid', PARAM_INT);
            }
        } else {
            $mform->addElement('hidden', 'parentid', 0);
            $mform->setType('parentid', PARAM_INT);
        }

        $mform->addElement('text', 'fullname', get_string('costcentername', 'local_costcenter'), 'maxlength="200" size="20"',array());
        $mform->setType('fullname', PARAM_TEXT);
        $mform->addRule('fullname', get_string('missingcostcentername', 'local_costcenter'), 'required', null, 'client');

        $mform->addElement('text', 'shortname', get_string('shortname', 'local_costcenter'), 'maxlength="200" size="20"');

        $mform->addRule('shortname', get_string('shortnamecannotbeempty', 'local_costcenter'), 'required', null, 'client');

        $mform->setType('shortname', PARAM_TEXT);
        $attributes = array('rows' => '8', 'cols' => '40');

        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden',  'formtype',  $formtype);
        $mform->setType('formtype', PARAM_TEXT);

        $mform->addElement('hidden',  'headstring', $headstring);
        $mform->setType('headstring', PARAM_TEXT);

        $mform->addElement('hidden', 'timecreated', time());
        $mform->setType('timecreated', PARAM_RAW);

        $mform->addElement('hidden', 'usermodified', $USER->id);
        $mform->setType('usermodified', PARAM_RAW);

        if ($formtype == 'organization') {
            // $themebloompluginexist = $corecomponent::get_plugin_directory('theme', 'bloom');
            // if (!empty($themebloompluginexist)) {
            //     $iconstyle = array();
            //     $default = 'circle';
            //     $iconstyle[] =& $mform->createElement('radio', 'shell', '',
            //                                            get_string('square', 'local_costcenter'),
            //                                            'square', array('class' => 'square')
            //                                         );
            //     $iconstyle[] =& $mform->createElement('radio', 'shell', '',
            //                                            get_string('rounded', 'local_costcenter'),
            //                                            'circle', array('class' => 'circle')
            //                                         );
            //     $iconstyle[] =& $mform->createElement('radio', 'shell', '',
            //                                            get_string('rounded-square', 'local_costcenter'),
            //                                            'rounded', array('class' => 'rounded')
            //                                         );

            //     $mform->addGroup($iconstyle, 'shell', get_string('iconstyle', 'local_costcenter'), array(''), false);
            // }
            $logoupload = array(
                'maxbytes' => $CFG->maxbytes,
                'subdirs' => 0,
                'maxfiles' => 1,
                'accepted_types' => 'web_image'
            );
            $mform->addElement('filemanager', 'costcenter_logo', get_string('costcenter_logo',
                                'local_costcenter'), '', $logoupload);
        }

        $submit = ($id > 0) ? get_string('update_costcenter', 'local_costcenter') : get_string('create', 'local_costcenter');
        $this->add_action_buttons('false', $submit);
    }

    /**
     * validates costcenter name and returns instance of this object
     *
     * @param [object] $data
     * @param [object] $files
     * @return costcenter validation errors
     */
    public function validation($data, $files) {
        global $COURSE, $DB, $CFG;
        $errors = parent::validation($data, $files);
        if (strrpos($data["shortname"], ' ') !== false){
            $errors['shortname'] = get_string('spacesarenotallowed', 'local_costcenter');
        }
        $costdata = $DB->get_record('local_costcenter', array('shortname' => $data['shortname'], 'parentid' => 0));
        if ($data['formtype'] == 'organization') {
            $costname = $DB->get_record('local_costcenter', ['fullname' => $data['fullname']]);
            $costsrtname = $DB->get_record('local_costcenter', ['shortname' => $data['shortname']]);
            if ($data['id'] != $costname->id) {
                if($DB->record_exists('local_costcenter', ['fullname' => $data['fullname']])) {
                    $errors['fullname'] = get_string('shortnametakenlp', 'local_costcenter', $costname->fullname);
                }
            }
            if ($data['id'] != $costsrtname->id) {
                if($DB->record_exists('local_costcenter', ['shortname' => $data['shortname']])) {
                    $errors['shortname'] = get_string('shortnametakenlp', 'local_costcenter', $costsrtname->shortname);
                }
            }
        } else if ($data['formtype'] == 'department') {

            $deptdata = $DB->get_records('local_costcenter', array('parentid' => $data['parentid']));
            foreach ($deptdata as $key => $value) {
                if ($value->id != $data['id']) {
                    if (strtolower($value->shortname) == strtolower($data['shortname']) || strtolower($costdata->shortname) == strtolower($data['shortname'])) {
                        if (strtolower($costdata->shortname) == strtolower($data['shortname'])) {
                            $value->shortname = $costdata->shortname;

                        } else {
                            $value->shortname = $value->shortname;
                        }
                        $errors['shortname'] = get_string('shortnametakenlp', 'local_costcenter', $value->shortname);
                    }
                    if ($value->fullname == $data['fullname']) {
                        $errors['fullname'] = get_string('shortnametakenlp', 'local_costcenter', $value->fullname);
                    }
                }
            }
        } else if ($data['formtype'] == 'subdepartment') {
            $sql = "SELECT id, shortname
                     FROM {local_costcenter}
                    WHERE id != {$data['id']} AND shortname = '{$data['shortname']}' AND parentid = {$data['parentid']} ";
            $subdepartmentdata = $DB->get_record_sql($sql);
            if ($subdepartmentdata) {
                $errors['shortname'] = get_string('shortnametakenlp', 'local_costcenter', strtoupper($data['shortname']));
            }
            $existeddata = $DB->record_exists('local_costcenter', ['shortname' => $data['shortname'], 'depth' => 1]);
            if ($existeddata) {
                $errors['shortname'] = get_string('shortnametakenlp', 'local_costcenter', strtoupper($data['shortname']));
            }
        }

        return $errors;
    }
}
