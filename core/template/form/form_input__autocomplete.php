<div class="autocomplete-preview">
	<div class="autocomplete-preview-title"><?=($value && array_key_exists("title", $value) ? $value["title"] : null)?></div>
	<div class="autocomplete-remove"><?=FontAwesome\Icon("times")?></div>
</div>
<input <?=$attributes?> value="<?=($value && array_key_exists("title", $value) ? $value["title"] : null)?>">
<input type="hidden" name="<?=$name?>[value]" value="<?=($value && array_key_exists("value", $value) ? $value["value"] : null)?>">
<div class="autocomplete-items"></div>