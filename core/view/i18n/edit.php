<?php
$this->Html->h1 = $this->Html->title = t("Edit translation");
$this->Html->breadcrumbs[] = ["i18n/list", t("Translations")];
$this->Html->breadcrumbs[] = $this->Html->title;

print $form;