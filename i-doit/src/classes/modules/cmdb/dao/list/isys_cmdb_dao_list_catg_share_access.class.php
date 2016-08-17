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
 * @package    i-doit
 * @subpackage
 * @author     Van Quyen Hoang <qhoang@i-doit.org>
 * @author     Leonard Fischer <lfischer@i-doit.com>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_catg_share_access extends isys_cmdb_dao_list implements isys_cmdb_dao_list_interface
{
    /**
     * Return constant of category.
     *
     * @return  integer
     */
    public function get_category()
    {
        return C__CATG__SHARE_ACCESS;
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
     * Method which helps modifying each row.
     *
     * @param  array $p_row
     */
    public function modify_row(&$p_row)
    {
        global $g_comp_database;

        $p_row['object'] = isys_factory::get_instance('isys_ajax_handler_quick_info')
            ->get_quick_info(
                $p_row['isys_connection__isys_obj__id'],
                isys_cmdb_dao::instance($g_comp_database)
                    ->get_obj_name_by_id_as_string($p_row['isys_connection__isys_obj__id']),
                C__LINK__OBJECT
            );
    } // function

    /**
     * Method for retrieving the displayable fields.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "isys_catg_shares_list__title"            => "LC__CMDB__CATG__SHARES__SHARE_NAME",
            "object"                                  => "LC__POPUP__BROWSER__SELECTED_OBJECT",
            "isys_catg_share_access_list__mountpoint" => "LC__CMDB__CATG__SHARE_ACCESS__MOUNTPOINT"
        ];
    } // function
} // class