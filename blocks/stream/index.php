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
 * @package stream
 * @subpackage block_stream
 */
require('../../config.php');
global $OUTPUT,$CFG,$PAGE;
require_login();
$systemcontext = context_system::instance();
if(!is_siteadmin()){
	require_capability('block/stream:viewvideos', $systemcontext);
}
echo $systemcontext->id;
$PAGE->requires->js_call_amd('block_stream/streamcontent', 'init', ['[data-region="stream-list-container"]', 5]);
$PAGE->requires->js_call_amd('block_stream/streamcontent', 'registerSelector');
$PAGE->requires->js_call_amd('block_stream/upload', 'init');
$PAGE->requires->js_call_amd('block_stream/renderstream', 'init');

$pageurl = new moodle_url('/blocks/stream/index.php');
$PAGE->set_url($pageurl);
$PAGE->set_pagelayout('standard');
$PAGE->set_context($systemcontext);
$heading = get_string('uploadedvideos', 'block_stream');
$PAGE->set_title($heading);

$PAGE->set_heading($heading);
$PAGE->navbar->add($heading);

$uploadedvideos = new \block_stream\output\uploadedvideos($systemcontext);
$streamoutput = $PAGE->get_renderer('block_stream');

echo $OUTPUT->header();

echo $streamoutput->render($uploadedvideos);

echo $OUTPUT->footer();