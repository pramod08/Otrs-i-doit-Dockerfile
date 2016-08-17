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
class isys_cmdb_dao_list_catg_cmk_host_service extends isys_cmdb_dao_list implements isys_cmdb_dao_list_interface
{
    /**
     * Return constant of category.
     *
     * @return  integer
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_category()
    {
        return C__CATG__CMK_HOST_SERVICE;
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
        if ($p_row['isys_catg_cmk_host_service_list__application__id'] > 0)
        {
            $l_dao      = isys_cmdb_dao_category_g_application::instance($this->get_database_component());
            $l_software = $l_dao->get_data($p_row['isys_catg_cmk_host_service_list__application__id'], null, null, null, C__RECORD_STATUS__NORMAL)
                ->get_row();

            $p_row['software_assignment'] = _L($l_software['isys_obj_type__title']) . ' >> ' . $l_software['isys_obj__title'];
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
            "isys_catg_cmk_host_service_list__id"      => "ID",
            "software_assignment"                      => "LC__CATG__CMK_SERVICE__SOFTWARE_ASSIGNMENT",
            "isys_catg_cmk_host_service_list__service" => "LC__CATG__CMK_SERVICE__CHECK_MK_SERVICES"
        ];
    } // function
} // class