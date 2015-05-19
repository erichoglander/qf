<?php
if ($status == "closed") {
	$this->Html->title = t("Registration closed");
	$this->Html->h1 = $this->Html->title;
}
else {
	$this->Html->title = t("Create account");
	$this->Html->h1 = $this->Html->title;
	print $form;
}