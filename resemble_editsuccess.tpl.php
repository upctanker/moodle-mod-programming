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
<p><?php echo get_string('resembleeditsucceeded', 'programming'); ?></p>
<?php print_single_button('resemble_edit.php', array('a' => $programming->id, 'page' => $page, 'perpage' => $perpage), get_string('continue')); ?>
</div>
<?php
/// Finish the page
    print_footer($course);
?>
