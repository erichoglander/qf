<?php
$this->Html->title = ($User->id() ? t("Add user") : t("Edit user"));
$this->Html->h1 = $this->Html->title;
$this->Html->breadcrumbs[] = ["user/list" => t("Users")];
$this->Html->breadcrumbs[] = $this->Html->title;

// print $Form->render();