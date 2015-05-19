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

<?php if ($h1) { ?>
	<h1 id="page-title"><?=$h1?></h1>
<?php } ?>

<?=$pre_content?>

<div id="content">
	<?=$content?>
</div>

<?=$post_content?>
