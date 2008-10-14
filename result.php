<?php

    require_once('../../config.php');
    require_once('lib.php');

    $a = optional_param('a', 0, PARAM_INT);     // programming ID
    $submitid = optional_param('submitid', 0, PARAM_INT);

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

    require_capability('mod/programming:viewdetailresult', $context);

    if ($submitid) {
        require_capability('mod/programming:viewotherresult', $context);
    }
    $viewhiddentestcase = has_capability('mod/programming:viewhiddentestcase', $context);

    add_to_log($course->id, 'programming', 'result', 'result.php?a'.$programming->id, $programming->name);

/// Print the page header
    $CFG->scripts[] = 'result.js';
    $pagename = get_string('result', 'programming');
    include_once('pageheader.php');

/// Print tabs
    $currenttab = 'result';
    include_once('tabs.php');

/// Print page content
    $strshowasplaintext = get_string('showasplaintext', 'programming');
    $strdownload = get_string('download', 'programming');

    if (!$submitid) {
        $r = get_record('programming_result', 'programmingid', $programming->id, 'userid', $USER->id);
        $submitid = $r->latestsubmitid;
    }
    $submit = get_record('programming_submits', 'id', $submitid);

    if ($submit) {
        $results = get_records('programming_test_results', 'submitid', $submit->id, 'testid');
        $tests = get_records('programming_tests', 'programmingid', $programming->id, 'id');
    }
    include_once('result.tpl.php');

/// Finish the page
    print_footer($course);

?>
