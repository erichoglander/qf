<?php
$this->Html->h1 = $this->Html->title = t("Edit user");
$this->Html->breadcrumbs[] = ["user/list", t("Users")];
$this->Html->breadcrumbs[] = $this->Html->title;

print $form;