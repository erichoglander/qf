<?php if (!empty($msgs)) { ?>
<div id="system-messages">
	<ul class="system-messages">
		<?php foreach ($msgs as $msg) { ?>
		<li class="system-message system-message-<?=$msg["type"]?>"><?=$msg["message"]?></li>
		<?php } ?>
	</ul>
</div>
<?php } ?>

<?php if (!empty($breadcrumbs)) { ?>
<ul class="breadcrumbs">
	<?php 
	foreach ($breadcrumbs as $i => $crumb) {
		print '<li class="breadcrumb">';
		if (is_array($crumb))
			print '<a href="'.BASE_URL.xss($crumb[0]).'">'.xss($crumb[1]).'</a>';
		else
			print '<span>'.xss($crumb).'</span>';
		print '</li>';
	} 
	?>
</ul>
<?php } ?>

<?php if ($h1) { ?>
	<h1 id="page-title"><?=$h1?></h1>
<?php } ?>

<?=$pre_content?>

<div id="content">
	<?=$content?>
</div>

<?=$post_content?>
