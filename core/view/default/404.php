<?php
$this->Html->title = "Page not found";
$this->Html->h1 = "404";
?>
<p>Page could not be found</p>
<?php if (isset($console)) { ?>
<script>console.log(<?=$console?>);</script>
<?php } ?>