<?=$prefix?>
<div class="<?=$item_class?>">
<?php if (!empty($label)) { ?>
	<label class="form-label" for="<?=$input_name?>"><?=$label?></label>
<?php } ?>
<?php 
if ($items != null) {
	foreach ($items as $item) 
		print $item;
}
else {
?>
	<div class="form-input">
		<?=$input_prefix?>
		<?=$input?>
		<?php if (!empty($error)) { ?>
		<div class="form-input-error"><?=$error?></div>
		<?php } ?>
		<?=$input_suffix?>
	</div>
<?php
}
?>
<?php if (!empty($description)) { ?>
	<div class="form-item-description"><?=$description?></div>
<?php } ?>
</div>
<?=$suffix?>