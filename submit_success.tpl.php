<div class="maincontent generalbox">
<h1><?php echo get_string('submitsuccess', 'programming'); ?></h1>
<div id="codeview">
<textarea name="code" class="c#" id="code" cols="60" rows="20"><?php echo htmlentities(stripslashes($code)); ?></textarea>
</div>
<p><a href="result.php?a=<?php echo $programming->id; ?>"><?php echo get_string('viewresults', 'programming'); ?></a></p>
</div>
