<?php

/// Print the page header
    $pagename = get_string('resemble', 'programming');
    include_once('pageheader.php');

/// Print tabs
    $currenttab = 'resemble';
	$currenttab2 = 'resemble-view';
    include_once('tabs.php');
?>
<div class="maincontent generalbox">
<?php
    if (is_array($resemble) && count($resemble)):
?>
<table class="resemble-list generaltable">
<tbody>
  <tr>
    <th><?php echo get_string('similitudedegree', 'programming'); ?></th>
    <th colspan="2"><?php echo get_string('program1', 'programming'); ?></th>
    <th><?php echo get_string('percent1', 'programming'); ?></th>
    <th colspan="2"><?php echo get_string('program2', 'programming'); ?></th>
    <th><?php echo get_string('percent2', 'programming'); ?></th>
    <th><?php echo get_string('matchedlines', 'programming'); ?></th>
  </tr>

<?php
    $mediumdegree = get_string('mediumsimilitude', 'programming');
    $highdegree = get_string('highsimilitude', 'programming');
    foreach ($resemble as $r):
        switch($r->flag) {
        case PROGRAMMING_RESEMBLE_WARNED:
            $styleclass = 'warned cell';
            $degree = $mediumdegree;
            break;
        case PROGRAMMING_RESEMBLE_CONFIRMED:
            $styleclass = 'confirmed cell';
            $degree = $highdegree;
            break;
        default:
            $styleclass = 'cell';
      }
?>
  <tr>
	<td class="<?php echo $styleclass; ?>"><?php echo $degree; ?></td>
	<td class="<?php echo $styleclass; ?>">
        <?php print_user_picture($r->userid1, $course->id, $users[$r->userid1]->picture); ?>
    </td>
	<td class="<?php echo $styleclass; ?>">
	    <?php echo '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$r->userid1.'&amp;course='.$course->id.'">'.fullname($users[$r->userid1]).'</a>'; ?>
	</td>
	<td class="<?php echo $styleclass; ?>"><?php echo $r->percent1; ?></td>
	<td class="<?php echo $styleclass; ?>">
        <?php print_user_picture($r->userid2, $course->id, $users[$r->userid2]->picture); ?>
    </td>
	<td class="<?php echo $styleclass; ?>">
        <?php echo '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$r->userid2.'&amp;course='.$course->id.'">'.fullname($users[$r->userid2]).'</a>'; ?>
    </td>
	<td class="<?php echo $styleclass; ?>"><?php echo $r->percent2; ?></td>
	<td class="<?php echo $styleclass; ?>"><a href="resemble_compare.php?a=<?php echo $programming->id ?>&amp;rid=<?php echo $r->id ?>"><?php echo $r->matchedcount; ?></a></td>
  </tr>
<?php endforeach; ?>
</tbody>
</table>
<?php
    else:
        echo '<p>'.get_string('noresembleinfo', 'programming').'</p>';
    endif;
?>
</div>

<?php
/// Finish the page
    print_footer($course);
?>
