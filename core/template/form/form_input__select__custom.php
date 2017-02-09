<div class="select-custom">
  <div class="select-custom-title form-textfield">
    <span class="select-custom-title-inner">
      <?=(array_key_exists($value, $options) ? $options[$value] : null)?>
    </span>
    <?=FontAwesome\Icon("angle-down")?>
  </div>
  <div class="select-custom-options">
  <?php foreach ($options as $key => $val) { ?>
    <div class="select-custom-option<?=($key == $value && strlen($key) == strlen($value) ? " active": "")?><?=(isset($disabled_options) && in_array($key, $disabled_options) ? " disabled" : "")?>">
      <?=$val?>
    </div>
  <?php } ?>
  </div>
  <div class="select-custom-hidden">
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
</div>