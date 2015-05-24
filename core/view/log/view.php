<?php
$this->Html->h1 = $this->Html->title = t("Log entry #:id", "en", [":id" => $Log->id()]);
$this->Html->breadcrumbs[] = ["log/list", t("Logs")];
$this->Html->breadcrumbs[] = $this->Html->title;
?>

<p><b>User:</b> <?=$Log->get("User")->name()?></p>
<p><b>IP:</b> <?=$Log->get("ip")?></p>
<p><b>Date:</b> <?=date("Y-m-d H:i:s", $Log->get("created"))?></p>
<p class="log-text"><?=nl2br(xss($Log->get("text")))?></p>
<p class="log-data"><?=pr($Log->get("data"), 1)?></p>