<?php
/**
 * i-doit
 *
 * Basic configuration
 *
 * @package     i-doit
 * @subpackage  General
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */

/**
 * @desc Database configuration
 * ---------------------------------------------------------------------------
 *       This configuration is for the system database. Don't forget to use
 *       mySQL with the InnoDB table-driver. Only TCP/IP Hosts are
 *       supported here, no UNIX sockets!
 */
$g_db_system = array(
	"type" => 'mysqli',
	"host" => "%config.db.host%",
	"port" => "%config.db.port%",
	"user" => "%config.db.username%",
	"pass" => "%config.db.password%",
	"name" => "%config.db.name%"
);

/**
  * This login is used for the i-doit administration gui. Note that an empty password will not work.
  * Leave the password empty to disable the admin center.
  *
  *  Syntax: "username" => "password"
  */
 $g_admin_auth = array(
 	"%config.adminauth.username%" => "%config.adminauth.password%",
 );