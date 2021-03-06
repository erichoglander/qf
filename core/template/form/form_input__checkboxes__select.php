<?php 
$title = "";
$n = 0;
foreach ($options as $key => $val) { 
  if ($value && in_array($key, $value)) {
    if ($n != 0)
      $title.= ", ";
    $title.= $val;
    $n++;
  }
}
if (!$n)
  $title = $empty_option;
?>
<div class="checkboxes-select" empty_option="<?=$empty_option?>">
  <div class="checkboxes-select-title form-textfield">
    <span class="checkboxes-select-title-inner"><?=$title?></span>
    <?=FontAwesome\Icon("angle-down")?>
  </div>
  <div class="checkboxes-select-options">
  <?php foreach ($options as $key => $val) { ?>
    <label class="form-label checkbox-label">
      <input <?=$attributes?> 
        value="<?=$key?>"
        <?=($value && in_array($key, $value) ? "checked" : "")?>
        <?=(isset($disabled_options) && in_array($key, $disabled_options) ? " disabled" : "")?>> 
      <?=FontAwesome\Icon("square checkbox-icon-unchecked")?>
      <?=FontAwesome\Icon("check-square checkbox-icon-checked")?> 
      <span class="label-inner"><?=$val?></span>
    </label>
  <?php } ?>
  </div>
</div>