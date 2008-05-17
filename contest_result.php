<?php

    require_once('../../config.php');
    require_once('lib.php');
    require_once('../../lib/tablelib.php');

    $a = optional_param('a', 0, PARAM_INT);     // programming ID
    $page = optional_param('page', 0, PARAM_INT);
    $perpage = 15;

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
    require_capability('mod/programming:viewreport', $context);

    add_to_log($course->id, 'programming', 'history', 'history.php?a'.$programming->id, $programming->name);

/// Print the page header
    $pagename = get_string('result', 'programming');
    include_once('pageheader.php');

/// Print tabs
    $currenttab = 'result';
    include_once('tabs.php');

/// Print page content
    $pagenum = count_records('programming_submits', 'programmingid', $programming->id);
    $submits = get_records('programming_submits', 'programmingid', $programming->id, 'timemodified DESC', '*', $page * $perpage, $perpage);
    $uids = array();
    foreach ($submits as $submit) {
        if (!in_array($submit->userid, $uids)) $uids[] = $submit->userid;
        
        if ($submit->status == PROGRAMMING_STATUS_FINISH) {
            $results = get_records('programming_test_results', 'submitid', $submit->id);
            if (is_array($results)) {;
                $submit->result = get_string('accepted', 'programming');
                foreach ($results as $result) {
                    if (!$result->passed) {
                        $submit->result = programming_get_fail_reason($result, $programming->showmode);
                        break;
                    }
                }
            }
        } else if ($submit->status == PROGRAMMING_STATUS_COMPILEFAIL) {
            $submit->result = get_string('compileerror', 'programming');
        } else {
            $submit->result = get_string('judging', 'programming');
        }
    }
    if (!empty($uids)) {
        $uids = implode($uids, ',');
        $users = get_records_sql("SELECT * FROM {$CFG->prefix}user WHERE id IN ($uids)");
    }
    $langs = get_records('programming_languages');

    include_once('contest_result.tpl.php');

/// Finish the page
    print_footer($course);

?>
