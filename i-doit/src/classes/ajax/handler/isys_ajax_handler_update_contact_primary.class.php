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
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @version     0.9.9-9
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       0.9.9-9
 */
class isys_ajax_handler_update_contact_primary extends isys_ajax_handler
{
    /**
     * Init method, which holds the necessary logic.
     */
    public function init()
    {
        header('Content-Type: application/json');

        $l_return = [
            'success' => true,
            'data'    => null,
            'message' => null
        ];

        try
        {
            isys_auth_cmdb::instance()
                ->check_rights_obj_and_category(isys_auth::EDIT, $_POST[C__CMDB__GET__OBJECT], 'C__CATG__CONTACT');

            $l_dao_contact = new isys_cmdb_dao_category_g_contact($this->m_database_component);

            if (!$l_dao_contact->is_primary($_POST['id']))
            {
                $l_dao_contact->make_primary($_POST[C__CMDB__GET__OBJECT], $_POST['id']);
                $l_return['data'] = ['is_primary' => true];
            }
            else
            {
                $l_dao_contact->reset_primary($_POST[C__CMDB__GET__OBJECT]);
                $l_return['data'] = ['is_primary' => false];
            } // if
        }
        catch (Exception $e)
        {
            $l_return['success'] = false;
            $l_return['message'] = $e->getMessage();
        } // try

        echo isys_format_json::encode($l_return);

        $this->_die();
    } // function
} // class