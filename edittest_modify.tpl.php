<div class="generalbox" align="center">
<h1><?php echo $id ? get_string('edit') : get_string('add'); ?></h1>
    
<div class="programming edittest">
<form method="post" enctype="multipart/form-data" action="edittest_modify.php">
<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo 1024 * 1024 * 16; ?>" />
<input type="hidden" name="a" value="<?php echo $programming->id ?>" />
<input type="hidden" name="id" value="<?php echo $testcase->id ?>" />

<table cellpadding="3">
<tr>
<td align="right"><label for="input" class="name"><?php echo get_string('input', 'programming') ?></label><?php helpbutton('editinput', 'editinput', 'programming'); ?></td>
<td>
<div>
<?php echo print_textarea(false, 10, 50, 0, 0, 'input', isset($testcase->input) && strlen($testcase->input) < 10240 ? $testcase->input : '') ?>
<a href="download_io.php?a=<?php echo $programming->id; ?>&amp;test=<?php echo $testcase->id; ?>&amp;type=in"><?php echo get_string('download', 'programming'); ?></a>
</div>
<div><?php echo get_string('usefile', 'programming'); ?><input type="file" name="inputfile" /></div>
</td>
</tr>

<tr>
<td align="right"><label for="output" class="name"><?php echo get_string('expectedoutput', 'programming') ?></label><?php helpbutton('editexpectedoutput', 'editexpectedoutput', 'programming'); ?></td>
<td>
<?php echo print_textarea(false, 10, 50, 0, 0, 'output', isset($testcase->output) && strlen($testcase->output) < 10240 ? $testcase->output : '') ?>
<a href="download_io.php?a=<?php echo $programming->id; ?>&amp;test=<?php echo $testcase->id; ?>&amp;type=out"><?php echo get_string('download', 'programming'); ?></a>
<div><?php echo get_string('usefile', 'programming'); ?><input type="file" name="outputfile" /></div>
</td>
</tr>

<tr>
<td align="right"><label for="timelimit" class="name"><?php echo get_string('timelimit', 'programming') ?></label><?php helpbutton('timelimit', 'timelimit', 'programming'); ?></td>
<td><?php choose_from_menu(programming_get_timelimit_options(), 'timelimit', isset($testcase->timelimit) ? $testcase->timelimit : $programming->timelimit) ?></td>
</tr>

<tr>
<td align="right"><label for="memlimit" class="name"><?php echo get_string('memlimit', 'programming') ?></label><?php helpbutton('memlimit', 'memlimit', 'programming'); ?></td>
<td><?php choose_from_menu(programming_get_memlimit_options(), 'memlimit', isset($testcase->memlimit) ? $testcase->memlimit : $programming->memlimit) ?></td>
</tr>

<tr>
<td align="right"><label for="weight" class="name"><?php echo get_string('weightsetting', 'programming') ?></label><?php helpbutton('weight', 'weight', 'programming'); ?></td>
<td><?php choose_from_menu(programming_get_weight_options(), 'weight', isset($testcase->weight) ? $testcase->weight : 0) ?></td>
</tr>

<tr>
<td align="right"><label for="public" class="name"><?php echo get_string('public', 'programming') ?></label></td>
<td><?php choose_from_menu(programming_get_test_pub_options(), 'pub', isset($testcase->pub) ? $testcase->pub : 0) ?></td>
</tr>

<tr>
<td colspan="2" align="center">
<?php if ($id): ?>
<input type="submit" name="action" value="<?php echo get_string('edit') ?>" />
<?php endif; ?>
<input type="submit" name="action" value="<?php echo get_string('add') ?>" onclick="form.elements['id'].value=0; return true;"/>
</td>
</tr>
</table>
</form>
</div>
</div>
