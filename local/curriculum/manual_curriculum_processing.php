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
 * List the tool provided in a course
 *
 * @package    local
 * @subpackage curriculum
 * @copyright  2022 Eabyas Info Solutions <www.eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('AJAX_SCRIPT', true);
require_once(dirname(__FILE__) . '/../../config.php');
global $DB, $PAGE, $USER, $CFG, $OUTPUT;
require_login();
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
require_once($CFG->dirroot.'/local/curriculum/lib.php');
$labelstring = get_config('local_costcenter');
$filterjson = optional_param('param', '',  PARAM_RAW);
$filterdata = json_decode($filterjson);
$department = $filterdata->$secondlevel;
$ssearch = $requestdata['search'];
$systemcontext = context_system::instance();
$selectsql = "SELECT * FROM {local_curriculum} c WHERE 1=1 AND c.program = 0 ";
$countsql  = "SELECT count(c.id) FROM {local_curriculum} c WHERE 1=1 ";

if (!is_siteadmin() && has_capability('local/costcenter:manage_ownorganization', $systemcontext)) {
    $selectsql .= " AND c.costcenter = {$USER->open_costcenterid}";
    $countsql .= " AND c.costcenter = {$USER->open_costcenterid}";
} else if (!is_siteadmin() && has_capability('local/costcenter:manage_owndepartments', $systemcontext)) {
    $selectsql .= " AND c.open_departmentid = {$USER->open_departmentid}";
    $countsql .= " AND c.open_departmentid = {$USER->open_departmentid}";
}
if ( $requestdata['ssearch'] != "" ) {
    $formsql .= " and ((c.fullname LIKE '%".$requestdata['ssearch']."%')
                        or (c.shortname LIKE '%".$requestdata['ssearch']."%')
                        or (co.fullname LIKE '%".$requestdata['ssearch']."%')
                        or (cc.name LIKE '%".$requestdata['ssearch']."%')
                    )";
}
$firstlevel = $labelstring->firstlevel;
$secondlevel = $labelstring->secondlevel;
$thirdlevel = $labelstring->thirdlevel;
if (!empty($filterdata->$firstlevel)) {
    $organizations = implode(',', $filterdata->$firstlevel);
    if (!empty($filterdata->$firstlevel)) {
        $deptquery = array();
        foreach ($filterdata->$firstlevel as $key => $group) {
            $deptquery[] = " FIND_IN_SET($group, c.costcenter) ";
        }
        $groupqueeryparams = implode('OR', $deptquery);
        $formsql .= ' AND ('.$groupqueeryparams.')';
    }
}
if (!empty($filterdata->$secondlevel)) {
    if (!empty($filterdata->$secondlevel)) {
        $deptquery = array();
        foreach ($filterdata->$secondlevel as $key => $group) {
            $deptquery[] = " FIND_IN_SET($group, c.department) ";
        }
        $groupqueeryparams = implode('OR', $deptquery);
        $formsql .= ' AND ('.$groupqueeryparams.')';
    }
}
$totalcourses = $DB->count_records_sql($countsql.$formsql);
if ($totalcourses == 0) {
    $totalcourses = 0;
} else {
    $totalcourses = $totalcourses;
}
$formsql .= " ORDER BY c.id DESC";
$curriculums = $DB->get_records_sql($selectsql.$formsql);
$data = array();
foreach ($curriculums as $curriculum) {
    $row = array();
    $costcentername = $DB->get_field('local_costcenter', 'fullname', array('id' => $curriculum->costcenter));
    if (strlen($curriculum->name) >= 20) {
        $curriculumname = substr($curriculum->name, 0, 17).'...';
    } else {
        $curriculumname = $curriculum->name;
    }
    $curriculumsurl = $CFG->wwwroot.'/local/curriculum/view.php?ccid='.$curriculum->id.'&type=1';
    $row[] = '<a href ="'.$curriculumsurl.'" alt = "' . get_string('view') . '"
                title = "' . $curriculum->name . '"
                target = "_blank"> '.$curriculumname.'
            </a>';
    $row[] = $costcentername;
    $programscount = $DB->count_records("local_program", array('curriculumid' => $curriculum->id));
    $action = html_writer::link('javascript:void(0)',
                                $OUTPUT->pix_icon('t/edit', get_string('edit'), 'moodle',
                                    array('')),
                                array('title' => get_string('edit'),
                                        'alt' => get_string('edit'),
                                        'data-value' => $advisor->id,
                                        'onclick' => '(function(e){
                                            require("local_curriculum/ajaxforms")
                                            .init({contextid:1, component:"local_curriculum",
                                            callback:"curriculum_form", form_status:0,
                                            plugintype: "local", pluginname: "curriculum",
                                            id: '.$curriculum->id.'})
                                        })(event)'
                                    )
                            );
    if ($programscount > 0) {
        $action .= html_writer::link('javascript:void(0)',
                                        $OUTPUT->pix_icon('t/delete', get_string('delete'),
                                                            'moodle', array('')
                                                ),
                                        array('title' => get_string('delete'),
                                                'id' => $curriculum->id,
                                                'onclick' => '(function(e){
                                                    require(\'local_curriculum/ajaxforms\')
                                                    .deleteConfirm({
                                                        action:\'cannotdeletecurriculum\',
                                                        curriculumname: \''.$curriculum->name.'\',
                                                        id: '.$curriculum->id.'
                                                    })
                                                })(event)'
                                            )
                                    );
    } else {
        $action .= html_writer::link('javascript:void(0)',
                                        $OUTPUT->pix_icon('t/delete', get_string('delete'),
                                            'moodle', array('')
                                        ), array('title' => get_string('delete'),
                                                    'id' => $curriculum->id,
                                                    'onclick' => '(function(e){
                                                        require(\'local_curriculum/ajaxforms\')
                                                        .deleteConfirm({
                                                            action:\'deletecurriculum\',
                                                            curriculumname: \''.$curriculum->name.'\',
                                                            id: '.$curriculum->id.'
                                                        })
                                                    })(event)'
                                                )
                                    );
    }
    if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext) 
        || has_capability('local/costcenter:manage_ownorganization', $systemcontext) 
        || has_capability('local/costcenter:manage_owndepartments', $systemcontext)){
        $row[] = html_writer::div($action,'action_btns_container d-flex align-items-center');
    } else{
        $row[] = html_writer::div('','action_btns_container d-flex align-items-center');
    }
    $data[] = $row;
}

$itotal = $totalcourses;
$ifilteredtotal = $itotal;
$output = array(
    "sEcho" => intval($requestdata['sEcho']),
    "iTotalRecords" => $itotal,
    "iTotalDisplayRecords" => $ifilteredtotal,
    "aaData" => $data
);
echo json_encode($output);
