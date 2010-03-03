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

    $mform = new testcase_form();
    if ($mform->is_cancelled()) {
        redirect('list.php?a='.$programming->id);

    } else if ($data = $mform->get_data()) {
        $infile = $mform->get_file_content('inputfile');
        if (empty($infile)) {
            $data->input = stripcslashes($data->input);
        } else {
            $data->input = $infile;
        }
        $outfile = $mform->get_file_content('outputfile');
        if (empty($outfile)) {
            $data->output = stripcslashes($data->output);
        } else {
            $data->output = $outfile;
        }
        programming_test_update_instance($data);

        add_to_log($course->id, 'programming', 'testcase_edit', 'testcase/edit.php?a='.$programming->id, 'edit test case');
        redirect('list.php?a='.$programming->id, get_string('testcasemodified', 'programming'));

    } else {
        $data = get_record('programming_tests', 'id', $ppid);
        $mform->set_data($data);
        /// Print the page header
        $pagename = get_string('edittestcase', 'programming');
        include_once('../pageheader.php');

        /// Print tabs
        $currenttab = 'edittest';
        include_once('../tabs.php');

        echo '<div class="maincontent generalbox">';
        echo '<h1>'.get_string('edittestcase', 'programming').'</h1>';

        /// Print page content
        $mform->display();

        echo '</div>';

        /// Finish the page
        print_footer($course);
    }
