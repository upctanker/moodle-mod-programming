<div class="maincontent generalbox">

fetch result:

<form id="resemble_analyze_form" action="resemble_analyze.php">
  <input type="hidden" name="a" value="<?php echo $programming->id ?>" />
  <input type="hidden" name="action" value="fetchresult" />
  <div>
    <label for="url">url</label>
	<input type="text" name="url" size="50" />
  </div>
  <div>
    <label for="max">max</label>
	<input type="text" name="max" size="10" value="250"/>
  </div>
  <div>
    <label for="lowest">lowest</label>
	<input type="text" name="lowest" size="10" value="30"/>
  </div>
  <input id="begin_analyze" type="submit" value="begin" />
</form>

</div>
