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
 * @package    block_proposals
 * @copyright  moodle
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
require_once("$CFG->libdir/formslib.php");

class funded extends moodleform {
    public function definition() {
        global $CFG,$DB;
        

        $mform = $this->_form;
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_NOTAGS);
        $fid = $_GET['formid'];
                        
        $mform->addElement('html','<div>');
        //Funded Elements
        $mform->addElement('html','<div id="funded">');

        //Concept Proposal

        // $res=$DB->get_records('applicationtable',['type' =>Funded]);
        // $applicationtype = array(); 
        // foreach ($res as $key => $value) {
        //   $applicationtype[$key] = $value->applicationtype;
        // }
        // $mform->addElement('select' , 'applicationtype' ,get_string('applicationtype','block_proposals'),$applicationtype);
        // $mform->addRule('applicationtype', get_string('applicationtype', 'block_proposals'), 'required', null, 'server');
 
    
        $title=array('placeholder' => 'Title of the Proposed Research Project 250 words');
        $mform->addElement('text' , 'title', get_string('titleproject','block_proposals'),$title);
         
		$mform->setType('title', PARAM_NOTAGS);
        $mform->addHelpButton('title', 'title' , 'block_proposals');
        $mform->addRule('title' , 'You can submit the information only in 250 words' , 'required' , 250 , 'server');

             
        $rationale=array('placeholder' => ' 256 words');
        $mform->addElement('textarea', 'rationale', 'Rationale',$rationale);
        $mform->setType('rationale', PARAM_NOTAGS);
        $mform->addHelpButton('rationale', 'rationale' , 'block_proposals');
        $mform->addRule('rationale' , 'You can submit the information only in 256 words' , 'maxlength' , 256 , 'server');
        $mform->addRule('rationale' , 'You can submit the information only in 256 words' , 'required' , 256 , 'server');
    
        $sql= $DB->get_records_menu('department');
        $mform->addElement('autocomplete', 'departmentname', get_string('Departmentname','block_proposals'), $sql);
        $mform->addRule('departmentname', get_string('mustbeselect','block_proposals'),'required', null, 'server');
            
        $noveltyinnovation=array('placeholder' => ' 100 words');
        $mform->addElement('textarea' , 'noveltyinnovation' , 'Novelty/Innovation',$noveltyinnovation);
        $mform->setType('noveltyinnovation', PARAM_NOTAGS);
        $mform->addHelpButton('noveltyinnovation', 'noveltyinnovation' , 'block_proposals');
        $mform->addRule('noveltyinnovation' , 'You can submit the information only in 100 words' , 'maxlength' , 100 , 'server');
        $mform->addRule('noveltyinnovation' , 'You can submit the information only in 100 words' , 'required' , 100 , 'server');
        $projectdescription=array('placeholder' => ' 700 words');
        $mform->addElement('textarea' , 'projectdescription' , 'Project Description',$projectdescription);
        $mform->setType('projectdescription', PARAM_NOTAGS);
        $mform->addHelpButton('projectdescription', 'projectdescription' , 'block_proposals');
        $mform->addRule('projectdescription' , 'You can submit the information only in 700 words' , 'maxlength' , 700 , 'server');
        $mform->addRule('projectdescription' , 'You can submit the information only in 700 words' , 'required' , 700 , 'server');

        $strength=array('placeholder' => ' 256 words');
        $mform->addElement('textarea' , 'strength','Strength of Principle Investigator' ,$strength);
        $mform->setType('strength', PARAM_NOTAGS);
        $mform->addHelpButton('strength', 'strength' , 'block_proposals');
        $mform->addRule('strength' , 'You can submit the information only in 256 words' , 'required' , 256 , 'server');
        $mform->addRule('strength' , 'You can submit the information only in 256 words' , 'maxlength' , 256 , 'server');

        $departmentsupport=array('placeholder' => ' 256 words');
        $mform->addElement('textarea' , 'departmentsupport' , 'Department Support', $departmentsupport);
        $mform->setType('departmentsupport', PARAM_NOTAGS);
        $mform->addHelpButton('departmentsupport', 'departmentsupport' , 'block_proposals');
         $mform->addRule('departmentsupport' , 'You can submit the information only in 256 words' , 'required' , 256 , 'server');
        $mform->addRule('departmentsupport' , 'You can submit the information only in 256 words' , 'maxlength' , 256 , 'server');

        $financialsupport=array('placeholder' => ' 256 words');
        $mform->addElement('textarea' , 'financialsupport' , 'Financial Support' ,$financialsupport);
        $mform->setType('financialsupport', PARAM_NOTAGS);
        $mform->addHelpButton('financialsupport', 'financialsupport' , 'block_proposals');

        $mform->addRule('financialsupport' , 'You can submit the information only in 256 words' , 'required' , 256 , 'server');

        $mform->addRule('financialsupport' , 'You can submit the information only in 256 words' , 'maxlength' , 256 , 'server');

        $mform->addElement('filemanager' , 'attachments', get_string('file' , 'block_proposals') , null,
        array( 'maxfiles' => 1 , 'accepted_types' => '*'));
        

        $mform->addElement('hidden', 'status', 'Status');
        
        $mform->addElement('html','</div>');
        $mform->addElement('html','</div>');
        $data = $DB->get_record('submissions',array('id'=>$fid));

        // if($data->status == 0){
        $this->add_action_buttons($cancel = false, $submitlabel=get_string('savedraft','block_proposals'));
       // }
        // if ($data->draft == 'f1') {
        //    $this->add_action_buttons($cancel = false, $submitlabel=get_string('preview','block_proposals'));
        // }
        $this->add_action_buttons($cancel = true, $submitlabel=get_string('submit','block_proposals'));

    }
}

