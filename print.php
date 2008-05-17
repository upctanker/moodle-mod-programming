<?php

    include_once('../../config.php');

    $submitid = optional_param('print_submit_id', 0, PARAM_INT);
    $submit = get_record('programming_submits', 'id', $submitid);
    if ($submit->userid != $USER->id) {
        exit(0);
    }
    
    $userfullname = fullname($USER);
    $d = $CFG->dataroot.'/temp/programming';
    if (!is_dir($d)) {
        if (file_exists($d)) {
            unlink($d);
        }
        mkdir($d);
    }
    $srcname = tempnam($d, 'pp');
    $destname = tempnam($d, 'pp');

    $f = fopen($srcname, 'w');
    fwrite($f, $submit->code);
    fclose($f);

    putenv('LC_ALL=en_US.UTF-8');
    system("/usr/bin/u2ps -o \"$destname\" -t \"$userfullname\" --gpfamily=\"Monospace\" \"$srcname\" 2>/dev/null");
    $destprt = False;
    if (address_in_subnet(getremoteaddr(), '10.1.10.0/23')) {
        $destprt = 'cc2';
    }
    else if (address_in_subnet(getremoteaddr(), '10.1.111.0/23')) {
        $destprt = 'sym1';
    }

    if ($destprt) {
      //system("/usr/bin/lp -d $destprt -h 192.168.104.10:631 \"$destname\"");
        echo get_string('printfinished', 'programming');
    } else {
        echo get_string('printnotallow', 'programming');
    }

    unlink($srcname);
    unlink($destname);

?>
