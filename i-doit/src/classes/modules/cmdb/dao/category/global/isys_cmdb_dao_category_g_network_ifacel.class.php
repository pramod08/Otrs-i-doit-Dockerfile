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
 * DAO: Global category for interfaces (logical).
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_network_ifacel extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'network_ifacel';
    /**
     * Category's constant.
     *
     * @var   string
     * @todo  No standard behavior!
     */
    protected $m_category_const = 'C__CMDB__SUBCAT__NETWORK_INTERFACE_L';
    /**
     * Category's identifier.
     *
     * @var   integer
     * @todo  No standard behavior!
     */
    protected $m_category_id = C__CMDB__SUBCAT__NETWORK_INTERFACE_L;
    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;
    /**
     * Main table where properties are stored persistently.
     *
     * @var   string
     * @todo  No standard behavior!
     */
    protected $m_table = 'isys_catg_log_port_list';
    /**
     * Category's template file.
     *
     * @var  string
     */
    protected $m_tpl = 'catg__interface_l.tpl';

    /**
     * Dynamic property handling for getting the assigned host addresses for the logical port.
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_addresses($p_row)
    {
        global $g_comp_database;

        $l_ident = $p_row['logp_addresses'] ?: $p_row['isys_catg_log_port_list__id'];

        $l_sql = "SELECT isys_cats_net_ip_addresses_list__title FROM isys_cats_net_ip_addresses_list
			INNER JOIN isys_catg_ip_list ON isys_catg_ip_list__isys_cats_net_ip_addresses_list__id = isys_cats_net_ip_addresses_list__id
			WHERE isys_catg_ip_list__isys_catg_log_port_list__id = '" . $l_ident . "'";

        $l_res = $g_comp_database->query($l_sql);

        if (count($l_res) > 0)
        {
            $l_return = [];

            while ($l_row = $g_comp_database->fetch_row_assoc($l_res))
            {
                $l_return[] = $l_row['isys_cats_net_ip_addresses_list__title'];
            } // while

            return '<ul><li>' . implode(',</li><li>', $l_return) . '</li></ul>';
        }
        else
        {
            return isys_tenantsettings::get('gui.empty_value', '-');
        } // if
    } // function

    /**
     * Dynamic property handling for getting the assigned ports to the logical port.
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_ports($p_row)
    {
        global $g_comp_database;

        $l_ident = $p_row['logp_ports'] ?: $p_row['isys_catg_log_port_list__id'];

        $l_sql = "SELECT p.isys_catg_port_list__title FROM isys_catg_port_list AS p
			INNER JOIN isys_catg_port_list_2_isys_catg_log_port_list AS lp_p ON lp_p.isys_catg_port_list__id = p.isys_catg_port_list__id
			WHERE lp_p.isys_catg_log_port_list__id = '" . $l_ident . "'";

        $l_res = $g_comp_database->query($l_sql);

        if (count($l_res) > 0)
        {
            $l_return = [];

            while ($l_row = $g_comp_database->fetch_row_assoc($l_res))
            {
                $l_return[] = $l_row['isys_catg_port_list__title'];
            } // while

            return '<ul><li>' . implode(',</li><li>', $l_return) . '</li></ul>';
        }
        else
        {
            return isys_tenantsettings::get('gui.empty_value', '-');
        } // if
    } // function

    /**
     * Callback method for the multiselection object-browser.
     *
     * @global  isys_component_database $g_comp_database
     *
     * @param   isys_request            $p_request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_net(isys_request $p_request)
    {
        global $g_comp_database;

        $l_return = [];

        $l_logport_dao = isys_cmdb_dao_category_g_network_ifacel::instance($g_comp_database);

        $l_cat_id = $p_request->get_category_data_id();

        if ($l_cat_id > 0)
        {
            $l_res = $l_logport_dao->get_attached_layer_2_net($l_cat_id, null, false, true);

            while ($l_row = $l_res->get_row())
            {
                $l_return[] = $l_row['isys_obj__id'];
            } // while
        } // if

        return $l_return;
    } // function

    /**
     * Callback method for the port dialog-list-field.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function callback_property_ports(isys_request $p_request)
    {
        $l_obj_id = $p_request->get_object_id();

        $l_dao_port = new isys_cmdb_dao_category_g_network_port($this->get_database_component());
        $l_ports    = $l_dao_port->get_data(null, $l_obj_id);
        $l_cat_id   = $p_request->get_category_data_id();
        $l_return   = [];

        if ($l_cat_id > 0)
        {
            $l_attached_ports = $this->get_ports_for_ifacel($l_cat_id);
            if (is_array($l_attached_ports))
            {
                $l_attached_ports = array_flip($l_attached_ports);
                $l_return         = [];

                while ($l_port = $l_ports->get_row())
                {
                    // @todo Is the "link" field used or necessary? Remove if possible.
                    $l_return[] = [
                        "id"   => $l_port["isys_catg_port_list__id"],
                        "val"  => $l_port["isys_catg_port_list__title"],
                        "sel"  => in_array($l_port['isys_catg_port_list__id'], $l_attached_ports),
                        "link" => isys_helper_link::create_catg_item_url(
                            [
                                C__CMDB__GET__OBJECT   => $l_obj_id,
                                C__CMDB__GET__CATG     => C__CATG__IP,
                                C__CMDB__GET__CATLEVEL => $l_port["isys_catg_ip_list__id"]
                            ]
                        )
                    ];
                } // while
            } // if
        } // if

        return $l_return;
    } // function

    /**
     * Callback method for the parent port dialog-field.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function callback_property_parent(isys_request $p_request)
    {
        $l_return = [];
        $l_obj_id = $p_request->get_object_id();

        if ($l_obj_id > 0)
        {
            $l_stacking_dao = isys_cmdb_dao_category_g_stack_member::instance($this->m_db);

            $l_stack_res = $l_stacking_dao->get_stacking_meta($l_obj_id);

            if (count($l_stack_res))
            {
                while ($l_stack_row = $l_stack_res->get_row())
                {
                    // Here we retrieve the meta "stacking" object.
                    $l_stack_object = $l_stack_row['isys_obj__id'];

                    // Now we fetch all stack members to then iterate over all logical ports.
                    $l_members_res = $l_stacking_dao->get_connected_objects($l_stack_object);
                    $l_key         = _L($l_stack_row['isys_obj_type__title']) . ' &raquo; ' . $l_stack_row['isys_obj__title'] . ' (#' . $l_stack_object . ')';

                    if (!isset($l_return[$l_key]))
                    {
                        $l_return[$l_key] = [];
                    } // if

                    while ($l_member_row = $l_members_res->get_row())
                    {
                        if ($l_member_row['isys_catg_stack_member_list__stack_member'] == $l_obj_id)
                        {
                            // Skip, if we found the current object itself.
                            continue;
                        } // if

                        $l_log_port_res = $this->get_data(null, $l_member_row['isys_catg_stack_member_list__stack_member'], '', null, C__RECORD_STATUS__NORMAL);

                        while ($l_log_port_row = $l_log_port_res->get_row())
                        {

                            $l_return[$l_key][$l_log_port_row['isys_catg_log_port_list__id']] = _L(
                                    $l_log_port_row['isys_obj_type__title']
                                ) . ' &raquo; ' . $l_log_port_row['isys_obj__title'] . ' &raquo; ' . $l_log_port_row['isys_catg_log_port_list__title'];
                        } // while
                    } // while
                } // while
            } // if
        } // if

        foreach ($l_return as $l_id => $l_ports)
        {
            if (empty($l_ports))
            {
                unset($l_return[$l_id]);
            } // if
        } // foreach

        return $l_return;
    } // function

    /**
     *
     * @param   integer $p_obj_id
     * @param   integer $p_cat_entry_id
     *
     * @return  array
     */
    public function get_logical_ports($p_obj_id, $p_cat_entry_id = null)
    {
        $l_return = [];
        $l_res    = $this->get_data(null, $p_obj_id);

        while ($l_row = $l_res->get_row())
        {
            // Avoid loops.
            if ($p_cat_entry_id !== null && $l_row['isys_catg_log_port_list__parent'] == $p_cat_entry_id)
            {
                continue;
            } // if

            // Ignore same category data.
            if ($l_row['isys_catg_log_port_list__id'] == $p_cat_entry_id)
            {
                continue;
            } // if

            $l_return[$l_row['isys_catg_log_port_list__id']] = $l_row['isys_catg_log_port_list__title'];
        } // while

        return $l_return;
    } // function

    /**
     * Callback method for the hostaddress dialog-list-field.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function callback_property_addresses(isys_request $p_request)
    {

        $l_obj_id = $p_request->get_object_id();

        $l_res    = $this->get_ips_by_obj_id($l_obj_id);
        $l_cat_id = $p_request->get_category_data_id();
        $l_return = [];

        while ($l_row = $l_res->get_row())
        {
            $l_address = $l_row["isys_cats_net_ip_addresses_list__title"] ? $l_row["isys_cats_net_ip_addresses_list__title"] : $l_row["isys_catg_ip_list__hostname"];

            // @todo Is the "link" field used or necessary? Remove if possible.
            $l_return[] = [
                "id"   => $l_row["isys_catg_ip_list__id"],
                "val"  => $l_address ? $l_address : _L('LC__IP__EMPTY_ADDRESS'),
                "sel"  => ($l_row['isys_catg_ip_list__isys_catg_log_port_list__id'] == $l_cat_id),
                "link" => isys_helper_link::create_catg_item_url(
                    [
                        C__CMDB__GET__OBJECT   => $l_obj_id,
                        C__CMDB__GET__CATG     => C__CATG__IP,
                        C__CMDB__GET__CATLEVEL => $l_row["isys_catg_ip_list__id"]
                    ]
                )
            ];
        } // while

        return $l_return;
    } // function

    /**
     * Creates a 1:1 relation between to ports.
     *
     * @param   integer $p_log_port_a
     * @param   integer $p_log_port_b
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function attach_log_port($p_log_port_a, $p_log_port_b)
    {
        // First we detach both logical ports.
        $this->detach_log_port($p_log_port_a)
            ->detach_log_port($p_log_port_b);

        // And now we attach them.
        $l_sql = 'UPDATE isys_catg_log_port_list
			SET isys_catg_log_port_list__isys_catg_log_port_list__id = ' . $this->convert_sql_id($p_log_port_a) . '
			WHERE isys_catg_log_port_list__id = ' . $this->convert_sql_id($p_log_port_b) . ';';

        $this->update($l_sql);

        $l_sql = 'UPDATE isys_catg_log_port_list
			SET isys_catg_log_port_list__isys_catg_log_port_list__id = ' . $this->convert_sql_id($p_log_port_b) . '
			WHERE isys_catg_log_port_list__id = ' . $this->convert_sql_id($p_log_port_a) . ';';

        $this->update($l_sql);
        $this->apply_update();
    } // function

    /**
     * Detaches a 1:1 logical port relation.
     *
     * @param   integer $p_log_port
     *
     * @return  isys_cmdb_dao_category_g_network_ifacel
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function detach_log_port($p_log_port)
    {
        $l_row = $this->get_data($p_log_port)
            ->get_row();

        if ($l_row['isys_catg_log_port_list__isys_catg_log_port_list__id'] !== null)
        {
            // We have to detach the existing relation.
            $l_sql = 'UPDATE isys_catg_log_port_list
				SET isys_catg_log_port_list__isys_catg_log_port_list__id = NULL
				WHERE isys_catg_log_port_list__id = ' . $this->convert_sql_id($l_row['isys_catg_log_port_list__isys_catg_log_port_list__id']) . ';';

            $this->update($l_sql);
        } // if

        $l_sql = 'UPDATE isys_catg_log_port_list
			SET isys_catg_log_port_list__isys_catg_log_port_list__id = NULL
			WHERE isys_catg_log_port_list__id = ' . $this->convert_sql_id($p_log_port) . ';';

        $this->update($l_sql);
        $this->apply_update();

        return $this;
    }

    /**
     * Returns array with all ports connected to the logical interface.
     *     key   -> id of port
     *     value -> title of port
     *
     * @param   integer $p_nIfacelID
     *
     * @return  mixed
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_ports_for_ifacel($p_nIfacelID)
    {
        $l_return = [];

        if (is_numeric($p_nIfacelID))
        {
            $l_strSQL = 'SELECT * FROM isys_catg_log_port_list AS main
				INNER JOIN isys_catg_port_list_2_isys_catg_log_port_list AS con ON main.isys_catg_log_port_list__id = con.isys_catg_log_port_list__id
				INNER JOIN isys_catg_port_list AS main2 ON main2.isys_catg_port_list__id = con.isys_catg_port_list__id
				WHERE main.isys_catg_log_port_list__id = ' . $this->convert_sql_id($p_nIfacelID) . '
				AND main.isys_catg_log_port_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

            $l_ret = $this->retrieve($l_strSQL);

            if (count($l_ret))
            {
                while ($l_row = $l_ret->get_row())
                {
                    $l_return[$l_row["isys_catg_port_list__id"]] = $l_row["isys_catg_port_list__title"];
                } // while
            } // if
        } // if

        return $l_return;
    } // function

    /**
     * Clears all ip attachments for $p_netp_port_id.
     *
     * @param   integer $p_log_port_id
     * @param   integer $p_ip_port_id
     *
     * @return  boolean
     * @throws  isys_exception_dao
     */
    public function clear_ip_attachments($p_log_port_id = null, $p_ip_port_id = null)
    {
        if (isset($p_log_port_id) && $p_log_port_id > 0)
        {
            $l_delete = 'UPDATE isys_catg_ip_list SET
				isys_catg_ip_list__isys_catg_log_port_list__id = NULL,
				isys_catg_ip_list__isys_catg_port_list__id = NULL
				WHERE isys_catg_ip_list__isys_catg_log_port_list__id = ' . $this->convert_sql_id($p_log_port_id);

            $this->update($l_delete);
        } // if

        if (isset($p_ip_port_id) && $p_ip_port_id > 0)
        {
            $l_delete = 'UPDATE isys_catg_ip_list SET
				isys_catg_ip_list__isys_catg_log_port_list__id = NULL,
				isys_catg_ip_list__isys_catg_port_list__id = NULL
				WHERE isys_catg_ip_list__id = ' . $this->convert_sql_id($p_ip_port_id);

            $this->update($l_delete);
        } // if

        return $this->apply_update();
    } // function

    /**
     * Attaches an ip address to a port.
     *
     * @param   integer $p_ifacel_id
     * @param   integer $p_catg_ip_id
     *
     * @return  boolean
     * @author  Dennis Stücken <dstuecken@i-doit.de>
     */
    public function attach_ip($p_ifacel_id, $p_catg_ip_id)
    {
        if (is_numeric($p_ifacel_id) && is_numeric($p_catg_ip_id))
        {
            $l_sql = "UPDATE isys_catg_ip_list
				SET isys_catg_ip_list__isys_catg_log_port_list__id = " . $this->convert_sql_id($p_ifacel_id) . "
				WHERE isys_catg_ip_list__id = " . $this->convert_sql_id($p_catg_ip_id) . ";";

            return ($this->update($l_sql) && $this->apply_update());
        } // if

        return false;
    } // function

    /**
     * Save global category interface (logical) element
     *
     *
     * @param   integer & $p_cat_level interfaceID
     * @param   integer & $p_intOldRecStatus
     * @param   boolean $p_create
     *
     * @return  mixed
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function save_element(&$p_cat_level, &$p_intOldRecStatus, $p_create = false)
    {
        $l_bRet      = false;
        $l_ports     = null;
        $l_addresses = null;

        $l_arPosts = isys_module_request::get_instance()
            ->get_posts();

        // Try to get the ID of the element we are editing. Bugfix #3356.
        $l_nIfacelID = $_GET[C__CMDB__GET__CATLEVEL];

        $l_arSelectedValuesRaw = explode(",", $l_arPosts["C__CATG__INTERFACE_L__PORT_ALLOCATION__selected_values"]);

        foreach (array_flip($l_arSelectedValuesRaw) as $key => $val)
        {
            $l_ports[$key] = $val;
        } // foreach

        if (isset($l_arPosts['C__CATG__PORT__IP_ADDRESS__selected_values']))
        {
            assert('is_string($l_arPosts["C__CATG__PORT__IP_ADDRESS__selected_values"])');

            if (empty($l_arPosts['C__CATG__PORT__IP_ADDRESS__selected_values']))
            {
                $l_addresses = false;
            }
            else
            {
                $l_addresses = explode(',', $l_arPosts['C__CATG__PORT__IP_ADDRESS__selected_values']);
            } // if
        } // if

        // We convert all sorts of mac addresses to one "default" form.
        if (!empty($l_arPosts["C__CATG__INTERFACE_L__MAC"]))
        {
            $p_mac_raw = preg_replace('/[\s\.\-\:]+/i', '', $l_arPosts["C__CATG__INTERFACE_L__MAC"]);
            $p_mac     = [];

            if (strlen($p_mac_raw) == 48)
            {
                // We've got a binary!
                for ($i = 0;$i < 6;$i++)
                {
                    $p_mac[] = substr($p_mac_raw, ($i * 8), 8);
                } // for

                $p_mac = implode(':', $p_mac);
            }
            else
            {
                // We've got a HEX!
                for ($i = 0;$i < 6;$i++)
                {
                    $p_mac[] = substr($p_mac_raw, ($i * 2), 2);
                } // for

                $p_mac = implode(':', $p_mac);
            } // if
        } // if

        if ($p_create && empty($l_nIfacelID))
        {
            $l_nIfacelID = $this->create(
                $_GET[C__CMDB__GET__OBJECT],
                $l_arPosts["C__CATG__INTERFACE_L__TITLE"],
                isys_format_json::decode($l_arPosts["C__CATG__INTERFACE_L__NET__HIDDEN"]),
                $l_arPosts["C__CATG__INTERFACE_L__ACTIVE"],
                $l_arPosts["C__CATG__INTERFACE_L__STANDARD"],
                $l_arPosts["C__CATG__INTERFACE_L__TYPE"],
                $l_ports,
                $l_arPosts["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                C__RECORD_STATUS__NORMAL,
                $l_addresses,
                $p_mac,
                $l_arPosts["C__CATG__INTERFACE_L__PARENT"],
                $l_arPosts["C__CATG__INTERFACE_L__DEST__HIDDEN"]
            );
        }
        else
        {
            $l_bRet = $this->save(
                $l_nIfacelID,
                $l_arPosts["C__CATG__INTERFACE_L__TITLE"],
                isys_format_json::decode($l_arPosts["C__CATG__INTERFACE_L__NET__HIDDEN"]),
                $l_arPosts["C__CATG__INTERFACE_L__ACTIVE"],
                $l_arPosts["C__CATG__INTERFACE_L__STANDARD"],
                $l_arPosts["C__CATG__INTERFACE_L__TYPE"],
                $l_ports,
                $l_arPosts["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                C__RECORD_STATUS__NORMAL,
                $l_addresses,
                $p_mac,
                $l_arPosts["C__CATG__INTERFACE_L__PARENT"],
                $l_arPosts["C__CATG__INTERFACE_L__DEST__HIDDEN"]
            );
        } // if

        if ($l_nIfacelID)
        {
            return $l_nIfacelID;
        }
        else
        {
            return ($l_bRet) ? null : -1;
        } // if
    } // function

    /**
     * Attach ports to logical interface
     *
     * @param int   $p_id
     * @param array $p_ports
     *
     * @return bool
     */
    public function attach_ports($p_id, $p_ports)
    {

        $l_sql = "DELETE FROM isys_catg_port_list_2_isys_catg_log_port_list WHERE " . "isys_catg_log_port_list__id = '" . $p_id . "';";

        /* API-Workaround: Flip */
        if (isset($p_ports[0])) $p_ports = array_flip($p_ports);

        if ($this->update($l_sql))
        {
            if (is_array($p_ports) && count($p_ports) > 0)
            {
                foreach ($p_ports as $l_port_id => $l_tmp)
                {
                    if ($l_port_id > 0)
                    {
                        $l_sql = "INSERT INTO isys_catg_port_list_2_isys_catg_log_port_list " . "(isys_catg_log_port_list__id, " . "isys_catg_port_list__id) " . "VALUES ('" . $p_id . "', '" . $l_port_id . "');";

                        $this->update($l_sql);
                    }
                    else
                    {
                        return;
                    }
                }
            }

            return $this->apply_update();
        }

        return false;
    } // function

    public function save($p_id, $p_title, $p_net, $p_active, $p_standard, $p_type, $p_ports, $p_description, $p_status = C__RECORD_STATUS__NORMAL, $p_addresses = null, $p_mac = null, $p_parent = null, $p_connected_logport = null)
    {
        $l_data = $this->get_data($p_id)
            ->__to_array();

        $l_strSQL = "DELETE FROM " . "isys_catg_port_list_2_isys_catg_log_port_list " . "WHERE " . "isys_catg_log_port_list__id = " . "'" . $p_id . "'";
        $this->update($l_strSQL);

        if (!$this->apply_update())
        {
            throw new isys_exception_dao_cmdb("Could not delete port connections while saving.", __CLASS__);
        }

        $l_strSQL = "UPDATE " . "isys_catg_log_port_list " . "SET " . "isys_catg_log_port_list__title = " . $this->convert_sql_text(
                $p_title
            ) . " ," . "isys_catg_log_port_list__active = " . $this->convert_sql_int($p_active) . " ," . "isys_catg_log_port_list__description = " . $this->convert_sql_text(
                $p_description
            ) . " ," . "isys_catg_log_port_list__isys_netp_ifacel_standard__id = " . $this->convert_sql_id(
                $p_standard
            ) . ", " . "isys_catg_log_port_list__isys_netx_ifacel_type__id = " . $this->convert_sql_id(
                $p_type
            ) . ", " . "isys_catg_log_port_list__mac = " . $this->convert_sql_text($p_mac) . ", " . "isys_catg_log_port_list__parent = " . $this->convert_sql_id(
                $p_parent
            ) . ", " . "isys_catg_log_port_list__status = " . "'" . $p_status . "' " . "WHERE " . "isys_catg_log_port_list__id = " . $this->convert_sql_id($p_id);

        if (!$this->update($l_strSQL) || !$this->apply_update())
        {
            return false;
        }

        // Assigned logical port(s).
        if ($p_connected_logport !== null)
        {
            $this->attach_log_port($p_id, $p_connected_logport);
        }
        else
        {
            $this->detach_log_port($p_id);
        }

        $this->detach_layer2($p_id);

        if (isset($p_net)) $this->attach_layer_2_net($p_id, $p_net);

        // Ports:
        if (isset($p_ports)) $this->attach_ports($p_id, $p_ports);

        // Addresses:
        $this->clear_ip_attachments($p_id);
        if (isset($p_addresses))
        {
            if (is_array($p_addresses))
            {
                foreach ($p_addresses as $l_address)
                {
                    if ($l_address > 0)
                    {
                        $this->clear_ip_attachments(null, $l_address);
                        $this->attach_ip($p_id, $l_address);
                    }
                }
            }

        }

        return true;
    } // function

    /**
     * Create method.
     *
     * @param   integer $p_object_id
     * @param   string  $p_title
     * @param   integer $p_net
     * @param   integer $p_active
     * @param   integer $p_standard
     * @param   integer $p_type
     * @param   array   $p_ports
     * @param   string  $p_description
     * @param   integer $p_status
     * @param   mixed   $p_addresses Array with IPs or boolean false.
     *
     * @return  mixed  Integer with last inserted ID on success, boolean false on failure.
     */
    public function create($p_object_id, $p_title, $p_net, $p_active, $p_standard, $p_type, $p_ports, $p_description, $p_status = C__RECORD_STATUS__NORMAL, $p_addresses = null, $p_mac = null, $p_parent = null, $p_connected_logport = null)
    {
        $l_strSQL = "INSERT INTO isys_catg_log_port_list SET " . "isys_catg_log_port_list__title = " . $this->convert_sql_text(
                $p_title
            ) . " ," . "isys_catg_log_port_list__active = " . $this->convert_sql_int($p_active) . " ," . "isys_catg_log_port_list__description = " . $this->convert_sql_text(
                $p_description
            ) . " ," . "isys_catg_log_port_list__isys_netp_ifacel_standard__id = " . $this->convert_sql_id(
                $p_standard
            ) . ", " . "isys_catg_log_port_list__isys_netx_ifacel_type__id = " . $this->convert_sql_id(
                $p_type
            ) . ", " . "isys_catg_log_port_list__isys_obj__id = " . $this->convert_sql_id($p_object_id) . ", " . "isys_catg_log_port_list__mac = " . $this->convert_sql_text(
                $p_mac
            ) . ", " . "isys_catg_log_port_list__parent = " . $this->convert_sql_id($p_parent) . ", " . "isys_catg_log_port_list__status = " . $this->convert_sql_int(
                $p_status
            ) . ";";

        if (!$this->update($l_strSQL) || !$this->apply_update())
        {
            return false;
        } // if

        $l_last_id = $this->get_last_insert_id();

        // Assigned logical port(s).
        if ($p_connected_logport !== null)
        {
            $this->attach_log_port($l_last_id, $p_connected_logport);
        } // if

        // Assigned port(s):
        if (isset($p_ports))
        {
            $this->attach_ports($l_last_id, $p_ports);
        } // if

        // Layer2 net(s).
        if (isset($p_net))
        {
            $this->attach_layer_2_net($l_last_id, $p_net);
        } // if

        // Addresses:
        if (isset($p_addresses))
        {
            if (is_array($p_addresses))
            {
                foreach ($p_addresses as $l_address)
                {
                    if ($l_address > 0)
                    {
                        $this->clear_ip_attachments(null, $l_address);
                        $this->attach_ip($l_last_id, $l_address);
                    } // if
                } // foreach
            } // if
        } // if

        return $l_last_id;
    } // function

    /**
     * Deleting all connections to this interface (ports, etc).
     *
     * @param    integer $p_id
     *
     * @return   boolean
     * @throws   isys_exception_dao_cmdb
     * @version  Niclas Potthast <npotthast@i-doit.org>
     */
    public function delete($p_id)
    {
        if ($p_id > 0)
        {
            return ($this->update('DELETE FROM isys_catg_log_port_list WHERE isys_catg_log_port_list__id = ' . $this->convert_sql_id($p_id) . ';') && $this->apply_update());
        } // if

        return false;
    }

    /**
     * @return bool
     *
     * @param integer $p_nDirection
     *
     * @global        $g_comp_database
     */
    public function rank_element($p_nID, $p_nDirection, $p_strTable = null)
    {
        global $g_comp_database;

        $l_data   = $this->get_data($p_nID)
            ->__to_array();
        $l_status = $l_data["isys_catg_log_port_list__status"];

        if ($p_nDirection == C__CMDB__RANK__DIRECTION_DELETE)
        {
            if (($l_status + 1) < C__RECORD_STATUS__PURGE)
            {
                $l_sql = "UPDATE isys_catg_log_port_list SET isys_catg_log_port_list__status = isys_catg_log_port_list__status + 1 WHERE isys_catg_log_port_list__id = '" . $this->m_db->escape_string(
                        $p_nID
                    ) . "';";
                $this->update($l_sql);
            }
            else
            {
                return $this->delete($p_nID);
            }
        }
        else if ($p_nDirection == C__CMDB__RANK__DIRECTION_RECYCLE)
        {
            $l_sql = "UPDATE isys_catg_log_port_list SET isys_catg_log_port_list__status = isys_catg_log_port_list__status - 1 WHERE isys_catg_log_port_list__id = '" . $this->m_db->escape_string(
                    $p_nID
                ) . "';";
            $this->update($l_sql);
        }

        return $this->apply_update();
    }

    public function is_ip_address_attached($p_catg_ip_list__id, $p_netp_ifacel__id)
    {
        $l_query = "SELECT * FROM isys_catg_ip_list WHERE TRUE ";

        if ($p_netp_ifacel__id > 0)
        {
            $l_query .= " AND isys_catg_ip_list__isys_catg_log_port_list__id = " . $this->convert_sql_id($p_netp_ifacel__id);
        } // if

        if ($p_catg_ip_list__id > 0)
        {
            $l_query .= " AND isys_catg_ip_list__id = " . $this->convert_sql_id($p_catg_ip_list__id);
        } // if

        return (count($this->retrieve($l_query . ';')) > 0);
    } // function

    /**
     *
     * @param   integer $p_objID
     * @param   boolean $p_primary_only
     * @param   integer $p_netp_ifacel__id
     *
     * @return  isys_component_dao_result
     * @throws  isys_exception_database
     */
    public function get_ips_by_obj_id($p_objID, $p_primary_only = false, $p_netp_ifacel__id = null)
    {
        $l_sql = 'SELECT main.*, isys_cats_net_ip_addresses_list__title, isys_cats_net_list__mask  FROM
			isys_catg_ip_list AS main
			INNER JOIN isys_cats_net_ip_addresses_list ON isys_catg_ip_list__isys_cats_net_ip_addresses_list__id = isys_cats_net_ip_addresses_list__id
			INNER JOIN isys_cats_net_list ON isys_cats_net_list__isys_obj__id = isys_cats_net_ip_addresses_list__isys_obj__id
			WHERE TRUE';

        if ($p_objID > 0)
        {
            $l_sql .= ' AND main.isys_catg_ip_list__isys_obj__id = ' . $this->convert_sql_id($p_objID);
        } // if

        if ($p_primary_only)
        {
            $l_sql .= ' AND main.isys_catg_ip_list__primary = 1';
        } // if

        if ($p_netp_ifacel__id)
        {
            $l_sql .= ' AND main.isys_catg_ip_list__isys_catg_log_port_list__id = ' . $this->convert_sql_id($p_netp_ifacel__id);
        } // if

        return $this->retrieve($l_sql . ' GROUP BY main.isys_catg_ip_list__id;');
    } // function

    /**
     * Deletes assignment between log. port and layer 2 net.
     *
     * @param   integer $p_id     Category entry id
     * @param   integer $p_net_id Layer 2 object id
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function detach_layer2($p_id = null, $p_net_id = null)
    {
        // @todo  ...Is this really necessary? Is'nt a simple "empty()" enough?
        if ((!is_numeric($p_id) && !empty($p_id)) && empty($p_net_id))
        {
            return;
        }
        elseif (empty($p_id) && (!is_numeric($p_net_id) && !empty($p_net_id)))
        {
            return;
        } // if

        $l_delete = 'DELETE FROM isys_catg_log_port_list_2_isys_obj WHERE ';

        if ($p_id > 0)
        {
            $l_delete .= 'isys_catg_log_port_list__id = ' . $this->convert_sql_id($p_id);
        } // if

        if ($p_net_id > 0)
        {
            $l_delete .= 'isys_obj__id = ' . $this->convert_sql_id($p_net_id);
        } // if

        return ($this->update($l_delete) && $this->apply_update());
    }

    /**
     * Creates assignments between log. port and layer 2 net.
     *
     * @param   integer $p_id
     * @param   array   $p_array
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function attach_layer_2_net($p_id, $p_array)
    {

        if (empty($p_array)) return;

        if (is_array($p_array))
        {
            if (count($p_array) > 0) $l_arr = $p_array;
            else return false;
        }
        elseif (is_string($p_array))
        {
            if (strpos($p_array, ',')) $l_arr = explode(',', $p_array);
            else return false;
        }
        else
        {
            return false;
        }

        $l_sql = 'INSERT INTO isys_catg_log_port_list_2_isys_obj (isys_catg_log_port_list__id, isys_obj__id ) VALUES';

        foreach ($l_arr AS $l_obj_id)
        {
            $l_sql .= '(' . $this->convert_sql_id($p_id) . ',' . $this->convert_sql_id($l_obj_id) . '),';
        }
        $l_sql = substr($l_sql, 0, -1);

        if ($this->update($l_sql) && $this->apply_update()) return true;
        else return false;
    } // function

    /**
     * Gets attached layer 2 nets.
     *
     * @param   integer $p_id        category entry id
     * @param   integer $p_obj_id    object id crom log. port
     * @param   boolean $p_as_string return as string
     * @param   boolean $p_as_result
     *
     * @return  mixed
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function get_attached_layer_2_net($p_id = null, $p_obj_id = null, $p_as_string = false, $p_as_result = false)
    {
        if ($p_as_string)
        {
            return $this->get_attached_layer_2_net_as_string($p_id, $p_obj_id);
        }

        $l_sql = 'SELECT * FROM isys_catg_log_port_list_2_isys_obj AS con
			INNER JOIN isys_catg_log_port_list AS main ON main.isys_catg_log_port_list__id = con.isys_catg_log_port_list__id ';

        if ($p_id)
        {
            $l_sql .= 'WHERE con.isys_catg_log_port_list__id = ' . $this->convert_sql_id($p_id);
        }
        else if ($p_obj_id) // @vqh: Why else if??
        {
            $l_sql .= 'WHERE main.isys_catg_log_port_list__isys_obj__id  = ' . $this->convert_sql_id($p_obj_id);
        } // if

        $l_res = $this->retrieve($l_sql);

        if (!$p_as_result)
        {
            $l_arr = [];

            while ($l_row = $l_res->get_row())
            {
                $l_arr[] = $l_row['isys_obj__id'];
            } // while

            return $l_arr;
        }
        else
        {
            return $l_res;
        } // if
    } // function

    /**
     * Optimized way of retrieving layer 2 net assignments as string.
     *
     * @param   integer $p_id
     * @param   integer $p_obj_id
     *
     * @return  mixed
     * @throws  Exception
     * @throws  isys_exception_database
     * @author  Dennis Stücken <dstuecken@i-doit.de>
     */
    public function get_attached_layer_2_net_as_string($p_id = null, $p_obj_id = null)
    {
        $l_sql = 'SELECT GROUP_CONCAT(isys_obj__id) AS assignments
			FROM isys_catg_log_port_list_2_isys_obj AS con
			INNER JOIN isys_catg_log_port_list AS main ON main.isys_catg_log_port_list__id = con.isys_catg_log_port_list__id ';

        if ($p_id)
        {
            $l_sql .= 'WHERE con.isys_catg_log_port_list__id = ' . $this->convert_sql_id($p_id);
        }
        else if ($p_obj_id) // @vqh: Why else if??
        {
            $l_sql .= 'WHERE main.isys_catg_log_port_list__isys_obj__id  = ' . $this->convert_sql_id($p_obj_id);
        } // if

        return $this->retrieve($l_sql . ';')
            ->get_row_value('assignments');
    } // function

    /**
     * Compares category data for import.
     *
     * @todo Currently, every transformation (using helper methods) are skipped.
     * If your unique properties needs them, implement it!
     *
     * @param  array    $p_category_data_values
     * @param  array    $p_object_category_dataset
     * @param  array    $p_used_properties
     * @param  array    $p_comparison
     * @param  integer  $p_badness
     * @param  integer  $p_mode
     * @param  integer  $p_category_id
     * @param  string   $p_unit_key
     * @param  array    $p_category_data_ids
     * @param  mixed    $p_local_export
     * @param  boolean  $p_dataset_id_changed
     * @param  integer  $p_dataset_id
     * @param  isys_log $p_logger
     * @param  string   $p_category_name
     * @param  string   $p_table
     * @param  mixed    $p_cat_multi
     */
    public function compare_category_data(&$p_category_data_values, &$p_object_category_dataset, &$p_used_properties, &$p_comparison, &$p_badness, &$p_mode, &$p_category_id, &$p_unit_key, &$p_category_data_ids, &$p_local_export, &$p_dataset_id_changed, &$p_dataset_id, &$p_logger, &$p_category_name = null, &$p_table = null, &$p_cat_multi = null, &$p_category_type_id = null, &$p_category_ids = null, &$p_object_ids = null, &$p_already_used_data_ids = null)
    {
        $l_title = $p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['title']['value'];
        $l_mac   = $p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['mac']['value'];

        $l_unique_properties = [
            'isys_catg_log_port_list__title'
        ];

        $l_mapping = [
            'isys_catg_log_port_list__title' => $l_title,
            'isys_catg_log_port_list__mac'   => $l_mac
        ];

        // Iterate through local data sets:
        foreach ($p_object_category_dataset as $l_dataset_key => $l_dataset)
        {
            $p_dataset_id_changed = false;
            $p_dataset_id         = $l_dataset[$p_table . '__id'];

            if (isset($p_already_used_data_ids[$p_dataset_id]))
            {
                // Skip it ID has already been used
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$l_dataset_key] = $p_dataset_id;
                $p_logger->debug('  Dateset ID "' . $p_dataset_id . '" has already been handled. Skipping to next entry.');
                continue;
            } // if

            // Test the category data identifier:
            if ($p_mode === isys_import_handler_cmdb::C__USE_IDS && $p_category_data_values['data_id'] !== $p_dataset_id)
            {
                $p_badness[$p_dataset_id]++;
                $p_dataset_id_changed = true;

                if ($p_mode === isys_import_handler_cmdb::C__USE_IDS)
                {
                    continue;
                } // if
            } // if

            if ($l_dataset['isys_catg_log_port_list__title'] == $l_title || (!empty($l_dataset['isys_catg_log_port_list__mac']) && $l_dataset['isys_catg_log_port_list__mac'] == $l_mac))
            {
                // Check properties
                $p_badness[$p_dataset_id] = 0;
                foreach ($l_mapping AS $l_table_key => $l_value)
                {
                    if ($l_dataset[$l_table_key] != $l_value)
                    {
                        $p_badness[$p_dataset_id]++;
                        if (in_array($l_table_key, $l_unique_properties))
                        {
                            $p_badness[$p_dataset_id] += 1000;
                        } // if
                    } // if
                } // foreach

                if ($p_badness[$p_dataset_id] > isys_import_handler_cmdb::C__COMPARISON__THRESHOLD && $p_badness[$p_dataset_id] > 1000)
                {
                    //$p_logger->debug('Dataset differs completly from category data.');
                    $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$l_dataset_key] = $p_dataset_id;
                }
                else if ($p_badness[$p_dataset_id] == 0)
                {
                    // We found our dataset
                    //$p_logger->debug('Dataset and category data are the same.');
                    $p_comparison[isys_import_handler_cmdb::C__COMPARISON__SAME][$l_dataset_key] = $p_dataset_id;

                    return;
                }
                else
                {
                    //$p_logger->debug('Dataset differs partly from category data.');
                    $p_comparison[isys_import_handler_cmdb::C__COMPARISON__PARTLY][$l_dataset_key] = $p_dataset_id;
                } // if
            }
            else
            {
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$l_dataset_key] = $p_dataset_id;
            } // if

            // @todo check badness again
        } // foreach
    }

    /**
     * Get attached logical port.
     *
     * @param   integer $p_log_port_id
     *
     * @return  isys_component_dao_result
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function get_attached_log_port($p_log_port_id)
    {
        $l_sql = 'SELECT isys_obj__id, isys_obj__title, isys_catg_log_port_list__title FROM isys_catg_log_port_list
			INNER JOIN isys_obj ON isys_obj__id = isys_catg_log_port_list__isys_obj__id
			WHERE isys_catg_log_port_list__id = ' . $this->convert_sql_id($p_log_port_id) . ';';

        return $this->retrieve($l_sql);
    } // function

    /**
     * Method for retrieving all logical ports + their VRRP cluster assignment (if existent).
     *
     * @param   integer $p_object_id
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     * @throws  isys_exception_database
     */
    public function get_data_with_vrrp($p_object_id, $p_status = null)
    {
        $l_sql = 'SELECT
			local.isys_catg_log_port_list__id AS local_logport_id,
			local.isys_catg_log_port_list__title AS local_logport_title,
			isys_obj__id AS vrrp_obj_id,
			isys_obj__title AS vrrp_obj_title,
			isys_obj_type__title AS vrrp_obj_type_title
			FROM isys_catg_log_port_list AS local
			LEFT JOIN isys_catg_vrrp_member_list ON isys_catg_vrrp_member_list__isys_catg_log_port_list__id = isys_catg_log_port_list__id
			LEFT JOIN isys_obj ON isys_obj__id = isys_catg_vrrp_member_list__isys_obj__id
			LEFT JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
			WHERE isys_catg_log_port_list__isys_obj__id = ' . $this->convert_sql_id($p_object_id);

        if ($p_status !== null)
        {
            $p_status = $this->convert_sql_int($p_status);

            $l_sql .= ' AND local.isys_catg_log_port_list__status = ' . $p_status . '
				AND isys_catg_vrrp_member_list__status = ' . $p_status . '
				AND isys_obj__status = ' . $p_status;
        } // if

        return $this->retrieve($l_sql . ';');
    } // function

    /**
     * Method for retrieving the dynamic properties, used by the new list component.
     *
     * @return  array
     */
    protected function dynamic_properties()
    {
        return [
            '_ports'     => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__NETWORK_TREE_CONFIG_PORTS',
                    C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATG__NETWORK_TREE_CONFIG_PORTS'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD       => 'isys_catg_log_port_list__id',
                    C__PROPERTY__DATA__FIELD_ALIAS => 'logp_ports'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_callback_ports'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST => true,
                ]
            ],
            '_addresses' => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__IP_ADDRESS',
                    C__PROPERTY__INFO__DESCRIPTION => 'Hostaddress'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD       => 'isys_catg_log_port_list__id',
                    C__PROPERTY__DATA__FIELD_ALIAS => 'logp_addresses'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_callback_addresses'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST => true,
                ]
            ],
        ];
    } // function

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'title'              => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__TITLE',
                        C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__LOGBOOK__TITLE'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD       => 'isys_catg_log_port_list__title',
                        C__PROPERTY__DATA__TABLE_ALIAS => 'isys_catg_log_port_list'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__INTERFACE_L__TITLE'
                    ]
                ]
            ),
            'net'                => array_replace_recursive(
                isys_cmdb_dao_category_pattern::object_browser(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__INTERFACE_L__NET',
                        C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATG__INTERFACE_L__NET'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_log_port_list__id'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__INTERFACE_L__NET',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strPopupType' => 'browser_object_ng',
                            'typeFilter'     => 'C__OBJTYPE__LAYER2_NET',
                            'multiselection' => true,
                            'p_strValue'     => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_network_ifacel',
                                    'callback_property_net'
                                ]
                            )
                        ]

                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'log_port'
                        ]
                    ]
                ]
            ),
            'mac'                => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO  => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__NETWORK__MAC',
                        C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATG__NETWORK__MAC'
                    ],
                    C__PROPERTY__DATA  => [
                        C__PROPERTY__DATA__FIELD       => 'isys_catg_log_port_list__mac',
                        C__PROPERTY__DATA__TABLE_ALIAS => 'isys_catg_log_port_list'
                    ],
                    C__PROPERTY__UI    => [
                        C__PROPERTY__UI__ID => 'C__CATG__INTERFACE_L__MAC'
                    ],
                    C__PROPERTY__CHECK => [
                        C__PROPERTY__CHECK__VALIDATION => [
                            FILTER_CALLBACK,
                            [
                                'options' => [
                                    'isys_helper',
                                    'filter_mac_address'
                                ]
                            ]
                        ]
                    ]
                ]
            ),
            'port_type'          => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__NETWORK__TYPE',
                        C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATG__NETWORK__TYPE'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_log_port_list__isys_netx_ifacel_type__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_netx_ifacel_type',
                            'isys_netx_ifacel_type__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__INTERFACE_L__TYPE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_netx_ifacel_type',
                            'tab'        => '3',
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'dialog_plus'
                        ]
                    ]
                ]
            ),
            'ports'              => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_list(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__INTERFACE_L__PORT_ALLOCATION',
                        C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATG__NETWORK_TREE_CONFIG_PORTS'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_log_port_list__id'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__INTERFACE_L__PORT_ALLOCATION',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_network_ifacel',
                                    'callback_property_ports'
                                ]
                            ),
                            'tab'      => '4',
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH  => false,
                        C__PROPERTY__PROVIDES__REPORT  => false,
                        C__PROPERTY__PROVIDES__LIST    => false,
                        C__PROPERTY__PROVIDES__VIRTUAL => true
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'logiface_ports'
                        ]
                    ]
                ]
            ),
            'parent'             => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__INTERFACE_L__PARENT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Parent Port'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD       => 'isys_catg_log_port_list__parent',
                        C__PROPERTY__DATA__TABLE_ALIAS => 'log_port',
                        C__PROPERTY__DATA__REFERENCES  => [
                            'isys_catg_log_port_list',
                            'isys_catg_log_port_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATG__NETWORK_IFACEL__PARENT',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_network_ifacel',
                                    'callback_property_parent'
                                ]
                            ),
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'get_reference_value'
                        ]
                    ]
                ]
            ),
            'standard'           => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__PORT__STANDARD',
                        C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATG__PORT__STANDARD'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_log_port_list__isys_netp_ifacel_standard__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_netp_ifacel_standard',
                            'isys_netp_ifacel_standard__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__INTERFACE_L__STANDARD',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_netp_ifacel_standard',
                            'tab'        => '6'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'dialog_plus'
                        ]
                    ]
                ]
            ),
            'active'             => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATP__IP__ACTIVE',
                        C__PROPERTY__INFO__DESCRIPTION => 'LC__CATP__IP__ACTIVE'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_log_port_list__active'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__INTERFACE_L__ACTIVE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => serialize(get_smarty_arr_YES_NO())
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'get_yes_or_no'
                        ]
                    ]
                ]
            ),
            'addresses'          => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_list(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__IP_ADDRESS',
                        C__PROPERTY__INFO__DESCRIPTION => 'Hostaddress'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_log_port_list__id'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__PORT__IP_ADDRESS',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_network_ifacel',
                                    'callback_property_addresses'
                                ]
                            ),
                            'tab'      => '4',
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__IMPORT => false,
                        C__PROPERTY__PROVIDES__EXPORT => false,
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'log_port_assigned_ips',
                            ['isys_catg_log_port_list']
                        ]
                    ]
                ]
            ),
            'assigned_connector' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::object_browser(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__CONNECTOR__ASSIGNED_CONNECTOR',
                        C__PROPERTY__INFO__DESCRIPTION => 'Assigned to connector'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_log_port_list__isys_catg_log_port_list__id'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__INTERFACE_L__DEST',
                        C__PROPERTY__UI__PARAMS => [
                            "p_strPopupType"  => "browser_cable_connection_ng",
                            "secondSelection" => true,
                            "only_log_ports"  => true,
                            "groupFilter"     => "C__OBJTYPE_GROUP__INFRASTRUCTURE"
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__IMPORT    => false,
                        C__PROPERTY__PROVIDES__EXPORT    => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false
                    ]
                ]
            ),
            'description'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__LOGBOOK__DESCRIPTION'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_log_port_list__description',
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CMDB__SUBCAT__NETWORK_INTERFACE_L
                    ]
                ]
            )
        ];
    } // function

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param   array   $p_category_data Values of category data to be saved.
     * @param   integer $p_object_id     Current object identifier (from database).
     * @param   integer $p_status        Decision whether category data should be created or just updated.
     *
     * @return  mixed  Returns category data identifier (int) on success, true (bool) if nothing had to be done, otherwise false.
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties']))
        {
            switch ($p_status)
            {
                case isys_import_handler_cmdb::C__CREATE:
                    if ($p_object_id > 0)
                    {
                        return $this->create(
                            $p_object_id,
                            $p_category_data['properties']['title'][C__DATA__VALUE],
                            $p_category_data['properties']['net'][C__DATA__VALUE],
                            $p_category_data['properties']['active'][C__DATA__VALUE],
                            $p_category_data['properties']['standard'][C__DATA__VALUE],
                            $p_category_data['properties']['port_type'][C__DATA__VALUE],
                            $p_category_data['properties']['ports'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE],
                            C__RECORD_STATUS__NORMAL,
                            $p_category_data['properties']['addresses'][C__DATA__VALUE],
                            $p_category_data['properties']['mac'][C__DATA__VALUE],
                            $p_category_data['properties']['parent'][C__DATA__VALUE]
                        );
                    } // if
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    if ($p_category_data['data_id'] > 0)
                    {
                        $this->save(
                            $p_category_data['data_id'],
                            $p_category_data['properties']['title'][C__DATA__VALUE],
                            $p_category_data['properties']['net'][C__DATA__VALUE],
                            $p_category_data['properties']['active'][C__DATA__VALUE],
                            $p_category_data['properties']['standard'][C__DATA__VALUE],
                            $p_category_data['properties']['port_type'][C__DATA__VALUE],
                            $p_category_data['properties']['ports'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE],
                            C__RECORD_STATUS__NORMAL,
                            $p_category_data['properties']['addresses'][C__DATA__VALUE],
                            $p_category_data['properties']['mac'][C__DATA__VALUE],
                            $p_category_data['properties']['parent'][C__DATA__VALUE]
                        );

                        return $p_category_data['data_id'];
                    }
                    break;
            } // switch
        }

        return false;
    } // function
} // class