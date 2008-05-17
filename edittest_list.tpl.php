<div class="maincontent generalbox">
<h1><?php echo get_string('testcase', 'programming'); helpbutton('testcase', 'testcase', 'programming'); ?></h1>

<?php if (is_array($testcases)): ?>
<table class="generaltable">
<tr>
<th>&nbsp;</th>
<th><?php echo get_string('public', 'programming'); helpbutton('testcasepub', 'helptestcasepub', 'programming'); ?></th>
<th><?php echo get_string('input', 'programming'); helpbutton('input', 'input', 'programming'); ?></th>
<th><?php echo get_string('expectedoutput', 'programming'); helpbutton('expectedoutput', 'expectedoutput', 'programming'); ?></th>
<th><?php echo get_string('timelimit', 'programming'); helpbutton('timelimit', 'timelimit', 'programming'); ?></th>
<th><?php echo get_string('memlimit', 'programming'); helpbutton('memlimit', 'memlimit', 'programming'); ?></th>
<th><?php echo get_string('weightsetting', 'programming'); helpbutton('weight', 'weight', 'programming'); ?></th>
<?php if (has_capability('mod/programming:edittestcase', $context)): ?>
<th><?php echo get_string('edit') ?></th>
<th><?php echo get_string('delete') ?></th>
<?php endif; ?>
</tr>

    <?php $i = $page * $perpage; $ioid = 0; foreach ($testcases as $programmingtest): ?>
<tr>
<th><?php echo get_string('testcasen', 'programming', $i++); ?></th>
<td class="cell"><?php $p = array(-1 => 'never', 0 => 'inresult', 1 => 'always'); echo get_string($p[$programmingtest->pub], 'programming') ?></td>

<?php if ($programmingtest->pub >= 0 || has_capability('mod/programming:viewhiddentestcase', $context)): ?>
<td class="cell programming-io">
<a href="javascript:showasplaintext($('#io<?php echo $ioid ?>'))" class="small"><?php echo get_string('showasplaintext', 'programming'); ?></a>
<?php echo '<a href="download_io.php?a='.$programming->id.'&test='.$programmingtest->id.'&amp;type=in" class="small">'.get_string('download', 'programming').'</a>'; ?>

<?php echo programming_format_io($programmingtest->input, 'io'.$ioid++) ?>
</td>
<td class="cell programming-io">
<a href="javascript:showasplaintext($('#io<?php echo $ioid ?>'))")" class="small"><?php echo get_string('showasplaintext', 'programming'); ?></a>
<?php echo '<a href="download_io.php?a='.$programming->id.'&test='.$programmingtest->id.'&amp;type=out" class="small">'.get_string('download', 'programming').'</a>'; ?>
<?php echo programming_format_io($programmingtest->output, 'io'.$ioid++) ?>
</td>
<?php else: ?>
<td class="cell"> <?php echo get_string('never', 'programming') ?> </td>
<td class="cell"> <?php echo get_string('never', 'programming') ?> </td>
<?php endif; ?>

<td class="cell"><?php echo get_string('nseconds', 'programming', $programmingtest->timelimit) ?></td>
<td class="cell"><?php echo get_string('nkb', 'programming', $programmingtest->memlimit) ?></td>
<td class="cell"><?php echo get_string('nweight', 'programming', $programmingtest->weight) ?></td>
      <?php if (has_capability('mod/programming:edittestcase', $context)): ?>
<td class="cell"><?php print_single_button('edittest_modify.php', array('a' => $programming->id, 'id' => $programmingtest->id), get_string('edit')) ?></td>
<td class="cell"><?php print_single_button('edittest_delete.php', array('a' => $programming->id, 'id' => $programmingtest->id), get_string('delete')) ?></td>
</tr>
      <?php endif; ?>
    <?php endforeach; ?>
</table>
<?php print_paging_bar($totalcount, $page, $perpage, $CFG->wwwroot.'/mod/programming/edittest_list.php?a='.$programming->id.'&amp;'); ?>
<?php else: ?>
<p><?php echo get_string('noanytests', 'programming') ?></p>
<?php endif; ?>


</div>
