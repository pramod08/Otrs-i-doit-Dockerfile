--
-- i-doit system dump for version 1.7.3
-- created with: mysqldump  Ver 10.15 Distrib 10.0.23-MariaDB, for debian-linux-gnu (x86_64)
-- at: Di 26. Jul 16:20:36 CEST 2016
--
-- For manual installations you need to insert your tenant connection info into
-- table isys_mandator in order to connect to a tenant.
--
-- For example: (idoit, idoit = user, pass; idoit_data = tenant db)
--
-- INSERT INTO isys_mandator 
--      VALUES(1, 'Mandator (DE)', 
--		'Mandator (DE)', 'cache_mandator', 'default', 'localhost', 3306, 'idoit_data', 
--       'idoit', 'idoit', NULL, 1, 1);
--

SET FOREIGN_KEY_CHECKS=0;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `isys_const_get` (
  `isys_const_get__id` int(11) NOT NULL AUTO_INCREMENT,
  `isys_const_get__title` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `isys_const_get__description` text COLLATE utf8_unicode_ci,
  `isys_const_get__value` text COLLATE utf8_unicode_ci,
  `isys_const_get__quoted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`isys_const_get__id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `isys_const_get` VALUES (1,'C__CMDB__GET__VIEWMODE',NULL,'viewMode',1),(2,'C__CMDB__GET__TREEMODE',NULL,'tvMode',1),(3,'C__CMDB__GET__OBJECTGROUP',NULL,'objGroupID',1),(4,'C__CMDB__GET__OBJECTTYPE',NULL,'objTypeID',1),(5,'C__CMDB__GET__OBJECT',NULL,'objID',1),(6,'C__CMDB__GET__CATTYPE',NULL,'catTypeID',1),(7,'C__CMDB__GET__CATG',NULL,'catgID',1),(8,'C__CMDB__GET__CATS',NULL,'catsID',1),(9,'C__CMDB__GET__CATD',NULL,'catdID',1),(10,'C__CMDB__GET__POPUP',NULL,'popup',1),(11,'C__CMDB__GET__CAT_MENU_SELECTION',NULL,'catMenuSelection',1),(12,'C__CMDB__GET__EDITMODE',NULL,'editMode',1),(13,'C__CMDB__GET__CAT_LIST_VIEW',NULL,'catListView',1),(14,'C__CMDB__GET__CATLEVEL',NULL,'cateID',1),(15,'C__CMDB__GET__CATLEVEL_1',NULL,'cat1ID',1),(16,'C__CMDB__GET__CATLEVEL_2',NULL,'cat2ID',1),(17,'C__CMDB__GET__CATLEVEL_3',NULL,'cat3ID',1),(18,'C__CMDB__GET__CATLEVEL_4',NULL,'cat4ID',1),(19,'C__CMDB__GET__CATLEVEL_5',NULL,'cat5ID',1),(20,'C__CMDB__GET__CATLEVEL_MAX',NULL,'5',1),(21,'C__GET__FILE_MANAGER',NULL,'file_manager',1),(22,'C__GET__MODULE_ID',NULL,'moduleID',1),(23,'C__GET__NAVMODE',NULL,'navMode',1),(24,'C__GET__FILE_NAME',NULL,'file_name',1),(25,'C__GET__AJAX_REQUEST',NULL,'ajax_request_func',1);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `isys_const_system` (
  `isys_const_system__id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `isys_const_system__const` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `isys_const_system__value` int(10) unsigned DEFAULT '1',
  `isys_const_system__description` text COLLATE utf8_unicode_ci,
  `isys_const_system__store_id` int(10) unsigned NOT NULL DEFAULT '0',
  `isys_const_system__doku_cross_reference` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`isys_const_system__id`)
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `isys_const_system` VALUES (1,'C__NAVBAR_BUTTON__DELETE',5,'element of navbar',0,NULL),(3,'C__NAVMODE__NEW',1,'Navigationsmodus \"Neu\"',0,NULL),(4,'C__NAVMODE__EDIT',2,'Navigationsmodus \"Bearbeiten\"',0,NULL),(5,'C__NAVMODE__DUPLICATE',3,'Navigationsmodus \"Duplizieren\"',0,NULL),(7,'C__NAVMODE__BACK',7,'Navigationsmodus \"Zurueck\"',0,NULL),(8,'C__NAVMODE__FORWARD',9,'Navigationsmodus \"Vorwaerts\"',0,NULL),(9,'C__NAVMODE__SAVE',10,'Navigationsmodus \"Speichern\"',0,NULL),(10,'C__NAVMODE__RESET',13,'Navigationsmodus \"Zuruecksetzen\"',0,NULL),(11,'C__NAVMODE__CANCEL',14,'Navigationsmodus \"Abbrechen\"',0,NULL),(12,'C__MAX_COUNT__GET_HISTORY',5,'Maximale Eintraege in der internen Sitzungs-URL-History',0,NULL),(13,'C__CMDB_VIEWMODE__OBJECT',0,'CMDB Baum-Anzeigemodus \"Objektsicht\"',0,NULL),(14,'C__CMDB_VIEWMODE__LOCATION',0,'CMDB Baum-Anzeigemodus \"Lokationssicht\"',0,NULL),(15,'C__CMDB_TREEMODE__GCAT',1,'CMDB Baum-Datenmodus \"Globale Kategorie\"',0,NULL),(16,'C__CMDB_TREEMODE__OBJTYPE',1,'CMDB Baum-Datenmodus \"Objekttypen\"',0,NULL),(18,'C__F_POPUP__LOCATION',1,'Location-Browser',0,NULL),(19,'C__F_POPUP__DATETIME',2,'Calendar',0,NULL),(20,'C__F_POPUP__CONTACT',3,'Contact-Browser',0,NULL),(21,'C__F_POPUP__PICTURE',4,'Picture-Browser',0,NULL),(22,'C__CMDB__CATG',1,'Identifier for a global category',0,NULL),(23,'C__CMDB__CATS',2,'Identifier for a specific category',0,NULL),(24,'C__CMDB__CATD',3,'Identifier for a dynamic category',0,NULL),(25,'C__RECORD_PROPERTY__NOT_EDITABLE',1,'if value is add to __property field - the related record is NOT editable by the user',0,NULL),(26,'C__RECORD_PROPERTY__NOT_DELETABLE',2,NULL,0,NULL),(27,'C__RECORD_PROPERTY__NOT_SEARCHABLE',4,NULL,0,NULL),(28,'C__RECORD_PROPERTY__NOT_SELECTABLE',8,NULL,0,NULL),(29,'C__RECORD_PROPERTY__NOT_SHOW_IN_LIST',16,NULL,0,NULL),(38,'C__NAVMODE__UP',8,'10',0,NULL),(40,'C__NAVBAR_BUTTON__NEW',1,'element of navbar',0,NULL),(41,'C__NAVBAR_BUTTON__EDIT',2,'element of navbar',0,NULL),(43,'C__NAVBAR_BUTTON__DUPLICATE',3,'element of navbar',0,NULL),(44,'C__NAVBAR_BUTTON__BLANK',11,'element of navbar',0,NULL),(45,'C__NAVBAR_BUTTON__BACK',7,'element of navbar',0,NULL),(46,'C__NAVBAR_BUTTON__UP',8,'element of navbar',0,NULL),(47,'C__NAVBAR_BUTTON__FORWARD',9,'element of navbar',0,NULL),(48,'C__RECORD_STATUS__BIRTH',1,'stored to the __status field by insert a new record,^before switching to the Editmode to verify th data',0,NULL),(49,'C__RECORD_STATUS__NORMAL',2,'if the Data is valid and the \"SAVE\" Button is pressed the Record becomes the __status ISYS_C__RECORD_STATUS__NORMAL',0,NULL),(50,'C__RECORD_STATUS__ARCHIVED',3,'if the Record is deleted, the __STATUS is set to ISYS_REC_STATUS_ARCHIEVED a sysop can change the __STATUS for CMDB OBJ from ISYS_REC_STATUS_ARCHIEVED to C__RECORD_STATUS__NORMAL (reactivate)',0,NULL),(51,'C__RECORD_STATUS__DELETED',4,'or to C__RECORD_STATUS__DELETED',0,NULL),(54,'C__EDITMODE__ON',1,NULL,0,NULL),(55,'C__EDITMODE__OFF',0,NULL,0,NULL),(56,'C__NAVBAR_BUTTON__RECYCLE',12,NULL,0,NULL),(57,'C__CAT_LISTVIEW__OFF',0,NULL,0,NULL),(58,'C__CAT_LISTVIEW__ON',1,NULL,0,NULL),(59,'C__RECORD_STATUS__PURGE',5,'u never find a record with the __status \r\r\nC__RECORD_STATUS__PURGE because\r\r\nthis constant is used to mark for phy. delete.',0,NULL),(60,'C__MPTT__ACTION_BEGIN',1,NULL,0,NULL),(61,'C__MPTT__ACTION_END',6,NULL,0,NULL),(62,'C__MPTT__ACTION_ADD',2,NULL,0,NULL),(63,'C__MPTT__ACTION_DELETE',3,NULL,0,NULL),(64,'C__MPTT__ACTION_MOVE',4,NULL,0,NULL),(65,'C__MPTT__ROOT_NODE',1,NULL,0,NULL),(66,'C__MPTT__ACTION_UPDATE',5,'MPTT Aktion fuer Update (Actionstack)',0,NULL),(67,'C__CHECK_PERMISSION__EDIT',1,'const for permission check in complex categories like power obj etc.',0,NULL),(68,'C__CHECK_PERMISSION__DELETE',2,'const for permission check in complex categories like power obj etc.',0,NULL),(69,'C__CHECK_PERMISSION__DUPLICATE',3,'const for permission check in complex categories like power obj etc.',0,NULL),(70,'C__CHECK_PERMISSION__APPEND',4,'const for permission check in complex categories like power obj etc.',0,NULL),(71,'C__CHECK_PERMISSION__RECYCLE',5,'const for permission check in complex categories like power obj etc.',0,NULL),(72,'C__NAVMODE__ARCHIVE',4,NULL,0,NULL),(73,'C__NAVBAR_BUTTON__ARCHIVE',4,NULL,0,NULL),(74,'C__NAVMODE__PURGE',6,NULL,0,NULL),(75,'C__NAVBAR_BUTTON__PURGE',6,NULL,0,NULL),(76,'C__NAVMODE__RECYCLE',12,NULL,0,NULL),(77,'C__LINK__OBJECT',1,'Constant for building a link to cmdb object',1,'used in class isys_component_link_cmdb_generic'),(78,'C__LINK__POBJ_FEMALE_SOCKET',1,'Constant for building a link to cmdb power-object, subcategory female socket',1,'used in class isys_component_link_cmdb_generic'),(79,'C__LINK__POBJ_MALE_PLUG',1,'Constant for building a link to cmdb power-object, subcategory male plug',1,'used in class isys_component_link_cmdb_generic'),(80,'C__LINK__CATG',2,'Constant for building a link to cmdb catg list-element',1,'used in class isys_component_link_cmdb_generic'),(81,'C__CMDB__CATEGORY__POBJ_MALE_PLUG',0,'used for the pobj_brower to show \r\nthe required plugType',0,'used in \r\nclass isys_popup_browser_pobj \r\nand\r\nisys_cmdb_dao_category_s_pobj'),(82,'C__CMDB__CATEGORY__POBJ_FEMALE_SOCKET',1,'used for the pobj_brower to show \r\nthe required plugType',0,'used in \r\nclass isys_popup_browser_pobj \r\nand\r\nisys_cmdb_dao_category_s_pobj'),(83,'C__NAVMODE__PRINT',15,'Drucken',0,NULL),(84,'C__NAVBAR_BUTTON__PRINT',15,'Print',0,NULL),(85,'C__NAVMODE__DELETE',5,'',0,NULL),(86,'C__NAVMODE__JS_ACTION',16,'Ueber JS wurde ein Submit ausgefuehrt, \r\nAenderungsdaten werden im __HIDDEN Field (array) uebergeben.\r\nWird erstmals fuer den Contactbrowser verwendet.',0,'popup.js, suche nach C__NAVMODE__JS_ACTION im code'),(87,'C__RECORD_STATUS__TEMPLATE',6,'',0,NULL),(88,'C__NAVBAR_BUTTON__COMPLETE',20,'Complete one or more tasks',0,NULL),(89,'C__NAVMODE__COMPLETE',20,'Navigationsmodus \"AbschlieÃƒÆ’Ã…Â¸en\"',0,NULL),(90,'C__RECORD_STATUS__MASS_CHANGES_TEMPLATE',7,'New status for mass changes templates',0,NULL),(91,'C__NAVBAR_BUTTON__SAVE',21,'element of navbar',0,NULL),(92,'C__NAVBAR_BUTTON__CANCEL',22,'element of navbar',0,NULL),(93,'C__NAVBAR_BUTTON__QUICK_PURGE',60,'element of navbar',0,NULL),(94,'C__NAVMODE__QUICK_PURGE',60,NULL,0,NULL),(95,'C__NAVBAR_BUTTON__EXPORT_AS_CSV',17,'element of navbar',0,NULL),(96,'C__NAVMODE__EXPORT_CSV',17,'Navigationsmodus \"Export als CSV\"',0,NULL);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `isys_db_init` (
  `isys_db_init__id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `isys_db_init__key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `isys_db_init__value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`isys_db_init__id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `isys_db_init` VALUES (1,'title','i-doit 1.7.3'),(2,'revision','21703'),(3,'version','1.7.3'),(4,'type','pro');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `isys_language` (
  `isys_language__id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `isys_language__title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `isys_language__short` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `isys_language__available` int(1) NOT NULL,
  `isys_language__const` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `isys_language__sort` int(10) NOT NULL,
  PRIMARY KEY (`isys_language__id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `isys_language` VALUES (1,'All','all',0,'ISYS_LANGUAGE_ALL',0),(2,'Deutsch','de',1,'ISYS_LANGUAGE_GERMAN',1),(3,'English','en',1,'ISYS_LANGUAGE_ENGLISH',2),(4,'Portuguese','pt',1,'ISYS_LANGUAGE_PORTUGUESE',3);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `isys_licence` (
  `isys_licence__id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `isys_licence__isys_mandator__id` int(10) unsigned DEFAULT NULL,
  `isys_licence__isys_licence__id` int(10) unsigned DEFAULT NULL,
  `isys_licence__contract` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `isys_licence__type` int(10) NOT NULL,
  `isys_licence__key` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `isys_licence__data` text COLLATE utf8_unicode_ci,
  `isys_licence__expires` int(10) DEFAULT NULL,
  `isys_licence__datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`isys_licence__id`),
  KEY `isys_licence__isys_mandator__id` (`isys_licence__isys_mandator__id`),
  CONSTRAINT `isys_licence_ibfk_1` FOREIGN KEY (`isys_licence__isys_mandator__id`) REFERENCES `isys_mandator` (`isys_mandator__id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `isys_mandator` (
  `isys_mandator__id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `isys_mandator__title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `isys_mandator__description` text COLLATE utf8_unicode_ci,
  `isys_mandator__dir_cache` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `isys_mandator__dir_tpl` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `isys_mandator__db_host` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `isys_mandator__db_port` int(11) DEFAULT '3306',
  `isys_mandator__db_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `isys_mandator__db_user` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `isys_mandator__db_pass` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `isys_mandator__apikey` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `isys_mandator__sort` int(10) unsigned DEFAULT NULL,
  `isys_mandator__active` int(10) unsigned DEFAULT '1',
  PRIMARY KEY (`isys_mandator__id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `isys_report` (
  `isys_report__id` int(32) NOT NULL AUTO_INCREMENT,
  `isys_report__title` varchar(255) NOT NULL,
  `isys_report__description` text NOT NULL,
  `isys_report__query` text NOT NULL,
  `isys_report__query_row` text NOT NULL,
  `isys_report__mandator` int(10) DEFAULT NULL,
  `isys_report__user` int(10) DEFAULT NULL,
  `isys_report__datetime` datetime NOT NULL,
  `isys_report__last_edited` datetime NOT NULL,
  `isys_report__type` char(1) NOT NULL,
  `isys_report__user_specific` tinyint(1) NOT NULL DEFAULT '0',
  `isys_report__querybuilder_data` text,
  `isys_report__isys_report_category__id` int(10) unsigned DEFAULT NULL,
  `isys_report__empty_values` tinyint(1) unsigned DEFAULT '1',
  `isys_report__display_relations` tinyint(1) unsigned DEFAULT '0',
  PRIMARY KEY (`isys_report__id`),
  KEY `isys_report__isys_report_category__id` (`isys_report__isys_report_category__id`),
  CONSTRAINT `isys_report__isys_report_category__id` FOREIGN KEY (`isys_report__isys_report_category__id`) REFERENCES `isys_report_category` (`isys_report_category__id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `isys_report_category` (
  `isys_report_category__id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `isys_report_category__title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `isys_report_category__const` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `isys_report_category__description` text COLLATE utf8_unicode_ci,
  `isys_report_category__property` int(10) unsigned DEFAULT NULL,
  `isys_report_category__sort` int(10) unsigned DEFAULT NULL,
  `isys_report_category__status` int(10) unsigned NOT NULL,
  PRIMARY KEY (`isys_report_category__id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `isys_report_category` VALUES (1,'Global',NULL,NULL,NULL,0,2);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `isys_settings` (
  `isys_settings__key` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `isys_settings__value` text COLLATE utf8_unicode_ci NOT NULL,
  `isys_settings__isys_mandator__id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`isys_settings__key`,`isys_settings__isys_mandator__id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `isys_settings` VALUES ('api.authenticated-users-only','1',0),('auth.active','1',0),('barcode.type','qr',0),('cmdb.connector.suffix-schema','',0),('cmdb.limits.connector-lists-assigned_connectors','5',0),('cmdb.limits.obj-browser.objects-in-viewmode','8',0),('cmdb.limits.port-lists-layer2','5',0),('cmdb.limits.port-lists-vlans','5',0),('cmdb.limits.port-overview-default-vlan-only','0',0),('cmdb.limits.port-overview-vlans','1',0),('cmdb.object-browser.max-objects','1500',0),('cmdb.object.title.cable-prefix','',0),('cmdb.quickpurge','0',0),('cmdb.unique.hostname','0',0),('cmdb.unique.ip-address','0',0),('cmdb.unique.layer-2-net','0',0),('cmdb.unique.object-title','0',0),('email.template.maintenance','Your maintenance contract: %s timed out.\n\n<strong>Contract information</strong>:\nStart: %s\nEnd: %s\nSupport-Url: %s\nContract-Number: %s\nCustomer-Number: %s',0),('email.template.password','',0),('gui.empty_value','-',0),('gui.empty_values','-',0),('gui.forum-link','0',0),('gui.wiki-url','',0),('gui.wysiwyg','1',0),('gui.wysiwyg-all-controls','1',0),('import.object.keep-status','0',0),('key','value',0),('ldap.debug','1',0),('ldap.default-group','14',0),('logbook.changes','1',0),('logging.cmdb.import','0',0),('logging.system.api','1',0),('login.tenantlist.sortby','isys_mandator__sort',0),('maxlenghts.location.objects','16',0),('maxlenghts.location.path','40',0),('maxlength.dialog_plus','110',0),('maxlength.location.objects','16',0),('maxlength.object.lists','55',0),('maxlengths.dialog_plus','110',0),('maxlengths.object.lists','55',0),('mysql.unbuffered-queries','0',0),('proxy.active','0',0),('proxy.host','',0),('proxy.password','admin',0),('proxy.port','',0),('proxy.username','admin',0),('qrcode.config','',0),('reports.browser-url','',0),('security.passwort.minlength','4',0),('session.sso.active','0',0),('session.sso.language','de',0),('session.sso.mandator-id','2',0),('session.time','300',0),('system.devmode','0',0),('system.email.connection-timeout','',0),('system.email.from','',0),('system.email.name','',0),('system.email.port','',0),('system.email.smtp-host','',0),('system.email.smtpdebug','0',0),('system.email.subject-prefix','',0),('system.last-change','1469542835',0),('system.show-proc-time','0',0),('system.timezone','Europe/Berlin',0),('tts.rt.queues','',0);
