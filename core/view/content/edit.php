<?php
$this->Html->h1 = $this->Html->title = t("Edit content");
$this->Html->breadcrumbs[] = ["content/list", t("Content")];
$this->Html->breadcrumbs[] = $this->Html->title;

print $form;