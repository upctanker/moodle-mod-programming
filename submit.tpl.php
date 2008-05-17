<div class="maincontent generalbox">
<h1><?php echo get_string('submit', 'programming'); helpbutton('submit', 'submit', 'programming'); ?></h1>

<?php if ($allowpost): ?>
<div id="submit">
<form method="post" enctype="multipart/form-data" action="submit.php">
<input type="hidden" name="a" value="<?php echo $programming->id ?>" />
<?php if ($submitfor): ?>
<input type="hidden" name="submitfor" value="<?php echo $submitfor ?>" />
<?php endif; ?>

<table>
<tr>
<td align="right">
<label for="programcode" class="name"><?php echo get_string('programcode', 'programming'); ?></label>
</td>
<td align="left">
<?php print_textarea(false, 10, 30, 0, 0, 'code'); ?>
</td>
</tr>
<tr>
<td align="right">
<label for="language" class="name"><?php echo get_string('programminglanguage', 'programming'); ?></label>
</td>
<td align="left">
<?php choose_from_menu(programming_get_language_options($programming), 'language', $default_language, false) ?>
</td>
</tr>

<tr>
<td align="right">
<label for="sourcefile" class="name"><?php echo get_string('sourcefile', 'programming'); ?></label>
</td>
<td align="left">
<input type="hidden" name="MAX_FILE_SIZE" value="65536" />
<input type="file" name="sourcefile" />
</td>
</tr>

<tr>
<td colspan="2">
<input type="reset" value="<?php echo get_string('reset', 'programming') ?>" />
<input type="submit" name="action" value="<?php echo get_string('submit', 'programming') ?>" />
</td>
</tr>
</table>
</form>
</div>
<?php endif; ?>
<?php if ($isearly): ?>
<p><?php echo get_string('programmingnotopen', 'programming'); ?></p>
<?php endif; ?>
<?php if ($islate): ?>
<p><?php echo get_string('timeexceed', 'programming'); ?></p>
<?php endif; ?>
</div>
