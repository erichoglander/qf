<?php
if ($status == 0) {
	$this->Html->title = t("Invalid url");
	$this->Html->h1 = $this->Html->title;
	print '<p>'.t("The specified link is invalid. Maybe it has already been used or has expired.").'</p>';
}
else if ($status == 1) {
	$this->Html->title = t("Change password");
	$this->Html->h1 = $this->Html->title;
	print '<h2>'.$name.'</h2>';
	print $form;
}