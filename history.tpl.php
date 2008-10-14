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
<div id="submitlist">
<?php echo get_string('submittime', 'programming'); ?>
<ul>
  <?php
    foreach($submits as $submit):
      if (!$currentsubmit || $submit->id == $submitid)
        $currentsubmit = $submit;
  ?>
  <li><a href="<?php echo $CFG->wwwroot; ?>/mod/programming/history.php?a=<?php echo $a; ?>&amp;submitid=<?php echo $submit->id; ?>" class="submit" submitid="<?php echo $submit->id; ?>"><?php echo userdate($submit->timemodified, '%Y-%m-%d %H:%M:%S'); ?></a></li>
  <?php endforeach; ?>
</ul>
</div>
</td>

<td>
<div id="codeview">
<textarea rows="20" cols="60" name="code" class="c#" id="code"><?php echo htmlentities($currentsubmit->code) ?></textarea>
</div>
</td>
</tr>
</table>

<table><tr>
<td><form action="print_preview.php" method="get"><input type="hidden" name="print_preview_submit_id" id="print_preview_submit_id" value="<?php echo $currentsubmit->id; ?>"/><input type="submit" value="<?php echo get_string('printpreview', 'programming'); ?>"/></form></td>
<td><form action="print.php" method="get"><input type="hidden" name="print_submit_id" id="print_submit_id" value="<?php echo $currentsubmit->id; ?>"/><input type="submit" value="<?php echo get_string('print', 'programming'); ?>"/></form></td>
</tr></table>
<?php else: ?>
<?php echo get_string('cannotfindyoursubmit', 'programming'); ?>
<?php endif; ?>

</div>
