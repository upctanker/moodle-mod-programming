<?php

    require_once('../../../config.php');
    require_once('../lib.php');
    require_once($CFG->dirroot.'/lib/tablelib.php');
    require_once('best_search_form.php');

    $a = optional_param('a', 0, PARAM_INT);     // programming ID
    $page = optional_param('page', 0, PARAM_INT);
    $perpage = optional_param('perpage', 10, PARAM_INT);
    $tsort = optional_param('tsort', 'timemodified', PARAM_CLEAN);
    $language = optional_param('language', '', PARAM_INT);
    $groupid = optional_param('group', 0, PARAM_INT);

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
    $viewotherresult = has_capability('mod/programming:viewotherresult', $context);
    $viewotherprogram = has_capability('mod/programming:viewotherprogram', $context);

    add_to_log($course->id, 'programming', 'reports_best');

/// Print the page header
    $pagename = get_string('detailreport', 'programming'); 
    include_once('../pageheader.php');

/// Print tabs
    $currenttab = 'reports';
    $currenttab2 = 'detail';
    include_once('../tabs.php');

/// Print the main part of the page
    echo '<div class="maincontent">';
    echo '<h1>'.get_string('bestprograms', 'programming').'</h1>';
    print_search_form();
    print_submit_table();
    echo '</div>';

/// Finish the page
    print_footer($course);

function print_search_form() {
    global $a, $page, $perpage, $groupid, $language;
    $values = array('a' => $a, 'page' => $page, 'perpage' => $perpage,
                    'group' => $groupid, 'language' => $language);

    $mform = new best_search_form(null, null, 'get');
    $mform->set_data($values);
    $mform->display();
    echo '
<script type="text/javascript" language="JavaScript">
$(document).ready(function() {
    $(".maincontent .mform select").change(function() {
         $(".maincontent .mform").submit();
    });
});
</script>';
}

function get_submits($orderby) {
    global $CFG, $page, $perpage, $programming, $course, $language, $groupid;

    $gfrom = $gwhere = '';
    if ($groupid) {
        $gfrom = ", {$CFG->prefix}groups_members AS gm";
        $gwhere = " AND gm.groupid = $groupid AND gm.userid = ps.userid";
    }

    $lwhere = '';
    if ($language) {
        $lwhere = " AND ps.language = $language";
    }
    
    $submits = 0;
    $total = 0;
    $crit = " FROM {$CFG->prefix}programming_submits AS ps,
                   {$CFG->prefix}programming_result AS pr
                   $gfrom
             WHERE ps.programmingid = {$programming->id}
               AND pr.programmingid = {$programming->id}
               AND pr.latestsubmitid = ps.id
               AND ps.judgeresult = 'AC'
                   $gwhere $lwhere
          ORDER BY $orderby";
    $sql = "SELECT ps.* $crit";
    $submits = get_records_sql($sql, $page * $perpage, $perpage);
    $sql = "SELECT COUNT(*) $crit";
    $total = count_records_sql($sql);

    return array($submits, $total);
}

function print_submit_table() {
    global $CFG, $page, $perpage, $programming, $course, $language, $groupid;
    global $viewotherresult, $viewotherprogram;

    $table = new flexible_table('detail-table');
    $def = array('rank', 'ps.timemodified', 'user', 'language', 'code', 'ps.timeused', 'ps.memused');
    $table->define_columns($def);
    $headers = array(
        get_string('rank', 'programming'),
        get_string('submittime', 'programming'),
        get_string('fullname'),
        get_string('language', 'programming'),
        get_string('programcode', 'programming'),
        get_string('timeused', 'programming'),
        get_string('memused', 'programming'),
        );
    $table->define_headers($headers);

    #$table->pagesize($perpage, $total);
    $table->set_attribute('cellspacing', '0');
    $table->set_attribute('id', 'detail-table');
    $table->set_attribute('class', 'generaltable generalbox');
    $table->set_attribute('align', 'center');
    $table->set_attribute('cellpadding', '3');
    $table->set_attribute('cellspacing', '1');
    $table->sortable(true, 'ps.timeused');
    $table->no_sorting('rank');
    $table->no_sorting('user');
    $table->no_sorting('language');
    $table->no_sorting('code');
    $table->setup();
    $orderby = $table->get_sql_sort();

    list($submits, $totalcount) = get_submits($orderby);
    if (is_array($submits)) {
        $i = 0;
        $lang = get_records('programming_languages');
        foreach ($submits as $submit) {
            $data = array();
            $data[] = ++$i;
            $data[] = userdate($submit->timemodified, '%Y-%m-%d %H:%M:%S');
            $data[] = "<a href='{$CFG->wwwroot}/user/view.php?id={$submit->userid}&amp;course={$course->id}'>".fullname(get_record('user', 'id', $submit->userid)).'</a>';
            $data[] = $lang[$submit->language]->name;
            if ($viewotherprogram) {
                $data[] = "<a href='{$CFG->wwwroot}/mod/programming/history.php?a={$programming->id}&amp;userid={$submit->userid}&amp;submitid={$submit->id}'>".get_string('sizelines', 'programming', $submit).'</a>';
            } else {
                $data[] = get_string('sizelines', 'programming', $submit);
            }
            if ($submit->judgeresult) {
                $data[] = round($submit->timeused, 3);
                $data[] = get_string('memusednk', 'programming', $submit->memused);
            } else {
                $data[] = ''; $data[] = ''; $data[] = '';
            }
            $table->add_data($data);
        }

    }

    echo '<div class="maincontent2">';
    $table->print_html();

    print_paging_bar($totalcount, $page, $perpage, "{$CFG->wwwroot}/mod/programming/reports/best.php?a={$programming->id}&amp;perpage=$perpage&amp;language=$language&amp;group=$groupid&amp;");
    echo '</div>';

}

?>
