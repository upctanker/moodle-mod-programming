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

    $mform = new datafile_form();
    if ($mform->is_cancelled()) {
        redirect('list.php?a='.$programming->id);

    } else if ($data = $mform->get_data()) {
        $data->seq = count_records_select('programming_datafile', 'programmingid='.$data->programmingid, 'MAX(seq)') + 1;

        $content = $mform->get_file_content('data');
        $data->data = addslashes(bzcompress($content));
        $data->datasize = strlen($content);

        if (!empty($data->usecheckdata)) {
            $content = $mform->get_file_content('checkdata');
            $data->checkdata = addslashes(bzcompress($content));
            $data->checkdatasize = strlen($content);
        }

        $data->timemodified = time();
        insert_record('programming_datafile', $data);
        programming_datafile_adjust_sequence($a);

        add_to_log($course->id, 'programming', 'datafile_add', 'datafile/add.php?a='.$programming->id, 'add data file');
        redirect('list.php?a='.$programming->id, get_string('datafileadded', 'programming'));

    } else {
        /// Print the page header
        $pagename = get_string('adddatafile', 'programming');
        include_once('../pageheader.php');

        /// Print tabs
        $currenttab = 'edittest';
        include_once('../tabs.php');

        echo '<div class="maincontent generalbox">';
        echo '<h1>'.get_string('adddatafile', 'programming').helpbutton('datafile', 'datafile', 'programming', true, false, '', true).'</h1>';

        /// Print page content
        $mform->display();

        echo '</div>';

        /// Finish the page
        print_footer($course);
    }
