<div class="maincontent generalbox">

<h1><?php
  if ($USER->id != $userid) {
    $u = get_record('user', 'id', $userid);
    echo get_string('viewsubmithistoryof', 'programming', fullname($u));
  } else {
    echo get_string('viewsubmithistory', 'programming');
  }
?></h1>

<?php if (!empty($submits)): ?>
<table>
<tr>
<td>
<form action="history_diff.php">
<input type="hidden" name="a" value="<?php echo $a; ?>" />
<div id="submitlist">
<?php echo get_string('submittime', 'programming'); ?>
<table>
  <?php
    $currentsubmit = NULL;
    foreach($submits as $submit):
      if (!$currentsubmit || $submit->id == $submitid)
        $currentsubmit = $submit;
  ?>
  <tr>
    <th><input type="radio" name="s1" value="<?php echo $submit->id; ?>" class="diff1" /></th>
    <th><input type="radio" name="s2" value="<?php echo $submit->id; ?>" class="diff2" /></th>
    <td><a href="<?php echo $CFG->wwwroot; ?>/mod/programming/history.php?a=<?php echo $a; ?>&amp;submitid=<?php echo $submit->id; ?>" class="submit" submitid="<?php echo $submit->id; ?>">
      <?php echo userdate($submit->timemodified, '%Y-%m-%d %H:%M:%S'); ?>
    </a></td>
  </tr>
  <?php endforeach; ?>
</table>
<input type="submit" value="<?php echo get_string('compare', 'programming'); ?>" />
</div>
</form>
</td>

<td>
<div id="codeview">
<textarea rows="20" cols="60" name="code" class="c#" id="code"><?php echo htmlspecialchars($currentsubmit->code) ?></textarea>
</div>
</td>
</tr>
</table>

<!--
<table><tr>
<td><form action="print_preview.php" method="get"><input type="hidden" name="print_preview_submit_id" id="print_preview_submit_id" value="<?php echo $currentsubmit->id; ?>"/><input type="submit" value="<?php echo get_string('printpreview', 'programming'); ?>"/></form></td>
<td><form action="print.php" method="get"><input type="hidden" name="print_submit_id" id="print_submit_id" value="<?php echo $currentsubmit->id; ?>"/><input type="submit" value="<?php echo get_string('print', 'programming'); ?>"/></form></td>
</tr></table>-->
<?php else: ?>
<?php echo get_string('cannotfindyoursubmit', 'programming'); ?>
<?php endif; ?>

</div>
