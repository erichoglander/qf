<div <?=$attributes?>>
	<div class="interval-slider"></div>
	<div class="interval-hidden">
		<input type="hidden" name="<?=$name?>[0]" value="<?=(is_array($value) ? $value[0] : null)?>">
		<input type="hidden" name="<?=$name?>[1]" value="<?=(is_array($value) ? $value[1] : null)?>">
	</div>
</div>