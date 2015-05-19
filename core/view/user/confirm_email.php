<?php
if ($status == 0) {
	$this->Html->title = t("Invalid url");
	$this->Html->h1 = $this->Html->title;
	print '<p>'.t("The specified link is invalid. Maybe it has already been used or has expired.").'</p>';
}
else if ($status == 1) {
	$this->Html->title = t("E-mail address confirmed");
	$this->Html->h1 = $this->Html->title;
	print '<p>'.t("Your e-mail address has been confirmed. Your account is now fully activiated.").'</p>';
}