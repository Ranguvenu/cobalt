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

defined('MOODLE_INTERNAL') || die;
require_once("$CFG->libdir/externallib.php");
use \local_program\program as program;
use context_system;
use stdClass;
use moodle_url;
use completion_completion;
use html_table;
use html_writer;
use core_component;
use Exception;

class local_admissions_external extends external_api {

	    /**
     * Describes the parameters for accept/approve user webservice.
     * @return external_function_parameters
     */
    public static function accept_admission_parameters() {
        return new external_function_parameters(
            array(
                'action' => new external_value(PARAM_ACTION, 'Action of the event', false),
                'id' => new external_value(PARAM_INT, 'ID of the record', 0),
                 'programid' => new external_value(PARAM_INT, 'ID of the record', 0),
                 'context' => new external_value(PARAM_INT, 'ID of the record', 0),
                'confirm' => new external_value(PARAM_BOOL, 'Confirm', false),
                'programname' => new external_value(PARAM_RAW, 'Action of the event', false),
            )
        );
    }

       /**
     * accept user.
     *
     * @param PARAM_INT $action, $admissionid, $contextid, $jsonformdata and $programid id of the table local_users     
     * @return PARAM_BOOL if accepted successfully.
     */

    public static function accept_admission($action, $id, $programid, $context, $confirm, $programname) {
        global $CFG, $DB, $USER, $PAGE;
        $accept_user = new \local_admissions\local\lib();
        $accept_user->accept_student_admission($id, $programid, $context);

        return true;
       }
    
      /**
     * Returns description of method result value.
     *
     * @return external_description
     */
    public static function accept_admission_returns() {
        return new external_value(PARAM_INT, "Accept user admission");
    }

    public static function statusconfirmform_parameters() {
        return new external_function_parameters(
            array(
                'contextid' => new external_value(PARAM_INT, 'The context id for the evaluation', false),
                'admissionid' => new external_value(PARAM_INT, 'admissionid', 0),
                'programid' => new external_value(PARAM_INT, 'programid', 0),
                'jsonformdata' => new external_value(PARAM_RAW, 'The data from the create group form, encoded as a json array', false)
            )
        );
    }

     /**
     * revise user.
     *
     * @param PARAM_INT $contextid, $admissionid, $programid, $jsonformdata     
     * @return PARAM_BOOL if revised successfully.
     */
    public function statusconfirmform($contextid, $admissionid, $programid, $jsonformdata){
        global $PAGE, $CFG, $USER, $DB;
        
        require_once($CFG->dirroot . '/local/admissions/lib.php');
        $context = context::instance_by_id($contextid, MUST_EXIST);
        $systemcontext = context_system::instance();
        self::validate_context($context);
        $serialiseddata = json_decode($jsonformdata);
        $data = array();
        parse_str($serialiseddata, $data);
        $warnings = array();
        $mform = new local_admissions\form\statusconfirmform(null, array('admissionid' => $admissionid, 'programid' => $programid), 'post', '', null, true, $data);
        $validateddata = $mform->get_data();

        $emaillogs = new \local_admissions\notification();
        $allow = true;
        $type = 'admission_revise';
        $dataobj = $programid;
        $fromuserid = $USER->id;
        $pid = array('programid'=>$programid);

        $sql = "SELECT lu.id as admissionid, lu.registrationid, lu.email, lu.firstname, lu.lastname, lu.reason, p.*  
        FROM {local_users} lu 
        JOIN {local_program} p ON p.id = lu.programid 
        WHERE lu.id = :admissionid AND lu.programid = :programid";

        $local_program = $DB->get_record_sql($sql, array('admissionid'=>$admissionid, 'programid'=>$programid));

        $admission = $DB->get_field('local_users', 'id', array('id'=>$admissionid));
        $cntrecord = $DB->get_record('local_users', array('id'=>$admissionid));

        $revcnt = $DB->get_field('local_users', 'revisecnt', array('id' => $admissionid));
        if ($validateddata) {
            $validateddata->id = $admissionid;
            $validateddata->userid = 0;
            $validateddata->programid = $validateddata->programid;
            $validateddata->reason = $validateddata->reason;
            $validateddata->revisecnt = $revcnt + 1;
            $validateddata->status = 3;
            $validateddata->timemodified = time();
            $validateddata->usermodified = $USER->id;
            $DB->update_record('local_users', $validateddata);

            $reject_record = $DB->get_record('local_users', array('id'=>$validateddata->admissionid));
            $reject_records = new \stdClass();
            $reject_records->id = $reject_record->id;
            $reject_records->userid = $reject_record->userid;
            $reject_records->email = $reject_record->email;
            $reject_records->programid = $programid;

            $reject_records->firstname = $reject_record->firstname;
            $reject_records->lastname = $reject_record->lastname;

            $reject_records->registrationid = $reject_record->registrationid;
            $reject_records->reason = $validateddata->reason;
            $reject_records->programname = $local_program->name;

            // Local Admission Event for Application Requested for Revise.

                $params = array(
                    'context' => context_system::instance(),
                    'objectid' => $reject_records->id,
                    'userid' => $USER->id,
                    'other' => array('programid' => $reject_records->programid, 'email'=>$reject_records->email)
                );

                $event = \local_admissions\event\status_revise::create($params);
                $event->add_record_snapshot('local_users', $reject_records);
                $event->trigger();

                // Sending an email to the user to check the application status.

                $datamailobj->body =  "Hi <b>$reject_records->firstname  $reject_records->lastname</b>
                <br>Your application with Registration No: <b> $reject_records->registrationid </b> to the program <b> $reject_records->programname </b> is requested for revision.<br> 
                Please access your submitted application through below link to review your information and re-submit.<br>
                Reason: $reject_records->reason.<br>
                <a>$CFG->wwwroot/local/admissions/status.php</a><br>

                Thanks<br>
                Admissions Team.
                ";

                email_to_user($reject_record, $USER, 'Application requested for revise', $datamailobj->body);

                if ($allow) {
                    $touser = $local_program;
                    $email_logs = $emaillogs->admission_notification($type, $touser, $fromuser = get_admin(), $local_program);
                }

            
        } else {
            // Generate a warning.
            throw new moodle_exception(get_string('error_in_creation','local_admissions'));
        }
        $return = array(
            'admissionid' => $admissionid,
            'programid' => $programid);
        return $return;
    }
    /**
     * Returns description of method result value.
     *
     * @return external_description
     */
    public static function statusconfirmform_returns() {
        return new external_single_structure(
            array(
            'admissionid' => new external_value(PARAM_RAW, 'admissionid'),
            'programid' => new external_value(PARAM_RAW, 'programid'),
            )
        );
    }

    # reject admission status form.

    /**
     * Describes the parameters for reject user webservice.
     * @return external_function_parameters
     */
    public static function rejectconfirmform_parameters() {
        return new external_function_parameters(
            array(
                'admissionid' => new external_value(PARAM_INT, 'admissionid', 0),
                'contextid' => new external_value(PARAM_INT, 'The context id for the evaluation', false),
                'jsonformdata' => new external_value(PARAM_RAW, 'The data from the create group form, encoded as a json array', false),
                'programid' => new external_value(PARAM_INT, 'programid', 0),
                'clickcount' => new external_value(PARAM_INT, 'clickcount', 0),
            )
        );
    }

      /**
     * Reject user.
     *
     * @param PARAM_INT $admissionid, $contextid, $jsonformdata and $programid id of the table local_users     
     * @return PARAM_BOOL if rejected successfully.
     */
    public function rejectconfirmform($admissionid, $contextid, $jsonformdata, $programid, $clickcount){
        global $PAGE, $CFG, $USER, $DB;
        require_once($CFG->dirroot . '/local/admissions/lib.php');
        
        $context = context::instance_by_id($contextid, MUST_EXIST);
        $systemcontext = context_system::instance();
        self::validate_context($context);
        $serialiseddata = json_decode($jsonformdata);
        $data = array();
        parse_str($serialiseddata, $data);
        $warnings = array();
        $mform = new local_admissions\form\rejectconfirmform(null, array('admissionid' => $admissionid, 'programid' => $programid), 'post', '', null, true, $data);
        $validateddata = $mform->get_data();
        $emaillogs = new \local_admissions\notification();
        $allow = true;
        $type = 'admission_reject';
            $dataobj = $programid;
            $fromuserid = $USER->id;
            $pid = array('programid'=>$programid);
            $sql = "SELECT lu.id as admissionid, lu.registrationid, lu.email, lu.firstname, lu.lastname, lu.reason, p.* 
                    FROM {local_users} lu 
                    JOIN {local_program} p ON p.id = lu.programid 
                    WHERE lu.id = :admissionid AND lu.programid = :programid";

            $local_program = $DB->get_record_sql($sql, array('admissionid'=>$admissionid, 'programid'=>$programid));

            $admission = $DB->get_field('local_users', 'id', array('id'=>$admissionid));
                if ($validateddata) {
                    $validateddata->id = $admissionid;
                    $validateddata->userid = 0;
                    $validateddata->programid = $validateddata->programid;
                    $validateddata->reason = $validateddata->reason;
                    $validateddata->status = 2;
                    $validateddata->timemodified = time();
                    $validateddata->usermodified = $USER->id;
                    $DB->update_record('local_users', $validateddata);

                    $validateddata->id = $DB->update_record('local_admissions', $validateddata);
                    $reject_record = $DB->get_record('local_users', array('id'=>$validateddata->admissionid));
                    $reject_records = new \stdClass();
                    $reject_records->id = $reject_record->id;
                    $reject_records->userid = $reject_record->userid;
                    $reject_records->email = $reject_record->email;
                    $reject_records->programid = $programid;

                    $reject_records->firstname = $reject_record->firstname;
                    $reject_records->lastname = $reject_record->lastname;

                    $reject_records->registrationid = $reject_record->registrationid;
                    $reject_records->reason = $validateddata->reason;
                    $reject_records->programname = $local_program->name;

                    // Local Admission Event for Application Reject.

                    $params = array(
                            'context' => context_system::instance(),
                            'objectid' => $reject_records->id,
                            'userid' => $USER->id,
                            'other' => array('programid' => $reject_records->programid, 'email'=>$reject_records->email)
                        );

                        $event = \local_admissions\event\status_reject::create($params);
                        $event->add_record_snapshot('local_users', $reject_records);
                        $event->trigger();

                        // Sending an email to the user to check the application status.

                        $datamailobj->body =  "Hi <b>$reject_records->firstname  $reject_records->lastname</b>, 
                        <br> We are sorry to inform you that your application with Registration No: <b> $reject_records->registrationid </b> to the program <b> $reject_records->program_name </b> is rejected.<br>
                        As you have not meet the program Pre-requisites.<br>
                        Reason: $reject_records->reason.<br>

                        Thanks<br>
                        Admissions Team.
                        ";
                            email_to_user($reject_record, $USER, 'Application Rejected', $datamailobj->body);
                            if ($allow) {
                                $touser = $local_program;
                                $email_logs = $emaillogs->admission_notification($type, $touser, $fromuser = get_admin(), $local_program);
                            }
                } else {
                // Generate a warning.
                throw new moodle_exception(get_string('error_in_creation','local_admissions'));
            }

        $return = array(
            'admissionid' => $admissionid,
            'programid' => $programid,
            'clickcount' => $clickcount);

        return $return;
    }
      /**
     * Returns description of method result value.
     *
     * @return external_description
     */
    public static function rejectconfirmform_returns() {
        return new external_single_structure(
            array(
            'admissionid' => new external_value(PARAM_RAW, 'admissionid'),
            'programid' => new external_value(PARAM_RAW, 'programid'), 
            'clickcount' => new external_value(PARAM_RAW, 'clickcount')
            )
        );
    }
}
