<?php
$this->Html->title = t("Add user");
$this->Html->h1 = $this->Html->title;
$this->Html->breadcrumbs[] = ["user/list", t("Users")];
$this->Html->breadcrumbs[] = $this->Html->title;

print $form;