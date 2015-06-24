<?=$prefix?>
<div class="<?=$item_class?>">
<?php if (!empty($label)) { ?>
	<label class="form-label" for="<?=$input_name?>"><?=$label?></label>
<?php } ?>
<?php 
if ($items !== null) {
	foreach ($items as $item) 
		print $item;
}
else {
?>
	<div class="form-input">
		<?=$input_prefix?>
		<?php if (!empty($icon)) { ?>
			<?=FontAwesome\Icon($icon, "form-icon-addon")?>
		<?php } ?>
		<?=$input?>
		<?php if (!empty($error)) { ?>
			<?=FontAwesome\Icon("times", "form-icon-feedback form-icon-error")?>
		<?php } ?>
		<?=$input_suffix?>
	</div>
	<?php if (!empty($error)) { ?>
		<div class="form-input-error"><?=$error?></div>
	<?php } ?>
<?php
}
?>
<?php if (!empty($description)) { ?>
	<div class="form-item-description"><?=$description?></div>
<?php } ?>
	<?=$delete_button?>
	<?=$add_button?>
	<div class="form-item-loader">
		<?=FontAwesome\Icon("spinner", "fa-pulse")?>
	</div>
</div>
<?=$suffix?>