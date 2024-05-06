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
 * Upgrade scirpt for blocks_proposals
 *
 * @package    blocks_proposals
 * @copyright  2014 onwards Ankit Agarwal <ankit.agrr@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade the plugin.
 *
 * @param int $oldversion
 * @return bool always true
 */
function xmldb_block_proposals_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

 

    if ($oldversion < 2021120620) {
           
        $table = new xmldb_table('submissions');

        $field = new xmldb_field('time', XMLDB_TYPE_CHAR, '50', null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2021120620, 'block', 'proposals');
    } 
    if ($oldversion < 2021120621) {
           
        $table = new xmldb_table('submissions');

        $field = new xmldb_field('countrev', XMLDB_TYPE_INTEGER, '20', null, null, null,'0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2021120621, 'block', 'proposals');
    }

    if ($oldversion < 2021120622) {
           
        $table = new xmldb_table('submissions');

        $field = new xmldb_field('coinvestigator1', XMLDB_TYPE_CHAR, '30', null, null, null,null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('coinvestigator2', XMLDB_TYPE_CHAR, '30', null, null, null,null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('coinvestigator3', XMLDB_TYPE_CHAR, '30', null, null, null,null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('coinvestigator4', XMLDB_TYPE_CHAR, '30', null, null, null,null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('coinvestigator5', XMLDB_TYPE_CHAR, '30', null, null, null,null);

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // coguidename
        $field = new xmldb_field('coguidename', XMLDB_TYPE_CHAR, '30', null, null, null,null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2021120622, 'block', 'proposals');
    }


    if ($oldversion < 2021120623) {
           
        $table = new xmldb_table('submissions');

        $field = new xmldb_field('methodofsamplingother', XMLDB_TYPE_CHAR, '100', null, null, null,null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('startdate', XMLDB_TYPE_INTEGER, '20', null, null, null,null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('enddate', XMLDB_TYPE_INTEGER, '20', null, null, null,null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('casereportform', XMLDB_TYPE_INTEGER, '20', null, null, null,null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('studyprocedurefile', XMLDB_TYPE_INTEGER, '20', null, null, null,null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('samplesizejustrificationfile', XMLDB_TYPE_INTEGER, '20', null, null, null,null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('interventionother', XMLDB_TYPE_CHAR, '250', null, null, null,null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('otherquestionnairesone', XMLDB_TYPE_INTEGER, '10', null, null, null,null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('otherquestionnairestwo', XMLDB_TYPE_INTEGER, '10', null, null, null,null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('otherquestionnairesthr', XMLDB_TYPE_INTEGER, '10', null, null, null,null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('otherquestionnairesfou', XMLDB_TYPE_INTEGER, '10', null, null, null,null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('otherquestionnairesfiv', XMLDB_TYPE_INTEGER, '10', null, null, null,null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        } 

        $field = new xmldb_field('guidename', XMLDB_TYPE_CHAR, '50', null, null, null,null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('approveronestatus', XMLDB_TYPE_INTEGER, '10', null, null, null,null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('approvertwostatus', XMLDB_TYPE_INTEGER, '10', null, null, null,null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2021120623, 'block', 'proposals');

        return true;
     }

    //  if ($oldversion < 2021120624) {
           
    //     $table = new xmldb_table('user');

    //     $field = new xmldb_field('applicationid', XMLDB_TYPE_CHAR, '10', null, null, null,null);
    //     if (!$dbman->field_exists($table, $field)) {
    //         $dbman->add_field($table, $field);
    //     }
    //     $field = new xmldb_field('deptid', XMLDB_TYPE_INTEGER, '10', null, null, null,null);
    //     if (!$dbman->field_exists($table, $field)) {
    //         $dbman->add_field($table, $field);
    //     }
    //     $field = new xmldb_field('levelofapprove', XMLDB_TYPE_INTEGER, '10', null, null, null,null);
    //     if (!$dbman->field_exists($table, $field)) {
    //         $dbman->add_field($table, $field);
    //     }
    //     $field = new xmldb_field('roleid', XMLDB_TYPE_INTEGER, '10', null, null, null,null);
    //     if (!$dbman->field_exists($table, $field)) {
    //         $dbman->add_field($table, $field);
    //     }

    //     upgrade_plugin_savepoint(true, 2021120624, 'block', 'proposals');
    // }
    return true;
}

