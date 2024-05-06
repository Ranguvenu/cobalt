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

require_once("../../config.php");
require($CFG->dirroot.'/course/lib.php');
require($CFG->dirroot.'/local/groups/lib.php');
require($CFG->dirroot.'/local/groups/classes/form/edit_form.php');
global $DB, $OUTPUT, $USER, $CFG;
$id        = optional_param('id', 0, PARAM_INT);
$contextid = optional_param('contextid', 1, PARAM_INT);
$delete    = optional_param('delete', 0, PARAM_BOOL);
$show      = optional_param('show', 0, PARAM_BOOL);
$hide      = optional_param('hide', 0, PARAM_BOOL);
$confirm   = optional_param('confirm', 0, PARAM_BOOL);
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);
require_login();
$category = null;
if ($id) {
    $groups = $DB->get_record('cohort', array('id' => $id), '*', MUST_EXIST);
    $context = context::instance_by_id($groups->contextid, MUST_EXIST);

    $groupsdetails = $DB->get_record('local_groups', array('cohortid' => $id));

    // I.e other than admin eg:Org.Head.
    if (!(is_siteadmin()) && has_capability('local/costcenter:manage_ownorganization', context_system::instance()) && !has_capability('local/costcenter:manage_owndepartments', context_system::instance())) {
        if ($groupsdetails->costcenterid != $USER->open_costcenterid) {
            throw new moodle_exception('Sorry, You are not accessable to this page');
        }
    }
    // For Dept.Head.
    if (!(is_siteadmin()) && has_capability('local/costcenter:manage_owndepartments', context_system::instance()) && !has_capability('local/costcenter:manage_ownorganization', context_system::instance())) {
        if ($groupsdetails->departmentid != $USER->open_departmentid) {
            throw new moodle_exception("You donot have permissions");
        }
    }

} else {
    $context = context::instance_by_id($contextid, MUST_EXIST);
    if ($context->contextlevel != CONTEXT_COURSECAT && $context->contextlevel != CONTEXT_SYSTEM) {
        throw new moodle_exception('invalidcontext');
    }
    $groups = new stdClass();
    $groups->id          = 0;
    $groups->contextid   = $context->id;
    $groups->name        = '';
    $groups->description = '';
}
require_capability('moodle/cohort:manage', $context);

if ($delete && $groups->id) {
    $PAGE->url->param('delete', 1);
    if ($confirm && confirm_sesskey()) {
        local_groups_delete_groups($groups);
        redirect($returnurl);
    }
    $strheading = get_string('delgroups', 'local_groups');
    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);
    $PAGE->set_heading($COURSE->fullname);
    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);
    $yesurl = new moodle_url('/local/groups/edit.php', array('id' => $groups->id, 'delete' => 1,
        'confirm' => 1, 'sesskey' => sesskey(), 'returnurl' => $returnurl->out_as_local_url()));
    $message = get_string('delconfirm', 'local_groups', format_string($groups->name));
    echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    echo $OUTPUT->footer();
    die;
}
if ($show && $groups->id && confirm_sesskey()) {
    if (!$groups->visible) {
        $record = (object)array('id' => $groups->id, 'visible' => 1, 'contextid' => $groups->contextid);
        local_groups_update_groups($record);
    }
    redirect($returnurl);
}
if ($hide && $groups->id && confirm_sesskey()) {
    if ($groups->visible) {
        $record = (object)array('id' => $groups->id, 'visible' => 0, 'contextid' => $groups->contextid);
        local_groups_update_groups($record);
    }
    redirect($returnurl);
}
echo $OUTPUT->header();
echo $OUTPUT->heading($strheading);

if (!$id && ($editcontrols = local_groups_edit_controls($context, $baseurl))) {
    echo $OUTPUT->render($editcontrols);
}
echo $editform->display();
echo $OUTPUT->footer();

