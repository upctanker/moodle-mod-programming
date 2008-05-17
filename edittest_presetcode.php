<?php

    require_once('../../config.php');
    require_once('lib.php');

    $a = optional_param('a', 0, PARAM_INT);     // programming ID
    $action = optional_param('action', '', PARAM_CLEAN);
    $presetcode = optional_param('presetcode', '', PARAM_RAW);
        
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

    if ($action) {
    	$sql = "UPDATE {$CFG->prefix}programming SET presetcode='{$presetcode}' WHERE id={$programming->id}";
    	execute_sql($sql, false);
        $programming = get_record('programming', 'id', $programming->id);
        add_to_log($course->id, 'programming', 'edittest_presetcode', 'edittest_presetcode.php?a='.$programming->id, 'set preset code');
    } else {
        add_to_log($course->id, 'programming', 'edittest_presetcode', 'edittest_presetcode.php?a='.$programming->id, 'view preset code');
    }

/// Print the page header
    $pagename = get_string('edittests', 'programming');
    include_once('pageheader.php');

/// Print tabs
    $currenttab = 'edittest';
    include_once('tabs.php');

/// Print page content

    include_once('edittest_presetcode.tpl.php');

/// Finish the page
    print_footer($course);

?>
