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
 * Form for editing HTML block instances.
 *
 * @package   block_general_message
 * @copyright 2023 eAbyas Info Solutions Pvt. Ltd.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_general_message extends block_base {
    public function init() {
        $this->title = '';
    }

    public function get_content() {
        global $DB, $USER;
        require_login();
        $systemcontext = context_system::instance();
        if ($this->content !== null) {
            return $this->content;
        }
        
        // $this->page->requires->css('/blocks/general_message/style.css');
        $teachers = $DB->get_records_sql("SELECT DISTINCT(r.id), r.shortname 
                                           FROM {role} r
                                           JOIN {user} u ON u.roleid = r.id
                                          WHERE u.id = {$USER->id}
                                           AND r.shortname = 'editingteacher'");
        $enrolledincoursesql = "SELECT c.id
                               FROM {user} u
                               JOIN {role_assignments} ra ON ra.userid = u.id
                               JOIN {role} r ON r.id = ra.roleid AND r.shortname = 'editingteacher'
                               JOIN {context} ctx ON ctx.id = ra.contextid
                               JOIN {course} c ON c.id = ctx.instanceid
                              WHERE u.id = {$USER->id}";
        $enrolledincourse = $DB->record_exists_sql($enrolledincoursesql);

        $this->content =  new \stdClass();
        if ($teachers && !$enrolledincourse
            && !has_capability('local/costcenter:manage_ownorganization', $systemcontext)
            && !has_capability('local/costcenter:manage_owndepartments', $systemcontext)
            && !has_capability('local/costcenter:manage_ownsubdepartments', $systemcontext)) {
            $this->content->text = get_string('empmessage', 'block_general_message');
        }

        $student = $DB->get_records_sql("SELECT u.id
                                          FROM {user} u
                                         JOIN {local_program_users} lpu ON u.id = lpu.userid
                                         JOIN {local_program_levels} lpl ON lpu.programid = lpl.programid
                                          WHERE u.id = {$USER->id} AND lpl.active = 1");


        $appliedstudent = $DB->get_records_sql("SELECT lu.userid
                                                 FROM {local_users} lu                                
                                                WHERE lu.userid = {$USER->id}");

        if (!$student && $appliedstudent && !$teachers && !is_siteadmin()
            && !has_capability('local/costcenter:manage_ownorganization', $systemcontext)
            && !has_capability('local/costcenter:manage_owndepartments', $systemcontext)
            && !has_capability('local/costcenter:manage_ownsubdepartments', $systemcontext)) {
            // $this->content =  new \stdClass();
            $this->content->text = get_string('appliedstdmessage', 'block_general_message');
        }

        if (!$appliedstudent && !$student && !$teachers && !is_siteadmin()
            && !has_capability('local/costcenter:manage_ownorganization', $systemcontext)
            && !has_capability('local/costcenter:manage_owndepartments', $systemcontext)
            && !has_capability('local/costcenter:manage_ownsubdepartments', $systemcontext)) {
            // $this->content =  new \stdClass();
            $this->content->text = get_string('stdmessage', 'block_general_message');
        }

        return $this->content;
    }
}
