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
 * @package BizLMS
 * @subpackage block_timetable
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->dirroot . '/theme/bloom/config.php');
global $CFG,$PAGE, $OUTPUT;

    $PAGE->set_pagelayout('standard');
    $PAGE->set_url(new moodle_url('/blocks/timetable/view.php'));
    $PAGE->set_heading(get_string('pluginname', 'block_timetable'));

    echo $OUTPUT->header();
    echo '<div class="d-flex justify-content-end">
    <a href="'.$CFG->wwwroot.'" class="btn mb-3" title="Back">Back</a>
    </div>';
    echo $OUTPUT->footer();
