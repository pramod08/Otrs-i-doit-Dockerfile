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
 * CMDB List DAO: Global category for VRRP members.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @since       1.7
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_catg_vrrp_member extends isys_cmdb_dao_list implements isys_cmdb_dao_list_interface
{
    /**
     * Return constant of category
     *
     * @return  integer
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_category()
    {
        return C__CATG__VRRP_MEMBER;
    } // function

    /**
     * Return constant of category type
     *
     * @return  integer
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    } // function

    /**
     * Modify row method.
     *
     * @param   array $p_row
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function modify_row(&$p_row)
    {
        if ($p_row['isys_catg_log_port_list__id'] > 0)
        {
            $l_object = isys_cmdb_dao::instance($this->get_database_component())
                ->get_object($p_row['isys_catg_log_port_list__isys_obj__id'])
                ->get_row();

            $p_row['connected_obj'] = isys_factory::get_instance('isys_ajax_handler_quick_info')
                ->get_quick_info(
                    $p_row['isys_catg_log_port_list__isys_obj__id'],
                    _L($l_object['isys_obj_type__title']) . ' >> ' . _L($l_object['isys_obj__title']),
                    C__LINK__OBJECT
                );

            $p_row['isys_catg_log_port_list__title'] = isys_factory::get_instance('isys_ajax_handler_quick_info')
                ->get_quick_info(
                    $p_row['isys_catg_log_port_list__isys_obj__id'],
                    _L($p_row['isys_catg_log_port_list__title']),
                    C__LINK__CATG,
                    false,
                    [C__CMDB__GET__CATG => C__CMDB__SUBCAT__NETWORK_INTERFACE_L]
                );
        } // if
    } // function

    /**
     * Method for retrieving the field-names.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_fields()
    {
        return [
            'connected_obj'                  => 'LC__CATG__VRRP_MEMBER__VRRP_MEMBER',
            'isys_catg_log_port_list__title' => 'LC__CATG__VRRP_MEMBER__LOG_PORT',
        ];
    } // function
} // class