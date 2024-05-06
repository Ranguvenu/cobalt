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
 * @package stream
 * @subpackage block_stream
 */
namespace block_stream\form;
defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir . '/formslib.php');
use context_system;
class upload extends \moodleform {
	public function definition() {
        global $USER, $CFG, $DB, $PAGE;
		$systemcontext = context_system::instance();
		$mform = $this->_form;
		$editoroptions = $this->_customdata['editoroptions'];
		$organisations = $this->_customdata['organisations'];
		$tags = $this->_customdata['tags'];
		
        $mform->addElement('hidden', 'organisationoptions', json_encode($organisations));
        $mform->addElement('hidden', 'tagsoptions', json_encode($tags));
        
        $organizationoptions = array(
            'class' => 'organisationnameselect',
            'data-class' => 'organisationselect',
            'multiple' => false,
            'placeholder' => get_string('selectcategory', 'block_stream'),
        );
        $organisations[0] = get_string('selectcategory', 'block_stream');
        ksort($organisations);

        $mform->addElement('text', 'title', get_string('title', 'block_stream'));
        $mform->addRule('title', get_string('titlerequired', 'block_stream'), 'required', null, 'client');
        $mform->setType('title', PARAM_RAW);

        $mform->addElement('filepicker', 'filepath', get_string('filepath', 'block_stream'), null, array('accepted_types' => array('.mp4', '.avi', '.m4v', '.mov')));
        $mform->addHelpButton('filepath', 'filepathhelp', 'block_stream');
        $mform->addRule('filepath', get_string('filepathrequired', 'block_stream'), 'required', null, 'client');

        $mform->addElement('header', 'advancedhdr', get_string('advancedfields', 'block_stream'));
        $mform->setExpanded('advancedhdr', false);
       	$tagsoptions = array(
            'class' => 'tagnameselect',
            'data-class' => 'tagselect',
            'multiple' => true,
            'placeholder' => 'Select Tags',
        );
        $mform->addElement('autocomplete', 'organization', get_string('category'), $organisations, $organizationoptions);
        $mform->addHelpButton('organization', 'organisationstream', 'block_stream');
        $mform->setType('organization', PARAM_INT);

		$mform->addElement('autocomplete', 'tags', get_string('tags','block_stream'), $tags, $tagsoptions);
        $mform->addHelpButton('tags', 'tagsstreamhelp', 'block_stream');
        $mform->setType('tags', PARAM_INT);
        
        
        $mform->addElement('editor','description', get_string('videodescription','block_stream'), null, $editoroptions);
        $mform->addHelpButton('description', 'descriptionhelp', 'block_stream');
        $mform->setType('description', PARAM_RAW);
        

        $mform->addElement('filepicker', 'thumbnail', get_string('thumbnail', 'block_stream'), null, array('accepted_types' => array('.jpg', '.jpeg', '.png')));
        $mform->addHelpButton('thumbnail', 'thumbnailhelp', 'block_stream');
        $mform->disable_form_change_checker();
	}
	public function validation($data, $files) {
        $errors = [];
        if(empty($data['title'])){
        	$errors['title'] = get_string('required');
        }
        if(empty($data['filepath'])){
        	$errors['filepath'] = get_string('required');
        }
        return $errors;
    }
}