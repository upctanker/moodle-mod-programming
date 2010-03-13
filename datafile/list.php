<?php

    require_once('../../../config.php');
    require_once($CFG->libdir.'/tablelib.php');
    require_once('../lib.php');

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

    require_capability('mod/programming:viewhiddentestcase', $context);

/// Print the page header
    $pagename = get_string('datafiles', 'programming');
    include_once('../pageheader.php');

/// Print tabs
    $currenttab = 'edittest';
    include_once('../tabs.php');

/// Print page content
    echo '<div class="maincontent generalbox">';
    echo '<h1>'.get_string('datafiles', 'programming').helpbutton('datafile', 'datafile', 'programming', true, false, '', true).'</h1>';
    print_datafile_table();
    echo '</div>';

/// Finish the page
    print_footer($course);

function print_datafile_table() {
    global $CFG, $page, $perpage, $programming, $course, $language, $groupid;

    $table = new flexible_table('datafile-table');
    $def = array('sequence', 'filename', 'filetype', 'data', 'checkdata', 'action');
    $table->define_columns($def);
    $headers = array(
        get_string('sequence', 'programming'),
        get_string('filename', 'programming'),
        get_string('filetype', 'programming'),
        get_string('data', 'programming'),
        get_string('checkdata', 'programming'),
        get_string('action'),
        );
    $table->define_headers($headers);

    $table->set_attribute('id', 'presetcode-table');
    $table->set_attribute('class', 'generaltable generalbox');
    $table->set_attribute('align', 'center');
    $table->set_attribute('cellpadding', '3');
    $table->set_attribute('cellspacing', '1');
    $table->no_sorting('code');
    $table->setup();

    $strpresstodownload = get_string('presstodownload', 'programming');
    $strbinaryfile = get_string('binaryfile', 'programming');
    $strtextfile = get_string('textfile', 'programming');
    $stredit = get_string('edit');
    $strdelete = get_string('delete');
    $strmoveup = get_string('moveup');
    $strmovedown = get_string('movedown');
    $files = get_records('programming_datafile', 'programmingid', $programming->id, 'seq');
    if (is_array($files)) {
        $files_count = count($files)-1;
        $i = 0;
        foreach ($files as $file) {
            $data = array();
            $data[] = $file->seq;
            $data[] = htmlentities($file->filename);
            $data[] = $file->isbinary ? $strbinaryfile : $strtextfile;
            $size = programming_format_codesize($file->datasize);
            $data[] = "<a href='download.php?a={$programming->id}&amp;id={$file->id}' title='{$strpresstodownload}'>{$size}</a>";
            if ($file->checkdatasize) {
                $size = programming_format_codesize($file->checkdatasize);
                $data[] = "<a href='download.php?a={$programming->id}&amp;id={$file->id}&amp;checkdata=1' title='{$strpresstodownload}'>{$size}</a>";
            } else {
                $data[] = get_string('n/a', 'programming');
            }
            $html = "<a class='icon edit' href='edit.php?a={$programming->id}&amp;id={$file->id}' title='$stredit'><img src='{$CFG->pixpath}/t/edit.gif' alt='$stredit'/></a> ";
            $html .= "<a class='icon delete' href='delete.php?a={$programming->id}&amp;id={$file->id}' title='$strdelete'><img src='{$CFG->pixpath}/t/delete.gif' alt='$strdelete'/></a> ";
            if ($i > 0) {
                $html .= "<a class='icon up' href='move.php?a={$programming->id}&amp;id={$file->id}&amp;direction=1' title='$strmoveup'><img src='{$CFG->pixpath}/t/up.gif' alt='$strmoveup'/></a> ";
            }
            if ($i < $files_count) {
                $html .= "<a class='icon down' href='move.php?a={$programming->id}&amp;id={$file->id}&amp;direction=2' title='$strmovedown'><img src='{$CFG->pixpath}/t/down.gif' alt='$strmovedown'/></a> ";
            }
            $data[] = $html;
            $table->add_data($data);
            $i++;
        }

        echo '<div class="maincontent2">';
        $table->print_html();
        echo '</div>';
    } else {
        echo '<p>'.get_string('nodatafile', 'programming').'</pre>';
    }
    echo "<p><a href='add.php?a={$programming->id}'>".get_string('adddatafile', 'programming').'</a></p>';
}

?>
