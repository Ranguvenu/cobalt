<?php
defined('MOODLE_INTERNAL') || die();
function xmldb_local_users_install(){
	 global $CFG, $USER, $DB, $OUTPUT;
	 $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes
	 $table = new xmldb_table('user');
	 if ($dbman->table_exists($table)) {
	 	

        $field1 = new xmldb_field('open_costcenterid');
        $field1->set_attributes(XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        $table->add_key('open_costcenterid', XMLDB_KEY_FOREIGN, array('open_costcenterid'), 'local_costcenter', array('id'));
        }


        $field2 = new xmldb_field('open_supervisorid');
        $field2->set_attributes(XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }


        $field5 = new xmldb_field('open_employeeid');
        $field5->set_attributes(XMLDB_TYPE_CHAR, '255', null, null, null, null);
        if (!$dbman->field_exists($table, $field5)) {
            $dbman->add_field($table, $field5);
        }


        $field6 = new xmldb_field('open_usermodified');
        $field6->set_attributes(XMLDB_TYPE_INTEGER, '10', null, null, null, null);   
        if (!$dbman->field_exists($table, $field6)) {
            $dbman->add_field($table, $field6);
        }
     
        $field11 = new xmldb_field('open_state');
        $field11->set_attributes(XMLDB_TYPE_CHAR, '200', null, null, null, null);
        if (!$dbman->field_exists($table, $field11)) {
            $dbman->add_field($table, $field11);
        }
     
        $field14 = new xmldb_field('open_group');
        $field14->set_attributes(XMLDB_TYPE_CHAR, '200', null, null, null, null);
         if (!$dbman->field_exists($table, $field14)) {
            $dbman->add_field($table, $field14);
        }

        $field18 = new xmldb_field('open_qualification');
        $field18->set_attributes(XMLDB_TYPE_CHAR, '200', null, null, null, null);
        if (!$dbman->field_exists($table, $field18)) {
            $dbman->add_field($table, $field18);
        }

        $field19 = new xmldb_field('open_departmentid');
        $field19->set_attributes(XMLDB_TYPE_INTEGER, '10', null, null, null, null);
         if (!$dbman->field_exists($table, $field19)) {
            $dbman->add_field($table, $field19);
            $table->add_key('open_departmentid', XMLDB_KEY_FOREIGN, array('open_departmentid'), 'local_costcenter', array('id'));
        }

        $field21 = new xmldb_field('open_subdepartment');
        $field21->set_attributes(XMLDB_TYPE_INTEGER, '10', null, null, null, null);
         if (!$dbman->field_exists($table, $field21)) {
            $dbman->add_field($table, $field21);
        $table->add_key('open_subdepartment', XMLDB_KEY_FOREIGN, array('open_subdepartment'), 'local_costcenter', array('id'));
        }

        $field22 = new xmldb_field('open_type');
        $field22->set_attributes(XMLDB_TYPE_INTEGER, '10', null, null, null, null);
         if (!$dbman->field_exists($table, $field22)) {
            $dbman->add_field($table, $field22);
        }

        $field24 = new xmldb_field('levelofapprove');
        $field24->set_attributes(XMLDB_TYPE_INTEGER, '10', null, null, null, null);
         if (!$dbman->field_exists($table, $field24)) {
            $dbman->add_field($table, $field24);
        }

        $field25 = new xmldb_field('roleid');
        $field25->set_attributes(XMLDB_TYPE_INTEGER, '10', null, null, null, null);
         if (!$dbman->field_exists($table, $field25)) {
            $dbman->add_field($table, $field25);
        } 

        $field26 = new xmldb_field('rollno');
        $field26->set_attributes(XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
         if (!$dbman->field_exists($table, $field26)) {
            $dbman->add_field($table, $field26);
        } 	
	}

    /*notifictaions content*/
    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes
    $table = new xmldb_table('local_notification_type');
    if (!$dbman->table_exists($table)) {
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('shortname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('parent_module', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('pluginname', XMLDB_TYPE_CHAR, '255', null, null, null, '0');

        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $result = $dbman->create_table($table);
    }
    $table = new xmldb_table('local_notification_info');
    if (!$dbman->table_exists($table)) {
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('costcenterid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('notificationid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        
        $table->add_field('moduletype', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('moduleid', XMLDB_TYPE_TEXT, null, null, null, null, null);
        // courses
        $table->add_field('reminderdays', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('attach_certificate', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('completiondays', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('enable_cc', XMLDB_TYPE_INTEGER, '1', null, null, null, '0');
        $table->add_field('active', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('subject', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('body', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, '0');
        $table->add_field('adminbody', XMLDB_TYPE_TEXT, null, null, null, null, '0');
        $table->add_field('attachment_filepath', XMLDB_TYPE_CHAR, null, null, null, null, '0');
        $table->add_field('status', XMLDB_TYPE_INTEGER, 10, null, null, null, '0');
        
        $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');

        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('foreign', XMLDB_KEY_FOREIGN, array('costcenterid'));
        $result = $dbman->create_table($table);
    }
    $table = new xmldb_table('local_emaillogs');
    if (!$dbman->table_exists($table)) {
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('notification_infoid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('from_userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('to_userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        
        $table->add_field('from_emailid', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('to_emailid', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('moduletype', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('moduleid', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('teammemberid', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        // courses
        $table->add_field('reminderdays', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('enable_cc', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('active', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('subject', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('emailbody', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, '0');
        $table->add_field('adminbody', XMLDB_TYPE_TEXT, null, null, null, null, '0');
        $table->add_field('attachment_filepath', XMLDB_TYPE_CHAR, null, null, null, null, '0');

        $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');

        $table->add_field('sent_date', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('sent_by', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('status', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('admissionid', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $result = $dbman->create_table($table);
    }
    $table = new xmldb_table('local_notification_strings');
    if (!$dbman->table_exists($table)) {
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('module', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        
        $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $result = $dbman->create_table($table);
    }
    $time = time();
    $initcontent = array('name' => 'User','shortname' => 'user','parent_module' => '0','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'users');
    $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'user'));
    if(!$parentid){
        $parentid = $DB->insert_record('local_notification_type', $initcontent);
    }
    // $notification_type_data = array(
    //     array('name' => 'External user register','shortname' => 'external_user_register','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'users')
    // );
    // foreach($notification_type_data as $notification_type){
    //     unset($notification_type['timecreated']);
    //     if(!$DB->record_exists('local_notification_type',  $notification_type)){
    //         $notification_type['timecreated'] = $time;
    //         $DB->insert_record('local_notification_type', $notification_type);
    //     }
    // }
    $strings = array(
        array('name' => '[user_name]','module' => 'user','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL),
        array('name' => '[user_email]','module' => 'user','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL),
    );
    foreach($strings as $string){
        unset($string['timecreated']);
        if(!$DB->record_exists('local_notification_strings', $string)){
            $string_obj = (object)$string;
            $string_obj->timecreated = $time;
            $DB->insert_record('local_notification_strings', $string_obj);
        }
    }
}
