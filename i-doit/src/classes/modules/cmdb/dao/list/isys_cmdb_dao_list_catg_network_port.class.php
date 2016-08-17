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
 * DAO: ObjectType list for ports (subcategory of network)
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_catg_network_port extends isys_cmdb_dao_list
{
    /**
     * Return constant of category.
     *
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category()
    {
        return C__CATG__NETWORK;
    } // function

    /**
     * Return constant of category type.
     *
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    } // function

    /**
     * Function which contains the order for the SQL query.
     *
     * @param string $p_column
     * @param string $p_direction
     *
     * @return string
     */
    public function get_order_condition($p_column, $p_direction)
    {
        switch ($p_column)
        {
            case "isys_catg_port_list__title":
                try
                {
                    //$l_condition = "LENGTH(" . $p_column . ") " . $p_direction . ", " . $p_column . " " . $p_direction;
                    if (isys_cmdb_dao_category_g_network_port::add_sql_functions_for_order($this->m_db))
                    {
                        // With this the list orders Ports like Port1/0/1, Port1/0/2 properly now.
                        $l_condition = "
                        alphas(" . $p_column . ") " . $p_direction . ",
                        substr_order(" . $p_column . ", '/') " . $p_direction . ",
                        substr_order(" . $p_column . ", '-') " . $p_direction . ",
                        substr_order(" . $p_column . ", '|') " . $p_direction . ",
                        substr_order(" . $p_column . ", '_') " . $p_direction . ",
                        LENGTH(" . $p_column . ") " . $p_direction . ",
                        digits(" . $p_column . ") " . $p_direction . ",
                        " . $p_column . " " . $p_direction;
                    }
                    else
                    {
                        $l_condition = parent::get_order_condition($p_column, $p_direction);
                    }
                }
                catch (Exception $e)
                {
                    // Do the default
                    $l_condition = parent::get_order_condition($p_column, $p_direction);
                    isys_notify::warning($e->getMessage());
                } // try
                break;
            default:
                $l_condition = parent::get_order_condition($p_column, $p_direction);
        }

        return $l_condition;
    } // function

    /**
     * Returns the resultset for the list.
     *
     * @param   string  $p_tableName
     * @param   integer $p_object_id
     * @param   integer $p_cRecStatus
     *
     * @return  isys_component_dao_result
     */
    public function get_result($p_tableName = null, $p_object_id, $p_cRecStatus = null)
    {
        $l_condition = "";

        if (!is_null($_GET["ifaceID"]))
        {
            $l_condition = " AND (isys_catg_netp_list__id = '" . $_GET["ifaceID"] . "')";
        } // if
        return isys_cmdb_dao_category_g_network_port::instance($this->get_database_component())
            ->get_ports($p_object_id, null, (empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus), null, null, $l_condition, true);
    } // function

    /**
     * Exchange column to create individual links in columns.
     *
     * @global  array $g_dirs
     *
     * @param   array $p_arrRow (by reference)
     */
    public function modify_row(&$p_arrRow)
    {
        global $g_dirs;

        $p_arrRow["object_connection"] = $p_arrRow["connector_connection"] = isys_tenantsettings::get('gui.empty_value', '-');

        if (!empty($p_arrRow["isys_cable_connection__id"]))
        {
            $l_dao = new isys_cmdb_dao_cable_connection($this->m_db);

            $l_objID   = $l_dao->get_assigned_object($p_arrRow["isys_cable_connection__id"], $p_arrRow["isys_catg_connector_list__id"]);
            $l_objInfo = $l_dao->get_type_by_object_id($l_objID)
                ->get_row();

            if ($l_objInfo["isys_obj_type__id"] > 0)
            {
                $l_strImage = '<img src="' . $g_dirs["images"] . 'icons/silk/link.png" class="vam" />';

                // Create link obj.
                $l_link = isys_helper_link::create_url([
                    C__CMDB__GET__OBJECT     => $l_objID,
                    C__CMDB__GET__OBJECTTYPE => $l_objInfo["isys_obj_type__id"],
                    C__CMDB__GET__VIEWMODE   => C__CMDB__VIEW__LIST_CATEGORY,
                    C__CMDB__GET__CATG       => C__CMDB__SUBCAT__NETWORK_PORT,
                    C__CMDB__GET__TREEMODE   => $_GET[C__CMDB__GET__TREEMODE]
                ]);

                // exchange the specified column
                $p_arrRow["object_connection"] = '<a href="' . $l_link . '">' . $l_strImage . ' ' . $l_objInfo['isys_obj__title'] . '</a>';

                $p_arrRow["connector_title"] = $l_dao->get_assigned_connector_name(
                    $p_arrRow["isys_catg_port_list__isys_catg_connector_list__id"],
                    $p_arrRow["isys_cable_connection__id"]
                );
            } // if
        } // if

        if ($p_arrRow['isys_catg_port_list__state_enabled'] >= 1)
        {
            $p_arrRow['isys_catg_port_list__state_enabled'] = '<img src="' . $g_dirs['images'] . 'icons/silk/bullet_green.png" alt="" class="mr5 vam" />' .
                '<span class="green vam">' . _L('LC__UNIVERSAL__YES') . '</span>';
        }
        else
        {
            $p_arrRow['isys_catg_port_list__state_enabled'] = '<img src="' . $g_dirs['images'] . 'icons/silk/bullet_red.png" alt="" class="mr5 vam" />' .
                '<span class="red vam">' . _L('LC__UNIVERSAL__NO') . '</span>';
        } // if

        // Do not add a span otherwise the function get_order_condition does not work anymore
        $p_arrRow['isys_catg_port_list__title'] = isys_glob_str_stop($p_arrRow['isys_catg_port_list__title'], 30);

        if (!empty($p_arrRow['isys_catg_netp_list__title']))
        {
            $p_arrRow['interface'] = '<span title="' . $p_arrRow['isys_catg_netp_list__title'] . '">' . isys_glob_str_stop(
                    $p_arrRow['isys_catg_netp_list__title'],
                    30
                ) . '</span>';
        }
        elseif ($p_arrRow['isys_catg_hba_list__title'])
        {
            $p_arrRow['interface'] = '<span title="' . $p_arrRow['isys_catg_hba_list__title'] . '">' . isys_glob_str_stop(
                    $p_arrRow['isys_catg_hba_list__title'],
                    30
                ) . '</span>';
        } // if

        if (!empty($p_arrRow['isys_catg_port_list__port_speed_value']))
        {
            $p_arrRow['isys_catg_port_list__port_speed_value'] = isys_convert::speed(
                    $p_arrRow['isys_catg_port_list__port_speed_value'],
                    $p_arrRow['isys_port_speed__id'],
                    C__CONVERT_DIRECTION__BACKWARD
                ) . ' ' . $p_arrRow['isys_port_speed__title'];
        }
        else
        {
            $p_arrRow['isys_port_speed__bit_per_second'] = 'N/A';
        } // if

        $l_assigned_layer2_nets = isys_cmdb_dao_category_g_network_port::instance($this->get_database_component())
            ->get_attached_layer2_net($p_arrRow['isys_catg_port_list__id']);
        $l_default_vlan         = '';

        if (count($l_assigned_layer2_nets) > 0)
        {
            $l_quicklink = new isys_ajax_handler_quick_info();
            $l_list      = [];

            $i = 0;
            while ($l_l2_obj = $l_assigned_layer2_nets->get_row())
            {
                if ($i++ == isys_tenantsettings::get('cmdb.limits.port-lists-vlans', 10))
                {
                    $l_list[] = '...';
                    break;
                } // if

                if (empty($l_l2_obj['vlan']))
                {
                    $l_l2_obj['vlan'] = '-';
                } // if

                $l_list[] = $l_quicklink->get_quick_info(
                    $l_l2_obj['object_id'],
                    $l_l2_obj['title'] . ' (VLAN: ' . $l_l2_obj['vlan'] . ')',
                    C__LINK__OBJECT
                );

                if ($l_l2_obj['default_vlan'])
                {
                    $l_default_vlan = array_pop($l_list);
                } // if
            } // foreach

            if ($l_default_vlan)
            {
                $p_arrRow['assigned_layer2_nets'] = '<ul class="fl"><li class="border-bottom border-ccc mr10">Untagged (Standard VLAN)</li><li>' . $l_default_vlan . '</li></ul>';

                if (count($l_list))
                {
                    $p_arrRow['assigned_layer2_nets'] .= '<ul class="fl"><li class="border-bottom border-ccc">Tagged</li><li>' . implode('</li><li>', $l_list) . '</li></ul>';
                } // if
            }
            else
            {
                $p_arrRow['assigned_layer2_nets'] = '<ul class="fl"><li>' . implode('</li><li>', $l_list) . '</li></ul>';
            } // if
        }
        else
        {
            $p_arrRow['assigned_layer2_nets'] = isys_tenantsettings::get('gui.empty_value', '-');
        } // if
    } // function

    /**
     * Retrieve the header-fields.
     *
     * @return  array
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_fields()
    {
        return [
            'isys_catg_port_list__title'            => 'LC__CMDB__CATG__NETWORK__TITLE',
            'interface'                             => 'LC__CMDB__CATG__NETWORK_TREE_CONFIG_INTERFACE_P',
            'isys_port_type__title'                 => 'LC__CMDB__CATG__NETWORK__TYPE',
            'isys_catg_port_list__port_speed_value' => 'LC__CMDB__CATG__PORT__SPEED',
            'isys_catg_port_list__mac'              => 'LC__CMDB__CATG__NETWORK__MAC',
            'assigned_layer2_nets'                  => 'LC__CMDB__LAYER2_NET',
            'assigned_ips'                          => 'LC__CATP__IP__ADDRESS',
            'object_connection'                     => 'LC__CMDB__CATG__NETWORK__TARGET_OBJECT',
            'connector_title'                       => 'LC__CATG__STORAGE_CONNECTION_TYPE',
            'isys_catg_port_list__state_enabled'    => 'LC__CATP__IP__ACTIVE'
        ];
    } // function
} // class
