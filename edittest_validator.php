<?php

    require_once('../../config.php');
    require_once('lib.php');

    $a = optional_param('a', 0, PARAM_INT);     // programming ID
    $action = optional_param('action', '', PARAM_CLEAN);
    $validator = optional_param('validator', '', PARAM_RAW);
    $validatortype = optional_param('validatortype', '', PARAM_CLEAN);
        
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
        $obj = new Object();
        $obj->id = $programming->id;
        $obj->validator = $validator;
        $obj->validatortype = $validatortype;
        update_record('programming', $obj);
        $programming = get_record('programming', 'id', $programming->id);
        add_to_log($course->id, 'programming', 'edittest_validator', 'edittest_validator.php?a='.$programming->id, 'set validator');
    } else {
        $programming = get_record('programming', 'id', $programming->id);
        add_to_log($course->id, 'programming', 'edittest_validator', 'edittest_validator.php?a='.$programming->id, 'view validator');
    }

/// Print the page header
    $pagename = get_string('edittests', 'programming');
    include_once('pageheader.php');

/// Print tabs
    $currenttab = 'edittest';
    include_once('tabs.php');

/// Print page content

    include_once('edittest_validator.tpl.php');

/// Finish the page
    print_footer($course);

?>
