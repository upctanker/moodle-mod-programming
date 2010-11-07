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

    $mform = new datafile_form();
    if ($mform->is_cancelled()) {
        redirect('list.php?a='.$programming->id);

    } else if ($data = $mform->get_data()) {
        $content = $mform->get_file_content('data');
        if (!empty($content)) {
            $data->datasize = strlen($content);
            $data->data = addslashes(bzcompress($content));
        } else {
            unset($data->datasize);
            unset($data->data);
        }
        $content = $mform->get_file_content('checkdata');
        if (!empty($content)) {
            $data->checkdatasize = strlen($content);
            $data->checkdata = addslashes(bzcompress($content));
        } else {
            unset($data->checkdatasize);
            unset($data->checkdata);
        }

        $data->timemodified = time();
        update_record('programming_datafile', $data);

        add_to_log($course->id, 'programming', 'datafile_edit', 'datafile/edit.php?a='.$programming->id, 'edit data file');
        redirect('list.php?a='.$programming->id, get_string('datafilemodified', 'programming'));

    } else {
        $data = get_record('programming_datafile', 'id', $ppid);
        $mform->set_data($data);
        /// Print the page header
        $pagename = get_string('editdatafile', 'programming');
        include_once('../pageheader.php');

        /// Print tabs
        $currenttab = 'edittest';
        include_once('../tabs.php');

        echo '<div class="maincontent generalbox">';
        echo '<h1>'.get_string('editdatafile', 'programming').helpbutton('datafile', 'datafile', 'programming', true, false, '', true).'</h1>';

        /// Print page content
        $mform->display();

        echo '</div>';

        /// Finish the page
        print_footer($course);
    }
