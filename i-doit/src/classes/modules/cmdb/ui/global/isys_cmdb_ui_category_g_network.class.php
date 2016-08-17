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
 * Network UI
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @author     Dennis Stücken <dstuecken@i-doit.org>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_network extends isys_cmdb_ui_category_global
{

    /**
     * @param isys_cmdb_dao_category $p_cat
     *
     * @author Niclas Potthast <npotthast@i-doit.org> - 2006-03-15
     * @desc   show the detail-template for category network (interfaces)
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        isys_cmdb_ui_category_g_network_interface::process($p_cat);
    }

    /**
     * @todo   : implement new network single value category
     *
     * @param isys_cmdb_dao_category $p_cat
     *
     * @author Dennis Stücken <dstuecken@i-doit.org>
     * @desc   show the detail-template for category network (interfaces)
     */
    public function process_new(isys_cmdb_dao_category $p_cat)
    {

        if (!empty($_GET[C__CMDB__GET__CATLEVEL]))
        {
            $this->set_template("catg__interface_p.tpl");
            $l_iface = new isys_cmdb_ui_category_g_network_interface($p_cat);

            return $l_iface->process($p_cat);
        }

        $l_get = $_GET;

        /* ---------------------------------------------------------------------------------------------- */
        /* Assign Interfaces */
        /* ---------------------------------------------------------------------------------------------- */
        $l_iface_dao = new isys_cmdb_dao_category_g_network_interface($this->m_catdao->get_database_component());
        $l_ifaces    = $l_iface_dao->get_data(null, $_GET[C__CMDB__GET__OBJECT]);

        $l_get[C__CMDB__GET__CATG] = C__CMDB__SUBCAT__NETWORK_INTERFACE_P;

        $i = 0;
        while ($l_iface = $l_ifaces->get_row())
        {
            $l_iface_array[++$i]       = $l_iface;
            $l_iface_array[$i]["link"] = isys_glob_build_ajax_url(C__FUNC__AJAX__CONTENT_BY_OBJECT, $l_get, C__CMDB__GET__CATG, $l_iface["isys_catg_netp_list__id"]);
        }
        $this->m_template->assign("ifaces", $l_iface_array);

        /* ---------------------------------------------------------------------------------------------- */
        /* Assign Ports */
        /* ---------------------------------------------------------------------------------------------- */
        $l_port_dao = new isys_cmdb_dao_category_g_network_port($this->m_catdao->get_database_component());
        $l_ports    = $l_port_dao->get_ports($_GET[C__CMDB__GET__OBJECT]);

        $l_get[C__CMDB__GET__CATG] = C__CMDB__SUBCAT__NETWORK_PORT;

        $i = 0;
        while ($l_port = $l_ports->get_row())
        {
            $l_port_array[++$i]       = $l_port;
            $l_port_array[$i]["link"] = isys_glob_build_ajax_url(C__FUNC__AJAX__CONTENT_BY_OBJECT, $l_get, C__CMDB__GET__CATG, $l_port["isys_catg_port_list__id"]);
        }
        $this->m_template->assign("ports", $l_port_array);

        /* ---------------------------------------------------------------------------------------------- */
        /* Assign IP Addresses */
        /* ---------------------------------------------------------------------------------------------- */
        $l_ip_dao = new isys_cmdb_dao_category_g_ip($this->m_catdao->get_database_component());
        $l_ips    = $l_ip_dao->get_ips_by_obj_id($_GET[C__CMDB__GET__OBJECT]);

        $l_get[C__CMDB__GET__CATG] = C__CATG__IP;

        $i = 0;
        while ($l_ip = $l_ips->get_row())
        {
            $l_ip_array[++$i]       = $l_ip;
            $l_ip_array[$i]["link"] = isys_glob_build_ajax_url(C__FUNC__AJAX__CONTENT_BY_OBJECT, $l_get, C__CMDB__GET__CATG, $l_ip["isys_catg_ip_list__id"]);
        }
        $this->m_template->assign("ips", $l_ip_array);

        /* ---------------------------------------------------------------------------------------------- */
        /* IP rules */
        /* ---------------------------------------------------------------------------------------------- */
        $l_rules["C__CATP__IP__ASSIGN"]["p_strTable"] = "isys_ip_assignment";
        $l_rules["C__CATP__IP__ACTIVE"]["p_arData"]   = serialize(get_smarty_arr_YES_NO());
        $l_rules["C__CATP__IP__PRIMARY"]["p_arData"]  = serialize(get_smarty_arr_YES_NO());

        /* ---------------------------------------------------------------------------------------------- */
        /* Port rules */
        /* ---------------------------------------------------------------------------------------------- */
        $l_rules["C__CATG__PORT__ACTIVE"]["p_arData"]        = serialize(get_smarty_arr_YES_NO());
        $l_rules["C__CATG__PORT__SETTINGS"]["p_bLinklist"]   = true;
        $l_rules["C__CATG__PORT__TYPE"]["p_strTable"]        = "isys_port_type";
        $l_rules["C__CATG__PORT__PLUG"]["p_strTable"]        = "isys_plug_type";
        $l_rules["C__CATG__PORT__CABLE"]["p_strTable"]       = "isys_catg_port_list__cable_name";
        $l_rules["C__CATG__PORT__NEGOTIATION"]["p_strTable"] = "isys_port_negotiation";
        $l_rules["C__CATG__PORT__DUPLEX"]["p_strTable"]      = "isys_port_duplex";
        $l_rules["C__CATG__PORT__SPEED"]["p_strTable"]       = "isys_port_speed";
        $l_rules["C__CATG__PORT__STANDARD"]["p_strTable"]    = "isys_port_standard";
        $l_rules["C__CATG__PORT__ACTIVE"]["p_arData"]        = $l_arYesNo;
        $l_rules["C__CATG__PORT__COUNT"]["p_strValue"]       = 1;

        $l_ui_port                            = new isys_cmdb_ui_category_g_network_port($this->m_template);
        $l_rules["C__CATG__PORT__IP_ADDRESS"] = $l_ui_port->get_linklist($_GET[C__CMDB__GET__OBJECT], null);

        /* ---------------------------------------------------------------------------------------------- */
        $this->m_template->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
        $this->m_template->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1");
        /* ---------------------------------------------------------------------------------------------- */

    }

    public function __construct(isys_component_template &$p_template)
    {
        parent::__construct($p_template);
        //$this->set_template("catg__network.tpl");
        $this->set_template("catg__interface_p.tpl");
    }
}

?>