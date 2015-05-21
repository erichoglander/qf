<?php if (!empty($msgs)) { ?>
<div id="system-messages">
	<?php foreach ($msgs as $type => $ul) { ?>
	<ul class="system-messages system-messages-<?=$type?>">
		<?php foreach ($ul as $li) { ?>
		<li class="system-message"><?=xss($li)?></li>
		<?php } ?>
	</ul>
	<?php } ?>
</div>
<?php } ?>

<?php if (!empty($breadcrumbs)) { ?>
<ul class="breadcrumbs">
	<?php 
	foreach ($breadcrumbs as $i => $crumb) {
		print '<li class="breadcrumb">';
		if (is_array($crumb))
			print '<a href="/'.$crumb[0].'">'.$crumb[1].'</a>';
		else
			print '<span>'.$crumb.'</span>';
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
