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
 * CMDB Global category stack membership.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @since       1.7
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_stack_membership extends isys_cmdb_ui_category_g_virtual
{
    /**
     * Processes view/edit mode.
     *
     * @param   isys_cmdb_dao_category_g_stack_membership $p_cat
     *
     * @return  void
     */
    public function process(isys_cmdb_dao_category_g_stack_membership $p_cat)
    {
        global $g_dirs;

        $l_obj_id = $this->m_object_id ?: $_GET[C__CMDB__GET__OBJECT];
        $l_dao    = isys_cmdb_dao_category_g_stack_member::instance($this->m_database_component);

        $l_stacks     = [];
        $l_is_stacked = false;
        $l_res        = $l_dao->get_stacking_meta($l_obj_id);

        if (count($l_res))
        {
            $l_is_stacked = true;
            $l_quickinfo  = new isys_ajax_handler_quick_info;

            while ($l_row = $l_res->get_row())
            {
                $l_members = [];

                $l_member_res = $l_dao->get_data(null, $l_row['isys_obj__id'], '', null, C__RECORD_STATUS__NORMAL);

                while ($l_member_row = $l_member_res->get_row())
                {
                    if ($l_obj_id == $l_member_row['isys_obj__id'])
                    {
                        // Don't display yourself.
                        continue;
                    } // if

                    $l_members[] = $l_quickinfo->get_quick_info(
                        $l_member_row['isys_obj__id'],
                        '<img src="' . $g_dirs['images'] . 'icons/silk/link.png" class="vam mr5" />' . _L(
                            $l_member_row['isys_obj_type__title']
                        ) . ' &raquo; ' . $l_member_row['isys_obj__title'],
                        C__LINK__OBJECT
                    );
                } // while

                if (count($l_members))
                {
                    $l_stacks[] = [
                        'quickinfo' => $l_quickinfo->get_quick_info(
                            $l_row['isys_obj__id'],
                            '<img src="' . $g_dirs['images'] . 'icons/silk/link.png" class="vam mr5" />' . _L(
                                $l_row['isys_obj_type__title']
                            ) . ' &raquo; ' . $l_row['isys_obj__title'],
                            C__LINK__CATG,
                            false,
                            [C__CMDB__GET__CATG => C__CATG__STACK_MEMBER]
                        ),
                        'members'   => $l_members
                    ];
                } // if
            } // while
        } // if

        $this->get_template_component()
            ->assign('is_stacked', $l_is_stacked)
            ->assign('stacks', $l_stacks);

        parent::process($p_cat);
    } //function
} // class