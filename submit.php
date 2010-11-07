<?php

    require_once('../../config.php');
    require_once('lib.php');

    $a = optional_param('a', 0, PARAM_INT);     // programming ID
    $language = optional_param('language');
    $code = optional_param('code', '', PARAM_RAW);
    $submitfor = optional_param('submitfor');
    $action = optional_param('action');

    if (isset($_FILES['sourcefile']) && $_FILES['sourcefile']['size'] > 0 && is_uploaded_file($_FILES['sourcefile']['tmp_name'])) {
        $code = addslashes(file_get_contents($_FILES['sourcefile']['tmp_name']));
    }
    
    $cookiename = 'MDLPROGLANG_'.$CFG->sessioncookie;
    $default_language = 0;
    if (isset($_COOKIE[$cookiename])) {
        $default_language = $_COOKIE[$cookiename];
    }
    if (!isset($language)) $language = $default_language;

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
    require_capability('mod/programming:submitprogram', $context);
    $submitatanytime = has_capability('mod/programming:submitatanytime', $context);
    $submitforothers = has_capability('mod/programming:submitforothers', $context);

    $result = get_record('programming_result', 'programmingid', $programming->id, 'userid', $USER->id);
    $submitcount = is_object($result) ? $result->submitcount : 0;
    $time = time();
    $isearly = $time < $programming->timeopen;
    $islate = !$programming->allowlate && $time > $programming->timeclose;
    $istoomore = $programming->attempts != 0 && $submitcount > $programming->attempts;
    $allowpost = $submitatanytime || (!$isearly && !$islate && !$istoomore);

    // Check if user has passed the practice
    $haspassed = false;
    if ($submitcount > 0) {
        $latestsubmit = get_record('programming_submits', 'id', $result->latestsubmitid);
        $haspassed = is_object($latestsubmit) && $latestsubmit->passed;
    }

    if ($allowpost && $action) {
        $submit = new Object();
        $submit->language = $language;

        if (!$submitfor) {
            $submits_count = count_records('programming_submits', 'programmingid', $programming->id, 'userid', $USER->id);

            if (!$submitatanytime && ($programming->attempts != 0 && $programming->attempts <= $submits_count)) {
                $error = get_string('submitfailednoattempts', 'programming');
                $submit = False;
            }
        } else {
            $mygroupid = mygroupid($course->id);
            $usergroup = user_group($course->id, $submitfor);
            if (!$submitforothers and !($submitatanytime and in_array($mygroupid, $usergroup))) {
                $submit = False;
            }
        }

        if ($submit) {
            $submit->userid = $submitfor ? $submitfor : $USER->id;
            $submit->programmingid = $a;
            if ($programming->presetcode) {
                $code = programming_submit_remove_preset($code);
            }
            $submit->code = trim($code);
            if ($submit->code == '') {
                $error = get_string('submitfailedemptycode', 'programming');
                $submit = False;
            }

            if ($submit) {
                programming_submit_add_instance($programming, $submit);
                if (!$submitfor) {
                    add_to_log($course->id, 'programming', 'submit', 'submit.php?a='.$programming->id, $programming->name);
                } else {
                    $u = get_record('user', 'id', $submitfor);
                    add_to_log($course->id, 'programming', 'submit for', 'submit.php?a='.$programming->id, $programming->name);
                }
            }
        }
    }

/// Print the page header
    setcookie($cookiename, $language, time() + 3600 * 24 * 60, $CFG->sessioncookiepath);

    if ($action && is_object($submit)) {
        $CFG->scripts[] = 'js/dp/shCore.js';
        $CFG->scripts[] = 'js/dp/shBrushCSharp.js';
        if ($action && is_object($submit)) {
            $CFG->scripts[] = 'submit_success.js';
        }
        $CFG->stylesheets[] = 'js/dp/SyntaxHighlighter.css';
    }
    $pagename = get_string('submit', 'programming');
    include_once('pageheader.php');

/// Print tabs
    $currenttab = 'submit';
    include_once('tabs.php');

/// Print the main part of the page

    if ($action && is_object($submit)) {
        include_once('submit_success.tpl.php');
    } else {
        include_once('submit.tpl.php');
    }

/// Finish the page
    print_footer($course);

?>
