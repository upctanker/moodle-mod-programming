<?PHP  // $Id: view.php,v 1.1 2003/09/30 02:45:19 moodler Exp $

/// This page prints a particular instance of programming
/// (Replace programming with the name of your module)

    require_once('../../config.php');
    require_once('lib.php');

    $submitid = optional_param('submitid');
    $a = optional_param('a');
    $confirm = optional_param('confirm');
    $href = optional_param('href', $_SERVER['HTTP_REFERER'], PARAM_URL);

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
    require_capability('mod/programming:deleteothersubmit', $context);

/// Print the page header
    $pagename = get_string('deletesubmits', 'programming');
    include_once('pageheader.php');

/// Print the main part of the page
    if ($confirm) {
        foreach ($submitid as $id) {
            $submit = get_record('programming_submits', 'id', $id);
            if ($submit) programming_delete_submit($submit);
        }
        add_to_log($course->id, 'programming', 'delete submit', '', implode($submitid, ','));

        echo '<div class="maincontent generalbox">';
        echo '<p align="center">'.get_string('deleted').'</p>';
        echo '<p align="center"><a href="'.$href.'">'.get_string('continue').'</a></p>';
        echo '</div>';
    } else {
        echo '<table align="center" width="60%" class="noticebox" border="0" cellpadding="20" cellspacing="0">';
        echo '<tr><td bgcolor="#FFAAAA" class="noticeboxcontent">';
        echo '<h2 class="main">'.get_string('deletesubmitconfirm', 'programming').'</h2>';
        echo '<ul>';
        foreach ($submitid as $id) {
            $submit = get_record('programming_submits', 'id', $id);
            $tm = userdate($submit->timemodified);
            $user = fullname(get_record('user', 'id', $submit->userid));
            echo "<li>$id $user $tm</li>";
        }
        echo '</ul>';
        echo '<form name="form" method="post">';
        foreach ($submitid as $id) {
            echo "<input type='hidden' name='submitid[]' value='$id' />";
        }
        echo "<input type='hidden' name='a' value='$a' />";
        echo '<input type="hidden" name="confirm" value="1" />';
        echo "<input type='hidden' name='href' value='$href' />";
        echo '<input type="submit" value=" '.get_string('yes').' " /> ';
        echo '<input type="button" value=" '.get_string('no').' " onclick="javascript:history.go(-1);" />';

        echo '</form>';
        echo '</td></tr></table>';
    }

/// Finish the page
    print_footer($course);

?>
