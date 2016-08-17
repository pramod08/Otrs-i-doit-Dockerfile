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
 * @package    i-doit
 * @subpackage CMDB_Category_lists
 * @author     Dennis Stuecken <dstuecken@i-doit.de> 2010-08
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_catg_cluster_service extends isys_cmdb_dao_list
{

    /**
     * Return constant of category
     *
     * @return integer
     */
    public function get_category()
    {
        return C__CATG__CLUSTER_SERVICE;
    }

    /**
     * Return constant of category type
     *
     * @return integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     * @param  array &$p_row
     */
    public function modify_row(&$p_row)
    {
        global $g_comp_template_language_manager;

        $l_dao       = isys_cmdb_dao_category_g_cluster_service::instance($this->m_db);
        $l_quickinfo = isys_factory::get_instance('isys_ajax_handler_quick_info');

        $l_members   = $l_dao->get_cluster_members($p_row["isys_catg_cluster_service_list__id"], null, C__RECORD_STATUS__NORMAL);
        $l_addresses = $l_dao->get_cluster_addresses($p_row["isys_catg_cluster_service_list__id"]);

        $p_row["hostaddresses"] = $p_row["runs_on"] = $p_row["default_server"] = isys_tenantsettings::get('gui.empty_value', '-');

        if ($p_row["isys_catg_cluster_service_list__cluster_members_list__id"] > 0)
        {
            $p_row["default_server"] = $l_dao->get_obj_name_by_id_as_string(
                isys_cmdb_dao_connection::instance($this->m_db)
                    ->get_object_id_by_connection($p_row["isys_catg_cluster_members_list__isys_connection__id"])
            );
        } // if

        if (count($l_members))
        {
            $p_row["runs_on"] = [];

            while ($l_row = $l_members->get_row())
            {
                $p_row["runs_on"][] = $l_quickinfo->get_quick_info(
                    $l_row["isys_obj__id"],
                    _L($l_dao->get_objtype_name_by_id_as_string($l_row["isys_obj__isys_obj_type__id"])) . " >> " . $l_row["isys_obj__title"],
                    C__LINK__OBJECT
                );
            } // while
        } // if

        if (count($l_addresses))
        {
            $p_row["hostaddresses"] = [];

            while ($l_row = $l_addresses->get_row())
            {
                $p_row["hostaddresses"][] = $l_row["isys_cats_net_ip_addresses_list__title"];
            } // while
        } // if

        $p_row["application"] = $l_quickinfo->get_quick_info(
            $p_row["isys_connection__isys_obj__id"],
            $l_dao->get_obj_name_by_id_as_string($p_row["isys_connection__isys_obj__id"]),
            C__LINK__OBJECT
        );
    } // function

    /**
     * Returns array with table headers.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "application"              => "LC__CMDB__CATG__CLUSTER_SERVICE__SERVICE",
            "isys_cluster_type__title" => "Cluster " . _L("LC__CMDB__CATG__CLUSTER_SERVICE__TYPE"),
            "runs_on"                  => "LC__CMDB__CATG__CLUSTER_SERVICE__RUNS_ON",
            "default_server"           => "LC__CMDB__CATG__CLUSTER_SERVICE__DEFAULT_SERVER",
            "hostaddresses"            => "LC__CMDB__CATG__CLUSTER_SERVICE__HOST_ADDRESSES"

        ];
    } // function
} // class