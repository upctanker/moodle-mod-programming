<div class="maincontent generalbox">
<?php

    $table = new flexible_table('programming-contest-result');
    $table->define_columns(array('submitid', 'user', 'result', 'language', 'codesize', 'submittime'));
    $table->define_headers(array(
        get_string('submitid', 'programming'),
        get_string('user'),
        get_string('result', 'programming'),
        get_string('language', 'programming'),
        get_string('codesize', 'programming'),
        get_string('submittime', 'programming'),
    ));
    $table->set_attribute('cellspacing', '0');
    $table->set_attribute('id', 'contest-result');
    $table->set_attribute('class', 'generaltable generalbox');
    $table->define_baseurl($CFG->wwwroot.'/mod/programming/contest_result.php');
    $table->setup();

    foreach($submits as $submit) {
        $table->add_data(array(
            $submit->id,
            '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$submit->userid.'&amp;course='.$course->id.'">'.fullname($users[$submit->userid]).'</a>',
            $submit->result,
            $langs[$submit->language]->name,
            programming_format_codesize($submit->codesize),
            userdate($submit->timemodified)));
    }

    $table->print_html();

    print_paging_bar($pagenum, $page, $perpage, $CFG->wwwroot.'/mod/programming/contest_result.php?a='.$programming->id.'&');
?>
</div>
