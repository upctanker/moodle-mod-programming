<?php

    function programming_restore_mods($mod, $restore) {

        global $CFG;

        $status = true;

        // Get record from backup_ids
        $data = backup_getid($restore->backup_unique_code, $mod->modtype, $mod->id);
        if ($data) {
            $info = $data->info;

            if ($restore->course_startdateoffset) {
                restore_log_date_changes('Programming', $restore, $info['MOD']['#'], array('TIMEOPEN', 'TIMECLOSE', 'TIMEDISCOUNT'));
            }

            $programming->course = $restore->course_id;
            $programming->name = backup_todb($info['MOD']['#']['NAME']['0']['#']);
            $programming->description = backup_todb($info['MOD']['#']['DESCRIPTION']['0']['#']);
            $programming->descformat = backup_todb($info['MOD']['#']['DESCFORMAT']['0']['#']);
            $programming->grade = backup_todb($info['MOD']['#']['GRADE']['0']['#']);
            $programming->timeopen = backup_todb($info['MOD']['#']['TIMEOPEN']['0']['#']);
            $programming->timeclose = backup_todb($info['MOD']['#']['TIMECLOSE']['0']['#']);
            $programming->timelimit = backup_todb($info['MOD']['#']['TIMELIMIT']['0']['#']);
            $programming->memlimit = backup_todb($info['MOD']['#']['MEMLIMIT']['0']['#']);
            $programming->timediscount = backup_todb($info['MOD']['#']['TIMEDISCOUNT']['0']['#']);
            $programming->discount = backup_todb($info['MOD']['#']['DISCOUNT']['0']['#']);
            $programming->allowlate  = backup_todb($info['MOD']['#']['ALLOWLATE']['0']['#']);
            $programming->attempts = backup_todb($info['MOD']['#']['ATTEMPTS']['0']['#']);
            $programming->keeplatestonly = backup_todb($info['MOD']['#']['KEEPLATESTONLY']['0']['#']);
            $programming->inputfile = backup_todb($info['MOD']['#']['INPUTFILE']['0']['#']);
            $programming->outputfile = backup_todb($info['MOD']['#']['OUTPUTFILE']['0']['#']);
            $programming->presetcode = backup_todb($info['MOD']['#']['PRESETCODE']['0']['#']);
            $programming->generator = backup_todb($info['MOD']['#']['GENERATOR']['0']['#']);
            $programming->validator = backup_todb($info['MOD']['#']['VALIDATOR']['0']['#']);
            $programming->generatortype = backup_todb($info['MOD']['#']['GENERATORTYPE']['0']['#']);
            $programming->validatortype = backup_todb($info['MOD']['#']['VALIDATORTYPE']['0']['#']);
            $programming->showmode = backup_todb($info['MOD']['#']['SHOWMODE']['0']['#']);

            $newid = insert_record('programming', $programming);
            
            if ($newid) {
                backup_putid($restore->backup_unique_code, $mod->modtype, $mod->id, $newid);

                programming_langlimit_restore_mods($newid, $info, $restore);
                programming_tests_restore_mods($newid, $info, $restore);
            } else {
                $status = false;
            }

            // do some output
            if (!defined('RESTORE_SILENTLY')) {
                echo '<li>'.get_string('modulename', 'programming').' "'.format_string(stripslashes($programming->name), true).'"</li>';
            }
            backup_flush(300);

        } else {
            $status = false;
        }
        
        return $status;
    }

    function programming_langlimit_restore_mods($programmingid, $info, $restore) {
        global $CFG;

        $status = true;

        $langlimits = $info['MOD']['#']['LANGLIMITS']['0']['#']['LANGLIMIT'];
        
        foreach ($langlimits as $opt_info) {

            $oldid = backup_todb($opt_info['#']['ID']['0']['#']);
            $langlimit->programmingid = $programmingid;
            $langlimit->languageid = backup_todb($opt_info['#']['LANGUAGEID']['0']['#']);

            $newid = insert_record('programming_langlimit', $langlimit);

            if (!defined('RESTORE_SILENTLY')) {
                echo '.';
            }
            backup_flush(300);

            if ($newid) {
                backup_putid($restore->backup_unique_code, 'programming_langlimit', $oldid, $newid);
            } else {
                $status = false;
            }
        }

        return $status;
    }

    function programming_tests_restore_mods($programmingid, $info, $restore) {

        global $CFG;

        $status = true;

        $tests = $info['MOD']['#']['TESTCASES']['0']['#']['TESTCASE'];
        
        foreach ($tests as $opt_info) {

            $oldid = backup_todb($opt_info['#']['ID']['0']['#']);
            $test->programmingid = $programmingid;
            $test->input = backup_todb(base64_decode($opt_info['#']['INPUT']['0']['#']));
            $test->gzinput = backup_todb(base64_decode($opt_info['#']['GZINPUT']['0']['#']));
            $test->output = backup_todb(base64_decode($opt_info['#']['OUTPUT']['0']['#']));
            $test->gzoutput = backup_todb(base64_decode($opt_info['#']['GZOUTPUT']['0']['#']));
            $test->timelimit = backup_todb($opt_info['#']['TIMELIMIT']['0']['#']);
            $test->memlimit = backup_todb($opt_info['#']['MEMLIMIT']['0']['#']);
            $test->pub = backup_todb($opt_info['#']['PUB']['0']['#']);
            $test->weight = backup_todb($opt_info['#']['WEIGHT']['0']['#']);
            $test->timemodified = backup_todb($opt_info['#']['TIMEMODIFIED']['0']['#']);

            $newid = insert_record('programming_tests', $test);

            if (!defined('RESTORE_SILENTLY')) {
                echo '.';
            }
            backup_flush(300);

            if ($newid) {
                backup_putid($restore->backup_unique_code, 'programming_tests', $oldid, $newid);
            } else {
                $status = false;
            }
        }

        return $status;
    }

?>
