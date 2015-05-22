<?php
$this->Html->h1 = $this->Html->title = t("Delete user");
$this->Html->breadcrumbs[] = ["user/listing", t("Users")];
$this->Html->breadcrumbs[] = t("Delete user");

print $form;