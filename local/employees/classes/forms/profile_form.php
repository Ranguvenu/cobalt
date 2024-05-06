<?php
namespace local_employees\forms;

defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->dirroot . '/local/costcenter/lib.php');
use moodleform;
use context_system;
use costcenter;
use events;
use context_user;
use local_employees\functions\employeeslibfunctions as userlib;

class profile_form extends moodleform {
	public $formstatus;
	public function __construct($action = null, $customdata = null, $method = 'post', $target = '', $attributes = null, $editable = true, $formdata = null) {

	 	$this->formstatus = array(
	 		'personalinfo' => get_string('personalinfo', 'local_employees'),
			'addressinfo' => get_string('addressinfo', 'local_employees'),
			'academicinfo' => get_string('academicinfo', 'local_employees'),
			);
	 	parent::__construct($action, $customdata, $method, $target, $attributes, $editable, $formdata);
	}
    public function definition() {
        global $USER, $CFG, $DB, $PAGE;
		$systemcontext = context_system::instance();
        $costcenter = new costcenter();
        $mform = $this->_form;

        $form_status = $this->_customdata['form_status'];
        $id = $this->_customdata['id'];
        $editoroptions = $this->_customdata['editoroptions'];
        if($form_status == 0){

	        if (is_siteadmin($USER->id) || has_capability('local/users:manage',$systemcontext)) {
				$sql="select id,fullname from {local_costcenter} where visible = :visible and parentid=:parentid ";
	            $costcenters = $DB->get_records_sql($sql,array('visible' => 1,'parentid' => 0));
	        }

			if (is_siteadmin($USER) || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)) {
				$organizationlist=array(null=>get_string('select_org', 'local_employees'));
				foreach ($costcenters as $scl) {
					$organizationlist[$scl->id]=$scl->fullname;
				}
				$mform->addElement('select', 'open_costcenterid', get_string('organization', 'local_employees'), $organizationlist);
				$mform->addRule('open_costcenterid', get_string('errororganization', 'local_employees'), 'required', null, 'client');
			} else if(has_capability('local/costcenter:manage_ownorganization', $systemcontext)|| has_capability('local/costcenter:manage_owndepartments', $systemcontext)){
				$user_dept=$DB->get_field('user','open_costcenterid', array('id'=>$USER->id));
				$mform->addElement('hidden', 'open_costcenterid', null);
				$mform->setType('open_costcenterid', PARAM_ALPHANUM);
				$mform->setConstant('open_costcenterid', $user_dept);
			}
	        $count = count($costcenters);
	        $mform->addElement('hidden', 'count', $count);
	        $mform->setType('count', PARAM_INT);

			$mform->addElement('text', 'firstname', get_string('firstname', 'local_employees'));
	        $mform->addRule('firstname', get_string('errorfirstname', 'local_employees'), 'required', null, 'client');
	        $mform->setType('firstname', PARAM_RAW);

	        $mform->addElement('text', 'lastname', get_string('lastname', 'local_employees'));
	        $mform->addRule('lastname', get_string('errorlastname', 'local_employees'), 'required', null, 'client');
	        $mform->setType('lastname', PARAM_RAW);

	        $mform->addElement('text', 'email', get_string('email', 'local_employees'));
	        $mform->addRule('email', get_string('erroremail','local_employees'), 'required', null, 'client');

	        $mform->addRule('email', get_string('emailerror', 'local_employees'), 'email', null, 'client');
	        $mform->setType('email', PARAM_RAW);

	        $mform->addElement('filepicker', 'imagefile', get_string('newpicture'), null, array('accepted_types' => array('.jpg', '.jpeg', '.png')));
	        $mform->addHelpButton('imagefile', 'newpicture');
		}//end of if($form_status = 0) condition.
		else if($form_status ==1){


        $mform->addElement('editor', 'description', get_string('aboutmyself', 'local_employees'), null, $editoroptions);
        $mform->setType('description', PARAM_RAW);
        $mform->addHelpButton('description', 'description', 'local_employees');

		    $mform->addElement('text', 'city', get_string('city','local_employees'));
	        $mform->setType('city', PARAM_RAW);

	        if (isset($CFG->forcetimezone) and $CFG->forcetimezone != 99) {
		        $choices = \core_date::get_list_of_timezones($CFG->forcetimezone);
		        $mform->addElement('static', 'forcedtimezone', get_string('timezone'), $choices[$CFG->forcetimezone]);
		        $mform->addElement('hidden', 'timezone');
		        $mform->setType('timezone', \core_user::get_property_type('timezone'));
		    } else {
		    	$userrecord = \core_user::get_user($id);
		        $choices = \core_date::get_list_of_timezones($userrecord->timezone, true);
		        $mform->addElement('select', 'timezone', get_string('timezone'), $choices);
		    }
		}//end of if($form_status = 1) condition.
		else if ($form_status == 2){
			$preflist = array(null=>get_string('select_not_type','local_employees'), 1 =>'I want completed and Enroll Notification',2 =>'Need Consolidated Email');
			$mform->addElement('select', 'open_notify_pref', get_string('notificationpreferences', 'local_employees'),$preflist);
	    	$mform->setType('open_notify_pref', PARAM_INT);
			 $sql="select id,name from {local_topicinterest} where visible = :visible";
	        $topicslist = $DB->get_records_sql_menu($sql,array('visible' => 0));
			$selecttopicinterest=$mform->addElement('autocomplete', 'open_topicinterest', get_string('topicsinterested', 'local_employees'),$topicslist);
	    	$mform->setType('open_topicinterest', PARAM_RAW);
			$mform->addRule('open_topicinterest', get_string('topicsinterestederr', 'local_employees'), 'required', null, 'client');
			$selecttopicinterest->setMultiple(true);

			$sql="select id,name from {local_hrmsroles} where visible = :visible";
	        $roleslist = $DB->get_records_sql_menu($sql,array('visible' => 0));

			$mform->addElement('select', 'open_hrmsrole', get_string('open_role', 'local_employees'),array(null=>get_string('select_role','local_employees'))+$roleslist);
	    	$mform->setType('open_hrmsrole', PARAM_INT);
			$mform->addRule('open_hrmsrole', get_string('myroleerr', 'local_employees'), 'required', null, 'client');
		}
		// end of form status = 2 condition
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id',  $id);
        $mform->addElement('hidden', 'form_status');
        $mform->setType('form_status', PARAM_INT);
        $mform->setDefault('form_status',  $form_status);
        $mform->disable_form_change_checker();
    }

    public function definition_after_data() {
        global $USER, $CFG, $DB, $OUTPUT;
        $mform = & $this->_form;
        $form_status = $this->_customdata['form_status'];
        if ($userid = $mform->getElementValue('id')) {
            $user = $DB->get_record('user', array('id' => $userid));
        } else {
            $user = false;
        }
        // print picture
        if (empty($USER->newadminuser)) {
            if ($user) {
                $context = context_user::instance($user->id, MUST_EXIST);
                $fs = get_file_storage();
                $hasuploadedpicture = ($fs->file_exists($context->id, 'user', 'icon', 0, '/', 'f2.png') || $fs->file_exists($context->id, 'user', 'icon', 0, '/', 'f2.jpg'));
                if (!empty($user->picture) && $hasuploadedpicture) {
                    $imagevalue = $OUTPUT->user_picture($user, array('courseid' => SITEID, 'size' => 64,'link' => false));
                } else {
                    $imagevalue = get_string('none');
                }
            } else {
                $imagevalue = get_string('none');
            }
            if ($user && $mform->elementExists('deletepicture') && !$hasuploadedpicture) {
                $mform->removeElement('deletepicture');
            }
        }
    }

   public function validation($data, $files) {
        $errors = array();
        global $DB, $CFG;
		$sub_data=data_submitted();
		$errors = parent::validation($data, $files);
        $email = $data['email'];
        $id = $data['id'];
        $form_status = $data['form_status'];
        if($form_status == 0){// as these fields are in only form part 1(form_status=0)
        	$firstname = $data['firstname'];
        	$lastname = $data['lastname'];
        	if(empty(trim($firstname))){
        		$errors['firstname'] = get_string('valfirstnamerequired','local_employees');
        	}
        	if(empty(trim($lastname))){
        		$errors['lastname'] = get_string('vallastnamerequired','local_employees');
        	}
	        if(get_config('core', 'allowaccountssameemail') == 0){
			    if (!empty($data['email']) && ($user = $DB->get_record('user', array('email' => $data['email']), '*', IGNORE_MULTIPLE))) {
		            if (empty($data['id']) || $user->id != $data['id']) {
		                $errors['email'] = get_string('emailexists', 'local_employees');
		            }
		        }
	    	}
	    	if (!empty($data['email']) && !validate_email($data['email'])) {
	    		$errors['email'] = get_string('emailerror', 'local_employees');
	    	}
	    }
	    if($form_status == 2){
        	if(empty(trim($data['open_hrmsrole']))){
        		$errors['open_hrmsrole'] = get_string('myroleerr','local_employees');
        	}
        	if(empty($data['open_topicinterest'])){
        		$errors['open_topicinterest'] = get_string('topicsinterestederr','local_employees');
        	}
	    }
        return $errors;
    }
}
