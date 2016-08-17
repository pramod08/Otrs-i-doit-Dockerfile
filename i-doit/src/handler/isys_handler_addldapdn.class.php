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
 * This handler synchronizes the LDAP DN String of all persons or objects with the ldap dn category
 *
 * @package        i-doit
 * @subpackage     Handler
 * @author         Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright      synetics GmbH
 * @version        1.1
 * @license        http://www.i-doit.com/license
 */
class isys_handler_addldapdn extends isys_handler
{
    /**
     * Only for type objects.
     *
     * @var  string
     */
    private $m_ldap_dn_string = '';
    /**
     * Possible options contacts, objects.
     *
     * @var string
     */
    private $m_ldap_dn_type = 'contacts';
    /**
     * Optional: LDAP Server.
     *
     * @var  string
     */
    private $m_ldap_host = '';
    /**
     * Optional: For example C__OBJTYPE__SERVER.
     *
     * @var string
     */
    private $m_obj_type = '';

    /**
     * Initializing method.
     *
     * @return  boolean
     */
    public function init()
    {
        verbose("Setting up system environment");

        try
        {
            $this->add_ldap_dn();
        }
        catch (Exception $e)
        {
            verbose($e->getMessage());
        } // try

        return true;
    } // function

    /**
     *
     */
    private function add_ldap_dn()
    {
        global $argv, $g_comp_database;

        if (in_array('-h', $argv))
        {
            $this->usage();
        }

        if (empty($this->m_ldap_host) && is_numeric(array_search('-host', $argv)))
        {
            if (isset($argv[array_search('-host', $argv) + 1]))
            {
                $this->m_ldap_host = $argv[array_search('-host', $argv) + 1];
            }
        } // if

        if ($this->m_ldap_dn_string == '' && $this->m_ldap_dn_type != 'contacts')
        {
            error("ERROR: Please define variable \$m_ldap_dn_string in File isys_handler_addldapdn.class.php. Example OU=Servers,DC=Test,DC=int\n");
            die;
        } // if

        if ($this->m_ldap_dn_type == '')
        {
            error("ERROR: Please define variable \$m_ldap_dn_type in File isys_handler_addldapdn.class.php.\nPossible options: objects,contacts\n");
            die;
        } // if

        $l_fixit_type = $this->m_ldap_dn_type;

        $l_ldap_dn = $this->m_ldap_dn_string;

        if ($l_fixit_type == 'objects')
        {
            $l_dao        = new isys_cmdb_dao($g_comp_database);
            $l_dao_cat_dn = new isys_cmdb_dao_category_g_ldap_dn($g_comp_database);

            if ($this->m_obj_type != '')
            {
                $l_sql = 'SELECT * FROM isys_obj WHERE isys_obj__isys_obj_type__id = (SELECT isys_obj_type__id FROM isys_obj_type WHERE isys_obj_type__const = ' . $l_dao->convert_sql_text(
                        $this->m_obj_type
                    ) . ')';
            }
            else
            {
                $l_sql = 'SELECT * FROM isys_obj WHERE isys_obj__isys_obj_type__id IN
					(
						SELECT isys_obj_type_2_isysgui_catg__isys_obj_type__id FROM isys_obj_type_2_isysgui_catg
						INNER JOIN isysgui_catg ON isysgui_catg__id = isys_obj_type_2_isysgui_catg__isysgui_catg__id
						WHERE isysgui_catg__const = \'C__CATG__LDAP_DN\'
					)';
            } // if

            $l_res = $l_dao->retrieve($l_sql);

            while ($l_row = $l_res->get_row())
            {
                $l_obj_id    = $l_row['isys_obj__id'];
                $l_obj_title = $l_row['isys_obj__title'];
                $l_dn_string = 'CN=' . $l_obj_title . ',' . $l_ldap_dn;
                $l_res_dn    = $l_dao_cat_dn->get_data(null, $l_obj_id);

                if ($l_res_dn->num_rows() > 0)
                {
                    $l_dn_data = $l_res_dn->get_row();
                    if ($l_dn_data['isys_catg_ldap_dn_list__title'] == '')
                    {
                        // Update dn string in category ldap dn
                        $l_update_sql = 'UPDATE isys_catg_ldap_dn_list SET isys_catg_ldap_dn_list__title = ' . $l_dao->convert_sql_text($l_dn_string) . '
						WHERE isys_catg_ldap_dn_list__id = ' . $l_dao->convert_sql_id($l_dn_data['isys_catg_ldap_dn_list__id']);
                    }
                }
                else
                {
                    // insert new entry for category ldap dn
                    $l_update_sql = 'INSERT INTO isys_catg_ldap_dn_list (
						isys_catg_ldap_dn_list__title,
						isys_catg_ldap_dn_list__isys_obj__id,
						isys_catg_ldap_dn_list__status
					)
					VALUES
					(
						' . $l_dao->convert_sql_text($l_dn_string) . ',
						' . $l_dao->convert_sql_id($l_obj_id) . ',
						' . C__RECORD_STATUS__NORMAL . '
					)';
                } // if
                $l_dao->update($l_update_sql);
            } // while
            $l_dao->apply_update();
        }
        elseif ($l_fixit_type == 'contacts')
        {
            $l_ldap_module = new isys_module_ldap();
            $l_ldap_dao    = new isys_ldap_dao($g_comp_database);
            $l_dao_person  = new isys_cmdb_dao_category_s_person_master($g_comp_database);

            if (!empty($this->m_ldap_host))
            {
                // Checks users in specified ldap host
                $l_sql     = "SELECT * FROM isys_ldap " . "LEFT JOIN isys_ldap_directory " . "ON " . "isys_ldap__isys_ldap_directory__id = " . "isys_ldap_directory__id " . "WHERE isys_ldap__hostname = " . $l_ldap_dao->convert_sql_text(
                        $this->m_ldap_host
                    );
                $l_servers = $l_ldap_dao->retrieve($l_sql);
            }
            else
            {
                // Checks users in all active ldap servers
                $l_servers = $l_ldap_dao->get_active_servers();
            }

            if ($l_servers->num_rows() > 0)
            {
                while ($l_row = $l_servers->get_row())
                {
                    $l_hostname = $l_row["isys_ldap__hostname"];
                    $l_port     = $l_row["isys_ldap__port"];
                    $l_dn       = $l_row["isys_ldap__dn"];
                    $l_ldap_id  = $l_row["isys_ldap__id"];
                    $l_password = isys_helper_crypt::decrypt($l_row["isys_ldap__password"]);
                    $l_mapping  = unserialize($l_row["isys_ldap_directory__mapping"]);

                    try
                    {
                        $l_ldap_lib = $l_ldap_module->get_library($l_hostname, $l_dn, $l_password, $l_port);

                        $l_res = $l_dao_person->get_data();
                        while ($l_row_person = $l_res->get_row())
                        {
                            $l_search = $l_ldap_lib->search(
                                $l_row["isys_ldap__user_search"],
                                "(&(" . $l_mapping[C__LDAP_MAPPING__USERNAME] . "=" . $l_row_person['isys_cats_person_list__title'] . ")(objectclass=user))"
                            );

                            if ($l_search)
                            {
                                $l_attributes = $l_ldap_lib->get_entries($l_search);
                                if ($l_attributes['count'] > 0)
                                {
                                    verbose("Found User with username " . $l_row_person['isys_cats_person_list__title'] . " in ldap server. Synchronizing LDAP DN String...");

                                    $l_ldap_data = $l_attributes[0];
                                    if (isset($l_ldap_data['distinguishedname']) && $l_ldap_data['distinguishedname']['count'] > 0)
                                    {
                                        $l_ldap_dn_string = $l_ldap_data['distinguishedname'][0];
                                        $l_update         = 'UPDATE isys_cats_person_list SET ' . 'isys_cats_person_list__isys_ldap__id = \'' . $l_ldap_id . '\', ' . 'isys_cats_person_list__ldap_dn = \'' . $l_ldap_dn_string . '\' ' . 'WHERE isys_cats_person_list__id = ' . $l_dao_person->convert_sql_id(
                                                $l_row_person['isys_cats_person_list__id']
                                            );
                                        $l_dao_person->update($l_update);
                                    } // if
                                }
                                else
                                {
                                    verbose("ERROR: User with username " . $l_row_person['isys_cats_person_list__title'] . " in ldap server does not exist. Skipped User");
                                    $l_ldap_module->debug(
                                        "ERROR: User with username " . $l_row_person['isys_cats_person_list__title'] . " in ldap server does not exist. Skipped User"
                                    );
                                } // if
                            } // if
                        } // while
                    }
                    catch (Exception $e)
                    {
                        error($e->getMessage());
                    } // try
                } // while
                $l_dao_person->apply_update();
            } // if
        } // if
    } // function

    /**
     * Display the usage help.
     */
    private function usage()
    {
        error(
            "Usage: ./controller -m addldapdn \n" . "Optional Parameter: -host [ldapserver]\n" . "Example: ./controller -m addldapdn -host [ldapserver]"
        );
        die;
    } // function
} // class