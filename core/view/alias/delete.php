<?php
$this->Html->h1 = $this->Html->title = t("Delete alias");
$this->Html->breadcrumbs[] = ["alias/list", t("Aliases")];
$this->Html->breadcrumbs[] = $this->Html->title;

print $form;