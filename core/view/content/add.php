<?php
$this->Html->h1 = $this->Html->title = t("Add content");
$this->Html->breadcrumbs[] = ["content/list", t("Content")];
$this->Html->breadcrumbs[] = $this->Html->title;

print $form;