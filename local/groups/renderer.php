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
 * Version information
 *
 * @package    local_groups
 * @copyright  2022 eAbyas Info Solutions Pvt. Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class local_groups_renderer extends plugin_renderer_base {

    public function managegroups_content($filter = false) {
        global $USER;
        $systemcontext = context_system::instance();
        $stable = new stdClass();
        $stable->thead = true;
        $stable->start = 0;
        $stable->length = -1;
        $stable->search = '';
        $stable->pagetype = get_string('renderepagetype', 'local_groups');
        $options = array(
            'targetID' => 'manage_groups',
            'perPage' => 8,
            'cardClass' => 'col-md-6 col-lg-4 col-xl-3 col-sm-6 col-12 card_main',
            'viewType' => 'table'
        );
        $options['methodName'] = 'local_groups_managegroups_view';
        $options['templateName'] = 'local_groups/groupstab';
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('userid' => $USER->id, 'contextid' => $systemcontext->id));
        $context = [
            'targetID' => 'manage_groups',
            'options' => $options,
            'dataoptions' => $dataoptions,
            'filterdata' => $filterdata
        ];

        if ($filter) {
            return $context;
        } else {
            return $this->render_from_template('local_costcenter/cardPaginate', $context);        }
    }

    public function get_group_btns() {
        global $USER, $DB;
        $systemcontext = context_system::instance();
        if ((is_siteadmin() || has_capability('moodle/cohort:manage', $systemcontext))
                && $this->page->pagetype == 'local-groups-index') {
            $createdeptpopup = "<a class='course_extended_menu_itemlink'
                                    data-action='createcostcentermodal' data-value='0'
                                    title = '".get_string('create_group', 'local_groups')."'
                                    onclick ='(function(e){
                                        require(\"local_groups/newgroup\").
                                        init({contextid:$systemcontext->id, groupsid:0}) })(event)' >
                                        <span class='createicon'>
                                        <i class='icon  fa fa-users user_icon' aria-hidden='true'></i>
                                        <i class='createiconchild fa fa-plus'
                                            aria-hidden='true'></i>
                                        </span>
                                </a>";
        } else {
            $createdeptpopup = '';
        }
        $buttons = [
            "createdeptpopup" => $createdeptpopup
        ];

        return $this->render_from_template('local_groups/viewbuttons', $buttons);
    }


    public function usersemdetails($semid, $rolehideid) {
        global $DB, $PAGE,$USER,$CFG,$OUTPUT;
        $roleid = $rolehideid;
        $levelid = $semid;

        $programid = $DB->get_field('local_program_users', 'programid', array('userid' => $roleid));

        $levelid = $DB->get_field('local_program_levels', 'id', array('programid' => $programid));

        $systemcontext = context_system::instance();

        $selectsql = "SELECT c.fullname AS course, bclc.courseid AS programcourseid, bclc.programid, cc.coursetype as ctype,
                        bclc.levelid, lpl.level AS levelname,
                        (
                            SELECT COUNT(*) FROM {course_modules} cm
                                WHERE cm.course = bclc.courseid
                        ) AS total_modules,
                        (
                                    SELECT COUNT(cmc.id) FROM {course_modules_completion} cmc
                                    LEFT JOIN {course_modules} cm ON cmc.coursemoduleid = cm.id
                                    WHERE cm.course = bclc.courseid and  cmc.userid = u.id
                        ) AS modules_completed,
                        (
                            ROUND( 100 / (SELECT COUNT(*) FROM {course_modules} cm WHERE cm.course = bclc.courseid) ) *
                            (SELECT COUNT(cmc.id) FROM {course_modules_completion} cmc
                            LEFT JOIN {course_modules} cm ON cmc.coursemoduleid = cm.id
                            WHERE  cm.course = bclc.courseid and cmc.userid = u.id)
                        ) AS course_progress

                        FROM {local_program_level_courses} bclc
                        JOIN {user} u
                        JOIN {user_enrolments} ue ON ue.userid=u.id
                        JOIN {enrol} e ON e.id=ue.enrolid
                        JOIN {course} c ON c.id = e.courseid and c.id = bclc.courseid
                        JOIN {local_cc_semester_courses} cc ON cc.open_parentcourseid =  bclc.parentid
                       JOIN {local_program_levels} lpl ON lpl.id = bclc.levelid and lpl.programid = bclc.programid WHERE u.id =  $roleid and lpl.id = $levelid";

        $queryparam = array();
        $semester_info = $DB->get_records_sql($selectsql, $queryparam, $tablelimits->start, $tablelimits->length);

        $list=array();
        $data = array();

        if ($semester_info) {
            foreach ($semester_info as $sem_detail) {
                $list['id'] = $sem_detail->programcourseid;
                $list['fullname'] = $sem_detail->course;
                $list['semester'] = $sem_detail->levelname;
                $list['roleid'] = $programid;

                if ($sem_detail->ctype == 0) {
                    $sem_detail->ctype = 'Elective';
                } else {
                    $sem_detail->ctype = 'Core';
                }
                
                $list['ctype'] = $sem_detail->ctype;
                $list['per'] = $sem_detail->course_progress;
                $list['per'] = $sem_detail->course_progress;

                $templatedata['rowdata'][] = $list;
            }
        }
        $output = $OUTPUT->render_from_template('local_groups/popupprogresscontent', $templatedata);

        return $output;
    }
}
