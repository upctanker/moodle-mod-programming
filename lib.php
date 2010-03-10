<?PHP  // $Id: lib.php,v 1.3 2004/06/09 22:35:27 gustav_delius Exp $

/// Library of functions and constants for module programming
/// (replace programming with the name of your module and delete this line)

require_once($CFG->dirroot.'/lib/datalib.php');

define('PROGRAMMING_STATUS_NEW', 0);
define('PROGRAMMING_STATUS_COMPILING', 1);
define('PROGRAMMING_STATUS_COMPILEOK', 2);
define('PROGRAMMING_STATUS_RUNNING', 3);
define('PROGRAMMING_STATUS_WAITING', 4);
define('PROGRAMMING_STATUS_FINISH', 10);
define('PROGRAMMING_STATUS_COMPILEFAIL', 11);
define('PROGRAMMING_MAX_ATTEMPTS', 6);

define('PROGRAMMING_SHOWMODE_NORMAL', 1);
define('PROGRAMMING_SHOWMODE_CONTEST', 2);

define('PROGRAMMING_RESEMBLE_DELETED', -1);
define('PROGRAMMING_RESEMBLE_NEW', 0);
define('PROGRAMMING_RESEMBLE_WARNED', 1);
define('PROGRAMMING_RESEMBLE_CONFIRMED', 2);
define('PROGRAMMING_RESEMBLE_FLAG1', 11);
define('PROGRAMMING_RESEMBLE_FLAG2', 12);
define('PROGRAMMING_RESEMBLE_FLAG3', 13);

define('PROGRAMMING_TEST_SHOWAFTERDISCOUNT', -2);
define('PROGRAMMING_TEST_HIDDEN', -1);
define('PROGRAMMING_TEST_SHOWINRESULT', 0);
define('PROGRAMMING_TEST_SHOW', 1);

define('PROGRAMMING_PRESET_BEGIN', "/* PRESET CODE BEGIN - NEVER TOUCH CODE BELOW */");
define('PROGRAMMING_PRESET_END', "/* PRESET CODE END - NEVER TOUCH CODE ABOVE*/");

define('PROGRAMMING_RANGE_ALL', 0);
define('PROGRAMMING_RANGE_LATEST', 1);

function programming_add_instance($programming) {
/// Given an object containing all the necessary data, 
/// (defined by the form in mod.html) this function 
/// will create a new instance and return the id number 
/// of the new instance.

    $programming->timemodified = mktime();

    if ($programming->inputs == 0) $programming->inputfile = '';
    if ($programming->outputs == 0) $programming->outputfile = '';

    $id = insert_record('programming', $programming);

    if (isset($programming->langlimit) && is_array($programming->langlimit)) {
        foreach ($programming->langlimit as $lang) {
            $pl = new Object();
            $pl->programmingid = $id;
            $pl->languageid = $lang;
            insert_record('programming_langlimit', $pl);
        }
    }

    return $id;
}


function programming_update_instance($programming) {
/// Given an object containing all the necessary data, 
/// (defined by the form in mod.html) this function 
/// will update an existing instance with new data.

    $programming->timemodified = mktime();
    $programming->id = $programming->instance;

    if ($programming->inputs == 0) $programming->inputfile = '';
    if ($programming->outputs == 0) $programming->outputfile = '';

    if (isset($programming->keeplatestonly) and $programming->keeplatestonly) {
        programming_delete_old_submits($programming->id);
    }

    // Update the langlimit table
    $changed = false;
    if (isset($programming->langlimit) && is_array($programming->langlimit)) {
        $newlangs = $programming->langlimit;
    } else {
        $newlangs = array();
    }

    $langs = array();
    $rows = get_records('programming_langlimit', 'programmingid', $programming->id);
    if (is_array($rows)) {
        foreach ($rows as $row) {
            $langs[] = $row->languageid;
        }
    }

    foreach (array_diff($langs, $newlangs) as $lang) {
        delete_records('programming_langlimit', 'programmingid', $programming->id, 'languageid', $lang);
        $changed = true;
    }

    foreach (array_diff($newlangs, $langs) as $lang) {
        $pl = new Object();
        $pl->programmingid = $programming->id;
        $pl->languageid = $lang;
        insert_record('programming_langlimit', $pl);
        $changed = true;
    }

    return update_record("programming", $programming) or $changed;
}


function programming_delete_instance($id) {
/// Given an ID of an instance of this module, 
/// this function will permanently delete the instance 
/// and any data that depends on it.  

    if (! $programming = get_record('programming', id, $id)) {
        return false;
    }

    $result = true;

    # Delete any dependent records here #
    if (! delete_records('programming_langlimit', 'programmingid', $programming->id)) {
        $result = false;
    }

    if (! delete_records('programming', 'id', $programming->id)) {
        $result = false;
    }

    return $result;
}

function programming_user_outline($course, $user, $mod, $programming) {
/// Return a small object with summary information about what a 
/// user has done with a given particular instance of this module
/// Used for user activity reports.
/// $return->time = the time they did it
/// $return->info = a short text description

    global $CFG, $USER;

    $return = new Object();

    $ps = get_records_sql("
        SELECT ps.*
          FROM {$CFG->prefix}programming_submits AS ps,
               {$CFG->prefix}programming_result AS pr
         WHERE pr.programmingid = $programming->id
           AND pr.userid = $user->id
           AND pr.latestsubmitid = ps.id");

    if (is_array($ps)) {
        $ps = array_shift($ps);
        if ($programming->showmode == PROGRAMMING_SHOWMODE_NORMAL) {
            if ($ps->status == PROGRAMMING_STATUS_FINISH) {
                $return->info = programming_get_test_results_short($ps);
            } else {
                $return->info = programming_get_submit_status_short($ps);
            }
        } else {
            if ($ps->status == PROGRAMMING_STATUS_FINISH) {
                $results = get_records('programming_test_results', 'submitid', $ps->id);
                $return->info = programming_contest_get_judgeresult($results);
            } else if ($ps->status == PROGRAMMING_STATUS_COMPILEFAIL) {
                $return->info = get_string('CE', 'programming');
            }
        }
        $return->time = $ps->timemodified;
    }

    return $return;
}

function programming_user_complete($course, $user, $mod, $programming) {
/// Print a detailed representation of what a  user has done with 
/// a given particular instance of this module, for user activity reports.

    return true;
}

function programming_print_recent_activity($course, $isteacher, $timestart) {
/// Given a course and a time, this module should find recent activity 
/// that has occurred in programming activities and print it out. 
/// Return true if there was output, or false is there was none.

    global $CFG;

    return false;  //  True if anything was printed, otherwise false 
}

function programming_cron () {
/// Function to be run periodically according to the moodle cron
/// This function searches for things that need to be done, such 
/// as sending out mail, toggling flags etc ... 

    global $CFG;

    return true;
}

function programming_grades($programmingid) {
/// Must return an array of grades for a given instance of this module, 
/// indexed by user.  It also returns a maximum allowed grade.
///
///    $return->grades = array of grades;
///    $return->maxgrade = maximum allowed grade;
///
///    return $return;

    global $CFG;

    $return = new stdClass;
    $programming = get_record('programming', 'id', $programmingid);  
    $return->maxgrade = $programming->grade; // find the maximum allowed grade;
    $return->grades = array();

    // get the summary of weight of all the test
    $total_weight = get_field_select('programming_tests', 'SUM(weight)', 'programmingid='.$programmingid);
    $total_weight = $total_weight ? 1.0 / $total_weight : 0;

    // get weight summary of each submit
    $query = "
        SELECT w.userid AS userid,
               round({$programming->grade} * w.weight * $total_weight * (1 - d.discount * (1 - {$programming->discount} / 10.0)), 2) AS grade
          FROM (
            SELECT s.userid AS userid,
                   SUM(tr.passed * t.weight) AS weight
              FROM {$CFG->prefix}programming_result AS r,
                   {$CFG->prefix}programming_submits AS s,
                   {$CFG->prefix}programming_test_results AS tr,
                   {$CFG->prefix}programming_tests AS t
             WHERE r.programmingid=$programmingid
               AND s.programmingid=$programmingid
               AND t.programmingid=$programmingid
               AND r.latestsubmitid=s.id
               AND s.id=tr.submitid
               AND tr.testid=t.id
          GROUP BY s.userid) AS w, (
            SELECT s.userid AS userid,
                   s.timemodified > {$programming->timediscount} AS discount
              FROM {$CFG->prefix}programming_result AS r,
                   {$CFG->prefix}programming_submits AS s
             WHERE r.programmingid=$programmingid
               AND s.programmingid=$programmingid
               AND r.latestsubmitid=s.id
          GROUP BY s.userid) AS d
         WHERE d.userid = w.userid
      ";
    //print $query;
    $grades = get_records_sql($query);
        
    if (is_array($grades)) {
        foreach($grades as $grade) {
            $return->grades[$grade->userid] = $grade->grade;
        }
    }

    return $return;
}

/**
 * Update the grade of a submission.
 *
 * @param $submitid ID of submission.
 */
function programming_update_grade($submitid) {
    global $CFG;

    $submit = get_record('programming_submits', 'id', $submitid);
    if (empty($submit)) return;

    # do not update grade if this is not the latest submission
    $rst = get_record('programming_result', 'programmingid', $submit->programmingid, 'userid', $submit->userid);
    if ($rst->latestsubmitid != $submitid) return;

    $p = get_record('programming', 'id', $submit->programmingid);
    if (empty($p)) return;

    $grade = 0;
    if ($submit->judgeresult != 'CE') {
        // get the summary of weight of all the test
        $total_weight = get_field_select('programming_tests', 'SUM(weight)', 'programmingid='.$submit->programmingid);
        if ($total_weight == 0) $total_weight = 1;
        $sql = "SELECT SUM(pt.weight) AS weight
                  FROM {$CFG->prefix}programming_test_results AS ptr,
                       {$CFG->prefix}programming_tests AS pt
                 WHERE ptr.submitid = $submit->id
                   AND ptr.passed = 1
                   AND pt.programmingid = $submit->programmingid
                   AND ptr.testid = pt.id";
        $rs = get_recordset_sql($sql);
        if ($rs) {
            if ($row = $rs->FetchNextObject(false)) {
                $grade = $p->grade * $row->weight / $total_weight;
                if ($submit->timemodified > $p->timediscount) {
                    $grade = $grade * $p->discount / 10.0;
                }
            }
            $rs->Close();
        }
    }
    programming_grade_item_update($p, $submit, $grade);
}

function programming_grade_item_update($programming, $submit, $grade) {
    global $CFG;

    if (!function_exists('grade_update')) { //workaround for buggy PHP versions
        require_once($CFG->libdir.'/gradelib.php');
    }

    $cm = get_coursemodule_from_instance('programming', $programming->id, $programming->course);

    $params = array('itemname' => $programming->name,
                    'idnumber' => $cm->id,
                    'grademax' => $programming->grade,
                    'grademin' => 0,
                    'gradetype' => GRADE_TYPE_VALUE);
    $grades = new Object();
    $grades->itemid = $cm->id;
    $grades->userid = $submit->userid;
    $grades->rawgrade = $grade;

    return grade_update('mod/programming', $programming->course, 'mod', 'programming', $programming->id, 0, $grades, $params);
}

function programming_get_participants($programmingid) {
//Must return an array of user records (all data) who are participants
//for a given instance of programming. Must include every user involved
//in the instance, independient of his role (student, teacher, admin...)
//See other modules as example.

    global $CFG;
    
    $sql = "SELECT DISTINCT u.*
              FROM {$CFG->prefix}programming_submits AS ps,
                   {$CFG->prefix}user AS u,
              WHERE ps.programmingid={$programmingid}
               AND ps.userid = u.id";
    $users = get_records_sql($sql);

    return $users;
}

function programming_scale_used ($programmingid,$scaleid) {
//This function returns if a scale is being used by one programming
//it it has support for grading and scales. Commented code should be
//modified if necessary. See forum, glossary or journal modules
//as reference.
   
    $return = false;

    //$rec = get_record("programming","id","$programmingid","scale","-$scaleid");
    //
    //if (!empty($rec)  && !empty($scaleid)) {
    //    $return = true;
    //}
   
    return $return;
}

//////////////////////////////////////////////////////////////////////////////////////
/// Any other programming functions go here.  Each of them must have a name that 
/// starts with programming_

function programming_get_showmode_options() {
    $options = array();
    $options['1'] = get_string('normalmode', 'programming');
    $options['2'] = get_string('contestmode', 'programming');
    return $options;
}

function programming_get_yesno_options() {
    $yesnooptions = array();
    $yesnooptions[0] = get_string('no');
    $yesnooptions[1] = get_string('yes');
    return $yesnooptions;
}

function programming_get_timelimit_options($default = 0) {
    $timelimitoptions = array();
    $timelimitoptions[0] = get_string('timelimitunlimited', 'programming');
    for ($i = 1; $i <= 30; $i += 1) {
        $timelimitoptions[$i] = get_string('nseconds', 'programming', $i);
    }
    return $timelimitoptions;
}

function programming_testcase_pub_options() {
    $options = array();
    $options[PROGRAMMING_TEST_SHOWAFTERDISCOUNT] = get_string('afterdiscount', 'programming');
    $options[PROGRAMMING_TEST_HIDDEN] = get_string('never', 'programming');
    $options[PROGRAMMING_TEST_SHOWINRESULT] = get_string('inresult', 'programming');
    $options[PROGRAMMING_TEST_SHOW] = get_string('always', 'programming');
    return $options;
}

function programming_testcase_pub_getstring($pub) {
    $options = programming_testcase_pub_options();
    return $options[$pub];    
}

function programming_testcase_require_view_capability($context, $case, $inresult = false, $afterdiscount = false)
{
    if ($case->pub == PROGRAMMING_TEST_HIDDEN ||
        $case->pub == PROGRAMMING_TEST_SHOWINRESULT && $inresult ||
        $case->pub == PROGRAMMING_TEST_SHOWAFTERDISCOUNT && $afterdiscount) {
        require_capability('mod/programming:viewhiddentestcase', $context);
    } else {
        require_capability('mod/programming:viewpubtestcase', $context);
    }
}

function programming_testcase_visible($tests, $result, $inresult = false, $afterdiscount = false)
{
    if (! is_object($result)) {
        return false;
    }

    $pub = $tests[$result->testid]->pub;
    return $pub == PROGRAMMING_TEST_SHOW ||
           $pub == PROGRAMMING_TEST_SHOWINRESULT && $inresult ||
           $pub == PROGRAMMING_TEST_SHOWAFTERDISCOUNT && $afterdiscount;
}

function programming_get_weight_options() {
    $weightoptions = array();
    //$weightoptions[0] = get_string('weightsetting', 'programming');
    for ($i = 0; $i <= 10; $i += 1) {
        $weightoptions[$i] =  $i;
    }
    return $weightoptions;
}

function programming_get_memlimit_options() {
    $memlimitoptions = array();
    $memlimitoptions[0] = get_string('memlimitunlimited', 'programming');
    for ($i = 256; $i <= 512; $i += 32) {
        $memlimitoptions[$i] = $i.'KB';
    }
    for ($i = 1; $i < 10; $i++) {
        $memlimitoptions[$i*1024] = $i.'MB';
    }
    for ($i = 10; $i <= 150; $i+= 10) {
        $memlimitoptions[$i*1024] = $i.'MB';
    }
    return $memlimitoptions;
}

function programming_get_nproc_options() {
    $options = array();
    for ($i = 0; $i <= 16; $i++) {
        $options[$i] = "$i";
    }
    return $options;
}

function programming_get_language_options($programming = False) {
    global $CFG;
    if ($programming) {
        $sql = "SELECT * FROM {$CFG->prefix}programming_languages
                 WHERE id in (
                SELECT languageid
                  FROM {$CFG->prefix}programming_langlimit
                 WHERE programmingid={$programming->id})";
        $languages = get_records_sql($sql);
        if (!is_array($languages) || count($languages) == 0) {
            $languages = get_records('programming_languages');
        }
    } else {
        $languages = get_records('programming_languages');
    }
    $languageoptions = array();
    foreach ($languages as $id => $row) {
        $languageoptions[$id] = $row->name;
    }
    return $languageoptions;
}

function programming_test_add_instance($testcase) {
    if (strlen($testcase->input) > 1024) {
        $testcase->gzinput = addslashes(bzcompress($testcase->input));
        $testcase->input = substr($testcase->input, 0, 1024);
    }
    if (strlen($testcase->output) > 1024) {
        $testcase->gzoutput = addslashes(bzcompress($testcase->output));
        $testcase->output = substr($testcase->output, 0, 1024);
    }
    $testcase->input = addslashes($testcase->input);
    $testcase->output = addslashes($testcase->output);
    $testcase->timemodified = time();
    return insert_record("programming_tests", $testcase);
}

function programming_test_update_instance($testcase) {
    # If input is not provided, do not update input and gzinput
    if (empty($testcase->input)) {
        unset($testcase->input);
        unset($testcase->gzinput);
    } else {
        $testcase->gzinput = addslashes(bzcompress($testcase->input));
        $testcase->input = addslashes(substr($testcase->input, 0, 1024));
    }

    # If output is not provided, do not update output and gzoutput
    if (empty($testcase->output)) {
        unset($testcase->output);
        unset($testcase->gzoutput);
    } else {
        $testcase->gzoutput = addslashes(bzcompress($testcase->output));
        $testcase->output = addslashes(substr($testcase->output, 0, 1024));
    }

    $testcase->timemodified = time();
    return update_record('programming_tests', $testcase);
}

function programming_submit_add_instance($programming, $submit) {
    global $CFG, $USER;

    $submit->timemodified = time();
    $submit->codelines = count(explode("\n", $submit->code));
    $submit->codesize = strlen($submit->code);
    $submit->status = 0;
    $result = insert_record('programming_submits', $submit);

    # update programming_result
    if ($result) {
        $r = get_record('programming_result', 'programmingid', $programming->id, 'userid', $USER->id);
        if ($r) {
            $r->submitcount++;
            $r->latestsubmitid = $result;
            $result2 = update_record('programming_result', $r);
        } else {
            $r = new stdClass;
            $r->programmingid = $programming->id;
            $r->userid = $submit->userid;
            $r->submitcount = 1;
            $r->latestsubmitid = $result;
            $result2 = insert_record('programming_result', $r);
        }
    }

    # update tester
    if ($result) {
        $sql = "INSERT INTO {$CFG->prefix}programming_testers
                            (submitid) VALUES ($result)";
        execute_sql($sql, false);
    }

    # delete the old submits
    if ($result and $programming->keeplatestonly) {
        programming_delete_old_submits($programming->id, $submit->userid);
    }

    return $result;
}

function programming_delete_old_submits($programmingid = -1, $userid = -1) {
    global $CFG;

    if ($programmingid > 0) {
        if ($userid >= 0) {
            $sql = "SELECT id, programmingid, userid FROM {$CFG->prefix}programming_submits WHERE programmingid = {$programmingid} AND userid = {$userid} ORDER BY timemodified DESC";
        } else {
            $sql = "SELECT id, programmingid, userid FROM {$CFG->prefix}programming_submits WHERE programmingid = {$programmingid} ORDER BY userid, timemodified DESC";
        }
    } else {
        $sql = "SELECT id, programmingid, userid FROM {$CFG->prefix}programming_submits ORDER BY programmingid, userid, timemodified DESC";
    }
    $submits = get_records_sql($sql);
    if (is_array($submits)) {
        $ids = array();
        $pprogramming = -1;
        $puser = -1;
        foreach ($submits as $row) {
            if ($row->programmingid != $pprogramming) {
                $puser = -1;
            }
            if ($row->userid != $puser) {
                $c = 1;
            } else {
                $c++;
            }
            if ($c > PROGRAMMING_MAX_ATTEMPTS) {
                $ids[] = $row->id;
            }
            $puser = $row->userid;
            $pprogramming = $row->programmingid;
        }
        $ids = implode(',', $ids);
    
        if ($ids) {
            #delete from submits table
            execute_sql("DELETE FROM {$CFG->prefix}programming_submits WHERE id IN ({$ids})", false);

            #delete from test results table
            execute_sql("DELETE FROM {$CFG->prefix}programming_test_results WHERE submitid IN ({$ids})", false);
        }
    }

    return true;
}

function programming_get_submit_status_short($submit) {
    switch ($submit->status) {
    case PROGRAMMING_STATUS_NEW:
        return get_string('statusshortnew', 'programming');
    case PROGRAMMING_STATUS_COMPILING:
        return get_string('statusshortcompiling', 'programming');
    case PROGRAMMING_STATUS_COMPILEOK:
        return get_string('statusshortcompileok', 'programming');
    case PROGRAMMING_STATUS_RUNNING:
        return get_string('statusshortrunning', 'programming');
    case PROGRAMMING_STATUS_FINISH:
        return get_string('statusshortfinish', 'programming');
    case PROGRAMMING_STATUS_COMPILEFAIL:
        return get_string('statusshortcompilefail', 'programming');
    }
    return '';
}

function programming_get_submit_status_desc($submit) {
    switch ($submit->status) {
    case PROGRAMMING_STATUS_NEW:
        return get_string('statusnew', 'programming');
    case PROGRAMMING_STATUS_COMPILING:
        return get_string('statuscompiling', 'programming');
    case PROGRAMMING_STATUS_COMPILEOK:
        return get_string('statuscompileok', 'programming');
    case PROGRAMMING_STATUS_RUNNING:
        return get_string('statusrunning', 'programming');
    case PROGRAMMING_STATUS_FINISH:
        return get_string('statusfinish', 'programming');
    case PROGRAMMING_STATUS_COMPILEFAIL:
        return get_string('statuscompilefail', 'programming');
    }
    return '';
}

function programming_get_test_results_short($submit) {
    $c = new Object();
    $c->total = count_records('programming_tests', 'programmingid', $submit->programmingid);
    $c->success = count_records('programming_test_results', 'submitid', $submit->id, 'passed', 1);
    $c->fail = $c->total - $c->success;
    if ($c->total == $c->success) {
        return get_string('passalltests', 'programming', $c);
    } else {
        return get_string('successfailshort', 'programming', $c);
    }
}

function programming_get_test_results_desc($submit, $results) {
    $c = new Object();
    $c->success = 0; $c->fail = 0;
    if (is_array($results)) {
        foreach ($results as $result) {
            if ($result->passed == 0) $c->fail++;
            else $c->success++;
        }
    }
    $c->total = $c->success + $c->fail;
    return get_string('successfailcount', 'programming', $c);
}

function programming_format_codesize($size) {
    if ($size < 1000)
        return $size.'B';
    else
        return round($size/1000, 2).'K';
}

function check_crarr() {
    global $_SERVER;

    $uagent = $_SERVER['HTTP_USER_AGENT'];
    if (!strpos($uagent, 'MSIE')) {
        return '&crarr;';
    } else {
        return '<span style="font-family: Wingdings 3">&crarr;</span>';
    }
}

function programming_format_io($message, $autolastreturn = false) {
    $crarr = check_crarr();
    $sizelimit = 1024;

    $haslastreturn = false;
    if (substr($message, strlen($message)-1, 1) == "\n") {
        $haslastreturn = true;
        $message = substr($message, 0, strlen($message)-1);
    }
       
    $message = str_replace(
        array(' ', "\r", "\n"),
        array('&nbsp;', '', $crarr.'</span></li><li><span>'),
        htmlspecialchars(substr($message, 0, $sizelimit)));

    if ($haslastreturn || $autolastreturn) $message .= $crarr;

    return '<div><ol><li><span>'.$message.'</span></li></ol></div>';
}

function programming_format_compile_message($message) {
    $lines = explode("\n", trim($message));
    $html = '<ol>';
    $t = '';
    foreach ($lines as $line) {
        if (strstr($line, 'warning:')) $t = 'warning';
        else if (strstr($line, 'error:')) $t = 'error';
        else $t = 'normal';

        $html .= "<li class='$t'>";
        $html .= str_replace(' ', '&nbsp;', htmlspecialchars($line));
        $html .= '</li>';
    }
    $html .= '</ol>';
    return $html;
}

function programming_parse_compile_message($message) {
    $r = new Object();
    $r->warnings = count(preg_split("/\d: warning: /", $message)) - 1;
    $r->errors =  count(preg_split("/\d: error: /", $message)) - 1;
    return $r;
}

function programming_delete_submit($submit) {
    global $CFG;

    delete_records('programming_test_results', 'submitid', $submit->id);
    delete_records('programming_submits', 'id', $submit->id);

    # update programming_result table
    $submits = get_records_sql("
        SELECT * FROM {$CFG->prefix}programming_submits
         WHERE programmingid={$submit->programmingid}
           AND userid={$submit->userid}
      ORDER BY timemodified DESC");
    $r = get_record('programming_result', 'programmingid', $submit->programmingid, 'userid', $submit->userid);
    if (is_array($submits) && count($submits)) {
        $s = array_shift($submits);
        $r->latestsubmitid = $s->id;
    } else {
        $r->latestsubmitid = 0;
    }
    update_record('programming_result', $r);
}

function programming_rejudge($programming, $submitid, $groupid, $ac) {
    global $CFG;

    if (empty($submitid)) {
        $users = False;
        if ($groupid != 0) {
            $users = get_group_users($groupid);
        }
        $sql = "SELECT latestsubmitid
                  FROM {$CFG->prefix}programming_result AS pr,
                       {$CFG->prefix}programming_submits AS ps
                 WHERE pr.latestsubmitid = ps.id
                   AND pr.programmingid = {$programming->id}
                   AND ps.programmingid = {$programming->id}";
        if (!$ac) {
            $sql .= " AND (ps.passed = 0 OR ps.passed IS NULL)";
        }
        if ($users) {
            $sql .= " AND ps.userid IN (".implode(',', array_keys($users));
            $sql .= " AND pr.userid IN (".implode(',', array_keys($users));
        }
        $lsids = get_records_sql($sql);
        foreach ($lsids as $submit) {
            $ids[] = $submit->latestsubmitid;
        }
    } else {
        $ids = $submitid;
    }
    $ids = implode(',', $ids);

    $sql = "submitid IN ($ids)";
    delete_records_select('programming_test_results', $sql);

    $sql = "UPDATE {$CFG->prefix}programming_submits
               SET judgeresult=null, timeused=null, memused=null,
                   passed=null, status=0, compilemessage=null
             WHERE id IN ($ids)";
    execute_sql($sql, False);

    $sql = "INSERT INTO {$CFG->prefix}programming_testers
            SELECT id, 0, 3, 0
              FROM {$CFG->prefix}programming_submits
             WHERE id IN ($ids)";
    execute_sql($sql, False);
}

function newme($params = NULL) {
    $uri = me();
    $append = '';
    $search = array();
    $replace = array();
    if (is_array($params)) {
        foreach ($params as $name => $value) {
            $s = '/(.*)&'.$name.'=[\d\w+%]*(.*)/';
            if (preg_match($s, $uri)) {
                $search[] = $s;
                $replace[] = '\1&'.$name.'='.htmlspecialchars($value).'\2';
            } else {
                $append .= '&'.$name.'='.htmlspecialchars($value);
            }
        }
    }
    return preg_replace($search, $replace, $uri).$append;
}

function programming_check_privilege($courseid, $groupid) {
    if (! isteacher($courseid)) return False;
    if (isteacheredit($courseid) || groupmode($courseid, $cm) == NOGROUPS) return True;
    if ($groupid === mygroupid($courseid)) return True;
    return False;
}

function programming_submit_timeused(&$results) {
    $time = 0;
    foreach ($results as $r) {
        $time = max($time, $r->timeused);
    }
    return $time;
}

function programming_submit_memused(&$results) {
    $mem = 0;
    foreach ($results as $r) {
        $mem = max($mem, $r->memused);
    }
    return $mem;
}

function programming_judgeresult_options($addempty = false) {
    $rst = array('AC', 'PE', 'WA', 'RE', 'FPE', 'KS', 'OLE', 'MLE', 'TLE', 'RFC', 'JGE', 'JSE');
    $ret = array();
    if ($addempty) $ret[''] = get_string('all');
    foreach ($rst as $k) {
        $ret[$k] = get_string($k, 'programming');
    }
    return $ret;
}

function programming_submit_judgeresult(&$results) {
    $err = array('JSE' => 20, 'JGE' => 19, 'RFC' => 18,
                 'TLE' => 10, 'MLE' => 9, 'OLE' => 8,
                 'KS' => 14, 'FPE' => 13, 'RE' => 12, 'WA' => 11, 'AC' => 0);

    $c = -1;
    $errstr = null;
    foreach ($results as $result) {
        if (isset($err[$result->judgeresult])
            && $err[$result->judgeresult] > $c) {
            $c = $err[$result->judgeresult];
            $errstr = $result->judgeresult;
        }
    }
    return $errstr;
}

function programming_contest_get_judgeresult(&$results) {
    $errstr = programming_submit_judgeresult($results);
    return get_string($errstr, 'programming');
}

function programming_get_judgeresult($result) {
    if ($result->judgeresult) {
        return '<a title="'.get_string($result->judgeresult.':description', 'programming').'">'.get_string($result->judgeresult, 'programming').'</a>';
    } else {
        return programming_get_fail_reason($result);
    }
}

function programming_get_fail_reason($result, $showmode = PROGRAMMING_SHOWMODE_NORMAL) {
    if ($result->passed) return get_string('n/a', 'programming');

    switch ($result->signal) {
    case 0:
        break;
    case 8:
        if ($showmode == PROGRAMMING_SHOWMODE_NORMAL)
            return get_string('failbecausefpe', 'programming');
        else
            return get_string('runtimeerror', 'programming');
    case 15:
        if ($showmode == PROGRAMMING_SHOWMODE_NORMAL)
            return get_string('failbecausetimelimit', 'programming');
        else
            return get_string('timelimitexceed', 'programming');
    case 11:
        if ($showmode == PROGRAMMING_SHOWMODE_NORMAL)
            return get_string('failbecausesegv', 'programming');
        else
            return get_string('runtimeerror', 'programming');
    case 9:
    case 24:
        if ($showmode == PROGRAMMING_SHOWMODE_NORMAL)
            return get_string('failbecausecputimelimit', 'programming');
        else
            return get_string('timelimitexceed', 'programming');
    default:
        if ($showmode == PROGRAMMING_SHOWMODE_NORMAL)
            return get_string('failbecauseunknownsig', 'programming', $result->signal);
        else
            return get_string('runtimeerror', 'programming');
    }

    if ($showmode == PROGRAMMING_SHOWMODE_NORMAL) {
        switch ($result->exitcode) {
        case 125:
            return get_string('failbecausejudgescript', 'programming');
        case 126:
            return get_string('failbecauserestrict', 'programming');
        case 127:
            return get_string('failbecausesimpleguard', 'programming');
        default:
            return get_string('failbecausewrongresult', 'programming');
        }
    } else {
        switch ($result->exitcode) {
        case 125:
        case 126:
        case 127:
            return get_string('runtimeerror', 'programming');
        default:
            return get_string('wronganswer', 'programming');
        }
    }
}

function programming_format_timelimit($timelimit) {
    if ($timelimit) {
        return get_string('nseconds', 'programming', $timelimit);
    } else {
        return get_string('timelimitunlimited', 'programming');
    }
}

function programming_format_memlimit($memlimit) {
    if ($memlimit) {
        return get_string('nkb', 'programming', $memlimit);
    } else {
        return get_string('memlimitunlimited', 'programming');
    }
}

function programming_judge_status($courseid, &$totalcount, $offset=0, $limit=15) {
    global $CFG, $USER;

    if (!isset($courseid)) return False;

    $crit = $courseid != 1 ? "p.course = $courseid AND" : '';
    $sql = "SELECT ps.id AS psid,
                   ps.userid AS userid,
                   p.globalid,
                   p.name AS pname,
                   ps.timemodified as timemodified,
                   p.id AS pid,
                   ps.status as status
              FROM {$CFG->prefix}programming AS p,
                   {$CFG->prefix}programming_submits AS ps
             WHERE $crit p.id = ps.programmingid
          ORDER BY ps.timemodified DESC";
    $rows = get_records_sql($sql, $offset, $limit);

    if ($courseid == 1) {
        $sql = "SELECT COUNT(*) AS total
                  FROM {$CFG->prefix}programming_submits AS ps";
    } else {
        $sql = "SELECT COUNT(*) AS total
                  FROM {$CFG->prefix}programming AS p,
                       {$CFG->prefix}programming_submits AS ps
                 WHERE p.course = $courseid
                   AND ps.programmingid = p.id";
    }
    $totalcount = count_records_sql($sql);

    if ($rows) {
        foreach ($rows as $row) {
            $row->user = get_record('user', 'id', $row->userid);
            switch ($row->status) {
            case PROGRAMMING_STATUS_COMPILEFAIL:
                $row->judgeresult = get_string('CE', 'programming');
                break;
            case PROGRAMMING_STATUS_FINISH:
                $tr = get_records('programming_test_results', 'submitid', $row->psid);
                $row->judgeresult = '';
                if ($tr) {
                    $row->judgeresult = programming_contest_get_judgeresult($tr);
                    $row->timeused = 0;
                    foreach($tr as $r) {
                        $row->timeused = max($row->timeused, $r->timeused);
                    }
                    $row->memused = 'Unknown';
                }
                break;
            default:
                $row->judgeresult = get_string('statusshortnew', 'programming');
            }
        }
    }

    return $rows;
}

function programming_latest_ac($courseid, $roleid, &$totalcount, $offset=0, $limit=15) {
    global $CFG, $USER;

    if (!isset($courseid)) return False;
    $context = get_context_instance(CONTEXT_COURSE, $courseid);

    if ($courseid == 1) {
        $sql = "SELECT ps.id AS psid,
                       ps.userid AS userid,
                       p.name AS pname,
                       p.globalid,
                       ps.timemodified as timemodified,
                       p.id AS pid
                  FROM {$CFG->prefix}programming AS p,
                       {$CFG->prefix}programming_result AS pr,
                       {$CFG->prefix}programming_submits AS ps
                 WHERE pr.programmingid = p.id
                   AND pr.latestsubmitid = ps.id
                   AND ps.passed = 1
              ORDER BY ps.timemodified DESC";
        $rows = get_records_sql($sql, $offset, $limit);

        $sql = "SELECT COUNT(*) AS total
                  FROM {$CFG->prefix}programming AS p,
                       {$CFG->prefix}programming_result AS pr,
                       {$CFG->prefix}programming_submits AS ps
                 WHERE pr.programmingid = p.id
                   AND pr.latestsubmitid = ps.id
                   AND ps.passed = 1";
        $totalcount = count_records_sql($sql);

    } else {

        $sql = "SELECT ps.id AS psid,
                       ps.userid AS userid,
                       p.name AS pname,
                       p.globalid,
                       ps.timemodified as timemodified,
                       p.id AS pid
                  FROM {$CFG->prefix}programming AS p,
                       {$CFG->prefix}programming_result AS pr,
                       {$CFG->prefix}programming_submits AS ps,
                       {$CFG->prefix}role_assignments AS ra
                 WHERE p.course = {$courseid}
                   AND ra.roleid = {$roleid}
                   AND ra.contextid = {$context->id}
                   AND pr.programmingid = p.id
                   AND pr.latestsubmitid = ps.id
                   AND ps.passed = 1
                   AND pr.userid = ra.userid
              ORDER BY ps.timemodified DESC";
        $rows = get_records_sql($sql, $offset, $limit);

        $sql = "SELECT COUNT(*) AS total
                  FROM {$CFG->prefix}programming AS p,
                       {$CFG->prefix}programming_result AS pr,
                       {$CFG->prefix}programming_submits AS ps,
                       {$CFG->prefix}role_assignments AS ra
                 WHERE p.course = {$courseid}
                   AND ra.roleid = {$roleid}
                   AND ra.contextid = {$context->id}
                   AND pr.programmingid = p.id
                   AND pr.latestsubmitid = ps.id
                   AND ps.passed = 1
                   AND pr.userid = ra.userid";
        $totalcount = count_records_sql($sql);
    }

    if ($rows) {
        foreach ($rows as $row) {
            $row->user = get_record('user', 'id', $row->userid);
        }
    }

    return $rows;
}

/**
 * Calculate the standing of the contest.
 *
 *  1. Each run is either accepted or rejected.
 *  2. The problem is considered solved by the team, if one of the runs
 *     submitted for it is accepted.
 *  3. The time consumed for a solved problem is the time elapsed from
 *     the beginning of the contest to the submission of the first accepted
 *     run for this problem (in minutes) plus 20 minutes for every other run
 *     for this problem before the accepted one. For an unsolved problem
 *     consumed time is not computed.
 *  4. The total time is the sum of the time consumed for each problem solved.
 *  5. Teams are ranked according to the number of solved problems. Teams that
 *     solve the same number of problems are ranked by the least total time.
 *  6. While the time shown is in minutes, the actual time is measured to the
 *     precision of 1 second, and the the seconds are taken into account when
 *     ranking teams.
 *  7. Teams with equal rank according to the above rules must be sorted by
 *     increasing team number. 
 *
 * @param courseid In which course the contest is hold.
 * @param wrongsubmitminutes How many minutes for wrong submit.
 * @param roleid Which role is included
 * @return An array contains all the information of teams.
 */
function programming_calc_standing($courseid, $roleid, $wrongsubmitminutes = 20, $offset=0, $limit=10) {
    global $CFG;

    if (!isset($courseid)) return array();
    if ($courseid == 1) {
        $query = "SELECT pr.userid,
                         COUNT(*) AS ac
                    FROM {$CFG->prefix}programming_result AS pr,
                         {$CFG->prefix}programming_submits AS ps
                   WHERE pr.latestsubmitid = ps.id
                     AND ps.passed = 1
                GROUP BY pr.userid
                ORDER BY ac DESC";
    } else {
        $context = get_context_instance(CONTEXT_COURSE, $courseid);
        $wss = $wrongsubmitminutes * 60;

        $query =
           "SELECT pr.userid,
                   SUM(ps.passed) AS ac,
                   SUM((ps.timemodified-p.timeopen)*ps.passed) AS timeused,
                   SUM(pr.submitcount * ps.passed) AS submitcount,
                   SUM(((ps.timemodified - p.timeopen) +
                       (pr.submitcount - ps.passed)*{$wss}) * ps.passed) AS penalty
              FROM {$CFG->prefix}programming AS p,
                   {$CFG->prefix}programming_result AS pr,
                   {$CFG->prefix}programming_submits AS ps,
                   {$CFG->prefix}role_assignments AS ra
             WHERE p.course={$courseid}
               AND ra.roleid = {$roleid}
               AND ra.contextid = {$context->id}
               AND ps.programmingid = p.id
               AND pr.programmingid = p.id
               AND pr.userid = ra.userid
               AND pr.latestsubmitid = ps.id
          GROUP BY pr.userid
            HAVING ac > 0
          ORDER BY ac DESC, penalty ASC";
    }

    //echo $query;

    $standing = get_records_sql($query, $offset, $limit);
    if (!is_array($standing)) $standing = array();
    foreach ($standing as $row) {
        $row->user = get_record('user', 'id', $row->userid);
    }

    return $standing;
}

function programming_count_standing($courseid, $roleid) {
    global $CFG;

    if (!isset($courseid)) return 0;
    if ($courseid == 1) {
        $query = "SELECT COUNT(*) AS COUNT FROM (
                  SELECT DISTINCT pr.userid
                    FROM {$CFG->prefix}programming_result AS pr,
                         {$CFG->prefix}programming_submits AS ps
                   WHERE pr.latestsubmitid = ps.id
                     AND ps.passed = 1) AS c";
    } else {
        $context = get_context_instance(CONTEXT_COURSE, $courseid);

        $query =
           "SELECT COUNT(*) AS count FROM (
                SELECT DISTINCT pr.userid
                  FROM {$CFG->prefix}programming AS p,
                       {$CFG->prefix}programming_result AS pr,
                       {$CFG->prefix}programming_submits AS ps,
                       {$CFG->prefix}role_assignments AS ra
                 WHERE p.course={$courseid}
                   AND ra.roleid = {$roleid}
                   AND ra.contextid = {$context->id}
                   AND ps.programmingid = p.id
                   AND pr.programmingid = p.id
                   AND pr.userid = ra.userid
                   AND pr.latestsubmitid = ps.id
                   AND ps.passed = 1) AS c";
    }
    //echo $query;

    return count_records_sql($query);
}

function programming_submit_remove_preset($code)
{
    $ret = array();
    $s = 0;

    $lines = explode("\n", $code);
    foreach ($lines as $line) {
        $line = trim($line, "\r");
        switch ($s) {
        case 0:
            if ($line == PROGRAMMING_PRESET_BEGIN) {
                $s = 1;
            }
            if ($s == 0) {
                $ret[] = $line;
            }
            break;
        case 1:
            if ($line == PROGRAMMING_PRESET_END) {
                $s = 0;
            }
            break;
        }
    }

    return implode("\n", $ret);
}

/**
 * Put prepend and postpend preset code and user submitted code together.
 */
function programming_format_code($programming, $submit, $check = false)
{
    if (is_object($programming)) {
        $programming = $programming->id;
    }
    $prepend = get_record('programming_presetcode', 'programmingid', $programming, 'languageid', $submit->language, 'name', '<prepend>');
    $postpend = get_record('programming_presetcode', 'programmingid', $programming, 'languageid', $submit->language, 'name', '<postpend>');

    $ret = array();
    if (!empty($prepend)) {
        $ret[] = PROGRAMMING_PRESET_BEGIN;
        $ret[] = trim(!$check || empty($prepend->presetcodeforcheck) ? $prepend->presetcode : $prepend->presetcodeforcheck);
        $ret[] = PROGRAMMING_PRESET_END;
    }

    if (is_object($submit)) {
        $ret[] = $submit->code;
    }

    if (!empty($postpend)) {
        $ret[] = PROGRAMMING_PRESET_BEGIN;
        $ret[] = trim(!$check || empty($postpend->presetcodeforcheck) ? $postpend->presetcode : $postpend->presetcodeforcheck);
        $ret[] = PROGRAMMING_PRESET_END;
    }
    return implode("\n\n", $ret);
}

function programming_get_presetcode_name($presetcode) {
    switch ($presetcode->name) {
    case '<prepend>':
        return get_string('prependcode', 'programming');
    case '<postpend>':
        return get_string('postpendcode', 'programming');
    default:
        return $presetcode->name;
    }
}

function programming_format_presetcode($presetcode) {
    $ret = array(PROGRAMMING_PRESET_BEGIN, trim($presetcode->presetcode),
                 PROGRAMMING_PRESET_END);
    return implode("\n\n", $ret);
}

function get_visible_programmings($courseid)
{
    $sql = "SELECT p.*, cm.visible
              FROM {$CFG->prefix}course_modules cm,
                   {$CFG->prefix}programming p
             WHERE cm.course=$courseid
               AND p.course=$courseid
               AND cm.instance = p.id
          ORDER BY p.name";
    return get_records_sql($sql);
}

function programming_adjust_sequence(&$records, $moveid, $direction)
{
    $seq = array();
    $i = 1; $idx = 0;
    foreach ($records as $rec) {
        if ($moveid == $rec->id) $idx = $i;
        $seq[$i++] = $rec;
    }

    if ($idx) {
        if ($direction == 1 && $idx > 1) { // move up
            $t = $seq[$idx];
            $seq[$idx] = $seq[$idx-1];
            $seq[$idx-1] = $t;
        } else if ($direction == 2 && $idx < count($records)) {
            $t = $seq[$idx];
            $seq[$idx] = $seq[$idx+1];
            $seq[$idx+1] = $t;
        }
    }
    return $seq;
}

function programming_presetcode_adjust_sequence($programmingid, $moveid = 0, $direction = 0)
{
    $codes = get_records('programming_presetcode', 'programmingid', $programmingid, 'sequence', 'id, sequence');
    if (!is_array($codes)) return;

    $seq = programming_adjust_sequence($codes, $moveid, $direction);

    foreach ($seq as $i => $code) {
        if ($code->sequence != $i) {
            $code->sequence = $i;
            update_record('programming_presetcode', $code);
        }
    }
}

function programming_datafile_adjust_sequence($programmingid, $moveid = 0, $direction = 0)
{
    $codes = get_records('programming_datafile', 'programmingid', $programmingid, 'seq', 'id, seq');
    if (!is_array($codes)) return;

    $seq = programming_adjust_sequence($codes, $moveid, $direction);

    foreach ($seq as $i => $code) {
        if ($code->seq != $i) {
            $code->seq = $i;
            update_record('programming_datafile', $code);
        }
    }
}

function programming_testcase_adjust_sequence($programmingid, $moveid = 0, $direction = 0)
{
    $codes = get_records('programming_tests', 'programmingid', $programmingid, 'seq', 'id, seq');
    if (!is_array($codes)) return;

    $seq = programming_adjust_sequence($codes, $moveid, $direction);

    foreach ($seq as $i => $code) {
        if ($code->seq != $i) {
            $code->seq = $i;
            update_record('programming_tests', $code);
        }
    }
}
?>
