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
 * @author Dipanshu Kasera
 * @package ODL
 * @subpackage local_timetable
 */
define('AJAX_SCRIPT', true);
require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB;
require_once($CFG->dirroot.'/local/timetable/lib.php');
require_once($CFG->dirroot.'/local/lib.php');
$semid = optional_param('semid', 0, PARAM_INT);
$start = required_param('start', PARAM_RAW);
$end = required_param('end', PARAM_RAW);
$startdate = strtotime($start);
$enddate = strtotime($end)+86400;

$params = array();
$eventsSql = "SELECT e.*, ats.id as sessionid
               FROM {event} e
               JOIN {attendance_sessions} ats ON e.id = ats.caleventid
               JOIN {local_program_level_courses} lplc ON e.courseid = lplc.courseid ";

$role = identify_teacher_role($USER->id);
if ($role->shortname == 'orgadmin' || $role->shortname == 'collegeadmin' || is_siteadmin()) {
    $eventsSql .= " JOIN {local_program} lp ON lplc.programid = lp.id ";
}

$params['timestart'] = $startdate;
$params['timeend'] = $enddate;
// $eventsSql .= " WHERE e.timestart >= :timestart AND e.timestart <= :timeend ";    
$eventsSql .= " WHERE 1=1 ";

if ($semid > 0) {
    $params['semid'] = $semid;
    $eventsSql .= " AND lplc.levelid = :semid ";
}

if ($role->shortname == 'editingteacher') {
    $params['userid'] = $USER->id;
    $eventsSql .= " AND ats.teacherid = :userid";
} else {
    $eventsSql .= " AND ats.teacherid > 0";
}

if ($role->shortname == 'orgadmin') {
    $params['costcenter'] = $USER->open_costcenterid;
    $eventsSql .= " AND lp.costcenter = :costcenter";
}

if ($role->shortname == 'collegeadmin') {
    $params['costcenter'] = $USER->open_costcenterid;
    $params['department'] = $USER->open_departmentid;
    $eventsSql .= " AND lp.costcenter = :costcenter AND lp.department = :department";
}

$record = identify_role($USER->id);
if ($record->shortname == 'student' && !$role->shortname == 'editingteacher' && !is_siteadmin()) {
    $programid = $DB->get_field('local_program_users', 'programid', ['userid' => $USER->id]);
    $params['programid'] = $programid;
    $eventsSql .= " AND lplc.programid = :programid";
}

$events = $DB->get_records_sql($eventsSql, $params);

$returnevents = [];
$eventlib = new \local_timetable\lib();
foreach($events AS $event){
    if(!$eventlib->can_access_event($event)){
        continue;
    }
    $eventdata = $eventlib->get_event_info($event);
    $eventinfo = new stdClass();
    $eventinfo->start = date('Y-m-d H:i', $event->timestart);
    $eventinfo->end = date('Y-m-d H:i', $event->timestart + $event->timeduration);
    $eventinfo->resourceId = 'ABC';
    $eventinfo->title = strip_tags($eventdata->name);
    $eventinfo->color = $eventdata->eventcolor;
    $eventinfo->editable = false;
    $eventdata->startdatetime = date('d-m-Y H:i', $event->timestart);
    $eventdata->enddatetime = date('d-m-Y H:i', $event->timestart + $event->timeduration);
    if (empty($eventdata->building) || $eventdata->building == 0) {
        $eventdata->building = 'N/A';
    } else {
        $eventdata->building = $DB->get_field('local_location_institutes', 'fullname', ['id' => $eventdata->building]);
    }
    if (empty($eventdata->room) || $eventdata->room == 0) {
        $eventdata->room = 'N/A';
    } else {
        $eventdata->room = $DB->get_field('local_location_room', 'name', ['id' => $eventdata->room]);
    }
    $teacherdetails = $DB->get_record('user', ['id' => $eventdata->teacherid]);
    $teachername = fullname($teacherdetails);
    $eventdata->teacher = $teachername;
    $sessionname = $DB->get_field('local_session_type', 'session_type', ['id' => $eventdata->session_type]);
    if (empty($sessionname)) {
        $sessionname = 'N/A';
    }
    $eventdata->session_type = $sessionname;
    $eventinfo->extendedProps = $eventdata;
    $returnevents[] = $eventinfo;
}
echo json_encode($returnevents);
