<?php
    //This php script contains all the stuff to backup/restore
    //label mods

    //This is the "graphical" structure of the label mod:
    //
    //                     programming
    //                     (CL,pk->id)
    //                         |
    //            +--------------------------+
    //            |                          |
    //      programming_tests         programming_langlimit
    // (UL,pk->id,fk->programmingid) (UL,pk->id,fk->programmingid)
    //
    // Meaning: pk->primary key field of the table
    //          fk->foreign key to link with parent
    //          nt->nested field (recursive data)
    //          CL->course level info
    //          UL->user level info
    //          files->table may have files)
    //
    //-----------------------------------------------------------

    function programming_backup_mods($bf, $preferences) {

        global $CFG;

        $status = true;

        $programmings = get_records('programming', 'course', $preferences->backup_course);

        if ($programmings) {
            foreach ($programmings as $programming) {
                $status = programing_backup_one_mod($bf, $preferences, $programming);
            }
        }

        return $status;
    }

    function programming_backup_one_mod($bf, $preferences, $programming) {

        global $CFG;

        if (is_numeric($programming)) {
            $programming = get_record('programming', 'id', $programming);
        }

        $status = true;

        // Start programming mod
        fwrite ($bf, start_tag('MOD', 3, true));

        // print programming data
        fwrite($bf, full_tag('ID', 4, false, $programming->id));
        fwrite($bf, full_tag('MODTYPE', 4, false, 'programming'));
        fwrite($bf, full_tag('NAME', 4, false, $programming->name));
        fwrite($bf, full_tag('DESCRIPTION', 4, false, $programming->description));
        fwrite($bf, full_tag('DESCFORMAT', 4, false, $programming->descformat));
        fwrite($bf, full_tag('GRADE', 4, false, $programming->grade));
        fwrite($bf, full_tag('TIMEOPEN', 4, false, $programming->timeopen));
        fwrite($bf, full_tag('TIMECLOSE', 4, false, $programming->timeclose));

        fwrite($bf, full_tag('TIMELIMIT', 4, false, $programming->timelimit));
        fwrite($bf, full_tag('MEMLIMIT', 4, false, $programming->memlimit));
        fwrite($bf, full_tag('NPROC', 4, false, $programming->nproc));

        fwrite($bf, full_tag('TIMEDISCOUNT', 4, false, $programming->timediscount));
        fwrite($bf, full_tag('DISCOUNT', 4, false, $programming->discount));
        fwrite($bf, full_tag('ALLOWLATE', 4, false, $programming->allowlate));
        fwrite($bf, full_tag('ATTEMPTS', 4, false, $programming->attempts));
        fwrite($bf, full_tag('KEEPLATESTONLY', 4, false, $programming->keeplatestonly));
        fwrite($bf, full_tag('INPUTFILE', 4, false, $programming->inputfile));
        fwrite($bf, full_tag('OUTPUTFILE', 4, false, $programming->outputfile));
        fwrite($bf, full_tag('PRESETCODE', 4, false, $programming->presetcode));
        fwrite($bf, full_tag('GENERATOR', 4, false, $programming->generator));
        fwrite($bf, full_tag('VALIDATOR', 4, false, $programming->validator));
        fwrite($bf, full_tag('GENERATORTYPE', 4, false, $programming->generatortype));
        fwrite($bf, full_tag('VALIDATORTYPE', 4, false, $programming->validatortype));
        fwrite($bf, full_tag('SHOWMODE', 4, false, $programming->showmode));

        programming_backup_langlimit($bf, $preferences, $programming);
        programming_backup_presetcode($bf, $preferences, $programming);
        programming_backup_datafile($bf, $preferences, $programming);
        programming_backup_testcase($bf, $preferences, $programming);

        $status = fwrite($bf, end_tag('MOD', 3, false));

        return $status;
    }

    function programming_backup_langlimit($bf, $preferences, $programming) {

        $langlimit = get_records('programming_langlimit', 'programmingid', $programming->id, 'id');

        $status = true;

        fwrite($bf, start_tag('LANGLIMITS', 4, true));
        if ($langlimit) {
            foreach ($langlimit as $ll) {
                fwrite($bf, start_tag('LANGLIMIT', 5, true));
                fwrite($bf, full_tag('ID', 6, false, $ll->id));
                fwrite($bf, full_tag('LANGUAGEID', 6, false, $ll->languageid));
                fwrite($bf, end_tag('LANGLIMIT', 5, true));
            }
        }
        fwrite($bf, end_tag('LANGLIMITS', 4, true));

        return $status;
    }

    function programming_backup_presetcode($bf, $preferences, $programming) {

        $presetcodes = get_records('programming_presetcode', 'programmingid', $programming->id, 'id');

        $status = true;

        fwrite($bf, start_tag('PRESETCODES', 4, true));
        if ($presetcodes) {
            foreach ($presetcodes as $pc) {
                fwrite($bf, start_tag('PRESETCODE', 5, true));
                fwrite($bf, full_tag('ID', 6, false, $pc->id));
                fwrite($bf, full_tag('PROGRAMMINGID', 6, false, $pc->programmingid));
                fwrite($bf, full_tag('LANGUAGEID', 6, false, $pc->languageid));
                fwrite($bf, full_tag('NAME', 6, false, $pc->name));
                fwrite($bf, full_tag('SEQUENCE', 6, false, $pc->sequence));
                fwrite($bf, full_tag('PRESETCODE', 6, false, $pc->presetcode));
                fwrite($bf, full_tag('PRESETCODEFORCHECK', 6, false, $pc->presetcodeforcheck));
                fwrite($bf, end_tag('PRESETCODE', 5, true));
            }
        }
        fwrite($bf, end_tag('PRESETCODES', 4, true));

        return $status;
    }

    function programming_backup_datafile($bf, $preferences, $programming) {

        $presetcodes = get_records('programming_datafile', 'programmingid', $programming->id, 'id');

        $status = true;

        fwrite($bf, start_tag('DATAFILES', 4, true));
        if ($presetcodes) {
            foreach ($presetcodes as $pc) {
                fwrite($bf, start_tag('DATAFILE', 5, true));
                fwrite($bf, full_tag('ID', 6, false, $pc->id));
                fwrite($bf, full_tag('PROGRAMMINGID', 6, false, $pc->programmingid));
                fwrite($bf, full_tag('FILENAME', 6, false, $pc->filename));
                fwrite($bf, full_tag('SEQ', 6, false, $pc->seq));
                fwrite($bf, full_tag('ISBINARY', 6, false, $pc->isbinary));
                fwrite($bf, full_tag('DATASIZE', 6, false, $pc->datasize));
                fwrite($bf, full_tag('DATA', 6, false, base64_encode($pc->data)));
                fwrite($bf, full_tag('CHECKDATASIZE', 6, false, $pc->checkdatasize));
                fwrite($bf, full_tag('CHECKDATA', 6, false, base64_encode($pc->checkdata)));
                fwrite($bf, full_tag('MEMO', 6, false, $pc->memo));
                fwrite($bf, full_tag('TIMEMODIFIED', 6, false, $pc->timemodified));
                fwrite($bf, end_tag('DATAFILE', 5, true));
            }
        }
        fwrite($bf, end_tag('DATAFILES', 4, true));

        return $status;
    }

    function programming_backup_testcase($bf, $preferences, $programming) {
        
        global $CFG;

        $tests = get_records('programming_tests', 'programmingid', $programming->id, 'id');
        
        fwrite($bf, start_tag('TESTCASES', 4, true));
        if ($tests) {
            foreach ($tests as $test) {
                fwrite($bf, start_tag('TESTCASE', 5, true));
                fwrite($bf, full_tag('ID', 6, false, $test->id));
                fwrite($bf, full_tag('SEQ', 6, false, $test->seq));
                fwrite($bf, full_tag('INPUT', 6, false, base64_encode($test->input)));
                fwrite($bf, full_tag('GZINPUT', 6, false, base64_encode($test->gzinput)));
                fwrite($bf, full_tag('OUTPUT', 6, false, base64_encode($test->output)));
                fwrite($bf, full_tag('GZOUTPUT', 6, false, base64_encode($test->gzoutput)));
                fwrite($bf, full_tag('TIMELIMIT', 6, false, $test->timelimit));
                fwrite($bf, full_tag('MEMLIMIT', 6, false, $test->memlimit));
                fwrite($bf, full_tag('NPROC', 6, false, $test->nproc));
                fwrite($bf, full_tag('PUB', 6, false, $test->pub));
                fwrite($bf, full_tag('WEIGHT', 6, false, $test->weight));
                fwrite($bf, full_tag('TIMEMODIFIED', 6, false, $test->timemodified));
                fwrite($bf, end_tag('TESTCASE', 5, true));
            }
        }
        fwrite($bf, end_tag('TESTCASES', 4, true));
    }

    function programming_check_backup_mods($course, $user_data = false, $backup_unique_code, $instances = null) {

        if (!empty($instances) && is_array($instances) && count($instances)) {
            $info = array();
            foreach ($instances as $id => $instance) {
                $info += programming_check_backup_mods_instance($instance, $backup_unique_code);
            }
        }

        $info[0][0] = get_string('modulenameplural', 'programming');
        if ($ids = programming_ids($course)) {
            $info[0][1] = count($ids);
        } else {
            $info[0][1] = 0;
        }

        return $info;
    }

    function programming_check_backup_mods_instance($instance, $backup_unique_code) {
        $info[$instance->id.'0'][0] = '<b>'.$instance->name.'</b>';
        $info[$instance->id.'0'][1] = '';

        return $info;
    }

    function programming_ids($course) {
        global $CFG;

        return get_records_sql("SELECT p.id, p.course
                                FROM {$CFG->prefix}programming p
                                WHERE p.course = '$course'");
    }

?>
