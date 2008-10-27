<div class="maincontent generalbox">

<h1><?php
  if ($submit && $submit->userid != $USER->id) {
    $u = get_record('user', 'id', $submit->userid);
    echo get_string('viewtestresultof', 'programming', fullname($u));
  } else {
    echo get_string('viewtestresult', 'programming');
  }
?></h1>

<?php if ($submit): ?>
<div id="status">
<p id="desc"><?php echo get_string('currentstatus', 'programming') ?>: <?php echo programming_get_submit_status_desc($submit); ?></p>
</div>

<?php if ($submit->compilemessage != ''): ?>
<p><?php echo get_string('compilemessage', 'programming'); ?></p>
<div id="compilemessage">
<?php echo programming_format_compile_message($submit->compilemessage); ?>
</div>
<?php endif; ?>

<?php if ($submit->status == PROGRAMMING_STATUS_FINISH): ?>
<div id="test-result-summary">
<!--
<p align="center"><a href="result.php" title="<?php echo get_string('clickshowdetail', 'programming') ?>php"><?php echo get_string('testresult', 'programming')?>: <?php  ?></a></p>
-->
</div>

<?php if ($submit->userid == $USER->id || has_capability('mod/programming:viewotherresult', $context)): ?>
<?php if ($programming->showmode == PROGRAMMING_SHOWMODE_NORMAL): ?>
<div id="test-result-detail">
<p><?php echo get_string('testresult', 'programming'); ?>: <?php echo programming_get_test_results_desc($submit, $results) ?></p>
<p><?php echo get_string('iostripped', 'programming', '1'); ?></p>
<table class="generaltable">
  <tr>
    <th><?php echo get_string('testcasenumber', 'programming'); ?></th>
    <th><?php echo get_string('weight', 'programming'); helpbutton('weight', 'weight', 'programming'); ?></th>
    <th><?php echo get_string('timelimit', 'programming'); helpbutton('timelimit', 'timelimit', 'programming'); ?></th>
    <th><?php echo get_string('memlimit', 'programming'); helpbutton('memlimit', 'memlimit', 'programming'); ?></th>
    <th><?php echo get_string('input', 'programming'); helpbutton('input', 'input', 'programming'); ?></th>
    <th><?php echo get_string('expectedoutput', 'programming'); helpbutton('expectedoutput', 'expectedoutput', 'programming'); ?></th>
    <th><?php echo get_string('output', 'programming'); helpbutton('output', 'output', 'programming'); ?></th>
    <th><?php echo get_string('errormessage', 'programming'); helpbutton('stderr', 'stderr', 'programming'); ?></th>
    <th><?php echo get_string('timeused', 'programming'); helpbutton('timeused', 'timeused', 'programming'); ?></th>
    <th><?php echo get_string('exitcode', 'programming'); helpbutton('exitcode', 'exitcode', 'programming'); ?></th>
    <th><?php echo get_string('passed', 'programming'); ?></th>
    <th><?php echo get_string('judgeresult', 'programming'); helpbutton('judgeresult', 'judgeresult', 'programming'); ?></th>
  </tr>

<?php
  $i = 0;
  $id = 0;
  foreach ($results as $result):
?>
  <tr class="<?php echo $result->passed ? 'passed' : 'notpassed' ?>">
    <th><?php echo $i++ ?></th>
    <td class="cell"><?php echo $tests[$result->testid]->weight; ?></td>
    <td class="cell"><?php echo programming_format_timelimit($tests[$result->testid]->timelimit); ?></td>
    <td class="cell"><?php echo programming_format_memlimit($tests[$result->testid]->memlimit); ?></td>
<?php
  if ($viewhiddentestcase || 
      $tests[$result->testid]->pub == PROGRAMMING_TEST_SHOW ||
      $tests[$result->testid]->pub == PROGRAMMING_TEST_SHOWINRESULT ||
      ($tests[$result->testid]->pub == PROGRAMMING_TEST_SHOWAFTERDISCOUNT &&
       $programming->timediscount <= time())) {
    echo '<td class="programming-io cell">';

    echo "<a href='download_io.php?a={$programming->id}&amp;test={$result->testid}&amp;type=in&amp;download=0' class='showasplaintext small'>$strshowasplaintext</a>";

    echo '&nbsp;';

    echo "<a href='download_io.php?a={$programming->id}&amp;test={$result->testid}&amp;type=in' class='download small'>$strdownload</a>";

    echo programming_format_io($tests[$result->testid]->input, true);
    echo '</td>';

  } else {
    echo '<td>';
    echo get_string('securetestcase', 'programming');
    echo '</td>';
  }
?>

<?php
  if ($tests[$result->testid]->pub >= 0 || $viewhiddentestcase) {
    echo '<td class="programming-io cell">';

    echo "<a href='download_io.php?a={$programming->id}&amp;test={$result->testid}&amp;type=out&amp;download=0' class='showasplaintext small'>$strshowasplaintext</a>";

    echo '&nbsp;';

    echo "<a href='download_io.php?a={$programming->id}&amp;test={$result->testid}&amp;type=out' class='download small'>$strdownload</a>";

    echo programming_format_io($tests[$result->testid]->output, true);
    echo '</td>';
  } else {
    echo '<td class="cell">';
    echo get_string('securetestcase', 'programming');
    echo '</td>';
  }
?>

<?php
  if ($tests[$result->testid]->pub >= 0 || $viewhiddentestcase) {
    if ($result->output != '') {
      echo '<td class="programming-io cell">';

      echo "<a href='download_io.php?a={$programming->id}&amp;test={$result->testid}&amp;submit={$result->submitid}&amp;type=out&amp;download=0' class='showasplaintext small'>$strshowasplaintext</a>";

      echo '&nbsp;';

      echo "<a href='download_io.php?a={$programming->id}&amp;test={$result->testid}&amp;submit={$result->submitid}&amp;type=out' class='download small'>$strdownload</a>";

      echo programming_format_io($result->output, false);
    } else {
	  echo '<td class="cell">';
      echo get_string('noresult', 'programming');
    }
  } else {
    echo '<td class="cell">';
    echo get_string('securetestcase', 'programming');
  }
  echo '</td>';
?>

<?php
  if ($tests[$result->testid]->pub >= 0 || $viewhiddentestcase) {
    if ($result->stderr) {
      echo '<td class="programming-io cell">';

      echo "<a href='download_io.php?a={$programming->id}&amp;test={$result->testid}&amp;submit={$result->submitid}&amp;type=err&amp;download=0' class='showasplaintext small'>$strshowasplaintext</a>";

      echo '&nbsp;';

      echo "<a href='download_io.php?a={$programming->id}&amp;test={$result->testid}&amp;submit={$result->submitid}&amp;type=err' class='download small'>$strdownload</a>";

      echo programming_format_io($result->stderr, false);
    } else {
      echo '<td class="cell">';
      echo get_string('n/a', 'programming');
    }
  } else {
    echo '<td class="cell">';
    echo get_string('securetestcase', 'programming');
  }
?>
    </td>
    <td class="cell"><?php echo round($result->timeused, 3) ?></td>
    <td class="cell">
<?php
  if ($tests[$result->testid]->pub >= 0 || $viewhiddentestcase) {
    echo $result->exitcode;
  } else {
    echo get_string('securetestcase', 'programming');
  }
?>
    </td>
    <td class="cell">
<?php
  if ($result->passed) {
    echo '<div class="passed">'.get_string('yes').'</div>';
  } else {
    echo '<div class="passed">'.get_string('no').'</div>';
  }
?>
    </td>
    <td class="cell"><?php echo programming_get_judgeresult($result); ?></td>
  </tr>
<?php endforeach; ?>

</table>
</div>
<?php else: /* showmode == NORMAL */ ?>
<?php
    echo programming_contest_get_judgeresult($results);
?>
<?php endif; /* showmode == NORMAL */ ?>

<?php endif; ?>

<?php endif; /* status == FINISH */ ?>

<?php if ($submitid): ?>
<p align="center"><a href="history.php?a=<?php echo $programming->id ?>&amp;userid=<?php echo $submit->userid; ?>"><?php echo get_string('viewprogram', 'programming'); ?></a></p>
<?php else: ?>
<p align="center"><a href="history.php?a=<?php echo $programming->id ?>"><?php echo get_string('viewprogram', 'programming'); ?></a></p>
<?php endif; ?>
<?php else: ?>
<p align="center"><?php echo get_string('cannotfindyoursubmit', 'programming'); ?></p>
<?php endif; ?>
</div>

