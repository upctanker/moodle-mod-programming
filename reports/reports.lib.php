<?php

/**
 * 统计各个小组完成题目的情况。
 *
 * 目前此函数只处理 roleid 为 5 即学生的情况。
 *
 * @param $state_results 存储统计结果
 * @param $groupid 要统计的小组的ID，如果为0则统计全部人员的情况
 */
function summary_stat(&$stat_results, $groupid = 0) {
    global $USER, $CFG, $course, $programming;

    $context = get_record('context', 'contextlevel', CONTEXT_COURSE, 'instanceid', $course->id);
    $roleid = 5;
    $name = get_string('allstudents', 'programming', $course->students);

    $gfrom = $gwhere = '';
    if ($groupid) {
        $gfrom = ", {$CFG->prefix}groups_members AS gm";
        $gwhere = " AND gm.groupid = $groupid AND ra.userid = gm.userid ";
    }

    $studentcount = count_records_sql("
        SELECT COUNT(*)
          FROM {$CFG->prefix}role_assignments AS ra
               $gfrom
         WHERE ra.roleid = $roleid
           AND ra.contextid = $context->id
               $gwhere");
    $submitcount = count_records_sql("
        SELECT COUNT(*)
          FROM {$CFG->prefix}role_assignments AS ra,
               {$CFG->prefix}programming_result AS pr
               $gfrom
         WHERE ra.roleid = $roleid
           AND ra.contextid = $context->id
           AND pr.programmingid = $programming->id
           AND ra.userid = pr.userid
               $gwhere");
    $compiledcount = count_records_sql("
        SELECT COUNT(*)
          FROM {$CFG->prefix}role_assignments AS ra,
               {$CFG->prefix}programming_result AS pr,
               {$CFG->prefix}programming_submits AS ps
               $gfrom
         WHERE ps.programmingid = $programming->id
           AND pr.programmingid = $programming->id
           AND ra.roleid = $roleid
           AND ra.contextid = $context->id
           AND ps.id = pr.latestsubmitid
           AND pr.userid = ra.userid
               $gwhere");
    $passedcount = count_records_sql("
        SELECT COUNT(*)
          FROM {$CFG->prefix}role_assignments AS ra,
               {$CFG->prefix}programming_submits AS ps,
               {$CFG->prefix}programming_result AS pr
               $gfrom
         WHERE ps.programmingid = {$programming->id}
           AND pr.programmingid = {$programming->id}
           AND ra.roleid = $roleid
           AND ra.contextid = $context->id
           AND pr.userid = ra.userid
           AND pr.latestsubmitid = ps.id
           AND ps.passed = 1
               $gwhere");
    $intimepassedcount = count_records_sql("
        SELECT COUNT(*)
          FROM {$CFG->prefix}role_assignments AS ra,
               {$CFG->prefix}programming_submits AS ps,
               {$CFG->prefix}programming_result AS pr
               $gfrom
         WHERE ps.programmingid = {$programming->id}
           AND pr.programmingid = {$programming->id}
           AND ra.roleid = $roleid
           AND ra.contextid = $context->id
           AND pr.userid = ra.userid
           AND pr.latestsubmitid = ps.id
           AND ps.timemodified <= {$programming->timediscount}
           AND ps.passed = 1
               $gwhere");
    $codeavg = count_records_sql("
        SELECT AVG(codelines)
          FROM {$CFG->prefix}role_assignments AS ra,
               {$CFG->prefix}programming_submits AS ps,
               {$CFG->prefix}programming_result AS pr
               $gfrom
         WHERE ps.programmingid = {$programming->id}
           AND pr.programmingid = {$programming->id}
           AND pr.latestsubmitid = ps.id
           AND ra.roleid = $roleid
           AND ra.contextid = $context->id
           AND pr.userid = ra.userid
               $gwhere");
    array_push($stat_results,
        array('name' => $name,
              'studentcount' => $studentcount,
              'submitcount' => $submitcount,
              'compiledcount' => $compiledcount,
              'passedcount' => $passedcount,
              'intimepassedcount' => $intimepassedcount,
              'averagelines' => $codeavg));
    return;
}

?>
