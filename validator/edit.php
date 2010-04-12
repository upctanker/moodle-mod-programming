<?php

    require_once('../../../config.php');
    require_once('../lib.php');
    require_once('form.php');

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
    require_capability('mod/programming:edittestcase', $context);

    $mform = new validator_form();
    if ($mform->is_cancelled()) {
        redirect('list.php?a='.$programming->id);

    } else if ($data = $mform->get_data()) {
        update_record('programming', $data);

        add_to_log($course->id, 'programming', 'validator_edit', 'validator/edit.php?a='.$programming->id, 'edit validator');

        redirect('edit.php?a='.$programming->id, get_string('validatormodified', 'programming'));
    } else {
        $data = get_record('programming', 'id', $a);
        $mform->set_data($data);

        add_to_log($course->id, 'programming', 'validator_view', 'validator/edit.php?a='.$programming->id, 'view validator');
    }

    /// Print the page header
    $pagename = get_string('validator', 'programming');
    include_once('../pageheader.php');

    /// Print tabs
    $currenttab = 'edittest';
    include_once('../tabs.php');

    echo '<div class="maincontent generalbox">';
    echo '<h1>'.$pagename.helpbutton('validator', 'validator', 'programming', true, false, '', true).'</h1>';

    /// Print page content
    $mform->display();

    echo '</div>';

    /// Finish the page
    print_footer($course);
