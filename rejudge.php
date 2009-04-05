<?PHP

    require_once('../../config.php');
    require_once('lib.php');

    $a = optional_param('a', 0, PARAM_INT);     // programming ID
    $groupid = optional_param('groupid', 0, PARAM_INT);
    $submitid = optional_param('submitid');
    $confirm = optional_param('confirm');
    $href = optional_param('href', $_SERVER['HTTP_REFERER'], PARAM_URL);
    $ac = optional_param('ac', 0, PARAM_INT);

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

/// Print the page header
    $pagename = get_string('rejudge', 'programming');
    include_once('pageheader.php');

/// Print the main part of the page

    if (!empty($submitid) || $confirm) {
        programming_rejudge($programming, $submitid, $groupid, $ac);
        add_to_log($course->id, 'programming', 'rejudge', me(), $programming->id);
        echo '<div class="maincontent generalbox">';
        echo '<p>'.get_string('rejudgestarted', 'programming').'</p>';
        echo '<p><a href="'.$href.'">'.get_string('continue').'</a></p>';
        echo '</div>';
    } else {
        echo '<table class="noticebox" border="0" cellpadding="20" cellspacing="0">';
        echo '<tr><td class="noticeboxcontent">';
        echo '<h2 class="main">'.get_string('rejudgeprograms', 'programming', $programming).'</h2>';
        echo '<form name="form" method="get" action="rejudge.php">';
        echo '<input type="hidden" name="a" value="'.$a.'" />';
        echo '<input type="hidden" name="confirm" value="1" />';
        echo '<input type="hidden" name="href" value="'.$_SERVER['HTTP_REFERER'].'" />';
        echo '<p><input type="checkbox" name="ac" value="1" />'.get_string('rejudgeac', 'programming').'</p>';
        echo '<input type="submit" value=" '.get_string('yes').' " /> ';
        echo '<input type="button" value=" '.get_string('no').' " onclick="javascript:history.go(-1);" />';
        echo '</form>';
        echo '</td></tr></table>';
    }

/// Finish the page
    print_footer($course);

?>
