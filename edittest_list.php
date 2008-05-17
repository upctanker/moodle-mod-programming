<?php

    require_once('../../config.php');
    require_once('lib.php');

    $a = optional_param('a', 0, PARAM_INT);     // programming ID
    $page = optional_param('page', 0, PARAM_INT);
    $perpage = optional_param('perpage', 3, PARAM_INT);
    $offset = $page * $perpage;

    if (! $programming = get_record('programming', 'id', $a)) {
        error('Course module is incorrect');
    }
    if (! $course = get_record('course', 'id', $programming->course)) {
        error('Course is misconfigured');
    }
    if (! $cm = get_coursemodule_from_instance('programming', $programming->id, $course->id)) {
        error('Course Module ID was incorrect');
    }

    require_login($course->id);

    $context = get_context_instance(CONTEXT_MODULE, $cm->id);

    require_capability('mod/programming:viewpubtestcase', $context);

    $testcases = get_records('programming_tests', 'programmingid', $programming->id, 'id', '*', $offset, $perpage);
    $totalcount = count_records('programming_tests', 'programmingid', $programming->id);

    add_to_log($course->id, 'programming', 'edittest_list', 'edittest_list.php?a'.$programming->id, $programming->id);

/// Print the page header
    $pagename = get_string('edittests', 'programming');
    include_once('pageheader.php');

/// Print tabs
    $currenttab = 'edittest';
    include_once('tabs.php');

/// Print page content

    include_once('edittest_list.tpl.php');

/// Finish the page
    print_footer($course);

?>
