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
 * CMDB Global list DAO for stacking.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @since       1.7
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_catg_stack_member extends isys_cmdb_dao_list
{
    /**
     * Return constant of category.
     *
     * @return  integer
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_category()
    {
        return C__CATG__STACK_MEMBER;
    } // function

    /**
     * Return constant of category type.
     *
     * @return  integer
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    } // function

    /**
     * Modifies row.
     *
     * @param  array &$p_row
     */
    public function modify_row(&$p_row)
    {
        global $g_dirs;

        $l_stack_member = isys_cmdb_dao::instance($this->m_db)
            ->get_object($p_row['isys_catg_stack_member_list__stack_member'])
            ->get_row();

        $p_row["obj_title"] = isys_factory::get_instance('isys_ajax_handler_quick_info')
            ->get_quick_info(
                $l_stack_member["isys_obj__id"],
                _L($l_stack_member["isys_obj_type__title"]) . ' &raquo; ' . $l_stack_member["isys_obj__title"],
                C__LINK__OBJECT
            );

        switch ($p_row['isys_catg_stack_member_list__mode'])
        {
            default:
            case null:
            case -1:
                $p_row['isys_catg_stack_member_list__mode'] = isys_tenantsettings::get('gui.empty_value', '-');
                break;

            case 1:
                $p_row['isys_catg_stack_member_list__mode'] = '<img src="' . $g_dirs['images'] . 'icons/silk/bullet_green.png" class="mr5 vam" />' . _L(
                        'LC__UNIVERSAL__ACTIVE'
                    );
                break;

            case 0:
                $p_row['isys_catg_stack_member_list__mode'] = '<img src="' . $g_dirs['images'] . 'icons/silk/bullet_red.png" class="mr5 vam" />' . _L(
                        'LC__UNIVERSAL__PASSIVE'
                    );
                break;
        } // switch
    } // function

    /**
     * Method for returning the fields to display.
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_fields()
    {
        return [
            "obj_title"                         => "LC__CATG__STACK_MEMBER__STACK_MEMBER",
            "isys_catg_stack_member_list__mode" => "LC__CATG__STACK_MEMBER__MODE"
        ];
    } // function
} // class