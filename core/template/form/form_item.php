<?=$prefix?>
<div <?=$attributes?>>
<?php if (!empty($label)) { ?>
  <label class="form-label" for="<?=$input_name?>"><?=$label?></label>
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
  </div>
  <?php if (!empty($error)) { ?>
    <div class="form-input-error"><?=$error?></div>
  <?php } ?>
<?php
}
?>
<?php if (!empty($description)) { ?>
  <div class="form-item-description"><?=$description?></div>
<?php } ?>
  <?=$delete_button?>
  <?=$add_button?>
  <div class="form-item-loader">
    <?=FontAwesome\Icon("refresh", "fa-spin")?>
  </div>
<?php if (!empty($sortable)) { ?>
  <div class="form-sortable">
    <div class="form-sortable-up"><?=FontAwesome\Icon("angle-up")?></div>
    <div class="form-sortable-drag"><?=FontAwesome\Icon("arrows")?></div>
    <div class="form-sortable-down"><?=FontAwesome\Icon("angle-down")?></div>
  </div>
<?php } ?>
</div>
<?=$suffix?>