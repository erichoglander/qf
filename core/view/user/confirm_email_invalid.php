<?php
$this->Html->title = t("Invalid url");
$this->Html->h1 = $this->Html->title;
print '<p>'.t("The specified link is invalid. Maybe it has already been used or has expired.").'</p>';