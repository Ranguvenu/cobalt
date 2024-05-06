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
require_once($CFG->dirroot.'/repository/stream/streamlib.php');

$id = optional_param('id', 0, PARAM_INT);        
$u  = optional_param('u', 0, PARAM_INT);
global $DB;
if ($u) {  
    $stream = $DB->get_record('stream', array('id'=>$u),'*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('stream', $stream->id, $stream->course, false, MUST_EXIST);

} else {
    $cm = get_coursemodule_from_id('stream', $id, 0, false, MUST_EXIST);
    $stream = $DB->get_record('stream', array('id'=>$cm->instance), '*', MUST_EXIST);
}

$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
$context = context_module::instance($cm->id);

require_course_login($course, true, $cm);
require_capability('mod/stream:view', $context);
stream_view($stream, $course, $cm, $context);
$PAGE->set_url('/mod/stream/view.php', array('id' => $cm->id));

$PAGE->set_pagelayout('incourse');
$PAGE->set_title($course->shortname.': '.$stream->name);
$PAGE->set_heading($stream->name);
$PAGE->requires->jquery();
$PAGE->requires->js('/mod/stream/js/highcharts.js',true);
$PAGE->requires->js('/mod/stream/js/heatmap.js',true);
$PAGE->requires->js('/mod/stream/js/data.js',true);
$PAGE->requires->js_call_amd('mod_stream/ratings', 'trigger');
$params = json_encode(['identifier' => 'my_video_1', 'src' => $stream->externalurl, 'cm' => $cm->id, 'course' => $cm->course]);
$PAGE->requires->js_call_amd('mod_stream/player', 'load', array($params));

echo $OUTPUT->header();

    $params = array(
        'context' => $context,
        'objectid' => $cm->id
    );
    $event = \mod_stream\event\stream_instance_viewed::create($params);
    $event->trigger();

$exturl = trim($stream->externalurl);
if (empty($exturl) or $exturl === 'http://') {
    notice(get_string('invalidstoredurl', 'stream'), new moodle_url('/course/view.php', array('id'=>$cm->course)));
    die;
}
unset($exturl);

stream_view($stream, $course, $cm, $context);

$player = new mod_stream\output\player($stream, $cm);
echo $OUTPUT->render($player);

echo $OUTPUT->footer();
