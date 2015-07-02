<?php 
$title = "";
$n = 0;
foreach ($options as $key => $val) { 
	if ($value && in_array($key, $value)) {
		if ($n == 3) {
			$title.= "...";
			break;
		}
		else {
			if ($n != 0)
				$title.= ", ";
			$title.= $val;
		}
	}
}
?>
<div class="checkboxes-select">
	<div class="checkboxes-select-title">
		<div class="checkboxes-select-title-inner"><?=$title?></div>
		<?=FontAwesome\Icon("angle-down")?>
	</div>
	<div class="checkboxes-select-options">
	<?php foreach ($options as $key => $val) { ?>
		<label class="form-label checkbox-label">
			<input <?=$attributes?> value="<?=$key?>"<?=($value && in_array($key, $value) ? " checked" : "")?>> 
			<?=FontAwesome\Icon("square checkbox-icon-unchecked")?>
			<?=FontAwesome\Icon("check-square checkbox-icon-checked")?> 
			<?=$val?>
		</label>
	<?php } ?>
	</div>
</div>