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
    print_judgeresult_chart();
    echo '</div>';

/// Finish the page
    print_footer($course);

function count_judgeresult() {
    global $CFG, $a;
    
    $sql = "SELECT ps.judgeresult AS judgeresult, COUNT(*) AS count
              FROM {$CFG->prefix}programming_submits AS ps,
                   {$CFG->prefix}programming_result AS pr
             WHERE pr.programmingid = {$a}
               AND ps.programmingid = {$a}
               AND pr.latestsubmitid = ps.id
          GROUP BY ps.judgeresult";
    $rst = get_recordset_sql($sql);
    $ret = array();
    while ($row = $rst->FetchNextObject(false)) {
        $ret[$row->judgeresult] = $row->count;
    }
    $rst->Close();
    return $ret;
}

function print_judgeresult_chart() {
    $c = count_judgeresult();
    $keys = array('AC', 'PE', 'WA', 'TLE', 'MLE', 'OLE', 'CE');
    $setvalues = '';
    $i = 0;
    foreach ($keys as $key) {
        $name = get_string($key, 'programming');
        if (!array_key_exists($key, $c)) $c[$key] = 0;
        $setvalues .= "data.setValue($i, 0, '$name');\n";
        $setvalues .= "data.setValue($i, 1, $c[$key]);\n";
        $c[$key] = 0;
        $i++;
    }
    $others = 0; foreach ($c as $key => $value) $others += $value;
    $name = get_string('others', 'programming');
    $setvalues .= "data.setValue($i, 0, '$name');\n";
    $setvalues .= "data.setValue($i, 1, $others);\n";
    $i++;
    $strjudgeresultchart = get_string('judgeresultcountchart', 'programming');

    echo '<div id="summary-charts" style="height: 400px; margin: 0 auto; width: 800px;">';
    echo "<div id='count-judge-result-chart'> </div>";
    echo '</div>';

    echo "
<script type='text/javascript'>
    google.load('visualization', '1', {packages:['piechart']});
    google.setOnLoadCallback(drawChart);
    function drawChart() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Result');
        data.addColumn('number', 'Count');
        data.addRows($i);
        $setvalues;
        var chart = new google.visualization.PieChart(document.getElementById('count-judge-result-chart'));
        chart.draw(data, {width: 600, height: 400, is3D: true, title: '$strjudgeresultchart'});
    }
</script>";
}

?>
