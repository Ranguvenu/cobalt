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
namespace block_stream;

defined('MOODLE_INTERNAL') || die;

use stdClass;

class stream{

	public function __construct(){
		global $DB, $CFG;
		$this->db = $DB;
		require_once($CFG->dirroot.'/repository/stream/streamlib.php');
        $streamconfig = get_config('stream');
	    $apikey = $streamconfig->api_key;
	    $api_url = $streamconfig->api_url;
	    $secret = $streamconfig->secret;
        $this->streamlib = new \phpstream($api_url, $apikey, $secret, '', '');
	}

	public function uploadedVideoData($search=array(), $params=array(), $onlycount = true){
        global $DB, $OUTPUT;
        $curlparams = array();
		$videossql = "SELECT uv.id, uv.title, uv.timecreated,uv.videoid,uv.status, concat(u.firstname,' ',u.lastname) AS usercreated";
        $videoidsql = "SELECT uv.videoid ";
        $countsql = "SELECT count(uv.id) ";

        $uploadedvideosSql = " FROM {uploaded_videos} as uv 
                                JOIN {user} AS u on u.id = uv.usercreated WHERE 1=1 ";
	   
		if(!empty($params->search)){
			$uploadedvideosSql .= " AND uv.title LIKE '%{$params->search}%' ";
		}
        if($params->statusfilter == 'inprogress'){
            $uploadedvideosSql .=" AND uv.status=0";
        }elseif ($params->statusfilter == 'published') {
            $uploadedvideosSql .=" AND uv.status=1";
        }
        if($params->sort == 'fullname'){
            $uploadedvideosSql .= " ORDER BY uv.title ASC ";
        }elseif ($params->sort == 'uploadeddate') {
            $uploadedvideosSql .= " ORDER BY uv.timecreated DESC ";
        }else{
            $uploadedvideosSql .= " ORDER BY uv.id DESC ";
        }
		

        $total = $this->db->count_records_sql($countsql . $uploadedvideosSql);

        if($onlycount){
            return ['data' => array(), 'length' => $total];
        }

		$uploadedvideos = $this->db->get_records_sql($videossql . $uploadedvideosSql, [], $params->offset, $params->limit);

        $videoids = $this->db->get_fieldset_sql($videoidsql . $uploadedvideosSql . 'LIMIT ' . $params->offset .','. $params->limit);

        $videoids_array = json_encode(['ids' => $videoids]);

   
        $curlparams['videoids'] = $videoids_array;

        $curlparams['perpage'] = $total;
        $content = $this->streamlib->get_videos($curlparams);

        foreach($uploadedvideos as $data){
           
            $thumb = array_search($data->videoid, array_column($content['data'], 'videoid'));
            $image = $OUTPUT->image_url('video', 'block_stream')->out(false);

            if(isset($thumb) && $content['data'][$thumb]['videoid'] == $data->videoid){
             
                $image = $this->streamlib->api_url.$content['data'][$thumb]['thumbnail'];
                
                $videopath = $content['data'][$thumb]['path'];
            }

            if($data->status == 1 && $content['data'][$thumb]['status'] >= 2) {
                $DB->delete_records('uploaded_videos', ['id' => $data->id]);
                continue;
            }
            
            $returndata[] = ['id' => $data->id,
                             'title' => $data->title, 
                             'thumbnail' => $image,
                             'timecreated' => userdate($data->timecreated),
                             'usercreated' => $data->usercreated,
                             'path' => $videopath,
                             'videoid' => $data->videoid,
                             'status' => $data->status];
        }


		return ['data' => $returndata, 'length' => $total];
        
	}
	public function block_content(){
        $search_url = $this->streamlib->createSearchApiUrl();
        $curlparams = $this->streamlib->get_listing_params();
        $curlparams['q'] = '';
        $curlparams['perpage'] = 1;
        $curlparams['page'] = 1;
		$c = new \curl();
        $content = $c->post($search_url, $curlparams);
		$content = json_decode($content,true);
		$totalVideos = $content['meta']['total'];
		$uploadedVideos = $this->db->count_records('uploaded_videos');
		$syncedVideos = $this->db->count_records('uploaded_videos', array('status' => 1));
		$systemcontext = \context_system::instance();
		$viewcap = is_siteadmin() || has_capability('block/stream:viewvideos', $systemcontext);
		return ['totalVideos' => $totalVideos, 'uploadedVideos' => $uploadedVideos, 'syncedVideos' => $syncedVideos, 'viewcap' => $viewcap];
	}
	public function delete_uploaded_video($id){
		try{
			$streamData = $this->db->get_record('uploaded_videos', array('id' => $id), 'id, filepath, thumbnail', MUST_EXIST);
			$this->delete_file_instance($streamData->filepath, 'video');
			if(!empty($streamData->thumbnail)){
				$this->delete_file_instance($streamData->thumbnail, 'thumbnail');
			}
            $context = \context_system::instance();
            $params = array(
                'context' => $context,
                'objectid' => $id
            );
            $event = \block_stream\event\video_deleted::create($params);
            $event->trigger();
			$this->db->delete_records('uploaded_videos', array('id' => $id));
			return true;
		}catch(Exception $e){
			return false;
		}
	}
	private function delete_file_instance($itemid, $filearea){
		$fileid = $this->db->get_field_sql("SELECT id FROM {files} where itemid = :itemid AND component LIKE :component AND filearea LIKE :filearea AND filename <> '.' ", array('itemid' => $itemid, 'component' => 'block_stream', 'filearea' => $filearea));
		if($fileid){
			$filesystem = get_file_storage();
			$fileinfo = $filesystem->get_file_by_id($fileid);
			$fileinfo->delete();
		}
	}
	public function insert_stream_content($validateddata, $tags, $context){
		global $_SESSION, $USER;
		$insertdata = new stdClass();
    	$insertdata->videoid = uniqid();
    	$insertdata->title = $validateddata->title;
    	$insertdata->organization = $validateddata->organization;
    	$insertdata->tags = is_array($validateddata->tags) ? implode(',', $validateddata->tags): $validateddata->tags;
    	$insertdata->description = $validateddata->description['text'];
    	$insertdata->filepath = $validateddata->filepath;
    	// file_save_draft_area_files($validateddata->filepath,  $context->id,  'block_stream', 'video',  $validateddata->filepath);
        

    	$insertdata->filename = $this->db->get_field_sql("SELECT filename FROM {files} WHERE itemid = {$validateddata->filepath} AND filename != '.' ");
    	if($this->db->record_exists('files', array('itemid' => $validateddata->thumbnail))){
    		file_save_draft_area_files($validateddata->thumbnail,  $context->id,  'block_stream', 'thumbnail',  $validateddata->thumbnail);
    		$insertdata->thumbnail = $validateddata->thumbnail;
    	}else{
    		$insertdata->thumbnail = '';
    	}
    	$tagsname = [];
    	foreach($validateddata->tags as $tag){
			$tagsname[] = $tags[$tag];
    	}
    	$insertdata->tagsname = implode(',', $tagsname);
    	$insertdata->timecreated = time();
    	$insertdata->usercreated = $USER->id;
        $insertdata->status = 0;
    	$uploadid = $this->db->insert_record('uploaded_videos', $insertdata);
    	unset($_SESSION['video_organisation']);
    	unset($_SESSION['video_tags']);
    	return $uploadid;
	}
}
