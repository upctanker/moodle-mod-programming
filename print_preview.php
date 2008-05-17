<?php

    include_once('../../config.php');

    $submitid = optional_param('print_preview_submit_id', 0, PARAM_INT);
    $submit = get_record('programming_submits', 'id', $submitid);
    if ($submit->userid != $USER->id) {
        exit(0);
    }
    
    $userfullname = $USER->username.'-'.fullname($USER);
    $d = $CFG->dataroot.'/temp/programming';
    if (!is_dir($d)) {
        if (file_exists($d)) {
            unlink($d);
        }
        mkdir($d);
    }
    $srcname = tempnam($d, 'pp');
    $f = fopen($srcname, 'w');
    fwrite($f, $submit->code);
    fclose($f);

    putenv('LC_ALL=en_US.UTF-8');
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="source.pdf"');
    passthru("/usr/bin/u2ps -o /dev/stdout -t \"$userfullname\" --gpfamily=\"Monospace\" \"$srcname\" 2>/dev/null | /usr/bin/ps2pdf - -");
    
    unlink($srcname);
?>
