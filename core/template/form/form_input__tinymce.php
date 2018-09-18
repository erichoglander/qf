<textarea <?=$attributes?>><?=$value?></textarea>
<script>
<?php if ($tinymce_init) { ?>
  setTimeout(function(){ tinymce.init(<?=$tinymce_config?>); }, 100);
<?php } else { ?>
  if (typeof(_tinymce_config) == "undefined")
    _tinymce_config = {};
  _tinymce_config['<?=$tinymce_id?>'] = <?=$tinymce_config?>;
<?php } ?>
</script>