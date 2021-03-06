<div class="autoselect">
  <input name="__autoselect_<?=$name?>" class="form-textfield form-autoselect" autocomplete="off" placeholder="<?=(isset($options[""]) ? $options[""] : null)?>" value="<?=(strlen($value) && array_key_exists($value, $options) ? $options[$value] : null)?>">
  <div class="autoselect-options"></div>
</div>
<div class="autoselect-hidden">
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
</div>