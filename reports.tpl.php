<?php
/// Print the page header
    $pagename = get_string('reports', 'programming');
    include_once('pageheader.php');

/// Print tabs
    $currenttab = 'reports';
    $currenttab2 = 'summary';
    include_once('tabs.php');

/// Print the main part of the page
?>
<div class="maincontent generalbox">
<h1><?php echo get_string('summary', 'programming'); ?></h1>
<table class="generaltable">
<tr>
  <th><?php echo get_string('statrange', 'programming'); ?></th>
  <th><?php echo get_string('statstudentcount', 'programming', $course->students); ?></th>
  <th colspan="2"><?php echo get_string('statsubmitcount', 'programming'); ?></th>
  <th colspan="2"><?php echo get_string('statcompiledcount', 'programming'); ?></th>
  <th colspan="2"><?php echo get_string('statpassedcount', 'programming'); ?></th>
  <th colspan="2"><?php echo get_string('statintimepassedcount', 'programming'); ?></th>
  <th><?php echo get_string('stataveragelines', 'programming'); ?></th>
</tr>
<?php foreach ($stat_results as $row): ?>
<tr>
  <th><?php echo $row['name']; ?></th>
  <td class="cell"><?php echo $row['studentcount']; ?></td>
  <td class="cell"><?php echo $row['submitcount']; ?></td>
  <td class="cell"><?php echo $row['studentcount'] > 0 ? round($row['submitcount'] / $row['studentcount'] * 100, 0) : 0; ?>%</td>
  <td class="cell"><?php echo $row['compiledcount']; ?></td>
  <td class="cell"><?php echo $row['studentcount'] > 0 ? round($row['compiledcount'] / $row['studentcount'] * 100, 0) : 0; ?>%</td>
  <td class="cell"><?php echo $row['passedcount']; ?></td>
  <td class="cell"><?php echo $row['studentcount'] > 0 ? round($row['passedcount'] / $row['studentcount'] * 100, 0) : 0; ?>%</td>
  <td class="cell"><?php echo $row['intimepassedcount']; ?></td>
  <td class="cell"><?php echo $row['studentcount'] > 0 ? round($row['intimepassedcount'] / $row['studentcount'] * 100, 0) : 0; ?>%</td>
  <td class="cell"><?php echo $row['submitcount'] > 0 ? round($row['totallines'] / $row['submitcount'], 2) : 0; ?></td>
</tr>
<?php endforeach; ?>
</table>

<table class="generaltable">
<?php if (has_capability('mod/programming:viewotherprogram', $context)): ?>
<tr>
<td>
<?php print_single_button('package.php', array('a' => $programming->id), get_string('package', 'programming')); ?>
</td>
<td>
<?php echo get_string('packagedesc', 'programming'); ?>
</td>
</tr>
<tr>
<?php endif; ?>
<?php if (has_capability('mod/programming:edittestcase', $context)): ?>
<td>
<?php print_single_button('retest.php', array('a' => $programming->id), get_string('retest', 'programming')); ?>
</td>
<td>
<?php echo get_string('retestdesc', 'programming'); ?>
</td>
</tr>
<?php endif; ?>
</table>
</div>
<?php
/// Finish the page
    print_footer($course);
?>
