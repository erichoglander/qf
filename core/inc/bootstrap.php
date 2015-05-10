<?php
session_start();
define("DOC_ROOT", str_replace("/core/inc", "", __DIR__));

require_once(DOC_ROOT."/core/inc/constants.php");
require_once(DOC_ROOT."/core/inc/functions.php");
require_once(DOC_ROOT."/core/inc/country_list.php");
require_once(DOC_ROOT."/core/entity/entity.php");
require_once(DOC_ROOT."/core/entity/i18n_entity.php");
require_once(DOC_ROOT."/core/controller/controller.php");
require_once(DOC_ROOT."/core/model/model.php");

if (file_exists(DOC_ROOT."/extend/inc/functions.php"))
	require_once(DOC_ROOT."/extend/inc/functions.php");

$classes = glob(DOC_ROOT."/core/class/*");
foreach ($classes as $class)
	require_once($class);

$ControllerFactory = new ControllerFactory();