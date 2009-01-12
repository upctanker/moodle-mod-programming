<?php

    require_once('../../config.php');
    require_once('lib.php');
    require_once("../../lib/filelib.php");
    require_once('resemble_analyze_lib.php');

    $a = optional_param('a', 0, PARAM_INT);     // programming ID
    $group = optional_param('group', 0, PARAM_INT);

    $action = optional_param('action', 0, PARAM_CLEAN);
    $max = optional_param('max', 0, PARAM_INT);
    $lowest = optional_param('lowest', 0, PARAM_INT);
    
    //from package.php
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
    require_capability('mod/programming:updateresemble', $context);

    add_to_log($course->id, 'programming', 'resemble_analyze', me(), $programming->id);

/// Print the page header
    $pagename = get_string('resemble_analyze', 'programming');
    // cross-site xmlhttprequest is not allowed :(
    //$CFG->scripts[] = 'resemble_analyze.js';
    include_once('pageheader.php');

/// Print tabs
    $currenttab = 'resemble';
    $currenttab2 = 'resemble-analyze';
    include_once('tabs.php');

/// Print page content

    if ($action) {
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
      $exts = array('.txt', '.c', '.cxx', '.java', '.java', '.pas', '.py', '.cs');
      foreach ($latestsubmits as $submit) {
          $ext = $exts[$submit->language];
          $filename = $dirname.'/'.$users[$submit->userid]->idnumber.'-'.$submit->id.$ext;
          $files[] = $filename;
          $f = fopen($filename, 'w');
          fwrite($f, $submit->code);
          fwrite($f, "\r\n");
          fclose($f);
      }

      $cwd = getcwd();
      chdir($dirname);
      $url = exec("perl $cwd/moss.pl *");
      echo "See result $url";

      // remove temp
      fulldelete($dirname);

      parse_result($programming->id, $url, $max, $lowest);
    } else {
      include_once('resemble_analyze.tpl.php');
    }

/// Finish the page
    print_footer($course);

?>
