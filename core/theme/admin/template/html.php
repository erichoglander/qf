<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title><?=$title?></title>
	<?=$favicon?>
	<?=$meta?>
	<?=$pre_css?>
	<?php foreach ($css as $c) { ?>
	<link rel="stylesheet" type="text/css" href="<?=$c?>">
	<?php } ?>
	<?=$pre_js?>
	<?php foreach ($js as $j) { ?>
	<script type="text/javascript" src="<?=$j?>"></script>
	<?php } ?>
	<?=$head_end?>
</head>
<body class="<?=implode(" ", $body_class)?>">

	<!-- PRE PAGE -->
	<?=$pre_page?>

	<!-- PAGE -->
	<div id="page">
		<?=$page?>
	</div>

	<!-- POST PAGE -->
	<?=$post_page?>

</body>
</html>