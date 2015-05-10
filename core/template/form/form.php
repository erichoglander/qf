<?=$prefix?>
<div class="form-wrapper">
	<form <?=$attributes?>>
		<?php if (!empty($errors)) { ?>
		<div class="form-errors">
			<?php foreach ($errors as $error) { ?>
			<div class="form-error"><?=$error?></div>
			<?php } ?>
		</div>
		<?php } ?>
		<div class="form-container form-items form-root-container">
		<?php 
		foreach ($items as $item)
			print $item;
		?>
		</div>
		<input type="hidden" name="form_<?=$name?>" value="1">
		<input type="hidden" name="form_token" value="<?=$token?>">
	</form>
</div>
<?=$suffix?>