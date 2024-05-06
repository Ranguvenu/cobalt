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
defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");
class block_stream_external extends external_api {
	public static function tablecontent_parameters(){
    	return new external_function_parameters(
            array(
                'args' => new external_value(PARAM_RAW, 'The data from datatables encoded as a json array', false),
            )
        );
    }
    public static function tablecontent($args){
    	global $PAGE;
    	require_login();
    	$PAGE->set_context(\context_system::instance());
    	$renderer = $PAGE->get_renderer('block_stream');
    	$params = json_decode($args);

    	$stream = new \block_stream\stream();

    	if($params->args->action == "updatePreferences"){
            $countonly = true;
        }else{
            $countonly = false;
        }
		$streamdata = $stream->uploadedVideoData($search, $params->args, $countonly);

        if($streamdata['length'] <=0 ){
            print_error('nodata', 'block_stream');
        }

        return $streamdata;
         
    }
    public static function tablecontent_returns(){

        return new external_single_structure(
            array(
                'data' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                             'id' => new external_value(PARAM_INT, 'Video id'), 
                             'title' => new external_value(PARAM_RAW, 'Video title'), 
                             'thumbnail' => new external_value(PARAM_RAW, 'Thumbnail'),
                             'timecreated' => new external_value(PARAM_RAW, 'Created date/time'),
                             'usercreated' => new external_value(PARAM_RAW, 'Created user'),
                             'path' => new external_value(PARAM_RAW, 'Video path'),
                             'videoid' => new external_value(PARAM_RAW, 'Video unique id'),
                             'status' => new external_value(PARAM_BOOL, 'Video publish status')
                            )
                    ), 'Data'
                ),
                'length' => new external_value(PARAM_RAW, 'Number of videos'),
            )
        );
    }
    public static function upload_video_parameters(){
    	return new external_function_parameters(
            array(
                'contextid' => new external_value(PARAM_INT, 'The context id for the video', false),
                'jsonformdata' => new external_value(PARAM_RAW, 'The data from the create group form, encoded as a json array', false),
            )
        );
    }
    public static function upload_video($contextid, $jsonformdata){
    	global $DB, $USER;
    	$context = context::instance_by_id($contextid, MUST_EXIST);
        // We always must call validate_context in a webservice.
		self::validate_context($context);
		$serialiseddata = json_decode($jsonformdata);

		$data = array();
        parse_str($serialiseddata, $data);
        $organisations = (array)json_decode($data['organisationoptions']);
        $tags = (array)json_decode($data['tagsoptions']);
        $mform = new block_stream\form\upload(null, array('editoroptions' => $editoroptions, 'organisations' => (array)$organisations, 'tags' => (array)$tags), 'post', '', null, true, $data);
        $validateddata = $mform->get_data();
        if(!empty($validateddata)){
        	$stream = new block_stream\stream();
            $id = $stream->insert_stream_content($validateddata, $tags, $context);
            $mform->save_stored_file('filepath', $context->id, 'block_stream', 'video',  $validateddata->filepath);
            $params = array(
                'context' => $context,
                'objectid' => $id
            );
            $event = \block_stream\event\video_uploaded::create($params);
            $event->trigger();
        }else{
        	throw new moodle_exception('Error in upload');
        	return false;
        }
        // return true;
    }
    public static function upload_video_returns(){
    	return new external_value(PARAM_BOOL, 'data');
    }
    public static function delete_video_parameters(){
        return new external_function_parameters(
            array(
                'contextid' => new external_value(PARAM_INT, 'The context id for the service', false),
                'id' => new external_value(PARAM_INT, 'The id of the video uploaded', false),
            )
        );
    }
    public static function delete_video($contextid, $id){
        $stream = new \block_stream\stream();
        return $stream->delete_uploaded_video($id);
    }
    public static function delete_video_returns(){
        return new external_value(PARAM_BOOL, 'data');
    }
    public static function update_video_parameters(){
        return new external_function_parameters(
            array(
                'videoid' => new external_value(PARAM_RAW, 'The videoid of the uploaded video', false),
                'streamurl' => new external_value(PARAM_RAW, 'The streamurl of the uploaded video', false),
            )
        );
    }
    public static function update_video($videoid, $streamurl){
        global $DB;
        try{
            $dataObj = new stdClass();
            $dataObj->id = $DB->get_field('uploaded_videos', 'id', array('videoid' => $videoid), MUST_EXIST);
            $dataObj->streamurl = $streamurl;
            $DB->update_record('uploaded_videos', $dataObj);
            return true;
        }catch(Exception $e){
            return false;
        }
    }
    public static function update_video_returns(){
        return new external_value(PARAM_BOOL, 'data');
    }
}
