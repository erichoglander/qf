<?php
$this->Html->h1 = $this->Html->title = t("Delete redirect");
$this->Html->breadcrumbs[] = ["redirect/list", t("Redirects")];
$this->Html->breadcrumbs[] = $this->Html->title;

print $form;