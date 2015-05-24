<?php
$this->Html->h1 = $this->Html->title = t("Add redirect");
$this->Html->breadcrumbs[] = ["redirect/list", t("Redirects")];
$this->Html->breadcrumbs[] = $this->Html->title;

print $form;