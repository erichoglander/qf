<input <?=$attributes?>>
<?php if ($generator) { ?>
<span class="form-password-generator" onclick="formGeneratePassword(this, '<?=$generator_copy?>');">
	<?=FontAwesome\Icon("magic")?>
</span>
<?php } ?>