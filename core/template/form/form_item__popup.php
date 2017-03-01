<?=$prefix?>
<div <?=$attributes?>>
<?php if (!empty($label)) { ?>
    <label class="form-label" for="<?=$input_name?>"><?=$label?></label>
<?php } ?>
<?php if (!$multiple) { ?>
  <div class="form-popup-wrap">
  <?php if (!empty($popup_label)) { ?>
    <label class="form-label" for="<?=$input_name?>"><?=$popup_label?></label>
  <?php } ?>
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
?>
<?php if (!$multiple) { ?>
  </div>
<?php } ?>
<?php if (!empty($description)) { ?>
  <div class="form-item-description"><?=$description?></div>
<?php } ?>
  <?=$preview?>
  <?=$popup_button?>
  <?=$delete_button?>
  <?=$add_button?>
  <div class="form-item-loader">
    <?=FontAwesome\Icon("refresh", "fa-spin")?>
  </div>
<?php if (!empty($sortable)) { ?>
  <div class="form-sortable"<?=($sort_callback ? ' sort_callback="'.$sort_callback.'"' : "")?>>
    <div class="form-sortable-up"><?=FontAwesome\Icon("angle-up")?></div>
    <div class="form-sortable-drag"><?=FontAwesome\Icon("arrows")?></div>
    <div class="form-sortable-down"><?=FontAwesome\Icon("angle-down")?></div>
  </div>
<?php } ?>
</div>
<?=$suffix?>