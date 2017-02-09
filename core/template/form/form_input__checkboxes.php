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