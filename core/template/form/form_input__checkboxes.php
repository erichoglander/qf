<?php foreach ($options as $key => $val) { ?>
	<label class="checkbox-label"><input <?=$attributes?> value="<?=$key?>"<?=($key == $value && strlen($key) == strlen($value) ? " checked" : "")?> <?=$val?></label>
<?php } ?>