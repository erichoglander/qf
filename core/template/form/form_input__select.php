<select <?=$attributes?>>
<?php foreach ($options as $key => $val) { ?>
  <option 
    value="<?=$key?>"
    <?=($key == $value && strlen($key) == strlen($value) ? "selected": "")?>
    <?=(isset($disabled_options) && in_array($key, $disabled_options) ? "disabled" : "")?>>
      <?=$val?>
  </option>
<?php } ?>
</select>