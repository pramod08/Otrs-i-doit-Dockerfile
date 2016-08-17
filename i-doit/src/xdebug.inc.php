<?php
/**
 * i-doit - Documentation and CMDB solution for IT environments
 *
 * This file is part of the i-doit framework. Modify at your own risk.
 *
 * Please visit http://www.i-doit.com/license for a full copyright and license information.
 *
 * @version     1.7.3
 * @package     i-doit
 * @author      synetics GmbH
 * @copyright   synetics GmbH
 * @url         http://www.i-doit.com
 * @license     http://www.i-doit.com/license
 */
/**
 * i-doit
 *
 * XDebug config
 *
 * @package    i-doit
 * @subpackage General
 * @author     Dennis Stuecken <dstuecken@i-doit.de>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */

//to start a debug session, call: index.php?XDEBUG_SESSION_START=1
//that will set a cookie with a lifetime of one hour.

ini_set("xdebug.collect_params", "3");
ini_set("xdebug.remote_enable", "1");

ini_set("xdebug.var_display_max_depth", "25");
ini_set("xdebug.var_display_max_data", "5000");
ini_set("xdebug.var_display_max_children", "100");
ini_set("xdebug.overload_var_dump", "0");

ini_set("xdebug.extended_info", "0");
ini_set("xdebug.max_nesting_level", "1000");

ini_set("xdebug.trace_format", "1");
ini_set("xdebug.trace_options", "0");

ini_set("xdebug.file_link_format", "txmt://open?url=file://%f&line=%1");

/*
 * xdebug_start_trace($g_absdir . '/log/idoit-trace', XDEBUG_TRACE_COMPUTERIZED);
 */