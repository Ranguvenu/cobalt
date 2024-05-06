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
 * @subpackage block_queries
 */
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->dirroot . '/theme/bloom/config.php');

global $CFG, $PAGE, $DB, $OUTPUT, $USER;
$PAGE->set_title('viewqueries', 'block_queries');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('askaquestion', 'block_queries'));
$PAGE->set_url(new moodle_url('/blocks/queries/view.php'));

// $PAGE->navbar->add(get_string('dashboard', 'block_queries'), new moodle_url('/my/index.php'), array());
// $PAGE->navbar->add(get_string('viewcomment', 'block_queries'));
$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/blocks/queries/js/queryResponse.js'));

$sql = $DB->get_field('user', 'id', array('id' => $USER->id));

$ifteacher = $DB->get_field('user', 'id', array('open_type' => '0', 'id' => $USER->id));
$ifstudent = $DB->get_field('user', 'id', array('open_type' => '1', 'id' => $USER->id));

echo $OUTPUT->header();
'<span style="color: red"><b>'.get_string('notresponded', 'block_queries').'</b></span>';
$previousqueries = '<div class="d-flex justify-content-end align-items-center">';
if (is_siteadmin()) {
    $previousqueries .= '<span class="d-flex justify-content-end mr-2">
    <a href="'.$CFG->wwwroot.'/blocks/queries/display_queries.php">'.get_string('previousQueries', 'block_queries').'</a>
    </span>';
}
if ($ifstudent) {
    $previousqueries .= '<span class="d-flex justify-content-end mr-2">
    <a href="'.$CFG->wwwroot.'/blocks/queries/display_queries.php?studentid='.$sql.'">'.get_string('previousQueries',
     'block_queries').'</a>
    </span>';
} else if ($ifteacher) {
    $previousqueries .= '<span class="d-flex justify-content-end mr-2">
    <a href="'.$CFG->wwwroot.'/blocks/queries/display_queries.php?userid='.$sql.'">'.get_string('previousQueries',
     'block_queries').'</a>
    </span>';
}
$previousqueries .= '<span class="d-flex justify-content-end">
<a class="btn mb-1" href="'.$CFG->wwwroot.'" >'.get_string('back', 'block_queries').'</a>
</span>
</div>';
echo $previousqueries;
echo $OUTPUT->footer();
