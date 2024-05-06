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
 * @author eabyas  <info@eabyas.in>
 * @package stream
 * @subpackage block_stream
 */
function block_stream_output_fragment_upload_video($args){
    global $DB, $CFG, $_SESSION;
    $args = (object)$args;
    if(!(isset($_SESSION['video_organisation']) || isset($_SESSION['video_tags']))){
        $uploaddata = block_stream_get_api_formdata();
        extract((array)$uploaddata);
        $organisations = (array)$organisations;
        $_SESSION['video_organisation'] = $organisations;
        $_SESSION['video_tags'] = (array)$tags;
    }
    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        parse_str($serialiseddata, $formdata);
    }
    $editoroptions = [
        'maxfiles' => EDITOR_UNLIMITED_FILES,
        'maxbytes' => $course->maxbytes,
        'trust' => false,
        'context' => $context,
        'noclean' => true,
        'subdirs' => false
    ];
    $mform = new block_stream\form\upload(null, array('editoroptions' => $editoroptions, 'organisations' => $_SESSION['video_organisation'], 'tags' => $_SESSION['video_tags']), 'post', '', null, true, $formdata);
    if (!empty($args->jsonformdata) && strlen($args->jsonformdata) >2) {
        // If we were passed non-empty form data we want the mform to call validation functions and show errors.
        $mform->is_validated();
    }
    return $mform->render();
}
function block_stream_get_api_formdata(){
    $streamobj = new block_stream\stream();
    $uploaddata = $streamobj->streamlib->get_upload_data();
    return json_decode($uploaddata);
}
function block_stream_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    // Check the contextlevel is as expected - if your plugin is a block, this becomes CONTEXT_BLOCK, etc.

    // Make sure the filearea is one of those used by the plugin.
    if (!($filearea == 'video' || $filearea == 'thumbnail')) {
        return false;
    }

    $itemid = array_shift($args);

    $filename = array_pop($args);
    if (!$args) {
        $filepath = '/';
    } else {
        $filepath = '/'.implode('/', $args).'/';
    }

    // Retrieve the file from the Files API.
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'block_stream', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false;
    }
    send_file($file, $filename, null, 0, false, 0, $options);
}