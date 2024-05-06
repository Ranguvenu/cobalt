<?php

namespace block_stream\output;

defined('MOODLE_INTERNAL') || die;

use plugin_renderer_base;

class renderer  extends plugin_renderer_base {
    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @return stdClass
     */
    public function render_uploadedvideos(uploadedvideos $output) {

        return $this->render_from_template('block_stream/list', $output->export_for_template($this));
        
    }

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