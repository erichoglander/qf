<?php
$this->Html->breadcrumbs[] = $this->Html->title;
if ($status == "closed") {
	$this->Html->h1 = $this->Html->title = t("Registration closed");
}
else {
	$this->Html->h1 = $this->Html->title = t("Create account");
	print $form;
}