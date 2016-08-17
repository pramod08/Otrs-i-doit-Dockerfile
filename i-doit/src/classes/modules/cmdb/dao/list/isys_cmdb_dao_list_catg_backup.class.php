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
 * DAO: Category list for backup servers
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Bluemer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_catg_backup extends isys_cmdb_dao_list implements isys_cmdb_dao_list_interface
{
    /**
     * Return constant of category.
     *
     * @return integer
     * @author Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category()
    {
        return C__CATG__BACKUP;
    } // function

    /**
     * Return constant of category type.
     *
     * @return integer
     * @author Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    } // function

    /**
     * Modify row method.
     *
     * @param  array &$p_arrRow
     */
    public function modify_row(&$p_arrRow)
    {
        if ($p_arrRow["isys_connection__isys_obj__id"] != null)
        {
            $l_dao = isys_cmdb_dao::factory($this->get_database_component());

            $l_link = isys_helper_link::create_url(
                [
                    C__CMDB__GET__OBJECT     => $p_arrRow["isys_connection__isys_obj__id"],
                    C__CMDB__GET__OBJECTTYPE => $l_dao->get_objTypeID($p_arrRow["isys_connection__isys_obj__id"]),
                    C__CMDB__GET__VIEWMODE   => C__CMDB__VIEW__LIST_CATEGORY,
                    C__CMDB__GET__CATG       => C__CATG__BACKUP__ASSIGNED_OBJECTS,
                    C__CMDB__GET__TREEMODE   => $_GET["tvMode"]
                ]
            );

            $l_quickinfo = new isys_ajax_handler_quick_info();

            $p_arrRow["obj__title"] = $l_quickinfo->get_quick_info(
                $p_arrRow["isys_connection__isys_obj__id"],
                $l_dao->get_obj_name_by_id_as_string($p_arrRow["isys_connection__isys_obj__id"]),
                $l_link
            );
        } // if
    } // function

    /**
     * This method returns the fields and translations.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            'isys_catg_backup_list__title'        => 'LC__CMDB__CATG__BACKUP__TITLE',
            'obj__title'                          => 'LC__CMDB__CATG__BACKUP__IS_BACKUPEP',
            'isys_backup_type__title'             => 'LC__CMDB__CATG__BACKUP__BACKUP_TYPE',
            'isys_backup_cycle__title'            => 'LC__CMDB__CATG__BACKUP__CYCLE',
            'isys_catg_backup_list__path_to_save' => 'LC__CMDB__CATG__BACKUP__PATH_TO_SAVE',
        ];
    } // function
} // class