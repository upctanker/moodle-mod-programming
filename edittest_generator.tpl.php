<div class="maincontent generalbox">

<form method="post" action="edittest_generator.php">
<input type="hidden" name="a" value="<?php echo $programming->id ?>" />
<input type="hidden" name="generatortype" value="script" />

<!--
<div class="formelement">
<label for="generatortype" class="name"><?php echo get_string('type') ?></label>
<div class="field"><?php
  choose_from_menu(
    array(get_string('scriptgenerator', 'programming'),
          get_string('binarygenerator', 'programming')),
    'generatortype', 'none');
?></div>
</div>
-->

<table>
<tr>
<td>
<label for="generator" class="name"><?php echo get_string('generator', 'programming') ?></label>
</td>
<td>
<?php echo print_textarea(false, 10, 50, 0, 0, 'generator', $programming->generator) ?>
</td>
</tr>

<tr>
<td colspan="2">
<input type="submit" name="action" value="<?php echo get_string('setgenerator', 'programming') ?>" />
<input type="submit" name="action" value="<?php echo get_string('delgenerator', 'programming') ?>" onclick="form.elements.generator.value=''; return true;" />
</td>
</tr>
</table>

</form>
</div>
