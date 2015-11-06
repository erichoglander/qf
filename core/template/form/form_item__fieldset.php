<?=$prefix?>
<div class="<?=$item_class?>">
<?php if (!empty($sortable)) { ?>
	<div class="form-sortable">
		<div class="form-sortable-up"><?=FontAwesome\Icon("angle-up")?></div>
		<div class="form-sortable-drag"><?=FontAwesome\Icon("arrows")?></div>
		<div class="form-sortable-down"><?=FontAwesome\Icon("angle-down")?></div>
	</div>
<?php } ?>
<?php if (!empty($label)) { ?>
	<label class="form-label" for="<?=$input_name?>"><?=$label?></label>
<?php } ?>
	<div class="form-items">
		<div class="inner">
<?php 
if ($items !== null) {
	foreach ($items as $item) 
		print $item;
}
?>
		</div>
	</div>
<?php if (!empty($description)) { ?>
	<div class="form-item-description"><?=$description?></div>
<?php } ?>
	<?=$delete_button?>
	<?=$add_button?>
	<div class="form-item-loader">
		<?=FontAwesome\Icon("refresh", "fa-spin")?>
	</div>
</div>
<?=$suffix?>