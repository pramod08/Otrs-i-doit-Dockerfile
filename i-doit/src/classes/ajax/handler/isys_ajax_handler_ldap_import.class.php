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
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       0.9.9-9
 */
class isys_ajax_handler_ldap_import extends isys_ajax_handler
{
    /**
     * Init method, which gets called from the framework.
     *
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function init()
    {
        switch ($_GET['func'])
        {
            case 'filter':
                echo $this->process_filter();
                break;
            case 'import':
                $this->process_import();
                break;
        } // switch

        // End the request.
        $this->_die();
    } // function

    public function process_import()
    {
        global $g_comp_database;

        try
        {
            $l_dn_array = isys_format_json::decode($_POST['ids']);

            $l_ldap_server_id  = $_POST['ldap_server'];
            $l_ldap_dn_string  = trim($_POST['ldap_dn']);
            $l_connection_info = null;

            $l_ldap_mod = new isys_module_ldap;
            $l_ldap_lib = $l_ldap_mod->get_library_by_id($l_ldap_server_id, $l_connection_info);

            $l_ret = false;

            if ($l_ldap_lib)
            {
                $l_search_resource = $l_ldap_lib->search($l_ldap_dn_string, '(objectclass=*)', [], 0, null, null, null, C__LDAP_SCOPE__RECURSIVE);

                switch ($l_connection_info['isys_ldap_directory__const'])
                {
                    case 'C__LDAP__AD':
                        isys_module_ldap::debug('Using "Active Directory"!');
                        $l_import_obj = new isys_ldap_dao_import_active_directory($g_comp_database, $l_ldap_lib);
                        $l_ret        = $l_import_obj->set_resource($l_search_resource)
                            ->set_root_dn($l_ldap_dn_string)
                            ->set_dn_data($l_dn_array)
                            ->prepare()
                            ->import();
                        break;
                    case 'C__LDAP__OPENLDAP':
                        isys_module_ldap::debug('Using "Open LDAP" ... Sorry, this is currently unsupported.');
                        echo _L('LC__MODULE__LDAP__DIRECTORY_UNSUPPORTED');
                        break;
                    case 'C__LDAP__NDS':
                        isys_module_ldap::debug('Using "Novell Directory Services" ... Sorry, this is currently unsupported.');
                        echo _L('LC__MODULE__LDAP__DIRECTORY_UNSUPPORTED');
                        break;
                } // switch

                if (!$l_ret)
                {
                    echo _L('LC__MODULE__LDAP__LDAP_OBJECTS_ERROR_MSG');
                } // if
            } // if
        }
        catch (isys_exception_ldap $e)
        {
            isys_notify::error($e->getMessage());
        }
    } // function

    /**
     * Method for processing the LDAP Filter.
     *
     * @return  json array string|boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function process_filter()
    {
        global $g_comp_database;

        try
        {

            $l_ldap_server_id  = $_POST['ldap_server'];
            $l_ldap_dn_string  = trim($_POST['ldap_dn']);
            $l_connection_info = null;

            $l_ldap_mod = new isys_module_ldap;
            $l_ldap_lib = $l_ldap_mod->get_library_by_id($l_ldap_server_id, $l_connection_info);

            if ($l_ldap_lib)
            {

                $l_search_resource = $l_ldap_lib->search($l_ldap_dn_string, '(objectclass=*)', [], 0, null, null, null, C__LDAP_SCOPE__SINGLE);

                switch ($l_connection_info['isys_ldap_directory__const'])
                {
                    case 'C__LDAP__AD':
                        $l_import_obj = new isys_ldap_dao_import_active_directory($g_comp_database, $l_ldap_lib);
                        $l_arr        = $l_import_obj->set_resource($l_search_resource)
                            ->set_root_dn($l_ldap_dn_string)
                            ->get_entries_from_resource();

                        return isys_format_json::encode($l_arr);
                        break;
                    case 'C__LDAP__OPENLDAP':
                        break;
                    case 'C__LDAP__NDS':
                        break;
                } // switch
            } // if
        }
        catch (isys_exception_ldap $e)
        {
            isys_notify::error($e->getMessage());
        }

        return false;
    } // function
} // class

?>