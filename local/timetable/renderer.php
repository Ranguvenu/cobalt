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
 * @package    local_timetable
 * @copyright  2023 Dipanshu Kasera <kasera.dipanshu@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/timetable/lib.php');
class local_timetable_renderer extends plugin_renderer_base {

    /**
     * [render_form_status description]
     * @method render_form_status
     * @param  \local_timeatble\output\form_status $page [description]
     * @return [type]                                    [description]
     */
    public function render_form_status(\local_timetable\output\form_status $page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('local_timetable/form_status', $data);
    }

    /**
     * Display the avialable timetable
     *
     * @return string The text to render
     */
    public function timetable_view_content($filter = false) {
        global $USER;
        $systemcontext = context_system::instance();
        $cardClass = 'tableformat';
        $options = array(
            'targetID' => 'timelayoutm_view',
            'perPage' => 5,
            'cardClass' => 'col-md-4 col-12',
            'viewType' => 'table'
        );
        $options['methodName'] = 'local_timetable_view_instance';
        $options['templateName'] = 'local_timetable/index';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('userid' => $USER->id, 'contextid' => $systemcontext->id));

        $context = [
            'targetID' => 'timelayoutm_view',
            'options' => $options,
            'dataoptions' => $dataoptions,
            'filterdata' => $filterdata
        ];

        if ($filter) {
            return  $context;
        } else {
            return  $this->render_from_template('local_costcenter/cardPaginate', $context);
        }
    }

    /**
     * Display the timetable add button
     */
    public function get_timetableadd_btns() {
        global $CFG, $USER, $DB;
        require_once($CFG->dirroot .'/local/lib.php');
        $systemcontext = context_system::instance();
        $semid = optional_param('tlid', 0, PARAM_INT);
        $semdates = $DB->get_record('local_program_levels', ['id' => $semid]);

        $pdiffdays = date('d-m-Y', $semdates->startdate);
        $odiffdays = date('d-m-Y', $semdates->enddate);
        $newpdate = date_create("$pdiffdays");
        $existingpdate = date_create("$odiffdays");
        $diffdays = date_diff($newpdate, $existingpdate);
        
        if ($diffdays->days < 7) {
            $disable = 'creation_not_allowed';
            $title = get_string('s_e_more_than_7_days', 'local_timetable');
        } else {
            $disable = '';
            $title = get_string('createtimetable', 'local_timetable');
        }

        if ($semid > 0) {
            $data = true;
        } else {
            $data = false;
        }
        $record = identify_teacher_role($USER->id);
        if ($record->shortname == 'editingteacher') {
            $teacher = true;
        } else {
            $teacher = false;
        }

        if (is_siteadmin()
            || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)) {
            $capability = true;
        } else if (has_capability('local/costcenter:manage_ownorganization', $systemcontext)) {
            $capability = true;
        } else if (has_capability('local/costcenter:manage_owndepartments', $systemcontext)) {
            $capability = false;
        }

        $configroot = [
            'configroot' => $CFG->wwwroot,
            'individual' => $data,
            'teacher' => $teacher,
            'semid' => $semid,
            'days' => $days,
            'disable' => $disable,
            'title' => $title,
            'capability' => $capability,
        ];

        return $this->render_from_template('local_timetable/viewbutton', $configroot);
    }

    /**
     * Display the avialable timetable
     *
     * @return string The text to render
     */
    public function timetable_individual_session_content($filter = false) {
        global $USER;
        $semid = optional_param('tlid', 0, PARAM_INT);
        $systemcontext = context_system::instance();
        $cardClass = 'tableformat';
        $options = array(
            'targetID' => 'sessions_manage_table',
            'perPage' => 10,
            'cardClass' => 'col-md-4 col-12',
            'viewType' => 'table'
        );
        $options['methodName'] = 'local_timetable_individual_session_instance';
        $options['templateName'] = 'local_timetable/individual_session';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('userid' => $USER->id, 'contextid' => $systemcontext->id, 'semid' => $semid));

        $context = [
            'targetID' => 'sessions_manage_table',
            'options' => $options,
            'dataoptions' => $dataoptions,
            'filterdata' => $filterdata
        ];

        if ($filter) {
            return  $context;
        } else {
            return  $this->render_from_template('local_costcenter/cardPaginate', $context);
        }
    }

    public function render_timetable() {
        global $DB, $USER, $OUTPUT, $CFG, $PAGE;

        $renderedtemplate = $this->render_from_template('local_timetable/timetable', []);
        return $renderedtemplate;
    }
}
