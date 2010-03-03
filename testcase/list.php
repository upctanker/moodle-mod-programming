<?php

    require_once('../../../config.php');
    require_once($CFG->libdir.'/tablelib.php');
    require_once('../lib.php');

    $a = required_param('a', PARAM_INT);     // programming ID
        
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

    require_capability('mod/programming:viewhiddentestcase', $context);

/// Print the page header
    $CFG->scripts[] = 'list.js';
    $pagename = get_string('testcase', 'programming');
    include_once('../pageheader.php');

/// Print tabs
    $currenttab = 'edittest';
    include_once('../tabs.php');

/// Print page content
    echo '<div class="maincontent generalbox">';
    echo '<h1>'.get_string('testcase', 'programming').helpbutton('testcase', 'testcase', 'programming', true, false, '', true).'</h1>';
    print_testcase_table();
    echo '</div>';

/// Finish the page
    print_footer($course);

function print_testcase_table() {
    global $CFG, $page, $perpage, $programming, $course, $language, $groupid;

    $table = new flexible_table('testcase-table');
    $def = array('sequence', 'pub', 'stdin', 'stdout', 'timelimit', 'memlimit', 'nproc', 'weight', 'action');
    $table->define_columns($def);
    $headers = array(
        get_string('sequence', 'programming'),
        get_string('public', 'programming').helpbutton('testcasepub', 'helptestcasepub', 'programming', true, false, '', true),
        get_string('input', 'programming').helpbutton('input', 'input', 'programming', true, false, '', true),
        get_string('output', 'programming').helpbutton('output', 'output', 'programming', true, false, '', true),
        get_string('timelimit', 'programming').helpbutton('timelimit', 'timelimit', 'programming', true, false, '', true),
        get_string('memlimit', 'programming').helpbutton('memlimit', 'memlimit', 'programming', true, false, '', true),
        get_string('extraproc', 'programming').helpbutton('nproc', 'nproc', 'programming', true, false, '', true),
        get_string('weight', 'programming').helpbutton('weight', 'weight', 'programming', true, false, '', true),
        get_string('action'),
        );
    $table->define_headers($headers);

    $table->set_attribute('id', 'presetcode-table');
    $table->set_attribute('class', 'generaltable generalbox');
    $table->set_attribute('align', 'center');
    $table->set_attribute('cellpadding', '3');
    $table->set_attribute('cellspacing', '1');
    $table->column_class('stdin', 'programming-io');
    $table->column_class('stdout', 'programming-io');
    $table->no_sorting('code');
    $table->setup();

    $strshowasplaintext = get_string('showasplaintext', 'programming');
    $strdownload = get_string('download', 'programming');
    $stredit = get_string('edit');
    $strdelete = get_string('delete');
    $strmoveup = get_string('moveup');
    $strmovedown = get_string('movedown');
    $tests = get_records('programming_tests', 'programmingid', $programming->id, 'seq');
    if (is_array($tests)) {
        $tests_count = count($tests)-1;
        $i = 0;
        foreach ($tests as $case) {
            $data = array();
            $data[] = $case->seq;
            $data[] = programming_testcase_pub_getstring($case->pub);
            
            // stdin
            $html = "<a href='download_io.php?a={$programming->id}&amp;test={$case->id}&amp;type=in&amp;download=0' class='showasplaintext small'>$strshowasplaintext</a>";
            $html .= '&nbsp;';
            $html .= "<a href='download_io.php?a={$programming->id}&amp;test={$case->id}&amp;type=in' class='download small'>$strdownload</a>";
            $html .= programming_format_io($case->input, false);
            $data[] = $html;

            // stdout
            $html = "<a href='download_io.php?a={$programming->id}&amp;test={$case->id}&amp;type=out&amp;download=0' class='showasplaintext small'>$strshowasplaintext</a>";
            $html .= '&nbsp;';
            $html .= "<a href='download_io.php?a={$programming->id}&amp;test={$case->id}&amp;type=out' class='download small'>$strdownload</a>";
            $html .= programming_format_io($case->output, false);
            $data[] = $html;

            // limits
            $data[] = get_string('nseconds', 'programming', $case->timelimit);
            $data[] = get_string('nkb', 'programming', $case->memlimit);
            $data[] = $case->nproc;

            $data[] = get_string('nweight', 'programming', $case->weight);

            // actions
            $actions = array();
            $actions[] = "<a class='icon edit' href='edit.php?a={$programming->id}&amp;id={$case->id}' title='$stredit'><img src='{$CFG->pixpath}/t/edit.gif' alt='$stredit'/></a>";
            $actions[] = "<a class='icon delete' href='delete.php?a={$programming->id}&amp;id={$case->id}' title='$strdelete'><img src='{$CFG->pixpath}/t/delete.gif' alt='$strdelete'/></a>";
            if ($i > 0) {
                $actions[] = "<a class='icon up' href='move.php?a={$programming->id}&amp;id={$case->id}&amp;direction=1' title='$strmoveup'><img src='{$CFG->pixpath}/t/up.gif' alt='$strmoveup'/></a> ";
            }
            if ($i < $tests_count) {
                $actions[] = "<a class='icon down' href='move.php?a={$programming->id}&amp;id={$case->id}&amp;direction=2' title='$strmovedown'><img src='{$CFG->pixpath}/t/down.gif' alt='$strmovedown'/></a> ";
            }
            $data[] = implode($actions, "\n");
            $table->add_data($data);
            $i++;
        }

        echo '<div class="maincontent2">';
        $table->print_html();
        echo '</div>';
    } else {
        echo '<p>'.get_string('notestcase', 'programming').'</pre>';
    }
    echo "<p><a href='add.php?a={$programming->id}'>".get_string('addtestcase', 'programming').'</a></p>';
}

?>
