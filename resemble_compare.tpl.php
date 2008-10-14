<?php

/// Print the page header
    $pagename = get_string('resemble', 'programming');
    include_once('pageheader.php');

/// Print tabs
    // No tabs for compare page.
    $currenttab = 'resemble';
	$currenttab2 = 'resemble-compare';
    include_once('tabs.php');

/// Print page content
?>

<div class="maincontent generalbox">

<div id="info">
<table class="generaltable">
  <tr>
    <th class="cell"><?php echo get_string('fullname'); ?></th>
    <td class="cell">
        <?php print_user_picture($user1->id, $course->id, $user1->picture); ?>
        <a href="<?php echo $CFG->wwwroot; ?>/user/view.php?id=<?php echo $user1->id; ?>&amp;course=<?php echo $course->id; ?>"><?php echo fullname($user1); ?></a>
    </td>
    <td class="cell">
        <?php print_user_picture($user2->id, $course->id, $user2->picture); ?>
        <a href="<?php echo $CFG->wwwroot; ?>/user/view.php?id=<?php echo $user2->id; ?>&amp;course=<?php echo $course->id; ?>"><?php echo fullname($user2); ?></a>
    </td>
  </tr>
  <tr>
    <th class="cell"><?php echo get_string('submittime', 'programming'); ?></th>
    <td class="cell"><?php echo userdate($submit1->timemodified); ?></td>
    <td class="cell"><?php echo userdate($submit2->timemodified); ?></td>
  </tr>
</table>
</div>

<div id="program">
<div id="submit1">
<?php
    foreach ($lines1 as $line) {
        if (is_array($line)) {
            $mid = $line[0];
            $line = $line[1];
            echo '<span class="code match'.$mid.'">';
        } else {
            echo '<span class="code">';
        }
        $line = htmlspecialchars($line);
        $line = str_replace(array(' ', "\r"), array('&nbsp;', ''), $line);
        echo $line;
        echo '</span><br />'."\n";
    }
?>
</div>

<div id="submit2">
<?php
    foreach ($lines2 as $line) {
        if (is_array($line)) {
            $mid = $line[0];
            $line = $line[1];
            echo '<span class="code match'.$mid.'">';
        } else {
            echo '<span class="code">';
        }
        $line = htmlspecialchars($line);
        $line = str_replace(array(' ', "\r"), array('&nbsp;', ''), $line);
        echo $line;
        echo '</span><br />'."\n";
    }
?>
</div>
</div>

<?php if (has_capability('mod/programming:editresemble', $context)): ?>
<form action="resemble_edit.php" method="post" id="resemble_editform">
<input type="hidden" name="a" value="<?php echo $programming->id; ?>" />
<input type="hidden" name="page" value="<?php echo $page; ?>" />
<?php if (isset($perpage)): ?>
<input type="hidden" name="perpage" value="<?php echo $perpage; ?>" />
<?php endif; ?>
<input type="hidden" name="action" value="" />
<input type="hidden" name="rids[]" value="<?php echo $resemble->id; ?>" />
<p>
<input type="submit" name="highsimilitude" value="<?php echo get_string('highsimilitude', 'programming'); ?>" onclick="this.form.elements['action'].value = 'confirm'" />
<input type="submit" name="mediumsimilitude" value="<?php echo get_string('mediumsimilitude', 'programming'); ?>" onclick="this.form.elements['action'].value = 'warn'"/>
<input type="submit" name="lowsimilitude" value="<?php echo get_string('lowsimilitude', 'programming'); ?>" onclick="this.form.elements['action'].value = 'reset'"/>
<input type="submit" name="delete" value="<?php echo get_string('delete'); ?>" onclick="this.form.elements['action'].value = 'delete'"/>
</p>
</form>
<?php endif; ?>
</div>
<?php
/// Finish the page
    print_footer($course);
?>
