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
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_ajax_handler_update_guest_system_primary extends isys_ajax_handler
{
    /**
     * Init method, which holds the necessary logic.
     */
    public function init()
    {
        global $g_dirs;

        try
        {
            isys_auth_cmdb::instance()
                ->check_rights_obj_and_category(isys_auth::EDIT, $this->m_get[C__CMDB__GET__OBJECT], 'C__CATG__GUEST_SYSTEMS');

            $l_dao = isys_cmdb_dao::instance($this->m_database_component);

            $l_query = "UPDATE isys_catg_virtual_machine_list
				SET isys_catg_virtual_machine_list__primary = " . $l_dao->convert_sql_id($this->m_post["valId"]) . "
				WHERE isys_catg_virtual_machine_list__id = " . $l_dao->convert_sql_id($this->m_post["conId"]) . ";";

            if ($l_dao->update($l_query) && $l_dao->apply_update())
            {
                echo '<img style="margin: 2px 0 0 3px;" src="' . $g_dirs["images"] . 'icons/infobox/blue.png" height="16"> <span>' . _L(
                        'LC__CATG__GUEST_SYSTEM_HAS_BEEN_UPDATED'
                    ) . '</span>';
            } // if
        }
        catch (isys_exception_auth $e)
        {
            echo '<img style="margin: 2px 0 0 3px;" src="' . $g_dirs["images"] . 'icons/infoicon/error.png" height="16"> <span>' . $e->getMessage() . '</span>';
        } // try

        die;
    } // function
} // class