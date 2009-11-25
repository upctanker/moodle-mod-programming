<?php

    require_once('../../../config.php');
    require_once($CFG->libdir.'/tablelib.php');
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

    require_capability('mod/programming:viewtestcase', $context);

/// Print the page header
    $pagename = get_string('edittests', 'programming');
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
        get_string('presetcode', 'programming'),
        get_string('presetcodeforcheck', 'programming'),
        get_string('edit', 'programming'),
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
        foreach ($codes as $code) {
            $data = array();
            $data[] = $code->sequence;
            $data[] = htmlentities($code->name);
            $data[] = $langs[$code->languageid]->name;
            $data[] = $code->presetcode ? 'Yes' : '';
            $data[] = $code->presetcodeforcheck ? 'Yes' : '';
            $data[] = "<a href='move.php?a={$programming->id}&amp;id={$code->id}&amp;direction=1'>MoveUp</a>, <a href='move.php?a={$programming->id}&amp;id={$code->id}&amp;direction=2'>MoveDown</a>, <a href='edit.php?a={$programming->id}&amp;id={$code->id}'>Edit</a>, <a href='delete.php?a={$programming->id}&amp;id={$code->id}'>Delete</a>";
            $table->add_data($data);
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
