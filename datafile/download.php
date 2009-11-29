<?php

    require_once('../../../config.php');
    require_once('../lib.php');

    $a = required_param('a', PARAM_INT);     // programming ID
    $id = required_param('id', PARAM_INT); 
    $checkdata = optional_param('checkdata', 0, PARAM_INT);

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
    if ($checkdata) {
        require_capability('mod/programming:viewhiddentestcase', $context);
    } else {
        require_capability('mod/programming:viewpubtestcase', $context);
    }

    add_to_log($course->id, 'programming', 'datafile_delete', "datafile/move.php?a={$programming->id}", 'download data file');

    $file = get_record('programming_datafile', 'id', $id, 'programmingid', $a);
    if ($file) {
        $content = bzdecompress($checkdata ? $file->checkdata : $file->data);
        $size = $checkdata ? $file->checkdatasize : $file->datasize;
        if ($file->isbinary) {
            header('Content-Type: application/octec-stream');
        } else{
            header('Content-Type: plain/text');
        }
        header("Content-Disposition: attachment; filename=$file->filename");
        header("Content-Length: $size");
        print $content;
    } else {
        header('HTTP/1.0 404 Not Found');
        echo 'Not Found';
    }
?>
