<?php
$this->Html->h1 = $this->Html->title = t("Redirects");
$this->Html->breadcrumbs[] = $this->Html->title;
?>
<a class="btn btn-primary" href="<?=url("redirect/add")?>"><?=t("Add redirect")?></a>

<table class="striped">
	<thead>
		<tr>
			<th><?=t("Source")?></th>
			<th><?=t("Target")?></th>
			<th><?=t("Code")?></th>
			<th><?=t("Active")?></th>
			<th class="actions"><?=t("Actions")?></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($redirects as $Redirect) { ?>
		<tr>
			<td><?=xss($Redirect->get("source"))?></td>
			<td><?=xss($Redirect->get("target"))?></td>
			<td><?=$Redirect->get("code")?></td>
			<td><?=($Redirect->get("status") ? t("Yes") : t("No"))?></td>
			<td class="actions">
				<a href="<?=url("redirect/edit/".$Redirect->id())?>"><?=t("Edit")?></a>
				<a href="<?=url("redirect/delete/".$Redirect->id())?>"><?=t("Delete")?></a>
			</td>
		</tr>
	<?php } ?>
	</tbody>
</table>