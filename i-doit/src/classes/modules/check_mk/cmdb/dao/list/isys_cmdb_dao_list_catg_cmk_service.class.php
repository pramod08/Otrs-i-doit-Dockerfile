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
 * DAO: list DAO for Check_MK.
 *
 * @package     Modules
 * @subpackage  Check_MK
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.4.0
 */
class isys_cmdb_dao_list_catg_cmk_service extends isys_cmdb_dao_list implements isys_cmdb_dao_list_interface
{
    /**
     * Return constant of category.
     *
     * @return  integer
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_category()
    {
        return C__CATG__CMK_SERVICE;
    } // function

    /**
     * Return constant of category type.
     *
     * @return  integer
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    } // function

    /**
     * Method which helps modifying each row.
     *
     * @param   array $p_row
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function modify_row(&$p_row)
    {
        if ($p_row['isys_catg_cmk_service_list__host'] > 0)
        {
            $p_row['isys_catg_cmk_service_list__host'] = isys_monitoring_dao_hosts::instance($this->m_db)
                ->get_data($p_row['isys_catg_cmk_service_list__host'], C__MONITORING__TYPE_LIVESTATUS)
                ->get_row_value('isys_monitoring_hosts__title');
        } // if
    } // function

    /**
     * Method for retrieving the displayable fields.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_fields()
    {
        return [
            "isys_catg_cmk_service_list__id"      => "ID",
            "isys_catg_cmk_service_list__host"    => "LC__MODULE__CHECK_MK__HOST",
            "isys_catg_cmk_service_list__service" => "LC__CATG__CMK_SERVICE__CHECK_MK_SERVICES"
        ];
    } // function
} // class