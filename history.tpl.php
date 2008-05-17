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
<div id="submittime">
<?php echo get_string('submittime', 'programming'); ?>
<div id="submits_form"><form method="get" action="history.php">
<input type="hidden" name="a" value="<?php echo $programming->id ?>" />

<div id="submits_list">
<select name="submitid" size="10" id="submitid">
<?php
  $currentsubmit = null;
  foreach($submits as $submit) {
      if (!$currentsubmit) {
          $currentsubmit = $submit;
          if (!$submitid) $submitid = $submit->id;
      }
      echo '<option value="'.$submit->id.'"';
      echo $submitid == $submit->id ? ' selected="selected"' : '';
      echo '>';
      echo userdate($submit->timemodified, '%Y-%m-%d %H:%M:%S');
      echo '</option>'."\n";
      if ($submitid == $submit->id) {
          $currentsubmit = $submit;
      }
  }
?>
</select></div>

<div align="center" id="submit_button">
<input type="submit" name="action" value="<?php echo get_string('show') ?>" />
</div>

</form></div>
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
