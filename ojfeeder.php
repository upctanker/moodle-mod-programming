<?php

require_once('../../config.php');
require_once('lib/xmlrpc.inc');
require_once('lib/xmlrpcs.inc');
require_once('lib.php');

@session_unset();
@session_destroy();

function get_judge_id($xmlrpcmsg) {
    $ip = explode('.', getremoteaddr()); 
    $id = ($ip[0] << 24) | ($ip[1] << 16) | ($ip[2] << 8) | $ip[3];
    return new xmlrpcresp(new xmlrpcval($id, 'int'));
}

function reset_submits($xmlrpcmsg) {
    global $CFG;

    $judgeid = $xmlrpcmsg->getParam(0)->scalarVal();

    $sql = "UPDATE {$CFG->prefix}programming_testers SET testerid = 0
            WHERE testerid = {$judgeid}";
    execute_sql($sql, false);

    return new xmlrpcresp(new xmlrpcval(null, 'null'));
}

function get_submits($xmlrpcmsg) {
    global $CFG;

    $judgeid = $xmlrpcmsg->getParam(0)->scalarVal();
    $limit = $xmlrpcmsg->getParam(1)->scalarVal();

    $rs = get_records('programming_languages');
    $languages = array();
    foreach ($rs as $id => $r) {
        $languages[$r->id] = $r->name;
    }

    // Update the testers table and mark some record for this tester
    $sql = "UPDATE {$CFG->prefix}programming_testers
               SET testerid = {$judgeid}
             WHERE state = 0 AND testerid = 0
          ORDER BY priority, submitid
             LIMIT $limit";
    execute_sql($sql, false);

    // Find marked records
    $sql = "SELECT ps.*, pt.*
              FROM {$CFG->prefix}programming_submits AS ps,
                   {$CFG->prefix}programming_testers AS pt,
                   {$CFG->prefix}programming AS p
             WHERE ps.id = pt.submitid
               AND ps.programmingid = p.id
               AND pt.testerid = {$judgeid}
               AND pt.state = 0
          ORDER BY pt.priority, pt.submitid";
    $rs = get_records_sql($sql);
    $retval = array(); 
    if (is_array($rs)) {
        $ids = array();
        foreach ($rs as $id => $submit) {
            $code = programming_format_code($submit->programmingid, $submit, true);
            $r = array(
                'id' => new xmlrpcval(sprintf('%010d', $submit->id), 'string'),
                'problem_id' => new xmlrpcval(
                    sprintf('%010d', $submit->programmingid), 'string'),
                'language' => new xmlrpcval($languages[$submit->language],
                                            'string'),
                'code' => new xmlrpcval($code, 'base64'),
            );
            $retval[] = new xmlrpcval($r, 'struct');
            $ids[] = $submit->id;
        }

        // Update state of the records and prevent them
        // from judged multiple times on one judge daemon
        $ids = implode(',', $ids);
        $sql = "UPDATE {$CFG->prefix}programming_testers
                   SET state = 1
                 WHERE submitid in ($ids)";
        execute_sql($sql, false);
    }
    
    return new xmlrpcresp(new xmlrpcval($retval, 'array'));
}

function update_submit_compilemessage($xmlrpcmsg) {
    global $CFG;

    $id = $xmlrpcmsg->getParam(0)->scalarVal();
    $message = addslashes($xmlrpcmsg->getParam(1)->scalarVal());

    $sql = "UPDATE {$CFG->prefix}programming_submits
               SET compilemessage='{$message}' WHERE id = {$id}";
    execute_sql($sql, false);
    if ($CFG->rcache === true) {
        rcache_unset('programming_submits', (int) $id);
    }

    return new xmlrpcresp(new xmlrpcval(null, 'null'));
}

function update_submit_status($xmlrpcmsg) {
    global $CFG;

    $id = $xmlrpcmsg->getParam(0)->scalarVal();
    $status = $xmlrpcmsg->getParam(1)->scalarVal();

    switch ($status) {
    case 'waiting':
    $s = PROGRAMMING_STATUS_WAITING;
    break;
    case 'compiling':
        $s = PROGRAMMING_STATUS_COMPILING;
        break;
    case 'compile_success':
        $s = PROGRAMMING_STATUS_COMPILEOK;
        break;
    case 'compile_failed':
        $s = PROGRAMMING_STATUS_COMPILEFAIL;
        break;
    case 'running':
        $s = PROGRAMMING_STATUS_RUNNING;
        break;
    case 'finish':
        $s = PROGRAMMING_STATUS_FINISH;
        break;
    default:
        return new xmlrpcresp(new xmlrpcval(null, 'null'));
    }

    if ($status == 'finish' || $status == 'compile_failed') {
        $sql = "DELETE FROM {$CFG->prefix}programming_testers
                 WHERE submitid={$id}";
        execute_sql($sql, false);
        $setce = '';
        if ($status == 'compile_failed')
            $setce = ", judgeresult = 'CE'";
        $sql = "UPDATE {$CFG->prefix}programming_submits
                   SET `status`={$s} $setce
                 WHERE id={$id}";
        execute_sql($sql, false);
        if ($CFG->rcache === true) {
            rcache_unset('programming_submits', (int) $id);
        }
    }

    return new xmlrpcresp(new xmlrpcval(null, 'null'));
}

function get_problem($xmlrpcmsg)
{
    $id = $xmlrpcmsg->getParam(0)->scalarVal();
    $p = get_record('programming', 'id', $id);
    switch ($p->validatortype) {
    case 1:
        $vtype = 'python';
        break;
    default:
        $vtype = 'internal';
    }
    $ret = new xmlrpcval(array(
            'id' => new xmlrpcval(sprintf('%010d', $p->id), 'string'),
            'timemodified' => new xmlrpcval($p->timemodified, 'int'),
            'input_filename' => new xmlrpcval($p->inputfile, 'string'),
            'output_filename' => new xmlrpcval($p->outputfile, 'string'),
            'validator_code' => new xmlrpcval($p->validator, 'base64'),
            'validator_type' => new xmlrpcval($vtype, 'string'),
            'generator_code' =>  new xmlrpcval('', 'base64'),
            'generator_type' => new xmlrpcval('', 'string'),
            'standard_code' => new xmlrpcval('', 'string'),
        ), 'struct');
    return new xmlrpcresp($ret);
}

function get_tests($xmlrpcmsg)
{
    $id = $xmlrpcmsg->getParam(0)->scalarVal();
    $full = $xmlrpcmsg->getParam(1)->scalarVal();

    $tests = array();
    $rs = get_records('programming_tests', 'programmingid', $id);
    if (is_array($rs)) {
        foreach ($rs as $rid => $r) {
            if ($full) {
                if (!empty($r->gzinput)) $r->input = bzdecompress($r->gzinput);
                if (!empty($r->gzoutput)) $r->output = bzdecompress($r->gzoutput);
            }
            $r = new xmlrpcval(array(
                'id' => new xmlrpcval(sprintf('%010d', $r->id), 'string'),
                'problem_id' => new xmlrpcval(
                    sprintf('%010d', $r->programmingid), 'string'),
                'timemodified' => new xmlrpcval($r->timemodified, 'int'),
                'input' => new xmlrpcval($full ? $r->input : '', 'base64'),
                'output' => new xmlrpcval($full ? $r->output : '', 'base64'),
                'timelimit' => new xmlrpcval($r->timelimit, 'int'),
                'memlimit' => new xmlrpcval($r->memlimit, 'int'),
            ), 'struct');
            $tests[] = $r;
        }
    }
    return new xmlrpcresp(new xmlrpcval($tests, 'array'));
}

function get_datafiles($xmlrpcmsg)
{
    $programmingid = $xmlrpcmsg->getParam(0)->scalarVal();

    $files = array();
    $rs = get_records('programming_datafile', 'programmingid', $programmingid, 'seq', 'id, filename, isbinary, timemodified');
    if (is_array($rs)) {
        foreach ($rs as $rid => $r) {
            $r = new xmlrpcval(array(
                'id' => new xmlrpcval(sprintf('%010d', $r->id), 'string'),
                'problem_id' => new xmlrpcval(sprintf('%010d', $programmingid), 'string'),
                'filename' => new xmlrpcval($r->filename, 'string'),
                'type' => new xmlrpcval($r->isbinary ? 'binary' : 'text', 'string'),
                'timemodified' => new xmlrpcval($r->timemodified, 'int'),
            ), 'struct');
            $files[] = $r;
        }
    }

    return new xmlrpcresp(new xmlrpcval($files, 'array'));
}

function get_datafile_data($xmlrpcmsg)
{
    $datafileid = $xmlrpcmsg->getParam(0)->scalarVal();

    $datafile = get_record('programming_datafile', 'id', $datafileid);
    if (!empty($datafile)) {
        if (empty($datafile->checkdata)) {
            $r = new xmlrpcval($datafile->data, 'base64');
        } else {
            $r = new xmlrpcval($datafile->checkdata, 'base64');
        }
    } else {
        $r = new xmlrpcval('', 'base64');
    }

    return $r;
}

function get_presetcodes($xmlrpcmsg)
{
    $programmingid = $xmlrpcmsg->getParam(0)->scalarVal();
    $language = $xmlrpcmsg->getParam(1)->scalarVal();

    $codes = array();
    $lang = get_record('programming_languages', 'name', $language);
    $rs = get_records_select(
            'programming_presetcode',
            "programmingid={$programmingid} AND languageid={$lang->id}");
    if (is_array($rs)) {
        foreach ($rs as $rid => $r) {
            if ($r->name == '<prepend>' || $r->name == '<postpend>') {
                continue;
            }
            $code = empty($r->presetcodeforcheck) ?
                $r->presetcode : $r->presetcodeforcheck;
            $extname = substr($r->name, strrpos($r->name, '.'));
            $isheader = in_array($extname, explode(' ', $lang->headerext));
            $r = new xmlrpcval(array(
                'id' => new xmlrpcval(sprintf('%010d', $r->id), 'string'),
                'name' => new xmlrpcval($r->name, 'string'),
                'code' => new xmlrpcval($code, 'base64'),
                'isheader' => new xmlrpcval($isheader, 'boolean'),
            ), 'struct');
            $codes[] = $r;
        }
    }
    return new xmlrpcresp(new xmlrpcval($codes, 'array'));
}

function get_test($xmlrpcmsg)
{
    $id = $xmlrpcmsg->getParam(0)->scalarVal();

    $r = get_record('programming_tests', 'id', $id);
    if (!empty($r->gzinput)) $r->input = bzdecompress($r->gzinput);
    if (!empty($r->gzoutput)) $r->output = bzdecompress($r->gzoutput);
    $ret = new xmlrpcval(array(
            'id' => new xmlrpcval(sprintf('%010d', $r->id), 'string'),
            'problem_id' => new xmlrpcval(
                sprintf('%010d', $r->programmingid), 'string'),
            'timemodified' => new xmlrpcval(0, 'int'),
            'input' => new xmlrpcval($r->input, 'base64'),
            'output' => new xmlrpcval($r->output, 'base64'),
            'timelimit' => new xmlrpcval($r->timelimit, 'int'),
            'memlimit' => new xmlrpcval($r->memlimit, 'int'),
        ), 'struct');
    return new xmlrpcresp($ret);
}

function get_gztest($xmlrpcmsg)
{
    $id = $xmlrpcmsg->getParam(0)->scalarVal();

    $r = get_record('programming_tests', 'id', $id);
    if (empty($r->gzinput)) $r->gzinput = bzcompress($r->input);
    if (empty($r->gzoutput)) $r->gzoutput = bzcompress($r->output);
    $ret = new xmlrpcval(array(
            'id' => new xmlrpcval(sprintf('%010d', $r->id), 'string'),
            'problem_id' => new xmlrpcval(
                sprintf('%010d', $r->programmingid), 'string'),
            'timemodified' => new xmlrpcval(0, 'int'),
            'input' => new xmlrpcval($r->gzinput, 'base64'),
            'output' => new xmlrpcval($r->gzoutput, 'base64'),
            'timelimit' => new xmlrpcval($r->timelimit, 'int'),
            'memlimit' => new xmlrpcval($r->memlimit, 'int'),
        ), 'struct');
    return new xmlrpcresp($ret);
}

function update_submit_test_results($xmlrpcmsg)
{
    global $CFG;

    $sid = $xmlrpcmsg->getParam(0)->scalarVal();
    $results = $xmlrpcmsg->getParam(1);

    delete_records('programming_test_results', 'submitid', $sid);
    $s = get_record('programming_submits', 'id', $sid);

    $passed = 1;
    $oo = array();
    for ($i = 0; $i < $results->arraySize(); $i++) {
        $result = $results->arrayMem($i);

        $o = new stdClass;
        $o->submitid = $sid;
        $o->testid = $result->structMem('test_id')->scalarVal();
        $o->judgeresult= $result->structMem('judge_result')->scalarVal();
        $o->passed = $o->judgeresult == 'AC';
        $o->exitcode = $result->structMem('exitcode')->scalarVal();
        $o->signal = $result->structMem('signal')->scalarVal();
        $o->output = addslashes($result->structMem('stdout')->scalarVal());
        $o->stderr = addslashes($result->structMem('stderr')->scalarVal());
        $o->timeused = $result->structMem('timeused')->scalarVal();
        $o->memused = $result->structMem('memused')->scalarVal();
        insert_record('programming_test_results', $o);
        $oo[] = $o;
        if (!$o->passed) $passed = 0;
    }
    $timeused = programming_submit_timeused($oo);
    $memused = programming_submit_memused($oo);
    $judgeresult = programming_submit_judgeresult($oo);
    $sql = "UPDATE {$CFG->prefix}programming_submits
               SET timeused = {$timeused},
                    memused = {$memused},
                judgeresult = '{$judgeresult}',
                     passed = {$passed}
             WHERE id = {$sid}";
    execute_sql($sql, false);
    if ($CFG->rcache === true) {
        rcache_unset('programming_submits', (int) $sid);
    }

    # For moodle 1.9, update grade
    programming_update_grade($sid);

    return new xmlrpcresp(new xmlrpcval(null, 'null'));
}

$addr = getremoteaddr();
if (empty($CFG->programming_ojip) || !in_array($addr, explode(' ', $CFG->programming_ojip))) {
    header('HTTP/1.0 401 Unauthorized');
    echo '401 Unauthorized.';
    if (empty($CFG->programming_ojip)) {
        echo "<a href='{$CFG->wwwroot}/admin/module.php?module=programming'>Please setup OJ IP.</a>";
    }
    exit;
}

$s = new xmlrpc_server(
  array(
    'oj.get_judge_id' => array(
        'function' => 'get_judge_id',
        'signature' => array(array($xmlrpcInt)),
    ),
    'oj.reset_submits' => array(
        'function' => 'reset_submits',
        'signature' => array(array($xmlrpcNull, $xmlrpcInt)),
    ),
    'oj.get_submits' => array(
        'function' => 'get_submits',
        'signature' => array(array($xmlrpcArray, $xmlrpcInt, $xmlrpcInt))),
    'oj.get_tests' => array(
        'function' => 'get_tests',
        'signature' => array(array($xmlrpcArray, $xmlrpcString, $xmlrpcBoolean)),
    ),
    'oj.get_datafiles' => array(
        'function' => 'get_datafiles',
        'signature' => array(array($xmlrpcArray, $xmlrpcString)),
    ),
    'oj.get_datafile_data' => array(
        'function' => 'get_datafile_data',
        'signature' => array(array($xmlrpcBase64, $xmlrpcString)),
    ),
    'oj.get_presetcodes' => array(
        'function' => 'get_presetcodes',
        'signature' => array(array($xmlrpcArray, $xmlrpcString, $xmlrpcString)),
    ),
    'oj.get_test' => array(
        'function' => 'get_test',
        'signature' => array(array($xmlrpcStruct, $xmlrpcString)),
    ),
    'oj.get_gztest' => array(
        'function' => 'get_gztest',
        'signature' => array(array($xmlrpcStruct, $xmlrpcString)),
    ),
    'oj.get_problem' => array(
        'function' => 'get_problem',
        'signature' => array(array($xmlrpcStruct, $xmlrpcString)),
    ),
    'oj.update_submit_compilemessage' => array(
        'function' => 'update_submit_compilemessage',
        'signature' => array(array($xmlrpcNull, $xmlrpcString, $xmlrpcBase64)),
    ),
    'oj.update_submit_status' => array(
        'function' => 'update_submit_status',
        'signature' => array(array($xmlrpcNull, $xmlrpcString, $xmlrpcString)),
    ),
    'oj.update_submit_test_results' => array(
        'function' => 'update_submit_test_results',
        'signature' => array(array($xmlrpcNull, $xmlrpcString, $xmlrpcArray)),
    ),
  ));

?>
