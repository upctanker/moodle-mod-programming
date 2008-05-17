<?php

function stat_all(&$stat_results) {
    global $USER, $CFG, $course, $programming;

    $context = get_record('context', 'contextlevel', CONTEXT_COURSE, 'instanceid', $course->id);
    $name = get_string('allstudents', 'programming', $course->students);
    $studentcount = count_records_sql("
        SELECT COUNT(*)
          FROM {$CFG->prefix}role_assignments AS ra
         WHERE ra.roleid = 5
           AND ra.contextid = $context->id");
    $submitcount = count_records_sql("
        SELECT COUNT(*) FROM (
        SELECT DISTINCT ps.userid
          FROM {$CFG->prefix}role_assignments AS ra,
               {$CFG->prefix}programming_submits AS ps
         WHERE ra.roleid = 5
           AND ra.contextid = $context->id
           AND ps.programmingid = $programming->id
           AND ra.userid = ps.userid
        ) AS duserid");
    $compiledcount = count_records_sql("
        SELECT COUNT(*)
          FROM {$CFG->prefix}programming_submits AS ps, (
              SELECT ps1.userid, MAX(timemodified) AS timemodified
                FROM {$CFG->prefix}programming_submits AS ps1
               WHERE ps1.programmingid = $programming->id
            GROUP BY userid
              ) AS latest
         WHERE ps.programmingid = $programming->id
           AND ps.userid = latest.userid
           AND ps.timemodified = latest.timemodified
           AND `status` = ".PROGRAMMING_STATUS_FINISH."
           AND ps.userid IN (
               SELECT userid FROM {$CFG->prefix}role_assignments AS ra
               WHERE ra.roleid = 5
                 AND ra.contextid = $context->id)");
    $passedcount = count_records_sql("
        SELECT COUNT(*)
          FROM {$CFG->prefix}programming_submits AS ps,
               {$CFG->prefix}programming_result AS pr
         WHERE ps.programmingid = {$programming->id}
           AND pr.programmingid = {$programming->id}
           AND pr.latestsubmitid = ps.id
           AND ps.passed = 1");
    $intimepassedcount = count_records_sql("
        SELECT COUNT(*)
          FROM {$CFG->prefix}programming_submits AS ps,
               {$CFG->prefix}programming_result AS pr
         WHERE ps.programmingid = {$programming->id}
           AND pr.programmingid = {$programming->id}
           AND pr.latestsubmitid = ps.id
           AND ps.timemodified <= {$programming->timediscount}
           AND ps.passed = 1");
    $codes = get_records_sql("
        SELECT id, userid, code FROM {$CFG->prefix}programming_submits
         WHERE programmingid = $programming->id
           AND userid IN (
               SELECT userid FROM {$CFG->prefix}role_assignments AS ra
               WHERE ra.roleid = 5
                 AND ra.contextid = $context->id)
      ORDER BY timemodified DESC");
    $codelines = array();
    $sum = 0;
    if (is_array($codes)) {
        foreach ($codes as $row) {
            if (array_key_exists($row->userid, $codelines)) continue;
            $sum += ($codelines[$row->userid] = count(explode("\n", $row->code)));
        }
    }
    array_push($stat_results,
        array('name' => $name,
              'studentcount' => $studentcount,
              'submitcount' => $submitcount,
              'compiledcount' => $compiledcount,
              'passedcount' => $passedcount,
              'intimepassedcount' => $intimepassedcount,
              'totallines' => $sum));
    return;

}

function stat_group($group, &$stat_results) {
    global $USER, $CFG, $course, $programming;

    $context = get_record('context', 'contextlevel', CONTEXT_COURSE, 'instanceid', $course->id);
    $name = $group->name;
    $studentcount = count_records_sql("
        SELECT COUNT(*)
          FROM {$CFG->prefix}role_assignments AS ra,
               {$CFG->prefix}groups_members AS gm
         WHERE ra.roleid = 5
           AND ra.contextid = $context->id
           AND gm.groupid = $group->id
           AND ra.userid = gm.userid");
    $submitcount = count_records_sql("
        SELECT COUNT(*) FROM (
        SELECT DISTINCT ps.userid
          FROM {$CFG->prefix}role_assignments AS ra,
               {$CFG->prefix}groups_members AS gm,
               {$CFG->prefix}programming_submits AS ps
         WHERE ra.roleid = 5
           AND ra.contextid = $context->id
           AND gm.groupid = $group->id
           AND ps.programmingid = $programming->id
           AND ra.userid = gm.userid
           AND ra.userid = ps.userid
        ) AS duserid");
    $compiledcount = count_records_sql("
        SELECT COUNT(*)
          FROM {$CFG->prefix}programming_submits AS ps, (
              SELECT ps1.userid, MAX(timemodified) AS timemodified
                FROM {$CFG->prefix}programming_submits AS ps1,
                     {$CFG->prefix}groups_members AS gm1
               WHERE ps1.programmingid = $programming->id
                 AND gm1.groupid = $group->id
                 AND ps1.userid = gm1.userid
            GROUP BY userid
              ) AS latest
         WHERE ps.programmingid = $programming->id
           AND ps.userid = latest.userid
           AND ps.timemodified = latest.timemodified
           AND `status` = ".PROGRAMMING_STATUS_FINISH);
    $passedcount = count_records_sql($s = "
        SELECT COUNT(*)
          FROM {$CFG->prefix}programming_submits AS ps,
               {$CFG->prefix}programming_result AS pr,
               {$CFG->prefix}groups_members AS gm
         WHERE ps.programmingid = {$programming->id}
           AND pr.programmingid = {$programming->id}
           AND gm.groupid = {$group->id}
           AND gm.userid = pr.userid
           AND pr.latestsubmitid = ps.id
           AND ps.passed = 1");
    $intimepassedcount = count_records_sql("
        SELECT COUNT(*)
          FROM {$CFG->prefix}programming_submits AS ps,
               {$CFG->prefix}programming_result AS pr,
               {$CFG->prefix}groups_members AS gm
         WHERE ps.programmingid = {$programming->id}
           AND pr.programmingid = {$programming->id}
           AND gm.groupid = {$group->id}
           AND gm.userid = pr.userid
           AND pr.latestsubmitid = ps.id
           AND ps.timemodified <= {$programming->timediscount}
           AND ps.passed = 1");
    $codes = get_records_sql("
        SELECT id, ps.userid, code FROM {$CFG->prefix}programming_submits AS ps
         WHERE programmingid = $programming->id
           AND userid IN (
               SELECT ra.userid
                 FROM {$CFG->prefix}role_assignments AS ra,
                      {$CFG->prefix}groups_members AS gm
               WHERE ra.roleid = 5
                 AND ra.contextid = $context->id
                 AND ra.userid = gm.userid
                 AND gm.groupid = $group->id)
      ORDER BY timemodified DESC");
    $codelines = array();
    $sum = 0;
    if (is_array($codes)) {
        foreach ($codes as $row) {
            if (array_key_exists($row->userid, $codelines)) continue;
            $sum += ($codelines[$row->userid] = count(explode("\n", $row->code)));
        }
    }
    array_push($stat_results,
        array('name' => $name,
              'studentcount' => $studentcount,
              'submitcount' => $submitcount,
              'compiledcount' => $compiledcount,
              'passedcount' => $passedcount,
              'intimepassedcount' => $intimepassedcount,
              'totallines' => $sum));
    return;
}
?>
