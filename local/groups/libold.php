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

define('LOCAL_COHORT_ALL', 0);
define('LOCAL_COHORT_COUNT_MEMBERS', 1);
define('LOCAL_COHORT_COUNT_ENROLLED_MEMBERS', 3);
define('LOCAL_COHORT_WITH_MEMBERS_ONLY', 5);
define('LOCAL_COHORT_WITH_ENROLLED_MEMBERS_ONLY', 17);
define('LOCAL_COHORT_WITH_NOTENROLLED_MEMBERS_ONLY', 23);


class local_groups implements renderable {

    public function __construct($page, $perpage, $searchquery, $showall) {
        $context = context_system::instance();
        $cohorts = local_groups_get_groups($context->id, $page, $perpage, $searchquery);
        $this->context = $context;
        $this->groups = $cohorts;
        $this->showall = $showall;
        $this->page = $page;
        $this->searchquery = $searchquery;
    }
}
function manage_groups_count ($stable, $filterdata, $decodedata) {
    global $DB, $USER;
    $params = array();
    $countsql = "SELECT count(c.id) ";
    $fields = "SELECT c.*";
    $countfields = "SELECT COUNT(1)";
    
    $sql = " FROM {cohort} c, {local_groups} g, {local_program} p
             WHERE g.cohortid = c.id AND contextid = $decodedata->contextid";

    $context = context_system::instance();
    if ( has_capability('local/costcenter:manage_multiorganizations', $context ) ) {
        $costcenters = $DB->get_records_sql_menu('select fullname, id from {local_costcenter} where parentid = 0 ');
        if (!empty($costcenters)) {
            $mycostcenters = implode(',', $costcenters);
            $sql .= " and costcenterid IN( $mycostcenters )";
        }
    } else if (has_capability('local/costcenter:manage_ownorganization', $context)) {
        $costcenter = $DB->get_record_sql("SELECT cc.id, cc.parentid
                                            FROM {user} u
                                            JOIN {local_costcenter} cc ON u.open_costcenterid = cc.id
                                           WHERE u.id = {$USER->id}");
        if ($costcenter->parentid == 0) {
            $sql .= " and costcenterid IN( $costcenter->id )";
        } else {

            $sql .= " AND ( CONCAT(',', $costcenter->id, ',') LIKE CONCAT('%,',g.departmentid,',%') )  ";
        }
    } else {
        $sql .= " AND ( CONCAT(',', $USER->open_departmentid, ',') LIKE CONCAT('%,',g.departmentid,',%') )  ";
    }
    $order = " ORDER BY g.id DESC";

    if (isset($filterdata->search_query) && trim($filterdata->search_query) != '') {
        $filteredprograms = array_filter(explode(',', $filterdata->search_query));
        $programsarray = array();
        if (!empty($filteredprograms)) {
            foreach ($filteredprograms as $key => $value) {
                $programsarray[] = "c.name LIKE '%".trim($value)."%' OR c.idnumber LIKE '%".trim($value)."%'";
            }
            $imploderequests = implode(' OR ', $programsarray);
            $sql .= " AND ($imploderequests)";
        }
    }
    if (isset($filterdata->program)) {
        $filterdata->program = str_replace('_qf__force_multiselect_submission', '', $filterdata->program);
    }
    if (!empty($filterdata->program)) {
        $filteredprograms = array_filter(explode(',', $filterdata->program), 'is_numeric');
        $programsarray = array();
        if (!empty($filteredprograms)) {
            foreach ($filteredprograms as $key => $value) {
                $programsarray[] = "p.id = {$value}";
            }
            $imploderequests = implode(' OR ', $programsarray);
            $sql .= " AND p.batchid = c.id AND ($imploderequests)";
        }
    }
    $values = array();
    foreach ($filteredprograms as $key => $value) {
        $values[] = $value;
    }
    $val = implode(' , ', $values);
    if ($programsarray) {
        $total = ("SELECT COUNT(c.id)
                    FROM {cohort} c
                   WHERE c.name IN ('$val') AND c.idnumber IN ('$val')"
                );
        $totalusers = $allgroups = $DB->count_records_sql($total);
    } else if ($val) {
        $total = ("SELECT COUNT(c.id)
                  FROM {cohort} c
                  JOIN {local_program} p ON c.id = p.batchid
                 WHERE p.id IN ($val)"
                );
        $totalusers = $allgroups = $DB->count_records_sql($total);
    } else {
        $total = ("SELECT COUNT(id) AS totalusers
                    FROM {cohort}"
                );
        $totalusers = $allgroups = $DB->count_records_sql($total);
    }
    $groups = $DB->get_records_sql($fields . $sql, $params, $stable->start, $stable->length);

    return array('totalusers' => $totalusers, 'groups' => $groups, 'allgroups' => $allgroups);
}

function manage_groups_content($stable, $totalgroups) {
    global $DB, $OUTPUT, $PAGE, $CFG, $USER;
    $output = '';
    $data = array();
    $systemcontext = context_system::instance();
    $editcolumnisempty = true;
    if ($showall) {
        $params['showall'] = true;
    }
    $params = array('page' => $page);
    $baseurl = new moodle_url('/local/groups/index.php', $params);
    $row = [];
    foreach ($totalgroups as $cohort) {
        $line = array();
        $groupname = $cohort->name;
        $groupid  = $cohort->idnumber;
        if (strlen($groupid) > 8) {
            $groupid = substr($groupid, 0, 8).'...';
        }
        $cohortcontext = context::instance_by_id($cohort->contextid);
        $urlparams = array('id' => $cohort->id, 'returnurl' => $baseurl->out_as_local_url());
        $programname = $DB->get_field('local_program', 'name', array('batchid' => $cohort->id));

        if ($programname == null) {
            $programname = 'N/A';
        }

        $line['groupname'] = $groupname;
        $line['groupid'] = $groupid;
        $line['programname'] = $programname;

        $buttons = array();

        if (empty($cohort->component)) {
            $cohortmanager = has_capability('moodle/cohort:manage', $cohortcontext);
            $cohortcanassign = has_capability('moodle/cohort:assign', $cohortcontext);
            $showhideurl = new moodle_url('/local/groups/edit.php', $urlparams + array('sesskey' => sesskey()));
            if ($cohortmanager) {
                $buttons[] = html_writer::start_tag('li');
                $buttons[] = html_writer::link('javascript:void(0)',
                                $OUTPUT->pix_icon('t/edit', get_string('edit')),
                                array('title' => get_string('edit'),
                                    'onclick' => '(function(e){require(
                                        "local_groups/newgroup").
                                        init({contextid:'.$systemcontext->id.',
                                        groupsid:'.$cohort->id.'}) })(event)'
                                    )
                            );
                $buttons[] = html_writer::end_tag('li');
                $editcolumnisempty = false;
                if ($cohort->visible) {
                    $buttons[] = html_writer::start_tag('li');
                    $buttons[] = html_writer::link('javascript:void(0)',
                                    $OUTPUT->pix_icon('t/hide', get_string('inactive')),
                                    array('id' => 'hideconfirm' . $cohort->id . '',
                                        'onclick' => '(function(e){require(
                                            "local_groups/renderselections").hidecohort('.
                                            $cohort->id.', "'.$cohort->name.'") })(event)'
                                        )
                                );
                    $buttons[] = html_writer::end_tag('li');
                } else {
                    $buttons[] = html_writer::start_tag('li');
                    $buttons[] = html_writer::link('javascript:void(0)',
                                    $OUTPUT->pix_icon('t/show', get_string('active')),
                                    array('id' => 'unhideconfirm' . $cohort->id . '',
                                        'onclick' => '(function(e){require("
                                            local_groups/renderselections").unhidecohort('.
                                            $cohort->id.', "'.$cohort->name.'") })(event)'
                                        )
                                );
                    $buttons[] = html_writer::end_tag('li');
                }
            }
            if ($cohortcanassign) {
                $buttons[] = html_writer::start_tag('li');
                if ($programid = $DB->record_exists('local_program', array('batchid' => $cohort->id))) {
                    $buttons[] = html_writer::link(new moodle_url('/local/groups/assign.php',
                                                    $urlparams), $OUTPUT->pix_icon('i/enrolusers',
                                                    get_string('assign', 'core_cohort')),
                                                    array('title' => get_string('assign',
                                                        'core_cohort')
                                                    )
                                                );
                    $editcolumnisempty = false;
                    $buttons[] = html_writer::end_tag('li');
                } else {
                    $buttons[] = html_writer::link(
                                                    new moodle_url('/local/groups/emptyusers.php?bcid=null'),
                                                    $OUTPUT->pix_icon('i/enrolusers',
                                                    get_string('assign', 'core_cohort')),
                                                    array('title' => get_string('assign',
                                                        'core_cohort')
                                                    )
                                                );
                }
                $buttons[] = html_writer::start_tag('li');
                if ($programid = $DB->record_exists('local_program', array('batchid' => $cohort->id))) {
                    $buttons[] = html_writer::link(new moodle_url('/local/groups/
                                                    mass_enroll.php', $urlparams),
                                                    $OUTPUT->pix_icon('i/users',
                                                    get_string('bulk_enroll', 'local_groups')),
                                                    array('title' => get_string('bulk_enroll',
                                                        'local_groups')
                                                    )
                                                );
                    $buttons[] = html_writer::end_tag('li');
                } else {
                    $buttons[] = html_writer::link(
                                                new moodle_url('/local/groups/emptyusers.php?bcid=0'),
                                                $OUTPUT->pix_icon('i/users', get_string(
                                                    'bulk_enroll', 'local_groups')),
                                                array('title' => get_string('bulk_enroll',
                                                    'local_groups')
                                                )
                                            );
                }
            }
            if ($cohortmanager) {
                $buttons[] = html_writer::start_tag('li');
                $buttons[] = html_writer::link(
                                "javascript:void(0)",
                                $OUTPUT->pix_icon('i/delete', get_string('delete'), 'moodle', array('title' => '')),
                                array('id' => 'deleteconfirm' . $cohort->id . '', 'onclick' => '(
                                      function(e){
                        require("local_groups/renderselections").deletecohort(' . $cohort->id . ', "' . $cohort->name . '")
                        })(event)'));
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
            $line['user_url'] = $CFG->wwwroot . '/local/groups/emptyusers.php?bcid='.$programid = 'null';
        }
        $line['userid'] = $cohort->id;
        $line['location_url'] = $CFG->wwwroot . '/local/groups/assign.php?id='.$cohort->id;
        if (has_capability('local/groups:manage', context_system::instance()) || is_siteadmin()) {
            $row[] = $line;
        }
    }
    return $row;
}

/**
 * Add new groups.
 *
 * @param  stdClass $groups
 * @return int new groups id
 */
function local_groups_add_groups($groups) {
    global $DB, $USER;

    if (!isset($groups->name)) {
        throw new coding_exception('Missing groups name in groups_add_groups().');
    }
    if (!isset($groups->idnumber)) {
        $groups->idnumber = null;
    }

    if (!empty($groups->description_editor['text'])) {
        $groups->description = $groups->description_editor['text'];
    } else {
        $groups->description = '';
    }

    if (!empty($groups->description_editor['format'])) {
        $groups->descriptionformat = $groups->description_editor['format'];
    } else {
        $groups->descriptionformat = FORMAT_HTML;
    }

    if (!isset($groups->visible)) {
        $groups->visible = 1;
    }
    if (empty($groups->component)) {
        $groups->component = '';
    }
    if (!isset($groups->timecreated)) {
        $groups->timecreated = time();
    }
    if (!isset($groups->timemodified)) {
        $groups->timemodified = $groups->timecreated;
    }
    $groups->id = $DB->insert_record('cohort', $groups);

    $newgroup = new stdClass();
    $newgroup->cohortid = $groups->id;
    $newgroup->usermodified = $USER->id;
    $newgroup->timemodified = time();
    $newgroup->costcenterid = $groups->open_costcenterid;
    $newgroup->departmentid = $groups->open_departmentid;
    $newgroup->subdepartmentid = $groups->open_subdepartment;

    $DB->insert_record('local_groups', $newgroup);

    $event = \core\event\cohort_created::create(array(
       'context' => context::instance_by_id($groups->contextid),
       'objectid' => $groups->id,
    ));
    $event->add_record_snapshot('groups', $groups);
    $event->trigger();

    return $groups->id;
}

/**
 * Update existing groups.
 * @param  stdClass $groups
 * @return void
 */
function local_groups_update_groups($groups) {
    global $DB;
    if (property_exists($groups, 'component') && empty($groups->component)) {
        $groups->component = '';
    }

    if (!empty($groups->description_editor['text'])) {
        $groups->description = $groups->description_editor['text'];
    } else {
        $groups->description = '';
    }

    if (!empty($groups->description_editor['format'])) {
        $groups->descriptionformat = $groups->description_editor['format'];
    } else {
        $groups->descriptionformat = FORMAT_HTML;
    }

    $groups->timemodified = time();
    $DB->update_record('cohort', $groups);

    $cohortgroup = $DB->get_record('local_groups', array('cohortid' => $groups->id));
    $newcohortgroup = new \stdClass();
    $newcohortgroup->id = $groups->id;
    $costcenterid = $groups->open_costcenterid;
    $departmentid = $groups->open_departmentid;
    $subdepartmentid = $groups->open_subdepartment;
    if (!empty($costcenterid)) {
        $newcohortgroup->costcenterid = $costcenterid;
    }
    if (!empty($departmentid) || $departmentid == 0) {
        $newcohortgroup->departmentid = $departmentid;
    }
    if (!empty($subdepartmentid) || $subdepartmentid == 0) {
        $newcohortgroup->subdepartmentid = $subdepartmentid;
    }
    if ($costcenterid) {
        $DB->update_record('local_groups', $newcohortgroup);
        $event = \core\event\cohort_updated::create(array(
           'context' => context::instance_by_id($groups->contextid),
           'objectid' => $groups->id,
        ));
        $event->trigger();
    }
}

/**
 * Delete groups.
 * @param  stdClass $groups
 * @return void
 */
function local_groups_delete_groups($groups) {
    global $DB;

    $DB->delete_records('cohort_members', array('cohortid' => $groups->id));
    $DB->delete_records('cohort', array('id' => $groups->id));
    $DB->delete_records('local_groups', array('cohortid' => $groups->id));

    // Notify the competency subsystem.
    \core_competency\api::hook_cohort_deleted($groups);

    $event = \core\event\cohort_deleted::create(array(
       'context' => context::instance_by_id($groups->contextid),
       'objectid' => $groups->id,
    ));
    $event->add_record_snapshot('groups', $groups);
    $event->trigger();
}

/**
 * Somehow deal with groups when deleting course category,
 * we can not just delete them because they might be used in enrol
 * plugins or referenced in external systems.
 * @param  stdClass|coursecat $category
 * @return void
 */
function local_groups_delete_category($category) {
    global $DB;
    // TODO: make sure that groups are really, really not used anywhere and delete, for now just move to parent or system context.

    $oldcontext = context_coursecat::instance($category->id);

    if ($category->parent && $parent = $DB->get_record('course_categories', array('id' => $category->parent))) {
        $parentcontext = context_coursecat::instance($parent->id);
        $sql = "UPDATE {cohort} SET contextid = :newcontext WHERE contextid = :oldcontext";
        $params = array('oldcontext' => $oldcontext->id, 'newcontext' => $parentcontext->id);
    } else {
        $syscontext = context_system::instance();
        $sql = "UPDATE {cohort} SET contextid = :newcontext WHERE contextid = :oldcontext";
        $params = array('oldcontext' => $oldcontext->id, 'newcontext' => $syscontext->id);
    }

    $DB->execute($sql, $params);
}

/**
 * Add groups member
 * @param  int $groupsid
 * @param  int $userid
 * @return void
 */
function local_groups_add_member($groupsid, $userid) {
    global $DB;
    if ($DB->record_exists('cohort_members', array('cohortid' => $groupsid, 'userid' => $userid))) {
        // No duplicates!
        return;
    }
    $record = new stdClass();
    $record->cohortid  = $groupsid;
    $record->userid    = $userid;
    $record->timeadded = time();
    $DB->insert_record('cohort_members', $record);

    $groups = $DB->get_record('cohort', array('id' => $groupsid), '*', MUST_EXIST);

    $event = \core\event\cohort_member_added::create(array(
       'context' => context::instance_by_id($groups->contextid),
       'objectid' => $groupsid,
       'relateduserid' => $userid,
    ));
    $event->add_record_snapshot('groups', $groups);
    $event->trigger();
}

/**
 * Remove groups member
 * @param  int $groupsid
 * @param  int $userid
 * @return void
 */
function local_groups_remove_member($groupsid, $userid) {
    global $DB;
    $DB->delete_records('cohort_members', array('cohortid' => $groupsid, 'userid' => $userid));

    $groups = $DB->get_record('cohort', array('id' => $groupsid), '*', MUST_EXIST);

    $event = \core\event\cohort_member_removed::create(array(
       'context' => context::instance_by_id($groups->contextid),
       'objectid' => $groupsid,
       'relateduserid' => $userid,
    ));
    $event->add_record_snapshot('groups', $groups);
    $event->trigger();
}

/**
 * Is this user a groups member?
 * @param int $groupsid
 * @param int $userid
 * @return bool
 */
function local_groups_is_member($groupsid, $userid) {
    global $DB;

    return $DB->record_exists('groups_members', array('groupsid' => $groupsid, 'userid' => $userid));
}

/**
 * Returns the list of groups visible to the current user in the given course.
 *
 * The following fields are returned in each record: id, name, contextid, idnumber, visible
 * Fields memberscnt and enrolledcnt will be also returned if requested
 *
 * @param context $currentcontext
 * @param int $withmembers one of the COHORT_XXX constants that allows to return non empty groups only
 *      or groups with enroled/not enroled users, or just return members count
 * @param int $offset
 * @param int $limit
 * @param string $search
 * @return array
 */
function local_groups_get_available_groups($currentcontext, $withmembers = 0, $offset = 0, $limit = 25, $search = '') {
    global $DB;

    $params = array();

    // Build context subquery. Find the list of parent context where user is able to see any or visible-only groups.
    // Since this method is normally called for the current course all parent contexts are already preloaded.
    $contextsany = array_filter($currentcontext->get_parent_context_ids(),
        function($a) {
            return has_capability("moodle/cohort:view", context::instance_by_id($a));
        });
    $contextsvisible = array_diff($currentcontext->get_parent_context_ids(), $contextsany);
    if (empty($contextsany) && empty($contextsvisible)) {
        // User does not have any permissions to view groups.
        return array();
    }
    $subqueries = array();
    if (!empty($contextsany)) {
        list($parentsql, $params1) = $DB->get_in_or_equal($contextsany, SQL_PARAMS_NAMED, 'ctxa');
        $subqueries[] = 'c.contextid ' . $parentsql;
        $params = array_merge($params, $params1);
    }
    if (!empty($contextsvisible)) {
        list($parentsql, $params1) = $DB->get_in_or_equal($contextsvisible, SQL_PARAMS_NAMED, 'ctxv');
        $subqueries[] = '(c.visible = 1 AND c.contextid ' . $parentsql. ')';
        $params = array_merge($params, $params1);
    }
    $wheresql = '(' . implode(' OR ', $subqueries) . ')';

    // Build the rest of the query.
    $fromsql = "";
    $fieldssql = 'c.id, c.name, c.contextid, c.idnumber, c.visible';
    $groupbysql = '';
    $havingsql = '';
    if ($withmembers) {
        $fieldssql .= ', s.memberscnt';
        $subfields = "c.id, COUNT(DISTINCT cm.userid) AS memberscnt";
        $groupbysql = " GROUP BY c.id";
        $fromsql = " LEFT JOIN {cohort_members} cm ON cm.groupsid = c.id ";
        if (in_array($withmembers,
                array(
                    LOCAL_COHORT_COUNT_ENROLLED_MEMBERS,
                    LOCAL_COHORT_WITH_ENROLLED_MEMBERS_ONLY,
                    LOCAL_COHORT_WITH_NOTENROLLED_MEMBERS_ONLY
                )
            )) {
            list($esql, $params2) = get_enrolled_sql($currentcontext);
            $fromsql .= " LEFT JOIN ($esql) u ON u.id = cm.userid ";
            $params = array_merge($params2, $params);
            $fieldssql .= ', s.enrolledcnt';
            $subfields .= ', COUNT(DISTINCT u.id) AS enrolledcnt';
        }
        if ($withmembers == LOCAL_COHORT_WITH_MEMBERS_ONLY) {
            $havingsql = " HAVING COUNT(DISTINCT cm.userid) > 0";
        } else if ($withmembers == LOCAL_COHORT_WITH_ENROLLED_MEMBERS_ONLY) {
            $havingsql = " HAVING COUNT(DISTINCT u.id) > 0";
        } else if ($withmembers == LOCAL_COHORT_WITH_NOTENROLLED_MEMBERS_ONLY) {
            $havingsql = " HAVING COUNT(DISTINCT cm.userid) > COUNT(DISTINCT u.id)";
        }
    }
    if ($search) {
        list($searchsql, $searchparams) = groups_get_search_query($search);
        $wheresql .= ' AND ' . $searchsql;
        $params = array_merge($params, $searchparams);
    }

    if ($withmembers) {
        $sql = "SELECT " . str_replace('c.', 'groups.', $fieldssql) . "
                  FROM {cohort} groups
                  JOIN (SELECT $subfields
                          FROM {cohort} c $fromsql
                         WHERE $wheresql $groupbysql $havingsql
                        ) s ON groups.id = s.id
              ORDER BY groups.name, groups.idnumber";
    } else {
        $sql = "SELECT $fieldssql
                  FROM {cohort} c $fromsql
                 WHERE $wheresql
              ORDER BY c.name, c.idnumber";
    }

    return $DB->get_records_sql($sql, $params, $offset, $limit);
}

/**
 * Check if groups exists and user is allowed to access it from the given context.
 *
 * @param stdClass|int $groupsorid groups object or id
 * @param context $currentcontext current context (course) where visibility is checked
 * @return boolean
 */
function local_groups_can_view_groups($groupsorid, $currentcontext) {
    global $DB;
    if (is_numeric($groupsorid)) {
        $groups = $DB->get_record('cohort', array('id' => $groupsorid), 'id, contextid, visible');
    } else {
        $groups = $groupsorid;
    }

    if ($groups && in_array($groups->contextid, $currentcontext->get_parent_context_ids())) {
        if ($groups->visible) {
            return true;
        }
        $groupscontext = context::instance_by_id($groups->contextid);
        if (has_capability('moodle/cohort:view', $groupscontext)) {
            return true;
        }
    }
    return false;
}

/**
 * Get a groups by id. Also does a visibility check and returns false if the user cannot see this groups.
 *
 * @param stdClass|int $groupsorid groups object or id
 * @param context $currentcontext current context (course) where visibility is checked
 * @return stdClass|boolean
 */
function local_groups_get_group($groupsorid, $currentcontext) {
    global $DB;
    if (is_numeric($groupsorid)) {
        $groups = $DB->get_record('cohort', array('id' => $groupsorid), 'id, contextid, visible');
    } else {
        $groups = $groupsorid;
    }

    if ($groups && in_array($groups->contextid, $currentcontext->get_parent_context_ids())) {
        if ($groups->visible) {
            return $groups;
        }
        $groupscontext = context::instance_by_id($groups->contextid);
        if (has_capability('moodle/cohort:view', $groupscontext)) {
            return $groups;
        }
    }
    return false;
}

/**
 * Produces a part of SQL query to filter groups by the search string
 *
 * Called from {@link groups_get_available_groups()}
 *
 * @access private
 *
 * @param string $search search string
 * @param string $tablealias alias of groups table in the SQL query (highly recommended if other tables are used in query)
 * @return array of two elements - SQL condition and array of named parameters
 */
function local_groups_get_search_query($search, $tablealias = '') {
    global $DB;
    $params = array();
    if (empty($search)) {
        // This function should not be called if there is no search string, just in case return dummy query.
        return array('1=1', $params);
    }
    if ($tablealias && substr($tablealias, -1) !== '.') {
        $tablealias .= '.';
    }
    $searchparam = '%' . $DB->sql_like_escape($search) . '%';
    $conditions = array();
    $fields = array('name', 'idnumber', 'description');
    $cnt = 0;
    foreach ($fields as $field) {
        $conditions[] = $DB->sql_like($tablealias . $field, ':csearch' . $cnt, false);
        $params['csearch' . $cnt] = $searchparam;
        $cnt++;
    }
    $sql = '(' . implode(' OR ', $conditions) . ')';
    return array($sql, $params);
}

/**
 * Get all the groups defined in given context.
 *
 * The function does not check user capability to view/manage groups in the given context
 * assuming that it has been already verified.
 *
 * @param int $contextid
 * @param int $page number of the current page
 * @param int $perpage items per page
 * @param string $search search string
 * @return array    Array(totalgroups => int, groups => array, allgroups => int)
 */
function local_groups_get_groups($contextid, $page = 0, $perpage = 25, $search = '') {
     global $DB, $USER;
     $fields = "SELECT c.*";
     $countfields = "SELECT COUNT(1)";
     $sql = " FROM {cohort} c, {local_groups} g
              WHERE g.cohortid = c.id AND contextid = :contextid";
     $context = context_system::instance();
    if ( has_capability('local/costcenter:manage_multiorganizations', $context )) {
        $costcenters = $DB->get_records_sql_menu('SELECT fullname, id
                                                    FROM {local_costcenter}
                                                  WHERE parentid = 0 ');
        if (!empty($costcenters)) {
            $mycostcenters = implode(',', $costcenters);
            $sql .= " and g.costcenterid IN( $mycostcenters )";
        }
    } else if (has_capability('local/costcenter:manage_ownorganization', $context)) {
        $costcenter = $DB->get_record_sql("SELECT cc.id, cc.parentid
                                            FROM {user} u
                                            JOIN {local_costcenter} cc ON u.open_costcenterid = cc.id
                                           WHERE u.id={$USER->id}");
        if ($costcenter->parentid == 0) {
            $sql .= " and g.costcenterid IN( $costcenter->id )";
        } else {
            $sql .= " AND ( CONCAT(',',$costcenter->id,',') LIKE CONCAT('%,',g.departmentid,',%') )  ";
        }
    } else {
        $sql .= " AND ( CONCAT(',',$USER->open_departmentid,',') LIKE CONCAT('%,',g.departmentid,',%') )  ";
    }
    $params = array('contextid' => $contextid);
    $order = " ORDER BY  g.id DESC";
    if (isset($search)) {
        $sql .= " AND c.name LIKE '%".trim($search)."%'";
    }
    $totalgroups = $allgroups = $DB->count_records('cohort', array('contextid' => $contextid));
    if (!empty($search)) {
        $totalgroups = $DB->count_records_sql($countfields . $sql, $params);
    }
        $groups = $DB->get_records_sql($fields . $sql . $order, $params, $page, $perpage);

    return array('totalgroups' => $totalgroups, 'groups' => $groups, 'allgroups' => $allgroups);
}

/**
 * Returns navigation controls (tabtree) to be displayed on groups management pages
 *
 * @param context $context system or category context where groups controls are about to be displayed
 * @param moodle_url $currenturl
 * @return null|`able
 */
function local_groups_edit_controls(context $context, moodle_url $currenturl) {
    $tabs = array();
    $currenttab = 'view';
    $viewurl = new moodle_url('/local/groups/index.php', array('contextid' => $context->id));
    if (($searchquery = $currenturl->get_param('search'))) {
        $viewurl->param('search', $searchquery);
    }
    if ($context->contextlevel == CONTEXT_SYSTEM) {
        $tabs[] = new tabobject('view', new moodle_url($viewurl, array('showall' => 0)), get_string('cohorts', 'local_groups'));

    } else {
        $tabs[] = new tabobject('view', $viewurl, get_string('cohort', 'local_groups'));
    }
    if (has_capability('moodle/cohort:manage', $context)) {
        $addurl = new moodle_url('/local/groups/edit.php', array('contextid' => $context->id));
        $tabs[] = new tabobject('addgroups', $addurl, get_string('addcohort', 'local_groups'));
        if ($currenturl->get_path() === $addurl->get_path() && !$currenturl->param('id')) {
            $currenttab = 'addgroups';
        }

    }
    if (count($tabs) > 1) {
        return new tabtree($tabs, $currenttab);
    }
    return null;
}


/**
 * [available_enrolled_users description]
 * @param  string  $type       [description]
 * @param  integer $groupid [description]
 * @param  [type]  $params     [description]
 * @param  integer $total      [description]
 * @param  integer $offset1    [description]
 * @param  integer $perpage    [description]
 * @param  integer $lastitem   [description]
 * @return [type]              [description]
 */
function local_group_users($type = null, $groupid = 0, $params, $total=0, $offset1=-1, $perpage=-1, $lastitem=0) {

    global $DB, $USER;

    $context = context_system::instance();
    $group = $DB->get_record('cohort', array('id' => $groupid));
    $params['suspended'] = 0;
    $params['deleted'] = 0;

    if ($total == 0) {
        $sql = "SELECT u.id,concat(u.firstname,' ',u.lastname,' ','(',u.email,')') as fullname";
    } else {
        $sql = "SELECT count(u.id) as total";
    }
    $sql .= " FROM {user} u
            WHERE  u.id > 2 AND u.suspended = :suspended AND u.deleted = :deleted AND (open_type=1)";
    if ($lastitem != 0) {
        $sql .= " AND u.id > $lastitem";
    }

    if (!is_siteadmin()) {
        $userdetail = $DB->get_record('user', array('id' => $USER->id));
        $sql .= " AND u.open_costcenterid = :costcenter";
        $params['costcenter'] = $userdetail->open_costcenterid;
        if (has_capability('local/costcenter:manage_owndepartments', $context) &&
                !has_capability('local/costcenter:manage_ownorganization', $context)) {
            $sql .= " AND u.open_departmentid = :department";
            $params['department'] = $userdetail->open_departmentid;
        }
    }
    if (!empty($params['email'])) {
        $sql .= " AND u.id IN ({$params['email']})";
    }
    if (!empty($params['uname'])) {
        $sql .= " AND u.id IN ({$params['uname']})";
    }
    if (!empty($params['department'])) {
        $sql .= " AND u.open_departmentid IN ({$params['department']})";
    }
    if (!empty($params['organization'])) {
        $sql .= " AND u.open_costcenterid IN ({$params['organization']})";
    }
    if (!empty($params['idnumber'])) {
        $sql .= " AND u.id IN ({$params['idnumber']})";
    }
    if (!empty($params['groups'])) {
        $query = "SELECT usr.id
                FROM {user} usr
                JOIN {cohort_members} cm ON cm.userid=usr.id
                WHERE cm.cohortid IN ({$params['groups']}) ";

        $groupusers = $DB->get_records_sql_menu($query);

        if ($userslist) {
            $userslist = implode(',', $groupusers);
            $sql .= " AND u.id IN ($userslist)";
        }
    }

    if ($type == 'add') {
        $sql .= " AND u.id NOT IN (SELECT userid FROM {cohort_members})";

    } else if ($type == 'remove') {
        $sql .= " AND u.id IN (SELECT userid FROM {cohort_members} WHERE cohortid = $groupid)";
    }
    $order = ' ORDER BY u.id ASC ';

    if ($total == 0) {
        $availableusers = $DB->get_records_sql_menu($sql.$order, $params);
    } else {
        $availableusers = $DB->count_records_sql($sql, $params);
    }

    return $availableusers;
}

/**
 * Implements callback inplace_editable() allowing to edit values in-place
 *
 * @param string $itemtype
 * @param int $itemid
 * @param mixed $newvalue
 * @return \core\output\inplace_editable
 */
function local_core_groups_inplace_editable($itemtype, $itemid, $newvalue) {
    if ($itemtype === 'groupsname') {
        return \core_groups\output\groupsname::update($itemid, $newvalue);
    } else if ($itemtype === 'groupsidnumber') {
        return \core_groups\output\groupsidnumber::update($itemid, $newvalue);
    }
}


/**
 * [groups_filter form element function]
 * @param  [form] $mform [filter form]
 * @return
 */
function groups_filter($mform, $query='', $searchanywhere=false, $page=0, $perpage=25) {
 
    global $DB, $USER;
    $systemcontext = context_system::instance();
    $groupslist = array();
    $data = data_submitted();

    if (is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext) ) {
        $groupslistsql = "SELECT c.id, c.name as fullname
                            FROM {local_groups} g, {cohort} c
                           WHERE c.visible = 1 AND c.id = g.cohortid ";
    } else if (has_capability('local/costcenter:manage_ownorganization', $systemcontext)) {

        $groupslistsql = "SELECT c.id, c.name as fullname
                            FROM {local_groups} g, {cohort} c
                           WHERE c.visible = 1 AND c.id = g.cohortid
                            AND g.costcenterid IN( $USER->open_costcenterid )";

    } else if (has_capability('local/costcenter:manage_owndepartments', $systemcontext)) {
        $groupslistsql = "SELECT c.id, c.name as fullname
                            FROM {local_groups} g, {cohort} c
                           WHERE c.visible = 1 AND c.id = g.cohortid
                            AND ( CONCAT(',',$USER->open_departmentid,',') LIKE CONCAT('%,',g.departmentid,',%') ) ";
    }
    if (!empty($query)) {
        if ($searchanywhere) {
            $groupslistsql .= " AND c.name LIKE '%$query%' ";
        } else {
            $groupslistsql .= " AND c.name LIKE '$query%' ";
        }
    }
    if (isset($data->groups) && $data->groups != "_qf__force_multiselect_submission" && !empty(($data->groups))) {
        $implode = implode(',', $data->groups);

        $groupslistsql .= " AND c.id in ($implode)";
    }
    if (!empty($query) || empty($mform)) {
        $groupslist = $DB->get_records_sql($groupslistsql, array(), $page, $perpage);
        return $groupslist;
    }
    if ((isset($data->groups) && !empty($data->groups))) {
        $groupslist = $DB->get_records_sql_menu($groupslistsql, array(), $page, $perpage);
    }

    $options = array(
        'ajax' => 'local_courses/form-options-selector',
        'multiple' => true,
        'data-action' => 'groups',
        'data-options' => json_encode(array('id' => 0)),
        'placeholder' => get_string('cohort', 'local_groups')
     );
    $selectbatch = get_string('selectbatch', 'local_groups');
    $groupslist = [0 => $selectbatch] + $groupslist;
    $select = $mform->addElement('autocomplete', 'groups', '', $groupslist, $options);

    $mform->setType('groups', PARAM_INT);
}
/*
* Displays a node in left side menu
* @return  [type] string  link for the leftmenu
*/
function local_groups_leftmenunode() {
    $systemcontext = context_system::instance();
    $groupnode = '';
    if (has_capability('moodle/cohort:manage', $systemcontext) || is_siteadmin()) {
        $groupnode .= html_writer::start_tag('li', array('id' => 'id_leftmenu_groups', 'class' => 'pull-left user_nav_div users'));
            $usersurl = new moodle_url('/local/groups/index.php');
            $users = html_writer::link($usersurl,
                                        '<span class="grp_wht_structure_icon dypatil_cmn_icon icon">
                                        </span>
                                        <span class="user_navigation_link_text">
                                            '.get_string('leftmenu_groups', 'local_groups').'
                                        </span>', array('class' => 'user_navigation_link')
                                    );
            $groupnode .= $users;
        $groupnode .= html_writer::end_tag('li');
    }

    return array('5' => $groupnode);
}
/**
 * process the groups_mass_enroll
 * @param csv_import_reader $cir  an import reader created by caller
 * @param Object $groups  a groups record from table mdl_local_groups
 * @param Object $context  course context instance
 * @param Object $data    data from a moodleform
 * @return string  log of operations
 */
function groups_mass_enroll($cir, $groups, $context, $data) {
    global $CFG, $DB, $USER;
    $result = '';
    $groupname = $groups->name;
    require_once($CFG->dirroot . '/group/lib.php');
    // Init csv import helper.
    $useridfield = $data->firstcolumn;
    $cir->init();
    $enrollablecount = 0;
    $enrolledusergroupnamesql = "SELECT u.id, u.email
                                    FROM {cohort} c
                                    JOIN {cohort_members} cm ON c.id = cm.cohortid
                                    JOIN {user} u ON cm.userid = u.id
                                    WHERE u.id = cm.userid AND cm.cohortid != $groups->id";
    $enrolledusergroupname = $DB->get_records_sql_menu($enrolledusergroupnamesql);
    $useremail = array_values($enrolledusergroupname);
    $emailkeyvalue = array_chunk($useremail, 1);
    $emailkey = array_values($emailkeyvalue);
    $emailvalue = array();
    foreach ($emailkey as $key => $emailkeys) {
        $emailvalue[] = $emailkeys[0];
    }
    $emailvalue = array_flip($emailvalue);
    while ($fields = $cir->next()) {
        $a = new stdClass();
        if (empty ($fields)) {
            continue;
        }
        $fields[0] = str_replace('"', '', trim($fields[0]));
        /*First Condition To validate users*/
        $sql = "SELECT u.*
                 FROM {user} u
                WHERE u.deleted=0 and u.suspended=0 and u.$useridfield='$fields[0]'";
        $systemcontext = \context_system::instance();
        if (!(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext))) {
            $sql .= " AND u.open_costcenterid = {$USER->open_costcenterid} ";
            if (!has_capability('local/costcenter:manage_ownorganization', $systemcontext)) {
                $sql .= " AND u.open_departmentid = {$USER->open_departmentid} ";
            }
        }
        if (!$user = $DB->get_record_sql($sql)) {
            $result .= '<div class="local_groups_sync_error">'
                            .get_string('im:user_unknown', 'local_courses', $fields[0] ).
                        '</div>';
            continue;
        } else {
            if ($DB->record_exists('cohort_members', array('userid' => $user->id))) {
                $groupdata = new stdClass();
                $groupdata->emailid = $fields[0];
                if (array_key_exists($fields[0], $emailvalue)) {
                    $groupdata->groupname = get_string('other', 'local_groups');
                    $result .= '<div class="local_groups_sync_warning">'
                                .get_string('user_not_exist', 'local_groups', $groupdata, 'warnings' ).
                            '</div>';
                } else {
                    $groupdata->groupname = $groupname;
                    $result .= '<div class="local_groups_sync_warning">'
                                .get_string('user_exist', 'local_groups', $groupdata, 'warnings' ).
                            '</div>';
                }
                continue;
            } else {
                $record = new stdClass();
                $record->cohortid  = $groups->id;
                $record->userid    = $user->id;
                $record->timeadded = time();
                $resprogramid = $DB->get_field('local_program', 'id', array('batchid' => $groups->id));
                $insertdata = new stdClass();
                $insertdata->programid = $resprogramid;
                $insertdata->userid = $user->id;
                $insertdata->usercreated = $USER->id;
                $insertdata->usermodified = $USER->id;
                $insertdata->supervisorid = 0;
                $insertdata->hours = 0;
                $insertdata->timecreated = time();
                $insertdata->timemodified = time();
                $DB->insert_record('local_program_users', $insertdata);
                $DB->insert_record('cohort_members', $record);
                $result .= '<div class="alert alert-success">'
                            .get_string('im:enrolled_ok', 'local_courses', fullname($user)).
                            '</div>';

                $enrollablecount ++;
            }
        }
    }
    $result .= '<br/>';
    $result .= get_string('im:stats_i', 'local_groups', $enrollablecount) . "";
    return $result;
}
function local_groups_output_fragment_new_groupsform($args) {
    global $CFG, $DB;
    $args = (object) $args;

    $o = '';
    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        parse_str($serialiseddata, $formdata);
    }
    $editoroptions = [
        'maxfiles' => EDITOR_UNLIMITED_FILES,
        'maxbytes' => $course->maxbytes,
        'trust' => false,
        'context' => $context,
        'noclean' => true,
        'subdirs' => false
    ];
    $group = file_prepare_standard_editor($group, 'description', $editoroptions, $context, 'group', 'description', null);
    if ($args->groupsid > 0) {
        $heading = get_string('libupdategroup', 'local_groups');
        $collapse = false;
        $data = $DB->get_record('cohort', array('id' => $args->groupsid));

        $data->description_editor['text'] = $data->description;
        $data->description_editor['format'] = FORMAT_HTML;

        $groupsdata = $DB->get_record('local_groups', array('cohortid' => $data->id));

        $data->open_costcenterid = $groupsdata->costcenterid;
        $data->open_departmentid = $groupsdata->departmentid;
        $data->open_subdepartment = $groupsdata->subdepartmentid;

        $mform = new local_groups\form\edit_form(null, array(
                                                        'editoroptions' => $editoroptions,
                                                        'id' => $data->id,
                                                        'costcenterid' => $groupsdata->costcenterid,
                                                        'deptid' => $groupsdata->departmentid,
                                                        'subdept' => $groupsdata->subdepartmentid
                                                    ), 'post', '', null, true, $formdata
                                                );
        $mform->set_data($data);
    } else {
        $mform = new local_groups\form\edit_form(null, array('editoroptions' => $editoroptions), 'post', '', null, true, $formdata);
    }
    if (!empty($formdata)) {
        // If we were passed non-empty form data we want the mform to call validation functions and show errors.
        $mform->is_validated();
    }

    ob_start();
    $mform->display();
    $o .= ob_get_contents();
    ob_end_clean();
    return $o;
}


function local_groups_output_fragment_batchuser_display($args)
{


    global $DB, $CFG, $PAGE, $OUTPUT, $USER;

   $PAGE->requires->js_call_amd('local_program/programcompletion','load');
    $ctid = $args['context']->id;
    $batchid = $args['batchid'];
    $templatedata =  array();
    $rowdata = array();
  $userquery = $DB->get_records_sql("SELECT userid from {cohort_members} where cohortid = '$batchid'");
    foreach($userquery as $a)
    {
        $arr[] = $a->userid;
    }
   $arstring = implode(',',$arr);

 if(!$arr){
    $arstring = '0';
 }

 
   $userallquery = $DB->get_records_sql("SELECT *  FROM {user} WHERE id IN ($arstring)");


        $templatedata['enabletable'] = true;

    foreach($userallquery as $a)
    { 
       $programid = $DB->get_field('local_program_users', 'programid', array('userid' => $a->id));
        $total = ("SELECT COUNT(programid)
                    FROM {local_program_levels} c WHERE programid = '$programid'");
            $totalusers   = $DB->count_records_sql($total);
   $pldata = $DB->get_records_sql("SELECT enddate AS enddate ,level  FROM {local_program_levels} WHERE programid = '$programid' AND enddate != 0 ");

       $cdate = time();

    $i= 0;
   foreach($pldata as $t)
   {
        if($cdate >$t->enddate)
        { $i++;
            
        }
   }
  //  preg_match_all('!\d+!', $lv, $matches);

     $semnumber = $i;//$matches[0][0];
 
     $tt = $semnumber*100;
     $per = $tt/$totalusers;

  $core_component = new \core_component();
      $certificate_plugin_exist = $core_component::get_plugin_directory('tool', 'certificate');
        if ($certificate_plugin_exist) {
            $certid = $DB->get_field('local_program', 'certificateid', array('id'=>$programid));
        } else {
            $certid = false;
        }
            $sql = "SELECT id, programid, userid, completionstatus
                        FROM {local_programcompletions}
                        WHERE programid = :programid AND userid = :userid
                        AND completionstatus != 0 ";

 $completed = $DB->record_exists_sql($sql, array('programid'=>$programid, 'userid'=>$a->id));
        


          if($certid) {
                    $icon = '<i class="icon fa fa-download" aria-hidden="true"></i>';
                    if($completed) {

                       $certcode = $DB->get_field('tool_certificate_issues', 'code', array('moduleid'=>$programid,'userid'=>$sdata->id,'moduletype'=>'program'));

                       $array = array('preview'=>1, 'templateid'=>$certid,'code'=>'previewing');

                        $url = new moodle_url('../../admin/tool/certificate/view.php?', $array);

                        $downloadlink = html_writer::link($url, $icon, array('title'=>get_string('download_certificate','tool_certificate')));
                    } else {
                        $url = 'javascript: void(0)';

                        $downloadlink = html_writer::tag($url, get_string('nodata', 'local_program'));
                    }


 
                    $line[] =  $downloadlink;
                }
 
            $name = $a->firstname.$a->lastname;
            $rowdata['name'] = $name;
            $rowdata['email'] = $a->email;
            $rowdata['city'] = $city;
            $rowdata['per'] = $per;
             $rowdata['roleid'] = $a->id;
            $rowdata['downloadlink'] = $downloadlink;
            $templatedata['rowdata'][] = $rowdata;
          }
          

$templatedata['batchid'] = $batchid;


  $output = $OUTPUT->render_from_template('local_groups/popupcontent', $templatedata);
   return $output;
}


function local_groups_output_fragment_batchprogram_display($args)
{
  global $DB, $CFG, $PAGE, $OUTPUT, $USER;
    $ctid = $args['context']->id;
         $programid = $args['roleid'];
    if(!$programid){
       $programid = 0;
    }


   $query =  $DB->get_records_sql("SELECT p.name,p.shortname,p.duration,p.description,p.totalcourses,p.course_elective,p.prerequisite,COUNT(lpl.programid) AS semester,lc.name AS curr_name , lc.shortname AS curr_shortname,date(from_unixtime(p.startdate)) AS startdate,date(from_unixtime(p.startdate)) AS enddate FROM {local_program} AS p 
    JOIN {local_program_levels} lpl ON lpl.programid = p.id 
    JOIN {local_curriculum} lc ON lc.id = p.curriculumid 
    WHERE p.id = $programid GROUP BY lpl.programid");
  


  $result = $DB->get_records_sql("SELECT userid FROM {local_program_users} WHERE programid = $programid");
    

    foreach($result as $t){
        $userarray[] = $t->userid;
    }
    $userarrstring = implode(',',$userarray);

if(!$userarray){
    $userarrstring = '0';
 }
    $groupcohortid    = $DB->get_records_sql("SELECT cohortid FROM {cohort_members} WHERE userid IN ($userarrstring)");
   foreach($groupcohortid as $gs)
   {
      $cohortid = $gs->cohortid;
   }
   $core_component = new \core_component();
      $certificate_plugin_exist = $core_component::get_plugin_directory('tool', 'certificate');
        if ($certificate_plugin_exist) {
            $certid = $DB->get_field('local_program', 'certificateid', array('id'=>$programid));
        } else {
            $certid = false;
        }
            $sql = "SELECT id, programid, userid, completionstatus
                        FROM {local_programcompletions}
                        WHERE programid = :programid  
                        AND completionstatus != 0 ";
$completed = $DB->record_exists_sql($sql, array('programid'=>$programid));

          if($certid) {
                    $icon = '<i class="icon fa fa-download" aria-hidden="true"></i>';
                    if($completed) {
                       $certcode = $DB->get_field('tool_certificate_issues', 'code', array('moduleid'=>$programid,'userid'=>$sdata->id,'moduletype'=>'program'));
                       $array = array('preview'=>1, 'templateid'=>$certid,'code'=>'previewing');
                        $url = new moodle_url('../../admin/tool/certificate/view.php?', $array);
                        $downloadlink = html_writer::link($url, $icon, array('title'=>get_string('download_certificate','tool_certificate')));
                    } else {
                        $url = 'javascript: void(0)';
                        $downloadlink = html_writer::tag($url, get_string('nodata', 'local_program'));
                    }
                    $line[] =  $downloadlink;
                }
            $templatedata =  array();

 
 
    foreach($query as $a){
    $countuser = $DB->get_records_sql("SELECT count(userid) AS enroll_user FROM {local_program_users} WHERE programid = $programid");

 foreach($countuser as $cu){
    $enroll_user = $cu->enroll_user;
 }
   $stdate = $DB->get_records_sql("SELECT   date(from_unixtime(startdate)) AS startddate FROM {local_program_levels} WHERE programid = $programid  ORDER BY id ASC LIMIT 1");

         foreach($stdate as $c){
            $startdata = $c->startddate;
         }

if($startdata == '1970-01-01')
{
    $stdate = 'N/A';
}
else{
    $stdate = $startdata;
}

$du = $a->duration;
 $date = $stdate;
$date = strtotime($date);
$new_date = strtotime('+ '.$du.' year', $date);
$enddate =  date('Y-m-d', $new_date);
 
 if($enddate == '1970-01-01')
{
    $enddate = 'N/A';
}
else{
    $enddate = $enddate;
}

$leveldata = $DB->get_records_sql("SELECT  courseid ,parentid, mandatory FROM {local_program_level_courses} WHERE programid = $programid");

 
 foreach($leveldata as $c)
 {
    $arr[] = $c->courseid;
    $parentid = $c->parentid;
    
        if($c->mandatory == 1){
            $coursetypearr[] = $c->mandatory;
            $cparentarr[] = $c->parentid;
        }
        else{
            $coursetypearre[] = $c->mandatory;
            $eparentarr[] = $c->parentid;
        }
    
    $coursecontext = $DB->get_field('context','id', array('instanceid' => $c->courseid, 'contextlevel' =>50));

         $instructors = $DB->get_records_sql("SELECT u.username,u.id FROM {user} u JOIN {role_assignments} ra ON ra.userid = u.id WHERE ra.roleid = 3 AND ra.contextid = $coursecontext");
             
             $k = 0;
            foreach($instructors as $key){
            $userrecord = $DB->get_record('user', array('id' => $key->id));
            $user_image = $OUTPUT->user_picture($userrecord, array('size' => 40, 'link' => false));
             
          $imgearr[$k]['userimage'] =$user_image;//$instructors;
          $imgearr[$k]['username'] =$userrecord->username;
          $k++;
        }
        
 }

   $elactvalue = implode(',',$eparentarr);
   $corevalues = implode(',',$cparentarr);
   $noofcourses = count($arr);
   $core = count($coursetypearr);
   $elactive = count($coursetypearre);
 
  if(!empty($cparentarr)){
    $coredata = $DB->get_records_sql("SELECT  fullname FROM {course} WHERE id IN ($corevalues)");
    foreach($coredata as $co){
            $coursefullname[] = $co->fullname;
         }
$coursefullname = implode(',',$coursefullname);
}else{
    $coursefullname = "N/A";
}
if(!empty($eparentarr)){
     $elactivedata = $DB->get_records_sql("SELECT fullname FROM {course} WHERE id IN ($elactvalue)");
     foreach($elactivedata as $eo){
            $ecoursefullname[] = $eo->fullname;
         }
$ecoursefullname = implode(',',$ecoursefullname);
 }else{
    $ecoursefullname = 'N/A';
 }
            $name = $a->firstname.$a->lastname;
            $rowdata['name'] = $a->name;
            $rowdata['shortname'] = $a->shortname;
            $rowdata['description']  = strip_tags($a->description);
            $rowdata['startdate'] = $stdate;
            $rowdata['enddate'] = $enddate;
            $rowdata['prerequisite'] = $a->prerequisite;
            $rowdata['course_elective'] = $a->course_elective;
            $rowdata['enroll_user'] = $enroll_user;
            $rowdata['semester'] = $a->semester;
            $rowdata['curr_name'] = $a->curr_name;
            $rowdata['noofcourses'] = $noofcourses;
            $rowdata['core'] = $core;
            $rowdata['elactive'] = $elactive;
            $rowdata['batchid'] = $cohortid;
            $rowdata['coursefullname'] = $coursefullname;
            $rowdata['ecoursefullname'] = $ecoursefullname;
            $rowdata['downloadlink'] = $downloadlink;
            $rowdata['instructor'] = array_values($imgearr);
            $templatedata['rowdata'][] = $rowdata;      
}


 if(count($templatedata)>0){
  $output = $OUTPUT->render_from_template('local_groups/popupprogramcontent', $templatedata);
}
else{
    $output = "there is no data";
}
   return $output;


}

function local_groups_output_fragment_batchprogress_display($args)
{

        global $DB, $PAGE,$USER,$CFG,$OUTPUT;
        $programid = $args['roleid'];
 
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
                            ROUND(100 / (SELECT COUNT(*) FROM {course_modules} cm WHERE cm.course = bclc.courseid) ) *
                            (SELECT COUNT(cmc.id) FROM {course_modules_completion} cmc
                            LEFT JOIN {course_modules} cm ON cmc.coursemoduleid = cm.id
                            WHERE  cm.course = bclc.courseid and cmc.userid = u.id)
                        )AS course_progress
                        
                        FROM {local_program_level_courses} bclc
                        JOIN {user} u
                        JOIN {user_enrolments} ue ON ue.userid=u.id
                        JOIN {enrol} e ON e.id=ue.enrolid
                        JOIN {course} c ON c.id = e.courseid and c.id = bclc.courseid
                        JOIN {local_cc_semester_courses} cc ON cc.open_parentcourseid =  bclc.parentid
                       JOIN {local_program_levels} lpl ON lpl.id = bclc.levelid and lpl.programid = bclc.programid WHERE u.id =  $programid";
        
        $queryparam = array();
        if(is_siteadmin()){
       }
        else {
          print_error('You dont have permissions to view this page.');
              die();
        }


        //$concatsql=" order by c.id desc";
       // $count = $DB->count_records_sql($countsql.$concatsql, $queryparam);
 
        $semester_info = $DB->get_records_sql($selectsql, $queryparam, $tablelimits->start, $tablelimits->length);
 
       $list=array();
        $data = array();
        if ($semester_info) {

            foreach ($semester_info as $sem_detail) {
                $list['id'] = $sem_detail->programcourseid;
                $list['fullname'] = $sem_detail->course;
                $list['semester'] = $sem_detail->levelname;
                $list['roleid'] = $programid;


               if($sem_detail->ctype == 0){
                       $sem_detail->ctype = 'Elective';
                }
                else {
                        $sem_detail->ctype = 'Core';
                }
               $list['ctype'] = $sem_detail->ctype;
               $list['per'] = $sem_detail->course_progress;
                
              // $data[] = $list;
               $templatedata['rowdata'][] = $list;
            }
        }

  $output = $OUTPUT->render_from_template('local_groups/popupprogresscontent', $templatedata);
   return $output;
       
}
