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
 * @subpackage local_location
 */

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG,$PAGE;
require_once($CFG->dirroot . '/local/location/lib.php');
require_once($CFG->dirroot . '/local/courses/filters_form.php');
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_url($CFG->wwwroot .'/local/location/room.php');
$PAGE->set_title(get_string('manage_room', 'local_location'));
$PAGE->set_heading(get_string('manage_room', 'local_location'));
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('building', 'local_location'), new moodle_url('/local/location/index.php'));
$PAGE->navbar->add(get_string('manage_room', 'local_location'));
$PAGE->requires->jquery();
$PAGE->requires->js('/local/location/js/delconfirm.js',TRUE);
$PAGE->requires->js('/local/location/js/jquery.min.js',TRUE);
$PAGE->requires->js('/local/location/js/datatables.min.js', TRUE);
$PAGE->requires->css('/local/location/css/datatables.min.css');
$PAGE->requires->js_call_amd('local_location/newroom', 'load', array());
$room = new local_location\event\location();
$renderer = $PAGE->get_renderer('local_location');
$id = optional_param('id',0,PARAM_INT);
$delete = optional_param('delete', 0, PARAM_INT);


echo $OUTPUT->header();

$filterparams = $renderer->room_view_content(true);

$filterparams['submitid'] = 'formfilteringform';

if ((has_capability('local/location:manageinstitute', context_system::instance()) || has_capability('local/location:viewinstitute', context_system::instance()))) {
  if ((has_capability('local/location:manageroom', context_system::instance()) || has_capability('local/location:viewroom', context_system::instance()))) {
    if ((has_capability('local/location:manageroom', context_system::instance()))) {
      $PAGE->requires->js_call_amd('local_location/newroom', 'load', array());
      echo "<ul class='course_extended_menu_list'>
              <li> 
                <div class = 'coursebackup course_extended_menu_itemcontainer'>
                  <a data-action='createroommodal' data-value='0' class='course_extended_menu_itemlink' onclick ='(function(e){ require(\"local_location/newroom\").init({selector:\"createroommodal\", contextid:$systemcontext->id, roomid:$id}) })(event)' title='".get_string('createroom', 'local_location')."'><i class='icon fa fa-plus' aria-hidden='true'></i>
                  </a>
                </div>
                </li>
            </ul>";
    }
    if($delete){
      $room->delete_rooms($id);
      redirect(new moodle_url('/local/location/room.php'));
    }
    echo $OUTPUT->render_from_template('local_location/global_filter', $filterparams);
    echo $renderer->room_view_content();
  } else {
    echo get_string('no_permissions','local_location');
  }
}else{
  echo get_string('no_permissions','local_location');
}

echo $OUTPUT->footer();
