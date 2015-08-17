<?php
$this->Html->h1 = $this->Html->title = t("Users");
$this->Html->breadcrumbs[] = t("Users");
?>

<a class="btn btn-primary" href="/user/add"><?=t("Add user")?></a>

<?=$search?>

<table class="striped">
	<thead>
		<tr>
			<th><?=t("Username")?></th>
			<th><?=t("E-mail")?></th>
			<th><?=t("Active")?></th>
			<th><?=t("Roles")?></th>
			<th><?=t("Created")?></th>
			<th><?=t("Last login")?></th>
			<th class="actions"><?=t("Actions")?></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($users as $User) { ?>
		<tr>
			<td><?=$User->get("name")?></td>
			<td><?=$User->get("email")?></td>
			<td><?=($User->get("status") ? t("Yes") : t("No"))?></td>
			<td><?php 
			foreach ($User->get("roles") as $i => $role) { 
				if ($i != 0)
					print ", ";
				print t($role->title);
			} 
			?></td>
			<td><?=date("Y-m-d H:i:s", $User->get("created"))?></td>
			<td><?=($User->get("login") ? date("Y-m-d H:i:s", $User->get("login")) : t("Never"))?></td>
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

<?=$pager?>