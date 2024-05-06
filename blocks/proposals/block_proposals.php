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
 * Version details.
 *
 * @package    block_proposals
 * @copyright  moodle
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
class block_proposals extends block_base {
    public function init() {
        $this->title = get_string('pluginname', 'block_proposals');
    }
    public function get_required_javascript() {
        $this->page->requires->css('/blocks/proposals/css/cards.css');
    }
    public function get_content() {

        global $DB , $OUTPUT , $CFG , $USER;
         if ($this->content !== null) {
             return $this->content;

        }

        $systemcontext = context_system::instance();
        if(!has_capability('block/proposals:dashboardview',$systemcontext)) {

             return '';
        }   
        if(is_siteadmin()){
            return '';
        }
        $url = $CFG->wwwroot.'/blocks/proposals/view.php';

        $formurl = $CFG->wwwroot.'/blocks/proposals/register.php';
        $image1url = $CFG->wwwroot.'/blocks/proposals/images/1.png';
        $image2url = $CFG->wwwroot.'/blocks/proposals/images/2.png';
        $image3url = $CFG->wwwroot.'/blocks/proposals/images/3.png';
        $image4url = $CFG->wwwroot.'/blocks/proposals/images/4.png';


$submissionscount = $DB->count_records_sql("SELECT COUNT(DISTINCT(s.id)) FROM {submissions} as s WHERE s.userid = $USER->id AND s.status = 0 OR s.status =1 AND (s.approveronestatus = 0 OR s.approveronestatus=1) AND s.approvertwostatus = 0");
 
$approvalscount = $DB->count_records_sql("SELECT COUNT(DISTINCT(s.id))
                                            FROM {submissions} s
                                            WHERE s.status = 1
                                            AND s.approveronestatus =1 AND s.approvertwostatus=1 AND s.userid = $USER->id");

$rejectedcount = $DB->count_records_sql("SELECT COUNT(DISTINCT(s.id))
                                            FROM {submissions} s
                                            WHERE (s.status = 2
                                            OR s.approveronestatus = 2 OR s.approvertwostatus=2)
                                            AND s.userid = $USER->id");

$revisedcount = $DB->count_records_sql("SELECT COUNT(DISTINCT(s.id))
                                        FROM {submissions} as s
                                        WHERE s.status = 3 AND s.userid = $USER->id");
$templatecontext = (object)[
        'total' => $submissionscount,
        'approved' => $approvalscount,
        'rejected' => $rejectedcount,
        'revised' => $revisedcount,
        'url' => $url,

        'formurl' => $formurl,
        'image1url' => $image1url,
        'image2url' => $image2url,
        'image3url' => $image3url,
        'image4url' => $image4url
        ];


    $this->content = new stdClass();
    
       
    $this->content->text = $OUTPUT->render_from_template('block_proposals/index', $templatecontext);

    $this->content->footer = '';

    return $this->content;

    }
}
