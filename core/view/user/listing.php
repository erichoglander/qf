<?php
$this->Html->h1 = $this->Html->title = t("Users");
$this->Html->breadcrumbs[] = t("Users");
?>

<a class="btn" href="/user/add">Add user</a>

<table class="striped">
	<thead>
		<tr>
			<th><?=t("ID")?></th>
			<th><?=t("Username")?></th>
			<th><?=t("E-mail")?></th>
			<th><?=t("Active")?></th>
			<th><?=t("Roles")?></th>
			<th class="actions"><?=t("Actions")?></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($users as $User) { ?>
		<tr>
			<td><?=$User->id()?></td>
			<td><?=$User->get("name")?></td>
			<td><?=$User->get("email")?></td>
			<td><?=($User->get("status") ? t("Yes") : t("No"))?></td>
			<td><?php 
			foreach ($User->roles as $i => $role) { 
				if ($i != 0)
					print ", ";
				print $role->name; 
			} 
			?></td>
			<td class="actions">
				<a href="/user/edit/<?=$User->id()?>"><?=t("Edit")?></a>
				<?php if ($User->id() != 1) { ?>
				<a href="/user/delete/<?=$User->id()?>"><?=t("Delete")?></a>
				<a href="/user/signin/<?=$User->id()?>"><?=t("Sign in")?></a>
				<?php } ?>
			</td>
		</tr>
	<?php } ?>
	</tbody>
</table>