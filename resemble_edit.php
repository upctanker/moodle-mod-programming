<?php

    require_once('../../config.php');
    require_once('lib.php');

    $a = optional_param('a', 0, PARAM_INT);     // programming ID
    $action = optional_param('action', 'list', PARAM_CLEAN);
    $page = optional_param('page', 0, PARAM_INT);
    $perpage = optional_param('perpage', 10, PARAM_INT);
    $format = optional_param('format', 'html', PARAM_CLEAN);
    $rids = optional_param('rids', null, PARAM_INT);

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

    switch($action) {
    case 'list':
        require_capability('mod/programming:editresemble', $context);
        $offset = $page * $perpage;
        $sql = "SELECT re.*, ua.id as userid1, ub.id as userid2
                  FROM {$CFG->prefix}programming_resemble AS re,
                       {$CFG->prefix}programming_submits AS sa,
                       {$CFG->prefix}programming_submits AS sb,
                       {$CFG->prefix}user AS ua,
                       {$CFG->prefix}user AS ub
                 WHERE re.programmingid={$programming->id}
                   AND re.flag>=0
                   AND re.submitid1 = sa.id
                   AND re.submitid2 = sb.id
                   AND sa.userid = ua.id
                   AND sb.userid = ub.id
              ORDER BY id
                 LIMIT $offset, $perpage";
        $resemble = get_records_sql($sql);
        if (!is_array($resemble)) $resemble = array();
        $uids = array(); $sids = array();
        foreach($resemble as $r) {
            $uids[] = $r->userid1;
            $uids[] = $r->userid2;
            $sids[] = $r->submitid1;
            $sids[] = $r->submitid2;
        }
        if (!empty($uids)) {
            $users = get_records_select('user', 'id IN ('.implode($uids, ',').')');
        }
        if (!empty($sids)) {
            $submits = get_records_select('programming_submits', 'id IN ('.implode($sids, ',').')');
        }
        $totalcount = count_records_select('programming_resemble', 'programmingid='.$programming->id.' AND flag>=0');

        /// Print page content
        if ($format == 'json') {
            require_once('lib/JSON.php');
            $data = array(array_keys($resemble), array_values($resemble), array_keys($users), array_values($users));
            $json = new Services_JSON();
            echo $json->encode($data);
        } else {
            include_once('resemble_edit.tpl.php');
        }

        break;

    case 'confirm':
        require_capability('mod/programming:editresemble', $context);
        $select = 'id in ('.join(',', $rids).')';
        $sql = set_field_select('programming_resemble', 'flag', PROGRAMMING_RESEMBLE_CONFIRMED, $select);
        include_once('resemble_editsuccess.tpl.php');
        break;

    case 'warn':
        require_capability('mod/programming:editresemble', $context);
        $select = 'id in ('.join(',', $rids).')';
        $sql = set_field_select('programming_resemble', 'flag', PROGRAMMING_RESEMBLE_WARNED, $select);
        include_once('resemble_editsuccess.tpl.php');
        break;

    case 'reset':
        require_capability('mod/programming:editresemble', $context);
        $select = 'id in ('.join(',', $rids).')';
        $sql = set_field_select('programming_resemble', 'flag', PROGRAMMING_RESEMBLE_NEW, $select);
        include_once('resemble_editsuccess.tpl.php');
        break;

    case 'flag1':
        require_capability('mod/programming:editresemble', $context);
        $select = 'id in ('.join(',', $rids).')';
        $sql = set_field_select('programming_resemble', 'flag', PROGRAMMING_RESEMBLE_FLAG1, $select);
        include_once('resemble_editsuccess.tpl.php');
        break;

    case 'flag2':
        require_capability('mod/programming:editresemble', $context);
        $select = 'id in ('.join(',', $rids).')';
        $sql = set_field_select('programming_resemble', 'flag', PROGRAMMING_RESEMBLE_FLAG2, $select);
        include_once('resemble_editsuccess.tpl.php');
        break;

    case 'flag3':
        require_capability('mod/programming:editresemble', $context);
        $select = 'id in ('.join(',', $rids).')';
        $sql = set_field_select('programming_resemble', 'flag', PROGRAMMING_RESEMBLE_FLAG3, $select);
        include_once('resemble_editsuccess.tpl.php');
        break;

    case 'delete':
        require_capability('mod/programming:editresemble', $context);
        $select = 'id in ('.join(',', $rids).')';
        $sql = set_field_select('programming_resemble', 'flag', PROGRAMMING_RESEMBLE_DELETED, $select);
        include_once('resemble_editsuccess.tpl.php');
        break;
    }

?>
