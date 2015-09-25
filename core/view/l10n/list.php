<?php
$this->Html->h1 = $this->Html->title = t("Localization");
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
	foreach ($l10n_strings as $l10nString) { 
		$translation_languages = "";
		foreach ($l10nString->translations() as $Translation)
			$translation_languages.= $Translation->get("lang")." ";
	?>
		<tr>
			<td><?=xss(shorten($l10nString->get("string"), 100))?></td>
			<td><?=$l10nString->get("lang")?></td>
			<td><?=$translation_languages?></td>
			<td class="actions">
				<a href="<?=url("l10n/edit/".$l10nString->id())?>"><?=t("Translate")?></a>
				<a href="<?=url("l10n/delete/".$l10nString->id())?>"><?=t("Delete")?></a>
			</td>
		</tr>
	<?php } ?>
	</tbody>
</table>

<?=$pager?>