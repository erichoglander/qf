<?php foreach ($options as $key => $val) { ?>
	<label class="form-label radio-label">
		<input <?=$attributes?> value="<?=$key?>"<?=($key == $value && strlen($key) == strlen($value) ? " checked" : "")?>>
		<?=FontAwesome\Icon("circle radio-icon-unchecked")?>
		<?=FontAwesome\Icon("check-circle radio-icon-checked")?>
		<?=$val?>
	</label>
<?php } ?>