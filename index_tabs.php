<?php

    if (empty($currenttab) or empty($course)) {
        error('You cannot call this script in that way');
    }

    $tabs = array();
    $inactive = NULL;

    $row = array();
    $row[] = new tabobject('result', "$CFG->wwwroot/mod/programming/index.php?id=$id", get_string('result','programming'), '', true);
    $row[] = new tabobject('resemble', "$CFG->wwwroot/mod/programming/index_resemble.php?id=$id", get_string('resemble','programming'), '', true);
    $tabs[] = $row;

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
