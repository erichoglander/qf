<?php
define("DOC_ROOT", str_replace("/core/inc", "", __DIR__));

require_once(DOC_ROOT."/core/inc/constants.php");
require_once(DOC_ROOT."/core/inc/functions.php");
require_once(DOC_ROOT."/core/entity/entity.php");
require_once(DOC_ROOT."/core/controller/controller.php");
require_once(DOC_ROOT."/core/model/model.php");

if (file_exists(DOC_ROOT."/extend/inc/functions.php"))
	require_once(DOC_ROOT."/extend/inc/functions.php");

$ControllerFactory = new ControllerFactory();