<?PHP // $Id: index.php,v 1.1 2003/09/30 02:45:19 moodler Exp $

/// This page lists all the instances of programming in a particular course
/// Replace programming with the name of your module

    require_once('../../config.php');
    require_once('lib.php');

    $id = required_param('id', PARAM_INT);   // course

    if (! $course = get_record('course', 'id', $id)) {
        error('Course ID is incorrect');
    }

    require_login($course->id);

    add_to_log($course->id, 'programming', 'view index', "index.php?id=$course->id", '');


/// Get all required strings

    $strprogrammings = get_string('modulenameplural', 'programming');
    $strprogramming  = get_string('modulename', 'programming');


/// Print the header
    $title = '';
    include_once('pageheader.php');

    $currenttab = 'result';
    include_once('index_tabs.php');

/// Get all the appropriate data

    if (! $programmings = get_all_instances_in_course('programming', $course)) {
        notice('There are no programmings', '../../course/view.php?id='.$course->id);
        die;
    }

/// Print the list of instances (your module will probably extend this)

    $timenow = time();
    $strname  = get_string('name');
    $strweek  = get_string('week');
    $strtopic  = get_string('topic');
    $strlinecount = get_string('linecount', 'programming');
    $strtotal = get_string('total', 'programming');
    $strjudgeresult = get_string('judgeresult', 'programming');
    $strna = get_string('n/a', 'programming');
    $strglobalid = get_string('globalid', 'programming');
    $strsubmitcount = get_string('submitcount', 'programming');
    $strlanguage = get_string('language', 'programming');

    $submits = "SELECT p.id, submitcount,
                       ps.id AS submitid, codelines, codesize, ps.timemodified,
                       ps.status AS status, pl.name AS lang
                  FROM {$CFG->prefix}programming AS p,
                       {$CFG->prefix}programming_result AS pr,
                       {$CFG->prefix}programming_submits AS ps,
                       {$CFG->prefix}programming_languages AS pl
                 WHERE p.course={$id}
                   AND p.id = pr.programmingid
                   AND pr.userid={$USER->id}
                   AND pr.latestsubmitid=ps.id
                   AND ps.language=pl.id";
    $submits = get_records_sql($submits);
    
    if (is_array($submits)) {
        foreach($submits as $submit) {
            if ($submit->status == PROGRAMMING_STATUS_COMPILEFAIL) {
                $submit->judgeresult = get_string('CE', 'programming');
            }
            else if ($submit->status == PROGRAMMING_STATUS_FINISH) {
                $tr = get_records('programming_test_results', 'submitid', $submit->submitid);
                $submit->judgeresult = programming_contest_get_judgeresult($tr);
            }
        }
    } else {
        $submits = array();
    }

    if ($course->format == 'weeks') {
        $table->head  = array ($strweek);
        $table->align = array ('CENTER');
    } else if ($course->format == 'topics') {
        $table->head  = array ($strtopic);
        $table->align = array ('CENTER');
    } else if ($course->format == 'proglist') {
        $table->head = array($strglobalid);
        $table->align = array('CENTER');
    } else {
        $table->head  = array ();
        $table->align = array ();
    }
    $table->head = array_merge($table->head, array($strname, $strjudgeresult, $strlanguage, $strlinecount, $strsubmitcount));
    $table->align = array_merge($table->align, array('LEFT', 'CENTER', 'CENTER', 'CENTER', 'CENTER'));

    $totallines = $totalsubmit = 0;
    foreach ($programmings as $programming) {
        $submit = null;
        if (array_key_exists($programming->id, $submits)) {
            $submit = $submits[$programming->id];
        }

        if ($submit) {
            $totallines += $submit->codelines;
            $totalsubmit += $submit->submitcount;
        }

        $link = $resultlink = $countlink = $langlink = $codelink = '';
        if (!$programming->visible) {
            //Show dimmed if the mod is hidden
            $link = "<a class=\"dimmed\" href=\"view.php?id=$programming->coursemodule\">$programming->name</a>";
            if ($submit) {
                $resultlink = '<a class="dimmed" href="result.php?a='.$submit->id.'">'.$submit->judgeresult.'</a>';
                $countlink = '<a class="dimmed" href="history.php?a='.$submit->id.'">'.$submit->submitcount.'</a>';
                $langlink = '<a class="dimmed" href="history.php?a='.$submit->id.'">'.$submit->lang.'</a>';
                $codelink= '<a class="dimmed" href="history.php?a='.$submit->id.'">'.$submit->codelines.'</a>';
            }
        } else {
            //Show normal if the mod is visible
            $link = "<a href=\"view.php?id=$programming->coursemodule\">$programming->name</a>";
            if ($submit) {
                $resultlink = '<a href="result.php?a='.$submit->id.'">'.$submit->judgeresult.'</a>';
                $countlink = '<a href="history.php?a='.$submit->id.'">'.$submit->submitcount.'</a>';
                $langlink = '<a href="history.php?a='.$submit->id.'">'.$submit->lang.'</a>';
                $codelink= '<a href="history.php?a='.$submit->id.'">'.$submit->codelines.'</a>';
            }
        }

        if ($course->format == 'weeks' or $course->format == 'topics') {
            $section = array($programming->section);
        } else if ($course->format == 'proglist') {
            $section = array($programming->globalid);
        } else {
            $section = array();
        }
        if ($submit) {
            $table->data[] = array_merge($section, array($link, $resultlink, $langlink, $codelink, $countlink));
        } else {
            $table->data[] = array_merge($section, array($link, '', '', '', ''));
        }
    }

    if (in_array($course->format, array('weeks', 'topics', 'proglist'))) {
        $table->data[] = array($strtotal, '', '', '', $totallines, $totalsubmit);
    } else {
        $table->data[] = array($strtotal, '', '', $totallines, $totalsubmit);
    }

    echo '<div class="maincontent generalbox">';
    echo '<h1>'.get_string('result', 'programming').'</h1>';
    print_table($table);
    echo '</div>';

/// Finish the page

    print_footer($course);

?>
