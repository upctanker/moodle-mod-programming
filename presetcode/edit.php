<?php

    require_once('../../../config.php');
    require_once($CFG->libdir.'/weblib.php');
    require_once('../lib.php');
    require_once('form.php');

    $a = required_param('a', PARAM_INT);     // programming ID
    $ppid = required_param('id', PARAM_INT);
        
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
        
        update_record('programming_presetcode', $data);

        add_to_log($course->id, 'programming', 'presetcode_add', 'preset/add.php?a='.$programming->id, 'add preset code');
        redirect('list.php?a='.$programming->id, get_string('presetcodemodified', 'programming'));

    } else {
        $data = get_record('programming_presetcode', 'id', $ppid);
        $mform->set_data($data);
        /// Print the page header
        $pagename = get_string('editpresetcode', 'programming');
        include_once('../pageheader.php');

        echo '<div class="maincontent generalbox">';
        echo '<h1>'.get_string('editpresetcode', 'programming').'</h1>';

        /// Print tabs
        $currenttab = 'edittest';
        include_once('../tabs.php');

        echo '</div>';

        /// Print page content
        $mform->display();

        /// Finish the page
        print_footer($course);
    }
