<?php
namespace mod_stream\output;

use mod_stream\local\stream as stream;
use renderable;                                                                                                                     
use renderer_base;                                                                                                                  
use templatable; 

class player implements renderable, templatable {

    private $data;
    private $stream;
    private $properties;
    private $width=640;
    private $height=268;

    public function __construct($streaminstance, $cm) {
      $this->properties = (array)json_decode($streaminstance->displayoptions);
      $this->stream = new stream();
      $this->data['itemid'] = $cm->instance;
      $this->set_width();
      $this->set_height();
      $this->display_rating();
      $this->display_like_unlike();
    }

    public function export_for_template(renderer_base $ouput) {
      return $this->data;
    }

    public function set_width() {
        if(isset($this->properties['width']) && $this->properties['width'] !=''){
            $this->width = $this->properties['width'];
        }
        $this->data['width'] = $this->width;
    }
    public function set_height() {
        if(isset($this->properties['height']) && $this->properties['height'] !=''){
            $this->height = $this->properties['height'];
        }
        $this->data['height'] = $this->height;
    }
    /*
     * @function  display_rating
     * @todo function calculates over all rating for course
     * @returns rating image for course
    */
    function display_rating() {
        global $DB, $USER, $PAGE;
        $avgratings = $this->stream->get_rating($this->data['itemid']);
        $existrating = $DB->get_field('stream_rating','rating',array('itemid' => $this->data['itemid'], 'userid'=> $USER->id), IGNORE_MULTIPLE);
        $ratings = $existrating ? $existrating :0;
        $currentuserrating = $DB->get_record('stream_rating', array('itemid'=>$this->data['itemid'], 'userid'=>$USER->id), '*', IGNORE_MULTIPLE);
        if($currentuserrating){
            $currentrating = $currentuserrating->rating;
        }else{
            $currentrating = null;
        }
        $ask_rating_data = $this->stream->ask_for_rating($this->data['itemid'], $heading=NULL, $currentrating);
        $this->data['usercanrate'] = $ask_rating_data['enroll'];
                    $options = ['max' => 5,
                    'rgbOn' => "#efce2e",
                    'rgbOff' => "#9c9b97",
                    'rgbSelection' => "#efce2e",
                    'indicator' => "fa-star",
                    'fontsize' => "18px"
                ];
        if($ask_rating_data['enroll']) {
            $this->data['ratings'] = $ratings;
            $this->data['userid'] = $USER->id;

            $PAGE->requires->js_call_amd('mod_stream/ratings', 'init', array("#userradiostars", json_encode($options)));
            $this->data['displayrating'] = $ratings;
        } else {
            $displayrating = $DB->get_field('stream_ratings_likes', 'module_rating', array('module_id' => $this->data['itemid']), IGNORE_MULTIPLE);
            $this->data['displayrating'] = $displayrating ? round($displayrating,2) : 0;
            $PAGE->requires->js_call_amd('mod_stream/ratings', 'init', array("#userradiostars", json_encode($options)));
        }
         $this->data['avgratingscount'] = $avgratings->count;
    }
    /*  @function display_like_unlike
     *  @function returns option to like or unlike.
     */
    function display_like_unlike(){
        global $DB, $USER;
        $ask_rating_data = $this->stream->ask_for_rating($this->data['itemid'], $heading=NULL, 0);
        if(!$ask_rating_data['enroll']){
            $this->data['class'] = "disable_pointer style='pointer-events:none'";
        }else{
            $mylikestatus = $DB->get_field('stream_like', 'likestatus', array('itemid' => $this->data['itemid'], 'userid' => $USER->id));
            $this->data['likestatus'] = $mylikestatus == 1 ? true : false;
            $this->data['dislikestatus'] = $mylikestatus == 2 ? true : false; 
        }
        $this->data['likecount'] = $DB->count_records('stream_like', array('itemid'=>$this->data['itemid'], 'likestatus'=>1));
        $this->data['unlikecount'] = $DB->count_records('stream_like', array('itemid'=>$this->data['itemid'], 'likestatus'=>2));
    }
}