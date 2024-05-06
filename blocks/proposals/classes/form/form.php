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

class newform extends moodleform {
    public function definition() {
        global $CFG;


        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_NOTAGS);

        $strrequired = get_string('required','block_proposals');
        
        $type[] = $mform->createElement('radio', 'type', '',get_string('Funded','block_proposals'), 1);
        $type[] = $mform->createElement('radio', 'type', '',get_string('Non-Funded','block_proposals'), 0);
        $mform->addGroup($type, 'radioar',get_string('applicationtype','block_proposals'), array(''), false);
        $mform->setDefault('type',false);

        
        // $mform->disabledIf('radioa', 'type', 'eq', 0);

        $funded[] = $mform->createElement('radio', 'funded', '',get_string('ConceptProposal','block_proposals'), 1);
        $funded[] = $mform->createElement('radio', 'funded', '',get_string('ResearchProposal','block_proposals'), 0);
        $mform->addGroup($funded, 'radioa', get_string('selecttype','block_proposals'), array(''), false);
        $mform->disable_form_change_checker();
    }
}


