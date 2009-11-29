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
        $tests = get_recordset('programming_tests', null, null, 'id, input, output');
        $olddebug = $db->debug;
        $db->debug = false;
        while ($t = $tests->FetchNextObject(false)) {
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
        $tests->Close();
        $db->debug = $olddebug;
    }

    if ($result && $oldversion < 2009032407) {

    /// Add index to table programming
        $table = new XMLDBTable('programming_submits');
        $idx = new XMLDBIndex('passed');
        $idx->setFields(array('passed'));
        $result = add_index($table, $idx);
    }

    if ($result && $oldversion < 2009032901) {

    /// Add index to table programming
        $table = new XMLDBTable('programming_test_results');
        $idx = new XMLDBIndex('testid');
        $idx->setFields(array('testid'));
        $result = add_index($table, $idx);
    }

    if ($result && $oldversion < 2009032902) {
    /// Add field to table programming
        $table = new XMLDBTable('programming_testers');
        $field = new XMLDBField('state');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, $notnull=null, $sequence=null, $enum=null, $enumvalues=null, $default=0, $previous='priority');
        $result = add_field($table, $field);
    }

    /// Add timeused, memused and judgeresult to table programming_submits
    if ($result && $oldversion < 2009032904) {
        $table = new XMLDBTable('programming_submits');
        $field = new XMLDBField('timeused');
        $field->setAttributes(XMLDB_TYPE_FLOAT, null, XMLDB_UNSIGNED, $notnull=null, $sequence=null, $enum=null, $enumvalues=null, $default=null, $previous='compilemessage');
        $result = add_field($table, $field);
    }

    if ($result && $oldversion < 2009032904) {
        $table = new XMLDBTable('programming_submits');
        $field = new XMLDBField('memused');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, $notnull=null, $sequence=null, $enum=null, $enumvalues=null, $default=null, $previous='timeused');
        $result = add_field($table, $field);
    }

    if ($result && $oldversion < 2009032904) {
        $table = new XMLDBTable('programming_submits');
        $field = new XMLDBField('judgeresult');
        $field->setAttributes(XMLDB_TYPE_CHAR, 5, XMLDB_UNSIGNED, $notnull=null, $sequence=null, $enum=null, $enumvalues=null, $default=null, $previous='memused');
        $result = add_field($table, $field);
    }

    if ($result && $oldversion < 2009032904) {

    /// Calculate timeused, memused and judgeresult for existing submits
        $submits = get_recordset('programming_submits', null, null, 'id');
        while ($s = $submits->FetchNextObject(false)) {
            $results = get_records('programming_test_results', 'submitid', $s->id, 'id', 'id, passed, timeused, memused, judgeresult');
            if (!empty($results)) {
                $timeused = programming_submit_timeused($results);
                $memused = programming_submit_memused($results);
                $judgeresult = programming_submit_judgeresult($results);
            } else {
                $timeused = $memused = 'NULL';
                $judgeresult = 'CE';
            }

            $sql = "UPDATE {$CFG->prefix}programming_submits
                       SET timeused = $timeused,
                           memused = $memused,
                           judgeresult = '$judgeresult'
                     WHERE id = {$s->id}";
            execute_sql($sql, false);
        }
        $submits->Close();
    }

    if ($result && $oldversion < 2009032905) {
    /// Add index to table programming_submits
        $table = new XMLDBTable('programming_submits');
        $idx = new XMLDBIndex('timeused');
        $idx->setFields(array('programmingid', 'timeused'));
        $result = add_index($table, $idx);

        $table = new XMLDBTable('programming_submits');
        $idx = new XMLDBIndex('memused');
        $idx->setFields(array('programmingid', 'memused'));
        $result = add_index($table, $idx);

        $table = new XMLDBTable('programming_submits');
        $idx = new XMLDBIndex('judgeresult');
        $idx->setFields(array('programmingid', 'judgeresult'));
        $result = add_index($table, $idx);
    }

    if ($result && $oldversion < 2009040703) {
        $rs = get_recordset('programming_result');
        while ($rst = $rs->FetchNextObject(false)) {
            programming_update_grade($rst->latestsubmitid);
        }
        $rs->Close();
    }

    if ($result && $oldversion < 2009051902) {
        $names = array('fpc-2.2', 'python-2.5', 'gmcs-2.0', 'bash-3');
        foreach ($names as $name) {
            if (!$result) break;
            $l = get_record('programming_languages', 'name', $name);
            if (!$l) {
                $l = new stdClass;
                $l->name = $name;
                $result = insert_record('programming_languages', $l);
            }
        }
    }

    if ($result && $oldversion < 2009052001) {
        $table = new XMLDBTable('programming');
        $field = new XMLDBField('inputfile');
        $field->setAttributes(XMLDB_TYPE_CHAR, 50, XMLDB_UNSIGNED, $notnull=null, $sequence=null, $enum=null, $enumvalues=null, $default=null, $previous='keeplatestonly');
        $result = add_field($table, $field);
    }

    if ($result && $oldversion < 2009052001) {
        $table = new XMLDBTable('programming');
        $field = new XMLDBField('outputfile');
        $field->setAttributes(XMLDB_TYPE_CHAR, 50, XMLDB_UNSIGNED, $notnull=null, $sequence=null, $enum=null, $enumvalues=null, $default=null, $previous='inputfile');
        $result = add_field($table, $field);
    }

    if ($result && $oldversion < 2009112506) {
        $table = new XMLDBTable('programming_languages');
        $field = new XMLDBField('description');
        $field->setAttributes(XMLDB_TYPE_CHAR, '50', XMLDB_UNSIGNED, $notnull=null, $sequence=null, $enum=null, $enumvalues=null, $default=null, $previous='name');
        $result = add_field($table, $field);
    }

    if ($result && $oldversion < 2009112506) {
        $table = new XMLDBTable('programming_languages');
        $field = new XMLDBField('sourceext');
        $field->setAttributes(XMLDB_TYPE_CHAR, '255', XMLDB_UNSIGNED, $notnull=null, $sequence=null, $enum=null, $enumvalues=null, $default=null, $previous='description');
        $result = add_field($table, $field);
    }

    if ($result && $oldversion < 2009112506) {
        $table = new XMLDBTable('programming_languages');
        $field = new XMLDBField('headerext');
        $field->setAttributes(XMLDB_TYPE_CHAR, '255', XMLDB_UNSIGNED, $notnull=null, $sequence=null, $enum=null, $enumvalues=null, $default=null, $previous='sourceext');
        $result = add_field($table, $field);
    }

    if ($result && $oldversion < 2009112506) {
        $l = get_record('programming_languages', 'id', 1);
        $l->description = 'C (GCC 3.3)';
        $l->sourceext = '.c'; $l->headerext = '.h';
        update_record('programming_languages', $l);

        $l = get_record('programming_languages', 'id', 2);
        $l->description = 'C++ (G++ 3.3)';
        $l->sourceext = '.cpp .cxx'; $l->headerext = '.h .hpp';
        update_record('programming_languages', $l);

        $l = get_record('programming_languages', 'id', 3);
        $l->description = 'Java (Sun JDK 5)';
        $l->sourceext = '.java';
        update_record('programming_languages', $l);

        $l = get_record('programming_languages', 'id', 4);
        $l->description = 'Java (Sun JDK 6)';
        $l->sourceext = '.java';
        update_record('programming_languages', $l);

        $l = get_record('programming_languages', 'id', 5);
        $l->description = 'Pascal (Free Pascal 2)';
        $l->sourceext = '.pas';
        update_record('programming_languages', $l);

        $l = get_record('programming_languages', 'id', 6);
        $l->description = 'Python 2.5';
        $l->sourceext = '.py';
        update_record('programming_languages', $l);

        $l = get_record('programming_languages', 'id', 7);
        $l->description = 'C# (Mono 2.0)';
        $l->sourceext = '.cs';
        update_record('programming_languages', $l);

        $l = get_record('programming_languages', 'id', 8);
        $l->description = 'Bash (Bash 3)';
        $l->sourceext = '.sh';
        update_record('programming_languages', $l);
    }

    if ($result && $oldversion < 2009112507) {
    /// Define table programming_presetcode to be created
        $table = new XMLDBTable('programming_presetcode');

    /// Adding fields to table programming_result
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('programmingid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('languageid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('name', XMLDB_TYPE_CHAR, '50', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('sequence', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('presetcode', XMLDB_TYPE_TEXT, 'small', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('presetcodeforcheck', XMLDB_TYPE_TEXT, 'small', XMLDB_UNSIGNED, XMLDB_NULL, null, null, null, null);

    /// Adding keys to table programming_result
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));

    /// Adding indexes to table programming_result
        $table->addIndexInfo('prog-lang-name', XMLDB_INDEX_UNIQUE, array('programmingid', 'languageid', 'name'));
        $table->addIndexInfo('prog-seq', XMLDB_INDEX_NOTUNIQUE, array('programmingid', 'sequence'));

    /// Launch create table for programming_result
        $result = $result && create_table($table);
    }

    if ($result && $oldversion < 2009112511) {
    /// Move presetcode to separate table
        $programmings = get_records('programming', null, null, $sort='id', $fields='id, presetcode');
        foreach ($programmings as $p) {
            if (!empty($p->presetcode)) {
                $code = new stdClass;
                $code->programmingid = $p->id;
                $code->languageid = 1;
                $code->name = '<prepend>';
                $code->sequence = 1;
                $code->presetcode = $p->presetcode;
                $code->presetcodeforcheck = NULL;
                insert_record('programming_presetcode', $code);
            }
        }
    }

    if ($result && $oldversion < 2009113002) {
    /// Define table programming_datafile to be created
        $table = new XMLDBTable('programming_datafile');

    /// Adding fields to table programming_datafile
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addFieldInfo('programmingid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('seq', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('filename', XMLDB_TYPE_CHAR, '50', XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('isbinary', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, $default='0');
        $table->addFieldInfo('datasize', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, $default='0');
        $table->addFieldInfo('data', XMLDB_TYPE_BINARY, 'medium', XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('checkdatasize', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, $default='0');
        $table->addFieldInfo('checkdata', XMLDB_TYPE_BINARY, 'medium', XMLDB_UNSIGNED, XMLDB_NULL);
        $table->addFieldInfo('memo', XMLDB_TYPE_TEXT, 'small');
        $table->addFieldInfo('timemodified', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);

    /// Adding keys to table programming_datafile
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));

    /// Adding indexes to table programming_datafile
        $table->addIndexInfo('prog-seq', XMLDB_INDEX_NOTUNIQUE, array('programmingid', 'seq'));
        $table->addIndexInfo('prog-filename', XMLDB_INDEX_UNIQUE, array('programmingid', 'filename'));

    /// Launch create table for programming_datafile
        $result = $result && create_table($table);
    }

    return $result;
}

?>
