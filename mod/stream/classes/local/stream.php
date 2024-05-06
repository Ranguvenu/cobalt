<?php

namespace mod_stream\local;

use html_writer;
use context_course;
use context_module;

class stream {
    /*
     * @function  ask_for_rating
     * @todo function displays the empty stars to give the rating
     * @returns rating star images
    */
    function ask_for_rating($itemid) {
        global $DB, $USER, $CFG, $OUTPUT;
        $result = html_writer::start_tag('div', array('class' => 'comment_'.$itemid, 'style'=>'padding: 5px;'));
        $user = $DB->get_record('user', array('id'=>$USER->id));
        $result .= html_writer::start_tag('div', array('class'=>'comment_commentarea'));
        $result .= '<div class="example_'.$itemid.'">';
        if ((isloggedin() && !isguestuser())) {
            $disable = '';

            $enroll = true;

            $participate_info = $this->can_participate($itemid);

            $result = $participate_info['result'];
            $attribute = $participate_info['attribute'];
            $enroll = $participate_info['enroll'];
        }
        $result .= '</div>';
        $result .= html_writer::end_tag('div'); //End of .comment_commentarea
        $result .= html_writer::end_tag('div'); //End of .comment_$itemid
        return array('html' => $result, 'attribute' => $attribute , 'enroll' => $enroll);
    }
    function can_participate($itemid){
        global $USER, $DB;
        $enroll =  true;
        $disable = '';
        $result = '';

        $module = $DB->get_field('modules', 'id', array('name' => 'stream'));
        
        $cm = $DB->get_record('course_modules', array('instance' => $itemid, 'module' => $module));
        $context = context_module::instance($cm->id);
        $coursecontext = context_course::instance($cm->course);
        if(!is_enrolled($coursecontext, $USER->id)) {
            $enroll = false;
        }
        if(!$enroll){
            $disable = get_string('disabled', 'stream');
            $result .= '<div>'.get_string('enrolforrating', 'stream').'</div>';
        }
        return array('result' => $result, 'attribute' => $disable , 'enroll' => $enroll);
    }
    /*
     * @function  get_rating
     * @todo caluclates rating
     * @return average rating as numeric value
    */
    function get_rating($itemid) {
        global $CFG, $DB, $USER;
        $sql = "SELECT SUM(rating) AS sum, count(userid) AS count
        FROM {stream_rating} WHERE itemid = {$itemid} ";

        $avgrec = $DB->get_record_sql($sql);
        if(!empty($avgrec) && $avgrec->count > 0){
            $avgrec->avg = $avgrec->sum/$avgrec->count;
        }else{
            $avgrec->avg = 0;
        }

        return $avgrec;
    }
}
