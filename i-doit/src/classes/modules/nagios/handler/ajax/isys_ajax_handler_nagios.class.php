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
 * Nagios AJAX handler.
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       1.1
 */
class isys_ajax_handler_nagios extends isys_ajax_handler
{
    /**
     * Init method, which gets called from the framework.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function init()
    {
        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json');

        $l_return = [
            'success' => true,
            'message' => null,
            'data'    => null
        ];

        try
        {
            switch ($_GET['func'])
            {
                case 'load_command_comment':
                    $l_return['data'] = $this->load_command_comment((int) $_POST['command_id']);
                    break;

                case 'load_state':
                    $l_return['data'] = current($this->load_states([$_POST[C__CMDB__GET__OBJECT]]));
                    break;

                case 'load_states':
                    $l_return['data'] = $this->load_states(isys_format_json::decode($_POST['obj_ids']));
                    break;

                case 'toggle_inherited_service':
                    $l_return['data'] = $this->toggle_inherited_service($_POST['host'], $_POST['service']);
                    break;
            } // switch
        }
        catch (Exception $e)
        {
            $l_return['success'] = false;
            $l_return['message'] = $e->getMessage();
        } // try

        echo isys_format_json::encode($l_return);

        $this->_die();
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

    /**
     * Method for loading the command comment.
     *
     * @param   integer $p_command_id
     *
     * @return  string
     */
    protected function load_command_comment($p_command_id)
    {
        $l_command = isys_component_dao_nagios::instance($this->m_database_component)
            ->getCommand($p_command_id);

        $l_comment = $l_command['description'];

        if (!empty($l_comment))
        {
            $l_wysiwyg = new isys_smarty_plugin_f_wysiwyg();

            $l_comment = isys_glob_utf8_encode(
                $l_wysiwyg->navigation_view(
                    isys_component_template::instance(),
                    [
                        'p_bEditMode' => false,
                        'p_strValue'  => $l_comment
                    ]
                )
            );
        }
        else
        {
            $l_comment = isys_tenantsettings::get('gui.empty_value', '-');
        } // if

        return $l_comment;
    } // function

    /**
     * This method will retrieve the "state" data of a given host from nagios in realtime.
     *
     * @param   array $p_obj_ids
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function load_states(array $p_obj_ids)
    {
        global $g_comp_database;

        $l_return = [];

        // Enable cache lifetime of 2 minutes.
        isys_core::expire(120);

        $p_obj_ids       = array_filter($p_obj_ids);
        $l_daoNagios     = isys_component_dao_nagios::instance($g_comp_database);
        $l_daoCMDBNagios = isys_cmdb_dao_category_g_nagios::instance($g_comp_database);

        foreach ($p_obj_ids as $l_obj_id)
        {
            $l_catData = $l_daoCMDBNagios->getCatDataById($l_obj_id);

            if (!$l_daoNagios->is_ndo_instance_active($l_catData["isys_catg_monitoring_list__isys_monitoring_hosts__id"]))
            {
                $l_return[] = [
                    'success' => false,
                    'obj_id'  => $l_obj_id,
                    'message' => null
                ];

                continue;
            } // if

            // If the current host has no nagios configuration, just skip it and don't display anything (especially errors).
            if (empty($l_catData["isys_catg_monitoring_list__isys_monitoring_hosts__id"]))
            {
                continue;
            } // if

            try
            {
                $l_ndo = isys_monitoring_ndo::factory($l_catData["isys_catg_monitoring_list__isys_monitoring_hosts__id"]);

                $l_daoNagios->set_ndo($l_ndo->get_db_connection(), $l_ndo->get_db_prefix());

                $l_return[] = [
                    'success' => true,
                    'obj_id'  => $l_obj_id,
                    'message' => $l_daoNagios->getHostStatus($l_catData)
                ];
            }
            catch (Exception $e)
            {
                $l_return[] = [
                    'success' => false,
                    'obj_id'  => $l_obj_id,
                    'message' => $e->getMessage()
                ];
            } // try
        } // foreach

        return $l_return;
    } // function

    /**
     * This method is used for toggling the service "inheritance" from connected software objects.
     *
     * @param   integer $p_host_object_id
     * @param   integer $p_service_object_id
     *
     * @return  array
     */
    protected function toggle_inherited_service($p_host_object_id, $p_service_object_id)
    {
        /* @var $l_dao isys_cmdb_dao */
        $l_dao             = isys_cmdb_dao::instance($this->m_database_component);
        $l_inheritance_sql = 'SELECT *
			FROM isys_catg_nagios_service_inheritance
			WHERE isys_catg_nagios_service_inheritance__host__isys_obj__id = ' . $l_dao->convert_sql_id($p_host_object_id) . '
			AND isys_catg_nagios_service_inheritance__service__isys_obj__id = ' . $l_dao->convert_sql_id($p_service_object_id) . ';';

        if (count($l_dao->retrieve($l_inheritance_sql)))
        {
            // We found a record, so we delete it.
            $l_dao->update(
                'DELETE FROM isys_catg_nagios_service_inheritance
				WHERE isys_catg_nagios_service_inheritance__host__isys_obj__id = ' . $l_dao->convert_sql_id($p_host_object_id) . '
				AND isys_catg_nagios_service_inheritance__service__isys_obj__id = ' . $l_dao->convert_sql_id($p_service_object_id) . ';'
            );

            return ['will_be_exported' => true];
        }
        else
        {
            // We found no record, so we create one.
            $l_dao->update(
                'INSERT INTO isys_catg_nagios_service_inheritance SET
				isys_catg_nagios_service_inheritance__host__isys_obj__id = ' . $l_dao->convert_sql_id($p_host_object_id) . ',
				isys_catg_nagios_service_inheritance__service__isys_obj__id = ' . $l_dao->convert_sql_id($p_service_object_id) . ';'
            );

            return ['will_be_exported' => false];
        } // if
    } // function
} // class