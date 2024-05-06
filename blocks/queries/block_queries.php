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
 * Version information
 *
 * @package    blocks_queries
 * @copyright  2022 eAbyas Info Solutions Pvt. Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use \block_queries\form\queries_form as queries_form;
class block_queries extends block_base {
    public function init() {
        $this->title = get_string('pluginname', 'block_queries');
    }
    function get_required_javascript() {
        $this->page->requires->jquery_plugin('ui-css');
        $this->page->requires->js(('/blocks/queries/js/validate.min.js'), true);
        $this->page->requires->js_call_amd('block_queries/form_popup', 'init');
        $this->page->requires->js('/blocks/queries/js/commentform_popup.js');
    }
    public function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }
        global $CFG, $USER, $PAGE;
        $this->content = new stdClass();

        // $this->page->requires->css('/blocks/queries/styles.css');
        require_once($CFG->dirroot.'/blocks/queries/lib.php');
        require_once($CFG->dirroot.'/blocks/queries/commentform.php');

        // Query to enrol users courses particulars.
        $courses = enrol_get_users_courses($USER->id);
        $systemcontext = context_system::instance();

        $instructorlogin = block_queries_getrole_user(null, 'editingteacher');
        $registrarlogin = block_queries_getrole_user(null, 'manager');
        $studentlogin = block_queries_getrole_user($courses, 'student');
        $this->content->text = '';
        if (is_siteadmin() || has_capability('block/queries:teacheraccess',
        $systemcontext) || has_capability('block/queries:manager', $systemcontext)) {
            $this->content->text = block_queries_display_view($USER);
        } else {
            $this->content->text = block_queries_display_view(null, $instructorlogin, $registrarlogin, $studentlogin);
        }
        $this->content->footer = '';
        // Return the content object.
        return $this->content->text;
    }
}
