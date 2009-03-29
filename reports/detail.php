<?php

    require_once('../../../config.php');
    require_once('../lib.php');

    $a = optional_param('a', 0, PARAM_INT);     // programming ID
    $groupid = optional_param('group', 0, PARAM_INT);
    $latestonly = optional_param('latestonly', 1, PARAM_INT);
    $orderby = optional_param('orderby', 'submittime desc', PARAM_CLEAN);
    $page = optional_param('page', 0, PARAM_INT);
    $perpage = optional_param('perpage', 10, PARAM_INT);
    $offset = $page * $perpage;
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
    $firstletter = "LCASE(SUBSTR(firstnameletter, 1, 1))";
    $lastletter = "LCASE(SUBSTR(lastnameletter, 1, 1))";
    switch ($orderbyfield) {
    case 'submittime':
        if ($groupid) {
            $inner_sql = "SELECT ipr.userid, latestsubmitid AS latest_id
                            FROM {$CFG->prefix}programming_result AS ipr,
                                 {$CFG->prefix}groups_members AS igm";
            if ($firstinitial || $lastinitial) {
                $inner_sql .= ", {$CFG->prefix}user AS iu";
            }
            $inner_sql .= " WHERE programmingid={$programming->id}
                             AND igm.groupid={$groupid}
                             AND ipr.userid=igm.userid";
            if ($firstinitial || $lastinitial) {
                $inner_sql .= " AND ipr.userid = iu.id";
            }
            if ($firstinitial) {
                $inner_sql .= " AND $firstletter = '$firstinitial'";
            }
            if ($lastinitial) {
                $inner_sql .= " AND $lastletter = '$lastinitial'";
            }
            $inner_sql .= " ORDER BY latest_id {$orderbyorder}";

            $count_sql = "SELECT COUNT(*) AS c
                            FROM {$CFG->prefix}programming_result AS ipr,
                                 {$CFG->prefix}groups_members AS igm";
            if ($firstinitial || $lastinitial) {
                $count_sql .= ", {$CFG->prefix}user u";
            }
            $count_sql .= " WHERE programmingid={$programming->id}
                             AND igm.groupid={$groupid}
                             AND ipr.userid=igm.userid";
            if ($firstinitial || $lastinitial) {
                $count_sql .= " AND ipr.userid = u.id";
            }
            if ($firstinitial) {
                $count_sql .= " AND $firstletter = '$firstinitial'";
            }
            if ($lastinitial) {
                $count_sql .= " AND $lastletter = '$lastinitial'";
            }
        } else {
            $inner_sql = "SELECT userid, latestsubmitid AS latest_id
                            FROM {$CFG->prefix}programming_result AS pr";
            if ($firstinitial || $lastinitial) {
                $inner_sql .= ", {$CFG->prefix}user AS u";
            }
            $inner_sql .= " WHERE programmingid={$programming->id}";
            if ($firstinitial || $lastinitial) {
                $inner_sql .= " AND pr.userid = u.id";
            }
            if ($firstinitial) {
                $inner_sql .= " AND $firstletter = '$firstinitial'";
            }
            if ($lastinitial) {
                $inner_sql .= " AND $lastletter = '$lastinitial'";
            }
            $inner_sql .= " ORDER BY latest_id {$orderbyorder}";

            $count_sql = "SELECT COUNT(*) AS c
                            FROM {$CFG->prefix}programming_result pr";
            if ($firstinitial || $lastinitial) {
                $count_sql .= ", {$CFG->prefix}user u";
            }
            $count_sql .= " WHERE programmingid={$programming->id}";
            if ($firstinitial || $lastinitial) {
                $count_sql .= " AND pr.userid = u.id";
            }
            if ($firstinitial) {
                $count_sql .= " AND $firstletter = '$firstinitial'";
            }
            if ($lastinitial) {
                $count_sql .= " AND $lastletter = '$lastinitial'";
            }
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
                                 {$CFG->prefix}programming_submits AS ps0";
            if ($firstinitial || $lastinitial) {
                $inner_sql .= ", {$CFG->prefix}user AS u";
            }
            $inner_sql .= " WHERE ipr.programmingid={$programming->id}
                             AND igm.groupid={$groupid}
                             AND ipr.userid=igm.userid
                             AND ps0.programmingid={$programming->id}
                             AND ps0.id=latestsubmitid";
            if ($firstinitial || $lastinitial) {
                $inner_sql .= " AND ipr.userid = u.id";
            }
            if ($firstinitial) {
                $inner_sql .= " AND $firstletter = '$firstinitial'";
            }
            if ($lastinitial) {
                $inner_sql .= " AND $lastletter = '$lastinitial'";
            }
            $inner_sql .= " ORDER BY ps0.codelines {$orderbyorder}";

            $count_sql = "SELECT COUNT(*) c
                            FROM {$CFG->prefix}programming_result AS ipr,
                                 {$CFG->prefix}groups_members AS igm";
            if ($firstinitial || $lastinitial) {
                $count_sql .= ", {$CFG->prefix}user u";
            }
            $count_sql .= " WHERE programmingid={$programming->id}
                             AND igm.groupid={$groupid}
                             AND ipr.userid=igm.userid";
            if ($firstinitial || $lastinitial) {
                $count_sql .= " AND ipr.userid = u.id";
            }
            if ($firstinitial) {
                $count_sql .= " AND $firstletter = '$firstinitial'";
            }
            if ($lastinitial) {
                $count_sql .= " AND $lastletter = '$lastinitial'";
            }
        } else {
            $inner_sql = "SELECT ips.id, ips.userid, ips.codelines
                            FROM {$CFG->prefix}programming_result AS ipr, 
                                 {$CFG->prefix}programming_submits AS ips";
            if ($firstinitial || $lastinitial) {
                $inner_sql .= ", {$CFG->prefix}user AS u";
            }
            $inner_sql .= " WHERE ipr.programmingid={$programming->id}
                             AND ips.programmingid={$programming->id}
                             AND ips.id=ipr.latestsubmitid";
            if ($firstinitial || $lastinitial) {
                $inner_sql .= " AND ipr.userid = u.id";
            }
            if ($firstinitial) {
                $inner_sql .= " AND $firstletter = '$firstinitial'";
            }
            if ($lastinitial) {
                $inner_sql .= " AND $lastletter = '$lastinitial'";
            }
            $inner_sql .= " ORDER BY ips.codelines {$orderbyorder}";

            $count_sql = "SELECT COUNT(*) AS c
                            FROM {$CFG->prefix}programming_result AS pr";
            if ($firstinitial || $lastinitial) {
                $count_sql .= ", {$CFG->prefix}user u";
            }
            $count_sql .= " WHERE programmingid={$programming->id}";
            if ($firstinitial || $lastinitial) {
                $count_sql .= " AND pr.userid = u.id";
            }
            if ($firstinitial) {
                $count_sql .= " AND $firstletter = '$firstinitial'";
            }
            if ($lastinitial) {
                $count_sql .= " AND $lastletter = '$lastinitial'";
            }
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

    if (is_array($submits)) {
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
    }

    // Get users object
    if (!empty($uids)) {
        $users = get_records_select('user', 'id in ('.implode(',', $uids).')');
        unset($uids);
    }
    // Get results object
    if (!empty($submits)) {
        $sids = array_keys($submits);
        $sresults = get_records_select('programming_test_results', 'submitid in ('.implode(',', $sids).')');
        unset($sids);
    }

    $results = array();
    if (!empty($sresults)) {
        foreach ($sresults as $sresult) {
            if (!array_key_exists($sresult->submitid, $results)) {
                $results[$sresult->submitid] = array();
            }
            array_push($results[$sresult->submitid], $sresult);
        }
    }

    $groupmode = groupmode($course, $cm);
    $groups = get_groups($course->id);

    add_to_log($course->id, 'programming', 'reports_detail');

    include_once('detail.tpl.php');
?>
