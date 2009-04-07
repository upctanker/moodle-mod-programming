<?php

    include_once('../../config.php');
    include_once('../../mod/programming/lib.php');
    include_once('../../lib/tablelib.php');

    $id = optional_param('id', 0, PARAM_INT);    // block instance id
    $page = optional_param('page', 0, PARAM_INT);

    $instance = get_record('block_instance', 'id', $id);
    $block = block_instance('programming_judge_status', $instance);
    $perpage = $block->config->perpageonfulllist;

    if (!$course = get_record('course', 'id', $block->instance->pageid)) {
        error('course misconfigured');
    }

    require_login($course->id);

/// Print the page header
    if (!isset($CFG->scripts) || !is_array($CFG->scripts)) {
        $CFG->scripts = array();
                $CFG->scripts[] = '/mod/programming/programming.js';
    }
    $CFG->stylesheets[] = $CFG->wwwroot.'/mod/programming/programming.css';
    array_unshift($CFG->scripts, $CFG->wwwroot.'/mod/programming/js/MochiKit/MochiKit.js');

    if ($course->category) {
        $navigation = '<a href="../../course/view.php?id='.$course->id.'">'.$course->shortname.'</a> ->';
    } else {
        $navigation = '';
    }

    $strprogrammings = get_string('modulenameplural', 'programming');
    $strprogramming  = get_string('modulename', 'programming');

    $meta = '';
    foreach ($CFG->scripts as $script) {
        $meta .= '<script type="text/javascript" src="'.$script.'"></script>';
        $meta .= "\n";
    }

    print_header(
        $course->shortname.': '.get_string('programmingjudgestatus', 'block_programming_judge_status'),
        $course->fullname,
        $navigation.get_string('programmingjudgestatus', 'block_programming_judge_status'),
        '', // focus
        $meta,
        true,
        '',
        '',
        false);

/// Print the main part of the page
    $offset = min(10000, $perpage * $page);
    $tops = programming_judge_status($block->instance->pageid, $totalcount, $offset, $perpage);

    echo '<div class="maincontent generalbox">';
    echo '<h1 align="center">'.get_string('programmingjudgestatus', 'block_programming_judge_status').'</h1>';

    $table = new flexible_table('programming-judge-status');
    $table->define_columns(array('number', 'user', 'name', 'result', 'timeused', 'memused', 'time'));
    $table->define_headers(array(
        get_string('no.', 'block_programming_judge_status'),
        get_string('who', 'block_programming_judge_status'),
        get_string('which', 'block_programming_judge_status'),
        get_string('result', 'block_programming_judge_status'),
        get_string('timeused', 'block_programming_judge_status'),
        get_string('memused', 'block_programming_judge_status'),
        get_string('submittime', 'block_programming_judge_status'),
    ));
    $table->set_attribute('cellspacing', '1');
    $table->set_attribute('cellpadding', '3');
    $table->set_attribute('id', 'programming-judge-status');
    $table->set_attribute('class', 'generaltable generalbox');
    $table->set_attribute('align', 'center');
    $table->define_baseurl($CFG->wwwroot.'/blocks/programming_judge_status/fulllist.php?id='.$id);
    $table->pagesize($perpage, min(10000, $totalcount));
    $table->setup();

    $i = $totalcount - $page * $perpage;
    if ($tops) {
        foreach ($tops as $t) {
            $table->add_data(array(
                $i--,
                has_capability('block/programming_judge_status:view') ? '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$t->user->id.'&amp;course='.$course->id.'">'.fullname($t->user).'</a>' : '???',
                "<a href='{$CFG->wwwroot}/mod/programming/view.php?a={$t->pid}'>".$t->globalid.'.'.$t->pname.'</a>',
                "<a href='{$CFG->wwwroot}/mod/programming/result.php?a={$t->pid}&amp;submitid=$t->psid'>$t->judgeresult</a>",
                isset($t->timeused) ? sprintf('%6.3f', $t->timeused) : '',
                isset($t->memused) ? $t->memused : '',
                userdate($t->timemodified, '%Y-%m-%d %H:%M:%S'),
            ));
        }
    }

    $table->print_html();

    echo '</div>';

/// Finish the page
    print_footer($course);
?>
