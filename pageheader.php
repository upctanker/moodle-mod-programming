<?php
    if (!isset($CFG->scripts) || !is_array($CFG->scripts)) {
        $CFG->scripts = array();
		$CFG->scripts[] = 'programming.js';
    }
    $CFG->stylesheets[] = 'programming.css';
    array_unshift($CFG->scripts, $CFG->wwwroot.'/mod/programming/js/jquery-latest.pack.js');

    if ($course->category) {
        $navigation = '<a href="../../course/view.php?id='.$course->id.'">'.$course->shortname.'</a> ->';
    }

    $strprogrammings = get_string('modulenameplural', 'programming');
    $strprogramming  = get_string('modulename', 'programming');

    $meta = '';
    foreach ($CFG->scripts as $script) {
        $meta .= '<script type="text/javascript" src="'.$script.'"></script>';
        $meta .= "\n";
    }

    if (isset($programming)) {
        $navigation .= ' <a href="index.php?id='.$course->id.'">'.$strprogrammings.'</a> -> <a href="view.php?a='.$programming->id.'">'.$programming->name.'</a> -> '.$pagename;
    } else {
        $navigation .= $strprogrammings;
    }

    print_header(
        $course->shortname.': '.$programming->name,
        $course->fullname,
        $navigation,
        '', // focus
        $meta,
        true,
        update_module_button($cm->id, $course->id, $strprogramming), 
        navmenu($course, $cm),
        false);
?>
