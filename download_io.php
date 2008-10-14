<?php

    require_once('../../config.php');
    require_once('lib.php');

    $a = required_param('a', PARAM_INT);     // programming ID
    $testid = required_param('test', PARAM_INT);
    $submitid = optional_param('submit', -1, PARAM_INT);
    $type = required_param('type', PARAM_CLEAN);
    $download = optional_param('download', 1, PARAM_INT);

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

    if ($type == 'in' or ($type == 'out' and $submitid == -1)) {
        if (! $test = get_record('programming_tests', 'id', $testid)) {
            error('Test ID was incorrect');
        }
        if ($test->pub >= 0) {
            require_capability('mod/programming:viewpubtestcase', $context);
        } else {
            require_capability('mod/programming:viewhiddentestcase', $context);
        }
        $filename = sprintf('test-%d.%s', $testid, $type);
        $content = $type == 'in' ? $test->input : $test->output;
    }
    else if ($type == 'out' or $type == 'err') {
        require_capability('mod/programming:viewdetailresult', $context);
        if (! $result = get_record('programming_test_results', 'submitid', $submitid, 'testid', $testid)) {
            error('Test ID or submit ID was incorrect.');
        }
        $test = get_record('programming_tests', 'id', $testid);
        if ($test->pub >= 0) {
            require_capability('mod/programming:viewpubtestcase', $context);
        } else {
            require_capability('mod/programming:viewhiddentestcase', $context);
        }
        $submit = get_record('programming_submits', 'id', $submitid);
        if ($submit->userid != $USER->id) {
            require_capability('mod/programming:viewotherresult', $context);
        }
        if ($result->judgeresult == 'AC' && strlen($result->output) == 0) {
            $result->output = $test->output;
        }
        $filename = sprintf('test-%d-%d.%s', $testid, $submitid, $type);
        $content = $type == 'out' ? $result->output : $result->stderr;
    }

    header('Content-Type: text/plain');
    if ($filename && $download) {
        header('Content-Disposition: attachment; filename="'.$filename.'"');
    }
    echo $content;

?>
