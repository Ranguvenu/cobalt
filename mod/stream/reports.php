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
 * @package    mod_stream
 */

require('../../config.php');
require_once("$CFG->dirroot/mod/stream/lib.php");
require_once("$CFG->dirroot/mod/stream/locallib.php");
require_login();
$PAGE->set_url('/mod/stream/reports.php');
$courseid       = optional_param('id', 0, PARAM_INT);
$activityid       = optional_param('cmid', 0, PARAM_INT);

$context = context_system::instance();

$PAGE->requires->js('/mod/stream/js/highcharts.js',true);
$PAGE->requires->js('/mod/stream/js/highcharts-more.js',true);
$PAGE->requires->js('/mod/stream/js/exporting.js',true);
$PAGE->requires->js('/mod/stream/js/export-data.js',true);

$PAGE->requires->js_call_amd('mod_stream/stream', 'init');
$PAGE->requires->js_call_amd('mod_stream/ratings', 'load');
$PAGE->requires->js_call_amd('mod_stream/stream', 'DrillDownReportPopup');
$PAGE->requires->js_call_amd('mod_stream/charts', 'ReportsCharts');

$PAGE->requires->css('/mod/stream/css/jquery.dataTables.min.css');
$PAGE->requires->css('/mod/stream/css/flatpickr.min.css');
$PAGE->requires->css('/mod/stream/css/radios-to-slider.min.css');

$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');

$PAGE->set_title(get_string('streamreports', 'stream'));
$PAGE->set_heading(get_string('reports', 'stream'));

$streamoutput = $PAGE->get_renderer('stream');

echo $OUTPUT->header();

echo $streamoutput->print_reports($courseid, $activityid);

echo $OUTPUT->footer();