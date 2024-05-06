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

    protected function render_local_groups(local_groups $renderable) {
        return $this->show($renderable->context, $renderable->groups, $renderable->showall,
                            $renderable->searchquery, $renderable->page
                        );
    }

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
            'perPage' => 6,
            'cardClass' => 'col-md-4 col-sm-6 col-12 card_main',
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
            return $this->render_from_template('local_costcenter/cardPaginate', $context);
        }               
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
                                        <i class='fa fa-users icon' aria-hidden='true'></i>
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

    public function show($context, $groups, $showall, $searchquery, $page) {


        global $DB, $CFG, $USER;
        $output = '';
        $data = array();
        $systemcontext = context_system::instance();
        $editcolumnisempty = true;
        $params = array('page' => $page);
        if ($context->id) {
            $params['contextid'] = $context->id;
        }
        if ($searchquery) {
            $params['search'] = $searchquery->search_query;
        }
        if ($showall) {
            $params['showall'] = true;
        }
        $baseurl = new moodle_url('/local/groups/index.php', $params);
        $cohorts = $groups['groups'];
        $row = [];

        foreach ($cohorts as $cohort) {
            $line = array();
            $groupname = $cohort->name;
            $groupid  = $cohort->idnumber;
            if (strlen($groupid) > 8) {
                 $groupid = substr($groupid, 0, 8).'...';
            }
            $cohortcontext = context::instance_by_id($cohort->contextid);
            $urlparams = array('id' => $cohort->id, 'returnurl' => $baseurl->out_as_local_url());
            $programname = $DB->get_field('local_program', 'name', array('batchid' => $cohort->id));

  if (strlen($programname)>15){
            $programname = substr($programname, 0, 15)."...";
        }
        $pid = $DB->get_field('local_program', 'id', array('batchid' => $cohort->id));

      $departmentid = $DB->get_field('local_groups', 'departmentid', array('cohortid' => $cohort->id));
      $dpname = $DB->get_field('local_costcenter', 'fullname', array('id' => $departmentid));
      $deptname = $dpname ? $dpname : 'N/A';

                 if (strlen($deptname)>13){
            $deptname = substr($deptname, 0, 13)."...";
        }

            $costcenterid = $DB->get_field('local_groups', 'costcenterid', array('cohortid' => $cohort->id));

        $duration = $DB->get_field('local_program', 'duration', array('id' => $pid));
 
        $costfullname= $DB->get_field('local_costcenter', 'fullname', array('id' => $costcenterid));

 if (strlen($costfullname)>15){
            $costfullname = substr($costfullname, 0, 15)."...";
        }
          if(!$pid){
            $pid= 0;
          }

          $stdate = $DB->get_records_sql("SELECT date(from_unixtime(startdate)) AS startddate FROM {local_program_levels} WHERE programid = $pid  ORDER BY id ASC LIMIT 1");

         foreach($stdate as $a){
            $startdata = $a->startddate;
         }
        
       if(!$stdate){
            $stdate = 'N/A';
        }
        if($startdata == '1970-01-01'){
            $stdate = 'N/A';
        }
        else{
            $stdate = $startdata;
        }

        $du = $duration;
        $date = $startdata;

        if($date == '1970-01-01'){
            $date = '';
        }
 
        if($date){
          $date = strtotime($date);
          $new_date = strtotime('+ '.$du.' year', $date);
          $enddate =  date('Y-m-d', $new_date);
        }else{
            $enddate = 'N/A';
        }
 
            $line['groupname'] = $groupname;
            $line['groupid'] = $groupid;
            $line['visible'] = $cohort->visible ? 'active' : 'inactive';
            if ($programname) {
                 $line['programname'] = $programname;
            } else {
                 $line['programname'] = 'N/A';
            }

            $buttons = array();
            if (empty($cohort->component)) {
                $cohortmanager = has_capability('moodle/cohort:manage', $cohortcontext);
                $cohortcanassign = has_capability('moodle/cohort:assign', $cohortcontext);
                $showhideurl = new moodle_url('/local/groups/edit.php', $urlparams + array('sesskey' => sesskey()));
                if ($cohortmanager) {
                    $buttons[] = html_writer::start_tag('li');
                    $buttons[] = html_writer::link('javascript:void(0)',
                                                    $this->output->pix_icon('t/editinline',
                                                    get_string('edit')),
                                                    array('title' => get_string('edit'),
                                                        'onclick' => '(function(e){
                                                         require("local_groups/newgroup").
                                                         init({
                                                            contextid:'.$systemcontext->id.',
                                                            groupsid:'.$cohort->id.'
                                                        }) })(event)'
                                                    )
                                                );
                    $buttons[] = html_writer::end_tag('li');
                    $editcolumnisempty = false;
                    if ($cohort->visible) {
                        $buttons[] = html_writer::start_tag('li');
                            $buttons[] = html_writer::link('javascript:void(0)',
                        $this->output->pix_icon('t/hide', get_string('inactive')),
                        array('id' => 'hideconfirm' . $cohort->id . '', 'onclick' => '(
                                      function(e){
                        require("local_groups/renderselections").hidecohort(' . $cohort->id . ', "' . $cohort->name . '")
                        })(event)'));
                        $buttons[] = html_writer::end_tag('li');
                    } else {
                        $buttons[] = html_writer::start_tag('li');
                           $buttons[] = html_writer::link('javascript:void(0)',
                        $this->output->pix_icon('t/show', get_string('active')),
                        array('id' => 'unhideconfirm' . $cohort->id . '', 'onclick' => '(
                                      function(e){
                        require("local_groups/renderselections").unhidecohort(' . $cohort->id . ', "' . $cohort->name . '")
                        })(event)'));
                        $buttons[] = html_writer::end_tag('li');
                    }
                }

                if ($cohortcanassign) {
                    $buttons[] = html_writer::start_tag('li');
                    if ($programid = $DB->record_exists('local_program', array('batchid' => $cohort->id))) {
                        $buttons[] = html_writer::link(
                                                        new moodle_url('/local/groups/assign.php', $urlparams),
                                                        $this->output->pix_icon('i/enrolusers',
                                                        get_string('assign', 'core_cohort')),
                                                        array('title' => get_string('assign',
                                                            'core_cohort')
                                                        )
                                                    );
                        $editcolumnisempty = false;
                        $buttons[] = html_writer::end_tag('li');
                    } else {
                        $buttons[] = html_writer::link(
                                                        new moodle_url('/local/groups/assign.php', $urlparams),
                                                        $this->output->pix_icon('i/enrolusers',
                                                        get_string('assign', 'core_cohort')),
                                                        array('title' => get_string('assign',
                                                            'core_cohort')
                                                        )
                                                    );
                    }
                    $buttons[] = html_writer::start_tag('li');
                    if ($programid = $DB->record_exists('local_program', array('batchid' => $cohort->id))) {
                        $buttons[] = html_writer::link(
                                                        new moodle_url('/local/groups/mass_enroll.php', $urlparams),
                                                        $this->output->pix_icon('i/users', get_string(
                                                            'bulk_enroll', 'local_groups')),
                                                        array('title' => get_string('bulk_enroll', 'local_groups')
                                                        )
                                                    );

                        $buttons[] = html_writer::end_tag('li');
                    } else {
                        $buttons[] = html_writer::link(
                                                        new moodle_url('/local/groups/mass_enroll.php', $urlparams),
                                                        $this->output->pix_icon('i/users', get_string(
                                                            'bulk_enroll', 'local_groups')),
                                                        array('title' => get_string('bulk_enroll', 'local_groups')
                                                        )
                                                    );
                    }
                }
                if ($cohortmanager) {
                    $buttons[] = html_writer::start_tag('li');
                    $programexist = $DB->get_records_sql("SELECT c.id, c.name
                                                            FROM {cohort} c
                                                            JOIN {local_program} p ON c.id = p.batchid
                                                           WHERE c.id = {$cohort->id}");
                    if (!$programexist) {
                        $buttons[] = html_writer::link("javascript:void(0)",
                                    $this->output->pix_icon('i/delete', get_string('delete'),
                                        'moodle', array('title' => '')),
                                    array('id' => 'deleteconfirm' . $cohort->id . '',
                                        'onclick' => '(function(e){
                                            require("local_groups/renderselections").
                                            deletecohort('.$cohort->id.',
                                                            "'.$cohort->name.'"
                                                        ) })(event)'
                                        )
                                );
                    } else if ($programexist) {
                        $buttons[] = html_writer::link("javascript:void(0)",
                                        $this->output->pix_icon('i/delete', get_string('delete'),
                                            'moodle', array('title' => '')),
                                        array('id' => 'notdeleteconfirm' . $cohort->id . '',
                                            'onclick' => '(function(e){
                                                require("local_groups/renderselections").notdeletecohort
                                                ('.$cohort->id.', "'.$cohort->name.'")         })(event)'
                                            )
                                    );
                    }
                }
            }
            $buttons[] = html_writer::end_tag('li');
            $line['actions'] = implode(' ', $buttons);
            if (!$cohort->visible) {
                $row->attributes['class'] = 'dimmed_text';
            }
            $cohortusers = $DB->count_records('cohort_members', array('cohortid' => $cohort->id));

            $line['groupcount'] = $cohortusers;
            $programid = $DB->get_field('local_program', 'id', array('batchid' => $cohort->id));
            if ($programid) {
                $line['user_url'] = $CFG->wwwroot . '/local/groups/users.php?bcid='.$programid;
            } else {
                $line['user_url'] = $CFG->wwwroot . '/local/groups/emptyusers.php?cid='.$cohort->id;
            }
            $line['userid'] = $cohort->id;
            $line['batchid'] = $cohort->id;
            $line['roleid'] = $programid;
            $line['startdate'] = $stdate;
             $line['enddate'] = $enddate;
            $line['costfullname'] = $costfullname;
            $line['deptname'] = $deptname;
            $line['role_count'] = 1;
            $line['location_url'] = $CFG->wwwroot . '/local/groups/assign.php?id='.$cohort->id;
            if (has_capability('local/groups:manage', context_system::instance()) || is_siteadmin()) {
                $row[] = $line;
            }
        }
 
        return $row;
    }
}

