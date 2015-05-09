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
	</form>
</div>
<?=$suffix?>