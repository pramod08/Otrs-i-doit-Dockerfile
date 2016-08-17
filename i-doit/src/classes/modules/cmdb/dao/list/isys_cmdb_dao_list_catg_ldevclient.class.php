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
 * List DAO: Gloabl category 'drive'.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_catg_ldevclient extends isys_cmdb_dao_list
{
    /**
     * Return constant of category.
     *
     * @return  integer
     */
    public function get_category()
    {
        return C__CATG__LDEV_CLIENT;
    } // function

    /**
     * Return constant of category type.
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
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_result($p_table = null, $p_object_id = null, $p_cRecStatus = null)
    {
        $l_sql = 'SELECT *
			FROM isys_catg_ldevclient_list
			LEFT JOIN isys_catg_sanpool_list ON isys_catg_sanpool_list__id = isys_catg_ldevclient_list__isys_catg_sanpool_list__id
			WHERE isys_catg_ldevclient_list__isys_obj__id = ' . $this->convert_sql_id($p_object_id);

        if ($p_object_id !== null)
        {
            $l_sql .= ' AND isys_catg_ldevclient_list__isys_obj__id = ' . $this->convert_sql_id($p_object_id);
        } // if

        $l_cRecStatus = $p_cRecStatus ?: $this->get_rec_status();

        if ($l_cRecStatus !== null && $l_cRecStatus > 0)
        {
            $l_sql .= ' AND isys_catg_ldevclient_list__status = ' . $this->convert_sql_int($l_cRecStatus);
        } // if

        return $this->retrieve($l_sql . ';');
    } // function

    /**
     * Exchange column to create individual links in columns.
     *
     * @param   array &$p_arrRow
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function modify_row(&$p_arrRow)
    {
        if ($p_arrRow['isys_catg_sanpool_list__capacity'] > 0)
        {
            if ($p_arrRow['isys_catg_sanpool_list__isys_memory_unit__id'] > 0)
            {
                $l_unit_name = isys_factory_cmdb_dialog_dao::get_instance('isys_memory_unit', $this->m_db)
                    ->get_data($p_arrRow['isys_catg_sanpool_list__isys_memory_unit__id']);

                $p_arrRow['storage_capacity'] = isys_convert::memory(
                        $p_arrRow['isys_catg_sanpool_list__capacity'],
                        $p_arrRow['isys_catg_sanpool_list__isys_memory_unit__id'],
                        C__CONVERT_DIRECTION__BACKWARD
                    ) . ' ' . _L($l_unit_name['isys_memory_unit__title']);
            }
            else
            {
                $p_arrRow['storage_capacity'] = number_format($p_arrRow['isys_catg_sanpool_list__capacity'], 2, ',', '');
            } // if
        } // if
    } // function

    /**
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_fields()
    {
        return [
            'isys_catg_ldevclient_list__title' => 'LC__CATG__LDEVCLIENT_TITLE',
            'isys_catg_sanpool_list__title'    => 'LC__CMDB__CATG__UI_ASSIGNED_UI',
            'storage_capacity'                 => _L('LC__CATG__STORAGE_CAPACITY') . ' (' . _L('LC__CMDB__CATG__LDEV_SERVER') . ')',
            'isys_catg_sanpool_list__lun'      => 'LC__CATD__SANPOOL_LUN'
        ];
    } // function
} // class