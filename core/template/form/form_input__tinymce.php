<?=$tinymce_script?>
<textarea <?=$attributes?>><?=$value?></textarea>
<script>setTimeout(function(){ tinymce.init(<?=$tinymce_config?>); }, 100)</script>