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
 * Classroom Upgrade
 *
 * @package     local_notifications
 * @author:     M Arun Kumar <arun@eabyas.in>
 *
 */
defined('MOODLE_INTERNAL') || die();

function xmldb_local_notifications_upgrade($oldversion) {
    global $DB, $CFG;
    $dbman = $DB->get_manager();
    if ($oldversion < 2017111300) {
        $table = new xmldb_table('local_notification_info');
        $field = new xmldb_field('adminbody',XMLDB_TYPE_TEXT, 'big', null, null, null,null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2017111300, 'local', 'notifications');
    }
    if ($oldversion < 2017111301) {
        $table = new xmldb_table('local_notification_info');
        $field = new xmldb_field('moduletype', XMLDB_TYPE_CHAR, '250', null, null, null,null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('moduleid', XMLDB_TYPE_TEXT, 'big', null, null, null,null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2017111301, 'local', 'notifications');
    }
    if ($oldversion < 2017111305.01) {
        $table = new xmldb_table('local_notification_info');
        $field = new xmldb_field('completiondays', XMLDB_TYPE_INTEGER, '10', null, null, null,0, null);
        if(!$dbman->field_exists($table,  $field)){
            $dbman->add_field($table,  $field);
        }
        upgrade_plugin_savepoint(true, 2017111305.01, 'local', 'notifications');
    }
    // if ($oldversion < 2017111302) {
    //     $time = time();
    //     $notification_type_data = array(
    //     array('name' => 'Program','shortname' => 'program','parent_module' => '0','usermodified' => NULL,'timemodified' => NULL),
    //     array('name' => 'Program Enrollment','shortname' => 'program_enrol','parent_module' => '51','usermodified' => NULL,'timemodified' => NULL),
    //     array('name' => 'Program Unenrollment','shortname' => 'program_unenroll','parent_module' => '51','usermodified' => NULL,'timemodified' => NULL),
    //     array('name' => 'Program Completion','shortname' => 'program_completion','parent_module' => '51','usermodified' => NULL,'timemodified' => NULL),
    //     array('name' => 'Program Level Completion','shortname' => 'program_level_completion','parent_module' => '51','usermodified' => NULL,'timemodified' => NULL),
    //     array('name' => 'Program Session Enrollment','shortname' => 'program_session_enrol','parent_module' => '51','usermodified' => NULL,'timemodified' => NULL),
    //     array('name' => 'Program Session Reschedule','shortname' => 'program_session_reschedule','parent_module' => '51','usermodified' => NULL,'timemodified' => NULL),
    //     array('name' => 'Program Session Attendance','shortname' => 'program_session_attendance','parent_module' => '51','usermodified' => NULL,'timemodified' => NULL),
    //     array('name' => 'Program Session Reminder (before startdate)','shortname' => 'program_session_reminder','parent_module' => '51','usermodified' => NULL,'timemodified' => NULL),
    //     array('name' => 'Program Session Cancel','shortname' => 'program_session_cancel','parent_module' => '51','usermodified' => NULL,'timemodified' => NULL),
    //     array('name' => 'Program Session Completion','shortname' => 'program_session_completion','parent_module' => '51','usermodified' => NULL,'timemodified' => NULL)  
    //         );
    //     foreach($notification_type_data as $notification_type){
    //         if($DB->record_exists('local_notification_type', array('name' => $notification_type['name'], 'shortname' => $notification_type['shortname']))){
    //             $DB->delete_records('local_notification_type', $notification_type);
    //         } else {
    //                $notification_type_obj = (object)$notification_type;
    //             $DB->insert_record('local_notification_type', $notification_type_obj);
    //         }
    //     }
    //     $time = time();
    //     $strings = array(
    //         array('name' => '[program_name]','module' => 'program','usermodified' => NULL,'timemodified' => NULL),
    //         array('name' => '[program_stream]','module' => 'program','usermodified' => NULL,'timemodified' => NULL),
    //         array('name' => '[program_startdate]','module' => 'program','usermodified' => NULL,'timemodified' => NULL),
    //         array('name' => '[program_enddate]','module' => 'program','usermodified' => NULL,'timemodified' => NULL),
    //         array('name' => '[program_level]','module' => 'program','usermodified' => NULL,'timemodified' => NULL),
    //         array('name' => '[program_session_username]','module' => 'program','usermodified' => NULL,'timemodified' => NULL),
    //         array('name' => '[program_sessionsinfo]','module' => 'program','usermodified' => NULL,'timemodified' => NULL),
    //         array('name' => '[program_enroluserfulname]','module' => 'program','usermodified' => NULL,'timemodified' => NULL),
    //         array('name' => '[program_link]','module' => 'program','usermodified' => NULL,'timemodified' => NULL),
    //         array('name' => '[program_enroluseremail]','module' => 'program','usermodified' => NULL,'timemodified' => NULL),
    //         array('name' => '[program_session_useremail]','module' => 'program','usermodified' => NULL,'timemodified' => NULL),
    //         array('name' => '[program_session_trainername]','module' => 'program','usermodified' => NULL,'timemodified' => NULL),
    //         array('name' => '[program_session_attendance]','module' => 'program','usermodified' => NULL,'timemodified' => NULL),
    //         array('name' => '[program_session_startdate]','module' => 'program','usermodified' => NULL,'timemodified' => NULL),
    //         array('name' => '[program_session_enddate]','module' => 'program','usermodified' => NULL,'timemodified' => NULL),
    //         array('name' => '[program_completiondate]','module' => 'program','usermodified' => NULL,'timemodified' => NULL),
    //         array('name' => '[program_organization]','module' => 'program','usermodified' => NULL,'timemodified' => NULL),
    //         array('name' => '[program_course]','module' => 'program','usermodified' => NULL,'timemodified' => NULL),
    //         array('name' => '[program_creater]','module' => 'program','usermodified' => NULL,'timemodified' => NULL),
    //         array('name' => '[program_level_link]','module' => 'program','usermodified' => NULL,'timemodified' => NULL),
    //         array('name' => '[program_lc_course_link]','module' => 'program','usermodified' => NULL,'timemodified' => NULL),
    //         array('name' => '[program_lc_course_sessions_link]','module' => 'program','usermodified' => NULL,'timemodified' => NULL),
    //         array('name' => '[program_lc_course_creater]','module' => 'program','usermodified' => NULL,'timemodified' => NULL),
    //         array('name' => '[program_level_creater]','module' => 'program','usermodified' => NULL,'timemodified' => NULL),
    //         array('name' => '[program_lc_course_sessions_creater]','module' => 'program','usermodified' => NULL,'timemodified' => NULL),
    //         array('name' => '[program_level_completiondate]','module' => 'program','usermodified' => NULL,'timemodified' => NULL),
    //         array('name' => '[program_lc_course_completiondate]','module' => 'program','usermodified' => NULL,'timemodified' => NULL),
    //         array('name' => '[program_lc_course__session_completiondate]','module' => 'program','usermodified' => NULL,'timemodified' => NULL),
    //         array('name' => '[program_session_link]','module' => 'program','usermodified' => NULL,'timemodified' => NULL),
    //         array('name' => '[program_session_name]','module' => 'program','usermodified' => NULL,'timemodified' => NULL),
    //         array('name' => '[program_session_completiondate]','module' => 'program','usermodified' => NULL,'timemodified' => NULL) 
    //         );
    //     foreach($strings as $string){
    //         if($DB->record_exists('local_notification_strings', array('name' => $string['name'], 'module' => $string['module']))){
    //             $DB->delete_records('local_notification_strings', $string);
    //         } else {
    //             $string_obj = (object)$string;
    //             $DB->insert_record('local_notification_strings', $string_obj);
    //         }
    //     }
         
    //     upgrade_plugin_savepoint(true, 2017111302, 'local', 'notifications');
    // }
    if ($oldversion < 2017111305.04) {
        $table = new xmldb_table('local_notification_info');
        if ($dbman->table_exists($table)) {
            $field = new xmldb_field('attach_certificate', XMLDB_TYPE_INTEGER, '10', null, null, null, 0);
            if(!$dbman->field_exists($table, $field)){
                $dbman->add_field($table, $field);
            }
            upgrade_plugin_savepoint(true, 2017111305.04, 'local', 'notifications');
        }
    }

    /* DM-215 - Dipanshu kasera - status field is required */
    if ($oldversion < 2017111305.05) {
        $table = new xmldb_table('local_emaillogs');
        if ($dbman->table_exists($table)) {
            $field = new xmldb_field('status', XMLDB_TYPE_INTEGER, '10', null, null, null, 0);
            if(!$dbman->field_exists($table, $field)){
                $dbman->add_field($table, $field);
            }
            upgrade_plugin_savepoint(true, 2017111305.05, 'local', 'notifications');
        }
    }
        if ($oldversion < 2017111305.05) {
        // data insertion.
        $time = time();
        $initcontent = array('name' => 'Admissions','shortname' => 'admissions','parent_module' => '0','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL);
        $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'admissions'));
        if(!$parentid){
            $parentid = $DB->insert_record('local_notification_type', $initcontent);
        }
        $notification_type_data = array(
           array('name' => 'Aceepted Admissions','shortname' => 'aceepted_admissions','parent_module' => $parentid,'timecreated' => $time,'usermodified' => NULL,'timemodified' => NULL),
           array('name' => 'Rejected Admissions','shortname' => 'rejected_admissions','parent_module' => $parentid,'timecreated' => $time,'usermodified' => NULL,'timemodified' => NULL),
           array('name' => 'Revised Admissions','shortname' => 'aceepted_admissions','parent_module' => $parentid,'timecreated' => $time,'usermodified' => NULL,'timemodified' => NULL)
        );
        foreach($notification_type_data as $notification_type){
            unset($notification_type['timecreated']);
            if(!$DB->record_exists('local_notification_type', $notification_type)){
                $notification_type['timecreated'] = $time;
                $DB->insert_record('local_notification_type', $notification_type);
            }
        }
        $strings = array(
            array('name' => '[admission_program]','module' => 'admissions','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL),
            array('name' => '[employee_id]','module' => 'admissions','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL),
            array('name' => '[email]','module' => 'admissions','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL),
            array('name' => '[status]','module' => 'admissions','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL)
        );
        foreach($strings as $string){
            unset($string['timecreated']);
            if(!$DB->record_exists('local_notification_strings', $string)){
                $string_obj = (object)$string;
                $string_obj->timecreated = $time;
                $DB->insert_record('local_notification_strings', $string_obj);
            }
        }
        upgrade_plugin_savepoint(true, 2017111305.06, 'local', 'notifications');
    }

 
    // Updated record Program Level Completion to Program Semester Completion.
    if ($oldversion < 2017111305.11) {
        $time = time();
        $notification_typeinfo = $DB->get_record('local_notification_type', array('shortname' => 'program_level_completion'));
        $parent_module = $DB->get_record('local_notification_type', array('shortname' => 'program', 'parent_module' => 0));
        if($notification_typeinfo){
            $notificationtypedata = array('id'=>$notification_typeinfo->id, 'name' => 'Program Semester Completion','shortname' => 'program_semester_completion','parent_module' => $parent_module->id,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'program');
            $DB->update_record('local_notification_type', $notificationtypedata );
        }
        upgrade_plugin_savepoint(true, 2017111305.11, 'local', 'notifications');
    }

    if ($oldversion < 2017111305.11) {
        $table = new xmldb_table('local_emaillogs');
        if ($dbman->table_exists($table)) {
            $field = new xmldb_field('admissionid', XMLDB_TYPE_INTEGER, '10', null, null, null, 0);
            if(!$dbman->field_exists($table, $field)){
                $dbman->add_field($table, $field);
            }
            upgrade_plugin_savepoint(true, 2017111305.11, 'local', 'notifications');
        }
    }
        // Updated record Program Level Completion to Program Semester Completion.
    if ($oldversion < 2022051902.70) {
        $time = time();
        // $notification_typeinfo = $DB->get_record('local_notification_type', array('shortname' => 'program_level_completion'));
        $parent_module = $DB->get_record('local_notification_type', array('shortname' => 'program', 'parent_module' => 0));
        if($parent_module){
            $notificationtypedata = array('name' => 'Program Semester Enrollment','shortname' => 'program_semester_enrollment','parent_module' => $parent_module->id,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'program');
            $DB->insert_record('local_notification_type', $notificationtypedata );
        }
        upgrade_plugin_savepoint(true, 2022051902.70, 'local', 'notifications');
    }
    if ($oldversion < 2022051902.58) {
        $time = time();
        $notification_type_data = array(
        array('name' => 'Course','shortname' => 'course','parent_module' => '0','usermodified' => 2,'timemodified' => NULL),
        array('name' => 'Course Enrollment','shortname' => 'course_enrol','parent_module' => '6','usermodified' => 2,'timemodified' => NULL),
        array('name' => 'Course Completion','shortname' => 'course_complete','parent_module' => '6','usermodified' => 2,'timemodified' => NULL),
        array('name' => 'Course Unenrollment','shortname' => 'course_unenroll','parent_module' => '6','usermodified' => 2,'timemodified' => NULL),
        array('name' => 'Course Reminder','shortname' => 'course_reminder','parent_module' => '6','usermodified' => 2,'timemodified' => NULL),
        array('name' => 'New Course Notification','shortname' => 'course_notification','parent_module' => '6','usermodified' => 2,'timemodified' => NULL)
            );
        foreach($notification_type_data as $notification_type){
            if($DB->record_exists('local_notification_type', array('name' => $notification_type['name'], 'shortname' => $notification_type['shortname']))){
                $DB->delete_records('local_notification_type', $notification_type);
            }
        }
        upgrade_plugin_savepoint(true, 2022051902.58, 'local', 'notifications');
    }

    if ($oldversion < 2022051902.61) {
        $time = time();
        $notification_type_data = array(
        array('name' => 'User','shortname' => 'user','parent_module' => '0','usermodified' => 2,'timemodified' => NULL),
        array('name' => 'External user register','shortname' => 'external_user_register','usermodified' => 2,'timemodified' => NULL),
        array('name' => 'Program Session Completion','shortname' => 'program_session_completion','usermodified' => 2,'timemodified' => NULL),
        array('name' => 'Program Session Cancel','shortname' => 'program_session_cancel','usermodified' => 2,'timemodified' => NULL),
        array('name' => 'Program Session Reminder (before startdate)','shortname' => 'program_session_reminder','usermodified' => 2,'timemodified' => NULL),
        array('name' => 'Program Session Attendance','shortname' => 'program_session_attendance','usermodified' => 2,'timemodified' => NULL),
        array('name' => 'Program Session Reschedule','shortname' => 'program_session_reschedule','usermodified' => 2,'timemodified' => NULL),
        array('name' => 'Program Semester Enrollment','shortname' => 'program_semester_enrollment','usermodified' => 2,'timemodified' => NULL),
            );
        foreach($notification_type_data as $notification_type){
            if($DB->record_exists('local_notification_type', array('name' => $notification_type['name'], 'shortname' => $notification_type['shortname']))){
                $DB->delete_records('local_notification_type', $notification_type);
            }
        }
        upgrade_plugin_savepoint(true, 2022051902.61, 'local', 'notifications');
    }

    if ($oldversion < 2022051902.65) {
        $time = time();
        $program_parent_module = $DB->get_record('local_notification_type', array('shortname' => 'program', 'parent_module' => 0));
        $session_enrol_module = $DB->get_record('local_notification_type', array('shortname' => 'program_session_enrol', 'parent_module' => $program_parent_module->id, 'pluginname' => $program_parent_module->pluginname));
        // $parent_module = $DB->get_record('local_notification_type', array('shortname' => 'program', 'parent_module' => 0));
        $session_type_data = array(
            array('name' => 'Program Session Enrollment','shortname' => 'program_session_enrol','usermodified' => 2,'timemodified' => NULL),
                );
        foreach($session_type_data as $session_types_data){
            if($session_enrol_module) {
                $DB->delete_records('local_notification_type', $session_types_data);
            }
        }
        $notification_type_data = array(
            array('name' => 'Program Semester Enrollment', 'shortname' => 'program_semester_enrollment', 'parent_module' => $program_parent_module->id, 'usercreated' => 2, 'timecreated' => $time, 'usermodified' => 2, 'timemodified' => NULL, 'pluginname' => $program_parent_module->pluginname),

        );
        foreach ($notification_type_data as $notification_type) {
            if (!$DB->record_exists('local_notification_type', array('name' => $notification_type['name'], 'shortname' => $notification_type['shortname']))) {
                // $DB->delete_records('local_notification_type', $notification_type);
                $DB->insert_record('local_notification_type', $notification_type);
            }
        }
        upgrade_plugin_savepoint(true, 2022051902.65, 'local', 'notifications');
    }

    if ($oldversion < 2022051902.68) {
        $time = time();
        $program_parent_module = $DB->get_record('local_notification_type', array('shortname' => 'program', 'parent_module' => 0));
        $notification_type_data = array(
        array('name' => 'Program Semester Unenrollment', 'shortname' => 'program_semester_unenrollment'),
        array('name' => 'Program Course Unenrollment', 'shortname' => 'program_course_unenrollment'),
            );
        foreach($notification_type_data as $notification_type){
            // if($notification_type){
                $DB->delete_records('local_notification_type', $notification_type);
            // }
        }
        upgrade_plugin_savepoint(true, 2022051902.68, 'local', 'notifications');
    }
    if ($oldversion < 2022051902.71) {
        $table = new xmldb_table('local_emaillogs');
        if ($dbman->table_exists($table)) {
            $field = new xmldb_field('admissionid', XMLDB_TYPE_INTEGER, '10', null, null, null, 0);
            if(!$dbman->field_exists($table, $field)){
                $dbman->add_field($table, $field);
            }
        }
        upgrade_plugin_savepoint(true, 2022051902.71, 'local', 'notifications');
    }
    if ($oldversion < 2022051902.72) {
        // $time = time();
        $notification_type_data = array(
        array('name' => 'Course','shortname' => 'course','parent_module' => '0','usermodified' => 2,'timemodified' => NULL),
        array('name' => 'Course Enrollment','shortname' => 'course_enrol','parent_module' => '6','usermodified' => 2,'timemodified' => NULL),
        array('name' => 'Course Completion','shortname' => 'course_complete','parent_module' => '6','usermodified' => 2,'timemodified' => NULL),
        array('name' => 'Course Unenrollment','shortname' => 'course_unenroll','parent_module' => '6','usermodified' => 2,'timemodified' => NULL),
        array('name' => 'Course Reminder','shortname' => 'course_reminder','parent_module' => '6','usermodified' => 2,'timemodified' => NULL),
        array('name' => 'New Course Notification','shortname' => 'course_notification','parent_module' => '6','usermodified' => 2,'timemodified' => NULL)
            );
        foreach($notification_type_data as $notification_type){
            if($DB->record_exists('local_notification_type', array('name' => $notification_type['name'], 'shortname' => $notification_type['shortname']))){
                $DB->delete_records('local_notification_type', $notification_type);
            }
        }
        upgrade_plugin_savepoint(true, 2022051902.72, 'local', 'notifications');
    }
    
    if ($oldversion < 2022051902.73) {
        $time = time();
        $parentmodule = $DB->get_record('local_notification_type', array('shortname' => 'program', 'parent_module' => 0, 'name' => 'Program'));
        $notification_type_data = array(
        array('name' => 'Program Session Enrollment','shortname' => 'program_session_enrol','parent_module' => '12','usermodified' => 2,'timemodified' => NULL),
        array('name' => 'Program Session Reschedule','shortname' => 'program_session_reschedule','parent_module' => '12','usermodified' => 2,'timemodified' => NULL),
        array('name' => 'Program Session Attendance','shortname' => 'program_session_attendance','parent_module' => '12','usermodified' => 2,'timemodified' => NULL),
        array('name' => 'Program Session Reminder (before startdate)','shortname' => 'program_session_reminder','parent_module' => '12','usermodified' => 2,'timemodified' => NULL),
        array('name' => 'Program Session Cancel','shortname' => 'program_session_cancel','parent_module' => '12','usermodified' => 2,'timemodified' => NULL),
        array('name' => 'Program Session Completion','shortname' => 'program_session_completion','parent_module' => '12','usermodified' => 2,'timemodified' => NULL),
        array('name' => 'User','shortname' => 'user','parent_module' => '0','usermodified' => 2,'timemodified' => NULL),
        array('name' => 'External user register','shortname' => 'external_user_register','usermodified' => 2,'timemodified' => NULL)
            );
        foreach($notification_type_data as $notification_type){
            if($DB->record_exists('local_notification_type', array('name' => $notification_type['name'], 'shortname' => $notification_type['shortname']))){
                $DB->delete_records('local_notification_type', $notification_type);
            }
        }

        $notificationdata = array(
            array('name' => 'Program Course Enrollment', 'shortname' => 'program_course_enrollment', 'parent_module' => $parentmodule->id, 'usercreated' => 2, 'timecreated' => $time, 'usermodified' => 2, 'timemodified' => NULL, 'pluginname' => $parentmodule->pluginname),
            array('name' => 'Program Semester Enrollment', 'shortname' => 'program_semester_enrollment', 'parent_module' => $parentmodule->id, 'usercreated' => 2, 'timecreated' => $time, 'usermodified' => 2, 'timemodified' => NULL, 'pluginname' => $parentmodule->pluginname),
            array('name' => 'Program Course Completion', 'shortname' => 'program_course_completion', 'parent_module' => $parentmodule->id, 'usercreated' => 2, 'timecreated' => $time, 'usermodified' => 2, 'timemodified' => NULL, 'pluginname' => $parentmodule->pluginname)
        );
        foreach ($notificationdata as $notification_type) {
            if (!$DB->record_exists('local_notification_type', array('name' => $notification_type['name'], 'shortname' => $notification_type['shortname']))) {
                $DB->insert_record('local_notification_type', $notification_type);
            }
        }
        upgrade_plugin_savepoint(true, 2022051902.73, 'local', 'notifications');
    }
    if ($oldversion < 2022051902.74) {
        $table = new xmldb_table('local_emaillogs');
        if ($dbman->table_exists($table)) {
            $field = new xmldb_field('admissionid', XMLDB_TYPE_INTEGER, '10', null, null, null, 0);
            if(!$dbman->field_exists($table, $field)){
                $dbman->add_field($table, $field);
            }
            upgrade_plugin_savepoint(true, 2022051902.74, 'local', 'notifications');
        }
    }
    if ($oldversion < 2022051902.76) {
        $time = time();
        $parentmodule = $DB->get_record('local_notification_type', array('shortname' => 'program', 'parent_module' => 0, 'name' => 'Program'));
        $notification_type_data = array(
        array('name' => 'Program Session Enrollment','shortname' => 'program_session_enrol','parent_module' => '12','usermodified' => 2,'timemodified' => NULL),
        array('name' => 'Program Session Reschedule','shortname' => 'program_session_reschedule','parent_module' => '12','usermodified' => 2,'timemodified' => NULL),
        array('name' => 'Program Session Attendance','shortname' => 'program_session_attendance','parent_module' => '12','usermodified' => 2,'timemodified' => NULL),
        array('name' => 'Program Session Reminder (before startdate)','shortname' => 'program_session_reminder','parent_module' => '12','usermodified' => 2,'timemodified' => NULL),
        array('name' => 'Program Session Cancel','shortname' => 'program_session_cancel','parent_module' => '12','usermodified' => 2,'timemodified' => NULL),
        array('name' => 'Program Session Completion','shortname' => 'program_session_completion','parent_module' => '12','usermodified' => 2,'timemodified' => NULL),
        array('name' => 'User','shortname' => 'user','parent_module' => '0','usermodified' => 2,'timemodified' => NULL),
        array('name' => 'External user register','shortname' => 'external_user_register','usermodified' => 2,'timemodified' => NULL)
            );
        foreach($notification_type_data as $notification_type){
            if($DB->record_exists('local_notification_type', array('name' => $notification_type['name'], 'shortname' => $notification_type['shortname']))){
                $DB->delete_records('local_notification_type', $notification_type);
            }
        }
        $notificationdata = array(
            array('name' => 'Program Course Enrollment', 'shortname' => 'program_course_enrollment', 'parent_module' => $parentmodule->id, 'usercreated' => 2, 'timecreated' => $time, 'usermodified' => 2, 'timemodified' => NULL, 'pluginname' => $parentmodule->pluginname),
            array('name' => 'Program Semester Enrollment', 'shortname' => 'program_semester_enrollment', 'parent_module' => $parentmodule->id, 'usercreated' => 2, 'timecreated' => $time, 'usermodified' => 2, 'timemodified' => NULL, 'pluginname' => $parentmodule->pluginname),
            array('name' => 'Program Course Completion', 'shortname' => 'program_course_completion', 'parent_module' => $parentmodule->id, 'usercreated' => 2, 'timecreated' => $time, 'usermodified' => 2, 'timemodified' => NULL, 'pluginname' => $parentmodule->pluginname)
        );
        foreach ($notificationdata as $notification_type) {
            if (!$DB->record_exists('local_notification_type', array('name' => $notification_type['name'], 'shortname' => $notification_type['shortname']))) {
                $DB->insert_record('local_notification_type', $notification_type);
            }
        }
        $notificatin_typ_data = array(
        array('name' => 'Course','shortname' => 'course','parent_module' => '0','usermodified' => 2,'timemodified' => NULL),
        array('name' => 'Course Enrollment','shortname' => 'course_enrol','parent_module' => '6','usermodified' => 2,'timemodified' => NULL),
        array('name' => 'Course Completion','shortname' => 'course_complete','parent_module' => '6','usermodified' => 2,'timemodified' => NULL),
        array('name' => 'Course Unenrollment','shortname' => 'course_unenroll','parent_module' => '6','usermodified' => 2,'timemodified' => NULL),
        array('name' => 'Course Reminder','shortname' => 'course_reminder','parent_module' => '6','usermodified' => 2,'timemodified' => NULL),
        array('name' => 'New Course Notification','shortname' => 'course_notification','parent_module' => '6','usermodified' => 2,'timemodified' => NULL)
            );
        foreach($notificatin_typ_data as $notification_type){
            if($DB->record_exists('local_notification_type', array('name' => $notification_type['name'], 'shortname' => $notification_type['shortname']))){
                $DB->delete_records('local_notification_type', $notification_type);
            }
        }
        upgrade_plugin_savepoint(true, 2022051902.76, 'local', 'notifications');
    }

    return true;
}
