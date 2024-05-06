<?php
namespace local_timetable;
class lib {
    public function can_access_event($event){
        global $DB, $USER;
        $record = identify_role($USER->id);
        $role = identify_teacher_role($USER->id);
        $return = false;
        if (is_siteadmin() || $record->shortname == 'student' || $role->shortname == 'editingteacher' || $role->shortname == 'orgadmin' || $role->shortname == 'collegeadmin') {
            return true;
        }
        $coursecontext = context_course::instance($event->courseid);
        if($event->modulename == 'attendance' && has_capability('local/classmanagement:manageself_attendance', $coursecontext)){
            $teacherids = $DB->get_field('attendance_session', 'teacherid', array('caleventid' => $event->id));
            $teachers = explode(',', $teacherids);
            if(in_array($USER->id, $teachers)){
                return true;
            }
        }else{
            $is_enrolled = is_enrolled($coursecontext, $USER->id);
            if($is_enrolled){
                return true;
            }
        }
        return $return;
    }
    public function get_event_info($event){
        global $DB;
        $methodname = $event->modulename.'_event';
        if(method_exists($this, $methodname)){
            $eventdata = $this->$methodname($event);
        }else{
            $eventdata = $event;
        }
        $courseinfo = get_course($event->courseid);
        if($event->sessionid % 2 == 0){ //internal
            $eventdata->eventcolor = '#b073e7';
        }else{
            $eventdata->eventcolor = '#FE6B64';
        }
        return $eventdata;
    }
    private function attendance_event($event){
        global $DB, $CFG, $USER;
        require_once($CFG->dirroot.'/local/lib.php');
        $eventsql = "SELECT ats.id, ats.sessionname AS name, ats.*, cm.id as cmid
                       FROM {attendance_sessions} AS ats
                       JOIN {modules} AS m ON m.name LIKE 'attendance'
                       JOIN {course_modules} AS cm ON cm.instance = ats.attendanceid AND cm.module = m.id
                      WHERE ats.caleventid = :caleventid AND cm.course = :course";
        $eventdata = $DB->get_record_sql($eventsql, array('caleventid' => $event->id, 'course' => $event->courseid));
        $semid = $DB->get_field('local_program_level_courses', 'levelid', ['courseid' => $eventdata->courseid]);
        $role = identify_teacher_role($USER->id);
        if (is_siteadmin() || $role->shortname == 'editingteacher' || $role->shortname == 'orgadmin' || $role->shortname == 'collegeadmin') {
            $eventdata->dataurl = "$CFG->wwwroot/local/attendance/take.php?id={$eventdata->cmid}&sessionid={$eventdata->id}&grouptype=0&viewmode=2&semid={$semid}&batch_group={$eventdata->batch_group}";
            $eventdata->launchstring = 'Mark Attendance';
            $eventdata->imgdataurl = "$CFG->wwwroot/local/attendance/password.php?session={$eventdata->id}";
            $eventdata->config = $CFG->wwwroot;
        }
        return $eventdata;
    }
}
