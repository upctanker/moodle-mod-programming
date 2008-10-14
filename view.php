<?php

    require_once('../../config.php');
    require_once('lib.php');

    $id = optional_param('id', 0, PARAM_INT);    // Course Module ID, or
    $a = optional_param('a', 0, PARAM_INT);     // programming ID

    if ($id) {
        if (! $cm = get_record('course_modules', 'id', $id)) {
            error('Course Module ID was incorrect');
        }
    
        if (! $course = get_record('course', 'id', $cm->course)) {
            error('Course is misconfigured');
        }
    
        if (! $programming = get_record('programming', 'id', $cm->instance)) {
            error('Course module is incorrect');
        }

    } else {
        if (! $programming = get_record('programming', 'id', $a)) {
            error('Course module is incorrect');
        }
        if (! $course = get_record('course', 'id', $programming->course)) {
            error('Course is misconfigured');
        }
        if (! $cm = get_coursemodule_from_instance('programming', $programming->id, $course->id)) {
            error('Course Module ID was incorrect');
        }
    }

    $context = get_context_instance(CONTEXT_MODULE, $cm->id);

    require_login($course->id);

    if (!$cm->visible) {
        require_capability('moodle/course:viewhiddenactivities', $context);
    }

    require_capability('mod/programming:viewcontent', $context);
    $viewatanytime = has_capability('mod/programming:viewcontentatanytime', $context);
    $viewpubtestcase = has_capability('mod/programming:viewpubtestcase', $context);

    add_to_log($course->id, 'programming', 'view', 'view.php?id='.$programming->id, $programming->name);

/// Print the page header
    $pagename = get_string('view', 'programming');
    $CFG->scripts[] = 'js/dp/shCore.js';
    $CFG->scripts[] = 'js/dp/shBrushCSharp.js';
    $CFG->scripts[] = 'view.js';
    $CFG->stylesheets[] = 'js/dp/SyntaxHighlighter.css';
    include_once('pageheader.php');

/// Print tabs
    $currenttab = 'view';
    include_once('tabs.php');

/// Print the main part of the page
    $strshowasplaintext = get_string('showasplaintext', 'programming');
    $time = time();

    $pubtests = get_records_sql("SELECT * FROM {$CFG->prefix}programming_tests WHERE programmingid=$programming->id AND pub=1 ORDER BY id");
    if (!is_array($pubtests)) $pubtests = array();

    $submits = get_records_sql("SELECT * FROM {$CFG->prefix}programming_submits WHERE programmingid={$programming->id} AND userid={$USER->id} ORDER BY timemodified DESC");
    if (is_array($submits)) {
        $submit = current($submits);
        $results = get_records('programming_test_results', 'submitid', $submit->id);
    }

    $notlate = $programming->allowlate || $time <= $programming->timeclose;

    include_once('view.tpl.php');

/// Finish the page
    print_footer($course);

?>
