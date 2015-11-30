<?php
$this->Html->h1 = $this->Html->title = t("Content");
$this->Html->breadcrumbs[] = $this->Html->title;
?>

<a class="btn btn-primary" href="<?=url("content/add")?>"><?=t("Add content")?></a>

<table class="striped">
	<thead>
		<tr>
			<th><?=t("ID")?></th>
			<th><?=t("Title")?></th>
			<th><?=t("Created")?></th>
			<th><?=t("Updated")?></th>
			<th class="actions"><?=t("Actions")?></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($contents as $Content) { ?>
		<tr>
			<td><?=$Content->id()?></td>
			<td><?=$Content->get("title")?></td>
			<td><?=date("Y-m-d H:i:s", $Content->get("created"))?></td>
			<td><?=date("Y-m-d H:i:s", $Content->get("updated"))?></td>
			<td class="actions">
				<?php if ($access_config) { ?>
				<a href="<?=url("content/config/".$Content->id())?>"><?=t("Configure")?></a>
				<?php } ?>
				<?php if ($access_edit) { ?>
				<a href="<?=url("content/edit/".$Content->id())?>"><?=t("Edit")?></a>
				<?php } ?>
				<?php if ($access_delete) { ?>
				<a href="<?=url("content/delete/".$Content->id())?>"><?=t("Delete")?></a>
				<?php } ?>
			</td>
		</tr>
	<?php } ?>
	</tbody>
</table>