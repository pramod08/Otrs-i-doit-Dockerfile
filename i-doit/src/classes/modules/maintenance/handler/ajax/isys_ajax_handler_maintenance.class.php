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
 * AJAX controller
 *
 * @package     modules
 * @subpackage  maintenance
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.1
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.5.1
 */
class isys_ajax_handler_maintenance extends isys_ajax_handler
{
    /**
     * Init method, which gets called from the framework.
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
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
                case 'get-filtered-planning-list':
                    $l_return['data'] = $this->get_filtered_planning_list($_POST['from'], $_POST['to']);
                    break;

                case 'finish-maintenances':
                    $l_return['data'] = $this->finish_maintenances(isys_format_json::decode($_POST['ids']), $_POST['comment']);
                    break;

                case 'send-planning-email':
                    // Shall the second parameter be dynamic?
                    $l_return['data'] = $this->send_planning_email(isys_format_json::decode($_POST['ids']));
                    break;
            } // switch
        }
        catch (isys_exception $e)
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
     * Method for triggering the "completion" of a maintenance planning.
     *
     * @param   array  $p_ids
     * @param   string $p_comment
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function finish_maintenances(array $p_ids, $p_comment)
    {
        $l_return = [];
        $l_dao    = new isys_maintenance_dao($this->m_database_component);

        if (!count($p_ids))
        {
            return $l_return;
        } // if

        foreach ($p_ids as $l_id)
        {
            try
            {
                $l_return[$l_id] = $l_dao->finish_maintenance_planning($l_id, $p_comment);
            }
            catch (Exception $e)
            {
                $l_return[$l_id] = $e->getMessage();
            }
        } // foreach

        return $l_return;
    } // function

    /**
     * Method for triggering the "email" of a maintenance planning.
     *
     * @param   array $p_ids
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function send_planning_email(array $p_ids)
    {
        $l_return = [];
        $l_dao    = new isys_maintenance_dao($this->m_database_component);

        if (!count($p_ids))
        {
            return $l_return;
        } // if

        foreach ($p_ids as $l_id)
        {
            try
            {
                $l_return[$l_id] = $l_dao->send_maintenance_planning_mail($l_id);
            }
            catch (Exception $e)
            {
                $l_return[$l_id] = $e->getMessage();
            }
        } // foreach

        return $l_return;
    } // function

    /**
     * Method for retrieving maintenance plannings during a given timeperiod.
     *
     * @param   string $p_from
     * @param   string $p_to
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function get_filtered_planning_list($p_from = null, $p_to = null)
    {
        global $g_dirs, $g_config, $g_loc;

        $l_return = [
            'objects'      => [],
            'maintenances' => []
        ];

        $l_dao       = new isys_maintenance_dao($this->m_database_component);
        $l_image_dao = new isys_cmdb_dao_category_g_image($this->m_database_component);

        $l_res = $l_dao->get_filtered_planning_list($p_from, $p_to);

        if (count($l_res))
        {
            while ($l_row = $l_res->get_row())
            {
                if (!isset($l_return['maintenances'][$l_row['isys_maintenance__id']]))
                {
                    $l_maintenance = [
                        'objects'  => [],
                        'contacts' => []
                    ];

                    foreach ($l_row as $l_key => $l_value)
                    {
                        if (strpos($l_key, 'isys_obj_') !== 0)
                        {
                            $l_maintenance[$l_key] = $l_value;
                        } // if
                    } // foreach

                    $l_maintenance['isys_contact_tag__title'] = _L($l_maintenance['isys_contact_tag__title']);
                    $l_maintenance['from']                    = strtotime($l_maintenance['isys_maintenance__date_from']);
                    $l_maintenance['from_formatted']          = $g_loc->fmt_date($l_maintenance['isys_maintenance__date_from']);
                    $l_maintenance['to']                      = strtotime($l_maintenance['isys_maintenance__date_to']);
                    $l_maintenance['to_formatted']            = $g_loc->fmt_date($l_maintenance['isys_maintenance__date_to']);

                    $l_contact_res = $l_dao->get_planning_contacts($l_maintenance['isys_maintenance__id']);

                    if (count($l_contact_res))
                    {
                        while ($l_contact_row = $l_contact_res->get_row())
                        {
                            $l_maintenance['contacts'][] = [
                                'isys_obj__id'         => $l_contact_row['isys_obj__id'],
                                'isys_obj__title'      => $l_contact_row['isys_obj__title'],
                                'isys_obj_type__title' => _L($l_contact_row['isys_obj_type__title']),
                                'isys_obj_type__icon'  => $l_contact_row['isys_obj_type__icon']
                            ];
                        } // while
                    } // if

                    $l_return['maintenances'][$l_row['isys_maintenance__id']] = $l_maintenance;
                } // if

                if (!isset($l_return['objects'][$l_row['isys_obj__id']]))
                {
                    $l_object = [
                        'maintenances' => [],
                        'roles'        => []
                    ];

                    foreach ($l_row as $l_key => $l_value)
                    {
                        if (strpos($l_key, 'isys_obj_') === 0)
                        {
                            $l_object[$l_key] = $l_value;
                        } // if
                    } // foreach

                    $l_object['isys_obj_type__title'] = _L($l_object['isys_obj_type__title']);
                    $l_object['image']                = $l_image_dao->get_image_name_by_object_id($l_object['isys_obj__id']);

                    if ($l_object['image'])
                    {
                        $l_object['image'] = $g_config['www_dir'] . 'upload/images/' . $l_object['image'];
                    }
                    else
                    {
                        $l_object['image'] = $g_dirs['images'] . 'objecttypes/' . $l_object['isys_obj_type__obj_img_name'];
                    } // if

                    if (empty($l_object['isys_obj_type__icon']))
                    {
                        $l_object['isys_obj_type__icon'] = $g_dirs['images'] . 'empty.gif';
                    }
                    else if (strpos($l_object['isys_obj_type__icon'], '/') === false)
                    {
                        $l_object['isys_obj_type__icon'] = $g_dirs['images'] . 'tree/' . $l_object['isys_obj_type__icon'];
                    } // if

                    if (!isset($l_object['roles'][$l_row['isys_maintenance__isys_contact_tag__id']]))
                    {
                        $l_names       = [];
                        $l_contact_res = isys_cmdb_dao_category_g_contact::instance($this->m_database_component)
                            ->get_contact_objects_by_tag($l_object['isys_obj__id'], $l_row['isys_maintenance__isys_contact_tag__id']);

                        if (count($l_contact_res))
                        {
                            while ($l_contact_row = $l_contact_res->get_row())
                            {
                                $l_names[] = _L($l_dao->get_obj_type_name_by_obj_id($l_contact_row['isys_obj__id'])) . ' > ' . $l_contact_row['isys_obj__title'];
                            } // while
                        } // if

                        $l_object['roles'][$l_row['isys_maintenance__isys_contact_tag__id']] = implode(PHP_EOL, $l_names);
                    } // if

                    $l_return['objects'][$l_row['isys_obj__id']] = $l_object;
                } // if

                // And finally we add a relation between objects and maintenances.
                $l_return['maintenances'][$l_row['isys_maintenance__id']]['objects'][] = $l_row['isys_obj__id'];
                $l_return['objects'][$l_row['isys_obj__id']]['maintenances'][]         = $l_row['isys_maintenance__id'];
            } // while
        } // if

        return $l_return;
    } // function
} // class