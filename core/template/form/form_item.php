<?=$prefix?>
<div class="<?=$item_class?>">
<?php if (!empty($label)) { ?>
	<label class="form-label" for="<?=$name?>"><?=$label?></label>
<?php } ?>
<?php foreach ($containers as $i => $container) { ?>
	<div class="form-container form-<?=$contains?>">
	<?php 
	if ($contains == "items") {
		// pr($container);
		foreach ($container as $item) {
			print $item;
		}
	}
	else if ($contains == "inputs") {
		foreach ($container as $j => $input) {
	?>
		<div class="form-input">
			<?=$input_prefix?>
			<?=$input?>
			<?php if (isset($error[$j])) { ?>
			<div class="form-input-error"><?=$error[$j]?></div>
			<?php } ?>
			<?=$input_suffix?>
		</div>
	<?php
		}
	}
	?>
	</div>
<?php } ?>
<?php if (!empty($description)) { ?>
	<div class="form-item-description"><?=$description?></div>
<?php } ?>
</div>
<?=$suffix?>