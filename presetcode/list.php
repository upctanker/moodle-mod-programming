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
    $pagename = get_string('presetcodes', 'programming');
    include_once('../pageheader.php');

/// Print tabs
    $currenttab = 'edittest';
    include_once('../tabs.php');

/// Print page content
    echo '<div class="maincontent">';
    echo '<h1>'.get_string('presetcode', 'programming').'</h1>';
    print_presetcode_table();
    echo '</div>';

/// Finish the page
    print_footer($course);

function print_presetcode_table() {
    global $CFG, $page, $perpage, $programming, $course, $language, $groupid;

    $table = new flexible_table('presetcode-table');
    $def = array('sequence', 'name', 'language', 'presetcode', 'presetcodeforcheck', 'edit');
    $table->define_columns($def);
    $headers = array(
        get_string('sequence', 'programming'),
        get_string('name', 'programming'),
        get_string('language'),
        get_string('codeforuser', 'programming'),
        get_string('codeforcheck', 'programming'),
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

    $codes = get_records('programming_presetcode', 'programmingid', $programming->id, 'sequence');
    if (is_array($codes)) {
        $langs = get_records('programming_languages');
        $codes_count = count($codes)-1;
        $i = 0;
        foreach ($codes as $code) {
            $data = array();
            $data[] = $code->sequence;
            $data[] = htmlentities($code->name);
            $data[] = $langs[$code->languageid]->name;
            $data[] = $code->presetcode ? 'Yes' : '';
            $data[] = $code->presetcodeforcheck ? 'Yes' : '';
            $html = "<a class='icon edit' href='edit.php?a={$programming->id}&amp;id={$code->id}'><img src='{$CFG->pixpath}/t/edit.gif' /></a> ";
            $html .= "<a class='icon delete' href='delete.php?a={$programming->id}&amp;id={$code->id}'><img src='{$CFG->pixpath}/t/delete.gif' /></a> ";
            if ($i > 0) {
                $html .= "<a class='icon up' href='move.php?a={$programming->id}&amp;id={$code->id}&amp;direction=1'><img src='{$CFG->pixpath}/t/up.gif' /></a> ";
            }
            if ($i < $codes_count) {
                $html .= "<a class='icon down' href='move.php?a={$programming->id}&amp;id={$code->id}&amp;direction=2'><img src='{$CFG->pixpath}/t/down.gif' /></a> ";
            }
            $data[] = $html;
            $table->add_data($data);
            $i++;
        }

        echo '<div class="maincontent2">';
        $table->print_html();
        echo '</div>';
    } else {
        echo '<p>'.get_string('nopresetcode', 'programming').'</pre>';
    }
    echo "<p><a href='add.php?a={$programming->id}'>".get_string('addpresetcode', 'programming').'</a></p>';
}

?>
