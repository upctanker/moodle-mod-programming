<?PHP

    require_once('../../../config.php');
    require_once('../lib.php');
    require_once('reports.lib.php');

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
        stat_group($group, $stat_results);
    }
    stat_all($stat_results);
    $mygroupid = mygroupid($course->id);

    add_to_log($course->id, 'programming', 'viewstat', 'viewresult.php?a='.$a, $programming->id);

    include_once('summary.tpl.php');
?>
