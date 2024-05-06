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
 * @package Bizlms
 * @subpackage stream
 */
namespace mod_stream\output;

use mod_stream\local\filters as filters;
use html_writer;
use user_course_details;
use html_table;
use moodle_url;

require_once($CFG->dirroot.'/mod/stream/locallib.php');

class renderer extends \plugin_renderer_base {

  /**
   * Defer to template.
   *
   * @param coursestats $page 
   *
   * @return string html for the page
   */
    public function render_player($page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('mod_stream/player', $data);                                                         
    }  

    public function print_reports($courseid, $activityid) {
        global $PAGE, $OUTPUT, $CFG, $DB;
        $reports = stream_reports($courseid, $activityid);
        $filter = new filters();
        $reports['courses'] = $filter->filter_get_courses($courseid);
        $PAGE->requires->data_for_js('stream_reports_data', $reports);
        if($activities = $filter->filter_get_activities($courseid, $activityid)){
            $reports['activities'] = $activities;
        }
        
        $reports['courseid'] = $courseid;
        if($courseid == SITEID){
          $reports['sitelevel'] = true;
        }
        $reports['activitylevel'] = $activityid;
        $reports['activityid'] = $activityid;
        $reports['data'][] = ['name' => 'toplikes',
                              'title' => get_string('toplikes', 'mod_stream'),
                              'icon' => 'fa-thumbs-up',
                              'tabledata' => $reports['toplikes'],
                              'active' => 'active' ];
        $reports['data'][] = ['name' => 'topratings',
                              'title' => get_string('topratings', 'mod_stream'),
                              'icon' => 'fa-star-half-o',
                              'tabledata' => $reports['topratings'] ];
        $reports['data'][] = ['name' => 'topviews',
                              'title' => get_string('topviews', 'mod_stream'),
                              'icon' => 'fa-eye',
                              'tabledata' => $reports['topviews'] ];
  

        $return =  $this->render_from_template('mod_stream/topReports', $reports);
        
        return $return;
    }
    public function uploadedVideos(){
        return $this->render_from_template('mod_stream/streamVideos', array('tableid' => 'uploaded_videos_data', 'function' => 'uploaded_videos_data'));
    }
    public function streamVideos(){
        return $this->render_from_template('mod_stream/streamVideos', array('tableid' => 'get_stream_data', 'function' => 'get_stream_data'));
    }
    public function viewreports($viewsinfo, $params) {
      $views = $viewsinfo['views'];
        $data =array();
        foreach($views as $view) {
          $row = array();
          $row[] = $view->fullname;
          $row[] = html_writer::link(new moodle_url('/mod/stream/view.php?id='.$view->cmid), $view->stream);
          $row[] = html_writer::tag('span', '<i class="fa fa-eye" aria-hidden="true"></i> ' . $view->attempts, array( 'class' => 'drilldownreportpopup', 'data-id' =>  $view->cmid, 'data-userid' => $view->userid, 'data-function' => 'views', 'data-email' => $view->email, 'data-reportname' => get_string('noofviewsbyuser', 'stream') ));
          if($view->percentage == 100)
              $row[] = html_writer::tag('span', get_string('completed', 'stream'), array('class'=>'badge badge-success') );
          elseif($view->percentage == 0)
              $row[] = html_writer::tag('span', get_string('notyetstarted', 'stream'), array('class'=>'badge badge-danger') );
          else
              $row[] = html_writer::tag('span', get_string('inprogress', 'stream'), array('class'=>'badge badge-warning') );
              $row[] = date('jS F Y', $view->timecreated);
              $row[] = date('jS F Y', $view->timecreated);
              $data[] = $row;
        }
        $iTotal = $viewsinfo['viewscount'];  
        $outputs = array(
                "draw" => isset($params['draw']) ? intval($params['draw'])+1 : 1,
                "iTotalRecords" => $iTotal,
                "iTotalDisplayRecords" => $iTotal,
                "data" => json_encode($data, true)
        );
        return $outputs;
    }
    public function courseinfo($coursesinfo, $params) {
        $courses = $coursesinfo['courses'];
        $data =array();
        foreach($courses as $course) {
          $row = array();
          $row[] = $course->fullname;
          $row[] = html_writer::tag('span', $course->totalvideos , array('class' => 'drilldownreportpopup', 'data-id' => $course->course, 'data-function' => 'totalcoursestats', 'data-reportname' => get_string('totalvideos', 'stream') ));
          $row[] = html_writer::tag('span', $course->activevideos, array( 'class' => 'drilldownreportpopup', 'data-id' => $course->course, 'data-function' => 'activecoursestats', 'data-reportname' => get_string('activevideos', 'stream') ) );
          $data[] = $row;
        }
        $iTotal = $coursesinfo['coursescount'];
        $outputs = array(
            "draw" => isset($params['draw']) ? intval($params['draw'])+1 : 1,
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iTotal,
            "data" => json_encode($data, true)
        );
        return $outputs;
    }
    public function summaryinfo($summaryinfo, $params) {
        $summarydata = $summaryinfo['summary'];
        $data =array();
        foreach($summarydata as $summary) {
          $row = array();
          $minutes = $summary->averagetime;

          $duration = gmdate("H:i:s", round($summary->duration));

          $likes = $summary->likes == '' ? 0 : $summary->likes;
          $link = html_writer::link(new moodle_url('/mod/stream/view.php?id='.$summary->cmid), '<i class="fa fa-file-video-o" aria-hidden="true"></i> ' . $summary->streamname); 
          $row[] = html_writer::tag('div', $link . '<p class="vduration"><small><i class="fa fa-clock-o" aria-hidden="true"></i> '. $duration .'</small></p>');
          $row[] = html_writer::link(new moodle_url('/course/view.php?id='.$summary->courseid), $summary->coursename);
          $row[] = html_writer::tag('span', '<i class="fa fa-eye" aria-hidden="true"></i> '. $summary->attempts, array( 'class' => 'drilldownreportpopup', 'data-id' => $summary->cmid, 'data-function' => 'topviews', 'data-reportname' => get_string('views', 'stream'), 'style' => "cursor:pointer" ) );
          $row[] = html_writer::tag('span', '<i class="fa fa-thumbs-up" aria-hidden="true"></i> ' . $likes, array( 'class' => 'drilldownreportpopup', 'data-id' => $summary->cmid, 'data-function' => 'toplikes', 'data-reportname' => get_string('likes', 'stream') ) );
          $row[] = html_writer::tag('span', ' <div class="rating-1" data-stars='.$summary->rating.'>('.$summary->rating.')</div>', 
                   array( 'class' => 'drilldownreportpopup', 'data-id' => $summary->cmid, 'data-function' => 'toprating', 'data-reportname' => get_string('rating', 'stream')) );
          $row[] = '<i class="fa fa-clock-o" aria-hidden="true"></i> ' . gmdate("H:i:s", ($minutes * 60));
          $row[] = date('jS F, Y', $summary->timecreated);
          $row[] = $summary->creatorname;
          $data[] = $row;
        }
        $iTotal = $summaryinfo['summarycount'];
        $outputs = array(
                "iTotalRecords" => $iTotal,
                "iTotalDisplayRecords" => $iTotal,
                "data" => json_encode($data, true)
            );
        return $outputs;
    }
    public function viewsinfo($viewsdata, $params) {
        $viewsinfo = $viewsdata['views'];
        $data =array();
        foreach($viewsinfo as $viewinfo) {
          $row = array();
          $row[] = $viewinfo->fullname;
          $row[] = $viewinfo->attempts;
          $row[] = date('jS F Y', $viewinfo->timecreated);
          $data[] = $row;
        }
        $iTotal = $viewsdata['viewscount'];
        $outputs = array(
                "draw" => isset($params['draw']) ? intval($params['draw'])+1 : 1,
                "iTotalRecords" => $iTotal,
                "iTotalDisplayRecords" => $iTotal,
                "data" => json_encode($data, true)
            );
        return $outputs;
    }
    public function activityinfo($activitiesinfo, $params) {
        $activitiesstatus = $activitiesinfo['activities'];
        $data =array();
        foreach ($activitiesstatus as $activitystatus) {
            $activitystatus->timecreated = date('jS F Y', $activitystatus->timecreated);
            $activitystatus->timemodified = date('jS F Y', $activitystatus->timemodified);
            if($activitystatus->status == '') {
              $activitystatus->status = get_string('notyetstarted', 'stream');
              $activitystatus->attemptval = 0;
              $activitystatus->timecreated = '---';
              $activitystatus->timemodified = '---';
            } else if($activitystatus->status == 100){
              $activitystatus->status = get_string('completed', 'stream');
            } else {
              $activitystatus->status = get_string('inprogress', 'stream');
              $activitystatus->timemodified = '---';
            }
          $row = array();
            $row[] = $activitystatus->fullname; 
            $row[] = $activitystatus->email;
            $row[] = html_writer::tag('span', '<i class="fa fa-eye" aria-hidden="true"></i> '. $activitystatus->attemptval, array( 'class' => 'drilldownreportpopup', 'data-id' => $id, 'data-function' => 'views', 'data-reportname' => get_string('views', 'stream'), 'data-email' => $activitystatus->email, 'data-cmid' => $activitystatus->cmid, 'data-userid' => $activitystatus->id ) );
            $row[] = $activitystatus->status;
            $row[] = $activitystatus->timecreated;
            $row[] = $activitystatus->timemodified;
            $data[] = $row;
        }
        $iTotal = $activitiesinfo['activitycount'];
        $outputs = array(
                "draw" => isset($params['draw']) ? intval($params['draw'])+1 : 1,
                "iTotalRecords" => $iTotal,
                "iTotalDisplayRecords" => $iTotal,
                "data" => json_encode($data)
            );
        return $outputs;
    }
    public function streamRender($streaminfo, $params){
        $content = $streaminfo['returndata'];
        $total = $streaminfo['total'];
        $data = [];
        foreach($content as $stream){
            $data[] = [$this->render_from_template('mod_stream/videocard', $stream)];
        }
        $outputs = array(
            "draw" => isset($params['draw']) ? intval($params['draw'])+1 : 1,
            "iTotalRecords" => $total,
            "iTotalDisplayRecords" => $total,
            "data" => json_encode($data, true)
        );
        return $outputs;
    }
    public function uploadRender($uploaddata, $params){
        global $DB;
        $content = $uploaddata['content'];
        $total = $uploaddata['total'];
        $tdata = [];
        foreach($content AS $video){
            $data = array();
            $data['title'] = $video->title;
            $data['tagsname'] = $video->tagsname;
            $thumbnaillogourl = '';
            if($video->thumbnail){
              $sql = "SELECT * FROM {files} WHERE itemid = {$video->thumbnail} AND component LIKE 'mod_stream' AND filearea LIKE 'thumbnail' AND filename <> '.'";
              $thumbnaillogo = $DB->get_record_sql($sql);
              if($thumbnaillogo)
              $thumbnaillogourl = moodle_url::make_pluginfile_url($thumbnaillogo->contextid, $thumbnaillogo->component, $thumbnaillogo->filearea, $thumbnaillogo->itemid, $thumbnaillogo->filepath, $thumbnaillogo->filename);
            }
            if(empty($thumbnaillogourl)){
              $thumbnaillogourl = $this->image_url('video', 'mod_stream');
            }
            $data['thumbnail'] = html_writer::tag('img', '', array("src"=>$thumbnaillogourl, 'height'=>'100px', 'width'=> '100px'));
            $data['username'] = $video->userfullname;
            $data['timecreated'] = date('d M Y', $video->timecreated);
            $data['status'] = $video->status == 0 ? get_string('notsynced', 'stream') : get_string('syncedat', 'stream').date('d M Y', $video->uploaded_on);
            $tdata[] = [$this->render_from_template('mod_stream/videocard', $data)];
        }
        $outputs = array(
            "draw" => isset($params['draw']) ? intval($params['draw'])+1 : 1,
            "iTotalRecords" => $total,
            "iTotalDisplayRecords" => $total,
            "data" => json_encode($tdata, true)
        );
        return $outputs;
    }
}
