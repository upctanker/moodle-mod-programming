<?php

    require_once('../../config.php');
    require_once('lib.php');

    $a = optional_param('a', 0, PARAM_INT);     // programming ID
    $groupid = optional_param('group', 0, PARAM_INT);
    $latestonly = optional_param('latestonly', 1, PARAM_INT);
    $orderby = optional_param('orderby', 'submittime desc', PARAM_CLEAN);
    $page = optional_param('page', 0, PARAM_INT);
    $perpage = optional_param('perpage', 10, PARAM_INT);
    $offset = $page * $perpage;

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
    $submitforothers = has_capability('mod/programming:submitforothers', $context);
    $deleteothersubmit = has_capability('mod/programming:deleteothersubmit', $context);
    $viewotherresult = has_capability('mod/programming:viewotherresult', $context);
    $viewotherprogram = has_capability('mod/programming:viewotherprogram', $context);


    $isteacher = isteacher($course->id);
    $isteacheredit = isteacheredit($course->id);

    $mygroupid = mygroupid($course->id);
    if (!$isteacher && !empty($mygroupid) && $groupid == 0) {
        $groupid = $mygroupid[0];
    }

    // For students only show latest
    if (!$isteacher) $latestonly = 1;

    // Get submits from database
    list($orderbyfield, $orderbyorder) = explode(' ', $orderby);
    $orderbyorder = $orderbyorder == 'desc' ? 'DESC' : 'ASC';
    $orderbyfield = $orderbyfield == 'fullname' ? 'displayname' : $orderbyfield;
    switch ($orderbyfield) {
    case 'submittime':
        if ($groupid) {
            $inner_sql = "SELECT ipr.userid, latestsubmitid AS latest_id
                            FROM {$CFG->prefix}programming_result AS ipr,
                                 {$CFG->prefix}groups_members AS igm
                           WHERE programmingid={$programming->id}
                             AND igm.groupid={$groupid}
                             AND ipr.userid=igm.userid 
                        ORDER BY latest_id {$orderbyorder}";
            $count_sql = "SELECT COUNT(*) AS c
                            FROM {$CFG->prefix}programming_result AS ipr,
                                 {$CFG->prefix}groups_members AS igm
                           WHERE programmingid={$programming->id}
                             AND igm.groupid={$groupid}
                             AND ipr.userid=igm.userid";
        } else {
            $inner_sql = "SELECT userid, latestsubmitid AS latest_id
                            FROM {$CFG->prefix}programming_result
                           WHERE programmingid={$programming->id}
                        ORDER BY latest_id {$orderbyorder}";
            $count_sql = "SELECT COUNT(*) AS c
                            FROM {$CFG->prefix}programming_result
                           WHERE programmingid={$programming->id}";
        }
        $sql = "SELECT ps.*, pl.name AS langname
                  FROM ({$inner_sql} LIMIT {$offset}, {$perpage}) AS latest,
                       {$CFG->prefix}programming_submits AS ps,
                       {$CFG->prefix}programming_languages AS pl
                 WHERE ps.programmingid={$programming->id}
                   AND latest.userid = ps.userid
                   AND ps.language=pl.id
              ORDER BY latest.latest_id {$orderbyorder},
                       ps.timemodified DESC";
        break;
    case 'linecount':
        if ($groupid) {
            $inner_sql = "SELECT ps0.id, ps0.userid, ps0.codelines
                            FROM {$CFG->prefix}programming_result AS ipr, 
                                 {$CFG->prefix}groups_members AS igm,
                                 {$CFG->prefix}programming_submits AS ps0
                           WHERE ipr.programmingid={$programming->id}
                             AND igm.groupid={$groupid}
                             AND ipr.userid=igm.userid
                             AND ps0.programmingid={$programming->id}
                             AND ps0.id=latestsubmitid
                        ORDER BY ps0.codelines {$orderbyorder}";
            $count_sql = "SELECT COUNT(*) c
                            FROM {$CFG->prefix}programming_result AS ipr,
                                 {$CFG->prefix}groups_members AS igm
                           WHERE programmingid={$programming->id}
                             AND igm.groupid={$groupid}
                             AND ipr.userid=igm.userid";
        } else {
            $inner_sql = "SELECT ips.id, ips.userid, ips.codelines
                            FROM {$CFG->prefix}programming_result AS ipr, 
                                 {$CFG->prefix}programming_submits AS ips
                           WHERE ipr.programmingid={$programming->id}
                             AND ips.programmingid={$programming->id}
                             AND ips.id=ipr.latestsubmitid
                        ORDER BY ips.codelines {$orderbyorder}";
            $count_sql = "SELECT COUNT(*) AS c
                            FROM {$CFG->prefix}programming_result
                           WHERE programmingid={$programming->id}";
        }
        $sql = "SELECT ps.*, pl.name AS langname
                  FROM ({$inner_sql} LIMIT {$offset}, {$perpage}) AS limited,
                       {$CFG->prefix}programming_submits AS ps,
                       {$CFG->prefix}programming_languages AS pl
                 WHERE ps.programmingid={$programming->id}
                   AND limited.userid=ps.userid 
                   AND ps.language=pl.id
              ORDER BY limited.codelines {$orderbyorder}, timemodified DESC";
        break;
    }
    $totalcount = count_records_sql($count_sql);
    $submits = get_records_sql($sql);

    // Generate usersubmits for output
    $uids = array();
    $usersubmits = array();
    foreach ($submits as $submit) {
        if (!array_key_exists($submit->userid, $usersubmits)) {
            $usersubmits[$submit->userid] = array();
        }
        array_push($usersubmits[$submit->userid], $submit);
        if (!in_array($submit->userid, $uids)) {
            array_push($uids, $submit->userid);
        }
    }
    // Get users object
    $users = get_records_select('user', 'id in ('.implode(',', $uids).')');
    unset($uids);
    // Get results object
    $sids = array_keys($submits);
    $sresults = get_records_select('programming_test_results', 'submitid in ('.implode(',', $sids).')');
    unset($sids);
    $results = array();
    foreach ($sresults as $sresult) {
        if (!array_key_exists($sresult->submitid, $results)) {
            $results[$sresult->submitid] = array();
        }
        array_push($results[$sresult->submitid], $sresult);
    }

    $groupmode = groupmode($course, $cm);
    $groups = get_groups($course->id);

    add_to_log($course->id, 'programming', 'reports_detail');

    include_once('reports_detail.tpl.php');
?>
