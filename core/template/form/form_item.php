<?=$prefix?>
<div class="<?=$itemClass?>">
<?php if (!empty($label)) { ?>
	<label class="form-label" for="<?=$inputName?>"><?=$label?></label>
<?php } ?>
<?php if (!empty($inputs)) { ?>
	<div class="form-inputs">
	<?php foreach ($inputs as $i => $input) { ?>
		<div class="form-input">
			<?=$inputPrefix?>
			<?=$input?>
			<?php if (isset($error[$i])) { ?>
			<div class="form-input-error"><?=$error[$i]?></div>
			<?php } ?>
			<?=$inputSuffix?>
		</div>
	<?php } ?>
	</div>
<?php } ?>
<?php if (!empty($items)) { ?>
	<div class="form-items">
	<?php foreach ($items as $item) { ?>
		<?=$item?>
	<?php } ?>
	</div>
<?php } ?>
<?php if (!empty($description)) { ?>
	<div class="form-item-description"><?=$description?></div>
<?php } ?>
</div>
<?=$suffix?>