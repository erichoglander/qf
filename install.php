<?php
/**
 * Installation file
 *
 * Sets up database, adds translations, and sets up admin account
 * Ex: php install.php
 * 
 * @author Eric Höglander
 */

require_once("core/inc/bootstrap.php");

if (!IS_CLI)
  die("Must be run through cli.");

// Database information
while (!$Db->connected) {
  print "Enter database host [localhost]: ";
  $db_host = trim(fgets(STDIN));
  if (!$db_host)
    $db_host = "localhost";
  print "Enter database username: ";
  $db_user = trim(fgets(STDIN));
  print "Enter database password: ";
  system("stty -echo");
  $db_pass = trim(fgets(STDIN));
  system("stty echo");
  print PHP_EOL;
  print "Enter database name: ";
  $db_name = trim(fgets(STDIN));
  if (!file_exists("extend/inc"))
    mkdir(DOC_ROOT."/extend/inc", 0775, true);
  $db = '<?php
$database = [
  "host" => "'.$db_host.'",
  "user" => "'.$db_user.'",
  "pass" => "'.$db_pass.'",
  "db" => "'.$db_name.'",
];';
  $Db->connect($db_user, $db_pass, $db_name, $db_host);
  if ($Db->connected)
    file_put_contents(DOC_ROOT."/extend/inc/database.php", $db);
  else
    print "Could not connect to database.".PHP_EOL;
}

if (!file_exists(DOC_ROOT."/extend/.gitignore"))
  file_put_contents(DOC_ROOT."/extend/.gitignore", "inc/database.php");

if ($Db->numRows("SHOW TABLES LIKE 'alias'"))
  die("System is already installed.\n");

print "Enter admin e-mail: ";
$email = trim(fgets(STDIN));

print "Enter admin password: ";
system("stty -echo");
$pass = trim(fgets(STDIN));
system("stty echo");
print PHP_EOL;

$sql = "
--
-- Table structure for table `alias`
--

CREATE TABLE IF NOT EXISTS `alias` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `status` tinyint(4) DEFAULT '1',
  `created` int(10) unsigned DEFAULT NULL,
  `updated` int(10) unsigned DEFAULT NULL,
  `lang` varchar(2) COLLATE utf8_swedish_ci DEFAULT NULL,
  `path` varchar(256) COLLATE utf8_swedish_ci DEFAULT NULL,
  `alias` varchar(256) COLLATE utf8_swedish_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `path` (`path`(255)),
  KEY `alias` (`alias`(255)),
  KEY `status` (`status`),
  KEY `lang` (`lang`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE IF NOT EXISTS `cache` (
  `name` varchar(128) COLLATE utf8_swedish_ci NOT NULL,
  `data` longblob,
  `expire` int(10) unsigned NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `content`
--

CREATE TABLE IF NOT EXISTS `content` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sid` int(11) DEFAULT NULL,
  `lang` varchar(2) COLLATE utf8_swedish_ci DEFAULT NULL,
  `title` varchar(256) COLLATE utf8_swedish_ci NOT NULL,
  `data` longblob NOT NULL,
  `config` longblob NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created` int(10) unsigned NOT NULL DEFAULT '0',
  `updated` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `lang` (`lang`),
  KEY `sid` (`sid`),
  KEY `status` (`status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `file`
--

CREATE TABLE IF NOT EXISTS `file` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) COLLATE utf8_swedish_ci NOT NULL,
  `uri` varchar(255) COLLATE utf8_swedish_ci NOT NULL,
  `extension` varchar(12) COLLATE utf8_swedish_ci DEFAULT NULL,
  `dir` enum('public','private') COLLATE utf8_swedish_ci NOT NULL DEFAULT 'public',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `created` int(10) unsigned NOT NULL DEFAULT '0',
  `updated` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `l10n_string`
--

CREATE TABLE IF NOT EXISTS `l10n_string` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sid` int(10) unsigned DEFAULT NULL,
  `lang` varchar(2) COLLATE utf8_swedish_ci NOT NULL,
  `string` longtext COLLATE utf8_swedish_ci NOT NULL,
  `input_type` enum('code','manual','import') COLLATE utf8_swedish_ci NOT NULL DEFAULT 'code',
  `created` int(10) unsigned NOT NULL DEFAULT '0',
  `updated` int(10) unsigned DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `sid` (`sid`),
  KEY `lang` (`lang`),
  KEY `status` (`status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `language`
--

CREATE TABLE IF NOT EXISTS `language` (
  `lang` varchar(2) COLLATE utf8_swedish_ci NOT NULL,
  `title` varchar(64) COLLATE utf8_swedish_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`lang`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;

--
-- Dumping data for table `language`
--

INSERT INTO `language` (`lang`, `title`, `status`) VALUES
('en', 'English', 1),
('sv', 'Svenska', 1);

-- --------------------------------------------------------

--
-- Table structure for table `log`
--

CREATE TABLE IF NOT EXISTS `log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `type` enum('info','success','warning','error') COLLATE utf8_swedish_ci NOT NULL DEFAULT 'info',
  `category` varchar(64) COLLATE utf8_swedish_ci NOT NULL,
  `text` longtext COLLATE utf8_swedish_ci NOT NULL,
  `data` longblob,
  `created` int(10) unsigned NOT NULL,
  `ip` varchar(64) COLLATE utf8_swedish_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `created` (`created`),
  KEY `user_id` (`user_id`),
  KEY `category` (`category`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `login_attempt`
--

CREATE TABLE IF NOT EXISTS `login_attempt` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `created` int(10) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(64) COLLATE utf8_swedish_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ip` (`ip`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `redirect`
--

CREATE TABLE IF NOT EXISTS `redirect` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `created` int(10) unsigned NOT NULL DEFAULT '0',
  `updated` int(10) unsigned NOT NULL DEFAULT '0',
  `lang` varchar(2) COLLATE utf8_swedish_ci DEFAULT NULL,
  `source` varchar(256) COLLATE utf8_swedish_ci NOT NULL,
  `target` varchar(512) COLLATE utf8_swedish_ci NOT NULL,
  `code` enum('301','302','303','307') COLLATE utf8_swedish_ci NOT NULL DEFAULT '301',
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `source` (`source`(255)),
  KEY `lang` (`lang`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE IF NOT EXISTS `role` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(64) COLLATE utf8_swedish_ci NOT NULL,
  `machine_name` varchar(32) COLLATE utf8_swedish_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `machine_name` (`machine_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`id`, `title`, `machine_name`) VALUES
(1, 'Administratör', 'administrator');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_swedish_ci NOT NULL,
  `email` varchar(128) COLLATE utf8_swedish_ci DEFAULT NULL,
  `lang` varchar(2) COLLATE utf8_swedish_ci DEFAULT NULL,
  `status` tinyint(4) DEFAULT '1',
  `created` int(11) NOT NULL DEFAULT '0',
  `updated` int(11) NOT NULL DEFAULT '0',
  `login` int(11) NOT NULL DEFAULT '0',
  `salt` varchar(128) COLLATE utf8_swedish_ci DEFAULT NULL,
  `pass` varchar(130) COLLATE utf8_swedish_ci DEFAULT NULL,
  `reset` varchar(128) COLLATE utf8_swedish_ci DEFAULT NULL,
  `reset_time` int(10) unsigned DEFAULT '0',
  `email_confirmation` varchar(128) COLLATE utf8_swedish_ci DEFAULT NULL,
  `email_confirmation_time` int(10) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `email` (`email`),
  KEY `status` (`status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `user_role`
--

CREATE TABLE IF NOT EXISTS `user_role` (
  `user_id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `role_id` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `variable`
--

CREATE TABLE IF NOT EXISTS `variable` (
  `name` varchar(128) COLLATE utf8_swedish_ci NOT NULL,
  `data` longblob,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `log`
--
ALTER TABLE `log`
  ADD CONSTRAINT `log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

--
-- Constraints for table `login_attempt`
--
ALTER TABLE `login_attempt`
  ADD CONSTRAINT `login_attempt_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

--
-- Constraints for table `user_role`
--
ALTER TABLE `user_role`
  ADD CONSTRAINT `user_role_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_role_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `l10n_string`
--
ALTER TABLE `l10n_string`
  ADD CONSTRAINT `l10n_string_ibfk_1` FOREIGN KEY (`sid`) REFERENCES `l10n_string` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
";

print "Constructing database... ";
if (!$Db->query($sql))
  die("Failed\n");
print "OK\n";

$Variable = newClass("Variable", $Db);
$Variable->set("core_update", 8);

print "Installing updates...\n";
$doc = $ControllerFactory->executeUri("updater/update");
print $doc.PHP_EOL;

print t("Creating admin account...")." ";
$User = newClass("User_Entity", $Db);
$User->set("name", "admin");
$User->set("email", $email);
$User->set("pass", $pass);
if (!$User->save())
  die(t("Failed").PHP_EOL);
print t("OK").PHP_EOL;

$Db->insert("user_role", ["user_id" => 1, "role_id" => 1]);

print t("Installation complete").PHP_EOL;