<?PHP

    require_once('../../../config.php');
    require_once('../lib.php');
    require_once($CFG->dirroot.'/lib/tablelib.php');

    $a = optional_param('a', 0, PARAM_INT);     // programming ID

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

    add_to_log($course->id, 'programming', 'viewstat', 'viewresult.php?a='.$a, $programming->id);

/// Print the page header
    $pagename = get_string('reports', 'programming');
    $CFG->scripts[] = 'http://www.google.com/jsapi';
    include_once('../pageheader.php');

/// Print tabs
    $currenttab = 'reports';
    $currenttab2 = 'summary';
    include_once('../tabs.php');

/// Print the main part of the page
    echo '<div class="maincontent generalbox">';
    print_testcase_chart($programming->id);
    echo '</div>';

/// Finish the page
    print_footer($course);

function print_testcase_chart($programmingid) {
    global $CFG;

    $j = array('AC', 'WA', 'RE');
    $sql = "SELECT * FROM (
              SELECT id AS testid, seq, weight, pub
                FROM {$CFG->prefix}programming_tests
               WHERE programmingid = {$programmingid}
            ) AS pt";
    foreach ($j as $r) {
        $sql .= " LEFT JOIN \n";
        $sql .= "(SELECT testid, COUNT(*) AS $r
                   FROM {$CFG->prefix}programming_result AS pr,
                        {$CFG->prefix}programming_test_results AS ptr
                  WHERE pr.programmingid = {$programmingid}
                    AND ptr.submitid = pr.latestsubmitid
                    AND ptr.judgeresult='$r'
                  GROUP BY testid) AS SE{$r}";
        $sql .= " USING (testid)\n";
    }
    $sql .= "ORDER BY seq";

    #print "<pre>$sql</pre";


    $table = new flexible_table('testcase-table');
    $table->define_columns(array('seq', 'weight', 'pub', 'ac', 'wa', 're'));
    $headers = array(
            get_string('testcase', 'programming'),
            get_string('weight', 'programming'),
            get_string('public', 'programming'),
            get_string('AC', 'programming'),
            get_string('WA', 'programming'),
            get_string('RE', 'programming'),
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

    $rst = get_recordset_sql($sql);
    while ($row = $rst->FetchNextObject(false)) {
        $data = array();
        $data[] = $row->seq;
        $data[] = $row->weight;
        $data[] = programming_testcase_pub_getstring($row->pub);
        $data[] = $row->AC;
        $data[] = $row->WA;
        $data[] = $row->RE;
        $table->add_data($data);
    }
    $rst->Close();

    $table->print_html();

    return 0;
}

?>
