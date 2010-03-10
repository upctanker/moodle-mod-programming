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
                programming_presetcode_restore_mods($newid, $info, $restore);
                programming_datafile_restore_mods($newid, $info, $restore);
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
            $langlimit = new stdClass;
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

    function programming_presetcode_restore_mods($programmingid, $info, $restore) {
        global $CFG;

        $status = true;

        $codes = $info['MOD']['#']['PRESETCODES']['0']['#'];
        print_r($info['MOD']['#']['PRESETCODES']);
        if (!empty($codes)) {
            foreach ($codes['PRESETCODE'] as $opt_info) {

                $oldid = backup_todb($opt_info['#']['ID']['0']['#']);
                $code = new stdClass;
                $code->programmingid = $programmingid;
                $code->languageid = backup_todb($opt_info['#']['LANGUAGEID']['0']['#']);
                $code->name = backup_todb($opt_info['#']['NAME']['0']['#']);
                $code->sequence = backup_todb($opt_info['#']['SEQUENCE']['0']['#']);
                $code->presetcode = backup_todb($opt_info['#']['PRESETCODE']['0']['#']); 
                $code->presetcodeforcheck = backup_todb($opt_info['#']['PRESETCODEFORCHECK']['0']['#']);

                $newid = insert_record('programming_presetcode', $code);

                if (!defined('RESTORE_SILENTLY')) {
                    echo '.';
                }
                backup_flush(300);

                if ($newid) {
                    backup_putid($restore->backup_unique_code, 'programming_presetcode', $oldid, $newid);
                } else {
                    $status = false;
                }
            }
        }

        return $status;
    }

    function programming_datafile_restore_mods($programmingid, $info, $restore) {
        global $CFG;

        $status = true;

        $files = $info['MOD']['#']['DATAFILES'][0]['#'];
        if (!empty($files)) {
            foreach ($files['DATAFILE'] as $opt_info) {

                $oldid = backup_todb($opt_info['#']['ID']['0']['#']);
                $file = new stdClass;
                $file->programmingid = $programmingid;
                $file->filename = backup_todb($opt_info['#']['FILENAME']['0']['#']);
                $file->seq = backup_todb($opt_info['#']['SEQ']['0']['#']);
                $file->isbinary = backup_todb($opt_info['#']['ISBINARY']['0']['#']);
                $file->datasize = backup_todb($opt_info['#']['DATASIZE']['0']['#']);
                $file->data = backup_todb(base64_decode($opt_info['#']['DATA']['0']['#']));
                $file->checkdatasize = backup_todb($opt_info['#']['CHECKDATASIZE']['0']['#']);
                $file->checkdata = backup_todb(base64_decode($opt_info['#']['CHECKDATA']['0']['#']));
                $file->memo = backup_todb($opt_info['#']['MEMO']['0']['#']);
                $file->timemodified = backup_todb($opt_info['#']['TIMEMODIFIED']['0']['#']);

                $newid = insert_record('programming_datafile', $file);

                if (!defined('RESTORE_SILENTLY')) {
                    echo '.';
                }
                backup_flush(300);

                if ($newid) {
                    backup_putid($restore->backup_unique_code, 'programming_datafile', $oldid, $newid);
                } else {
                    $status = false;
                }
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
            $test = new stdClass;
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
