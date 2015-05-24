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

	<!-- This website uses Font Awesome by Dave Gandy - http://fontawesome.io -->

	<!-- PRE PAGE -->
	<?=$pre_page?>

	<?php if (!empty($menu["admin"])) print $menu["admin"]; ?>

	<!-- PAGE -->
	<div id="page">
		<?=$page?>
	</div>

	<!-- POST PAGE -->
	<?=$post_page?>

</body>
</html>