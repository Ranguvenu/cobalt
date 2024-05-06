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



defined('MOODLE_INTERNAL') || die;

/**
 * Supported features
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know
 */
function stream_supports($feature) {
    switch($feature) {
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_COMPLETION_HAS_RULES: return true;
        case FEATURE_BACKUP_MOODLE2: return true;
        default: return null;
    }
}
/**
 * List the actions that correspond to a post of this module.
 * This is used by the participation report.
 *
 * Note: This is not used by new logging system. Event with
 *       crud = ('c' || 'u' || 'd') and edulevel = LEVEL_PARTICIPATING
 *       will be considered as post action.
 *
 * @return array
 */
function stream_get_post_actions() {
    return array('update', 'add');
}

/**
 * Add url instance.
 * @param object $data
 * @param object $mform
 * @return int new url instance id
 */
function stream_add_instance($data, $mform) {
    global $CFG, $DB, $USER;

    require_once($CFG->dirroot.'/mod/stream/locallib.php');

    $displayoptions = array();

    $displayoptions['width']  = $data->width;
    $displayoptions['height'] = $data->height;
    $data->displayoptions = json_encode($displayoptions);
	$externalurl=$data->externalurl;
    $data->externalurl = stream_fix_submitted_url($externalurl);
    $data->usercreated = $USER->id;
    $data->timecreated = time();
    $data->id = $DB->insert_record('stream', $data);

    return $data->id;
}

/**
 * Update url instance.
 * @param object $data
 * @param object $mform
 * @return bool true
 */
function stream_update_instance($data, $mform) {
   global $CFG, $DB, $USER;

   require_once($CFG->dirroot.'/mod/stream/locallib.php');
   
   $displayoptions = ['width' => $data->width, 
                      'height' => $data->height];
   $data->displayoptions = json_encode($displayoptions);
   $externalurl=$data->externalurl;
   $data->externalurl = stream_fix_submitted_url($externalurl);
   $data->usermodified = $USER->id;
   $data->timemodified = time();
   $data->id           = $data->instance;

   $DB->update_record('stream', $data);

   return true;
}

/**
 * Delete url instance.
 * @param int $id
 * @return bool true
 */
function stream_delete_instance($id) {
    global $DB;

    if (!$stream = $DB->get_record('stream', array('id'=>$id))) {
        return false;
    }

    // note: all context files are deleted automatically

    $DB->delete_records('stream', array('id'=>$url->id));

    return true;
}

/**
 * Given a course_module object, this function returns any
 * "extra" information that may be needed when printing
 * this activity in a course listing.
 *
 * See {@link get_array_of_activities()} in course/lib.php
 *
 * @param object $coursemodule
 * @return cached_cm_info info
 */
function stream_get_coursemodule_info($coursemodule) {
    global $CFG, $DB;
    require_once("$CFG->dirroot/mod/stream/locallib.php");

    if (!$url = $DB->get_record('stream', array('id'=>$coursemodule->instance),
            'id, name, display, displayoptions, externalurl, parameters, intro, introformat')) {
        return NULL;
    }

    $info = new cached_cm_info();
    $info->name = $url->name;

    //note: there should be a way to differentiate links from normal resources
    $info->icon = stream_guess_icon($url->externalurl, 24);

    $display = stream_get_final_display_type($url);

    if ($coursemodule->showdescription) {
        // Convert intro to html. Do not filter cached version, filters run at display time.
        $info->content = format_module_intro('stream', $url, $coursemodule->id, false);
    }

    return $info;
}

/**
 * Return a list of page types
 * @param string $pagetype current page type
 * @param stdClass $parentcontext Block's parent context
 * @param stdClass $currentcontext Current context of block
 */
function stream_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $module_pagetype = array('mod-url-*'=>get_string('page-mod-url-x', 'url'));
    return $module_pagetype;
}

/**
 * Export URL resource contents
 *
 * @return array of file content
 */
function stream_export_contents($cm, $baseurl) {
    global $CFG, $DB;
    require_once("$CFG->dirroot/mod/stream/locallib.php");
    $contents = array();
    $context = context_module::instance($cm->id);

    $course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
    $urlrecord = $DB->get_record('stream', array('id'=>$cm->instance), '*', MUST_EXIST);

   // $fullurl = str_replace('&amp;', '&', stream_get_full_url($urlrecord, $cm, $course));
   $fullurl = $urlrecord->externalurl;
    $isurl = clean_param($fullurl, PARAM_URL);
    if (empty($isurl)) {
        return null;
    }

    $url = array();
    $url['type'] = get_string('url', 'stream');
    $url['filename']     = clean_param(format_string($urlrecord->name), PARAM_FILE);
    $url['filepath']     = null;
    $url['filesize']     = 0;
    $url['fileurl']      = $fullurl;
    $url['timecreated']  = null;
    $url['timemodified'] = $urlrecord->timemodified;
    $url['sortorder']    = null;
    $url['userid']       = null;
    $url['author']       = null;
    $url['license']      = null;
    $contents[] = $url;

    return $contents;
}

/**
 * Register the ability to handle drag and drop file uploads
 * @return array containing details of the files / types the mod can handle
 */
function stream_dndupload_register() {
    return array('types' => array(
                     array('identifier' => get_string('url', 'stream'), 'message' => get_string('createurl', 'url'))
                 ));
}

/**
 * Handle a file that has been uploaded
 * @param object $uploadinfo details of the file / content that has been uploaded
 * @return int instance id of the newly created mod
 */
function stream_dndupload_handle($uploadinfo) {
    // Gather all the required data.
    $data = new stdClass();
    $data->course = $uploadinfo->course->id;
    $data->name = $uploadinfo->displayname;
    $data->intro = '<p>'.$uploadinfo->displayname.'</p>';
    $data->introformat = FORMAT_HTML;
    $data->externalurl = clean_param($uploadinfo->content, PARAM_URL);
    $data->timemodified = time();

    // Set the display options to the site defaults.
    $config = get_config('stream');
    $data->display = $config->display;
    $data->popupwidth = $config->popupwidth;
    $data->popupheight = $config->popupheight;
    $data->printintro = $config->printintro;

    return url_add_instance($data, null);
}

/**
 * Mark the activity completed (if required) and trigger the course_module_viewed event.
 *
 * @param  stdClass $url        url object
 * @param  stdClass $course     course object
 * @param  stdClass $cm         course module object
 * @param  stdClass $context    context object
 * @since Moodle 3.0
 */
function stream_view($stream, $course, $cm, $context) {
    $params = array(
        'context' => $context,
        'objectid' => $stream->id
    );

    $event = \mod_stream\event\course_module_viewed::create($params);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('stream', $stream);
    $event->trigger();

    
    // Completion.
    $completion = new completion_info($course);
    $completion->set_module_viewed($cm);

}

 function stream_get_completion_state($course,$cm,$userid,$type) {
     global $CFG,$DB;
 
     // Get forum details
     $stream = $DB->get_record('stream', array('id' => $cm->instance), '*', MUST_EXIST);
     // If completion option is enabled, evaluate it and return true/false 
     if($stream->completionvideoenabled) {  
        $lastattempt = $DB->get_record_sql('select attempt,percentage from {stream_attempts} where moduleid=:moduleid AND userid=:userid order by id desc', 
                                           ['moduleid' => $cm->id, 'userid' => $userid]);
        if($lastattempt->attempt > 1){
            return true;
        }else if($lastattempt->percentage == 100){
            return true;
        }else{
            return false;
        }
     } else {
         // Completion option is not enabled so just return $type
         return $type;
     }
 }

/**
 * extend an assigment navigation settings
 *
 * @param settings_navigation $settings
 * @param navigation_node $navref
 * @return void
 */
function stream_extend_settings_navigation(settings_navigation $settings, navigation_node $navref) {
    global $PAGE, $DB;

    // We want to add these new nodes after the Edit settings node, and before the
    // Locally assigned roles node. Of course, both of those are controlled by capabilities.
    $keys = $navref->get_children_key_list();
    $beforekey = null;
    $i = array_search('modedit', $keys);
    if ($i === false and array_key_exists(0, $keys)) {
        $beforekey = $keys[0];
    } else if (array_key_exists($i + 1, $keys)) {
        $beforekey = $keys[$i + 1];
    }

    $cm = $PAGE->cm;
    if (!$cm) {
        return;
    }

    $context = $cm->context;
    $course = $PAGE->course;

    if (!$course) {
        return;
    }

    if (is_siteadmin()) {
        $url = new moodle_url('/mod/stream/reports.php', array('id' => $PAGE->course->id, 'cmid' => $PAGE->cm->id));

        $navref->add(get_string('report', 'stream'), $url, navigation_node::TYPE_SETTING);
    }

}
function stream_extend_navigation_course($navigation, $course, $context) {
    global $USER, $DB;
    $url = new moodle_url('/mod/stream/reports.php', array('id' => $course->id));
    $name = get_string('streamreports', 'mod_stream');
    $sql = " SELECT ue.id FROM {enrol} as e 
                JOIN {user_enrolments} as ue ON ue.enrolid = e.id
                JOIN {user} as u ON u.id = ue.userid
                JOIN {role_assignments} as ra ON ra.userid = u.id
                JOIN {role} as r ON r.id = ra.roleid 
                JOIN {context} as ctx ON e.courseid = ctx.instanceid AND ctx.id = ra.contextid AND ctx.contextlevel = 50
                WHERE u.id = :userid AND r.shortname = 'student' AND e.courseid = :courseid";
                $courseexist = $DB->get_record_sql($sql, array('courseid' => $course->id,'userid' => $USER->id));
    if(!$courseexist){
        $navigation->add($name, $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}
function mod_stream_output_fragment_drilldownreport($args){
    global $DB, $PAGE, $OUTPUT;
    $timefilter = str_replace("-",",",$args["timestamps"]);
    $time = explode (",", $timefilter);
    if(!isset($args['id'])) {
        $args['id'] = $args['cmid'];
    }
    switch ($args['function']) {
      case "toplikes":
        $toplikessql = "SELECT u.id, concat(u.firstname, ' ', u.lastname) AS user, u.email, sl.likestatus, sl.timecreated
                        FROM {stream_like} AS sl
                        JOIN {user} AS u ON u.id = sl.userid
                        WHERE sl.itemid = (SELECT cm.instance FROM {course_modules} as cm WHERE cm.id =". $args['id'] .") AND sl.likestatus = 1";
        if(count($time) == 2 && $time[0]>0 && $time[1]>0) {
            $toplikessql .= " AND sl.timemodified > {$time[0]} AND sl.timemodified < {$time[1]} ";
        }
        $records = $DB->get_records_sql($toplikessql);
        $title = get_string('likes', 'stream');
        foreach($records as $record){
            $user_picture = new user_picture($record, array('size' => 60, 'class' => 'userpic', 'link'=>false));
            $user_picture = $user_picture->get_url($PAGE);
            $userpicurl = $user_picture->out();
            $data[]['content'] = '<div class="card_left d-flex align-items-center">
                                    <div class="pull-left pr-3"><img src='.$userpicurl.' alt='. get_string('picture', 'stream') . $record->user . ' width="50px" height="50px" class="rounded-circle">
                                    </div>
                                    <div class="pull-left"> 
                                        <div class="mb-2">'.get_string('user', 'stream').' : '.$record->user.'</div>
                                        <div>'. get_string('email', 'stream') .' : '.$record->email.'</div>
                                    </div>
                                </div>
                                <div class="text-center card_right">
                                    <div class="mb-2"><span class="like_icon"><i class="fa fa-thumbs-up" aria-hidden="true"></i></span></div>
                                    <div><span>'.date("jS F Y", $record->timecreated) .'</span></div>
                                </div>';
        }
        break;
      case "toprating":
        $topratingsql = "SELECT u.id, concat(u.firstname, ' ', u.lastname) as user, u.email, sr.rating, sr.timecreated FROM {stream_rating} as sr
                                        JOIN {user} as u ON u.id = sr.userid
                                        WHERE sr.itemid = (SELECT cm.instance FROM {course_modules} as cm WHERE cm.id =". $args['id'] ." ) ";
        if(count($time) == 2 && $time[0]>0 && $time[1]>0) {
            $topratingsql .= " AND sr.timemodified > {$time[0]} AND sr.timemodified < {$time[1]} ";
        }
        $records = $DB->get_records_sql($topratingsql);
        $title = get_string('rating', 'stream');
        foreach($records as $record){
            $user_picture = new user_picture($record, array('size' => 60, 'class' => 'userpic', 'link'=>false));
            $user_picture = $user_picture->get_url($PAGE);
            $userpicurl = $user_picture->out();
            $data[]['content'] = '<div class="card_left d-flex align-items-center">
                                    <div class="pull-left pr-3"><img src='.$userpicurl.' alt='. get_string('picture', 'stream') .$record->user.'" width="50px" height="50px" class="rounded-circle">
                                    </div>
                                    <div class="pull-left"> 
                                        <div class="mb-2">'.get_string('user', 'stream').' : '.$record->user.'</div>
                                        <div>'. get_string('email', 'stream') .' : '.$record->email.'</div>
                                    </div>
                                </div>
                                <div class="text-center card_right">
                                    <div class="mb-2"><span class="">'. get_string('rated', 'stream') .$record->rating.'</span></div>
                                    <div><span>'.date("jS F Y", $record->timecreated) .'</span></div>
                                </div>';
            // $data[]['content'] = '<img src='.$userpicurl.' alt="picture of '.$record->user.'">'.$record->user.' '.$record->email.' '.$record->rating.''.date("jS F Y", $record->timecreated) .'';
        }
        break;
      case "topviews":
        $topviewssql = "SELECT u.id, concat(u.firstname, ' ', u.lastname) as user, u.email, MAX(attempt) as views, (SELECT timemodified from {stream_attempts} WHERE moduleid =". $args['id'] ." ORDER BY attempt DESC LIMIT 1) as lastview FROM {stream_attempts} as sa 
                        JOIN {user} AS u ON u.id = sa.userid
                        WHERE moduleid = ". $args['id'];
        if(count($time) == 2 && $time[0]>0 && $time[1]>0) {
            $topviewssql .= " AND sa.timemodified > {$time[0]} AND sa.timemodified < {$time[1]} ";
        }
        $topviewssql .= " GROUP BY sa.userid";
        $records = $DB->get_records_sql($topviewssql);
        $title = get_string('views', 'stream');
        foreach($records as $record){
            $user_picture = new user_picture($record, array('size' => 60, 'class' => 'userpic', 'link'=>false));
            $user_picture = $user_picture->get_url($PAGE);
            $userpicurl = $user_picture->out();
            $data[]['content'] = '<div class="card_left d-flex align-items-center">
                                    <div class="pull-left pr-3"><img src='.$userpicurl.' alt='. get_string('picture', 'stream') . $record->user.' width="50px" height="50px" class="rounded-circle">
                                    </div>
                                    <div class="pull-left"> 
                                        <div class="mb-2">'.get_string('user', 'stream').' : '.$record->user.'</div>
                                        <div>'. get_string('email', 'stream') .' : '.$record->email.'</div>
                                    </div>
                                </div>
                                <div class="text-center card_right">
                                    <div class="mb-2"><span class="like_icon"><i class="fa fa-eye" aria-hidden="true"></i></span></div>
                                    <div><span>'. $record->views .'</span></div>

                                </div>';
            // $data[]['content'] = '<img src='.$userpicurl.' alt="picture of '.$record->user.'"> '.$record->user.' '.$record->email.''.$record->views.'Last Viewed on '.date("jS F Y", $record->lastview) .'';
        }
        break;
      case "fivemin":
        $records = $DB->get_records_sql("SELECT u.id, concat(u.firstname, ' ', u.lastname) as user, u.email, sl.timecreated FROM {stream_like} as sl
                                        JOIN {course_modules} as cm on cm.instance = sl.itemid
                                        JOIN {user} as u on u.id = sl.userid
                                        WHERE cm.id = ". $args['id'] ." and sl.likestatus = 1");
        $title = get_string('likes', 'stream');
        foreach($records as $record){
            $user_picture = new user_picture($record, array('size' => 60, 'class' => 'userpic', 'link'=>false));
            $user_picture = $user_picture->get_url($PAGE);
            $userpicurl = $user_picture->out();
            $data[]['content'] = '<img src='.$userpicurl.' alt='. get_string('picture', 'stream') . $record->user .'>'.$record->user.' '.$record->email. get_string('likedon', 'stream') .date("jS F Y", $record->lastview) .'';
        }
        break;
      case "views":
        $viewssql = "SELECT sa.attempt, sa.timecreated, u.email, (SELECT timemodified from {stream_attempts} WHERE moduleid =". $args['id'] ." ORDER BY attempt DESC LIMIT 1) as lastview FROM {stream_attempts} AS sa
                                         JOIN {user} AS u ON u.id = sa.userid 
                                         WHERE userid = ". $args['userid'] ." and moduleid = ". $args['id'];
        if(count($time) == 2 && $time[0]>0 && $time[1]>0) {
            $viewssql .= " AND sa.timemodified > {$time[0]} AND sa.timemodified < {$time[1]} ";
        }
        $viewssql .= " GROUP BY attempt";
        $records = $DB->get_records_sql($viewssql);

        $title = get_string('views', 'stream');
        foreach($records as $record){
            $timecreated = date('jS F Y', $record->timecreated);
            $lastviewed = date('jS F Y', $record->lastview);
            $data[]['content'] = get_string('view', 'stream') .$record->attempt.' '.date('jS F Y h:i:s A', $record->timecreated). get_string('lastviewedon', 'stream') .date('jS F Y', $record->lastview) .'';
        }
        break;
      case "totalcoursestats":
        $totalcoursessql = "SELECT s.name AS totalvideos, s.timecreated FROM {stream} AS s
                            JOIN {course_modules} AS cm ON cm.instance = s.id AND cm.module = (SELECT id FROM {modules} WHERE name = 'stream')
                            WHERE cm.course =". $args['id'];
        if(count($time) == 2 && $time[0]>0 && $time[1]>0) {
            $totalcoursessql .= " AND s.timecreated > {$time[0]} AND s.timecreated < {$time[1]} ";
        }
        $viewssql .= " GROUP BY attempt";
        $records = $DB->get_records_sql($totalcoursessql);
        $title = get_string('views', 'stream');
        foreach($records as $record){
            $data[]['content'] = get_string('video', 'stream') . $record->totalvideos . get_string('date', 'stream') . date('jS F Y', $record->timecreated).'';
        }
        break;
      case "activecoursestats":
        $totalcoursessql = "SELECT s.name AS activevideos, s.timecreated FROM {stream} AS s
                            JOIN {course_modules} AS cm ON cm.instance = s.id AND cm.module = (SELECT id FROM {modules} WHERE name = 'stream')
                            WHERE cm.visible = '1' AND cm.course =". $args['id'];
        if(count($time) == 2 && $time[0]>0 && $time[1]>0) {
            $totalcoursessql .= " AND s.timecreated > {$time[0]} AND s.timecreated < {$time[1]} ";
        }
        $viewssql .= " GROUP BY attempt";
        $records = $DB->get_records_sql($totalcoursessql);
        $title = get_string('views', 'stream');
        foreach($records as $record){
            $timecreated = date('jS F Y', $record->timecreated);
            $data[]['content'] = get_string('video', 'stream') . $record->activevideos. get_string('date', 'stream') .date('jS F Y', $record->timecreated).'';
        }
        break;
      default:
        // echo "Your favorite color is neither red, blue, nor green!";
    }
     $reportname = $args['function'].get_string('table', 'stream').$args['id'];
    return $OUTPUT->render_from_template('mod_stream/drilldownreport', compact('title', 'data', 'reportname'));
}
// function mod_stream_output_fragment_upload_video($args){
//     global $DB, $CFG, $SESSION;
//     $args = (object)$args;
//     if(isset($_SESSION['video_organisation']) || isset($_SESSION['video_tags'])){
//         require_once($CFG->dirroot.'/repository/stream/streamlib.php');    
//         $apikey = get_config('stream', 'api_key');
//         $api_url = get_config('stream', 'api_url');
//         $secret = get_config('stream', 'secret');
//         $streamlib = new phpstream($api_url, $apikey, $secret, '', '');
//         $uploaddata = $streamlib->get_upload_data();
//         $uploaddata = json_decode($uploaddata);
//         extract((array)$uploaddata);
//         $organisations = (array)$organisations;
//         // $organisations[0] = 'Select Organization';
//         // ksort($organisations);
//         $_SESSION['video_organisation'] = $organisations;
//         $_SESSION['video_tags'] = (array)$tags;
//     }
//     $formdata = [];
//     if (!empty($args->jsonformdata)) {
//         $serialiseddata = json_decode($args->jsonformdata);
//         parse_str($serialiseddata, $formdata);
//     }
//     $editoroptions = [
//         'maxfiles' => EDITOR_UNLIMITED_FILES,
//         'maxbytes' => $course->maxbytes,
//         'trust' => false,
//         'context' => $context,
//         'noclean' => true,
//         'subdirs' => false
//     ];
//     $mform = new mod_stream\form\upload(null, array('editoroptions' => $editoroptions, 'organisations' => $_SESSION['video_organisation'], 'tags' => $_SESSION['video_tags']), 'post', '', null, true, $formdata);
//     if (!empty($args->jsonformdata) && strlen($args->jsonformdata) >2) {
//         // If we were passed non-empty form data we want the mform to call validation functions and show errors.
//         $mform->is_validated();
//     }
//     $o = $mform->render();
//     return $o;
// }
// function mod_stream_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
//     // Check the contextlevel is as expected - if your plugin is a block, this becomes CONTEXT_BLOCK, etc.

//     // Make sure the filearea is one of those used by the plugin.
//     if (!($filearea == 'video' || $filearea == 'thumbnail')) {
//         return false;
//     }

//     $itemid = array_shift($args);

//     $filename = array_pop($args);
//     if (!$args) {
//         $filepath = '/';
//     } else {
//         $filepath = '/'.implode('/', $args).'/';
//     }

//     // Retrieve the file from the Files API.
//     $fs = get_file_storage();
//     $file = $fs->get_file($context->id, 'mod_stream', $filearea, $itemid, $filepath, $filename);
//     if (!$file) {
//         return false;
//     }
//     send_file($file, $filename, null, 0, false, 0, $options);
// }
function mod_stream_get_browsevideo_form_html($mform){
    global $PAGE, $OUTPUT, $DB,$CFG;
    $cmid = optional_param('update', 0,  PARAM_INT);

    if($cmid){
        $extrenalurl = $DB->get_field_sql("SELECT s.externalurl FROM {stream} AS s JOIN {course_modules} AS cm on cm.instance = s.id WHERE cm.id = :id ", array('id' => $cmid));
        $params = json_encode(['identifier' => 'mod_stream_form_video', 'src' => $extrenalurl]);
        $PAGE->requires->js_call_amd('mod_stream/player', 'load', array($params));
        $class = '';
        $straddlink = 'Update video';
    }else{
        $class = 'hidden';
        $straddlink = 'Choose video';
    }
    
    $str = '<div id="fitem_id_externalurl" class="form-group row  url fitem  clearfix ">
            <div class="col-md-3  Browse Video" id="Browse Video">
                <label class="col-form-label d-inline " for="id_externalurl">
                    Browse video<abbr class="initialism text-danger" title="Required"><i class="icon fa fa-exclamation-circle text-danger fa-fw " title="Required" aria-label="Required"></i></abbr>
                </label>
            </div>
            <div class="col-md-9 form-inline felement" data-fieldtype="custom-url">
                <div class="'.$class.' stream_file_selector w-100" >
                <b>Preview</b>
                    <video id="mod_stream_form_video" class="stream_video video-js vjs-default-skin vjs-big-play-centered vjs-layout-medium" controls preload="auto" width="450" height="250">
                    </video>
                </div>
            ';

    $clientid = uniqid();
    $args = new stdClass();
    $args->accepted_types = '*';
    $args->return_types = FILE_EXTERNAL;
    $args->context = $PAGE->context;
    $args->client_id = $clientid;
    $args->env = 'filepicker';
    $fp = new file_picker($args);
    $options = $fp->options;
   
    $streamingId = array_search('stream', array_column($options->repositories, 'type', 'id'));
    if(!$streamingId){
        return html_writer::div(get_string('nostreamrepository', 'mod_stream', $CFG->wwwroot . '/admin/repository.php'), 'alert alert-danger');
    }
    $options->repositories = [$streamingId => $options->repositories[$streamingId]];

    $str .= '<button type="button" id="filepicker-button-js-'.$clientid.'" class="visibleifjs btn btn-secondary pull-right" style="margin-right:70%;margin-top:5px;">
    '.$straddlink.'
    </button>';
    // print out file picker
    $str .= $OUTPUT->render($fp).'</div></div>';

    $module = array('name'=>'stream_url', 'fullpath'=>'/mod/stream/js/streamurl.js', 'requires'=>array('core_filepicker'));
    $PAGE->requires->js_init_call('M.stream_url.init', array($options), true, $module);
    return $str;
  }

function mod_stream_coursemodule_standard_elements($formwrapper, $mform) {
    global $CFG, $COURSE;
    if($formwrapper->get_current()->modulename != 'zoom'){
        return false;
        exit;
    }
    $mform->addElement('header', 'streamingapp', get_string('streamingapp', 'mod_stream'));

    $mform->addElement('checkbox', 'recordsession', get_string('recordsession', 'mod_stream'));
}


function mod_stream_coursemodule_edit_post_actions($data, $course) {
global $CFG,$DB;
    
    // require_once($CFG->dirroot.'/mod/zoom/classes/webservice.php');
    // $service = new mod_zoom_webservice();
    // $recordings = $service->_make_call('meetings/82928363709/recordings');
 
    // $DB->insert_record('stream', ['course' => 2, 'name' => 'test']);
    
   
   return $data;
}

 function videosSync(){
    global $CFG;
    $streamobj = new \block_stream\stream();
    $params = $streamobj->streamlib->get_listing_params();
    $search_url = $streamobj->streamlib->api_url."/api/v1/videos/importVideo";
    $c = new curl();
    $header[] = "Content-Type: multipart/form-data";
    $c->setHeader($header);
    $c->setopt(array('CURLOPT_HEADER' => false));
    $files = [];
    if($videoInfo->thumbnail){
        $thumbnailSql = "SELECT f.id FROM {files} AS f WHERE f.filename != '.' AND f.component LIKE 'block_stream' AND f.filearea LIKE 'thumbnail' AND f.itemid = :itemid ";
        $thumbnailid = $this->db->get_field_sql($thumbnailSql, array('itemid' => $videoInfo->thumbnail));
        $params['thumbnail'] = $this->get_storedfile_object($c, $thumbnailid, 'image');
    }
    $params['video'] = $this->get_storedfile_object($c, $videoInfo->fileid, 'video');

    $params['CURLOPT_RETURNTRANSFER'] = 1;
    $params['CURLOPT_POST'] = 1;
    $params += (array)$videoInfo;
    $params['key'] = $streamobj->streamlib->client_id;
    $params['secret'] = $streamobj->streamlib->secret;
    $contents = $c->post($search_url, $params);
    $content = json_decode($contents, true);

    if (!$content['error']) {
        $params['video']->delete();
        $videoInfo->status = 1;
        $videoInfo->uploaded_on = $videoInfo->timemodified = time();
        $status = $this->db->update_record('uploaded_videos', $videoInfo);
    }
    $context = \context_system::instance();
    $params = array(
            'context' => $context,
            'objectid' => $status,
            'other' => array('error' => $content['error'], 'msg' => $content['message'])
        );
        $event = \block_stream\event\video_synced::create($params);
        $event->trigger();
    }
     function get_storedfile_object(&$curlobj, $fileid, $filetype){
        $fs = get_file_storage();
        $fileinfo = $fs->get_file_by_id($fileid);
        $fileinfo->add_to_curl_request($curlobj, $filetype);
        $source = @unserialize($fileinfo->get_source());
        $filename = '';
        if (is_object($source)) {
            $filename = $source->source;
        } else {
            // If source is not a serialised object, it is a string containing only the filename.
            $filename = $fileinfo->get_source();
        }
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        if (empty($extension)) {
            $extension = mimeinfo_from_type('extension', $fileinfo->get_mimetype());
            $filename .= '.' . $extension;
        }
        $mimetype = mimeinfo('type', $filename);
        list($mediatype, $subtype) = explode('/', $mimetype);
        if ($mediatype != $filetype) {
            throw new \moodle_exception('wrongmimetypedetected', 'block_stream');
        }
        $fileinfo->postname = $filename;
        $fileinfo->mime = $mimetype;
        return $fileinfo;
    }

function stream_cm_info_view(cm_info $cm) {
    global $CFG;
     // $cm->set_after_link("Any shit here");
}
