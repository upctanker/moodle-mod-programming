<?php

    require_once('../../../config.php');
    require_once('../lib.php');

    $a = required_param('a', PARAM_INT);     // programming ID
    $id = required_param('id', PARAM_INT); 
    $confirmed = optional_param('confirmed', 0, PARAM_INT);

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

    if ($confirmed) {
        delete_records('programming_test_results', 'testid', $id);
        delete_records('programming_tests', 'id', $id);
        programming_testcase_adjust_sequence($a);
        add_to_log($course->id, 'programming', 'testcase_delete', "testcase/delet.php?a={$programming->id}", 'delete data file');
        redirect("list.php?a=$programming->id", get_string('testcasedeleted', 'programming'));
    }

/// Print the page header
    $pagename = get_string('deletetestcase', 'programming');
    include_once('../pageheader.php');

/// Print tabs
    $currenttab = 'edittest';
    include_once('../tabs.php');

/// Print page content
    echo '<div class="noticebox">';
    echo '<h2 class="main">'.get_string('deletetestcaseconfirm', 'programming').'</h2>';
    echo '<form name="form" method="post">';
    echo "<input type='hidden' name='a' value='$a' />";
    echo '<input type="hidden" name="confirmed" value="1" />';
    echo '<input type="submit" value=" '.get_string('yes').' " /> ';
    echo '<input type="button" value=" '.get_string('no').' " onclick="javascript:history.go(-1);" />';

    echo '</form>';
    echo '</div>';

/// Finish the page
    print_footer($course);

?>
