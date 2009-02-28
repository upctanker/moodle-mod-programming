<div class="maincontent generalbox">

<h1 class="name"><?php echo $programming->name; ?></h1>

<?php if ($programming->showmode == PROGRAMMING_SHOWMODE_NORMAL):?>
<div class="grade"><?php echo get_string('grade').': '.$programming->grade; ?> / <?php echo get_string('discount', 'programming').': '.$programming->discount/10.0; ?></div>
<?php else: ?>
<div>
<?php echo get_string('timelimit', 'programming'); ?>:
<?php echo programming_format_timelimit($programming->timelimit); ?>
&nbsp;
<?php echo get_string('memlimit', 'programming'); ?>:
<?php echo programming_format_memlimit($programming->memlimit); ?>
</div>
<?php endif; ?>

<div id="description">
<?php echo format_text($programming->description, $programming->descformat); ?>
</div>

<?php if ($programming->presetcode) : ?>
<div id="presetcode">
<h2><?php echo get_string('presetcode', 'programming'); ?></h2>
<textarea rows="20" cols="60" name="code" class="c#" id="code"><?php echo htmlspecialchars(programming_format_code($programming)); ?></textarea>
</div>
<?php endif; ?>

<?php if ($viewpubtestcase && $programming->showmode == PROGRAMMING_SHOWMODE_NORMAL && count($pubtests) > 0): ?>
<div id="testcase-table">
<table class="generaltable">
  <tr>
    <th>&nbsp;</th>
	<th><?php echo get_string('input', 'programming'); helpbutton('input', 'input', 'programming'); ?></th>
	<th><?php echo get_string('expectedoutput', 'programming'); helpbutton('expectedoutput', 'expectedoutput', 'programming'); ?></th>
	<th><?php echo get_string('timelimit', 'programming'); helpbutton('timelimit', 'timelimit', 'programming'); ?></th>
	<th><?php echo get_string('memlimit', 'programming'); helpbutton('memlimit', 'memlimit', 'programming'); ?></th>
  </tr>

<?php
  $i = 0; $ioid = 0;
  foreach ($pubtests as $programmingtest):
?>
  <tr>
    <th><?php echo get_string('testcasen', 'programming', $i++); ?></th>
    <td class="programming-io cell">
    <?php echo "<a href='download_io.php?a={$programming->id}&amp;test={$programmingtest->id}&amp;type=in&amp;download=0' class='showasplaintext small'>$strshowasplaintext</a>"; ?>
	  <?php echo programming_format_io($programmingtest->input, true); ?>
	</td>
	<td class="programming-io cell">
    <?php echo "<a href='download_io.php?a={$programming->id}&amp;test={$programmingtest->id}&amp;type=out&amp;download=0' class='showasplaintext small'>$strshowasplaintext</a>"; ?>
	  <?php echo programming_format_io($programmingtest->output, true); ?>
	</td>
    <td class="cell">
	  <?php echo programming_format_timelimit($programmingtest->timelimit); ?>
	</td>
    <td class="cell">
	  <?php echo programming_format_memlimit($programmingtest->memlimit); ?>
	</td>
  </tr>
<?php endforeach; ?>
</table>
</div>
<?php endif; ?>

<?php if ($programming->showmode == PROGRAMMING_SHOWMODE_NORMAL):?>
<div id="time-table">
<table class="generaltable">
  <tr>
    <th><?php echo get_string('timeopen', 'programming'); ?></th>
    <td class="cell"><?php echo userdate($programming->timeopen); ?></td>
  </tr>
  <tr>
    <th><?php echo get_string('timediscount', 'programming'); ?></th>
    <td class="cell"><?php echo userdate($programming->timediscount); ?></td>
  </tr>
  <tr>
    <th><?php echo get_string('timeclose', 'programming'); ?></th>
    <td class="cell"><?php echo userdate($programming->timeclose); ?></td>
  </tr>
</table>

<p align="center">
<?php echo get_string('allowlate', 'programming'); ?>:
<?php echo get_string($programming->allowlate ? 'yes' : 'no'); ?>
</p>

</div>
<?php endif; ?>
</div>
