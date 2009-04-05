<?php

    require_once('../../../config.php');
    require_once('../lib.php');
    require_once($CFG->dirroot.'/lib/tablelib.php');
    require_once('detail_search_form.php');

    $a = optional_param('a', 0, PARAM_INT);     // programming ID
    $groupid = optional_param('group', 0, PARAM_INT);
    $page = optional_param('page', 0, PARAM_INT);
    $perpage = optional_param('perpage', 10, PARAM_INT);
    $latestonly = optional_param('latestonly', 1, PARAM_INT);
    $judgeresult = optional_param('judgeresult', '', PARAM_CLEAN);

    $firstinitial = optional_param('firstinitial', '', PARAM_CLEAN);
    $lastinitial = optional_param('lastinitial', '', PARAM_CLEAN);


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

    require_capability('mod/programming:viewreport', $context);
    $deleteothersubmit = has_capability('mod/programming:deleteothersubmit', $context);
    $viewotherresult = has_capability('mod/programming:viewotherresult', $context);
    $viewotherprogram = has_capability('mod/programming:viewotherprogram', $context);

    add_to_log($course->id, 'programming', 'reports_detail');

    list($submits, $totalcount) = get_submits();

/// Print the page header
    $pagename = get_string('detailreport', 'programming'); 
    include_once('../pageheader.php');

/// Print tabs
    $currenttab = 'reports';
    $currenttab2 = 'detail';
    include_once('../tabs.php');

/// Print the main part of the page
    echo '<div class="maincontent">';
    echo '<h1>'.get_string('allprograms', 'programming').'</h1>';
    print_search_form();
    print_initial($lastinitial, $firstinitial);
    if (is_array($submits)) {
        print_submit_table($submits, $totalcount);
    }
    print_paging_bar($totalcount, $page, $perpage, "{$CFG->wwwroot}/mod/programming/reports/detail.php?a={$programming->id}&amp;latestonly={$latestonly}&amp;group={$groupid}&amp;firstinitial={$firstinitial}&amp;lastinitial={$lastinitial}&amp;judgeresult={$judgeresult}&amp;");
    echo '</div>';

/// Finish the page
    print_footer($course);

function get_submits() {
    global $CFG, $page, $perpage, $programming, $course;
    global $firstinitial, $lastinitial, $latestonly, $groupid, $language;
    global $judgeresult;

    $submits = 0;
    $total = 0;
    if ($latestonly) {
        $rfrom = ", {$CFG->prefix}programming_result AS pr";
        $rwhere = " AND pr.programmingid = {$programming->id}
                    AND pr.latestsubmitid = ps.id";
    } else {
        $rfrom = $rwhere = '';
    }
    if ($firstinitial || $lastinitial) {
        $ufrom = ", {$CFG->prefix}user AS u";
        $uwhere = " AND u.firstnameletter LIKE '{$firstinitial}%'
                    AND u.lastnameletter LIKE '{$lastinitial}%'
                    AND u.id = ps.userid";
    } else {
        $ufrom = $uwhere = '';
    }
    if ($groupid) {
        $gfrom = ", {$CFG->prefix}groups_members AS gm";
        $gwhere = " AND gm.groupid = $groupid AND gm.userid = ps.userid";
    } else {
        $gfrom = $gwhere = '';
    }
    if ($judgeresult) {
        $jrwhere = " AND ps.judgeresult = '$judgeresult'";
    } else {
        $jrwhere = '';
    }

    $crit = " FROM {$CFG->prefix}programming_submits AS ps
                   $ufrom $rfrom $gfrom
             WHERE ps.programmingid = {$programming->id}
                   $uwhere $rwhere $gwhere $jrwhere
          ORDER BY ps.timemodified DESC";
    $sql = "SELECT ps.* $crit";
    $submits = get_records_sql($sql, $page * $perpage, $perpage);
    $sql = "SELECT COUNT(*) $crit";
    $total = count_records_sql($sql);

    return array($submits, $total);
}

function print_submit_table($submits, $total) {
    global $CFG, $page, $perpage, $programming, $course;
    global $viewotherresult, $viewotherprogram;

    $table = new flexible_table('detail-table');
    $def = array('id', 'timemodified', 'user', 'language', 'code', 'judgeresult', 'timeused', 'memused', 'select');
    $table->define_columns($def);
    $headers = array(
        get_string('ID', 'programming'),
        get_string('submittime', 'programming'),
        get_string('fullname'),
        get_string('language', 'programming'),
        get_string('programcode', 'programming'),
        get_string('result', 'programming'),
        get_string('timeused', 'programming'),
        get_string('memused', 'programming'),
        get_string('select')
        );
    $table->define_headers($headers);

    #$table->pagesize($perpage, $total);
    $table->set_attribute('cellspacing', '0');
    $table->set_attribute('id', 'detail-table');
    $table->set_attribute('class', 'generaltable generalbox');
    $table->set_attribute('align', 'center');
    $table->set_attribute('cellpadding', '3');
    $table->set_attribute('cellspacing', '1');
    $table->setup();

    $lang = get_records('programming_languages');
    foreach ($submits as $submit) {
        $data = array();
        $data[] = $submit->id;
        $data[] = userdate($submit->timemodified, '%Y-%m-%d %H:%M:%S');
        $data[] = "<a href='{$CFG->wwwroot}/user/view.php?id={$submit->userid}&amp;course={$course->id}'>".fullname(get_record('user', 'id', $submit->userid)).'</a>';
        $data[] = $lang[$submit->language]->name;
        if ($viewotherprogram) {
            $data[] = "<a href='{$CFG->wwwroot}/mod/programming/history.php?a={$programming->id}&amp;userid={$submit->userid}&amp;submitid={$submit->id}'>".get_string('sizelines', 'programming', $submit).'</a>';
        } else {
            $data[] = get_string('sizelines', 'programming', $submit);
        }
        if ($submit->judgeresult) {
            $strresult = get_string($submit->judgeresult, 'programming');
            if ($viewotherresult) {
                $data[] = "<a href='{$CFG->wwwroot}/mod/programming/result.php?a={$programming->id}&amp;submitid={$submit->id}'>$strresult</a>";
            } else {
                $data[] = $strresult;
            }
            $data[] = round($submit->timeused, 3);
            $data[] = get_string('memusednk', 'programming', $submit->memused);
        } else {
            $data[] = ''; $data[] = ''; $data[] = '';
        }
        $data[] = "<input class='selectsubmit' type='checkbox' name='submitid[]' value='$submit->id'></input>";
        $table->add_data($data);
    }

    echo "<form id='submitaction' method='post'>";
    echo "<input type='hidden' name='a' value='$programming->id' />";
    $table->print_html();
    echo '<div id="submitbuttons" style="display: none">';
    echo '<input id="rejudge" type="button" name="action" value="Rejudge" />';
    echo '<input id="delete" type="button" name="action" value="Delete" />';
    echo '</div>';
    echo '</form>';
    echo "
<script type='text/javascript' language='JavaScript'>
$(document).ready(function() {
    $('.selectsubmit').change(function() {
        var show = false;
        $('.selectsubmit').each(function() {
            if ($(this).attr('checked')) show = true;
        });
        if (show) {
            $('#submitbuttons').show();
            $('.paging').hide();
        } else {
            $('#submitbuttons').hide();
            $('.paging').show();
        }
    });
    $('#rejudge').click(function() {
        $('#submitaction').get(0).action = '{$CFG->wwwroot}/mod/programming/rejudge.php';
        $('#submitaction').submit();
    });
    $('#delete').click(function() {
        $('#submitaction').get(0).action = '{$CFG->wwwroot}/mod/programming/deletesubmit.php';
        $('#submitaction').submit();
    });
});
</script>";
}

function print_initial() {
    global $a, $latestonly, $lastinitial, $firstinitial, $groupid;
    global $judgeresult;

    $strall = get_string('all');
    $alphabet = explode(',', get_string('alphabet'));

    echo "<p style=\"text-align:center\">";
    $ne = get_string('nameedit', 'langconfig');
    for ($i = 0; $i < strlen($ne); $i++) {
        if (substr($ne, $i, 1) == 'F') {
            /// Bar of first initials
            if ($i > 0) echo '<br />';

            echo get_string("firstname")." : ";
            if ($firstinitial) {
                echo " <a href=\"detail.php?a=$a&amp;".
                     "latestonly=$latestonly&amp;".
                     "judgeresult={$judgeresult}&amp;".
                     "group=$groupid&amp;lastinitial=$lastinitial".
                     "\">$strall</a> ";
            } else {
                echo " <b>$strall</b> ";
            }
            foreach ($alphabet as $letter) {
                if ($letter == $firstinitial) {
                    echo " <b>$letter</b> ";
                } else {
                    echo " <a href=\"detail.php?a=$a&amp;".
                         "latestonly=$latestonly&amp;".
                         "judgeresult={$judgeresult}&amp;".
                         "group=$groupid&amp;lastinitial=$lastinitial&amp;".
                         "firstinitial=$letter\">$letter</a> ";
                }
            }
            if ($i > 0) echo '<br />';

        } else if (substr($ne, $i, 1) == 'L') {
            /// Bar of last initials
            if ($i > 0) echo '<br />';

            echo get_string("lastname")." : ";
            if ($lastinitial) {
                echo " <a href=\"detail.php?a=$a&amp;".
                     "latestonly=$latestonly&amp;".
                     "judgeresult={$judgeresult}&amp;".
                     "group=$groupid&amp;firstinitial=$firstinitial".
                     "\">$strall</a> ";
            } else {
                echo " <b>$strall</b> ";
            }
            foreach ($alphabet as $letter) {
                if ($letter == $lastinitial) {
                    echo " <b>$letter</b> ";
                } else {
                    echo " <a href=\"detail.php?a=$a&amp;".
                         "latestonly=$latestonly&amp;".
                         "judgeresult={$judgeresult}&amp;".
                         "group=$groupid&amp;firstinitial=$firstinitial&amp;".
                         "lastinitial=$letter\">$letter</a> ";
                }
            }
            if ($i > 0) echo '<br />';

        } // if
    } // for nameedit
    echo "</p>";
}

function print_search_form() {
    global $perpage, $page, $programming;
    global $latestonly, $lastinitial, $firstinitial, $groupid, $judgeresult;

    $values = array('a' => $programming->id,
                    'latestonly' => $latestonly,
                    'lastinitial' => $lastinitial,
                    'firstinitial' => $firstinitial,
                    'group' => $groupid,
                    'judgeresult' => $judgeresult,
                    'page' => $page,
                    'perpage' => $perpage);
    $mform = new detail_search_form(null, null, 'get');
    $mform->set_data($values);
    $mform->display();
    echo '
<script type="text/javascript" language="JavaScript">
$(document).ready(function() {
    $("#mform1 select").change(function() { $("#mform1").submit(); });
});
</script>';
}

?>
