<?php
/**
* @package  block_teachers_tests_summary
* @copyright Kristian
* @license  http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
require_once(__DIR__ . '/../../config.php');
require_login();
$PAGE->set_url(new moodle_url('/blocks/teachers_tests_summary/download.php'));
$PAGE->set_context(\context_system::instance());
define('AJAX_SCRIPT', true);
global $CFG, $PAGE, $OUTPUT, $DB;
$id = required_param('id', PARAM_INT);
$params = array();
$params['quizid'] = $id;
$coursessql = "SELECT q.id, q.course
                FROM {quiz} q
               WHERE q.id = :quizid ";
$courseids = $DB->get_record_sql($coursessql, $params);

$params['courseid'] = $courseids->course;

$stdrecordsql = "SELECT u.id
                   FROM {user} u
                   JOIN {role_assignments} ra ON ra.userid = u.id
                   JOIN {role} r ON r.id = ra.roleid AND r.shortname = 'student'
                   JOIN {context} ctx ON ctx.id = ra.contextid
                   JOIN {course} c ON c.id = ctx.instanceid
                  WHERE c.id = :courseid
                  GROUP BY u.id";
$enrolledstudents = $DB->get_records_sql($stdrecordsql, $params);
$enrolledstudentids = implode(', ', array_keys($enrolledstudents));
$students = $DB->get_records_sql("SELECT qa.userid, qa.quiz
                                   FROM {quiz_attempts} qa
                                   JOIN {quiz} q ON qa.quiz = q.id
                                  WHERE q.id = $id AND qa.userid IN ($enrolledstudentids)");
$enrolledkey = array_flip(array_keys($enrolledstudents));
$completedkey = array_flip(array_keys($students));
$notyetcompletedusers = array_diff_key($enrolledkey, $completedkey);

foreach ($notyetcompletedusers as $keys => $values) {

    $incompletedquizdetails = new stdClass();
    $param['userid'] = $keys;
    $studentname = $DB->get_records_select('user', 'id = ?', $param);

    $firstname = $studentname[$keys]->firstname;
    $lastname = $studentname[$keys]->lastname;
    $studentfullname = ''.$firstname.' '.$lastname.'';
    $email = $studentname[$keys]->email;

    $incompletedquizdetails->username = $studentfullname;
    $incompletedquizdetails->email = $email;
    $inprogressquizarrays[] = $incompletedquizdetails;
}

$table = new html_table(); // Already declared in moodle
$table->id = "block_teachers_tests_summary_head";

$table->head[] = 'Name';
$table->head[] = 'Email';
$data = array();

foreach ($inprogressquizarrays as $value) {
    $line = array();
    $line[] = ucfirst($value->username);
    $line[] = $value->email;
    $data[] = $line;
}

$table->id = "notattemptedquiz_student_data";
$table->data = $data;

require_once($CFG->libdir.'/csvlib.class.php');

$matrix = array();
$filename = 'Un-attempted_students';

if (!empty($table->head)) {
    $countcols = count($table->head);
    $keys = array_keys($table->head);
    $lastkey = end($keys);
    foreach ($table->head as $key => $heading) {
        // htmlspecialchars_decode() converts some predefined HTML entities to characters
        // strip_tags() strips a string from HTML, XML, and PHP tags
        // nl2br() function inserts HTML line breaks (<br> or <br />) in front of each newline (\n) in a string
        $matrix[0][$key] = str_replace("\n", ' ', htmlspecialchars_decode(strip_tags(nl2br($heading))));
    }
}

if (!empty($table->data)) {
    foreach ($table->data as $rkey => $row) {
        foreach ($row as $key => $item) {
            $matrix[$rkey + 1][$key] = str_replace("\n", ' ', htmlspecialchars_decode(strip_tags(nl2br($item))));
        }
    }
}

$csvexport = new csv_export_writer();
$csvexport->set_filename($filename);

foreach ($matrix as $ri => $col) {
    $csvexport->add_data($col);
}

$csvexport->download_file();
exit;
