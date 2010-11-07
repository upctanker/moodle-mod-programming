<?PHP // $Id: index.php,v 1.1 2003/09/30 02:45:19 moodler Exp $

/// This page lists all the instances of programming in a particular course
/// Replace programming with the name of your module

    require_once('../../../config.php');
    require_once('../lib.php');

    $id = required_param('id', PARAM_INT);   // course

    if (! $course = get_record('course', 'id', $id)) {
        error('Course ID is incorrect');
    }

    require_login($course->id);

    add_to_log($course->id, 'programming', 'view index resemble', "index_resemble.php?id=$course->id", '');


/// Get all required strings

    $strprogrammings = get_string('modulenameplural', 'programming');
    $strprogramming  = get_string('modulename', 'programming');


/// Print the header
    $title = get_string('resemble', 'programming');
    include_once('../pageheader.php');

    $currenttab = 'resemble';
    include_once('../index_tabs.php');

/// Get all the appropriate data

    if (! $programmings = get_all_instances_in_course('programming', $course)) {
        notice('There are no programmings', '../../course/view.php?id='.$course->id);
        die;
    }

/// Print the list of instances (your module will probably extend this)

    $strname = get_string('name');
    $strsimilitudedegree = get_string('similitudedegree', 'programming');
    $strprogram1 = get_string('program1', 'programming');
    $strpercent1 = get_string('percent1', 'programming');
    $strprogram2 = get_string('program2', 'programming');
    $strpercent2 = get_string('percent2', 'programming');
    $strmatchedlines = get_string('matchedlines', 'programming');
    $strmediumdegree = get_string('mediumsimilitude', 'programming');
    $strhighdegree = get_string('highsimilitude', 'programming');

    if (! $programmings = get_all_instances_in_course('programming', $course)) {
        notice('There are no programmings', '../../course/view.php?id='.$course->id);
        die;
    }
    $sql = "SELECT pr.*, p.name, ps1.userid AS userid1, ps2.userid AS userid2
              FROM {$CFG->prefix}programming_resemble pr,
                   {$CFG->prefix}programming p,
                   {$CFG->prefix}programming_submits ps1,
                   {$CFG->prefix}programming_submits ps2
             WHERE p.course = $id
               AND pr.flag > 0
               AND pr.programmingid = p.id
               AND ps1.programmingid = p.id
               AND ps2.programmingid = p.id
               AND pr.submitid1 = ps1.id
               AND pr.submitid2 = ps2.id
               AND (ps1.userid = $USER->id OR ps2.userid = $USER->id)
          ORDER BY p.id";
    $rows = get_records_sql($sql);
    $uids = array();
    if (is_array($rows)) {
        foreach ($rows as $row) {
            if (!in_array($row->userid1, $uids)) $uids[] = $row->userid1;
            if (!in_array($row->userid2, $uids)) $uids[] = $row->userid2;
        }
    }
    if (count($uids) > 0) {
        $uids = implode(',', $uids);
        $sql = "SELECT * FROM mdl_user WHERE id IN ($uids)";
        $users = get_records_sql($sql);
    } else {
        $users = array();
    }

    $table->head = array($strname, $strsimilitudedegree, $strprogram1, $strpercent1, $strprogram2, $strpercent2, $strmatchedlines);
    $table->align = array('LEFT', 'CENTER', 'CENTER', 'CENTER', 'CENTER', 'CENTER', 'CENTER');
    foreach ($programmings as $programming) {
        if (is_array($rows)) {
            foreach ($rows as $row) {
                if ($row->programmingid != $programming->id) continue;

                switch($row->flag) {
                case PROGRAMMING_RESEMBLE_WARNED:
                    $styleclass1 = $styleclass2 = 'warned';
                    $degree = $strmediumdegree;
                    break;
                case PROGRAMMING_RESEMBLE_CONFIRMED:
                    $styleclass1 = $styleclass2 = 'confirmed';
                    $degree = $strhighdegree;
                    break;
                case PROGRAMMING_RESEMBLE_FLAG1:
                    $styleclass1 = 'confirmed';
                    $styleclass2 = '';
                    $degree = $strhighdegree;
                    break;
                case PROGRAMMING_RESEMBLE_FLAG2:
                    $styleclass1 = '';
                    $styleclass2 = 'confirmed';
                    $degree = $strhighdegree;
                    break;
                case PROGRAMMING_RESEMBLE_FLAG3:
                    $styleclass1 = $styleclass2 = 'flag3';
                    $degree = $strhighdegree;
                    break;
                default:
                    $styleclass1 = $styleclass2 = '';
                }

                $user1 = print_user_picture($row->userid1, $course->id, $users[$row->userid1]->picture, 0, true).fullname($users[$row->userid1]);
                $user2 = print_user_picture($row->userid2, $course->id, $users[$row->userid2]->picture, 0, true).fullname($users[$row->userid2]);

                $table->data[] = array(
                    "<a href='view.php?a=$row->programmingid'>$row->name</a>",
                    $degree,
                    "<span class='$styleclass1'>$user1</span>",
                    "<span class='$styleclass1'>$row->percent1</span>",
                    "<span class='$styleclass2'>$user2</span>",
                    "<span class='$styleclass2'>$row->percent2</span>",
                    "<a href='resemble_compare.php?a=$row->programmingid&amp;rid=$row->id'>$row->matchedcount</a>");
            }
        }
    }

    echo '<div class="maincontent generalbox">';
    echo '<h1>'.get_string('resemble', 'programming').'</h1>';
    if (is_array($rows)) {
        print_table($table);
    } else {
        echo get_string('noresemble', 'programming');
    }
    echo '</div>';

/// Finish the page

    print_footer($course);

?>
