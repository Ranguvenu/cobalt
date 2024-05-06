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
 * Version information
 *
 * @package    local_groups
 * @copyright  2022 eAbyas Info Solutions Pvt. Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->libdir.'/csvlib.class.php');
require_once('lib.php');
require_once($CFG->dirroot.'/local/lib.php');
$context = context_system::instance();
$PAGE->set_context($context);

$id = required_param('id', PARAM_INT);
$groups = $DB->get_record('cohort', array('id' => $id), '*', MUST_EXIST);
if (empty($groups)) {
    throw new moodle_exception(get_string('groupsnotfound', 'local_groups'));
}
require_login();
$context = context_system::instance();
require_capability('moodle/cohort:assign', $context);

$groupsdetails = $DB->get_record('local_groups', array('cohortid' => $id));
// I.e other than admin eg:Org.Head.
if (!(is_siteadmin()) && has_capability('local/costcenter:manage_ownorganization',
        context_system::instance()) && !has_capability('local/costcenter:manage_owndepartments', context_system::instance())) {
    if ($groupsdetails->costcenterid != $USER->open_costcenterid) {
        throw new moodle_exception(get_string('pagecantaccess', 'local_groups'));
    }
}
// For Dept.Head.
if (!(is_siteadmin()) && has_capability('local/costcenter:manage_owndepartments', context_system::instance()) && !has_capability('local/costcenter:manage_ownorganization',
        context_system::instance())) {
    if ($groupsdetails->departmentid != $USER->open_departmentid) {
        throw new moodle_exception(get_string('donthavepermissions', 'local_groups'));
    }
}

// Start making page.
$PAGE->set_pagelayout('standard');
$PAGE->set_url('/local/groups/mass_enroll.php', array('id' => $id));

/*$strinscriptions = get_string('mass_enroll', 'local_courses');

$PAGE->set_title($groups->name . ': ' . $strinscriptions);*/
$PAGE->set_heading($groups->name . ': '.get_string('assignments', 'local_groups'));
$PAGE->navbar->add(get_string('cohorts', 'local_groups'),
                    new moodle_url('/local/groups/index.php')
                );
$PAGE->navbar->add(get_string('assignments', 'local_groups'));


echo $OUTPUT->header();

$mform = new local_courses\form\mass_enroll_form($CFG->wwwroot . '/local/groups/mass_enroll.php', array(
    'course' => $groups,
    'context' => $context,
    'type' => 'groups'
));

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/groups/index.php'));
} else if ($data = $mform->get_data(false)) {
    echo $OUTPUT->heading($strinscriptions);

    $iid = csv_import_reader::get_new_iid('uploaduser');
    $cir = new csv_import_reader($iid, 'uploaduser');

    $content = $mform->get_file_content('attachment');

    $readcount = $cir->load_csv_content($content, $data->encoding, $data->delimiter_name);
    unset($content);

    if ($readcount === false) {
        throw new moodle_exception('csvloaderror', '', $returnurl);
    } else if ($readcount == 1) {
        // throw new moodle_exception('csvemptyfile', 'error', $returnurl);
        echo '<div class="local_groups_sync_error">' 
                .get_string('csvemptyfile', 'local_groups').
            '</div>';
    }
    $result = groups_mass_enroll($cir, $groups, $context, $data);
    $cir->close();
    $cir->cleanup(false); // Only currently uploaded CSV file.
    // The code has been disbaled to stop sending auto maila and make loading issues.
    echo $OUTPUT->box(nl2br($result), 'center');
    // Back to course page.
    echo $OUTPUT->continue_button(new moodle_url('/local/groups/index.php'));
    echo $OUTPUT->footer($groups);
    die();
}

echo $OUTPUT->box (get_string('mass_enroll_info', 'local_courses'), 'center');
echo html_writer::link(
                        new moodle_url('/local/groups/index.php'),
                        get_string('back', 'local_courses'), array('id' => 'back_tp_course','class' => 'btn btn-primary ')
                    );
$sample = html_writer::link(
                            new moodle_url('/local/courses/sample.php', array('format' => 'csv')
                                        ), get_string('sample', 'local_courses'),
                            array('id' => 'download_users','class' => 'btn btn-primary')
                        );
echo $sample;
$mform->display();
echo $OUTPUT->footer($groups);
