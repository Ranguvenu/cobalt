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
 * URL external API
 *
 * @package    mod_url
 * @category   external
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot.'/lib/completionlib.php');
/**
 * URL external functions
 *
 * @package    mod_url
 * @category   external
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */
class mod_stream_external extends external_api {
    public static $reportname = '';
	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 * @since Moodle 3.0
	 */
	public static function view_stream_url_parameters() {
		return new external_function_parameters(
			array(
				'urlid' => new external_value(PARAM_INT, 'url instance id')
			)
		);
	}

	/**
	 * Trigger the course module viewed event and update the module completion status.
	 *
	 * @param int $urlid the url instance id
	 * @return array of warnings and status result
	 * @since Moodle 3.0
	 * @throws moodle_exception
	 */
	public static function view_stream_url($urlid) {
		global $DB, $CFG;
		require_once($CFG->dirroot . "/mod/url/lib.php");

		$params = self::validate_parameters(self::view_url_parameters(),
											array(
												'urlid' => $urlid
											));
		$warnings = array();

		// Request and permission validation.
		$url = $DB->get_record('url', array('id' => $params['urlid']), '*', MUST_EXIST);
		list($course, $cm) = get_course_and_cm_from_instance($url, 'url');

		$context = context_module::instance($cm->id);
		self::validate_context($context);

		require_capability('mod/url:view', $context);

		// Call the url/lib API.
		url_view($url, $course, $cm, $context);

		$result = array();
		$result['status'] = true;
		$result['warnings'] = $warnings;
		return $result;
	}

	/**
	 * Returns description of method result value
	 *
	 * @return external_description
	 * @since Moodle 3.0
	 */
	public static function view_stream_url_returns() {
		return new external_single_structure(
			array(
				'status' => new external_value(PARAM_BOOL, 'status: true if success'),
				'warnings' => new external_warnings()
			)
		);
	}
	public static function  get_stream_content_parameters() {
		return new external_function_parameters (
			array(
				'cmid' => new external_value(PARAM_INT, 'UserID')
			)
		);
	}
	public static function get_stream_content($cmid) {
		global $USER, $DB, $CFG;
		require_once($CFG->dirroot.'/mod/stream/locallib.php');
		$data = $DB->get_record_sql("SELECT s.* FROM {stream} as s
			JOIN {course_modules} as cm ON cm.instance = s.id WHERE cm.id = ".$cmid);
		$result[] = array(
			'id' => $data->id,
			'course' => $data->course,
			'name' => $data->name,
			'intro' => $data->intro,
			'introformat' => $data->introformat,
			'externalurl' => $data->externalurl,
			'display' => $data->display,
			'displayoptions' => $data->displayoptions,
			'parameters' => $data->parameters,
			'timemodified' => $data->timemodified
		);
		return array('media' => $result);
	}
	public static function get_stream_content_returns() {
		return new external_single_structure(
			array(
				'media' =>  new external_multiple_structure(
					new external_single_structure(
						array(
							'id' => new external_value(PARAM_INT, 'Module id'),
							'course' => new external_value(PARAM_INT, 'Course module id'),
							'name' => new external_value(PARAM_RAW, 'saranyu name'),
							'intro' => new external_value(PARAM_RAW, 'Summary'),
							'introformat' => new external_format_value('intro', 'Summary format'),
							'externalurl' => new external_value(PARAM_RAW, 'saranyu content'),
							'display' => new external_value(PARAM_RAW, 'saranyu content'),
							'displayoptions' => new external_value(PARAM_RAW, 'saranyu content'),
							'parameters' => new external_value(PARAM_RAW, 'saranyu content'),
							'timemodified' => new external_value(PARAM_RAW, 'saranyu content')
						)
					)
				)
			)
		);
	}

	public static function mod_streamattempts_parameters() {
		return new external_function_parameters (
			array (
				'moduleid' => new external_value(PARAM_INT, 'moduleid'),
				'courseid' => new external_value(PARAM_INT, 'courseid'),
				'duration' => new external_value(PARAM_RAW, 'whether to return courses that the user can see
																	even if is not enroled in. This requires the parameter courseids
																	to not be empty.', VALUE_DEFAULT, false),
				'currenttime' => new external_value(PARAM_RAW, 'whether to return courses that the user can see
																	even if is not enroled in. This requires the parameter courseids
																	to not be empty.', VALUE_DEFAULT, false),
				'event' => new external_value(PARAM_RAW, 'whether to return courses that the user can see
																	even if is not enroled in. This requires the parameter courseids
																	to not be empty.', VALUE_DEFAULT, false)
			)
		);
	}

	/**
	 * Inserting and Updating data into database
	 * @param int $moduleid - Course module id
	 * @param int $courseid - course id
	 * @param int $duration - Length of the video
	 * @param int $curenttime - shows the current time when event triggered(pause)
	 * @param int $event - sending static values like play/pause/completed
	 * @return Update the data into database
	 */
	public static function mod_streamattempts($moduleid, $courseid=null, $duration=null, $currenttime=null, $event=null) {
		global $DB, $USER;
		$context = context_module::instance($moduleid);
		$course = get_course($courseid);
		$cm = get_coursemodule_from_id('stream', $moduleid);
		$stream = $DB->get_record('stream', ['id' => $cm->instance]);
		if (!is_siteadmin() && has_capability('mod/stream:create', $context)) {
        	$percentage = $DB->get_field_sql("SELECT percentage FROM {stream_attempts} where moduleid = ".$moduleid." AND userid =".$USER->id." ORDER BY id desc");
			if($percentage == '' || $percentage == '100'){
				if($event == 'play'){
					$attempt = $DB->get_field_sql("SELECT attempt FROM {stream_attempts} where moduleid = ".$moduleid." AND userid =".$USER->id." ORDER BY id desc");
					try {
						$data = new stdclass();
						$data->moduleid = $moduleid;
						$data->courseid = $courseid;
						$data->userid = $USER->id;
						$data->timecreated = time();
						$data->duration = $duration;
						if($attempt == ''){
							$data->attempt = '1';
						} else{
							$data->attempt = ++$attempt;
						}
						$data->usercreated = $USER->id;
						$status['recordid'] = $DB->insert_record('stream_attempts', $data);
					    $params = array(
					        'context' => $context,
					        'objectid' => $status['recordid']
					    );
					    $eventcheck = \mod_stream\event\stream_played::create($params);
					    $eventcheck->trigger();
						return $status;
					}catch(\Exception $e){
			    		$error = true;
			    		$report = 'Message: ' .$e->getMessage();
			    	}
				} else {
					$status['recordid'] = '1';
					return $status;
				}
			} else if($event == 'pause') {
				try{
					$record = $DB->get_record_sql("SELECT id, attempt FROM {stream_attempts} where moduleid = ".$moduleid." AND userid =".$USER->id." ORDER BY id desc");
					$data = new stdclass();
					$data->id = $record->id;
					$pauselog = ($currenttime/$duration)*100;
					$data->percentage = $pauselog;
					$data->completedduration = $currenttime;
					$data->last_accessed = time();
					$data->timemodified = time();
					$DB->update_record('stream_attempts', $data);
					$status['recordid'] = $data->id;
				    $params = array(
				        'context' => $context,
				        'objectid' => $status['recordid']
				    );
					if($currenttime == $duration){
						$completion=new completion_info($course);
						if($completion->is_enabled($cm) && $stream->completionvideoenabled) {
						    $completion->update_state($cm,COMPLETION_COMPLETE);
						}
					    $eventcheck = \mod_stream\event\video_completed::create($params);
					}else{
					    $eventcheck = \mod_stream\event\stream_paused::create($params);
					}
					$eventcheck->trigger();
					return $status;
				}catch(\Exception $e){
		    		$error = true;
		    		$report = 'Message: ' .$e->getMessage();
		    	}
			} else {
				$duration = $DB->get_field_sql("SELECT completedduration FROM {stream_attempts} where moduleid = ".$moduleid." AND userid =".$USER->id." ORDER BY id desc");
				$status['recordid'] = $duration;
				    $params = array(
				        'context' => $context,
				        'objectid' => $status['recordid']
				    );
				    $eventcheck = \mod_stream\event\stream_played::create($params);
				    $eventcheck->trigger();
				return $status;
			}
		} else {
            return ['recordid' => ''];
        }
	}

	/**
	 * Describes the streamingstat return value
	 * @return external_single_structure
	 */
	public static function mod_streamattempts_returns() {
		return new external_single_structure (
			array (
				'recordid' => new external_value(PARAM_RAW, 'recordid'),
			)
		);
	}
    public static function get_specific_rating_info_parameters(){
		return new external_function_parameters(
            array(
                'contextid' => new external_value(PARAM_INT, 'The context id for rating', false),
                'itemid' => new external_value(PARAM_INT, 'itemid', 0),
                'ratearea' => new external_value(PARAM_TEXT, 'ratearea', false)
            )
        );
	}
	public static function get_specific_rating_info($contextid, $itemid, $ratearea){
		global $PAGE;
		$params = self::validate_parameters(
            self::get_specific_rating_info_parameters(),
            [
                'contextid' => $contextid,
                'itemid' => $itemid,
                'ratearea' => $ratearea
            ]
        );
		$PAGE->set_context(\context_system::instance());
        $lib = new \mod_stream\lib\ratinglib();
        $return = $lib->get_specific_rating_info($params['itemid'], $params['ratearea']);
        return array('rows' => $return);
	}
	public static function get_specific_rating_info_returns(){
        return new external_single_structure([
            'rows' => new external_multiple_structure(
                new external_single_structure([
                    'rateheader' => new external_value(PARAM_TEXT, 'Rating header'),
                    'bar_class' => new external_value(PARAM_TEXT, 'rating bar class'),
                    'ratedusers_count' => new external_value(PARAM_INT, 'Rated Users Count'),
                    'rating_perc' => new external_value(PARAM_RAW, 'percentage value of rating'),
                    'rating' => new external_value(PARAM_INT, 'Rating', VALUE_OPTIONAL),
                ])
            )
        ]);
	}
    public static function save_comment_parameters(){
        return new external_function_parameters(
            array(
                'contextid' => new external_value(PARAM_INT, 'The context id for rating', false),
                'itemid' => new external_value(PARAM_INT, 'itemid'),
                'commentarea' => new external_value(PARAM_TEXT, 'Comment Area'),
                'userid' => new external_value(PARAM_INT, 'Commenting User'),
                'comment' => new external_value(PARAM_RAW, 'Comment Area'),
            )
        );
    }
    public static function save_comment($contextid, $itemid, $commentarea, $userid, $comment){
        global $DB;
        $params = self::validate_parameters(
            self::save_comment_parameters(),
            [
                'contextid' => $contextid,
                'itemid' => $itemid,
                'commentarea' => $commentarea,
                'userid' => $userid,
                'comment' => $comment
            ]
        );
        $commentid = $DB->get_field('stream_comment', 'id', array('itemid' => $params['itemid'], 'commentarea' => $params['commentarea'] , 'userid' => $params['userid']));
        $comment_instance = new \stdClass();
        $comment_instance->comment = $params['comment'];
        if($commentid > 0){
            $comment_instance->id = $commentid;
            $comment_instance->timemodified = time();
            $status = $DB->update_record('stream_comment', $comment_instance);
        }else{
            $comment_instance->itemid = $params['itemid'];
            $comment_instance->commentarea = $params['commentarea'];
            $comment_instance->userid = $params['userid'];
            $comment_instance->timecreated = time();
            $status = $DB->insert_record('stream_comment', $comment_instance, false);
        }
        return array('success' => $status);
    }
    public static function save_comment_returns(){
        return new external_single_structure(
            array(
                'success' => new external_value(PARAM_BOOL, 'status of comment')
            )
        );
    }
    public static function display_ratings_content_parameters(){
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'options'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid'),
            'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
        ]);
    }
    public static function display_ratings_content(
        $options,
        $dataoptions,
        $offset = 0,
        $limit = 0,
        $contextid,
        $filterdata){

        $params = self::validate_parameters(
            self::display_ratings_content_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'contextid' => $contextid,
                'filterdata' => $filterdata
            ]
        );

        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($filterdata);
        $filteroptions = json_decode($options);
        if(is_array($filtervalues)){
            $filtervalues = (object)$filtervalues;
        }
        $defaults = new \stdClass();
        $defaults->thead = false;
        $defaults->start = $offset;
        $defaults->length = $limit;
        $decoded_dataoptions = json_decode($params['dataoptions']);
        $defaults->itemid = $decoded_dataoptions->itemid;
        $defaults->commentarea = $decoded_dataoptions->commentarea;

        $ratings_lib = new \mod_stream\lib\ratinglib();
        $records = $ratings_lib->get_ratings_content($defaults, $filtervalues);
        $totalcount = $records['totalrecords'];
        $data = $records['records'];
        return [
            'totalcount' => $totalcount,
            'filterdata' => $filterdata,
            'records' =>$data,
            'options' => $options,
            'dataoptions' => $dataoptions,
        ];

    }
    public static function display_ratings_content_returns(){
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of count in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
            'records' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'id' => new external_value(PARAM_INT, 'id of review'),
                                    'userpic' => new external_value(PARAM_RAW, 'url of userimage'),
                                    'userfullname' => new external_value(PARAM_RAW, 'User Fullname'),
                                    'rating' => new external_value(PARAM_TEXT, 'rating value'),
                                    'likestatus' => new external_value(PARAM_TEXT, 'Status of Like'),
                                    'comment' => new external_value(PARAM_RAW, 'Review posted')
                                )
                            )
                        )
        ]);
    }
    public static function set_module_rating_parameters(){
        return new external_function_parameters(
            array(
                'contextid' => new external_value(PARAM_INT, 'The context id for rating', false),
                'itemid' => new external_value(PARAM_INT, 'itemid'),
                'ratearea' => new external_value(PARAM_TEXT, 'Rating Area'),
                'userid' => new external_value(PARAM_INT, 'Rating User'),
                'rating' => new external_value(PARAM_INT, 'Rating value'),
            )
        );
    }
    public static function set_module_rating($contextid, $itemid, $ratearea, $userid, $rating){
        $params = self::validate_parameters(
            self::set_module_rating_parameters(),
            [
                'contextid' => $contextid,
                'itemid' => $itemid,
                'ratearea' => $ratearea,
                'userid' => $userid,
                'rating' => $rating
            ]
        );
        $rating_lib = new \mod_stream\lib\ratinglib();
        $rating_lib->
        $rateid = $DB->get_field('stream_rating', 'id', array('itemid' => $params['itemid'], 'ratearea' => $params['ratearea'] , 'userid' => $params['userid']));
        $rate_instance = new \stdClass();
        $rate_instance->rating = $params['rating'];
        if($rateid > 0){
            $rate_instance->id = $rateid;
            $rate_instance->timemodified = time();
            $status = $DB->update_record('stream_rating', $rate_instance);
        }else{
            $rate_instance->itemid = $params['itemid'];
            $rate_instance->ratearea = $params['ratearea'];
            $rate_instance->userid = $params['userid'];
            $rate_instance->timecreated = time();
            $status = $DB->insert_record('stream_rating', $rate_instance);
        }
        return $params['rating'];
    }
    public static function set_module_rating_returns(){
        return new external_value(PARAM_INT, 'Rating posted by the user');
    }
    public static function like_dislike_parameters(){
        return new external_function_parameters(
            array(
                'componentid' => new external_value(PARAM_INT, 'component id'),
                'likestatus' => new external_value(PARAM_INT, 'Like status')
            )
        );
    }
    public static function like_dislike( $componentid, $likestatus){
        global $DB,$USER;
        $params = self::validate_parameters(
            self::like_dislike_parameters(),
            [
                'componentid' => $componentid,
                'likestatus' => $likestatus
            ]
        );
        $data = new stdClass();
        $data->userid = $USER->id;
        $data->itemid = $componentid;
        if($existdata = $DB->get_record('stream_like',array('userid' => $data->userid, 'itemid'=>$componentid))){
        	try{
	            $updatedata = new stdClass();
	            $updatedata->id = $existdata->id;
	            $updatedata->likestatus = $likestatus;
	            $updatedata->timemodified = time();
	            $updatedata->module_id = $componentid;
	            $result = $DB->update_record('stream_like', $updatedata);
				$context = context_module::instance($componentid);
			    $params = array(
			        'context' => $context,
			        'objectid' => $result
			    );
			    if($likestatus == 1){
				    $eventcheck = \mod_stream\event\video_liked::create($params);
			    } else {
				    $eventcheck = \mod_stream\event\video_disliked::create($params);
			    }
			    $eventcheck->trigger();
        	} catch(\Exception $e){
    			$error = true;
    			$report = 'Message: ' .$e->getMessage();
    		}
        }
        else{
        	try {
	            $data->timecreated = time();
	            $data->timemodified = time();
	            $data->likestatus = $likestatus;
	    		$data->module_id = $componentid;
	    		$data->module_like = $likestatus;
	            $result = $DB->insert_record('stream_like', $data);
	            $context = context_module::instance($componentid);
			    $params = array(
			        'context' => $context,
			        'objectid' => $result
			    );
			    if($likestatus == 1){
				    $eventcheck = \mod_stream\event\video_liked::create($params);
			    } else {
				    $eventcheck = \mod_stream\event\video_disliked::create($params);
			    }
			    $eventcheck->trigger();
        	} catch(\Exception $e){
	    		$error = true;
	    		$report = 'Message: ' .$e->getMessage();
	    	}
        }

        if($existdata = $DB->get_record('stream_ratings_likes',array('module_id' => $componentid))){
        		$sql = "SELECT count(id) as likes FROM {stream_like} as sl WHERE itemid = {$componentid} AND likestatus = 1";
        		$likesdata = $DB->get_record_sql($sql);
                $updatedata = new stdClass();
	            $updatedata->id = $existdata->id;
	            $updatedata->likestatus = $likestatus;
	            $updatedata->timemodified = time();
	            $updatedata->module_id = $componentid;
				$updatedata->module_like = $likesdata->likes;
	            $DB->update_record('stream_ratings_likes', $updatedata);
        } else {
	            $data->timecreated = time();
	            $data->timemodified = time();
	            $data->likestatus = $likestatus;
	    		$data->module_id = $componentid;
	    		$data->module_like = $likestatus;
				$DB->insert_record('stream_ratings_likes', $data);
        }
        $like = $DB->count_records("stream_like", array('likestatus' => 1, 'itemid'=>$componentid));
        $dislike = $DB->count_records("stream_like", array('likestatus' => 2, 'itemid'=>$componentid));
            return array('status' => $result, 'like' => $like, 'dislike' => $dislike);
    }
    public static function like_dislike_returns(){
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_INT, 'status of comment'),
                'like' => new external_value(PARAM_INT, 'count of likes '),
                'dislike' => new external_value(PARAM_INT, 'Count of dislikes')
            )
        );
    }
    public static function get_likedislike_parameters(){
        return new external_function_parameters(
            array(
                'componentname' => new external_value(PARAM_TEXT, 'component name'),
                'componentid' => new external_value(PARAM_INT, 'component id')
            )
        );
    }
    public static function get_likedislike($componentname, $componentid){
        global $DB,$USER;
        $params = self::validate_parameters(
            self::get_likedislike_parameters(),
            [
                'componentname' => $componentname,
                'componentid' => $componentid
            ]
        );
        $likes = $DB->count_records('stream_like', array('likearea'=>$componentname, 'itemid' => $componentid, 'likestatus'=>'1'));
        $dislikes = $DB->count_records('stream_like', array('likearea'=>$componentname, 'itemid' => $componentid, 'likestatus'=>'2'));
        return array('likes' => $likes, 'dislikes' => $dislikes);
    }
    public static function get_likedislike_returns(){
        return new external_single_structure(
            array(
                'likes' => new external_value(PARAM_INT, 'Likes Count'),
                'dislikes' => new external_value(PARAM_INT, 'Likes Count')
            )
        );
    }
    public static function submit_rating_parameters(){
        return new external_function_parameters(
            array(
                'componentid' => new external_value(PARAM_INT, 'component id'),
                'ratingvalue' => new external_value(PARAM_INT, 'Rating value')
            )
        );
    }
    public static function submit_rating($componentid, $ratingvalue){
        global $DB,$USER,$CFG;
        $params = self::validate_parameters(
            self::submit_rating_parameters(),
            [
                'componentid' => $componentid,
                'ratingvalue' => $ratingvalue
            ]
        );

        $data = new stdClass();
        $data->userid = $USER->id;
        $data->itemid = $componentid;
        if($existdata = $DB->get_record('stream_rating',array('userid' => $data->userid,'itemid'=>$componentid))){
        	try {
        		$updatedata = new stdClass();
	            $updatedata->id = $existdata->id;
	            $updatedata->rating = $ratingvalue;
	            $updatedata->timemodified = time();
	            $result = $DB->update_record('stream_rating', $updatedata);
	            $context = context_module::instance($componentid);
				$params = array(
			        'context' => $context,
			        'objectid' => $result
			    );
			    $eventcheck = \mod_stream\event\video_rated::create($params);
			    $eventcheck->trigger();
	        }catch(\Exception $e){
	    		$error = true;
	    		$report = 'Message: ' .$e->getMessage();
	    	}
        }
        else{
        	try{
	            $data->timecreated = time();
	            $data->timemodified = time();
	            $data->rating = $ratingvalue;
	            $result = $DB->insert_record('stream_rating', $data);
	            $context = context_module::instance($componentid);
				$params = array(
			        'context' => $context,
			        'objectid' => $result
			    );
			    $eventcheck = \mod_stream\event\video_rated::create($params);
			    $eventcheck->trigger();
        	}catch(\Exception $e){
	    		$error = true;
	    		$report = 'Message: ' .$e->getMessage();
	    	}
        }
        $numstars = $ratingvalue*2;
        $return_values = array();

        $avgratings = (new mod_stream\local\stream)->get_rating($componentid, $componentname);
        $starsobtained = $avgratings->avg/*/2*/;
        $res = "$starsobtained";
        $return_values = array();
        $return_values[] = $res;
        $return_values[] = $avgratings->count;
        $ratings_likes = $DB->get_record('stream_ratings_likes', array('module_id' => $componentid));
        if($ratings_likes){
            $ratings_likes->module_rating = $res;
            $ratings_likes->module_rating_users = $avgratings->count;
            $ratings_likes->timemodified = time();
            $DB->update_record('stream_ratings_likes', $ratings_likes);
        }else{
            $ratings_likes = new stdClass();
            $ratings_likes->module_id = $componentid;
            $ratings_likes->module_rating = $res;
            $ratings_likes->module_rating_users = $avgratings->count;
            $ratings_likes->timecreated = time();
            $DB->insert_record('stream_ratings_likes', $ratings_likes);
        }

        return array('status' => $result);
    }
    public static function submit_rating_returns(){
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_INT, 'Status')
            )
        );
    }
    public static function get_ratings_parameters(){
        return new external_function_parameters(
            array(
                'componentname' => new external_value(PARAM_TEXT, 'component name'),
                'componentid' => new external_value(PARAM_INT, 'component id'),
                'ratingvalue' => new external_value(PARAM_INT, 'Rating value')
            )
        );
    }
    public static function get_ratings($componentname, $componentid, $ratingvalue){
        global $DB,$USER,$CFG;
        require_once($CFG->dirroot.'/mod/stream/ratings/lib.php');
        $params = self::validate_parameters(
            self::get_ratings_parameters(),
            [
                'componentname' => $componentname,
                'componentid' => $componentid,
                'ratingvalue' => $ratingvalue
            ]
        );

        if(is_null($ratingvalue)){
            $modulerating = $DB->get_field('stream_ratings_likes', 'module_rating', array('module_id' => $componentid, 'module_area' => $componentname));
        }else{
            $modulerating = $ratingvalue;
        }
        $avgratings = get_rating($componentid, $componentname);
        $avgrating = $avgratings->avg;
        return array('rating' => $modulerating, 'avgrating' => $avgrating);
    }
    public static function get_ratings_returns(){
        return new external_single_structure(
            array(
                'rating' => new external_value(PARAM_INT, 'rating'),
                'avgrating' => new external_value(PARAM_FLOAT, 'avgrating')
            )
        );
    }
    public static function get_reviews_parameters(){
        return new external_function_parameters(
            array(
                'componentname' => new external_value(PARAM_TEXT, 'component name'),
                'componentid' => new external_value(PARAM_INT, 'component id')
            )
        );
    }
    public static function get_reviews($componentname, $componentid){
        global $DB,$USER,$PAGE;
        $params = self::validate_parameters(
            self::get_reviews_parameters(),
            [
                'componentname' => $componentname,
                'componentid' => $componentid
            ]
        );
        $getreviews = $DB->get_records_sql("SELECT c.id as cid,c.itemid,c.commentarea,c.comment,c.timecreated,u.id,u.picture,u.firstname, u.lastname,u.firstnamephonetic,u.lastnamephonetic,
            u.middlename,u.alternatename,u.imagealt,u.email  FROM {stream_comment} c JOIN {user} as u ON c.userid = u.id  WHERE c.itemid = $componentid AND c.commentarea = '$componentname'");
        $reviews = [];
        foreach ($getreviews as $review) {
            $list=array();
            $userinfo = array();
            $list['cid'] = $review->cid;
            $list['itemid'] = $review->itemid;
            $list['commentarea'] = $review->commentarea;
            $list['comment'] = $review->comment;
            $list['timecreated'] = $review->timecreated;
            $userinfo['id'] = $review->id;
            $userinfo['firstname'] = $review->firstname;
            $userinfo['lastname'] = $review->lastname;
            $userinfo['email'] = $review->email;
            $user_picture = new user_picture($review, array('link'=>false));
            $user_picture->size = 1;
            $user_picture =$user_picture->get_url($PAGE);
            $userpic = $user_picture->out();
            $userinfo['profilepic'] = $userpic;
            $list['userinfo'] = $userinfo;
            $reviews[]=$list;
        }

        return array('reviews' => $reviews);
    }
    public static function get_reviews_returns(){
        return new external_single_structure(
            array(
                'reviews' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'cid' =>  new external_value(PARAM_INT, 'Comment id'),
                            'itemid' =>  new external_value(PARAM_INT, 'Item id'),
                            'commentarea' =>  new external_value(PARAM_TEXT, 'Component name'),
                            'comment' =>  new external_value(PARAM_RAW, 'Comment'),
                            'timecreated' =>  new external_value(PARAM_RAW, 'Time created'),
                            'userinfo' =>
                                new external_single_structure(
                                    array(
                                        'id' =>  new external_value(PARAM_INT, 'User id'),
                                        'firstname' =>  new external_value(PARAM_TEXT, 'Posted By'),
                                        'lastname' =>  new external_value(PARAM_TEXT, 'Posted By'),
                                        'email' =>  new external_value(PARAM_RAW, 'Posted By'),
                                        'profilepic' => new external_value(PARAM_RAW, 'User Profile')
                                    )
                                )
                        )
                    )
                )
            )
        );
    }
    public static function transfer_stream_report_parameters(){
        $reportname = new external_value(PARAM_TEXT, 'Report name');
    	return new external_function_parameters(
            array(
                'reportname' => $reportname,
                'videoid' => new external_value(PARAM_TEXT, 'Video Identifier', !VALUE_REQUIRED, ''),
                'lastmodified' => new external_value(PARAM_INT, 'Report last modified', !VALUE_REQUIRED, 0)
            )
        );
    }
    public static function transfer_stream_report($reportname, $videoid = '', $lastmodified = 0){
    	$reportsInfo = new \mod_stream\reports();

    	try{
    		$error = false;
    		$reportdata = $reportsInfo->get_report_info($reportname, $videoid, $lastmodified);
    		if($reportdata){
    			$report = json_encode($reportdata);
    		}else{
    			$report = '';
    		}
    	}catch(\Exception $e){
    		$error = true;
    		$report = 'Message: ' .$e->getMessage();
    	}
    	return array('error' => $error, 'report' => $report);
    }
    public static function transfer_stream_report_returns(){

    	return new external_single_structure(
            array(
                'error' => new external_value(PARAM_BOOL, 'error'),
                'report' => new external_value(PARAM_RAW, 'report')
            )
        );
    }

    public static function stream_timeperiod_parameters() {
        return new external_function_parameters (
            array (
                'timeperiod' => new external_value(PARAM_TEXT, 'timeperiod'),
                'courseid' => new external_value(PARAM_RAW, 'courseid', VALUE_OPTIONAL)
            )
        );
    }

    public static function stream_timeperiod($timeperiod, $courseid=null){
    	global $DB;

		$reportBuilder = new \mod_stream\reports();
		$timedata = $reportBuilder->timeperiod($courseid, $timeperiod);
     	return array('timeperiod' => json_encode($timedata));
    }

    public static function stream_timeperiod_returns(){
    	return new external_single_structure(
            array(
                'timeperiod' => new external_value(PARAM_RAW, 'timeperiod'),
            )
        );
    }
    public static function completedVideosbyUsersGraph_parameters(){
    	return new external_function_parameters (
            array (
                'timestamps' => new external_value(PARAM_TEXT, 'timestamps'),
                'courseid' => new external_value(PARAM_RAW, 'courseid', VALUE_OPTIONAL, null)
            )
        );
    }
    public static function completedVideosbyUsersGraph($timestamps, $courseid = null){
    	$params = self::validate_parameters(
            self::completedVideosbyUsersGraph_parameters(),
            [
                'timestamps' => $timestamps,
                'courseid' => $courseid
            ]
        );
    	$time = explode('-', $params['timestamps']);
    	if(count($time) == 2){
    		$reportBuilder = new \mod_stream\reports();
    		$data = $reportBuilder->videos_usersdata($params['courseid'], array('starttime' => $time[0], 'endtime' => $time[1]));
    		return json_encode($data, JSON_NUMERIC_CHECK);
    	}
    }
    public static function completedVideosbyUsersGraph_returns(){
    	return new external_value(PARAM_RAW, 'data');
    }
    
    public static function reportsHeader_parameters(){
    	return new external_function_parameters (
            array (
                'timestamps' => new external_value(PARAM_TEXT, 'timestamps'),
                'courseid' => new external_value(PARAM_RAW, 'courseid', VALUE_OPTIONAL, null)
            )
        );
    }
    public static function reportsHeader($timestamps, $courseid = null){
    	$params = self::validate_parameters(
            self::reportsHeader_parameters(),
            [
                'timestamps' => $timestamps,
                'courseid' => $courseid
            ]
        );
    	$time = explode('-', $params['timestamps']);
    	if(count($time) == 2){
    		$reportBuilder = new \mod_stream\reports();
    		$data = $reportBuilder->reportsHeader($params['courseid'], array('starttime' => $time[0], 'endtime' => $time[1]));
    		return json_encode($data);
    	}
    }
    public static function reportsHeader_returns(){
    	return new external_value(PARAM_RAW, 'data');
    }

    public static function reportHeader_parameters(){
    	return new external_function_parameters (
            array (
                'timestamps' => new external_value(PARAM_TEXT, 'timestamps'),
                'moduleid' => new external_value(PARAM_RAW, 'moduleid', VALUE_OPTIONAL, null)
            )
        );
    }
    public static function reportHeader($timestamps, $moduleid = null){
    	$params = self::validate_parameters(
            self::reportHeader_parameters(),
            [
                'timestamps' => $timestamps,
                'moduleid' => $moduleid
            ]
        );
    	$time = explode('-', $params['timestamps']);
    	if(count($time) == 2){
    		$reportBuilder = new \mod_stream\reports();
			$data = $reportBuilder->reportHeader($params['moduleid'], array('starttime' => $time[0], 'endtime' => $time[1]));
    		return json_encode($data);
		}
    }
    public static function reportHeader_returns(){
    	return new external_value(PARAM_RAW, 'data');
    }
    
    public static function completedUsersbyVideos_parameters(){
    	return new external_function_parameters (
            array (
                'timestamps' => new external_value(PARAM_TEXT, 'timestamps'),
                'moduleid' => new external_value(PARAM_RAW, 'moduleid')
            )
        );
    }
    public static function completedUsersbyVideos($timestamps, $moduleid){
		$params = self::validate_parameters(
            self::reportHeader_parameters(),
            [
                'timestamps' => $timestamps,
                'moduleid' => $moduleid
            ]
        );
    	$time = explode('-', $params['timestamps']);
    	if(count($time) == 2){
    		$reportBuilder = new \mod_stream\reports();
    		$data = $reportBuilder->dailyVisitsData($params['moduleid'], array('starttime' => $time[0], 'endtime' => $time[1]));
    		return json_encode($data, JSON_NUMERIC_CHECK);
    	}
    }
    public static function completedUsersbyVideos_returns(){
    	return new external_value(PARAM_RAW, 'data');
    }

    public static function topVideosInfo_parameters(){
    	return new external_function_parameters (
            array (
                'timestamps' => new external_value(PARAM_TEXT, 'timestamps'),
                'courseid' => new external_value(PARAM_RAW, 'courseid', VALUE_OPTIONAL, null)
            )
        );
    }
    public static function topVideosInfo($timestamps, $courseid = null){
    	global $CFG;
    	$params = self::validate_parameters(
            self::topVideosInfo_parameters(),
            [
                'timestamps' => $timestamps,
                'courseid' => $courseid
            ]
        );
        $time = explode('-', $params['timestamps']);
        if(count($time) == 2){
        	$reportBuilder = new \mod_stream\reports();
            $data = $reportBuilder->top_like_rate_view($params['courseid'], array('starttime' => $time[0], 'endtime' => $time[1]));
            $data['config'] = $CFG;
            return json_encode($data);
        }
    }
    public static function topVideosInfo_returns(){
    	return new external_value(PARAM_RAW, 'data');
    }
    
    public static function durationbasedinfo_parameters(){
    	return new external_function_parameters (
            array (
                'timestamps' => new external_value(PARAM_TEXT, 'timestamps'),
                'courseid' => new external_value(PARAM_RAW, 'courseid', VALUE_OPTIONAL, null)
            )
        );
    }
    public static function durationbasedinfo($timestamps, $courseid = null){
    	global $CFG;
    	$params = self::validate_parameters(
            self::durationbasedinfo_parameters(),
            [
                'timestamps' => $timestamps,
                'courseid' => $courseid
            ]
        );
        $time = explode('-', $params['timestamps']);
        if(count($time) == 2){
        	$reportBuilder = new \mod_stream\reports();
            $data = $reportBuilder->likes_based_duration($params['courseid'], array('starttime' => $time[0], 'endtime' => $time[1]));
            $data['config'] = $CFG;
            return json_encode($data);
        }
    }
    public static function durationbasedinfo_returns(){
    	return new external_value(PARAM_RAW, 'data');
    }

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
    	$renderer = $PAGE->get_renderer('stream');
    	$args = json_decode($args);
		$jsparams = json_decode(json_encode($args), true);
    	$reportBuilder = new \mod_stream\reports();
    	$id = $args->args->id;
    	$search = $jsparams["d"]["search"]["value"];
    	$timefilter = str_replace("-",",",$jsparams["d"]["columns"]["0"]["search"]["value"]);
    	$params = $jsparams['d'];

    	switch($args->args->action){
    		case 'viewreport':
    			$viewsinfo = $reportBuilder->viewsReport($id, $args->args->type, $params, $timefilter, $search);
    			$outputs = $renderer->viewreports($viewsinfo, $params);
		        return $outputs;
		    break;
		    case "coursestats":
		    	$coursesinfo = $reportBuilder->coursestats($id, $params, $search);
		    	$outputs = $renderer->courseinfo($coursesinfo, $params);
		    	return $outputs;
		    break;
		    case "getSummaryReport":
			    $timefilter = str_replace("-",",",$jsparams['args']['timestamps']);
		    	$summaryinfo = $reportBuilder->getSummaryReport($id, $params, $timefilter, $search);
		    	$outputs = $renderer->summaryinfo($summaryinfo, $params);
		    	return $outputs;
		    break;
		    case "viewsReport":
		    	$viewsdata = $reportBuilder->viewsReport($id, $type, $params, $search);
		    	$outputs = $renderer->viewsinfo($viewsdata, $params);
		    	return $outputs;
		    break;
		    case "activitystatus":
		    	$activitiesinfo = $reportBuilder->activityStatus($id, $params, $search, $timefilter);
		    	$outputs = $renderer->activityinfo($activitiesinfo, $params);
		    	return $outputs;
		    break;
    	}
    }
    public static function tablecontent_returns(){
    	return new external_single_structure(
            array(
                'iTotalRecords' => new external_value(PARAM_INT, 'draw value'),
                'iTotalDisplayRecords' => new external_value(PARAM_INT, 'draw value'),
                'data' => new external_value(PARAM_RAW, 'draw value'),
            )
        );
    }

    /**
     * Describes the parameters for get_streams_by_courses.
     *
     * @return external_function_parameters
     */
    public static function get_streams_by_courses_parameters() {
        return new external_function_parameters (
            array(
                'courseids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'Course id'), 'Array of course ids', VALUE_DEFAULT, array()
                ),
            )
        );
    }

    /**
     * Returns a list of streams in a provided list of courses.
     * If no list is provided all streams that the user can view will be returned.
     *
     * @param array $courseids course ids
     * @return array of warnings and streams
     */
    public static function get_streams_by_courses($courseids = array()) {

        $warnings = array();
        $returnedstreams = array();

        $params = array(
            'courseids' => $courseids,
        );
        $params = self::validate_parameters(self::get_streams_by_courses_parameters(), $params);

        $mycourses = array();
        if (empty($params['courseids'])) {
            $mycourses = enrol_get_my_courses();
            $params['courseids'] = array_keys($mycourses);
        }

        // Ensure there are courseids to loop through.
        if (!empty($params['courseids'])) {

            list($courses, $warnings) = external_util::validate_courses($params['courseids'], $mycourses);

            // Get the streams in this course, this function checks users visibility permissions.
            // We can avoid then additional validate_context calls.
            $streams = get_all_instances_in_courses("stream", $courses);
            foreach ($streams as $stream) {
                $context = context_module::instance($stream->coursemodule);
                // Entry to return.
                $stream->name = external_format_string($stream->name, $context->id);

                $options = array('noclean' => true);
                list($stream->intro, $stream->introformat) =
                    external_format_text($stream->intro, $stream->introformat, $context->id, 'mod_stream', 'intro', null, $options);
                $stream->introfiles = external_util::get_area_files($context->id, 'mod_stream', 'intro', false, false);

                $returnedstreams[] = $stream;
            }
        }

        $result = array(
            'streams' => $returnedstreams,
            'warnings' => $warnings
        );
        return $result;
    }

    /**
     * Describes the get_urls_by_courses return value.
     *
     * @return external_single_structure
     * @since Moodle 3.3
     */
    public static function get_streams_by_courses_returns() {
        return new external_single_structure(
            array(
                'streams' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'Module id'),
                            'coursemodule' => new external_value(PARAM_INT, 'Course module id'),
                            'course' => new external_value(PARAM_INT, 'Course id'),
                            'name' => new external_value(PARAM_RAW, 'URL name'),
                            'intro' => new external_value(PARAM_RAW, 'Summary'),
                            'introformat' => new external_format_value('intro', 'Summary format'),
                            'externalurl' => new external_value(PARAM_RAW_TRIMMED, 'External URL'),
                            'display' => new external_value(PARAM_INT, 'How to display the url'),
                            'displayoptions' => new external_value(PARAM_RAW, 'Display options (width, height)'),
                            'parameters' => new external_value(PARAM_RAW, 'Parameters to append to the URL'),
                            'timemodified' => new external_value(PARAM_INT, 'Last time the url was modified'),
                            'section' => new external_value(PARAM_INT, 'Course section id'),
                            'visible' => new external_value(PARAM_INT, 'Module visibility'),
                            'groupmode' => new external_value(PARAM_INT, 'Group mode'),
                            'groupingid' => new external_value(PARAM_INT, 'Grouping id'),
                        )
                    )
                ),
                'warnings' => new external_warnings(),
            )
        );
    }

    public static function view_stream_parameters() {
        return new external_function_parameters(
            array(
                'streamid' => new external_value(PARAM_INT, 'stream instance id')
            )
        );
    }

    /**
     * Trigger the course module viewed event and update the module completion status.
     *
     * @param int $urlid the stream instance id
     * @return array of warnings and status result
     * @throws moodle_exception
     */
    public static function view_stream($streamid) {
        global $DB, $CFG;
        require_once($CFG->dirroot . "/mod/stream/lib.php");

        $params = self::validate_parameters(self::view_stream_parameters(),
                                            array(
                                                'streamid' => $streamid
                                            ));
        $warnings = array();

        // Request and permission validation.
        $stream = $DB->get_record('stream', array('id' => $params['streamid']), '*', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($stream, 'stream');

        $context = context_module::instance($cm->id);
        self::validate_context($context);

        require_capability('mod/stream:view', $context);

        // Call the stream/lib API.
        stream_view($stream, $course, $cm, $context);

        $result = array();
        $result['status'] = true;
        $result['warnings'] = $warnings;
        return $result;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function view_stream_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'warnings' => new external_warnings()
            )
        );
    }

}
