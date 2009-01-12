<?php

    require_once('../../config.php');
    require_once('lib.php');

    $a = optional_param('a', 0, PARAM_INT);     // programming ID
    $rid = optional_param('rid', 0, PARAM_INT); // resemble id
    $page = optional_param('page', 0, PARAM_INT);

    if (! $programming = get_record('programming', 'id', $a)) {
        error('Course module is incorrect');
    }
    if (! $course = get_record('course', 'id', $programming->course)) {
        error('Course is misconfigured');
    }
    if (! $cm = get_coursemodule_from_instance('programming', $programming->id, $course->id)) {
        error('Course Module ID was incorrect');
    }
    if (! $resemble = get_record('programming_resemble', 'id', $rid)) {
        error('Resemble record can\'t be find');
    }
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);

    require_login($course->id);
    $submit1 = get_record('programming_submits', 'id', $resemble->submitid1);
    $submit2 = get_record('programming_submits', 'id', $resemble->submitid2);
    if ($submit1->userid == $USER->id || $submit2->userid == $USER->id) {
        require_capability('mod/programming:viewresemble', $context);
    } else {
        require_capability('mod/programming:editresemble', $context);
    }

    $user1 = get_record('user', 'id', $submit1->userid);
    $user2 = get_record('user', 'id', $submit2->userid);


    // Change matched lines into array, with an matched id as first element
    $lines1 = explode("\n", $submit1->code);
    $lines2 = explode("\n", $submit2->code);

    $matches = explode(';', $resemble->matchedlines);
    $mid = 1;
    foreach($matches as $range) {
        list($range1, $range2) = explode(',', $range);

        list($start, $end) = explode('-', $range1);
        while ($start <= $end) {
            if (array_key_exists($start, $lines1) &&
                !is_array($lines1[$start])) {
                $lines1[$start] = array($mid, $lines1[$start]);
            }
            $start++;
        }
        list($start, $end) = explode('-', $range2);
        while ($start <= $end) {
            if (array_key_exists($start, $lines2) &&
                !is_array($lines2[$start])) {
                $lines2[$start] = array($mid, $lines2[$start]);
            }
            $start++;
        }
        $mid++;
    }

    //add_to_log($course->id, 'programming', 'resemble_list', 'resemble_list.php?a'.$programming->id, $programming->id);

	include_once('resemble_compare.tpl.php');

?>
