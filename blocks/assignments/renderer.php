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
 * @author Moodle India
 * @package
 * @subpackage block_assignments
 */
class block_assignments_renderer extends plugin_renderer_base {
        /**
     * Display the avialable courses
     *
     * @return string The text to render
     */
    public function assignments_content($filter = false) {
        global $USER;
        $systemcontext = context_system::instance();
        $cardClass = 'tableformat';
        $options = array(
            'targetID' => 'grade_assignments',
            'perPage' => 10,
            'cardClass' => 'col-md-4 col-12',
            'viewType' => 'table'
        );
        $options['methodName'] = 'block_assignments_view';
        $options['templateName'] = 'block_assignments/submissions';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('teacherid' => $USER->id, 'contextid' => $systemcontext->id));
        
        $context = [
            'targetID' => 'grade_assignments',
            'options' => $options,
            'dataoptions' => $dataoptions,
            'filterdata' => $filterdata
        ];

        if ($filter) {
            return  $context;
        } else {
            return  $this->render_from_template('block_assignments/cardPaginate', $context);
        }
    }
}