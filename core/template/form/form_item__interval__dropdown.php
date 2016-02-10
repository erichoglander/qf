<?=$prefix?>
<div class="<?=$item_class?> interval-dropdown">
<?php if (!empty($label)) { ?>
  <label class="form-label form-textfield">
    <?=$label?><?=FontAwesome\Icon("angle-down")?>
  </label>
<?php } ?>
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
    <?php if (!empty($icon)) { ?>
      <?=FontAwesome\Icon($icon, "form-icon-addon")?>
    <?php } ?>
    <?=$input?>
    <?php if (!empty($error)) { ?>
      <?=FontAwesome\Icon("times", "form-icon-feedback form-icon-error")?>
    <?php } ?>
    <?=$input_suffix?>
    <?php if (!empty($description)) { ?>
      <div class="form-item-description"><?=$description?></div>
    <?php } ?>
  </div>
  <?php if (!empty($error)) { ?>
    <div class="form-input-error"><?=$error?></div>
  <?php } ?>
<?php
}
?>
  <?=$delete_button?>
  <?=$add_button?>
  <div class="form-item-loader">
    <?=FontAwesome\Icon("refresh", "fa-spin")?>
  </div>
</div>
<?=$suffix?>