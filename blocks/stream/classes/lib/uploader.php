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
namespace block_stream\lib;
use curl;
use phpstream;
class uploader{
    public function __construct(){
        global $DB;
        $this->db = $DB;
    }
    public function videosSync(){
        global $CFG;
         $videoInfoSql = "SELECT uv.*, f.id as fileid FROM {uploaded_videos} AS uv
            JOIN {files} AS f ON f.itemid = uv.filepath AND f.component = 'block_stream' AND f.filearea = 'video'
            WHERE f.filename != '.' AND uv.status = :status ";
        $videoInfo = $this->db->get_record_sql($videoInfoSql, array('status' => 0));

        if(empty($videoInfo)){
            return;
        }
        $streamobj = new \block_stream\stream();
        $params = $streamobj->streamlib->get_listing_params();

        $search_url = $streamobj->streamlib->api_url."/api/v1/videos/importVideo";
        $c = new curl();//['debug' => true]
        $header[] = "Content-Type: multipart/form-data";
        $c->setHeader($header);
        $c->setopt(array('CURLOPT_HEADER' => false));
        $c->setopt(CURLOPT_VERBOSE, true);
        $files = [];
        // if($videoInfo->thumbnail){
        //     $thumbnailSql = "SELECT f.id FROM {files} AS f WHERE f.filename != '.' AND f.component LIKE 'block_stream' AND f.filearea LIKE 'thumbnail' AND f.itemid = :itemid ";
        //     $thumbnailid = $this->db->get_field_sql($thumbnailSql, array('itemid' => $videoInfo->thumbnail));
        //     $params['thumbnail'] = $this->get_storedfile_object($c, $thumbnailid, 'image');
        // }
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
    public function get_storedfile_object(&$curlobj, $fileid, $filetype){
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
}
