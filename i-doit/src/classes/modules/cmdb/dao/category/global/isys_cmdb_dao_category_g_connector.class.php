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
 * DAO: Global category Connector.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_connector extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'connector';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Cache connector paths.
     *
     * @var  array
     */
    private $m_connector_recursion_array = [];

    /**
     * Key for the cached connector path which is the start connector.
     *
     * @var  integer
     */
    private $m_start_connector;

    /**
     * Dynamic property for handling assigned connectors for lists
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_assigned_connector($p_row)
    {
        global $g_comp_database;

        $l_dao        = isys_cmdb_dao_category_g_connector::instance($g_comp_database);
        $l_limit      = isys_tenantsettings::get('cmdb.limits.connector-lists-assigned_connectors', 10) + 1;
        $l_quick_info = isys_factory::get_instance('isys_ajax_handler_quick_info');
        $l_res        = $l_dao->get_assigned_objects($p_row['isys_obj__id'], $l_limit);
        $l_strOut     = '-';

        if (count($l_res))
        {
            $i        = 1;
            $l_strOut = "<ul>";

            while ($l_row = $l_res->get_row())
            {
                if ($i++ == $l_limit)
                {
                    $l_strOut .= '<li>...</li>';
                    break;
                } // if

                $l_strOut .= '<li>' . $l_quick_info->get_quick_info(
                        $l_row['isys_obj__id'],
                        $l_row['isys_obj__title'] . " &raquo; " . $l_row['connector_name'],
                        C__LINK__OBJECT,
                        false
                    ) . '</li>';
            } // while

            $l_strOut .= "</ul>";
        } // if

        return $l_strOut;
    } // function

    /**
     * Callback method for the property "fiber wave lengths"
     *
     * @param   isys_request $p_request
     *
     * @return  array
     */
    public function callback_property_fiber_color_data(isys_request $p_request)
    {
        $l_cat_list               = [];
        $l_res_fiber_wave_lengths = $this->get_fiber_wave_lengths();

        while ($l_row_fiber_wave_length = $l_res_fiber_wave_lengths->get_row())
        {
            $l_cat_list[] = [
                "caption" => $l_row_fiber_wave_length['isys_fiber_wave_lengths__title'],
                "value"   => $l_row_fiber_wave_length['isys_fiber_wave_lengths__id']
            ];
        } //while

        return $l_cat_list;
    } // function

    /**
     * Gets all existing fiber wave lengths filtered by "normal" status.
     *
     * @return  isys_component_dao_result
     */
    public function get_fiber_wave_lengths()
    {
        return $this->retrieve('SELECT * FROM isys_fiber_wave_length WHERE isys_fiber_wave_length__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';');
    } // function

    /**
     * Callback method for the property "fiber wave lengths"
     *
     * @param \isys_request $p_request
     *
     * @return array
     * @throws \isys_exception_general
     */
    public function callback_property_fiber_color_arData(isys_request $p_request)
    {
        $l_catdata = $p_request->get_row();
        $l_ar_data = [];

        if (isset($l_catdata['isys_catg_connector_list__id']) && is_numeric($l_catdata['isys_catg_connector_list__id']))
        {
            $l_assigned_fiber_wave_lengths = isys_cmdb_dao_category_g_connector::instance(
                $this->get_database_component()
            )
                ->get_assigned_fiber_wave_lengths(null, $l_catdata['isys_catg_connector_list__id']);

            if ($l_assigned_fiber_wave_lengths->num_rows())
            {
                while ($l_row_fiber_wave_length = $l_assigned_fiber_wave_lengths->get_row())
                {
                    $l_ar_data[] = [
                        "caption" => $l_row_fiber_wave_length['isys_fiber_wave_length__title'],
                        "value"   => $l_row_fiber_wave_length['isys_fiber_wave_length__id']
                    ];
                } // while
            } // if
        } // if

        return $l_ar_data;
    } // function

    /**
     * Gets assigned fiber wave lengths.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_cat_data_id
     *
     * @return  isys_component_dao_result
     */
    public function get_assigned_fiber_wave_lengths($p_obj_id = null, $p_cat_data_id = null)
    {

        if (empty($p_obj_id) && empty($p_cat_data_id))
        {
            return false;
        }

        $l_sql = 'SELECT wavelength.isys_fiber_wave_length__id, wavelength.isys_fiber_wave_length__title
			FROM isys_catg_connector_list_2_isys_fiber_wave_length AS main
			INNER JOIN isys_catg_connector_list AS connector_list ON connector_list.isys_catg_connector_list__id = main.isys_catg_connector_list__id
			INNER JOIN isys_fiber_wave_length AS wavelength ON main.isys_fiber_wave_length__id = wavelength.isys_fiber_wave_length__id';

        $l_condition = null;

        if ($p_obj_id > 0)
        {
            $l_condition = ' WHERE connector_list.isys_catg_connector_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id);
        } // if

        if ($p_cat_data_id > 0)
        {
            $l_condition = ' WHERE main.isys_catg_connector_list__id = ' . $this->convert_sql_id($p_cat_data_id);
        } // if

        return $this->retrieve($l_sql . $l_condition . ';');
    } // function

    /**
     * Callback method for property "used fiber lead (RX)".
     *
     * @param   isys_request $p_request
     *
     * @return  string
     */
    public function callback_property_used_fiber_lead_rx(isys_request $p_request)
    {
        return $this->prepare_used_fibers_leads($p_request);
    } // function

    /**
     * Callback method for property "used fiber lead (TX)".
     *
     * @param   isys_request $p_request
     *
     * @return  string
     */
    public function callback_property_used_fiber_lead_tx(isys_request $p_request)
    {
        return $this->prepare_used_fibers_leads($p_request);
    } // function

    /**
     * Callback method for the notification option dialog-field.
     *
     * @param   isys_request $p_request
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_type(isys_request $p_request)
    {
        return serialize(
            [
                C__CONNECTOR__INPUT  => "LC__CATG__CONNECTOR__INPUT",
                C__CONNECTOR__OUTPUT => "LC__CATG__CONNECTOR__OUTPUT"
            ]
        );
    } // function

    /**
     * Get connector(s) by type.
     *
     * @param   integer $p_object_id
     * @param   integer $p_connector_type
     * @param   integer $p_status
     * @param   string  $p_filter
     * @param   string  $p_condition
     * @param   string  $p_sort
     * @param   string  $p_dir
     *
     * @return  isys_component_dao_result
     */
    public function get_data_by_type($p_object_id, $p_connector_type, $p_status = null, $p_filter = null, $p_condition = "", $p_sort = null, $p_dir = null)
    {
        return $this->get_data(
            null,
            $p_object_id,
            "AND (isys_catg_connector_list.isys_catg_connector_list__type = " . $this->convert_sql_id($p_connector_type) . ")" . $p_condition,
            $p_filter,
            $p_status,
            $p_sort,
            $p_dir
        );
    } // function

    /**
     * Get data by sibling id (isys_catg_connector_list__isys_catg_connector_list__id).
     *
     * @param   integer $p_connector_id
     * @param   integer $p_status
     * @param   string  $p_sort
     * @param   string  $p_dir
     *
     * @return  isys_component_dao_result
     */
    public function get_data_by_sibling($p_connector_id, $p_status = null, $p_sort = null, $p_dir = null)
    {
        return $this->get_data(
            null,
            null,
            "AND (isys_catg_connector_list.isys_catg_connector_list__isys_catg_connector_list__id = " . $this->convert_sql_id($p_connector_id) . ")",
            null,
            $p_status,
            $p_sort,
            $p_dir
        );
    } // function

    /**
     * Get corresponding sibling by connector.
     *
     * @param   integer $p_connector_id
     * @param   integer $p_type
     *
     * @return  isys_component_dao_result
     */
    public function get_sibling_by_connector($p_connector_id, $p_type = null)
    {
        if (!is_null($p_type))
        {
            $l_type = " AND (isys_catg_connector_list.isys_catg_connector_list__type = " . $this->convert_sql_id($p_type) . ")";
        } // if

        return $this->get_data(
            null,
            null,
            "AND (isys_catg_connector_list.isys_catg_connector_list__isys_catg_connector_list__id = " . $this->convert_sql_id($p_connector_id) . ")" . $l_type
        );
    } // function

    /**
     * Method for retrieving a sibling mod.
     *
     * @param   integer $p_connector_id
     *
     * @return  isys_component_dao_result
     */
    public function get_sibling_mod($p_connector_id)
    {
        $l_sql = "SELECT isys_catg_connector_list__isys_cable_connection__id FROM isys_catg_connector_list WHERE isys_catg_connector_list__id = " . $this->convert_sql_id(
                $p_connector_id
            );
        $l_res = $this->retrieve($l_sql);

        if ($l_res->num_rows())
        {
            $l_row   = $l_res->get_row();
            $l_cable = $l_row['isys_catg_connector_list__isys_cable_connection__id'];
            $l_sql   = "SELECT * FROM isys_catg_connector_list " . "LEFT JOIN isys_connection_type ON isys_connection_type__id = isys_catg_connector_list__isys_connection_type__id " . "WHERE isys_catg_connector_list__id != " . $this->convert_sql_id(
                    $p_connector_id
                ) . " " . "AND isys_catg_connector_list__isys_cable_connection__id = " . $this->convert_sql_id($l_cable) . ";";

            return $this->retrieve($l_sql);
        } // if
    }

    /**
     * Resolve a cable run.
     *
     * @param   integer $p_connector_id
     * @param   boolean $p_resolve_both_ways
     *
     * @return  array
     */
    public function resolve_cable_run($p_connector_id, $p_resolve_both_ways = true)
    {
        $l_dao_cable_connection = new isys_cmdb_dao_cable_connection($this->m_db);
        $l_connector_data       = $this->get_data($p_connector_id)
            ->__to_array();

        if ($this->m_connector_recursion_array[$p_connector_id] === null)
        {
            $this->m_connector_recursion_array[$p_connector_id] = [];
            $this->m_start_connector                            = $p_connector_id;
        } // if

        $l_assigned_connector     = $l_dao_cable_connection->get_assigned_connector($p_connector_id);
        $l_assigned_connector_row = $l_assigned_connector->get_row();

        $l_assigned_cable_connection = $l_assigned_connector_row["isys_catg_connector_list__isys_cable_connection__id"];

        // Get object title.
        $l_object_title = $l_dao_cable_connection->get_obj_name_by_id_as_string($l_connector_data["isys_catg_connector_list__isys_obj__id"]);
        try
        {
            $l_left = [];

            // Build starting point
            $l_right = [
                "CONNECTOR_ID"      => $l_connector_data["isys_catg_connector_list__id"],
                "CONNECTOR_TITLE"   => $l_connector_data["isys_catg_connector_list__title"],
                "CABLE_CONNECTION"  => $l_assigned_cable_connection,
                "CABLE_ID"          => $l_connector_data["cable_id"],
                "CABLE_TITLE"       => $l_connector_data["cable_title"],
                "OBJECT_TITLE"      => $l_object_title,
                "OBJECT_TYPE"       => $l_connector_data["isys_obj__isys_obj_type__id"],
                "OBJECT_ID"         => $l_connector_data["isys_obj__id"],
                "CONNECTOR_TYPE"    => $l_connector_data["isys_catg_connector_list__type"],
                "ASSIGNED_CATEGORY" => $l_connector_data["isys_catg_connector_list__assigned_category"],
                "SIBLING"           => false,
                "LINK"              => $this->prepare_link(
                    $l_connector_data["isys_catg_connector_list__id"],
                    $l_connector_data["isys_catg_connector_list__isys_obj__id"]
                ),
                "CONNECTION"        => $this->recurse_cable_run($l_connector_data["isys_catg_connector_list__id"])
            ];

            // If this port has got a sibling, get the other direction (left).
            if ($l_connector_data["isys_catg_connector_list__isys_catg_connector_list__id"] && $p_resolve_both_ways)
            {
                // Retrieve sibling infos and recurse it into right direction.
                $l_sibling_data             = $this->get_data($l_connector_data["isys_catg_connector_list__isys_catg_connector_list__id"])
                    ->__to_array();
                $l_sibling_cable_connection = $l_assigned_connector_row["isys_catg_connector_list__isys_cable_connection__id"];

                // Get object title.
                $l_object_title = $l_dao_cable_connection->get_obj_name_by_id_as_string($l_sibling_data["isys_catg_connector_list__isys_obj__id"]);

                // Build starting point.
                $l_left = [
                    "CONNECTOR_ID"      => $l_sibling_data["isys_catg_connector_list__id"],
                    "CONNECTOR_TITLE"   => $l_sibling_data["isys_catg_connector_list__title"],
                    "CABLE_CONNECTION"  => $l_sibling_cable_connection,
                    "CABLE_ID"          => $l_sibling_data["cable_id"],
                    "CABLE_TITLE"       => $l_sibling_data["cable_title"],
                    "OBJECT_TITLE"      => $l_object_title,
                    "OBJECT_TYPE"       => $l_sibling_data["isys_obj__isys_obj_type__id"],
                    "OBJECT_ID"         => $l_sibling_data["isys_obj__id"],
                    "CONNECTOR_TYPE"    => $l_sibling_data["isys_catg_connector_list__type"],
                    "ASSIGNED_CATEGORY" => $l_sibling_data["isys_catg_connector_list__assigned_category"],
                    "SIBLING"           => true,
                    "LINK"              => $this->prepare_link(
                        $l_sibling_data["isys_catg_connector_list__id"],
                        $l_sibling_data["isys_catg_connector_list__isys_obj__id"]
                    ),
                    "CONNECTION"        => $this->recurse_cable_run($l_connector_data["isys_catg_connector_list__isys_catg_connector_list__id"])
                ];

            } // if

            // Reverse left array.
            $l_data[C__DIRECTION__LEFT] = $l_left;

            // And just store the right one into l_data.
            $l_data[C__DIRECTION__RIGHT] = $l_right;
        }
        catch (Exception $e)
        {
            isys_notify::error($e->getMessage());
            throw new isys_exception_general($e->getMessage());
        }

        return $l_data;
    } // function

    /**
     * This method finds the first and last element of a complete cable-run.
     *
     * @param   integer $p_connector_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_first_and_last_cable_run_object($p_connector_id)
    {
        $l_cable_run = $this->resolve_cable_run($p_connector_id, false);

        $l_limit = 200;

        $l_first = $l_cable_run[C__DIRECTION__LEFT];
        $l_last  = $l_cable_run[C__DIRECTION__RIGHT];

        while (!$this->is_last_cable_run_object($l_last) && $l_limit > 1)
        {
            if (is_array($l_last['CONNECTION']))
            {
                $l_last = $l_last['CONNECTION'];
            } // if

            if (is_array($l_last['SIBLING']))
            {
                $l_last = $l_last['SIBLING'][0];
            } // if

            $l_limit--;
        } // while

        return [
            'first' => $l_first,
            'last'  => $l_last
        ];
    } // function

    /**
     * Get connector name by id.
     *
     * @param   integer $p_id
     *
     * @return  string
     */
    public function get_connector_name_by_id($p_id)
    {
        $l_title = $this->retrieve(
            'SELECT isys_catg_connector_list__title FROM isys_catg_connector_list WHERE isys_catg_connector_list__id = ' . $this->convert_sql_id($p_id) . ';'
        )
            ->get_row_value('isys_catg_connector_list__title');

        return (($l_title !== null) ? $l_title : false);
    } // function

    /**
     *
     * @param   integer $p_id
     * @param   string  $p_title
     *
     * @return  boolean
     * @throws  isys_exception_dao
     */
    public function set_connector_name($p_id, $p_title)
    {
        $l_sql = "UPDATE isys_catg_connector_list SET isys_catg_connector_list__title = " . $this->convert_sql_text(
                $p_title
            ) . " WHERE isys_catg_connector_list__id = " . $this->convert_sql_id($p_id) . ";";

        return $this->update($l_sql) && $this->apply_update();
    } // function

    /**
     * Return assigned category title.
     *
     * @param   string $p_str_cat_const
     *
     * @return  string
     */
    public function get_assigned_category_title($p_str_cat_const)
    {
        if (defined($p_str_cat_const))
        {
            return _L($this->get_catg_name_by_id_as_string(constant($p_str_cat_const)));
        }
        else
        {
            return _L("LC__CMDB__CATG__CONNECTORS");
        } // if
    } // function

    /**
     * Save element method.
     *
     * @param   integer $p_cat_level
     * @param   integer &$p_intOldRecStatus
     * @param   boolean $p_create
     *
     * @return  mixed
     */
    public function save_element(&$p_cat_level, &$p_intOldRecStatus, $p_create)
    {
        $l_catdata = $this->get_result()
            ->__to_array();
        $l_sibling = null;

        if (empty($_POST['C__CATG__CONNECTOR__INOUT']))
        {
            $_POST['C__CATG__CONNECTOR__INOUT'] = C__CONNECTOR__OUTPUT;
        } // if

        // Check sibling type.
        switch ($_POST["C__CATG__CONNECTOR__INOUT"])
        {
            case C__CONNECTOR__INPUT:
                $l_sibling = $_POST['C__CATG__CONNECTOR__SIBLING_OUT'];
                break;

            case C__CONNECTOR__OUTPUT:
                $l_sibling = $_POST['C__CATG__CONNECTOR__SIBLING_IN'];
                break;
        } // switch

        // Create new cable or use existing one.
        $l_cableID = $_POST['C__CATG__CONNECTOR__CABLE__HIDDEN'];

        if ($_POST['C__CATG__CONNECTOR__ASSIGNED_CONNECTOR__HIDDEN'] != "")
        {
            if (empty($l_cableID))
            {
                $l_cableID = isys_cmdb_dao_cable_connection::add_cable($_POST["C__CATG__CONNECTOR__ASSIGNED_CONNECTOR__CABLE_NAME"]);
            } // if
        } // if

        // Create new or save existing?
        if ($p_create)
        {
            // Get fitting title.
            if (empty($_POST["C__CATG__CONNECTOR__SUFFIX_COUNT"]))
            {
                $_POST["C__CATG__CONNECTOR__SUFFIX_COUNT"] = 1;
            } // if

            $l_connected = $_POST['C__CATG__CONNECTOR__ASSIGNED_CONNECTOR__HIDDEN'];

            $l_title_arr = isys_smarty_plugin_f_title_suffix_counter::generate_title_as_array($_POST, 'C__CATG__CONNECTOR', 'C__UNIVERSAL__TITLE');

            // Iterate through connector creation.
            for ($i = 0;$i < $_POST["C__CATG__CONNECTOR__SUFFIX_COUNT"];$i++)
            {
                $l_title = $l_title_arr[$i];

                // Create connector.
                $p_cat_level = $this->create(
                    $_GET[C__CMDB__GET__OBJECT],
                    $_POST['C__CATG__CONNECTOR__INOUT'],
                    $_POST['C__CATG__CONNECTOR__CONNECTED_NET__HIDDEN'],
                    $_POST['C__CATG__CONNECTOR__CONNECTION_TYPE'],
                    $l_title,
                    $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                    $l_sibling,
                    $l_connected,
                    "C__CATG__CONNECTOR",
                    $l_cableID,
                    null,
                    $_POST['C__CATG__CONNECTOR__INTERFACE'],
                    $_POST['C__CATG__CONNECTOR__USED_FIBER_LEAD_RX'],
                    $_POST['C__CATG__CONNECTOR__USED_FIBER_LEAD_TX'],
                    $_POST['C__CATG__CONNECTOR__FIBER_WAVE_LENGTHS']
                );

                // Create applicable output.
                if ($_POST["C__CATG__CONNECTOR__CREATE_APPLICABLE_OUTPUTS"] && $_POST['C__CATG__CONNECTOR__INOUT'] == C__CONNECTOR__INPUT)
                {
                    // Prepare sibling naming.
                    $l_schema_config = isys_settings::get(
                        'cmdb.connector.suffix-schema',
                        isys_format_json::encode(
                            [
                                "##INPUT## - OUT",
                                "- ##INPUT##",
                                "(*) ##INPUT##",
                            ]
                        )
                    );

                    if ($l_schema_config == 'null') // migration did an error. In DB the value for key 'cmdb.connector.suffix-schema' is 'null' as string
                    {
                        $l_schema_config = [
                            "##INPUT## - OUT",
                            "- ##INPUT##",
                            "(*) ##INPUT##",
                        ];

                        isys_settings::set('cmdb.connector.suffix-schema', isys_format_json::encode($l_schema_config));
                    }
                    else if (is_string($l_schema_config) && isys_format_json::is_json_array($l_schema_config))
                    {
                        $l_schema_config = isys_format_json::decode($l_schema_config);
                    } // if

                    if ($_POST['C__CATG__CONNECTOR__SUFFIX_SCHEMA'] == "-1")
                    {
                        $l_schema = $_POST['C__CATG__CONNECTOR__SUFFIX_SCHEMA_OWN'];
                    }
                    else
                    {
                        $l_schema = (isset($l_schema_config[$_POST['C__CATG__CONNECTOR__SUFFIX_SCHEMA']])) ? $l_schema_config[$_POST['C__CATG__CONNECTOR__SUFFIX_SCHEMA']] : min(
                            $l_schema_config
                        );
                    } // if

                    $l_sibling_title = str_replace("##INPUT##", $l_title, $l_schema);

                    // Create output sibling.
                    $l_out_id = $this->create(
                        $_GET[C__CMDB__GET__OBJECT],
                        C__CONNECTOR__OUTPUT,
                        $_POST['C__CATG__CONNECTOR__CONNECTED_NET__HIDDEN'],
                        $_POST['C__CATG__CONNECTOR__CONNECTION_TYPE'],
                        $l_sibling_title,
                        $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                        $p_cat_level,
                        null,
                        "C__CATG__CONNECTOR",
                        $l_cableID,
                        null,
                        $_POST['C__CATG__CONNECTOR__INTERFACE'],
                        $_POST['C__CATG__CONNECTOR__USED_FIBER_LEAD_RX'],
                        $_POST['C__CATG__CONNECTOR__USED_FIBER_LEAD_TX'],
                        $_POST['C__CATG__CONNECTOR__FIBER_WAVE_LENGTHS']
                    );

                    /* Save corresponding output into created input */
                    if ($l_out_id)
                    {
                        $this->save_parent($p_cat_level, $l_out_id);
                    } // if
                } // if

                $l_connected = null;
            } // for

            return true;
        }
        else
        {
            // Save connector.
            $p_intOldRecStatus = $l_catdata["isys_catg_connector_list__status"];
            $l_bRet            = $this->save(
                $l_catdata['isys_catg_connector_list__id'],
                $_POST['C__CATG__CONNECTOR__INOUT'],
                $_POST['C__CATG__CONNECTOR__CONNECTED_NET__HIDDEN'],
                $_POST['C__CATG__CONNECTOR__CONNECTION_TYPE'],
                $_POST['C__UNIVERSAL__TITLE'],
                $l_sibling,
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                $p_intOldRecStatus,
                $_POST['C__CATG__CONNECTOR__ASSIGNED_CONNECTOR__HIDDEN'],
                $l_cableID,
                null,
                $_POST['C__CATG__CONNECTOR__INTERFACE'],
                $_POST['C__CATG__CONNECTOR__USED_FIBER_LEAD_RX'],
                $_POST['C__CATG__CONNECTOR__USED_FIBER_LEAD_TX'],
                $_POST['C__CATG__CONNECTOR__FIBER_WAVE_LENGTHS']
            );

            $this->m_strLogbookSQL = $this->get_last_query();
        } // if

        return $l_bRet == true ? null : -1;
    } // function

    /**
     * Save method.
     *
     * @param   integer $p_list_id
     * @param   integer $p_type
     * @param   integer $p_connected_obj_id
     * @param   integer $p_connector_type
     * @param   string  $p_title
     * @param   integer $p_connector_sibling
     * @param   string  $p_description
     * @param   integer $p_status
     * @param   integer $p_connected_connector_id
     * @param   integer $p_cableID
     * @param   boolean $p_is_master_obj
     * @param   integer $p_interface
     * @param   integer $p_used_fiber_lead_rx
     * @param   integer $p_used_fiber_lead_tx
     * @param   string  $p_wave_lengths
     *
     * @return  boolean
     * @throws  Exception
     */
    public function save($p_list_id, $p_type, $p_connected_obj_id, $p_connector_type, $p_title, $p_connector_sibling = null, $p_description = null, $p_status = C__RECORD_STATUS__NORMAL, $p_connected_connector_id = null, $p_cableID = null, $p_is_master_obj = null, $p_interface = null, $p_used_fiber_lead_rx = null, $p_used_fiber_lead_tx = null, $p_wave_lengths = null)
    {
        if (is_null($p_type) || $p_type < 1 || $p_type > 2)
        {
            $p_type = C__CONNECTOR__OUTPUT;
        } // if

        $l_dao = new isys_cmdb_dao_cable_connection($this->m_db);

        // Get cable object if cable as not been passed
        if (empty($p_cableID))
        {
            $p_cableID = $l_dao->get_assigned_cable($p_list_id);
        } // if

        $l_dao->delete_cable_connection($l_dao->get_cable_connection_id_by_connector_id($p_list_id));

        if (!empty($p_connected_connector_id))
        {
            // Add cable connection.
            if ($p_cableID == "")
            {
                $p_cableID = isys_cmdb_dao_cable_connection::add_cable();
            } // if

            $l_cable_con = $l_dao->add_cable_connection($p_cableID);

            if ($p_is_master_obj)
            {
                $l_master_connector = $p_list_id;
            }
            else
            {
                $l_master_connector = $p_connected_connector_id;
            } // if

            if (!$l_dao->save_connection($p_list_id, $p_connected_connector_id, $l_cable_con, $l_master_connector))
            {
                throw new Exception("Could not create cable connection");
            } // if

            // We need to set the same wavelength, RX and TX data on the other end.
            $l_fiber_update_sql = 'UPDATE isys_catg_connector_list SET
				isys_catg_connector_list__used_fiber_lead_rx = ' . $this->convert_sql_id($p_used_fiber_lead_tx) . ',
				isys_catg_connector_list__used_fiber_lead_tx = ' . $this->convert_sql_id($p_used_fiber_lead_rx) . '
				WHERE isys_catg_connector_list__id = ' . $this->convert_sql_id($p_connected_connector_id) . ';';

            $this->update($l_fiber_update_sql);

            $this->remove_all_wavelengths($p_connected_connector_id);

            if (is_array($p_wave_lengths) && count($p_wave_lengths))
            {
                foreach ($p_wave_lengths as $l_wavelength)
                {
                    $this->add_wavelength($p_connected_connector_id, $l_wavelength);
                } // foreach
            } // if
        } // if

        if (!empty($p_connector_sibling) && $p_connector_sibling > 0)
        {
            $l_sibling = "isys_catg_connector_list__isys_catg_connector_list__id = " . $this->convert_sql_id($p_connector_sibling) . ", ";
        }
        else
        {
            $l_sibling = "isys_catg_connector_list__isys_catg_connector_list__id = NULL, ";

            // Update sibling
            $l_sibling_id = $this->get_sibling_by_connector($p_list_id)
                ->get_row_value('isys_catg_connector_list__id');
            if ($l_sibling_id > 0)
            {
                $l_update_sibling = 'UPDATE isys_catg_connector_list SET isys_catg_connector_list__isys_catg_connector_list__id = NULL
				WHERE isys_catg_connector_list__id = ' . $this->convert_sql_id($l_sibling_id);
                $this->update($l_update_sibling);
            } // if
        } // if

        $l_strSql = "UPDATE isys_catg_connector_list " . "INNER JOIN isys_connection " . "ON isys_catg_connector_list__isys_connection__id = isys_connection__id " . "SET " . "isys_connection__isys_obj__id  = " . $this->convert_sql_id(
                $p_connected_obj_id
            ) . ", " . "isys_catg_connector_list__isys_connection_type__id = " . $this->convert_sql_id(
                $p_connector_type
            ) . ", " . "isys_catg_connector_list__isys_interface__id = " . $this->convert_sql_id(
                $p_interface
            ) . ", " . "isys_catg_connector_list__used_fiber_lead_rx = " . $this->convert_sql_id(
                $p_used_fiber_lead_rx
            ) . ", " . "isys_catg_connector_list__used_fiber_lead_tx = " . $this->convert_sql_id(
                $p_used_fiber_lead_tx
            ) . ", " . $l_sibling . "isys_catg_connector_list__title = " . $this->convert_sql_text(
                $p_title
            ) . ", " . "isys_catg_connector_list__type = " . $this->convert_sql_id($p_type) . ", " . "isys_catg_connector_list__description = " . $this->convert_sql_text(
                $p_description
            ) . ", " . "isys_catg_connector_list__status = " . $this->convert_sql_id($p_status) . " " . "WHERE isys_catg_connector_list__id = " . $this->convert_sql_id(
                $p_list_id
            ) . " ";

        $l_assigned_category = $this->get_assigned_category_by_id($p_list_id);

        // Create implicit relation and update category titles.
        $l_title_update = false;
        switch ($l_assigned_category)
        {
            case "C__CMDB__SUBCAT__NETWORK_PORT":
                $l_title_update = "UPDATE isys_catg_port_list SET isys_catg_port_list__title = " . $this->convert_sql_text(
                        $p_title
                    ) . " WHERE isys_catg_port_list__isys_catg_connector_list__id = " . $this->convert_sql_id($p_list_id);
                break;
            case "C__CATG__CONTROLLER_FC_PORT":
                $l_title_update = "UPDATE isys_catg_fc_port_list SET isys_catg_fc_port_list__title = " . $this->convert_sql_text(
                        $p_title
                    ) . " WHERE isys_catg_fc_port_list__isys_catg_connector_list__id = " . $this->convert_sql_id($p_list_id);
                break;
            case "C__CATG__UNIVERSAL_INTERFACE":
                $l_title_update = "UPDATE isys_catg_ui_list SET isys_catg_ui_list__title = " . $this->convert_sql_text(
                        $p_title
                    ) . " WHERE isys_catg_ui_list__isys_catg_connector_list__id = " . $this->convert_sql_id($p_list_id);
                break;
            case "C__CATG__POWER_CONSUMER":
                $l_title_update = "UPDATE isys_catg_pc_list SET isys_catg_pc_list__title = " . $this->convert_sql_text(
                        $p_title
                    ) . " WHERE isys_catg_pc_list__isys_catg_connector_list__id = " . $this->convert_sql_id($p_list_id);
                break;
            case "C__CATG__POWER_SUPPLIER":
                $l_title_update = "UPDATE isys_catg_power_supplier_list SET isys_catg_power_supplier_list__title = " . $this->convert_sql_text(
                        $p_title
                    ) . " WHERE isys_catg_power_supplier_list__isys_catg_connector_list__id = " . $this->convert_sql_id($p_list_id);
                break;
        } // switch

        // Update title of corresponding category.
        if ($l_title_update)
        {
            $this->update($l_title_update);
        } // if

        if ($this->update($l_strSql) && $this->apply_update())
        {
            $this->remove_all_wavelengths($p_list_id);

            if (is_array($p_wave_lengths) && count($p_wave_lengths))
            {
                foreach ($p_wave_lengths as $l_wavelength)
                {
                    $this->add_wavelength($p_list_id, $l_wavelength);
                } // foreach
            } // if

            if ($p_connector_sibling > 0)
            {
                $this->save_parent($p_connector_sibling, $p_list_id);
            } // if

            return true;
        } // if

        return false;
    } // function

    /**
     * Retrieve the assigned category, by a given id.
     *
     * @param   integer $p_list_id
     *
     * @return  integer
     */
    public function get_assigned_category_by_id($p_list_id)
    {
        return $this->retrieve(
            'SELECT isys_catg_connector_list__assigned_category FROM isys_catg_connector_list WHERE isys_catg_connector_list__id = ' . $this->convert_sql_id(
                $p_list_id
            ) . ';'
        )
            ->get_row_value('isys_catg_connector_list__assigned_category');
    } // function

    /**
     * Save sibling.
     *
     * @param   integer $p_list_id
     * @param   integer $p_parent
     *
     * @return  boolean
     */
    public function save_parent($p_list_id, $p_parent)
    {
        $l_sql = 'UPDATE isys_catg_connector_list
			SET isys_catg_connector_list__isys_catg_connector_list__id = ' . $this->convert_sql_id($p_parent) . '
			WHERE isys_catg_connector_list__id = ' . $this->convert_sql_id($p_list_id);

        return $this->update($l_sql) && $this->apply_update();
    } // function

    /**
     * Create a connector.
     *
     * @param   integer $p_object_id
     * @param   integer $p_type
     * @param   integer $p_connected_obj_id
     * @param   integer $p_connection_type
     * @param   string  $p_title
     * @param   string  $p_description
     * @param   integer $p_connector_sibling
     * @param   integer $p_connected_connector_id
     * @param   string  $p_assigned_category
     * @param   integer $p_cableID
     * @param   integer $p_is_master_obj
     * @param   integer $p_interface
     * @param   integer $p_used_fiber_lead_rx
     * @param   integer $p_used_fiber_lead_tx
     * @param   string  $p_wave_lengths
     *
     * @return  integer
     * @throws  Exception
     * @throws  isys_exception_cmdb
     * @throws  isys_exception_dao
     */
    public function create($p_object_id, $p_type = C__CONNECTOR__OUTPUT, $p_connected_obj_id = null, $p_connection_type = null, $p_title = null, $p_description = null, $p_connector_sibling = null, $p_connected_connector_id = null, $p_assigned_category = "C__CATG__CONNECTOR", $p_cableID = null, $p_is_master_obj = null, $p_interface = null, $p_used_fiber_lead_rx = null, $p_used_fiber_lead_tx = null, $p_wave_lengths = null)
    {
        $l_dao_connection = isys_cmdb_dao_connection::instance($this->m_db);

        if (is_null($p_type) || $p_type < 1 || $p_type > 2) $p_type = C__CONNECTOR__OUTPUT;

        $l_sibling = '';
        if (!empty($p_connector_sibling))
        {
            $l_sibling = "isys_catg_connector_list__isys_catg_connector_list__id = " . $this->convert_sql_id($p_connector_sibling) . ", ";
        }

        $l_strSql = "INSERT INTO isys_catg_connector_list SET " . "isys_catg_connector_list__isys_connection__id  = " . $this->convert_sql_id(
                $l_dao_connection->add_connection($p_connected_obj_id)
            ) . ", " . $l_sibling . "isys_catg_connector_list__isys_connection_type__id = " . $this->convert_sql_id(
                $p_connection_type
            ) . ", " . "isys_catg_connector_list__isys_interface__id = " . $this->convert_sql_id(
                $p_interface
            ) . ", " . "isys_catg_connector_list__used_fiber_lead_rx = " . $this->convert_sql_id(
                $p_used_fiber_lead_rx
            ) . ", " . "isys_catg_connector_list__used_fiber_lead_tx = " . $this->convert_sql_id(
                $p_used_fiber_lead_tx
            ) . ", " . "isys_catg_connector_list__title = " . $this->convert_sql_text($p_title) . ", " . "isys_catg_connector_list__type = " . $this->convert_sql_id(
                $p_type
            ) . ", " . "isys_catg_connector_list__assigned_category = " . $this->convert_sql_text(
                $p_assigned_category
            ) . ", " . "isys_catg_connector_list__description = " . $this->convert_sql_text(
                $p_description
            ) . ", " . "isys_catg_connector_list__status = " . $this->convert_sql_id(
                C__RECORD_STATUS__NORMAL
            ) . ", " . "isys_catg_connector_list__isys_obj__id = " . $this->convert_sql_id($p_object_id);

        if ($this->update($l_strSql) && $this->apply_update())
        {
            $l_id = $this->get_last_insert_id();

            if ($p_connector_sibling > 0)
            {
                $this->save_parent($p_connector_sibling, $l_id);
            } // if

            $this->remove_all_wavelengths($l_id);

            if (is_array($p_wave_lengths) && count($p_wave_lengths))
            {
                foreach ($p_wave_lengths as $l_wavelength)
                {
                    $this->add_wavelength($l_id, $l_wavelength);
                } // foreach
            } // if

            if ($p_connected_connector_id != null)
            {
                $l_dao = new isys_cmdb_dao_cable_connection($this->m_db);

                if (empty($p_cableID))
                {
                    $p_cableID = isys_cmdb_dao_cable_connection::add_cable();
                } // if

                $l_dao->delete_cable_connection($l_dao->get_cable_connection_id_by_connector_id($p_connected_connector_id));
                $l_conID = $l_dao->add_cable_connection($p_cableID);

                if ($p_is_master_obj)
                {
                    $l_master_connector = $l_id;
                }
                else
                {
                    $l_master_connector = $p_connected_connector_id;
                }

                if (!$l_dao->save_connection($l_id, $p_connected_connector_id, $l_conID, $l_master_connector))
                {
                    throw new Exception("Could not create cable connection");
                } // if

                $l_fiber_update_sql = 'UPDATE isys_catg_connector_list SET
					isys_catg_connector_list__used_fiber_lead_rx = ' . $this->convert_sql_id($p_used_fiber_lead_tx) . ',
					isys_catg_connector_list__used_fiber_lead_tx = ' . $this->convert_sql_id($p_used_fiber_lead_rx) . '
					WHERE isys_catg_connector_list__id = ' . $this->convert_sql_id($p_connected_connector_id) . ';';

                $this->update($l_fiber_update_sql);

                $this->remove_all_wavelengths($p_connected_connector_id);

                if (is_array($p_wave_lengths) && count($p_wave_lengths))
                {
                    foreach ($p_wave_lengths as $l_wavelength)
                    {
                        $this->add_wavelength($p_connected_connector_id, $l_wavelength);
                    } // foreach
                } // if
            } // if

            return $l_id;
        }
        else
        {
            return false;
        } // if
    } // function

    /**
     * Post rank is called after a regular rank.
     *
     * @param   integer $p_list_id
     * @param   integer $p_direction
     * @param   string  $p_table
     *
     * @return  boolean
     */
    public function post_rank($p_list_id, $p_direction, $p_table)
    {
        $l_sql = 'UPDATE isys_catg_connector_list
			SET isys_catg_connector_list__isys_catg_connector_list__id = NULL
			WHERE isys_catg_connector_list__id = ' . $this->convert_sql_id($p_list_id) . '
			OR isys_catg_connector_list__isys_catg_connector_list__id = ' . $this->convert_sql_id($p_list_id) . ';';

        return $this->update($l_sql) && $this->apply_update();
    } // function

    /**
     * Fetches the connector-id by the given name.
     *
     * @param   string $p_title
     *
     * @return  integer
     */
    public function getConnectorIDByName($p_title)
    {
        return $this->retrieve(
            'SELECT isys_catg_connector_list__id FROM isys_catg_connector_list WHERE isys_catg_connector_list__title = ' . $this->convert_sql_text($p_title) . ';'
        )
            ->get_row_value('isys_catg_connector_list__id');
    } // function

    /**
     * Retrieve a additional export condition.
     *
     * @return  string
     */
    public function get_export_condition()
    {
        return " AND (isys_catg_connector_list.isys_catg_connector_list__assigned_category = 'C__CATG__CONNECTOR' " . " OR isys_catg_connector_list.isys_catg_connector_list__assigned_category = '" . C__CATG__CONNECTOR . "') ";
    } // function

    /**
     * Checks if the connector is assigned to the assigned object ID.
     *
     * @param   integer $p_connector_id
     * @param   integer $p_obj_id
     *
     * @return  boolean
     */
    public function is_connector_id_from_object($p_connector_id, $p_obj_id)
    {
        $l_sql = 'SELECT isys_catg_connector_list__id FROM isys_catg_connector_list
			WHERE isys_catg_connector_list__id = ' . $this->convert_sql_id($p_connector_id) . '
			AND isys_catg_connector_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id);

        return !!count($this->retrieve($l_sql));
    } // function

    /**
     * Pre rank entry before a regular rank.
     * Set the connected input or output with "null" before the regular rank. Otherwise the input and output will be both ranked.
     *
     * @param   integer $p_list_id
     * @param   integer $p_direction
     * @param   string  $p_table
     * @param   mixed   $p_checkMethod
     *
     * @return  boolean
     */
    public function pre_rank($p_list_id, $p_direction = null, $p_table = 'isys_catg_connector_list', $p_checkMethod)
    {
        $l_sql = 'UPDATE isys_catg_connector_list
			SET isys_catg_connector_list__isys_catg_connector_list__id = NULL
			WHERE isys_catg_connector_list__id = ' . $this->convert_sql_id($p_list_id) . '
			OR isys_catg_connector_list__isys_catg_connector_list__id = ' . $this->convert_sql_id($p_list_id) . ';';

        return $this->update($l_sql) && $this->apply_update();
    } // function

    /**
     * Fetches the connector-id by the given name as resultset.
     *
     * @param   string $p_title
     *
     * @return  isys_component_dao_result
     */
    public function get_connector_by_name_as_result($p_title)
    {
        return $this->retrieve('SELECT * FROM isys_catg_connector_list WHERE isys_catg_connector_list__title = ' . $this->convert_sql_text($p_title) . ';');
    } // function

    /**
     * Removes all assigned wavelengths from a given connector.
     *
     * @param   integer $p_entry_id
     *
     * @return  boolean
     * @throws  isys_exception_dao
     */
    public function remove_all_wavelengths($p_entry_id)
    {
        return $this->update(
            'DELETE FROM isys_catg_connector_list_2_isys_fiber_wave_length WHERE isys_catg_connector_list__id = ' . $this->convert_sql_id($p_entry_id) . ';'
        ) && $this->apply_update();
    } // function

    /**
     * Adds a wavelength to a given connector.
     *
     * @param   integer $p_entry_id
     * @param   integer $p_wavelength_id
     *
     * @return  boolean
     * @throws  isys_exception_dao
     */
    public function add_wavelength($p_entry_id, $p_wavelength_id)
    {
        $l_sql = 'INSERT INTO isys_catg_connector_list_2_isys_fiber_wave_length SET
			isys_catg_connector_list__id = ' . $this->convert_sql_id($p_entry_id) . ',
			isys_fiber_wave_length__id = ' . $this->convert_sql_id($p_wavelength_id) . ';';

        return $this->update($l_sql) && $this->apply_update();
    } // function

    /**
     * Update one or more connector types.
     *
     * @param   mixed   $p_entry_id May be an integer or array of integers.
     * @param   integer $p_connectory_type_id
     *
     * @return  boolean
     * @throws  isys_exception_dao
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function update_connector_type($p_entry_id, $p_connectory_type_id)
    {
        if (is_array($p_entry_id))
        {
            $l_condition = $this->prepare_in_condition($p_entry_id);
        }
        else
        {
            $l_condition = '= ' . $this->convert_sql_id($p_entry_id);
        } // if

        $l_sql = 'UPDATE isys_catg_connector_list SET
			isys_catg_connector_list__isys_connection_type__id = ' . $this->convert_sql_id($p_connectory_type_id) . '
			WHERE isys_catg_connector_list__id ' . $l_condition . ';';

        return $this->update($l_sql) && $this->apply_update();
    } // function

    public function get_assigned_objects($p_obj_id, $p_limit = null)
    {
        $l_sql = "SELECT
			isys_obj__id, isys_obj__title, connected_connector.isys_catg_connector_list__id AS con_connector, connected_connector.isys_catg_connector_list__title AS connector_name
			FROM isys_catg_connector_list
			INNER JOIN isys_cable_connection ON isys_catg_connector_list.isys_catg_connector_list__isys_cable_connection__id = isys_cable_connection__id
			INNER JOIN isys_catg_connector_list AS connected_connector ON connected_connector.isys_catg_connector_list__isys_cable_connection__id = isys_cable_connection__id
				AND (connected_connector.isys_catg_connector_list__id != isys_catg_connector_list.isys_catg_connector_list__id OR connected_connector.isys_catg_connector_list__id IS NULL)
			INNER JOIN isys_obj ON isys_obj__id = connected_connector.isys_catg_connector_list__isys_obj__id
			WHERE isys_catg_connector_list.isys_catg_connector_list__isys_obj__id = " . $this->convert_sql_id($p_obj_id);

        if ($p_limit !== null && $p_limit > 0)
        {
            $l_sql .= ' LIMIT ' . (int) $p_limit;
        } // if

        return $this->retrieve($l_sql . ';');
    } // function

    /**
     * Dynamic property price.
     *
     * @return  array
     */
    protected function dynamic_properties()
    {
        return [
            '_assigned_connector' => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__CONNECTOR__ASSIGNED_CONNECTOR',
                    C__PROPERTY__INFO__DESCRIPTION => 'Assigned to connector'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_callback_assigned_connector'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST       => true,
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__EXPORT     => false,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false
                ]
            ]
        ];
    } // function

    /**
     * Method for counting.
     *
     * @param   integer $p_obj_id
     *
     * @return  integer
     * @deprecated Use generic method
     */
    public function get_count($p_obj_id = null)
    {
        if (!empty($p_obj_id))
        {
            $l_obj_id = $p_obj_id;
        }
        else
        {
            $l_obj_id = $this->m_object_id;
        } // if

        $l_sql = 'SELECT COUNT(isys_obj__id) AS count FROM isys_catg_connector_list
			INNER JOIN isys_obj ON isys_catg_connector_list__isys_obj__id = isys_obj__id
			WHERE TRUE';

        if (!empty($l_obj_id))
        {
            $l_sql .= ' AND isys_catg_connector_list__isys_obj__id = ' . $this->convert_sql_id($l_obj_id);
        } // if

        return $this->retrieve($l_sql . ' AND isys_catg_connector_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';')
            ->get_row_value('count');
    } // function

    /**
     * Return Category Data.
     *
     * @param   integer $p_catg_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     *
     * @return  isys_component_dao_result
     */

    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null, $p_order_by = null, $p_direction = null)
    {
        $l_sql = "SELECT " . "isys_connection_type.*, isys_interface.*, used_fiber_lead_rx.*, used_fiber_lead_tx.*, " . "isys_cable_connection.*, cable.isys_obj__id AS cable_id, cable.isys_obj__title AS cable_title, " . "isys_connection.*, isys_obj.*, isys_catg_connector_list.*, isys_obj_type__title as object_type, " . "connected_connector.isys_catg_connector_list__title AS connector_name, connected_connector.isys_catg_connector_list__id AS con_connector, " . "cable.isys_obj__title AS cable_object, " . "(CASE
						WHEN (used_fiber_lead_rx.isys_catg_fiber_lead_list__id > 0 AND used_fiber_lead_tx.isys_catg_fiber_lead_list__id > 0)
						THEN CONCAT(used_fiber_lead_rx.isys_catg_fiber_lead_list__label, ', ', used_fiber_lead_tx.isys_catg_fiber_lead_list__label)
						WHEN used_fiber_lead_rx.isys_catg_fiber_lead_list__id > 0 THEN used_fiber_lead_rx.isys_catg_fiber_lead_list__label
						WHEN used_fiber_lead_tx.isys_catg_fiber_lead_list__id > 0 THEN used_fiber_lead_tx.isys_catg_fiber_lead_list__label
						ELSE NULL
					 END) AS fiber_wave_lengths " .

            "FROM isys_catg_connector_list " .

            "LEFT JOIN isys_connection_type ON isys_catg_connector_list.isys_catg_connector_list__isys_connection_type__id = isys_connection_type__id " . "LEFT JOIN isys_interface ON isys_catg_connector_list.isys_catg_connector_list__isys_interface__id = isys_interface__id " . "LEFT JOIN isys_catg_fiber_lead_list AS used_fiber_lead_rx ON isys_catg_connector_list.isys_catg_connector_list__used_fiber_lead_rx = used_fiber_lead_rx.isys_catg_fiber_lead_list__id " . "LEFT JOIN isys_catg_fiber_lead_list AS used_fiber_lead_tx ON isys_catg_connector_list.isys_catg_connector_list__used_fiber_lead_tx = used_fiber_lead_tx.isys_catg_fiber_lead_list__id " . "LEFT JOIN isys_cable_connection ON isys_catg_connector_list.isys_catg_connector_list__isys_cable_connection__id = isys_cable_connection__id " . "LEFT JOIN isys_obj AS cable ON isys_cable_connection__isys_obj__id = cable.isys_obj__id " . "LEFT JOIN isys_catg_connector_list AS connected_connector ON connected_connector.isys_catg_connector_list__isys_cable_connection__id = isys_cable_connection__id " . "AND (connected_connector.isys_catg_connector_list__id != isys_catg_connector_list.isys_catg_connector_list__id OR connected_connector.isys_catg_connector_list__id IS NULL) " .

            "INNER JOIN isys_connection ON isys_catg_connector_list.isys_catg_connector_list__isys_connection__id = isys_connection__id " . "INNER JOIN isys_obj ON isys_catg_connector_list.isys_catg_connector_list__isys_obj__id = isys_obj.isys_obj__id " . "INNER JOIN isys_obj_type ON isys_obj.isys_obj__isys_obj_type__id = isys_obj_type__id " .

            "WHERE TRUE " . $p_condition . ' ' . $this->prepare_filter($p_filter);

        if ($p_obj_id !== null)
        {
            $l_sql .= $this->get_object_condition($p_obj_id);
        } // if

        if ($p_catg_list_id !== null)
        {
            $l_sql .= " AND (isys_catg_connector_list.isys_catg_connector_list__id = " . $this->convert_sql_id($p_catg_list_id) . ")";
        } // if

        if ($p_status !== null)
        {
            $l_sql .= " AND isys_catg_connector_list.isys_catg_connector_list__status = " . $this->convert_sql_int($p_status);
        } // if

        $l_sql .= $this->sort($p_order_by, $p_direction);

        return $this->retrieve($l_sql);
    } // function

    /**
     * Creates the condition to the object table
     *
     * @param   mixed $p_obj_id
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function get_object_condition($p_obj_id = null, $p_alias = 'isys_obj')
    {
        $l_sql = '';

        if (!empty($p_obj_id))
        {
            if (is_array($p_obj_id))
            {
                $l_sql = ' AND (isys_catg_connector_list.isys_catg_connector_list__isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ') ';
            }
            else
            {
                $l_sql = ' AND (isys_catg_connector_list.isys_catg_connector_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ') ';
            } // if
        } // if

        return $l_sql;
    } // function

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function properties()
    {
        return [
            'title'              => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__TITLE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Title'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD       => 'isys_catg_connector_list__title',
                        C__PROPERTY__DATA__TABLE_ALIAS => 'isys_catg_connector_list'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__UNIVERSAL__TITLE'
                    ]
                ]
            ),
            'type'               => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__CONNECTOR__INOUT',
                        C__PROPERTY__INFO__DESCRIPTION => 'In-/Output'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_connector_list__type'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__CONNECTOR__INOUT',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData'     => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_connector',
                                    'callback_property_type'
                                ]
                            ),
                            'p_bDbFieldNN' => 1,
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__REPORT => false
                    ]
                ]
            ),
            'wiring_system'      => array_replace_recursive(
                isys_cmdb_dao_category_pattern::object_browser(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__CONNECTOR__CONNECTED_NET',
                        C__PROPERTY__INFO__DESCRIPTION => 'Wiring system'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_connector_list__isys_connection__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_connection',
                            'isys_connection__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__CONNECTOR__CONNECTED_NET',
                        C__PROPERTY__UI__PARAMS => [
                            isys_popup_browser_object_ng::C__CAT_FILTER => 'C__CATS__WS;C__CMDB__SUBCAT__WS_ASSIGNMENT;C__CMDB__SUBCAT__WS_NET_TYPE',
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'connection'
                        ]
                    ]
                ]
            ),
            'interface'          => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__CONNECTOR__INTERFACE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Interface'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_connector_list__isys_interface__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_interface',
                            'isys_interface__id'
                        ]
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CATG__CONNECTOR__INTERFACE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_interface'
                        ]
                    ]
                ]
            ),
            'fiber_wave_lengths' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::multiselect(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__CONNECTOR__FIBER_WAVE_LENGTHS',
                        C__PROPERTY__INFO__DESCRIPTION => 'Fiber wave lengths'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD       => 'isys_catg_connector_list__id',
                        C__PROPERTY__DATA__TABLE_ALIAS => 'fiber_wave_lengths',
                        C__PROPERTY__DATA__REFERENCES  => [
                            'isys_catg_connector_list_2_isys_fiber_wave_length',
                            'isys_catg_connector_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID      => 'C__CATG__CONNECTOR__FIBER_WAVE_LENGTHS',
                        C__PROPERTY__UI__PARAMS  => [
                            'p_strTable'   => 'isys_fiber_wave_length',
                            'placeholder'  => _L('LC__CATG__CONNECTOR__FIBER_WAVE_LENGTHS'),
                            'p_onComplete' => "idoit.callbackManager.triggerCallback('cmdb-catg-service-alias-update', selected);",
                            'multiselect'  => true,
                            //'p_arData'   => new isys_callback(array('isys_cmdb_dao_category_g_connector', 'callback_property_fiber_color_arData')),
                            //'data'      => new isys_callback(array('isys_cmdb_dao_category_g_connector', 'callback_property_fiber_color_data'))
                        ],
                        C__PROPERTY__UI__DEFAULT => null
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT     => false,
                        C__PROPERTY__PROVIDES__LIST       => false,
                        C__PROPERTY__PROVIDES__SEARCH     => false,
                        C__PROPERTY__PROVIDES__VALIDATION => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT  => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'dialog_multiselect'
                        ]
                    ]
                ]
            ),
            'connection_type'    => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__CONNECTOR__CONNECTION_TYPE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Connection type'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_connector_list__isys_connection_type__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_connection_type',
                            'isys_connection_type__id'
                        ]
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CATG__CONNECTOR__CONNECTION_TYPE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable'   => 'isys_connection_type',
                            'p_bDbFieldNN' => 1
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
                        C__PROPERTY__DATA__FIELD       => 'isys_catg_connector_list__id',
                        C__PROPERTY__DATA__TABLE_ALIAS => 'connected_connector',
                        C__PROPERTY__DATA__FIELD_ALIAS => 'con_connector'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__CONNECTOR__ASSIGNED_CONNECTOR',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strPopupType'  => 'browser_cable_connection_ng',
                            'secondSelection' => true
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH  => false,
                        //C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST    => false,
                        C__PROPERTY__PROVIDES__VIRTUAL => true
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'assigned_connector'
                        ]
                    ]
                ]
            ),
            'assigned_category'  => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    // This property has no UI field.
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__CONNECTOR__CATEGORY_TYPE',
                        C__PROPERTY__INFO__DESCRIPTION => 'LC__CATG__CONNECTOR__CATEGORY_TYPE'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_connector_list__assigned_category'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH     => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                        C__PROPERTY__PROVIDES__VALIDATION => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'get_connector_assigned_category'
                        ]
                    ]
                ]
            ),
            'cable_connection'   => array_replace_recursive(
                isys_cmdb_dao_category_pattern::object_browser(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__OBJTYPE__CABLE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Cable'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_connector_list__isys_cable_connection__id'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__CONNECTOR__CABLE',
                        C__PROPERTY__UI__PARAMS => [
                            'title'                                     => 'LC__BROWSER__TITLE__CABLE',
                            isys_popup_browser_object_ng::C__CAT_FILTER => 'C__CATG__CABLE',
                            'callback_accept'                           => "idoit.callbackManager.triggerCallback('catg_connector.attachFiberLead');",
                            'callback_detach'                           => "idoit.callbackManager.triggerCallback('catg_connector.detachFiberLead');"
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => true,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'cable_connection'
                        ]
                    ]
                ]
            ),
            'used_fiber_lead_rx' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__CONNECTOR__USED_FIBER_LEAD_RX',
                        C__PROPERTY__INFO__DESCRIPTION => 'fiber/lead (RX)'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_connector_list__used_fiber_lead_rx'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__CONNECTOR__USED_FIBER_LEAD_RX',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_connector',
                                    'callback_property_used_fiber_lead_rx'
                                ]
                            ),
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__LIST   => true,
                        C__PROPERTY__PROVIDES__REPORT => true
                    ]
                ]
            ),
            'used_fiber_lead_tx' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__CONNECTOR__USED_FIBER_LEAD_TX',
                        C__PROPERTY__INFO__DESCRIPTION => 'fiber/lead (TX)'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_connector_list__used_fiber_lead_tx'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__CONNECTOR__USED_FIBER_LEAD_TX',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_connector',
                                    'callback_property_used_fiber_lead_tx'
                                ]
                            ),
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__LIST   => true,
                        C__PROPERTY__PROVIDES__REPORT => true
                    ]
                ]
            ),
            'connector_sibling'  => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__CONNECTOR__SIBLING_IN_OR_OUT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Assigned Input/Output'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_connector_list__isys_catg_connector_list__id'
                    ],
                    // @todo This property has no field ID and has to be renamed.
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'connector'
                        ]
                    ]
                ]
            ),
            'description'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD       => 'isys_catg_connector_list__description',
                        C__PROPERTY__DATA__TABLE_ALIAS => 'isys_catg_connector_list'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CATG__CONNECTOR
                    ]
                ]
            ),
            'relation_direction' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'Relation direction',
                        C__PROPERTY__INFO__DESCRIPTION => 'Relation direction'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_connector_list__isys_catg_relation_list__id'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH     => false,
                        C__PROPERTY__PROVIDES__REPORT     => false,
                        C__PROPERTY__PROVIDES__LIST       => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                        C__PROPERTY__PROVIDES__VALIDATION => false,
                        C__PROPERTY__PROVIDES__IMPORT     => true,
                        C__PROPERTY__PROVIDES__EXPORT     => true,
                        C__PROPERTY__PROVIDES__VIRTUAL    => true
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'relation_direction'
                        ]
                    ]
                ]
            )
        ];
    } // function

    /**
     * @param array $p_category_data
     * @param int   $p_object_id
     * @param int   $p_status
     *
     * @return bool|int
     * @throws Exception
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties']))
        {
            $l_is_master_obj = ($p_category_data['properties']['relation_direction'][C__DATA__VALUE]) ? (($p_category_data['properties']['relation_direction'][C__DATA__VALUE] == $p_object_id) ? true : false) : false;

            if (empty($p_category_data['properties']['assigned_category'][C__DATA__VALUE]))
            {
                // Set default value
                $p_category_data['properties']['assigned_category'][C__DATA__VALUE] = 'C__CATG__CONNECTOR';
            } // if

            switch ($p_status)
            {
                case isys_import_handler_cmdb::C__CREATE:
                    if ($p_object_id > 0)
                    {
                        return $this->create(
                            $p_object_id,
                            $p_category_data['properties']['type'][C__DATA__VALUE],
                            $p_category_data['properties']['wiring_system'][C__DATA__VALUE],
                            $p_category_data['properties']['connection_type'][C__DATA__VALUE],
                            $p_category_data['properties']['title'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE],
                            $p_category_data['properties']['connector_sibling'][C__DATA__VALUE],
                            $p_category_data['properties']['assigned_connector'][C__DATA__VALUE],
                            $p_category_data['properties']['assigned_category'][C__DATA__VALUE],
                            null,
                            $l_is_master_obj,
                            $p_category_data['properties']['interface'][C__DATA__VALUE],
                            $p_category_data['properties']['used_fiber_lead_rx'][C__DATA__VALUE],
                            $p_category_data['properties']['used_fiber_lead_tx'][C__DATA__VALUE]
                        );
                    }
                    break;

                case isys_import_handler_cmdb::C__UPDATE:
                    if ($p_category_data['data_id'] > 0)
                    {
                        $this->save(
                            $p_category_data['data_id'],
                            $p_category_data['properties']['type'][C__DATA__VALUE],
                            $p_category_data['properties']['wiring_system'][C__DATA__VALUE],
                            $p_category_data['properties']['connection_type'][C__DATA__VALUE],
                            $p_category_data['properties']['title'][C__DATA__VALUE],
                            $p_category_data['properties']['connector_sibling'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE],
                            C__RECORD_STATUS__NORMAL,
                            $p_category_data['properties']['assigned_connector'][C__DATA__VALUE],
                            null,
                            null,
                            $p_category_data['properties']['interface'][C__DATA__VALUE],
                            $p_category_data['properties']['used_fiber_lead_rx'][C__DATA__VALUE],
                            $p_category_data['properties']['used_fiber_lead_tx'][C__DATA__VALUE]
                        );

                        return $p_category_data['data_id'];
                    }
                    break;
            }
        }

        return false;
    } // function

    /**
     *
     * @param   isys_request $p_request
     *
     * @return  string
     * @throws  Exception
     * @throws  isys_exception_database
     * @throws  isys_exception_general
     */
    protected function prepare_used_fibers_leads(isys_request $p_request)
    {
        $l_return   = [];
        $l_cat_data = $p_request->get_row();

        // We need a cable:
        if (isset($l_cat_data['isys_cable_connection__isys_obj__id']))
        {
            $l_dao = isys_cmdb_dao_category_g_fiber_lead::instance($this->get_database_component());

            $l_cable_object_id = (int) $l_cat_data['isys_cable_connection__isys_obj__id'];

            $l_fibers_leads = $l_dao->get_data(null, $l_cable_object_id);

            while ($l_fiber_lead = $l_fibers_leads->get_row())
            {
                $l_value = $l_fiber_lead['isys_catg_fiber_lead_list__label'];

                if (isset($l_fiber_lead['isys_cable_colour__title']) && !empty($l_fiber_lead['isys_cable_colour__title']))
                {
                    $l_value .= ' (' . $l_fiber_lead['isys_cable_colour__title'] . ')';
                } //if

                $l_return[$l_fiber_lead['isys_catg_fiber_lead_list__id']] = $l_value;
            } // while
        } // if

        return $l_return;
    } // function

    /**
     * Prepares a link.
     *
     * @param   integer $p_connector_id
     * @param   integer $p_object_id
     *
     * @return  string
     */
    private function prepare_link($p_connector_id, $p_object_id)
    {
        return isys_helper_link::create_url(
            [
                C__CMDB__GET__OBJECT   => $p_object_id,
                C__CMDB__GET__VIEWMODE => C__CMDB__VIEW__CATEGORY_GLOBAL,
                C__CMDB__GET__CATG     => $this->get_category_id(),
                C__CMDB__GET__TREEMODE => C__CMDB__VIEW__TREE_OBJECT,
                C__CMDB__GET__CATLEVEL => $p_connector_id
            ]
        );
    } // function

    /**
     * Recursive function to retrieve a complete cable run.
     *
     * @param   integer $p_connector_id
     * @param   array   $p_data
     *
     * @return  array
     */
    private function recurse_cable_run($p_connector_id, $p_data = [])
    {
        $l_data = [];

        // Initialize dao's.
        $l_dao_cable_connection = new isys_cmdb_dao_cable_connection($this->m_db);
        $l_assigned_connector   = $l_dao_cable_connection->get_assigned_connector($p_connector_id)
            ->__to_array();

        if ($l_assigned_connector['isys_catg_connector_list__id'] > 0 && in_array(
                $l_assigned_connector['isys_catg_connector_list__id'],
                $this->m_connector_recursion_array[$this->m_start_connector]
            )
        )
        {
            // Link to connected object.
            $l_arrConnectedObj   = [
                C__CMDB__GET__OBJECT     => $l_assigned_connector["isys_obj__id"],
                C__CMDB__GET__OBJECTTYPE => $l_assigned_connector["isys_obj_type__id"],
                C__CMDB__GET__VIEWMODE   => C__CMDB__VIEW__CATEGORY_GLOBAL,
                C__CMDB__GET__CATG       => C__CATG__CONNECTOR,
            ];
            $l_exception_message = sprintf(
                _L('LC__REPORT__VIEW__OPEN_CABLE_CONNECTIONS__EXCEPTION'),
                '<a href="' . isys_helper_link::create_url($l_arrConnectedObj) . '">' . $l_assigned_connector['isys_obj__title'] . '</a>',
                $l_assigned_connector['isys_catg_connector_list__title']
            );
            throw new Exception($l_exception_message);
        } // if

        // Add connector id to recursion array
        $this->m_connector_recursion_array[$this->m_start_connector][] = $l_assigned_connector['isys_catg_connector_list__id'];

        if (isset($l_assigned_connector["isys_catg_connector_list__id"]) && !is_null($l_assigned_connector["isys_catg_connector_list__id"]))
        {

            // Get object title.
            $l_object_title = $l_dao_cable_connection->get_obj_name_by_id_as_string($l_assigned_connector["isys_catg_connector_list__isys_obj__id"]);

            // Add Connector to array structure.
            $l_data = [
                "CONNECTOR_ID"      => $l_assigned_connector["isys_catg_connector_list__id"],
                "CONNECTOR_TITLE"   => $l_assigned_connector["isys_catg_connector_list__title"],
                "CABLE_CONNECTION"  => $l_assigned_connector["isys_catg_connector_list__isys_cable_connection__id"],
                "CABLE_ID"          => $l_assigned_connector["isys_cable_connection__isys_obj__id"],
                "CABLE_TITLE"       => $l_dao_cable_connection->get_obj_name_by_id_as_string($l_assigned_connector["isys_cable_connection__isys_obj__id"]),
                "OBJECT_TITLE"      => $l_object_title,
                "OBJECT_TYPE"       => $this->get_objTypeID($l_assigned_connector["isys_catg_connector_list__isys_obj__id"]),
                "OBJECT_ID"         => $l_assigned_connector["isys_catg_connector_list__isys_obj__id"],
                "CONNECTOR_TYPE"    => $l_assigned_connector["isys_catg_connector_list__type"],
                "ASSIGNED_CATEGORY" => $l_assigned_connector["isys_catg_connector_list__assigned_category"],
                "SIBLING"           => false,
                "LINK"              => $this->prepare_link(
                    $l_assigned_connector["isys_catg_connector_list__id"],
                    $l_assigned_connector["isys_catg_connector_list__isys_obj__id"]
                )
            ];

            // Sibling check.
            {
                $l_sibling_dao = $this->get_sibling_by_connector($l_assigned_connector["isys_catg_connector_list__id"]);

                /* Check if we should go the left ways */
                if (($l_sibling_dao->num_rows() == 0) && !is_null($l_assigned_connector["isys_catg_connector_list__isys_catg_connector_list__id"]))
                {
                    $l_sibling_dao = $this->get_data($l_assigned_connector["isys_catg_connector_list__isys_catg_connector_list__id"]);
                } // if

                // If one ore more siblings found. Go further with processing.
                if ($l_sibling_dao->num_rows() > 0)
                {
                    // Get sibling info as array.
                    while ($l_sibling = $l_sibling_dao->get_row())
                    {
                        // Get object title.
                        $l_object_title = $l_dao_cable_connection->get_obj_name_by_id_as_string($l_sibling["isys_catg_connector_list__isys_obj__id"]);

                        // Add sibling to array structure.
                        $l_siblings = [
                            "CONNECTOR_ID"      => $l_sibling["isys_catg_connector_list__id"],
                            "CONNECTOR_TITLE"   => $l_sibling["isys_catg_connector_list__title"],
                            "CABLE_CONNECTION"  => $l_sibling["isys_catg_connector_list__isys_cable_connection__id"],
                            "CABLE_ID"          => $l_sibling["isys_cable_connection__isys_obj__id"],
                            "CABLE_TITLE"       => $l_dao_cable_connection->get_obj_name_by_id_as_string($l_sibling["isys_cable_connection__isys_obj__id"]),
                            "OBJECT_TITLE"      => $l_object_title,
                            "OBJECT_TYPE"       => $l_sibling["isys_obj__isys_obj_type__id"],
                            "OBJECT_ID"         => $l_sibling["isys_obj__id"],
                            "CONNECTOR_TYPE"    => $l_sibling["isys_catg_connector_list__type"],
                            "ASSIGNED_CATEGORY" => $l_sibling["isys_catg_connector_list__assigned_category"],
                            "SIBLING"           => true,
                            "LINK"              => $this->prepare_link(
                                $l_sibling["isys_catg_connector_list__id"],
                                $l_sibling["isys_catg_connector_list__isys_obj__id"]
                            )
                        ];

                        // Recursion.
                        $l_siblings["CONNECTION"] = $this->recurse_cable_run($l_sibling["isys_catg_connector_list__id"]);

                        $l_data["SIBLING"][] = $l_siblings;
                        unset($l_siblings);
                    } // while
                } // if
            }
        } // if

        return $l_data;
    } // function

    /**
     * Small helper method to find the last element of a cable run.
     *
     * @param   array $p_data
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    private function is_last_cable_run_object($p_data)
    {
        if ($p_data['SIBLING'] === true || is_array($p_data['CONNECTION']))
        {
            return false;
        } // if

        return true;
    } // function

    /**
     * Extending the parents "validate" method, because this DAO does not work with the generic unique-check.
     *
     * @param  array  $p_data
     * @param  mixed  $p_prepend_table_field
     *
     * @return array|bool
     */
    public function validate(array $p_data = [], $p_prepend_table_field = false)
    {
        return parent::validate($p_data, true);
    } // function
} // class