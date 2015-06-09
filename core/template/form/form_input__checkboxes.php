<?php foreach ($options as $key => $val) { ?>
	<label class="form-label checkbox-label">
		<input <?=$attributes?> value="<?=$key?>"<?=($value && in_array($key, $value) ? " checked" : "")?>> 
		<?=FontAwesome\Icon("square checkbox-icon-unchecked")?>
		<?=FontAwesome\Icon("check-square checkbox-icon-checked")?> 
		<?=$val?>
	</label>
<?php } ?>