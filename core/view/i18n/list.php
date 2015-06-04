<?php
$this->Html->h1 = $this->Html->title = t("Translations");
$this->Html->breadcrumbs[] = $this->Html->title;
?>

<?=$search?>

<table class="striped">
	<thead>
		<tr>
			<th><?=t("Text")?></th>
			<th><?=t("Source language")?></th>
			<th><?=t("Translations")?></th>
			<th class="actions"><?=t("Actions")?></th>
		</tr>
	</thead>
	<tbody>
	<?php 
	foreach ($translations as $translation) { 
		$translation_languages = "";
		foreach ($translation->translations as $tr)
			$translation_languages.= $tr->lang." ";
	?>
		<tr>
			<td><?=xss(shorten($translation->text, 100))?></td>
			<td><?=$translation->lang?></td>
			<td><?=$translation_languages?></td>
			<td class="actions">
				<a href="/i18n/edit/<?=$translation->id?>"><?=t("Translate")?></a>
				<a href="/i18n/delete/<?=$translation->id?>"><?=t("Delete")?></a>
			</td>
		</tr>
	<?php } ?>
	</tbody>
</table>

<?=$pager?>