<?php foreach ($options as $key => $val) { ?>
	<label class="form-label checkbox-label"><input <?=$attributes?> value="<?=$key?>"<?=(in_array($key, $value) ? " checked" : "")?>> <?=$val?></label>
<?php } ?>