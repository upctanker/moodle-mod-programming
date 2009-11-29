<?php

    require_once('../../../config.php');
    require_once('../lib.php');

    $a = required_param('a', PARAM_INT);     // programming ID
    $id = required_param('id', PARAM_INT); 
    $direction = required_param('direction', PARAM_INT);

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

    programming_datafile_adjust_sequence($a, $id, $direction);
    add_to_log($course->id, 'programming', 'datafile_delete', "datafile/move.php?a={$programming->id}", 'move data file');
    redirect("list.php?a=$programming->id", get_string('datafilemoved', 'programming'), 0);

?>
