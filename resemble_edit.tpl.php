<?php

/// Print the page header
    $pagename = get_string('resemble', 'programming');
    include_once('pageheader.php');

/// Print tabs
    $currenttab = 'resemble';
	$currenttab2 = 'resemble-edit';
    include_once('tabs.php');
?>
<div class="maincontent generalbox">
<?php
    if (is_array($resemble) && count($resemble)):
    print_paging_bar($totalcount, $page, $perpage, 'resemble_edit.php?a='.$programming->id.'&amp;');
?>
<form action="resemble_edit.php" method="post" id="resemble_editform">
<input type="hidden" name="a" value="<?php echo $programming->id; ?>" />
<input type="hidden" name="page" value="<?php echo $page; ?>" />
<input type="hidden" name="perpage" value="<?php echo $perpage; ?>" />
<input type="hidden" name="action" value="" />
<table class="resemble-list generaltable">
<tbody>
  <tr>
    <th><input type="checkbox" onchange="$('input[@type=checkbox]').attr('checked', this.checked);"/></th>
    <th colspan="2"><?php echo get_string('program1', 'programming'); ?></th>
    <th><?php echo get_string('percent1', 'programming'); ?></th>
    <th colspan="2"><?php echo get_string('program2', 'programming'); ?></th>
    <th><?php echo get_string('percent2', 'programming'); ?></th>
    <th><?php echo get_string('matchedlines', 'programming'); ?></th>
  </tr>

<?php
    foreach ($resemble as $r):
        switch($r->flag) {
        case PROGRAMMING_RESEMBLE_WARNED:
            $styleclass = 'warned cell';
            break;
        case PROGRAMMING_RESEMBLE_CONFIRMED:
            $styleclass = 'confirmed cell';
            break;
        default:
            $styleclass = 'cell';
      }
?>
  <tr>
	<td class="<?php echo $styleclass; ?>"><input type="checkbox" name="rids[]" value="<?php echo $r->id ?>" /></td>
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
	<td class="<?php echo $styleclass; ?>"><a href="resemble_compare.php?a=<?php echo $programming->id ?>&amp;rid=<?php echo $r->id ?>&amp;page=<?php echo $page; ?>&amp;perpage=<?php echo $perpage; ?>"><?php echo $r->matchedcount; ?></a></td>
  </tr>
<?php endforeach; ?>
</tbody>
</table>
<p>
<input type="submit" name="highsimilitude" value="<?php echo get_string('highsimilitude', 'programming'); ?>" onclick="this.form.elements['action'].value = 'confirm'" />
<input type="submit" name="mediumsimilitude" value="<?php echo get_string('mediumsimilitude', 'programming'); ?>" onclick="this.form.elements['action'].value = 'warn'"/>
<input type="submit" name="lowsimilitude" value="<?php echo get_string('lowsimilitude', 'programming'); ?>" onclick="this.form.elements['action'].value = 'reset'"/>
<input type="submit" name="delete" value="<?php echo get_string('delete'); ?>" onclick="this.form.elements['action'].value = 'delete'"/>
</p>
</form>
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
