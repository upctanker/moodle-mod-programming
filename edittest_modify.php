<?php

    require_once('../../config.php');
    require_once('../../lib/uploadlib.php');
    require_once('lib.php');

    $a = optional_param('a', 0, PARAM_INT);     // programming ID
    $action = optional_param('action');         // action(submitted)
    $id = optional_param('id', 0, PARAM_INT);   // test ID

    if (! $programming = get_record('programming', 'id', $a)) {
        error('Course module is incorrect');
    }
    if (! $course = get_record('course', 'id', $programming->course)) {
        error('Course is misconfigured');
    }
    if (! $cm = get_coursemodule_from_instance('programming', $programming->id, $course->id)) {
        error('Course Module ID was incorrect');
    }

    $context = get_context_instance(CONTEXT_MODULE, $cm->id);

    require_login($course->id);

    require_capability('mod/programming:edittestcase', $context);

    if ($id) {
        // Edit
        $testcase = get_record('programming_tests', 'id', $id);
    } else {
        // Add
        $testcase = new object;
        $testcase->id = 0;
        $testcase->programmingid = $a;
    }

    if ($action) {
        $testcase->timemodified = mktime();
        $input = optional_param('input', '', PARAM_RAW);
        if ($input) {
            $testcase->input = $input;
        }
        $output = optional_param('output', '', PARAM_RAW);
        if ($output) {
            $testcase->output = $output;
        }
        $testcase->timelimit = optional_param('timelimit', 0, PARAM_INT);
        $testcase->memlimit = optional_param('memlimit', 0, PARAM_INT);
        $testcase->weight = optional_param('weight', 0, PARAM_INT);
        $testcase->pub = optional_param('pub', 0, PARAM_INT);

        if ($_FILES['inputfile']['size'] > 0 && is_uploaded_file($_FILES['inputfile']['tmp_name'])) {
            $testcase->input = addslashes(file_get_contents($_FILES['inputfile']['tmp_name']));

        }
        if ($_FILES['outputfile']['size'] > 0 && is_uploaded_file($_FILES['outputfile']['tmp_name'])) {
            $testcase->output = addslashes(file_get_contents($_FILES['outputfile']['tmp_name']));
        }

        if ($id) {
            programming_test_update_instance($testcase);
        } else {
            programming_test_add_instance($testcase);
        }
    }

    if ($action) {
        if ($id) {
            add_to_log($course->id, 'programming', 'edittest_modify', 'edittest_modify.php?a'.$programming->id, $programming->id);
        } else {
            add_to_log($course->id, 'programming', 'edittest_add', 'edittest_add.php?a='.$programming->id, $programming->id);
        }
    }

/// Print the page header
    $pagename = get_string('edittests', 'programming');
    include_once('pageheader.php');

/// Print tabs
    $currenttab = 'edittest';
    include_once('tabs.php');

/// Print page content

    if ($action) {
        include_once('edittest_modify_success.tpl.php');
    } else {
        include_once('edittest_modify.tpl.php');
    }

/// Finish the page
    print_footer($course);

?>
