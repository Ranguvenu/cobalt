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
 *
 * @package    notifications
 * @copyright  2022 eAbyas Info Solutions
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_notifications\forms;
require_once($CFG->libdir . '/formslib.php');
// require_once($CFG->dirroot .'/local/notifications/lib.php');
use moodleform;
use stdClass;
class notification_form extends moodleform {
    public $formstatus;
    public function __construct($action = null, $customdata = null, $method = 'post', $target = '', $attributes = null, $editable = true, $formdata = null) {

        $this->formstatus = array(
            'generaldetails' => get_string('generaldetails', 'local_users'),
            'otherdetails' => get_string('otherdetails', 'local_users'),
            );
        parent::__construct($action, $customdata, $method, $target, $attributes, $editable, $formdata);
    }

    public function definition() {
        global $DB, $PAGE, $USER, $OUTPUT;
        $mform = $this->_form;
        $lib = new \notifications();
        $form_status = $this->_customdata['form_status'];
        $org = $this->_customdata['org'];
        $id = $this->_customdata['id'] > 0 ? $this->_customdata['id'] : 0;
        $context = \context_system::instance();
		
		$moduleid = $this->_customdata['moduleid'];
		$notificationid = $this->_customdata['notificationid'];
		$labelstring = get_config('local_costcenter');
		if($id){
       		$formdata = $DB->get_record('local_notification_info', array('id' => $id));
		}else{
			$formdata = new stdClass();
		}
        if ($form_status == 0) {       
            if (is_siteadmin($USER->id)) {
    			$department = $DB->get_records_sql_menu("SELECT * FROM {local_costcenter} WHERE visible = 1 AND parentid = 0");

				$departments = array(null => get_string('select_organization', 'local_notifications', $labelstring->firstlevel)) + $department;
    			$mform->addElement('select', 'costcenterid', get_string('organization', 'local_notifications', $labelstring->firstlevel), $departments);
    			// $mform->setType('costcenterid', PARAM_INT);
    			$mform->addRule('costcenterid', null, 'required', null, 'client');
    		} elseif (!is_siteadmin()  && has_capability('local/costcenter:assign_multiple_departments_manage',$context)){
    			$user_dept = $DB->get_field('user','open_costcenterid', array('id'=>$USER->id));
    			$department = $DB->get_records_sql_menu("SELECT * FROM {local_costcenter} WHERE visible = 1 AND id = $user_dept");
    			$dept = array(null => get_string('select_organization', 'local_notifications', $labelstring->firstlevel));
    			if ($department) {
    			    $departments = $dept + $department;
    			} else {
    			    $departments = $department;
    			}
    			$mform->addElement('select', 'costcenterid', get_string('organization', 'local_notifications', $labelstring->firstlevel), $departments);
    			$mform->setType('costcenterid', PARAM_INT);
    			$mform->setConstant('costcenterid', $user_dept);
    		} else {
    			$user_dept = $DB->get_field('user','open_costcenterid', array('id'=>$USER->id));
    			$mform->addElement('hidden', 'costcenterid', null, array('id' => 'id_costcenterid'));
    			$mform->setType('costcenterid', PARAM_INT);
    			$mform->setConstant('costcenterid', $user_dept);
    		}
            
            $notification_type = array();
            $select = array();
            $select[null] = get_string('select_opt', 'local_notifications');
            $notification_type[null] = $select;
            $module_categories = $DB->get_records('local_notification_type', array('parent_module'=>0));
            if($module_categories){
                 foreach($module_categories as $module_category){
                    $notifications = $DB->get_records_sql_menu("SELECT * FROM {local_notification_type} WHERE parent_module = {$module_category->id} AND parent_module <> 0 AND shortname NOT IN ('classroom_invitation', 'program_invitation','forum','forum_subscription','forum_unsubscription','forum_reply','forum_post','lep_reminder')");
                    $notification_type[$module_category->name] = $notifications;
                }
            }
            
            $mform->addElement('selectgroups', 'notificationid', get_string('notification_type', 'local_notifications'), $notification_type, array());
            $mform->addRule('notificationid', null, 'required', null, 'client');  
            $mform->addHelpButton('notificationid','notification_help','local_notifications');
            $mform->addElement('text', 'subject', get_string('subject', 'local_notifications'));
            $mform->setType('subject', PARAM_RAW);
			
            $datamoduleids=array();
			$datamodule_label="Courses";
			$strings = 'None';
			$notification_selected = $this->_ajaxformdata['notificationid'];
			$organization_selected = $this->_ajaxformdata['costcenterid'];
            if($id > 0 || ($notificationid&&is_array($moduleid)&&!empty($moduleid))){
				if($id > 0){
					$notifyid = $DB->get_record('local_notification_info',  array('id'=>$id));
					
					$notif_type = $DB->get_field('local_notification_type', 'shortname', array('id'=>$notifyid->notificationid));
				}else{

					$notif_type = $DB->get_field('local_notification_type', 'shortname', array('id'=>$notificationid));
					$notifyid=new stdClass();
					$notif_type_find=explode('_',$notif_type);
					$notifyid->moduletype=$notif_type_find[0];
					$notifyid->costcenterid=$org;
				}
				$data = $this->get_datamoduleids_labels($notifyid->moduletype, $notifyid->costcenterid);
				$datamoduleids = $data['datamoduleids'];
				$datamodule_label = $data['datamodule_label'];
				$strings = $lib->get_string_identifiers($notif_type);
				
			
    		}else if($notification_selected && $organization_selected){
    			$notif_type = $DB->get_field('local_notification_type', 'shortname', array('id'=>$notification_selected));
    			$notif_type_find = explode('_',$notif_type);
				$moduletype = $notif_type_find[0]; 
				$data = $this->get_datamoduleids_labels($moduletype, $organization_selected);
				// $datamoduleids = $data['datamoduleids'];
				// $datamodule_label = $data['datamodule_label'];
				// $strings = $lib->get_string_identifiers($notif_type);
    		}
    		if(strtolower($notifyid->moduletype)!='request'){
	    		$mform->addElement('text', 'reminderdays', get_string('reminderdays', 'local_notifications'));
	            $mform->setType('reminderdays', PARAM_INT);
	            $notifications = $DB->get_records_sql_menu("SELECT id, id as nid FROM {local_notification_type} WHERE shortname NOT IN ('course_reminder','classroom_reminder','onlinetest_reminder','onlinetest_due','program_reminder','program_session_reminder','certification_reminder', 'feedback_due')");
	    		$mform->hideIf('reminderdays', 'notificationid', 'in', $notifications);
	    		$mform->hideIf('reminderdays', 'notificationid', 'eq', '');

				$pos = 0;
				$new = 'All';
				$datamoduleids[$pos] = $new;

				$courseselect = $mform->addElement('autocomplete', 'moduleid',$datamodule_label,$datamoduleids,array('class' => 'module_label'));
				$mform->setType('moduleid', PARAM_RAW);
				$courseselect->setMultiple(true);

				$nonmodulenotifications = $DB->get_records_sql_menu("SELECT id, id as nid FROM {local_notification_type} WHERE parent_module IN (SELECT lnti.id FROM {local_notification_type} AS lnti WHERE shortname IN ('certification', 'request', 'challenge'))");
	    		$mform->hideIf('moduleid', 'notificationid', 'in', $nonmodulenotifications);
				$mform->hideIf('moduleid', 'notificationid', 'eq', NULL);
		   	}
   
            $mform->addElement('static', 'string_identifiers', get_string('string_identifiers', 'local_notifications'),  $strings);
            $mform->addHelpButton('string_identifiers', 'strings', 'local_notifications');
            $mform->hideIf('string_identifiers', 'notificationid', 'eq', NULL);
            //default issue https://tracker.moodle.org/browse/MDL-66251

            $mform->addElement('editor', 'body', get_string('emp_body', 'local_notifications'), array(), array('autosave'=>false));
            $mform->setType('body', PARAM_RAW);
        }// end of form status = 0 condition    
        else if($form_status ==1){
        	$notifytypesql = "SELECT lnt.shortname FROM {local_notification_type} AS lnt
        		JOIN {local_notification_info} AS lni ON lni.notificationid=lnt.id WHERE lni.id=:id";
        	$notif_type = $DB->get_field_sql($notifytypesql, array('id'=>$id));
			$strings = $lib->get_string_identifiers($notif_type);
        	$mform->addElement('static', 'string_identifiers', get_string('string_identifiers', 'local_notifications'),  $strings);
            $mform->addElement('editor', 'adminbody', get_string('admin_body', 'local_notifications'), array(), array('autosave'=>false));
            $mform->setType('adminbody', PARAM_RAW);
        }// end of form status = 1 condition
        
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->disable_form_change_checker();
    }
    public function get_datamoduleids_labels($moduletype, $costcenterid){
    	global $DB;
    	switch(strtolower($moduletype)){
			case 'course':	
				$sql = "SELECT c.id, c.fullname as name FROM {course} c                           
				WHERE  c.visible = 1 AND c.open_costcenterid = {$costcenterid} ";                    
				$datamoduleids = $DB->get_records_sql_menu($sql);
                $datamodule_label=get_string('form_courses', 'local_notifications');
				
			break;	
			case 'program':	
				$sql = "SELECT c.id, c.name FROM {local_program} c                           
				WHERE  c.visible = 1 AND c.costcenter = {$costcenterid} ";                 
				$datamoduleids = $DB->get_records_sql_menu($sql);
				$datamodule_label=get_string('form_programs', 'local_notifications');
				
			break;
			case 'admission':	
				$sql = "SELECT c.id, c.name FROM {local_program} c                           
				WHERE  c.visible = 1 AND c.costcenter = {$costcenterid} ";                 
				$datamoduleids = $DB->get_records_sql_menu($sql);
				$datamodule_label=get_string('form_admissions', 'local_notifications');
			break;
		}
		return array('datamoduleids' => $datamoduleids, 'datamodule_label' => $datamodule_label);
    }
    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);
		$mform = $this->_form;
        $moduleid = $this->_customdata['moduleid'];
		
        $notificationid = $data['notificationid'];
        $costcenterid = $data['costcenterid'];
        $id = $data['id'];
		if($notificationid&&$costcenterid){
			$sql="SELECT id FROM {local_notification_info} WHERE costcenterid = {$costcenterid} AND notificationid = {$notificationid}";
			if($id>0){
				$sql.=" AND id <> {$id} ";
			}
			if(is_array($moduleid)&&!empty($moduleid)){
				$concatqry = array();
				foreach($moduleid AS $module){
					if(empty($module)){
						continue;
					}
					$param = '%,'.$module.',%';
					$concatqry[] = " concat(',',moduleid,',') LIKE  '{$param}' " ;
				}
				if(!empty($concatqry)){
					$concatsql = implode(' OR ', $concatqry);
					$sql .= " AND ( {$concatsql} ) ";
				}
			}else{
				$sql.=" AND moduleid IS NULL";
			}
            $record = $DB->get_records_sql($sql);
	
            if (!empty($record)&&((count($record)>0&$id<=0)||(count($record)>0&$id>0))) {

                $notif_type = $DB->get_field('local_notification_type', 'shortname', array('id'=>$notificationid));
                $notif_type_find=explode('_',$notif_type);
                if($notif_type_find[1]!='reminder' && $notif_type != 'program_session_reminder'){
                   $errors['notificationid'] = get_string('codeexists', 'local_notifications',$notif_type);
                }else{
                	$sql .= " AND reminderdays={$data['reminderdays']} ";
                	$record = $DB->get_record_sql($sql);
                	if(!empty($record)){
                		$errors['notificationid'] = get_string('codeexists', 'local_notifications',$notif_type);
                	}
                }
            }
		}
		
        return $errors;
    }
    
}
