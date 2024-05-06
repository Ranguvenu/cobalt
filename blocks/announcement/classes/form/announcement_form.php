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
 * @package ODL
 * @subpackage blocks_announcement
 */
namespace block_announcement\form;
use core;
use moodleform;
use context_system;
use coursecat;
use html_writer;
require_once($CFG->dirroot . '/lib/formslib.php');
require_once($CFG->dirroot . '/lib/badgeslib.php');
class announcement_form extends moodleform {

    /**
     * Defines the form
     */
    public function definition() {
        global $USER, $PAGE, $OUTPUT, $DB;
        $mform = $this->_form;
        $id = $this->_customdata['id'];
        
        $courseid = $this->_customdata['courseid'];
        $context =  context_system::instance();

        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);
        
        $mform->addElement('hidden', 'courseid', $courseid);
        $mform->setType('courseid', PARAM_INT);
        
        $mform->addElement('text', 'name', get_string('subject', 'block_announcement'));
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->setType('name', PARAM_RAW);
        
		$mform->addElement('editor', 'description', get_string('description', 'block_announcement'), null,
        $this->get_description_editor_options());
		
        $mform->addRule('description', null, 'required', null, 'client');
        $mform->addHelpButton('description', 'description','block_announcement');
        $mform->setType('description', PARAM_RAW);

        $types= array('mainfile' => '', 'subdirs' => 1, 'maxbytes' => -1, 
            'maxfiles' => -1,'accepted_types' => '*', 'return_types' =>  null, 'areamaxbytes' => FILE_AREA_MAX_BYTES_UNLIMITED);
        $mform->addElement('html','<div class="typeoffiles">');
        $mform->addElement('filepicker', 'attachment', get_string('attachments', 'block_announcement'), null, array('accepted_types' => $types));
        $mform->addElement('static', '', '', 'Accept all files');
        $mform->addElement('html','</div>');
        $startdate_options = array(
                                'startyear' => date('Y'),
                                'stopyear'  => date('Y')+3,
                                'applydst'  => true,
                                'step'      => 1,
                                'optional' => true
                                );
        $mform->addElement('date_time_selector', 'startdate', get_string('startdate', 'block_announcement'), $startdate_options);
        
        $enddate_options = array(
                                'startyear' => date('Y'),
                                'stopyear'  => date('Y')+3,
                                'applydst'  => true,
                                'step'      => 1,
                                'optional' => true
                                );
        $mform->addElement('date_time_selector', 'enddate', get_string('enddate', 'block_announcement'), $enddate_options);
       
        $this->add_action_buttons();
    }
	/**
     * Returns the description editor options.
     * @return array
     */
    public function get_description_editor_options() {
        global $CFG;
        
        $context = $this->_customdata['context'];
        if(empty($context)){
            $context =  context_system::instance();
        }
        //print_object($context);
        $itemid = $this->_customdata['itemid'];
        //print_object($itemid);
        return array(
            'maxfiles'  => EDITOR_UNLIMITED_FILES,
            'maxbytes'  => $CFG->maxbytes,
            'trusttext' => true,
            'context'   => $context,
			'autosave' => false,
            'subdirs'   => file_area_contains_subdirs($context, 'system', 'description', $itemid),
        );
    }
    
    /**
     * Validates form data
     */
    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);
        
        $startdate = '';
        $enddate = '';
        if (isset($data['startdate']) && !empty($data['startdate'])) {
            $timestamp = $data['startdate'];
            $presenttime = time();
            $startdate = $timestamp;
            
        }
        if (isset($data['enddate']) && !empty($data['enddate'])) {
            $timestamp = $data['enddate'];
            $presenttime = time();
            $enddate = $timestamp;
            
        }
        
        if(!empty($startdate) && !empty($enddate)){
            if($startdate >= $enddate){
                $errors['enddate'] = get_string('nohighandsameenddate', 'block_announcement');
            }
        }
        
        return $errors;
    }
}
