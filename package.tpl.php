<?php
/// Print the page header
    $pagename = get_string('package', 'programming'); 
    include_once('pageheader.php');
?>

<div class="maincontent generalbox">

<p><?php echo get_string('packagesuccess', 'programming'); ?></p>

<p><a href="<?php echo $filelink; ?>"><?php echo get_string('download', 'programming'); ?></a></p>

<p><a href="<?php echo $referer; ?>"><?php echo get_string('return', 'programming'); ?></a></p>

</div>

<?php
/// Finish the page
    print_footer($course);
?>
