<?php

    require_once('../../config.php');
    require_once('lib.php');

    $a = optional_param('a', 0, PARAM_INT);     // programming ID
    $userid = optional_param('userid', 0, PARAM_INT);
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

    require_capability('mod/programming:viewhistory', $context);

    if (!$userid) $userid = $USER->id;

    $submits = get_records_select('programming_submits', 'programmingid='.$programming->id.' AND userid='.$userid, 'id DESC');
    if ($programming->presetcode) {
        if (is_array($submits)) {
            foreach ($submits as $submit) {
                $submit->code = programming_format_code($programming, $submit);
            }
        }
    }

    add_to_log($course->id, 'programming', 'history', me(), $programming->name);

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

    include_once('history.tpl.php');

/// Finish the page
    print_footer($course);

?>
