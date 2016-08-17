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
 * DAO: assigned nagios services
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_Lists
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_catg_nagios_refs_services_backwards extends isys_cmdb_dao_list
{
    /**
     * Returns the category ID.
     *
     * @return  integer
     */
    public function get_category()
    {
        return C__CATG__NAGIOS_REFS_SERVICES_BACKWARDS;
    } // function

    /**
     * Returns the category type.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    } // function

    /**
     * @param   string  $p_table
     * @param   integer $p_id
     *
     * @return  isys_component_dao_result
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_result($p_table = null, $p_id, $p_unused = null)
    {
        global $g_comp_database;

        $l_dao = new isys_cmdb_dao_category_g_nagios_refs_services_backwards($g_comp_database);

        return $l_dao->get_selected_objects($p_id);
    } // function

    /**
     * Modify row method will be called by each iteration.
     *
     * @param   array $p_row
     *
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function modify_row(&$p_row)
    {
        global $g_comp_database;

        $l_dao = new isys_cmdb_dao($g_comp_database);

        $p_row['isys_obj__title']      = '<a href="?' . C__CMDB__GET__OBJECT . '=' . $p_row['isys_obj__id'] . '">' . $p_row['isys_obj__title'] . '</a>';
        $p_row['isys_obj_type__title'] = '<a href="?' . C__CMDB__GET__OBJECTTYPE . '=' . $l_dao->get_objTypeID(
                $p_row['isys_obj__id']
            ) . '&' . C__CMDB__GET__VIEWMODE . '=' . C__CMDB__VIEW__LIST_OBJECT . '">' . _L($l_dao->get_obj_type_name_by_obj_id($p_row['isys_obj__id'])) . '</a>';
    } // function

    /**
     * Flag for the rec status dialog
     *
     * @return bool
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function rec_status_list_active()
    {
        return false;
    } // function

    /**
     * Build header for the list.
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_fields()
    {
        return [
            'isys_obj__id'         => 'ID',
            'isys_obj__title'      => 'LC__UNIVERSAL__OBJECT_TITLE',
            'isys_obj_type__title' => 'LC__UNIVERSAL__OBJECT_TYPE'
        ];
    } // function

    /**
     * Returns the link the browser shall follow if clicked on a row.
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function make_row_link()
    {
        return '#';
    } // function
} // class