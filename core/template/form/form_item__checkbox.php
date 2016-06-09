<?=$prefix?>
<div <?=$attributes?>>
<?php 
if ($items !== null) {
?>
  <div class="form-items">
    <div class="inner">
<?php
  foreach ($items as $item) 
    print $item;
?>
    </div>
  </div>
<?php
}
else {
?>
  <div class="form-input">
    <?=$input_prefix?>
    <label class="form-label checkbox-label">
      <?=$input?>
      <?php if (!empty($label)) { ?>
        <span class="label-inner"><?=$label?></span>
      <?php } ?>
    </label>
    <?php if (!empty($error)) { ?>
    <div class="form-input-error"><?=$error?></div>
    <?php } ?>
    <?=$input_suffix?>
  </div>
<?php
}
?>
<?php if (!empty($description)) { ?>
  <div class="form-item-description"><?=$description?></div>
<?php } ?>
</div>
<?=$suffix?>