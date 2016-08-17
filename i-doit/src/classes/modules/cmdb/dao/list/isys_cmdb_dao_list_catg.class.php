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
 * DAO: ObjectType lists
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_catg extends isys_component_dao_object_table_list implements isys_cmdb_dao_list_interface
{
    /**
     * Return constant of category.
     *
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category()
    {
        return null;
    } // function

    /**
     * Return constant of category type.
     *
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    } // function

    /**
     * Flag for the rec status dialog
     *
     * @return bool
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function rec_status_list_active()
    {
        return true;
    } // function

    /**
     * Overwrite this for special count Handling.
     *
     * @return  array  Counts of several Status
     */
    public function get_rec_counts()
    {
        return [
            C__RECORD_STATUS__NORMAL   => 0,
            C__RECORD_STATUS__ARCHIVED => 0,
            C__RECORD_STATUS__DELETED  => 0
        ];
    } // function

    /**
     * Retrieve all.
     *
     * @param   string  $p_table
     * @param   integer $p_objID
     * @param   integer $p_cRecStatus
     *
     * @return  isys_component_dao_result
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_result($p_table = null, $p_objID, $p_cRecStatus = null)
    {
        $l_strSQL = 'SELECT ' . $p_table . '_list__id, ' . $p_table . '_list__title, ' . $p_table . '_list__status AS status_hidden ' . 'FROM ' . $p_table . '_list ' . 'WHERE ' . $p_table . '_list__isys_obj__id = ' . ($p_objID + 0);

        $l_cRecStatus = empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus;

        if (!empty($l_cRecStatus))
        {
            $l_strSQL .= ' AND ' . $p_table . '_list__status = ' . $l_cRecStatus;
        } // if

        return $this->retrieve($l_strSQL . ';');
    } // function

    /**
     * Method for retrieving the fields.
     *
     * @param   string $p_table
     *
     * @return  array
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_fields($p_table)
    {
        return [
            $p_table . '_list__id'     => 'LC__UNIVERSAL__ID',
            $p_table . '_list__title'  => 'LC__UNIVERSAL__TITLE',
            $p_table . 'status_hidden' => 'LC__UNIVERSAL__STATUS'
        ];
    } // function
} // class