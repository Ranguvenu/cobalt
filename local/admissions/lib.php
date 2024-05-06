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
 * @subpackage local_admissions
 */

defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot.'/user/editlib.php');

function local_admissions_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
        // Check the contextlevel is as expected - if your plugin is a block, this becomes CONTEXT_BLOCK, etc.
    
        // Make sure the filearea is one of those used by the plugin.
        if ($filearea !== 'uploaddocs') {
            return false;
        }
    
        $itemid = array_shift($args);
    
        $filename = array_pop($args);
        if (!$args) {
            $filepath = '/';
        } else {
            $filepath = '/'.implode('/', $args).'/';
        }
    
        // Retrieve the file from the Files API.
        $fs = get_file_storage();
        $file = $fs->get_file($context->id, 'local_admissions', $filearea, $itemid, $filepath, $filename);
        if (!$file) {
            return false;
        }
        send_file($file, $filename, 0, $forcedownload, $options);
    }

/**
 * Function to display the assign role form in popup
 * returns data of the popup
 */
function local_admissions_output_fragment_new_statusconfirmform($args) {
    global $CFG, $DB, $PAGE;
    $args = (object) $args;
    $context = $args->context;
    $systemcontext = \context_system::instance();
  
    $o = '';
    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        parse_str($serialiseddata, $formdata);
    }

    $programid = $DB->get_record('local_program', array('id' => $args->programid));
    $admissionid = $DB->get_record('local_users', array('id' => $args->admissionid));

    if ($args->admissionid > 0) {
        $heading = 'Update User';
        $collapse = false;
        $data = $DB->get_record('local_users', array('id' => $args->admissionid));

        $mform = new local_admissions\form\statusconfirmform(null, array('admissionid' => $admissionid->id, 'programid'=>$programid->id), 'post', '', null, true, $formdata);
        // $mform->set_data($data);
    } else {
        $mform = new local_admissions\form\statusconfirmform(null, array('admissionid'=>$admissionid->id, 'programid'=>$programid->id), 'post', '', null, true, $formdata);    
    }
    if (!empty($args->jsonformdata) && strlen($args->jsonformdata) >2) {
        // If we were passed non-empty form data we want the mform to call validation functions and show errors.
        $mform->is_validated();
    }
    ob_start();
    $mform->display();
    $o .= ob_get_contents();
    ob_end_clean();
    return $o;
}

/**
 * Function to display the assign role form in popup
 * returns data of the popup
 */
function local_admissions_output_fragment_new_rejectconfirmform($args) {
    global $CFG, $DB, $PAGE;
    $args = (object) $args;
    $context = $args->context;
    $systemcontext = \context_system::instance();
  
    $o = '';
    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        parse_str($serialiseddata, $formdata);
    }

    $programid = $DB->get_record('local_program', array('id' => $args->programid));
    $admissionid = $DB->get_record('local_users', array('id' => $args->admissionid));

    if ($args->admissionid > 0) {
        $heading = 'Update User';
        $collapse = false;
        $data = $DB->get_record('local_users', array('id' => $args->admissionid));

        $mform = new local_admissions\form\rejectconfirmform(null, array('admissionid' => $admissionid->id, 'programid'=>$programid->id), 'post', '', null, true, $formdata);
        // $mform->set_data($data);
    } else {
        $mform = new local_admissions\form\rejectconfirmform(null, array('admissionid'=>$admissionid->id, 'programid'=>$programid->id), 'post', '', null, true, $formdata);    
    }
    if (!empty($args->jsonformdata) && strlen($args->jsonformdata) >2) {
        // If we were passed non-empty form data we want the mform to call validation functions and show errors.
        $mform->is_validated();
    }
    ob_start();
    $mform->display();
    $o .= ob_get_contents();
    ob_end_clean();
    return $o;
}


/*
* Displays a node in left side menu
* @return  [type] string  link for the leftmenu
*/
function local_admissions_leftmenunode() {
    global $DB, $USER;
    $systemcontext = context_system::instance();
    $assignrolesnode = '';
    $loginusers = $DB->get_record_sql("SELECT DISTINCT(r.id),r.shortname FROM {role} r
                JOIN {role_assignments} ra ON ra.roleid = r.id
                JOIN {user} u ON u.id = ra.userid
            WHERE u.id = {$USER->id} AND r.shortname = 'student'");
    if (is_siteadmin()) {
        $assignrolesnode .= html_writer::start_tag('li', array('id' => 'id_leftmenu_assign_roles', 'class' =>
            'pull-left user_nav_div assign_roles'));
            $usersurl = new moodle_url('/local/admissions/view.php');
            $users = html_writer::link($usersurl,
                '<span class="admissions_structure_icon dypatil_cmn_icon icon"></span>
                <span class="user_navigation_link_text">'.get_string('pluginname', 'local_admissions').'</span>',
                array('class' => 'user_navigation_link'));
            $assignrolesnode .= $users;
        $assignrolesnode .= html_writer::end_tag('li');
    } else if ($loginusers){
         $assignrolesnode .= html_writer::start_tag('li', array('id' => 'id_leftmenu_assign_roles', 'class' =>
            'pull-left user_nav_div assign_roles'));
            $studenturl = new moodle_url('/local/admissions/studentview.php');
            $users = html_writer::link($studenturl,
                '<span class="admissions_structure_icon dypatil_cmn_icon iconn"></span>
                <span class="user_navigation_link_text">'.get_string('myapplication', 'local_admissions').'</span>',
                array('class' => 'user_navigation_link'));
            $assignrolesnode .= $users;
        $assignrolesnode .= html_writer::end_tag('li');
    }
    return array('13' => $assignrolesnode);
}



