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
 * @package    local_costcenter
 * @copyright  2022 eAbyas Info Solutions Pvt. Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/local/costcenter/lib.php');
if (file_exists($CFG->dirroot.'/local/includes.php')) {
    require_once($CFG->dirroot.'/local/includes.php');
}
class local_costcenter_renderer extends plugin_renderer_base {

    /**
     * @method treeview
     * @todo To add action buttons
     */
    public function departments_view() {
        global $DB, $CFG, $USER;
        $labelstring = get_config('local_costcenter');
        $systemcontext = context_system::instance();
        if (is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)) {
                $sql = "SELECT DISTINCT(s.id), s.*
                         FROM {local_costcenter} s
                        WHERE parentid = 0 ORDER BY s.sortorder";
                $costcenters = $DB->get_records_sql($sql);
        } else if (has_capability('local/costcenter:view', $systemcontext)) {
            $sql = "SELECT distinct(s.id), s.*
                     FROM {local_costcenter} s
                    WHERE parentid = 0 AND id = ? ORDER BY s.sortorder";
            $costcenters = $DB->get_records_sql($sql, [$USER->open_costcenterid]);
        }
        if (!is_siteadmin() && empty($costcenters)) {
            throw new moodle_exception('notassignedcostcenter', 'local_costcenter');
        }
        $data = array();
        if (!empty($costcenters)) {
            foreach ($costcenters as $costcenter) {
                $line = array();
                $showdepth = 1;
                $line[] = $this->display_department_item($costcenter, $showdepth);
                $data[] = $line;
            }
            $table = new html_table();
            if (has_capability('local/costcenter:manage', $systemcontext)) {
                $table->head = array('');
                $table->align = array('left');
                $table->width = '100%';
                $table->data = $data;
                $table->id = 'department-index';
                $output = html_writer::table($table);
            }
        } else {
            $output = html_writer::tag('div', get_string('noorganizationsavailable', 'local_costcenter',$labelstring),
                array('class' => 'alert alert-info text-center mt-3'));
        }
        return $output;
    }

    /**
     * @method display_department_item
     * @todo To display the all costcenter items
     * @param object $record is costcenter
     * @param boolean $indicatedepth  depth for the costcenter item
     * @return string
     */
    public function display_department_item($record, $indicatedepth = true) {
        global $DB, $CFG;
        require_once($CFG->dirroot.'/local/costcenter/lib.php');
        $systemcontext = context_system::instance();
        $labelstring = get_config('local_costcenter');

        $sql = "SELECT id, id as id_val from {local_costcenter} where parentid = ?";
        $orgs = $DB->get_records_sql_menu($sql, [$record->id]);
        $departmentcount = count($orgs);
        if ($departmentcount > 0) {
            $deptcountlink = new moodle_url("/local/costcenter/costcenterview.php?id=".$record->id."");
        } else {
            $deptcountlink = 'javascript:void(0)';
        }
        $subdepartmentcount = 0;
        if ($departmentcount) {
            list($orgsql, $orgparams) = $DB->get_in_or_equal($orgs, SQL_PARAMS_NAMED, 'param', true, false);
            $subsql = "SELECT id, id as id_val from {local_costcenter} where parentid $orgsql";
            $subids = $DB->get_records_sql_menu($subsql, $orgparams);
            $subdepartmentcount = count($subids);
            if ($subdepartmentcount > 0) {
                $subdepartmentcount = $subdepartmentcount;
            } else {
                $subdepartmentcount = get_string('not_available', 'local_costcenter');
            }
        } else {
            $subdepartmentcount = get_string('not_available', 'local_costcenter');
        }

        $pluginnavs = local_costcenter_plugins_count($record->id);
        $dept_exists = $DB->get_record('local_costcenter', ['parentid' => $record->id, 'depth' => 2]);
        $itemdepth = ($indicatedepth) ? 'depth' . min(10, $record->depth) : 'depth1';
        $itemicon = $this->output->image_url('/i/item');
        $cssclass = !$record->visible ? 'dimmed' : '';

        if (has_capability('local/costcenter:manage', $systemcontext)) {
            $edit = true;
            if ($pluginnavs['totalusers'] == 0 && $pluginnavs['coursecount'] == 0 && $pluginnavs['allprogramcount'] == 0 && $pluginnavs['totalcurriculums'] == 0 && $pluginnavs['opencoursecount'] == 0 && $pluginnavs['totalemployees'] == 0 && empty($dept_exists)) {
                if ($pluginnavs['totalgroups'] !== 0) {
                    $cannotdelete = true;
                    $delconfirmationmsg = get_string('cannotdeletelinkedbatchcostcenter', 'local_costcenter', $record->fullname);
                    $cannothide = true;
                    $actionmessage = get_string('cannotinactivelinkedbatchcostcenter', 'local_costcenter', $record->fullname);
                } else {
                    if ($record->visible) {
                        $hide = true;
                        $hideurl = 'javascript:void(0)';
                    } else {
                        $show = true;
                        $showurl = 'javascript:void(0)';
                        $style = "opacity: 0.5;";
                    }
                    $labelstring->fullname = $record->fullname;
                    $cannothide = false;
                    $cannotdelete = false;
                    $actionmessage = get_string('confirmation_to_disable_'.$record->visible, 'local_costcenter', $labelstring);
                    $delete = true;
                    $delconfirmationmsg = get_string('confirmationmsgfordel', 'local_costcenter', $record->fullname);
                }
            } else {
                $hide = false;
                $show = false;
                $delete = false;
                $cannotdelete = false;
                $cannothide = false;
                $style = false;
                $delconfirmationmsg = '';
            }
        }
        // $labelstring = get_config('local_costcenter');
        $viewdeptcontext = [
            "coursefileurl" => $this->output->image_url('/course_images/courseimg', 'local_costcenter'),
            "orgname" => format_string($record->fullname),
            "dept_count_link" => $deptcountlink,
            "deptcount" => $departmentcount,
            "subdeptcount" => $subdepartmentcount,
            "editicon" => $this->output->image_url('t/edit'),
            "hideicon" => $this->output->image_url('t/hide'),
            "showicon" => $this->output->image_url('t/show'),
            "deleteicon" => $this->output->image_url('t/delete'),
            "hideurl" => $hideurl,
            "showurl" => $showurl,
            "edit" => $edit,
            "hide" => $hide,
            "show" => $show,
            "action_message" => $actionmessage,
            "delete_message" => $delconfirmationmsg,
            "status" => $record->visible,
            "delete" => $delete,
            "cannotdelete" => $cannotdelete,
            "cannothide" => $cannothide,
            "style" => $style,
            "recordid" => $record->id,
            "parentid" => $record->parentid,
            "headstring" => 'editcostcen',
            "labelstring" => "'$labelstring->firstlevel'",
            "secondlevelstring" => $labelstring->secondlevel,
            "formtype" => 'organization'
        ];

        $viewdeptcontext = $viewdeptcontext + $pluginnavs;

        return $this->render_from_template('local_costcenter/costcenter_view', $viewdeptcontext);
    }

    /**
     * @method get_dept_view_btns
     * @todo To display create icon
     * @param object $id costcenter  id
     * @return string
     */
    public function get_dept_view_btns ($id=false) {
        global $USER, $DB, $CFG;
        $labelstring = get_config('local_costcenter');
        $createorganisation = '';
        $systemcontext = context_system::instance();
        if ((is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext))) {
            $depth = $DB->get_field('local_costcenter', 'depth', array('id' => $id));
            if ($depth == null) {
                $createorganisation = "<a class='course_extended_menu_itemlink'
                                            data-action='createcostcentermodal'
                                            data-value='0' title = '".get_string('create_organization', 'local_costcenter', $labelstring)."'
                                            onclick ='(function(e){
                                                require(\"local_costcenter/newcostcenter\").init({
                                                    selector:\"createcostcentermodal\",
                                                    contextid:$systemcontext->id,
                                                    id:0, formtype:\"organization\",
                                                    headstring:\"adnewcostcenter\", labelstring: \"$labelstring->firstlevel\" })
                                            })(event)'>
                                            <span class='createicon'>
                                            <span class='create_collegeicon'></span>
                                            <i class='createiconchild fa fa-plus' aria-hidden='true'>
                                            </i></span>
                                            </a>";


                $url = "$CFG->wwwroot/admin/settings.php?section=local_costcenter";

                $settingstitle = get_string('settingstitle', 'local_costcenter');
                $settingurl = "<a class = 'text-dark ' href='$url' title='$settingstitle'><i class='icon setting-icon fa fa-gear'></i></a>";
            } else {
                $createorganisation = false;
                $settingurl = false;
            }
        } else {
            $createorganisation = false;
            $settingurl = false;
        }

        $existsql = "SELECT id FROM {local_costcenter} WHERE 1 = 1 ";
        $costcenterexist = $DB->record_exists_sql($existsql);
        if ($id) {
            $depth = $DB->get_field('local_costcenter', 'depth', array('id' => $id));
        } else {
            $depth = 1;
        }
        if ($costcenterexist && $depth != 2) {
            if (is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations',
                    $systemcontext) || has_capability('local/costcenter:manage_ownorganization',
                        $systemcontext)) {
                // $url = "$CFG->wwwroot/admin/settings.php?section=local_costcenter";
                $headstring = 'adnewdept';
                $title = get_string('createdepartment', 'local_costcenter', $labelstring);
                $createdepartment = "<a class='course_extended_menu_itemlink'
                                         data-action='createcostcentermodal' data-value='0'
                                         title = '$title' onclick ='(function(e){
                                            require(\"local_costcenter/newcostcenter\").init({
                                                selector:\"createcostcentermodal\",
                                                contextid:$systemcontext->id, id:0,
                                                formtype:\"department\", headstring:\"$headstring\", labelstring: \"$labelstring->secondlevel\"
                                                }) })(event)'><i class='fa fa-sitemap icon' aria-hidden='true'></i>
                                        </a>";
                // $settingstitle = get_string('settingstitle', 'local_costcenter');
                // $settingurl = "<a class = 'text-dark ' href='$url' title='$settingstitle'><i class='icon setting-icon fa fa-gear'></i>
                //                         </a>";


            } else {
                $createdepartment = false;
                // $settingurl = false;
            }
        } else {
            $createdepartment = false;
            // $settingurl = false;
        }
        $deptexistsql = "SELECT id FROM {local_costcenter} WHERE depth = 2 ";
        if (!(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext))) {
            $deptexistsql .= " AND parentid = {$USER->open_costcenterid} ";
        }
        $deptexist = $DB->record_exists_sql($deptexistsql);
        if ($deptexist) {
            $headstring = 'adnewsubdept';
            $title = get_string('createsubdepartment', 'local_costcenter', $labelstring);
            $createsubdepartment = "<a class='course_extended_menu_itemlink'
                                         data-action='createcostcentermodal' data-value='0'
                                         title = '$title' onclick ='(function(e)
                                         { require(\"local_costcenter/newcostcenter\")
                                         .init({selector:\"createcostcentermodal\", contextid:
                                         $systemcontext->id, id:0, formtype:\"subdepartment\",
                                         headstring:\"$headstring\", labelstring: \"$labelstring->thirdlevel\"}) })(event)'>
                                         <span class='create_depticon'></span>
                                      </a>";
        } else {
            $createsubdepartment = false;
        }

        $buttons = array(
            'create_organisation' => $createorganisation,
            'create_department' => $createdepartment,
            'create_sub_department' => $createsubdepartment,
            'settingurl' => $settingurl,

        );
        return $this->render_from_template('local_costcenter/viewbuttons', $buttons);
    }


    /**
     * @method get_dept_view_btns
     * @todo To display create icon
     * @param object $id costcenter  id
     * @return string
     */
    public function costcenterview ($id, $systemcontext) {
        global $DB, $USER, $CFG;
        $labelstring = get_config('local_costcenter');
        if (!$depart = $DB->get_record('local_costcenter', array('id' => $id))) {
            throw new moodle_exception('invalidschoolid');
        }
        if (has_capability('local/costcenter:manage', $systemcontext)) {
            $edit = true;
            if (count(array($depart)) == 0 && $pluginnavs['totalusers'] == 0) {
                if ($depart->visible) {
                    $hide = true;
                } else {
                    $show = true;
                }
                $actionmessage = get_string('confirmation_to_disable_'.$depart->visible, 'local_costcenter', $depart->fullname);
                $delete = true;
                $delconfirmationmsg = get_string('confirmationmsgfordel', 'local_costcenter', $depart->fullname);
            } else {
                $hide = false;
                $show = false;
                $delete = false;
                $delconfirmationmsg = '';
            }
        }
        $deptcountlink = '';
        $subdepartment = '';
        $departmentssql = "SELECT id,id AS id_val FROM {local_costcenter} WHERE parentid = :parent";
        $departments = $DB->get_records_sql_menu($departmentssql, array('parent' => $id));
        $department = count($departments);
        $department = ($department > 0 ? $department : get_string('not_available', 'local_costcenter'));
        $deptid = implode(',', $departments);

        if ($deptid) {
             $subdepartmentssql = "SELECT id,id AS id_val FROM {local_costcenter} WHERE parentid IN ($deptid)";
             $subdepartments = $DB->get_records_sql_menu($subdepartmentssql);
             $subdepartment = count($subdepartments);
             $subdepartment = ($subdepartment > 0 ? $subdepartment : get_string('not_available', 'local_costcenter'));
        }

        if ($department > 0) {
            $deptcountlink = $department;
        } else {
            $deptcountlink = 0;
        }

        $departments = $DB->get_records('local_costcenter', array('parentid' => $id));
        $totaldepts = count($departments);

        $departmentscontent = array();
        if ($totaldepts % 2 == 0) {
            $deptclass = '';
        } else {
            $deptclass = 'deptsodd';
        }

        $deptkeys = array_values($departments);
        foreach ($deptkeys as $key => $dept) {
            $even = false;
            $odd = false;
            if ($key % 2 == 0) {
                $even = true;
            } else {
                $odd = true;
            }

            $costcenterarray = array();
            $subdepartments = $DB->get_records('local_costcenter', array('parentid' => $dept->id));
            $subdept = count($subdepartments);
            if ($subdept) {
                $subdeptcountlink = $CFG->wwwroot.'/local/costcenter/costcenterview.php?id='.$dept->id;
            } else {
                $subdeptcountlink = "javascript:void(0)";
            }
            $subdept = ($subdept > 0 ? $subdept : get_string('not_available', 'local_costcenter'));

            $deparray = local_costcenter_plugins_count($dept->parentid, $dept->id);
            $subdept_exists = $DB->get_record('local_costcenter', ['parentid' => $dept->id, 'depth' => 3]);
            if (has_capability('local/costcenter:manage', $systemcontext)) {
                $deptedit = true;
                $string = new \stdClass();
                $string->fullname = $dept->fullname;
                $string->secondlevel = $labelstring->secondlevel;
                if ($deparray['totalusers'] == 0 && $deparray['coursecount'] == 0 && $deparray['allprogramcount'] == 0 && $deparray['totalcurriculums'] == 0 && $deparray['opencoursecount'] == 0 && $deparray['totalemployees'] == 0 && empty($subdept_exists)) {
                    if (!$deparray['totalgroups'] == 0) {
                        $deptdelete = false;
                        $deptdelconfirmationmsg = get_string('cannotdeletelinkedbatchdepartment', 'local_costcenter', $dept->fullname);
                        $cannothidedept = true;
                        $deptactionmessage = get_string('cannotinactivelinkedbatchdepartment', 'local_costcenter', $dept->fullname);
                        $depthide = false;
                        $deptshow = false;
                    } else {
                        if ($dept->visible) {
                            $depthide = true;
                        } else {
                            $deptshow = true;
                            $style = "opacity: 0.5;";
                        }
                        $cannothidedept = false;
                        $deptactionmessage = get_string('confirmation_to_disable_department_'.$dept->visible, 'local_costcenter', $string);
                        $deptdelete = true;
                        $deptdelconfirmationmsg = get_string('confirmationmsgfordel', 'local_costcenter', $dept->fullname);
                    }
                } else {
                    $hidestring = new \stdclass();
                    $hidestring->fullname = $dept->fullname;
                    $hidestring->secondlevel = $labelstring->secondlevel;
                    $hidestring->thirdlevel = $labelstring->thirdlevel;
                    $depthide = false;
                    $deptshow = false;
                    $cannothidedept = true;
                    $deptactionmessage = get_string('cannot_disable_department', 'local_costcenter', $hidestring);

                    $deptdelete = false;
                    $deptdelconfirmationmsg = get_string('cannotdeletedep', 'local_costcenter', $hidestring);
                }
            }
            $costcenterarray['style'] = $style;
            $costcenterarray['cannothidedept'] = $cannothidedept;
            $costcenterarray['subdept'] = $subdept;
            $costcenterarray['subdepartment'] = $labelstring->thirdlevel;
            $costcenterarray['enablesubdepartment_link'] = true;
            $costcenterarray['subdept_count_link'] = $subdeptcountlink;
            $costcenterarray['departmentparentid'] = $dept->parentid;
            $costcenterarray['departmentfullname'] = $dept->fullname;
            $costcenterarray['edit_image_url'] = $this->output->image_url('t/edit');
            $costcenterarray['even'] = $even;
            $costcenterarray['odd'] = $odd;
            $costcenterarray['deptclass'] = $deptclass;
            $costcenterarray['deptedit'] = $deptedit;
            $costcenterarray['depthide'] = $depthide;
            $costcenterarray['deptshow'] = $deptshow;
            $costcenterarray['deptstatus'] = $dept->visible;
            $costcenterarray['deptdelete'] = $deptdelete;
            $costcenterarray['deptid'] = $dept->id;
            $costcenterarray['deptaction_message'] = $deptactionmessage;
            $costcenterarray['deptdel_confirmationmsg'] = $deptdelconfirmationmsg;
            $costcenterarray['dept_actionmessage'] = $deptactionmessage;
            $costcenterarray['headstring'] = 'update_costcenter';
            $costcenterarray['labelstring'] = "'$labelstring->secondlevel'";
            $costcenterarray['formtype'] = 'department';
            $departmentscontent[] = $costcenterarray + $deparray;
        }
        $labelstring = get_config('local_costcenter');
        $costcenterviewcontent = [
            "deptcount" => $deptcountlink,
            "subdeptcount" => $subdepartment,
            "deptclass" => $deptclass,
            "coursefileurl" => $this->output->image_url('/course_images/courseimg', 'local_costcenter'),
            "orgname" => $depart->fullname,
            "edit" => $edit,
            "hide" => $hide,
            "show" => $show,
            "status" => $depart->visible,
            "delete" => $delete,
            "recordid" => $depart->id,
            "parentid" => $depart->parentid,
            "action_message" => $actionmessage,
            "delete_message" => $delconfirmationmsg,
            "departments_content" => $departmentscontent,
            "headstring" => 'editcostcen',
            "labelstring" => "'$labelstring->firstlevel'",
            "secondlevelstring" => $labelstring->secondlevel,
            "formtype" => 'organization'
        ];
        $pluginnavs = local_costcenter_plugins_count($id);
        $costcenterviewcontent = $costcenterviewcontent + $pluginnavs;
        return $this->output->render_from_template('local_costcenter/departments_view', $costcenterviewcontent);
    }
    public function department_view ($id, $systemcontext) {
        global $DB, $USER;
        $labelstring = get_config('local_costcenter');
        if (!$depart = $DB->get_record('local_costcenter', array('id' => $id))) {
            throw new moodle_exception('invalidschoolid');
        }
        if (has_capability('local/costcenter:manage', $systemcontext)) {
            $edit = true;
            if (count(array($depart)) == 0 && $pluginnavs['totalusers'] == 0) {
                if ($depart->visible) {
                    $hide = true;
                } else {
                    $show = true;
                }
                $string = new \stdClass();
                $string->fullname = $depart->fullname;
                $string->secondlevel = $labelstring->secondlevel;
                $actionmessage = get_string('confirmation_to_disable_department_'.$depart->visible, 'local_costcenter', $string);
                $delete = true;
                $delconfirmationmsg = get_string('confirmationmsgfordel', 'local_costcenter', $depart->fullname);
            } else {
                $hide = false;
                $show = false;
                $delete = false;
                $delconfirmationmsg = '';
            }
        }
        $deptcountlink = '';
        $subdeptcountlink = '';
        $organisationid = $DB->get_field('local_costcenter', 'parentid', array('id' => $id));
        $subdepartment = '';
        $departmentssql = "SELECT id, id AS id_val FROM {local_costcenter} WHERE parentid = :parent";
        $departments = $DB->get_records_sql_menu($departmentssql, array('parent' => $id));
        $department = count($departments);
        $department = ($department > 0 ? $department : get_string('not_available', 'local_costcenter'));
        $subdepartments = $DB->get_records('local_costcenter', array('parentid' => $id));
        $totalsubdepts = count($subdepartments);
        $totaldepts = $totalsubdepts;
        $departmentscontent = array();
        if ($totaldepts % 2 == 0) {
            $deptclass = '';
        } else {
            $deptclass = 'deptsodd';
        }
        $deptkeys = array_values($subdepartments);
        foreach ($deptkeys as $key => $dept) {
            $even = false;
            $odd = false;
            if ($key % 2 == 0) {
                $even = true;
            } else {
                $odd = true;
            }

            $departmentsarray = array();
            $subdepartments = $DB->get_records('local_costcenter', array('parentid' => $dept->id));
            $subdept = count($subdepartments);
            $subdept = ($subdept > 0 ? $subdept : get_string('not_available', 'local_costcenter'));
            $subdeparray = local_costcenter_plugins_count($organisationid, $dept->parentid, $dept->id);

            if (has_capability('local/costcenter:manage', $systemcontext)) {
                $deptedit = true;
                $string = new \stdClass();
                $string->fullname = $dept->fullname;
                $string->thirdlevel = $labelstring->thirdlevel;
                if ($subdeparray['totalusers'] == 0 && $subdeparray['coursecount'] == 0 && $subdeparray['allprogramcount'] == 0 && $subdeparray['totalcurriculums'] == 0 && $subdeparray['opencoursecount'] == 0 && $subdeparray['totalemployees'] == 0) {
                    if (!$subdeparray['totalgroups'] == 0) {
                        $deptdelete = false;
                        $deptdelconfirmationmsg = get_string('cannotdeletelinkedbatchsubdepartment', 'local_costcenter', $dept->fullname);
                        $cannothidedept = true;
                        $deptactionmessage = get_string('cannotinactivelinkedbatchsubdepartment', 'local_costcenter', $dept->fullname);
                        $depthide = false;
                        $deptshow = false;
                    } else {
                        if ($dept->visible == 1) {
                            $depthide = true;
                        } else {
                            $deptshow = true;
                            $style = "opacity: 0.5;";
                        }
                        $cannothidedept = false;
                        $deptactionmessage = get_string('confirmation_to_disable_subdepartment_'.$dept->visible, 'local_costcenter', $string);

                        $deptdelete = true;
                        $deptdelconfirmationmsg = get_string('confirmationmsgfordel', 'local_costcenter', $dept->fullname);
                    }
                } else {
                    $hidestring = new \stdClass();
                    $hidestring->fullname = $dept->fullname;
                    $hidestring->thirdlevel = $labelstring->thirdlevel;
                    $depthide = false;
                    $deptshow = false;
                    $cannothidedept = true;
                    $deptactionmessage = get_string('cannot_disable_subdepartment_'.$dept->visible, 'local_costcenter', $hidestring);

                    $deptdelete = false;
                    $deptdelconfirmationmsg = get_string('cannotdeletesubdep', 'local_costcenter', $hidestring);;
                }
            }
            $departmentsarray['style'] = $style;
            $departmentsarray['cannothidedept'] = $cannothidedept;
            $departmentsarray['subdept'] = $subdept;
            $departmentsarray['enablesubdepartment_link'] = false;
            $departmentsarray['subdept_count_link'] = $subdeptcountlink;
            $departmentsarray['departmentparentid'] = $dept->parentid;
            $departmentsarray['departmentfullname'] = $dept->fullname;
            $departmentsarray['edit_image_url'] = $this->output->image_url('t/edit');
            $departmentsarray['even'] = $even;
            $departmentsarray['odd'] = $odd;
            $departmentsarray['deptclass'] = $deptclass;
            $departmentsarray['deptedit'] = $deptedit;
            $departmentsarray['depthide'] = $depthide;
            $departmentsarray['deptshow'] = $deptshow;
            $departmentsarray['deptstatus'] = $dept->visible;
            $departmentsarray['deptdelete'] = $deptdelete;
            $departmentsarray['deptid'] = $dept->id;
            $departmentsarray['deptaction_message'] = $deptactionmessage;
            $departmentsarray['hide_users'] = false;
            $departmentsarray['hide_courses'] = true;
            $departmentsarray['hide_exams'] = true;
            $departmentsarray['hide_learninplans'] = true;
            $departmentsarray['hide_feedbacks'] = true;
            $departmentsarray['hide_classroom'] = true;
            $departmentsarray['hide_program'] = true;
            $departmentsarray['hide_certification'] = true;
            $departmentsarray['headstring'] = 'update_subdept';
            $departmentsarray['labelstring'] = "'$labelstring->thirdlevel'";
            $departmentsarray['formtype'] = 'subdepartment';
            $departmentsarray['deptdel_confirmationmsg'] = $deptdelconfirmationmsg;
            $departmentsarray['dept_actionmessage'] = $deptactionmessage;
            $departmentscontent[] = $departmentsarray + $subdeparray;
        }

        $costcenterviewcontent = [
            'showsubdept_content' => true,
            'totalsubdepts' => $totalsubdepts,
            "deptcount" => $deptcountlink,
            "subdeptcount" => $subdepartment,
            "deptclass" => $deptclass,
            "coursefileurl" => $this->output->image_url('/course_images/courseimg', 'local_costcenter'),
            "orgname" => $depart->fullname,
            "edit" => $edit,
            "hide" => $hide,
            "show" => $show,
            "status" => $depart->visible,
            "delete" => $delete,
            "recordid" => $depart->id,
            "parentid" => $depart->parentid,
            "action_message" => $actionmessage,
            "delete_message" => $delconfirmationmsg,
            "departments_content" => $departmentscontent,
            "headstring" => 'update_costcenter',
            "labelstring" => "'$labelstring->secondlevel'",
            "thirdlabelstring" => $labelstring->thirdlevel,
            "formtype" => 'department'
        ];
        $pluginnavs = local_costcenter_plugins_count($organisationid, $dept->parentid);
        $costcenterviewcontent = $costcenterviewcontent + $pluginnavs;
        return $this->output->render_from_template('local_costcenter/departments_view', $costcenterviewcontent);
    }
}
