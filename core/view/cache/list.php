<?php
$this->Html->h1 = $this->Html->title = t("Cache");
$this->Html->breadcrumbs[] = $this->Html->title;
?>

<table class="striped cache-list">
	<thead>
		<tr>
			<th><?=t("Name")?></th>
			<th><?=t("Size")?></th>
			<th><?=t("Expires")?></th>
			<th class="actions"><?=t("Actions")?></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($caches as $cache) { ?>
		<tr>
			<td><?=$cache->name?></td>
			<td><?=formatBytes($cache->size)?></td>
			<td><?=date("Y-m-d H:i:s", $cache->expire)?></td>
			<td class="actions">
				<a href="<?=url("cache/delete/".$cache->name, true)?>"><?=t("Delete")?></a>
			</td>
		</tr>
	<?php } ?>
	</tbody>
</table>