<div class="maincontent generalbox">
<h1><?php echo get_string('presetcode', 'programming'); helpbutton('presetcode', 'presetcode', 'programming'); ?></h1>

<form method="post" action="edittest_presetcode.php">
<input type="hidden" name="a" value="<?php echo $programming->id ?>" />

<table>
<tr>
<td>
<label for="presetcode" class="name"><?php echo get_string('presetcode', 'programming') ?></label>
</td>
<td>
<?php echo print_textarea(false, 10, 50, 0, 0, 'presetcode', $programming->presetcode) ?>
</td>
</tr>

<tr>
<td colspan="2">
<input type="submit" name="action" value="<?php echo get_string('setpresetcode', 'programming') ?>" />
<input type="submit" name="action" value="<?php echo get_string('delpresetcode', 'programming') ?>" onclick="form.elements.presetcode.value=''; return true;" />
</td>
</tr>
</table>

</form>
</div>
