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
class block_stream_renderer extends plugin_renderer_base{
	public function streamRender($streaminfo, $params){
        $content = $streaminfo['returndata'] ? $streaminfo['returndata'] : 0;
        $total = $streaminfo['total'] ? $streaminfo['total'] : 0;
        $data = [];
        foreach($content as $stream){
            $data[] = $this->render_from_template('block_stream/videocard', $stream);
        }
        if(!empty($data))
            $data = [$this->handleEmptyElements($data, $params['length']-1)];
        $outputs = array(
            "draw" => isset($params['draw']) ? intval($params['draw']) : 1,
            "iTotalRecords" => $total,
            "iTotalDisplayRecords" => $total,
            "data" => json_encode($data, true)
        );
        return $outputs;
    }
    public function uploadRender($uploaddata, $params){
        global $DB;
        $systemcontext = \context_system::instance();
        $content = $uploaddata['content'];
        $total = $uploaddata['total'];
        $tdata = [];
        foreach($content AS $video){
            $data = array();
            $data['id'] = $video->id;
            $data['title'] = $video->title;
            $data['tagsname'] = $video->tagsname;
            $thumbnaillogourl = $this->get_thumbnail_url($video->thumbnail);
            $data['thumbnail'] = $thumbnaillogourl;
            $data['usercreated'] = $video->usercreated;
            $data['timecreated'] = date('d M Y', $video->timecreated);
            $data['status'] = $video->status == 0 ? 'Not Synced' : 'Synced at '.date('d M Y', $video->uploaded_on);
            $data['delete_enable'] = ($video->status == 0 && (is_siteadmin() || has_capability('block/stream:deletevideo', $systemcontext))) ? true : false;
            $data['edit_enable'] = ($video->status == 0 && (is_siteadmin() || has_capability('block/stream:editvideo', $systemcontext))) ? true : false;
            $tdata[] = $this->render_from_template('block_stream/videocard', $data);
        }
        if(!empty($tdata))
            $tdata = [$this->handleEmptyElements($tdata, $params['length'])];
        $outputs = array(
            "draw" => isset($params['draw']) ? intval($params['draw']) : 1,
            "iTotalRecords" => $total,
            "iTotalDisplayRecords" => $total,
            "data" => json_encode($tdata, true)
        );
        return $outputs;
    }
    public function get_thumbnail_url($logoitemid){
        global $DB;
        $thumbnaillogourl = '';
        if($logoitemid){
            $sql = "SELECT * FROM {files} WHERE itemid = :itemid AND component LIKE 'block_stream' AND filearea LIKE 'thumbnail' AND filename <> '.'";
            $logo = $DB->get_record_sql($sql, array('itemid' => $logoitemid));
            if($logo)
                $thumbnaillogourl = moodle_url::make_pluginfile_url($logo->contextid, $logo->component, $logo->filearea, $logo->itemid, $logo->filepath, $logo->filename);
        }
        if(empty($thumbnaillogourl)){
            $thumbnaillogourl = $this->image_url('video', 'block_stream');
        }
        return $thumbnaillogourl;
    }
    public function handleEmptyElements($data, $count){
        if(count($data) == $count){
            $lists = [];
        }else{
    	   $lists = range(count($data)-1, $count);
        }
    	$returndata = $data;
    	foreach($lists AS $list){
    		$returndata[] = null;
    	}
    	return $returndata;
    }
    public function uploadedVideos(){
        return $this->render_from_template('block_stream/streamVideos', array('tableid' => 'uploaded_videos_data', 'function' => 'uploaded_videos_data', 'nodatastring' => 'novideosuploadedyet'));
    }
    public function streamVideos(){
        return $this->render_from_template('block_stream/streamVideos', array('tableid' => 'get_stream_data', 'function' => 'get_stream_data', 'nodatastring' => 'streamingnotyetset'));
    }
    public function render_block_content(){
    	$stream = new \block_stream\stream();
    	$content = $stream->block_content();
    	return $this->render_from_template('block_stream/blockcontent', $content);
    }
}
