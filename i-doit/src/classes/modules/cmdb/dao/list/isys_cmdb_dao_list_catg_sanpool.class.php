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
 * DAO: Global category sanpool
 *
 * @package    i-doit
 * @subpackage CMDB_Category_lists
 * @author     Dennis Stuecken - 09-2009
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_catg_sanpool extends isys_cmdb_dao_list
{
    /**
     * Return constant of category.
     *
     * @return  integer
     */
    public function get_category()
    {
        return C__CATG__LDEV_SERVER;
    } // function

    /**
     * Return constant of category type
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    } // function

    /**
     *
     * @param   string  $p_table
     * @param   integer $p_object_id
     * @param   integer $p_cRecStatus
     *
     * @return  isys_component_dao_result
     */
    public function get_result($p_table = null, $p_object_id, $p_cRecStatus = null)
    {
        return isys_cmdb_dao_category_g_sanpool::instance($this->m_db)
            ->get_data(null, $p_object_id, "", null, (empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus));
    } // function

    /**
     * Row modification.
     *
     * @param  array &$p_arrRow
     */
    public function modify_row(&$p_arrRow)
    {
        $l_client_list = '';
        $l_res         = isys_cmdb_dao_category_g_ldevclient::instance($this->m_db)
            ->get_clients($p_arrRow["isys_catg_sanpool_list__id"]);

        if (count($l_res))
        {
            $l_client_list = [];

            while ($l_row = $l_res->get_row())
            {
                $l_client_list[] = $l_row["isys_obj__title"] . ' >> ' . $l_row["isys_catg_ldevclient_list__title"];
            } // while
        } // if

        $p_arrRow["clients"]                          = $l_client_list;
        $p_arrRow["isys_catg_sanpool_list__capacity"] = isys_convert::memory(
                $p_arrRow["isys_catg_sanpool_list__capacity"],
                $p_arrRow["isys_catg_sanpool_list__isys_memory_unit__id"],
                C__CONVERT_DIRECTION__BACKWARD
            ) . " " . $p_arrRow["isys_memory_unit__title"];
    } // function

    /**
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "isys_catg_sanpool_list__title"    => "LC__CATD__DRIVE_TITLE",
            "isys_catg_sanpool_list__lun"      => "LC__CATD__SANPOOL_LUN",
            "isys_catg_sanpool_list__capacity" => "LC__CATD__DRIVE_CAPACITY",
            "clients"                          => "LC__CMDB__CATG__LDEV_CLIENT"
        ];
    } // function
} // class