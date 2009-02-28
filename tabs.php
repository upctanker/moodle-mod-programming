<?php

    if (empty($currenttab) or empty($programming) or empty($course)) {
        error('You cannot call this script in that way');
    }

    $tabs = array();
    $inactive = NULL;

    $row = array();
    
    $row[] = new tabobject('view', $CFG->wwwroot.'/mod/programming/view.php?a='.$programming->id, get_string('view','programming'), '', true);

    $row[] = new tabobject('submit', $CFG->wwwroot.'/mod/programming/submit.php?a='.$programming->id, get_string('submit','programming'), '', true);

    $row[] = new tabobject('result', $CFG->wwwroot.'/mod/programming/result.php?a='.$programming->id, get_string('result','programming'), '', true);

    $row[] = new tabobject('history', $CFG->wwwroot.'/mod/programming/history.php?a='.$programming->id, get_string('submithistory','programming'), '', true);

    if (has_capability('mod/programming:edittestcase', $context)) {
        $row[] = new tabobject('edittest', $CFG->wwwroot.'/mod/programming/edittest_list.php?a='.$programming->id, get_string('testcase','programming'));
    }
    if (has_capability('mod/programming:viewreport', $context)) {
        $row[] = new tabobject('reports', $CFG->wwwroot.'/mod/programming/reports.php?a='.$programming->id, get_string('reports','programming'), '', true);
    }
    if (has_capability('mod/programming:viewresemble', $context)) {
        $row[] = new tabobject('resemble', $CFG->wwwroot.'/mod/programming/resemble_view.php?a='.$programming->id, get_string('resemble','programming'), '', true);
    }

    $tabs[] = $row;

    if ($currenttab == 'edittest' && has_capability('mod/programming:edittestcase', $context)) {
        $row = array();
        $inactive[] = 'edittest';
        $row[] = new tabobject('listtest', $CFG->wwwroot.'/mod/programming/edittest_list.php?a='.$programming->id, get_string('list','programming'));
        $row[] = new tabobject('addtest', $CFG->wwwroot.'/mod/programming/edittest_modify.php?a='.$programming->id, get_string('add'));
        $row[] = new tabobject('validator', $CFG->wwwroot.'/mod/programming/edittest_validator.php?a='.$programming->id, get_string('validator','programming'));
        $row[] = new tabobject('presetcode', $CFG->wwwroot.'/mod/programming/edittest_presetcode.php?a='.$programming->id, get_string('presetcode','programming'));
//      $row[] = new tabobject('generator', $CFG->wwwroot.'/mod/programming/edittest_generator.php?a='.$programming->id, get_string('generator','programming'));

        $tabs[] = $row;
    }

	if ($currenttab == 'reports') {
		$row = array();
		$inactive[] = 'reports';
		$row[] = new tabobject('reports-summary', $CFG->wwwroot.'/mod/programming/reports.php?a='.$programming->id, get_string('summary','programming'));
		$row[] = new tabobject('reports-detail', $CFG->wwwroot.'/mod/programming/reports_detail.php?a='.$programming->id.'&amp;latestonly=1', get_string('detail','programming'));
		$tabs[] = $row;
	}

	if ($currenttab == 'resemble') {
		$row = array();
		$inactive[] = 'resemble';
		$row[] = new tabobject('resemble-view', $CFG->wwwroot.'/mod/programming/resemble_view.php?a='.$programming->id, get_string('personal','programming'));
        if (has_capability('mod/programming:editresemble', $context)) {
    		$row[] = new tabobject('resemble-edit', $CFG->wwwroot.'/mod/programming/resemble_edit.php?a='.$programming->id, get_string('edit'));
        }
        if (has_capability('mod/programming:updateresemble', $context)) {
    		$row[] = new tabobject('resemble-analyze', $CFG->wwwroot.'/mod/programming/resemble_analyze.php?a='.$programming->id, get_string('analyze','programming'));
        }
		$tabs[] = $row;
	}

    /*****************************
    * stolen code from quiz report
    *****************************/
    if ($currenttab == 'templates' and isset($mode)) {
        $inactive[] = 'templates';
        $templatelist = array ('listtemplate', 'singletemplate', 'addtemplate', 'rsstemplate', 'csstemplate');

        $row  = array();
        $currenttab ='';
        foreach ($templatelist as $template) {
            $row[] = new tabobject($template, "templates.php?d=$data->id&amp;mode=$template",
                                    get_string($template, 'data'));
            if ($template == $mode) {
                $currenttab = $template;
            }
        }
        $tabs[] = $row;
    }
    

/// Print out the tabs and continue!

    if (!isguest()) {
        print_tabs($tabs, $currenttab, $inactive);
    }
    
?>
