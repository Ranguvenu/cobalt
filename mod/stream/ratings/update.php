<?php
define('AJAX_SCRIPT', true);
require_once(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->dirroot.'/mod/stream/ratings/lib.php');
global $USER,$DB,$CFG;

$itemid = $_REQUEST['itemid'];
$ratearea = $_REQUEST['ratearea'];
$rating = $_REQUEST['rating'];
$heading = $_REQUEST['heading'];

$rate = new stdClass;
$rate->itemid = $itemid;
$rate->ratearea = $ratearea;
$rate->userid = $USER->id;
$rate->rating = (int)(round($rating));
$rate->timecreated = time();
$rate->timemodified = time();

if(!$existeddata = $DB->get_record('stream_rating', array( 'itemid'=>$itemid, 'ratearea'=>$ratearea, 'userid'=>$USER->id))){
    $rate->id = $DB->insert_record( 'stream_rating', $rate );

}
else{
	$updatedata = new stdClass();
	$updatedata->id = $existeddata->id;
	$updatedata->rating = $rate->rating;
	$updatedata->timemodified = time();
	$DB->update_record('stream_rating', $updatedata);
}
$numstars = $rating*2;
$return_values = array();

$avgratings = get_rating($itemid, $ratearea);
$starsobtained = $avgratings->avg/*/2*/;
$res = "$starsobtained";
$return_values = array();
$return_values[] = $res;
$return_values[] = $avgratings->count;
$ratings_likes = $DB->get_record('stream_ratings_likes', array('module_area' => $ratearea, 'module_id' => $itemid));
if($ratings_likes){
	$ratings_likes->module_rating = $res;
	$ratings_likes->module_rating_users = $avgratings->count;
	$ratings_likes->timemodified = time();
	$DB->update_record('stream_ratings_likes', $ratings_likes);
}else{
	$ratings_likes = new stdClass();
	$ratings_likes->module_id = $itemid;
	$ratings_likes->module_area = $ratearea;
	$ratings_likes->module_rating = $res;
	$ratings_likes->module_rating_users = $avgratings->count;
	$ratings_likes->timecreated = time();
	$DB->insert_record('stream_ratings_likes', $ratings_likes);
}

echo $starsobtained;