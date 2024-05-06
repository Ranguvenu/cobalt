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
 * @package
 */
class block_students_attendance_renderer extends plugin_renderer_base {
        /**
     * Display the avialable courses
     *
     * @return string The text to render
     */
    public function courses_content($filter = false) {
        global $USER;
        $systemcontext = context_system::instance();
        $cardClass = 'tableformat';
        $options = array(
            'targetID' => 'courses_table_student',
            'perPage' => 10,
            'cardClass' => 'col-md-4 col-12',
            'viewType' => 'table'
        );
        $options['methodName'] = 'block_student_courses_view';
        $options['templateName'] = 'block_students_attendance/studentcourses';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('studentid' => $USER->id, 'contextid' => $systemcontext->id));
        
        $context = [
            'targetID' => 'courses_table_student',
            'options' => $options,
            'dataoptions' => $dataoptions,
            'filterdata' => $filterdata
        ];
        if ($filter) {
            return  $context;
        } else {
            return  $this->render_from_template('block_students_attendance/cardPaginate', $context);
        }
    }
}