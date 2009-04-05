<?PHP

    require_once('../../../config.php');
    require_once('../lib.php');
    require_once('reports.lib.php');
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

    // results is stored in a array
    $stat_results = array();
    $groupnum = count_records('groups', 'courseid', $course->id);
    $groups = get_records('groups', 'courseid', $course->id);
    foreach($groups as $group) {
        summary_stat($stat_results, $group);
    }
    summary_stat($stat_results);
    $mygroupid = mygroupid($course->id);

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
    echo '<h1>'.get_string('summary', 'programming').'</h1>';
    print_summary_table($stat_results);
    print_summary_chart($stat_results);
    echo '</div>';

/// Finish the page
    print_footer($course);

function print_summary_table($stat_results) {
    global $CFG, $course;

    $table = new flexible_table('summary-stat-table');
    $def = array('range', 'studentcount', 'submitcount', 'submitpercent', 'compilecount', 'compilepercent', 'passedcount', 'passedpercent', 'intimepassedcount', 'intimepassedpercent', 'codelines');
    $table->define_columns($def);
    $headers = array(
        get_string('statrange', 'programming'),
        get_string('statstudentcount', 'programming', $course->students),
        get_string('statsubmitcount', 'programming'),
        '%',
        get_string('statcompiledcount', 'programming'),
        '%',
        get_string('statpassedcount', 'programming'),
        '%',
        get_string('statintimepassedcount', 'programming'),
        '%',
        get_string('stataveragelines', 'programming'));
    $table->define_headers($headers);

    $table->set_attribute('cellspacing', '0');
    $table->set_attribute('id', 'summary-stat-table');
    $table->set_attribute('class', 'generaltable generalbox');
    $table->set_attribute('align', 'center');
    $table->set_attribute('cellpadding', '3');
    $table->set_attribute('cellspacing', '1');
    $table->setup();

    foreach ($stat_results as $row) {
        $data = array();
        $data[] = $row['name'];
        $data[] = $row['studentcount'];
        $data[] = $row['submitcount'];
        $data[] = ($row['studentcount'] > 0 ? round($row['submitcount'] / $row['studentcount'] * 100, 0) : 0).'%';
        $data[] = $row['compiledcount'];
        $data[] = ($row['studentcount'] > 0 ? round($row['compiledcount'] / $row['studentcount'] * 100, 0) : 0).'%';
        $data[] = $row['passedcount'];
        $data[] = ($row['studentcount'] > 0 ? round($row['passedcount'] / $row['studentcount'] * 100, 0) : 0).'%';
        $data[] = $row['intimepassedcount'];
        $data[] = ($row['studentcount'] > 0 ? round($row['intimepassedcount'] / $row['studentcount'] * 100, 0) : 0).'%';
        $data[] = $row['submitcount'] > 0 ? round($row['averagelines']) : 0;
        $table->add_data($data);
    }

    $table->print_html();
}

function print_summary_chart($stat_results) {
    $summary = array_pop($stat_results);
    $acintime = $summary['intimepassedcount'];
    $ac = $summary['passedcount'] - $summary['intimepassedcount'];
    $se = $summary['compiledcount'] - $summary['passedcount'];
    $ce = $summary['submitcount'] - $summary['compiledcount'];
    $ns = $summary['studentcount'] - $summary['submitcount'];
    $strresultcount = get_string('resultcountchart', 'programming');
    $stracintime = get_string('resultchartacintime', 'programming');
    $strac = get_string('resultchartacdiscount', 'programming');
    $strse = get_string('resultchartsomethingwrong', 'programming');
    $strce = get_string('resultchartcompileerror', 'programming');
    $strns = get_string('resultchartnosubmition', 'programming');
    $strgroupresultcount = get_string('resultgroupcountchart', 'programming');

    $groupcount = count($stat_results);
    $style = $setvalues = '';
    if ($groupcount) {
        $i = 0;
        foreach ($stat_results as $group) {
            $acintime1 = $group['intimepassedcount'];
            $ac1 = $group['passedcount'] - $group['intimepassedcount'];
            $se1 = $group['compiledcount'] - $group['passedcount'];
            $ce1 = $group['submitcount'] - $group['compiledcount'];
            $setvalues .= "data.setValue($i, 0, '{$group['name']}');\n";
            $setvalues .= "data.setValue($i, 1, $acintime1);\n";
            $setvalues .= "data.setValue($i, 2, $ac1);\n";
            $setvalues .= "data.setValue($i, 3, $se1);\n";
            $setvalues .= "data.setValue($i, 4, $ce1);\n";
            $i++;
        }
        $style = 'style="float: left;"';
    }

    echo '<div id="summary-charts" style="height: 300px; margin: 0 auto; width: 900px;">';
    echo "<div id='summary-group-count-chart' $style> </div>";
    echo "<div id='summary-percent-chart' $style> </div>";
    echo '</div>';

    echo "
<script type='text/javascript'>
    google.load('visualization', '1', {packages:['piechart', 'columnchart']});
    google.setOnLoadCallback(drawChart);
    function drawChart() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Result');
        data.addColumn('number', 'Count');
        data.addRows(5);
        data.setValue(0, 0, '$stracintime');
        data.setValue(0, 1, $acintime);
        data.setValue(1, 0, '$strac');
        data.setValue(1, 1, $ac);
        data.setValue(2, 0, '$strse');
        data.setValue(2, 1, $se);
        data.setValue(3, 0, '$strce');
        data.setValue(3, 1, $ce);
        data.setValue(4, 0, '$strns');
        data.setValue(4, 1, $ns);
        var chart = new google.visualization.PieChart(document.getElementById('summary-percent-chart'));
        chart.draw(data, {width: 400, height: 300, is3D: true, title: '$strresultcount'});

        if ($groupcount) {
            data = new google.visualization.DataTable();
            data.addColumn('string', 'Group');
            data.addColumn('number', '$stracintime');
            data.addColumn('number', '$strac');
            data.addColumn('number', '$strse');
            data.addColumn('number', '$strce');
            data.addRows($groupcount);
            $setvalues
            chart = new google.visualization.ColumnChart(document.getElementById('summary-group-count-chart'));
            chart.draw(data, {width: 500, height: 300, is3D: true, title: '$strgroupresultcount'});
        }

    }
</script>";
}

?>
