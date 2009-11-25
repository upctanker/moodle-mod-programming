<?php

    require_once('../../../config.php');
    require_once($CFG->libdir.'/weblib.php');
    require_once('../lib.php');
    require_once('form.php');

    $a = required_param('a', PARAM_INT);     // programming ID
        
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
    require_capability('mod/programming:edittestcase', $context);

    $mform = new presetcode_form();
    if ($mform->is_cancelled()) {
        redirect('list.php?a='.$programming->id);

    } else if ($data = $mform->get_data()) {
        if ($data->choosename == '1') $data->name = '<prepend>';
        if ($data->choosename == '2') $data->name = '<postpend>';
        
        $data->sequence = count_records_select('programming_presetcode', 'programmingid='.$data->programmingid, 'MAX(sequence)') + 1;
        insert_record('programming_presetcode', $data);
        programming_presetcode_adjust_sequence($a);

        add_to_log($course->id, 'programming', 'presetcode_add', 'preset/add.php?a='.$programming->id, 'add preset code');
        redirect('list.php?a='.$programming->id, get_string('presetcodeadded', 'programming'));

    } else {
        /// Print the page header
        $pagename = get_string('edittests', 'programming');
        include_once('../pageheader.php');

        /// Print tabs
        $currenttab = 'edittest';
        include_once('../tabs.php');

        /// Print page content
        $mform->display();

        /// Finish the page
        print_footer($course);
    }
