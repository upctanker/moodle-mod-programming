<?PHP

    require_once("../../config.php");
    require_once("lib.php");
    require_once("../../lib/filelib.php");

    $a = optional_param('a', 0, PARAM_INT);     // programming ID
    $group = optional_param('group', 0, PARAM_INT);

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
    require_capability('mod/programming:viewotherprogram', $context);

    add_to_log($course->id, 'programming', 'package', me(), $programming->id);

    if ($group != 0) {
        $users = get_group_users($group);
    } else {
        $mygroupid = mygroupid($course->id);
        if ($mygroupid) {
            $users = get_group_users($mygroupid);
        } else {
            $users = False;
        }
    }

    $sql = "SELECT * FROM {$CFG->prefix}programming_submits WHERE programmingid={$programming->id}";
    if (is_array($users)) {
        $sql .= ' AND userid IN ('.implode(',', array_keys($users)).')';
    }
    $sql .= ' ORDER BY timemodified DESC';
    $submits = get_records_sql($sql);

    $users = array();
    $latestsubmits = array();
    if (is_array($submits)) {
        foreach ($submits as $submit) {
            if (in_array($submit->userid, $users)) continue;
            $users[] = $submit->userid;
            $latestsubmits[] = $submit;
        }
    }
    $sql = 'SELECT * FROM '.$CFG->prefix.'user WHERE id IN ('.implode(',', $users).')';
    $users = get_records_sql($sql);

    // create dir
    $dirname = $CFG->dataroot.'/temp';
    if (!file_exists($dirname)) {
        mkdir($dirname, 0777) or ('Failed to create dir');
    }
    $dirname .= '/programming';
    if (!file_exists($dirname)) {
        mkdir($dirname, 0777) or ('Failed to create dir');
    }
    $dirname .= '/'.$programming->id;
    if (file_exists($dirname)) {
        if (is_dir($dirname)) {
            fulldelete($dirname) or error('Failed to remove dir contents');
            //rmdir($dirname) or error('Failed to remove dir');
        } else {
            unlink($dirname) or error('Failed to delete file');
        }
    }
    mkdir($dirname, 0700) or error('Failed to create dir');

    $files = array();
    // write files
    foreach ($latestsubmits as $submit) {
        if ($submit->language == 1) $ext = '.c';
        elseif ($submit->language == 2) $ext = '.cxx';
        $filename = $dirname.'/'.$users[$submit->userid]->idnumber.'-'.$submit->id.$ext;
        $files[] = $filename;
        $f = fopen($filename, 'w');
        fwrite($f, $submit->code);
        fwrite($f, "\r\n");
        fclose($f);
    }

    // zip file
    $dest = $CFG->dataroot.'/'.$course->id.'/programming-'.$programming->id;
    if ($group === 0) {
        $dest .= '-all';
    } else {
        $group_obj = get_current_group($course->id, True);
        $dest .= '-'.$group_obj->name;
    }
    $dest .= '.zip';
    if (file_exists($dest)) {
        unlink($dest) or error("Failed to delete dest file");
    }
    zip_files($files, $dest);

    // remove temp
    fulldelete($dirname);

    $g = $group === 0 ? 'all' : $group_obj->name;
    $filelink = $CFG->wwwroot.($CFG->slasharguments ? '/file.php/' : '/file.php?file=/').$course->id.'/programming-'.$programming->id.'-'.$g.'.zip';
    $count = count($files);
    $referer = $_SERVER['HTTP_REFERER'];

    include_once('package.tpl.php');
?>
