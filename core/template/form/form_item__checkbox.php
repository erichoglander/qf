<?=$prefix?>
<div class="<?=$item_class?>">
<?php 
if ($items != null) {
	foreach ($items as $item) 
		print $item;
}
else {
?>
	<div class="form-input">
		<?=$input_prefix?>
		<?php if (!empty($label)) { ?>
			<label class="form-label" for="<?=$name?>"><?=$input?> <?=$label?></label>
		<?php } else { ?>
			<?=$input?>
		<?php } ?>
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