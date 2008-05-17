<?php

    require_once('../../config.php');
    require_once('lib.php');

    $a = optional_param('a', 0, PARAM_INT);     // programming ID
    $format = optional_param('format', 'html', PARAM_CLEAN);

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
    require_capability('mod/programming:viewresemble', $context);

    $sql = "SELECT re.*, sa.userid AS userid1, sb.userid AS userid2
              FROM {$CFG->prefix}programming_resemble AS re,
                   {$CFG->prefix}programming_submits AS sa,
                   {$CFG->prefix}programming_submits AS sb
             WHERE re.programmingid={$programming->id}
               AND re.flag > 0
               AND sa.programmingid={$programming->id}
               AND sb.programmingid={$programming->id}
               AND re.submitid1 = sa.id
               AND re.submitid2 = sb.id
               AND (sa.userid = $USER->id OR sb.userid = $USER->id)
          ORDER BY re.id";
    $resemble = get_records_sql($sql);
    if (!is_array($resemble)) $resemble = array();

    $uids = array();
    foreach($resemble as $r) {
        $uids[] = $r->userid1;
        $uids[] = $r->userid2;
    }
    if (!empty($uids)) {
        $users = get_records_select('user', 'id IN ('.implode($uids, ',').')');
    }

    /// Print page content
    if ($format == 'json') {
        require_once('JSON.php');
        $data = array(array_keys($resemble), array_values($resemble), array_keys($users), array_values($users));
        $json = new Services_JSON();
        echo $json->encode($data);
    } else {
        include_once('resemble_view.tpl.php');
    }

?>
