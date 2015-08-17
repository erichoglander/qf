<?php
$this->Html->h1 = $this->Html->title = t("Aliases");
$this->Html->breadcrumbs[] = $this->Html->title;
?>
<a class="btn btn-primary" href="/alias/add"><?=t("Add alias")?></a>

<table class="striped">
	<thead>
		<tr>
			<th><?=t("Path")?></th>
			<th><?=t("Alias")?></th>
			<th><?=t("Active")?></th>
			<th class="actions"><?=t("Actions")?></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($aliases as $Alias) { ?>
		<tr>
			<td><?=xss($Alias->get("path"))?></td>
			<td><?=xss($Alias->get("alias"))?></td>
			<td><?=($Alias->get("status") ? t("Yes") : t("No"))?></td>
			<td class="actions">
				<a href="/alias/edit/<?=$Alias->id()?>"><?=t("Edit")?></a>
				<a href="/alias/delete/<?=$Alias->id()?>"><?=t("Delete")?></a>
			</td>
		</tr>
	<?php } ?>
	</tbody>
</table>