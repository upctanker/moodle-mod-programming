<?php

// This file keeps track of upgrades to 
// the programming module
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installtion to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the functions defined in lib/ddllib.php

function xmldb_programming_upgrade($oldversion=0) {

    global $CFG, $THEME, $db;

    $result = true;

/// And upgrade begins here. For each one, you'll need one 
/// block of code similar to the next one. Please, delete 
/// this comment lines once this file start handling proper
/// upgrade code.

/// if ($result && $oldversion < YYYYMMDD00) { //New version in version.php
///     $result = result of "/lib/ddllib.php" function calls
/// }

    if ($result && $oldversion < 2007030706) {

        $table = new XMLDBTable('programming_resemble');
        $idx = new XMLDBIndex('progflagid');
        $idx->setUnique();
        $idx->setFields(array('programmingid', 'flag', 'id'));
        $result = add_index($table, $idx);

    }

    if ($result && $oldversion < 2007031801) {

    /// Add field to table programming_submits
        $table = new XMLDBTable('programming_submits');
        $field = new XMLDBField('passed');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, XMLDB_UNSIGNED, $notnull=null, $sequence=null, $enum=null, $enumvalues=null, $default=null, $previous='compilemessage');
        $result = add_field($table, $field);

    /// Define table programming_result to be created
        $table = new XMLDBTable('programming_result');

    /// Adding fields to table programming_result
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('programmingid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('userid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('submitcount', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('latestsubmitid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);

    /// Adding keys to table programming_result
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));

    /// Adding indexes to table programming_result
        $table->addIndexInfo('programming-user', XMLDB_INDEX_NOTUNIQUE, array('programmingid', 'userid'));

    /// Launch create table for programming_result
        $result = $result && create_table($table);
    }

    if ($result && $oldversion < 2007031802) {
    /// Change data
        $ps = get_records('programming');
        if (!is_array($ps)) $ps = array();
        foreach ($ps as $pid => $p) {
            $users = array();
            $tc = count_records('programming_tests', 'programmingid', $pid);
            $sql = "SELECT * from {$CFG->prefix}programming_submits
                     WHERE programmingid={$pid} ORDER BY id";
            $rs = $db->Execute($sql);
            while ($row = rs_fetch_next_record($rs)) {
                if (array_key_exists($row->userid, $users)) {
                    $user = $users[$row->userid];
                } else {
                    $user = new stdClass;
                    $user->programmingid = $pid;
                    $user->userid = $row->userid;
                    $user->submitcount = 0;
                    $users[$row->userid] = $user;
                }
                $pc = count_records('programming_test_results', 'submitid', $row->id, 'passed', 1);
                $user->submitcount++;
                $user->latestsubmitid = $row->id;
                $passed = $pc >= $tc ? 1 : 0;
                execute_sql("UPDATE {$CFG->prefix}programming_submits SET passed={$passed} WHERE id={$row->id}");
            }
            foreach ($users as $u) {
                $result = insert_record('programming_result', $u);
                if (!$result) break;
            }
            if (!$result) break;
        }

    }

    if ($result && $oldversion < 2007041601) {

    /// Adding index to table programming_submits
        $table = new XMLDBTable('programming_submits');
        $idx = new XMLDBIndex('status');
        $idx->setFields(array('status'));
        $result = add_index($table, $idx);

    /// Set null language to gcc-3.3
        execute_sql("UPDATE {$CFG->prefix}programming_submits SET language = 1 WHERE language is NULL", false);
    }

    if ($result && $oldversion < 2007052401) {

    /// Add field to table programming_test_results
        $table = new XMLDBTable('programming_test_results');
        $field = new XMLDBField('memused');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, $notnull=null, $sequence=null, $enum=null, $enumvalues=null, $default=null, $previous='timeused');
        $result = add_field($table, $field);

        $field = new XMLDBField('judgeresult');
        $field->setAttributes(XMLDB_TYPE_CHAR, 5, XMLDB_UNSIGNED, $notnull=null, $sequence=null, $enum=null, $enumvalues=null, $default=null, $previous='memused');
        $result = add_field($table, $field);
    }

    if ($result && $oldversion < 2007060201) {

    /// Add field to table programming_tests
        $table = new XMLDBTable('programming_tests');
        $field = new XMLDBField('timemodified');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, $notnull=null, $sequence=null, $enum=null, $enumvalues=null, $default=null, $previous='weight');
        $result = add_field($table, $field);
    }

    if ($result && $oldversion < 2007083002) {

    /// Add field to table programming
        $table = new XMLDBTable('programming');
        $field = new XMLDBField('presetcode');
        $field->setAttributes(XMLDB_TYPE_TEXT, 'small', null, null, null, null, null, null, 'keeplatestonly');
        $result = add_field($table, $field);
    }

    if ($result && $oldversion < 2007103103) {
        execute_sql("INSERT INTO {$CFG->prefix}programming_languages (name) VALUES ('java-1.5')", false);
        execute_sql("INSERT INTO {$CFG->prefix}programming_languages (name) VALUES ('java-1.6')", false);
    }

    if ($result && $oldversion < 2007103104) {

    /// Add field to table programming_tests
        $table = new XMLDBTable('programming');
        $field = new XMLDBField('timemodified');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, $notnull=null, $sequence=null, $enum=null, $enumvalues=null, $default=null, $previous='showmode');
        $result = add_field($table, $field);
    }

    if ($result && $oldversion < 2007110602) {

    /// Change table programming_test_results fields type
        $table = new XMLDBTable('programming_test_results');
        $field = new XMLDBField('output');
        $field->setAttributes(XMLDB_TYPE_BINARY, 'small', XMLDB_UNSIGNED, $notnull=null, $sequence=null, $enum=null, $enumvalues=null, $default=null);
        change_field_type($table, $field);
        $field = new XMLDBField('stderr');
        $field->setAttributes(XMLDB_TYPE_BINARY, 'small', XMLDB_UNSIGNED, $notnull=null, $sequence=null, $enum=null, $enumvalues=null, $default=null);
        $result = change_field_type($table, $field);
    }

    if ($result && $oldversion < 2007112008) {

    /// Add field to table programming
        $table = new XMLDBTable('programming');
        $field = new XMLDBField('globalid');
        $field->setAttributes(XMLDB_TYPE_CHAR, '10', $unsigned=null, $notnull=XMLDB_NOTNULL, $sequence=null, $enum=null, $enumvalues=null, $default='', $previous='grade');
        $result = add_field($table, $field);
        execute_sql("UPDATE {$CFG->prefix}programming SET globalid=id");
    }

    if ($result && $oldversion < 2007112008) {

    /// Add index to table programming
        $table = new XMLDBTable('programming');
        $idx = new XMLDBIndex('globalid');
        $idx->setFields(array('globalid'));
        $result = add_index($table, $idx);
    }

    if ($result && $oldversion < 2009032303) {

    /// Add field priority to table programming_testers
        $table = new XMLDBTable('programming_testers');
        $field = new XMLDBField('priority');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, XMLDB_UNSIGNED, $notnull=null, $sequence=null, $enum=null, $enumvalues=null, $default=0);
        $result = add_field($table, $field);
    }

    if ($result && $oldversion < 2009032401) {

    /// Add field compressed input to table programming_tests
        $table = new XMLDBTable('programming_tests');
        $field = new XMLDBField('gzinput');
        $field->setAttributes(XMLDB_TYPE_BINARY, 'medium', XMLDB_UNSIGNED, $notnull=null, $sequence=null, $enum=null, $enumvalues=null, $default=null, $previous='input');
        $result = add_field($table, $field);

    /// Add field compressed output to table programming_tests
        $table = new XMLDBTable('programming_tests');
        $field = new XMLDBField('gzoutput');
        $field->setAttributes(XMLDB_TYPE_BINARY, 'medium', XMLDB_UNSIGNED, $notnull=null, $sequence=null, $enum=null, $enumvalues=null, $default=null, $previous='output');
        $result = add_field($table, $field);
    }

    if ($result && $oldversion < 2009032406) {

    /// put compressed input into gzinput and compressed output into gzoutput
        $tests = get_records('programming_tests');
        $olddebug = $db->debug;
        $db->debug = false;
        foreach ($tests as $t) {
            if (strlen($t->input) > 1024) {
                echo "UPDATE gzinput of record $t->id <br />";
                $sql = "UPDATE {$CFG->prefix}programming_tests
                           SET gzinput='".addslashes(bzcompress($t->input))."'
                         WHERE id={$t->id}";
                execute_sql($sql, false);
            }
            if (strlen($t->output) > 1024) {
                echo "UPDATE gzoutput of record $t->id <br />";
                $sql = "UPDATE {$CFG->prefix}programming_tests
                           SET gzoutput='".addslashes(bzcompress($t->output))."'
                         WHERE id={$t->id}";
                execute_sql($sql, false);
            }
        }
        $db->debug = $olddebug;
    }

    if ($result && $oldversion < 2009032407) {

    /// Add index to table programming
        $table = new XMLDBTable('programming_submits');
        $idx = new XMLDBIndex('passed');
        $idx->setFields(array('passed'));
        $result = add_index($table, $idx);
    }

    return $result;
}

?>
