<?php

    require_once('../../config.php');
    require_once('lib.php');
    
    $a = optional_param('a', 0, PARAM_INT);     // programming ID
    $s1 = required_param('s1', PARAM_INT);
    $s2 = required_param('s2', PARAM_INT);

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
    require_capability('mod/programming:viewhistory', $context);

    $submit1 = get_record('programming_submits', 'id', $s1);
    $submit2 = get_record('programming_submits', 'id', $s2);

    if ($submit1->userid != $USER->id || $submit2->userid != $USER->id) {
        require_capability('mod/programming:viewotherprogram', $context);
    }

/// Print the page header
    $pagename = get_string('submithistory', 'programming');
    $CFG->scripts[] = 'js/dp/shCore.js';
    $CFG->scripts[] = 'js/dp/shBrushCSharp.js';
    $CFG->scripts[] = 'history.js';
    $CFG->stylesheets[] = 'js/dp/SyntaxHighlighter.css';
    include_once('pageheader.php');

/// Print tabs
    $currenttab = 'history';
    include_once('tabs.php');

/// Print page content

    ini_set("include_path", ".:./lib");
    require_once('Text/Diff.php');
    require_once('text_diff_render_html.php');

    $lines1 = explode("\n", $submit1->code);
    $lines2 = explode("\n", $submit2->code);

    $diff = new Text_Diff('auto', array($lines1, $lines2));

    $renderer = new Text_Diff_Renderer_html();

    echo '<pre>';
    echo $renderer->render($diff);
    echo '</pre>';

/// Finish the page
    print_footer($course);

?>
