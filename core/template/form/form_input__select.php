<select <?=$attributes?>>
<?php foreach ($options as $key => $val) { ?>
	<option value="<?=$key?>"<?=($key == $value && strlen($key) == strlen($value) ? " selected": "")?>><?=$val?></option>
<?php } ?>
</select>