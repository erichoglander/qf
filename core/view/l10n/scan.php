<?php
$this->Html->h1 = $this->Html->title = t("Scan code");
$this->Html->breadcrumbs[] = ["l10n/list", t("Localization")];
$this->Html->breadcrumbs[] = $this->Html->title;

print $form;