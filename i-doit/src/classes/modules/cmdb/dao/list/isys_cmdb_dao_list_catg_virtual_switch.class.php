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
 * DAO: Gloabl category Hostadapter (HBA)
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Bluemer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_catg_virtual_switch extends isys_cmdb_dao_list
{
    /**
     * Return constant of category.
     *
     * @return  integer
     */
    public function get_category()
    {
        return C__CATG__VIRTUAL_SWITCH;
    } // function

    /**
     * Return constant of category type
     *
     * @return integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    } // function

    public function modify_row(&$p_arRow)
    {
        $l_pgs = isys_cmdb_dao_category_g_virtual_switch::instance($this->m_db)
            ->get_port_groups($p_arRow["isys_catg_virtual_switch_list__id"]);

        $p_arRow["port_groups"] = isys_tenantsettings::get('gui.empty_value', '-');

        if (count($l_pgs))
        {
            $p_arRow["port_groups"] = [];

            while ($l_row = $l_pgs->get_row())
            {
                $p_arRow["port_groups"][] = $l_row["isys_virtual_port_group__title"];
            } // while
        } // if
    } // function

    /**
     * Returns array with table headers
     *
     * @return array
     * @global $g_comp_template_language_manager
     */
    public function get_fields()
    {
        global $g_comp_template_language_manager;

        return [
            "isys_catg_virtual_switch_list__title" => $g_comp_template_language_manager->get("LC__CMDB__CATG__TITLE"),
            "port_groups"                          => $g_comp_template_language_manager->get("LC__CMDB__CATG__VSWITCH__PORT_GROUPS")
        ];
    } // function
} // class