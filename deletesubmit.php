<?PHP  // $Id: view.php,v 1.1 2003/09/30 02:45:19 moodler Exp $

/// This page prints a particular instance of programming
/// (Replace programming with the name of your module)

    require_once('../../config.php');
    require_once('lib.php');

    $submitid = optional_param('submitid', 0, PARAM_INT);
    $confirm = optional_param('confirm');
    $href = optional_param('href');

    if (! $submit = get_record('programming_submits', 'id', $submitid)) {
        error('Submit can\'t be find');
    }

    if (! $programming = get_record('programming', 'id', $submit->programmingid)) {
        error('Course module is incorrect');
    }
    $a = $programming->id;

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

    if ($course->category) {
        $navigation = '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$course->id.'">'.$course->shortname.'</A> ->';
    }

    $strprogrammings = get_string('modulenameplural', 'programming');
    $strprogramming  = get_string('modulename', 'programming');

    $CFG->stylesheets[] = $CFG->wwwroot.'/mod/programming/highlight.css';
    print_header("$course->shortname: $programming->name", $course->fullname,
                 $navigation.'<a href="index.php?id='.$course->id.'">'.$strprogrammings.'</a> -> <a href="view.php?a='.$a.'">'.$programming->name.'</a> -> '.get_string('delete'),
                  '', '', true, update_module_button($cm->id, $course->id, $strprogramming), 
                  navmenu($course, $cm));

/// Print the main part of the page

    if (is_object($submit)) {
        if ($confirm) {
            programming_delete_submit($submit);
            add_to_log($course->id, 'programming', 'delete submit', '', $submitid);

            echo '<p align="center">'.get_string('deleted').'</p>';
            echo '<p align="center"><a href="'.$href.'">'.get_string('continue').'</a></p>';
        } else {
            $owner = get_record('user', 'id', $submit->userid);
            echo '<table align="center" width="60%" class="noticebox" border="0" cellpadding="20" cellspacing="0">';
            echo '<tr><td bgcolor="#FFAAAA" class="noticeboxcontent">';
            echo '<h2 class="main">'.get_string('deletesubmitconfirm', 'programming', fullname($owner)).'</h2>';
            echo '<form name="form" method="get" action="deletesubmit.php">';
            echo '<input type="hidden" name="submitid" value="'.$submit->id.'" />';
            echo '<input type="hidden" name="confirm" value="1" />';
            echo '<input type="hidden" name="href" value="'.$_SERVER['HTTP_REFERER'].'" />';
            echo '<input type="submit" value=" '.get_string('yes').' " /> ';
            echo '<input type="button" value=" '.get_string('no').' " onclick="javascript:history.go(-1);" />';

            echo '</form>';
            echo '</td></tr></table>';
        }
    }

/// Finish the page
    print_footer($course);

?>
