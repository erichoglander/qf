<?php foreach ($options as $key => $val) { ?>
  <label class="form-label radio-label">
    <input <?=$attributes?> 
      value="<?=$key?>"
      <?=($key == $value && strlen($key) == strlen($value) ? "checked" : "")?>
      <?=(isset($disabled_options) && in_array($key, $disabled_options) ? "disabled" : "")?>>
    <?=FontAwesome\Icon("circle radio-icon-unchecked")?>
    <?=FontAwesome\Icon("check-circle radio-icon-checked")?>
    <span class="label-inner"><?=$val?></span>
  </label>
<?php } ?>