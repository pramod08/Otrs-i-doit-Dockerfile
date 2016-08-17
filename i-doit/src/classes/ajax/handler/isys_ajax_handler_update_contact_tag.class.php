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
class isys_ajax_handler_update_contact_tag extends isys_ajax_handler
{
    /**
     * Init method, which holds the necessary logic.
     */
    public function init()
    {
        global $g_dirs;

        // Removed: isys_rs_system

        try
        {
            isys_auth_cmdb::instance()
                ->check_rights_obj_and_category(isys_auth::EDIT, $this->m_get[C__CMDB__GET__OBJECT], 'C__CATG__CONTACT');

            isys_cmdb_dao_category_g_contact::instance($this->m_database_component)
                ->save_contact_tag($this->m_post["conId"], $this->m_post["valId"]);

            echo '<img style="margin: 2px 0 0 3px;" src="' . $g_dirs["images"] . 'icons/infobox/blue.png" height="16"> <span>' . _L(
                    'LC__CATG__CONTACT_HAS_BEEN_UPDATED'
                ) . '</span>';
        }
        catch (isys_exception_auth $e)
        {
            echo '<img style="margin: 2px 0 0 3px;" src="' . $g_dirs["images"] . 'icons/infoicon/error.png" height="16"> <span>' . $e->getMessage() . '</span>';
        } // try

        die;
    } // function

    /**
     * This method defines, if the hypergate needs to be included for this request.
     *
     * @static
     * @return  boolean
     */
    public static function needs_hypergate()
    {
        return true;
    } // function
} // class
?>