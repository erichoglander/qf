<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title><?=$title?></title>
	<?=$favicon?>
	<?=$meta?>
	<?=$pre_styles?>
	<?php foreach ($styles as $style) { ?>
	<link rel="stylesheet" type="text/css" href="<?=$style?>">
	<?php } ?>
	<?=$pre_scripts?>
	<?php foreach ($scripts as $script) { ?>
	<script type="text/javascript" src="<?=$script?>"></script>
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