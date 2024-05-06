<?php

defined('MOODLE_INTERNAL') || die();

function xmldb_block_queries_upgrade($oldversion) {
    global $DB, $CFG;

    $dbman = $DB->get_manager();
    if ($oldversion < 2019101103.03) {
        $table = new xmldb_table('block_queries');
        $field1 = new xmldb_field('viewed', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null);
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }
        upgrade_plugin_savepoint(true, 2019101103.03, 'block', 'queries');
    }
    return true;
}