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
 * CMDB Status Handler.
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stuecken <dstuecken@synetics.de>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */

// ITIL Status.
$l_status   = [0 => ''];
$l_dao_cmdb = new isys_cmdb_dao_status($g_comp_database);

$l_status_dao = $l_dao_cmdb->get_cmdb_status();

while ($l_row = $l_status_dao->get_row())
{
    $l_status[$l_row["isys_cmdb_status__id"]] = $g_comp_template_language_manager->get($l_row["isys_cmdb_status__title"]);
} // while

$g_comp_template->assign("www_dir", $g_config["www_dir"])
    ->assign("cmdb_status", $l_status);
?>