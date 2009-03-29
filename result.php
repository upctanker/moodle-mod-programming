<?php

    require_once('../../config.php');
    require_once('lib.php');
    require_once('../../lib/tablelib.php');

    $a = optional_param('a', 0, PARAM_INT);     // programming ID
    $submitid = optional_param('submitid', 0, PARAM_INT);

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
    require_capability('mod/programming:viewdetailresult', $context);

    if (!$submitid) {
        // get the latest submitid of current user
        $r = get_record('programming_result', 'programmingid', $programming->id, 'userid', $USER->id);
        if (!empty($r)) $submitid = $r->latestsubmitid;
    }
    $submit = get_record('programming_submits', 'id', $submitid);
    // Check is the user view result of others
    if (!empty($submit) && $submit->userid != $USER->id) {
        require_capability('mod/programming:viewotherresult', $context);
    }

    $viewhiddentestcase = has_capability('mod/programming:viewhiddentestcase', $context);

    add_to_log($course->id, 'programming', 'result', 'result.php?a'.$programming->id, $programming->name);

    // get title of the page
    if ($submit && $submit->userid != $USER->id) {
        $u = get_record('user', 'id', $submit->userid);
        $title = get_string('viewtestresultof', 'programming', fullname($u));
        $pagename = $title;
    } else {
        $title = get_string('viewtestresult', 'programming');
        $pagename = get_string('result', 'programming');
    }

/// Print the page header
    include_once('pageheader.php');

/// Print tabs
    $currenttab = 'result';
    include_once('tabs.php');

/// Print page content
    echo '<div class="maincontent generalbox">';
    echo "<h1>$title</h1>";
    if (empty($submit)) {
        echo '<p>'.get_string('cannotfindyoursubmit', 'programming').'</p>';
    } else {
        echo '<p>'.$currentstate = get_string('currentstatus', 'programming', programming_get_submit_status_desc($submit)).'</p>';

        if (!empty($submit->compilemessage)) {
            echo '<p>'.get_string('compilemessage', 'programming').'</p>';
            echo '<div class="compilemessage">';
            echo programming_format_compile_message($submit->compilemessage);
            echo '</div>';
        }

        if (!empty($submit->judgeresult)) {
            $results = get_records('programming_test_results', 'submitid', $submit->id, 'testid');

            if ($programming->showmode == PROGRAMMING_SHOWMODE_NORMAL || has_capability('mod/programming:viewdetailresultincontest', $context)) {
                $tests = get_records('programming_tests', 'programmingid', $programming->id, 'id');
                echo '<div id="test-result-detail">';
                echo '<p>'.get_string('testresult', 'programming', programming_get_test_results_desc($submit, $results)).'</p>';
                echo '<p>'.get_string('iostripped', 'programming', '1').'</p>';
                print_test_result_table();
                echo '</div>';
            } else {
                echo'<p>'.programming_contest_get_judgeresult($results).'</p>';
            }
        }

        $strviewprogram = get_string('viewprogram', 'programming');
        $viewprogramurl = 'history.php?a='.$programming->id;
        if ($submitid) $viewprogramurl .= '&amp;userid='.$submit->userid;
        echo "<p><a href='$viewprogramurl'>$strviewprogram</a></p>";
    }
    echo '</div>';

/// Finish the page
    print_footer($course);

function print_test_result_table()
{
    global $CFG;
    global $tests, $results;
    global $programming, $viewhiddentestcase;

    $strsecuretestcase = get_string('securetestcase', 'programming');
    $strshowasplaintext = get_string('showasplaintext', 'programming');
    $strdownload = get_string('download', 'programming');

    $table = new flexible_table('test-result-detail-table');
    $def = array('testcasenumber', 'weight', 'timelimit', 'memlimit', 'input', 'expectedoutput', 'output', 'errormessage', 'timeused', 'memused', 'exitcode', 'passed', 'judgeresult');
    $table->define_columns($def);
    $headers = array(
        get_string('testcasenumber', 'programming'),
        get_string('weight', 'programming').helpbutton('weight', 'weight', 'programming', true, false, '', true),
        get_string('timelimit', 'programming').helpbutton('timelimit', 'timelimit', 'programming', true, false, '', true),
        get_string('memlimit', 'programming').helpbutton('memlimit', 'memlimit', 'programming', true, false, '', true),
        get_string('input', 'programming').helpbutton('input', 'input', 'programming', true, false, '', true),
        get_string('expectedoutput', 'programming').helpbutton('expectedoutput', 'expectedoutput', 'programming', true, false, '', true),
        get_string('output', 'programming').helpbutton('output', 'output', 'programming', true, false, '', true),
        get_string('errormessage', 'programming').helpbutton('stderr', 'stderr', 'programming', true, false, '', true),
        get_string('timeused', 'programming').helpbutton('timeused', 'timeused', 'programming', true, false, '', true),
        get_string('memused', 'programming').helpbutton('memused', 'memused', 'programming', true, false, '', true),
        get_string('exitcode', 'programming').helpbutton('exitcode', 'exitcode', 'programming', true, false, '', true),
        get_string('passed', 'programming'),
        get_string('judgeresult', 'programming'));
    $table->define_headers($headers);

    $table->set_attribute('cellspacing', '0');
    $table->set_attribute('id', 'test-result-detail-table');
    $table->set_attribute('class', 'generaltable generalbox');
    $table->set_attribute('align', 'center');
    $table->set_attribute('cellpadding', '3');
    $table->set_attribute('cellspacing', '1');
    $table->setup();

    $i = 0; $id = 0;
    $rowclazz = array('');
    foreach ($results as $result) {
        $rowclazz[] = $result->passed ? 'passed' : 'notpassed';
        $data = array();
        $data[] = $i++;
        $data[] = $tests[$result->testid]->weight;
        $data[] = programming_format_timelimit($tests[$result->testid]->timelimit);
        $data[] = programming_format_memlimit($tests[$result->testid]->memlimit);
        $downloadurl = $CFG->wwwroot."/mod/programming/download_io.php?a={$programming->id}&amp;test={$result->testid}";
        if ($viewhiddentestcase || programming_test_case_visible($tests, $results)) {
            // input
            $html = "<div class='programming-io'>";
            $html.= link_to_popup_window($downloadurl.'&amp;type=in&amp;download=0', '_blank', $strshowasplaintext, 300, 400, null, null, true);
            $html.= '&nbsp;';
            $html.= "<a href='$downloadurl&amp;type=in'>$strdownload</a>";
            $html.= programming_format_io($tests[$result->testid]->input, true);
            $html.= '</div>';
            $data[] = $html;

            // expected output
            $html = "<div class='programming-io'>";
            $html.= link_to_popup_window($downloadurl.'&amp;type=out&amp;download=0', '_blank', $strshowasplaintext, 300, 400, null, null, true);
            $html.= '&nbsp;';
            $html.= "<a href='$downloadurl&amp;type=out'>$strdownload</a>";
            $html.= programming_format_io($tests[$result->testid]->output, true);
            $html.= '</div>';
            $data[] = $html;

            // output
            if (!empty($result->output)) {
                $html = "<div class='programming-io'>";
                $html.= link_to_popup_window($downloadurl."&amp;submit={$result->submitid}&amp;type=out&amp;download=0", '_blank', $strshowasplaintext, 300, 400, null, null, true);
                $html.= '&nbsp;';
                $html.= "<a href='$downloadurl&amp;submit={$result->submitid}&amp;type=out'>$strdownload</a>";
                $html.= programming_format_io($result->output, false);
                $html.= "</div>";
                $data[] = $html;
            } else {
                $data[] = get_string('noresult', 'programming');
            }

            // error message
            if (!empty($result->stderr)) {
                $html = "<div class='programming-io>";
                $html.= link_to_popup_window($downloadurl."&amp;submit={$result->submitid}&amp;type=err&amp;download=0", '_blank', $strshowasplaintext, 300, 400, null, null, true);
                $html.= '&nbsp;';
                $html.= "<a href='$downloadurl&amp;submit={$result->submitid}&amp;type=err'>$strdownload</a>";
                $html.= programming_format_io($result->stderr, false);
                $data[] = $html;
            } else {
                $data[] = get_string('n/a', 'programming');
            }
        } else {
            $html = div($strsecuretestcase, $clazz);
            $data[] = $html; $data[] = $html;
            $data[] = $html; $data[] = $html;
        }

        $data[] = round($result->timeused, 3);
        $data[] = $result->memused;
    
        if ($viewhiddentestcase || programming_test_case_visible($tests, $results)) {
            $data[] = $result->exitcode;
        } else {
            $data[] = $strsecuretestcase;
        }

        $data[] = get_string($result->passed ? 'yes' : 'no');
        $data[] = programming_get_judgeresult($result);
        $table->add_data($data);
    }

    $table->print_html();

    // An ugly hack to add row class to table, moodlelib do not support this.
    echo '<script language="JavaScript" type="text/javascript">';
    echo '$(document).ready(function() {';
    echo '  var i = 0;';
    echo '  var clazz = ["'.implode('","', $rowclazz).'"];';
    echo '  $("#test-result-detail-table tr").each(function () {';
    echo '    $(this).addClass(clazz[i++]);';
    echo '  });';
    echo '});';
    echo '</script>';
}

?>
