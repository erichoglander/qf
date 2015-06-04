<?php
$this->Html->h1 = $this->Html->title = t("Scan code");
$this->Html->breadcrumbs[] = ["i18n/list", t("Translations")];
$this->Html->breadcrumbs[] = $this->Html->title;

print $form;