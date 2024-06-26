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
 * @package    mod_stream
 */

defined('MOODLE_INTERNAL') || die;

require_once ($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/stream/locallib.php');
require_once($CFG->dirroot.'/repository/lib.php');

class mod_stream_mod_form extends moodleform_mod {
    function definition() {
        global $CFG, $DB, $PAGE, $OUTPUT;
        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('name'), array('size'=>'48'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $mform->addElement('hidden', 'externalurl', '',array('id' => 'stream_external_url'));
        $mform->setType('externalurl', PARAM_URL);
        $mform->addElement('hidden', 'duration', '',array('id' => 'stream_duration'));
        $mform->setType('duration', PARAM_TEXT);
        $mform->addElement('hidden', 'videoid', '',array('id' => 'stream_external_videoid'));
        $mform->setType('videoid', PARAM_INT);

        $html = mod_stream_get_browsevideo_form_html($mform);
        $mform->addElement('html',  $html);

        $this->standard_intro_elements();
        $element = $mform->getElement('introeditor');
        $attributes = $element->getAttributes();
        $attributes['rows'] = 5;
        $element->setAttributes($attributes);

        $mform->addElement('header', 'appearence', get_string('appearence', 'stream'));

        $mform->addElement('text', 'width', get_string('width', 'stream'), array('size'=>3));
        $mform->setType('width', PARAM_INT);

        $mform->addElement('text', 'height', get_string('height', 'stream'), array('size'=>3));
        $mform->setType('height', PARAM_INT);

        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    public function add_completion_rules() {
        $mform = $this->_form;

        $mform->addElement('checkbox', 'completionvideoenabled', ' ', get_string('completionvideo', 'stream'));
        return ['completionvideoenabled'];
    }
    public function completion_rule_enabled($data) {
        return (!empty($data['completionvideoenabled']) && $data['completionvideoenabled'] != 0);
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if (!empty($data['externalurl'])) {
            $url = $data['externalurl'];
            if (preg_match('|^/|', $url)) {
                // links relative to server root are ok - no validation necessary

            } else if (preg_match('|^[a-z]+://|i', $url) or preg_match('|^https?:|i', $url) or preg_match('|^ftp:|i', $url)) {
                // normal URL
                if (!stream_appears_valid_url($url)) {
                    $errors['externalurl'] = get_string('invalidurl', 'stream');
                }

            } else if (preg_match('|^[a-z]+:|i', $url)) {
                // general URI such as teamspeak, mailto, etc. - it may or may not work in all browsers,
                // we do not validate these at all, sorry

            } else {
                // invalid URI, we try to fix it by adding 'http://' prefix,
                // relative links are NOT allowed because we display the link on different pages!
                if (!stream_appears_valid_url('http://'.$url)) {
                    $errors['externalurl'] = get_string('invalidurl', 'stream');
                }
            }
        }
        return $errors;
    }
    function get_data() {
        $data = parent::get_data();
        if (!$data) {
            return $data;
        }
        if (!empty($data->completionunlocked)) {
            // Turn off completion settings if the checkboxes aren't ticked
            $autocompletion = !empty($data->completion) && $data->completion==COMPLETION_TRACKING_AUTOMATIC;
            if (empty($data->completionvideoenabled) || !$autocompletion) {
               $data->completionvideoenabled = 0;
            }
        }
        return $data;
    }
}
