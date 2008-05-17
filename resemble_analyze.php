<?php

    require_once('../../config.php');
    require_once('lib.php');
	require_once('resemble_analyze_lib.php');

    $a = optional_param('a', 0, PARAM_INT);     // programming ID
    $action = optional_param('action', 0, PARAM_CLEAN);     // programming ID
	$url = optional_param('url', 0, PARAM_RAW);
	$max = optional_param('max', 0, PARAM_INT);
	$lowest = optional_param('lowest', 0, PARAM_INT);

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
    require_capability('mod/programming:updateresemble', $context);

    //add_to_log($course->id, 'programming', 'resemble_list', 'resemble_list.php?a'.$programming->id, $programming->id);

/// Print the page header
    $pagename = get_string('resemble_analyze', 'programming');
    // cross-site xmlhttprequest is not allowed :(
    //$CFG->scripts[] = 'resemble_analyze.js';
    include_once('pageheader.php');

/// Print tabs
    $currenttab = 'resemble';
	$currenttab2 = 'resemble-analyze';
    include_once('tabs.php');

/// Print page content

    if ($action) {
	  parse_result($programming->id, $url, $max, $lowest);
	} else {
	  include_once('resemble_analyze.tpl.php');
	}

/// Finish the page
    print_footer($course);

?>
