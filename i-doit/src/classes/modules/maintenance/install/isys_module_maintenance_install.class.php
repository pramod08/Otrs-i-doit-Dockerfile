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
 * i-doit maintenance module installer.
 *
 * @package     i-doit
 * @subpackage  maintenance
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.5.1
 */
class isys_module_maintenance_install
{
    /**
     * Init method.
     *
     * @param   isys_component_database $p_database
     * @param   isys_component_database $p_database_system
     * @param   integer                 $p_module_id
     * @param   string                  $p_type
     */
    public static function init($p_database, $p_database_system, $p_module_id, $p_type)
    {
        if ($p_type == 'install')
        {
            $l_dao = isys_cmdb_dao::instance($p_database);

            $l_module_id = $l_dao->retrieve('SELECT isys_module__id FROM isys_module WHERE isys_module__identifier = "maintenance";')
                ->get_row_value('isys_module__id');

            if ($l_module_id > 0)
            {
                $l_sqls = [];

                $l_rights = [
                    'PLANNING',
                    'PLANNING_ARCHIVE',
                    'MAILTEMPLATE',
                    'OVERVIEW',
                    'SEND_MAILS',
                ];

                $l_users = [
                    $l_dao->retrieve('SELECT isys_obj__id FROM isys_obj WHERE isys_obj__const = "C__OBJ__PERSON_GROUP_ADMIN";')
                        ->get_row_value('isys_obj__id'),
                    $l_dao->retrieve('SELECT isys_obj__id FROM isys_obj WHERE isys_obj__const = "C__OBJ__PERSON_GROUP_AUTHOR";')
                        ->get_row_value('isys_obj__id'),
                    $l_dao->retrieve('SELECT isys_obj__id FROM isys_obj WHERE isys_obj__const = "C__OBJ__PERSON_GROUP_EDITOR";')
                        ->get_row_value('isys_obj__id')
                ];

                $l_reader = $l_dao->retrieve('SELECT isys_obj__id FROM isys_obj WHERE isys_obj__const = "C__OBJ__PERSON_GROUP_READER";')
                    ->get_row_value('isys_obj__id');

                $l_users = array_filter($l_users);

                foreach ($l_users as $l_user)
                {
                    foreach ($l_rights as $l_right)
                    {
                        $l_sqls[] = 'INSERT isys_auth
							SET isys_auth__isys_obj__id = ' . $l_dao->convert_sql_id($l_user) . ',
							isys_auth__type = ' . $l_dao->convert_sql_int(isys_auth::EDIT + isys_auth::DELETE + isys_auth::EXECUTE) . ',
							isys_auth__isys_module__id = ' . $l_dao->convert_sql_id($l_module_id) . ',
							isys_auth__path = ' . $l_dao->convert_sql_text($l_right) . ';';
                    } // foreach
                } // foreach

                if ($l_reader > 0)
                {
                    foreach ($l_rights as $l_right)
                    {
                        $l_sqls[] = 'INSERT isys_auth
							SET isys_auth__isys_obj__id = ' . $l_dao->convert_sql_id($l_reader) . ',
							isys_auth__type = ' . $l_dao->convert_sql_int(isys_auth::VIEW) . ',
							isys_auth__isys_module__id = ' . $l_dao->convert_sql_id($l_module_id) . ',
							isys_auth__path = ' . $l_dao->convert_sql_text($l_right) . ';';
                    } // foreach
                } // if

                if (count($l_sqls))
                {
                    foreach ($l_sqls as $l_sql)
                    {
                        $l_dao->update($l_sql) && $l_dao->apply_update();
                    } // foreach
                } // if
            } // if
        } // if
    } // function
} // class