<div class="maincontent generalbox">
<h1><?php echo get_string('validator', 'programming'); helpbutton('validator', 'validator', 'programming'); ?></h1>

<form method="post" action="edittest_validator.php">
<input type="hidden" name="a" value="<?php echo $programming->id ?>" />
<input type="hidden" name="validatortype" value="script" />

<table>
<tr>
<td>
<label for="validatortype" class="name"><?php echo get_string('validatortype', 'programming') ?></label>
</td>
<td align="left">
<?php choose_from_menu(array(1 => 'python'), 'validatortype', 'python', ''); ?>
</td>
</tr>
<tr>
<td>
<label for="validator" class="name"><?php echo get_string('validatorcode', 'programming') ?></label>
</td>
<td>
<?php echo print_textarea(false, 10, 50, 0, 0, 'validator', $programming->validator) ?>
</td>
</tr>

<tr>
<td colspan="2">
<input type="submit" name="action" value="<?php echo get_string('setvalidator', 'programming') ?>" />
<input type="submit" name="action" value="<?php echo get_string('delvalidator', 'programming') ?>" onclick="form.elements.validator.value=''; return true;" />
</td>
</tr>
</table>

</form>
</div>
