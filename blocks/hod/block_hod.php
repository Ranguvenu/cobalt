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
 * @package    block_my_induction
 * @copyright  moodle
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . "/externallib.php");
require_once( $CFG->libdir . '/filelib.php' );

class block_hod extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_hod');
    }

    public function get_required_javascript() {

        $this->page->requires->css('/blocks/hod/css/index.css');
       // $this->page->requires->css('/blocks/hod/css/bootstrap.css');
        //$this->page->requires->css('/blocks/hod/css/bootstrap.min.css');
    }

    public function get_content() {
        if (!has_capability('block/hod:dashboard', $this->context)) {
            return null;
        }
        global $DB , $OUTPUT,$USER, $CFG;
        if ($this->content !== null) {
            return $this->content;
        }

        $url = $CFG->wwwroot.'/blocks/hod/view.php';
        $image1url = $CFG->wwwroot.'/blocks/hod/images/1.png';
        $image2url = $CFG->wwwroot.'/blocks/hod/images/2.png';
        $image3url = $CFG->wwwroot.'/blocks/hod/images/3.png';
        $image4url = $CFG->wwwroot.'/blocks/hod/images/4.png';
        // $submissions = $DB->count_records_sql("SELECT COUNT(DISTINCT(s.id))
        //                                         FROM {submissions} s
        //                                         WHERE s.status = 0 AND (draft = 'f1' OR draft ='n1')");
        $details = $DB->get_record('user',array('id'=>$USER->id));
       

        if($details->levelofapprove == 0){
        $submissions = $DB->count_records_sql("SELECT COUNT(s.id) FROM {submissions} AS s JOIN {user} AS u ON s.departmentname = u.deptid
     WHERE u.id = $USER->id AND status = 0 AND (draft = 'f1' OR draft ='n1' OR draft ='nres1')");


        $approvals = $DB->count_records_sql("SELECT COUNT(s.id) FROM {submissions} AS s JOIN {user} AS u ON s.departmentname = u.deptid
     WHERE u.id = $USER->id AND status = 1");

        $rejected = $DB->count_records_sql("SELECT COUNT(s.id) FROM {submissions} AS s JOIN {user} AS u ON s.departmentname = u.deptid
     WHERE u.id = $USER->id AND status = 2");

        $revised = $DB->count_records_sql("SELECT COUNT(s.id) FROM {submissions} AS s JOIN {user} AS u ON s.departmentname = u.deptid
     WHERE u.id = $USER->id AND status = 3"); 
        
        // $approvals = $DB->count_records('submissions' ,array('status' => 1));
        // $rejected = $DB->count_records('submissions' , array('status' => 2));
        // $revised = $DB->count_records('submissions' , array('status' => 3));
        $templatecontext = (object)[
            'total' => $submissions,
            'approved' => $approvals,
            'rejected' => $rejected,
            'revised' => $revised,
            'url' => $url,
            'image1url' => $image1url,
            'image2url' => $image2url,
            'image3url' => $image3url,
            'image4url' => $image4url
        ];
        $this->content = new stdClass();

        $this->content->text = $OUTPUT->render_from_template('block_hod/blocks', $templatecontext);
        
        $this->content->footer = '';
    }

    else if($details->levelofapprove == 1){

$submissions = $DB->count_records_sql("SELECT COUNT(id) FROM {submissions}  WHERE status = 1 AND approveronestatus = 0");

$approvals = $DB->count_records_sql("SELECT COUNT(id) FROM {submissions}  WHERE status = 1 AND approveronestatus = 1");

$rejected = $DB->count_records_sql("SELECT COUNT(id) FROM {submissions}  WHERE status = 1 AND approveronestatus = 2");


        $templatecontext = (object)[
            'total' => $submissions,
            'approved' => $approvals,
            'rejected' => $rejected,
            // 'revised' => $revised,
            'url' => $url,
            'image1url' => $image1url,
            'image2url' => $image2url,
            'image3url' => $image3url,
            'image4url' => $image4url
        ];
        $this->content = new stdClass();

        $this->content->text = $OUTPUT->render_from_template('block_hod/blockapproverone', $templatecontext);
        
        $this->content->footer = '';
    }

       else if($details->levelofapprove == 2){

$submissions = $DB->count_records_sql("SELECT COUNT(id) FROM {submissions}  WHERE status = 1 AND approveronestatus = 1 AND approvertwostatus = 0");

$approvals = $DB->count_records_sql("SELECT COUNT(id) FROM {submissions}  WHERE status = 1 AND approveronestatus = 1 AND approvertwostatus =1");

$rejected = $DB->count_records_sql("SELECT COUNT(id) FROM {submissions}  WHERE status = 1 AND approvertwostatus = 2");

        $templatecontext = (object)[
            'total' => $submissions,
            'approved' => $approvals,
            'rejected' => $rejected,
            // 'revised' => $revised,
            'url' => $url,
            'image1url' => $image1url,
            'image2url' => $image2url,
            // 'image3url' => $image3url,
            'image4url' => $image4url
        ];
        $this->content = new stdClass();

        $this->content->text = $OUTPUT->render_from_template('block_hod/blockapprovertwo', $templatecontext);
        
        $this->content->footer = '';
    }


        
    }
}
