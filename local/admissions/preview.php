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
 * Version details.
 *
 * @package    local_admissions
 * @copyright  moodle
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use local_admissions\local\lib;
require_once(__DIR__ . '/../../config.php');
global $CFG, $USER, $PAGE, $OUTPUT,$DB;
require_login();
$PAGE->set_url(new moodle_url('/local/admissions/preview.php'));
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('viewdocs', 'local_admissions'));
$PAGE->set_heading(get_string('viewdocs', 'local_admissions'));
$PAGE->navbar->add(get_string('home', 'local_admissions'), new moodle_url('/my'));
$PAGE->navbar->add(get_string('view', 'local_admissions'), new moodle_url('/local/admissions/view.php'));
$PAGE->navbar->add(get_string('viewdocs', 'local_admissions'));
$PAGE->set_pagelayout('standard');
$id = optional_param('id' , null , PARAM_RAW);

    $lib = new lib();
    $uploaddocs = $lib->downloadpdf_file($id);
    $educationdetails = $lib->educational_details($id);
    $templatecontext = (object)[
        'preview' => array_values($uploaddocs),
        'educationdetails' => array_values($educationdetails),
        'configwwwroot' => $CFG->wwwroot,
    ];

echo $OUTPUT->header();
    if(is_siteadmin()) {
        echo $OUTPUT->render_from_template('local_admissions/preview' , $templatecontext);
    } else {
        throw new moodle_exception(get_string('permissiondenied', 'local_admissions'));
    }
echo $OUTPUT->footer();
