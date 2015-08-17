<?php
$this->Html->h1 = $this->Html->title = t("Add alias");
$this->Html->breadcrumbs[] = ["alias/list", t("Aliases")];
$this->Html->breadcrumbs[] = $this->Html->title;

print $form;