<?php
$this->Html->h1 = $this->Html->title = t("Logs");
$this->Html->breadcrumbs[] = $this->Html->title;
?>

<?=$search?>

<table class="table-standard">
  <thead>
    <tr>
      <th><?=t("User")?></th>
      <th><?=t("Category")?></th>
      <th><?=t("Text")?></th>
      <th><?=t("Date")?></th>
      <th><?=t("Actions")?></th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($logs as $Log) { ?>
    <tr class="status-<?=$Log->get("type")?>">
      <td><?=$Log->user()->name()?></td>
      <td><?=xss($Log->get("category"))?></td>
      <td><a href="<?=url("log/view/".$Log->id())?>"><?=xss(substr($Log->get("text"), 0, 140))?></a></td>
      <td><?=date("Y-m-d H:i:s", $Log->get("created"))?></td>
      <td><a href="<?=url("log/delete/".$Log->id())?>"><?=t("Delete")?></a></td>
    </tr>
  <?php } ?>
  </tbody>
</table>

<?=$pager?>