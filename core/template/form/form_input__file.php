<div class="form-file-upload">
	<div class="form-file-upload-button btn btn-primary">
		<input <?=$attributes?>>
		<input type="hidden" name="<?=$name?>[id]" value="<?=$value?>">
		<input type="hidden" name="<?=$name?>[token]" value="<?=$token?>">
		<span><?=$upload_button?></span>
	</div>
	<?php if (!empty($file_extensions)) { ?>
	<div class="form-file-extensions">
		<?=t("Accepted file extensions")?>: <?=implode(", ", $file_extensions)?>
	</div>
	<?php } ?>
	<?php if (!empty($file_extra_text)) { ?>
	<div class="form-file-extra-text">
		<?=$file_extra_text?>
	</div>
	<?php } ?>
</div>
<div class="form-file-preview">
	<?=$preview?>
	<div class="btn form-file-remove-button" onclick="<?=$file_remove?>"><?=$remove_button?></div>
</div>