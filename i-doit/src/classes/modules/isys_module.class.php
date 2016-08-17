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
 * Abstract base class for i-doit modules.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      i-doit Team <dev@i-doit.de>
 * @version     $Version$
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
abstract class isys_module implements isys_module_interface, isys_module_installable
{
    const DISPLAY_IN_MAIN_MENU   = false;
    const DISPLAY_IN_SYSTEM_MENU = false;
    /**
     * Is this module fully licenced.
     *
     * @var  boolean
     */
    private static $m_licenced = false;
    /**
     * Holds an instance of the module dao.
     *
     * @var  isys_module_dao
     */
    protected $m_dao;
    /**
     * Will hold "isys_module" table data.
     *
     * @var  array
     */
    protected $m_data; // function

    /*
     * Here we are starting with the interface declaration for
     * i-doit modules. All subclasses have to overwrite the
     * following abstract functions.
     *
     * 	init - Initializes the module
     * 	build_tree - Builds the modules menu-tree
     *
     * @todo We need a more detailed description of the module framework here.
     */

    // Define, if this module shall be displayed in the named menus.
    /**
     * A custom data register.
     *  Fill with ->set()
     *  Retrieve with ->get()
     *
     * @var isys_array
     */
    protected $m_register = null;

    /**
     * Is this module licenced, or not?
     *
     * @return  boolean
     */
    public final static function is_licenced()
    {
        return isset(static::$m_licenced) ? static::$m_licenced : true;
    }

    /**
     * Set licence status.
     *
     * @param  boolean $p_status
     */
    public final static function set_licenced($p_status = false)
    {
        if (isset(static::$m_licenced))
        {
            static::$m_licenced = $p_status;
        } // if
    } // function

    /**
     * Static method for retrieving the path, to the modules templates.
     *
     * @static
     * @global  array $g_dirs
     * @return  string
     */
    public static function get_tpl_www_dir()
    {
        return isys_application::instance()->www_path . 'src/classes/modules/' . (str_replace('isys_module_', '', get_called_class())) . '/templates/';
    } // function

    /**
     * Signal Slot initialization.
     */
    public function initslots()
    {
        return false;
    } // function

    /**
     * Default start method.
     *
     * @return  isys_module_events
     */
    public function start()
    {
        if (func_num_args() > 0)
        {
            // This is a legacy load, so we're emulating the new Controller handling.
            $l_request = func_get_arg(0);

            if (!$l_request->module)
            {
                $l_request->module = str_replace('isys_module_', '', get_class($this));
                unset($l_request->action);

                isys_application::instance()
                    ->request($l_request);

                return $this;
            } // if
        }
        else
        {
            throw new RuntimeException('Module not compatible with i-doit ' . isys_application::instance()->info->get('version'));
        } // if

        return $this;
    } // function

    /**
     * Dummy method for building the menu-tree.
     *
     * @param   isys_component_tree $p_tree
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @since   Version 0.9.9-7
     */
    public function build_tree(isys_component_tree $p_tree, $p_system_module = true, $p_parent = null)
    {
        ; // We don't declare this method as abstract anymore, to prevent errors while installing or updating.
    } // function

    /**
     * Callback function for construction of my-doit area.
     *
     * @return  boolean
     */
    public function mydoit_get(&$p_text, &$p_link)
    {
        return false;
    } // function

    /**
     * Custom handler for handling trial versions of this module.
     *
     * @param   isys_module_register $p_module_register
     * @param   integer              $p_end_date end date as timestamp
     *
     * @return  isys_module
     */
    public function start_trial(isys_module_register $p_module_register, $p_end_date)
    {
        isys_component_template::instance()
            ->assign(
                'trialInfo',
                [
                    'title'   => _L($p_module_register->get_data('isys_module__title')),
                    'message' => _L(
                        'LC__LICENCE__TRIAL_INFO',
                        isys_locale::get_instance()
                            ->fmt_date($p_end_date)
                    )
                ]
            );

        return $this;
    } // function

    /**
     * Build breadcrumb navigation. Override for custom handling.
     *
     * @param   &$p_gets
     *
     * @return  array|null
     */
    public function breadcrumb_get(&$p_gets)
    {
        $l_return = [];

        /**
         * @var $l_breadcrumb \idoit\Model\Breadcrumb[]
         */
        $l_breadcrumb = $this->get('breadcrumb');

        if (is_array($l_breadcrumb))
        {
            foreach ($l_breadcrumb as $l_b)
            {
                if (is_a($l_b, 'idoit\Model\Breadcrumb'))
                {
                    $l_return[] = [$l_b->title => $l_b->parameters];
                } // if
            } // foreach
        } // if

        return $l_return;
    } // function

    /**
     * @param   array $p_data
     *
     * @return  $this
     */
    public function set_data($p_data)
    {
        $this->m_data = $p_data;

        return $this;
    } // function

    /**
     * Set value to $m_data
     *
     * @param string $p_key
     * @param string $p_value
     *
     * @return $this
     */
    public function set($p_key, $p_value)
    {
        if (!is_a($this->m_register, 'isys_array'))
        {
            $this->m_register = new isys_array();
        } // if

        $this->m_register[$p_key] = $p_value;

        return $this;
    } // function

    /**
     * Get $p_key from $m_register.
     *
     * @param   string $p_key
     *
     * @return  mixed
     */
    public function get($p_key)
    {
        return $this->m_register[$p_key] ?: null;
    } // function

    /**
     * Get module DAO.
     */
    public function get_dao()
    {
        return $this->m_dao;
    } // function

    /**
     * Return template directory based on the module's path.
     */
    public function get_template_dir()
    {
        return __DIR__ . '/' . str_replace('isys_module_', '', get_class($this)) . '/templates/';
    } // function

    /**
     * Checks if a module is installed.
     *
     * @param   string  $p_identifier
     * @param   boolean $p_and_active
     *
     * @return  mixed
     */
    public function is_installed($p_identifier = null, $p_and_active = false)
    {
        global $g_comp_database;

        if (is_object($g_comp_database))
        {
            $l_dao = new isys_component_dao($g_comp_database);

            if (!$p_identifier)
            {
                $l_sql = "SELECT isys_module__id FROM isys_module WHERE isys_module__class = " . $l_dao->convert_sql_text(get_class($this));
            }
            else
            {
                $l_sql = "SELECT isys_module__id FROM isys_module WHERE isys_module__identifier = " . $l_dao->convert_sql_text($p_identifier);
            } // if

            if ($p_and_active)
            {
                $l_sql .= ' AND isys_module__status = ' . $l_dao->convert_sql_int(C__RECORD_STATUS__NORMAL);
            } // if

            $l_id = $l_dao->retrieve($l_sql . ';')
                ->get_row_value('isys_module__id');

            return $l_id ? $l_id : false;
        } // if

        return false;
    } // function

    /**
     * Prepares user data assignments to UI.
     *
     * @param   array $p_properties Properties
     * @param   array $p_data       (optional) Data. Defaults to null.
     * @param   array $p_result     (optional) Validation result. Defaults to null.
     *
     * @return  array Associative array
     * @author  Benjamin Heisig <bheisig@synetics.de>
     */
    protected function prepare_user_data_assignment($p_properties, $p_data = null, $p_result = null)
    {
        $l_content = [];
        $l_request = isys_request::factory();

        // Iterate through each property:
        foreach ($p_properties as $l_property_id => $l_property_info)
        {
            $l_value = null;
            $l_ui    = [];

            if (!array_key_exists(C__PROPERTY__UI, $l_property_info))
            {
                // There is no information about the UI. Skipping.
                continue;
            }

            if (is_array($p_data) && array_key_exists($l_property_id, $p_data))
            {
                if (is_array($p_result) && array_key_exists($l_property_id, $p_result) && $p_result[$l_property_id] !== isys_module_dao::C__VALIDATION_RESULT__NOTHING)
                {
                    // Validation failed.

                    switch ($p_result[$l_property_id])
                    {
                        case isys_module_dao::C__VALIDATION_RESULT__MISSING:
                            $l_ui['p_strInfoIconError'] = _L('LC__UNIVERSAL__MANDATORY_FIELD_IS_EMPTY');
                            break;
                        case isys_module_dao::C__VALIDATION_RESULT__INVALID:
                            $l_ui['p_strInfoIconError'] = _L('LC__UNIVERSAL__FIELD_VALUE_IS_INVALID');
                            break;
                    }
                } //if

                $l_value = $p_data[$l_property_id];
            } //if

            // Use default value, if nothing is given:
            if ($l_value === null && array_key_exists('default', $l_property_info[C__PROPERTY__DATA]))
            {
                $l_value = $l_property_info[C__PROPERTY__DATA]['default'];
            } // if

            // Assign value:
            switch ($l_property_info[C__PROPERTY__UI][C__PROPERTY__UI__TYPE])
            {
                case C__PROPERTY__UI__TYPE__TEXT:
                case C__PROPERTY__UI__TYPE__TEXTAREA:
                    $l_ui['p_strValue'] = $l_value;
                    break;
                case C__PROPERTY__UI__TYPE__POPUP:
                    if ($l_property_info[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType'] === 'calendar')
                    {
                        $l_ui['p_strValue'] = $l_value;
                    }
                    else
                    {
                        $l_ui['p_strSelectedID'] = isys_format_json::encode($l_value);
                    } //if
                    break;
                case C__PROPERTY__UI__TYPE__DIALOG:
                    $l_ui['p_strSelectedID'] = $l_value;
                    break;
                case C__PROPERTY__UI__TYPE__DIALOG_LIST:
                    // @todo Assignment is currently done manually...
                    break;
                case C__PROPERTY__UI__TYPE__CHECKBOX:
                    if ($l_value)
                    {
                        $l_ui['p_bChecked'] = '1';
                    } // if
                    break;
                case C__PROPERTY__UI__TYPE__PROPERTY_SELECTOR:
                    $l_ui['preselection'] = $l_value;
                    break;
            } // switch

            // Assign mandatory attribute to lable with the same name attribute as the property form tag:
            if ($l_property_info[C__PROPERTY__CHECK][C__PROPERTY__CHECK__MANDATORY])
            {
                $l_ui[C__PROPERTY__CHECK__MANDATORY] = true;
            } // if

            // Assign description attribute to lable with the same name attribute as the property form tag:
            if (isset($l_property_info[C__PROPERTY__INFO][C__PROPERTY__INFO__DESCRIPTION]))
            {
                $l_ui['description'] = $l_property_info[C__PROPERTY__INFO][C__PROPERTY__INFO__DESCRIPTION];
            } // if

            // Assign default value attribute:
            if (isset($l_property_info[C__PROPERTY__UI]['default']))
            {
                // First, try to use the default value specified for the user interface:
                $l_default = $l_property_info[C__PROPERTY__UI]['default'];

                if ($l_default === null)
                {
                    $l_default = _L('LC__UNIVERSAL__EMPTY');
                } // if

                $l_ui['default'] = $l_default;
            }
            else if (isset($l_property_info[C__PROPERTY__DATA]['default']))
            {
                // Alternatively, try to use the default value from the data model:
                $l_default = $l_property_info[C__PROPERTY__DATA]['default'];

                if ($l_default === null)
                {
                    $l_default = _L('LC__UNIVERSAL__EMPTY');
                } // if

                $l_ui['default'] = $l_default;
            } // if

            // Assign all parameters for the smarty plugin:
            if (array_key_exists(C__PROPERTY__UI__PARAMS, $l_property_info[C__PROPERTY__UI]))
            {
                $l_ui = array_merge($l_ui, $l_property_info[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]);
            } // if

            if (isset($l_property_info[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']))
            {
                $l_arData = $l_property_info[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData'];

                switch (gettype($l_arData))
                {
                    case 'object':
                        if (get_class($l_arData) == 'isys_callback')
                        {
                            $l_ui['p_arData'] = $l_arData->execute($l_request);
                        } // if
                        break;
                    case 'array':
                        $l_ui['p_arData'] = serialize($l_arData);
                        break;
                    default:
                        $l_ui['p_arData'] = $l_arData;
                        break;
                } // switch
            } // if

            // Assign content:
            $l_ui_id             = $l_property_info[C__PROPERTY__UI][C__PROPERTY__UI__ID];
            $l_content[$l_ui_id] = $l_ui;
        } //foreach

        return $l_content;
    } // function

    /**
     * Parses user data from HTTP GET and POST.
     *
     * @param   array $p_properties Fetch these properties.
     *
     * @return  array  Associative array of parsed property data
     * @author  Benjamin Heisig <bheisig@synetics.de>
     */
    protected function parse_user_data($p_properties)
    {
        assert('is_array($p_properties)');

        $l_user_data = $this->m_userrequest->get_posts();
        $l_data      = [];

        foreach ($p_properties as $l_property_id => $l_property_info)
        {
            $l_id        = $l_property_info[C__PROPERTY__UI][C__PROPERTY__UI__ID];
            $l_hidden_id = $l_id . '__HIDDEN';
            $l_value     = null;
            $l_found     = false;

            if (isset($l_property_info[C__PROPERTY__UI]['post']) && array_key_exists($l_property_info[C__PROPERTY__UI]['post'], $l_user_data))
            {
                // First, use given property info to fetch data:
                $l_value = $l_user_data[$l_property_info[C__PROPERTY__UI]['post']];
                $l_found = true;
            }
            else if (array_key_exists($l_hidden_id, $l_user_data))
            {
                // Try to fetch value from hidden field:
                $l_value = $l_user_data[$l_hidden_id];
                $l_found = true;
            }
            else if ($l_value === null && array_key_exists($l_id, $l_user_data))
            {
                // Try to fetch value from visible field:
                $l_value = $l_user_data[$l_id];
                $l_found = true;
            } //if

            // Special handling for UI type checkbox: If field is not found in
            // POST filter it's value as false (bool).
            if ($l_property_info[C__PROPERTY__UI][C__PROPERTY__UI__TYPE] === C__PROPERTY__UI__TYPE__CHECKBOX && $l_found === false)
            {
                $l_value = false;
                $l_found = true;
            } //if

            // Value found? Keep it:
            if ($l_found === true)
            {
                $l_transformed = false;

                switch ($l_property_info[C__PROPERTY__UI][C__PROPERTY__UI__TYPE])
                {
                    case C__PROPERTY__UI__TYPE__CHECKBOX:
                        $l_value       = intval(filter_var($l_value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE));
                        $l_transformed = true;
                        break;
                    case C__PROPERTY__UI__TYPE__POPUP:
                    case C__PROPERTY__UI__TYPE__DIALOG_LIST:
                        if ($l_property_info[C__PROPERTY__DATA][C__PROPERTY__DATA__TYPE] == C__PROPERTY__UI__TYPE__DATETIME)
                        {
                            break;
                        } //if

                        $l_values = [];

                        // Treat empty string as NULL:
                        if (empty($l_value))
                        {
                            $l_value = null;
                            break;
                        } //if

                        if (isys_format_json::is_json_array($l_value))
                        {
                            $l_values = isys_format_json::decode($l_value, true);

                            // Avoid 'array(0 => 0)':
                            if (is_array($l_values) && count($l_values) === 1)
                            {
                                if (key($l_values) === 0 && current($l_values) === 0)
                                {
                                    $l_values = [];
                                } //if
                            } //if
                        }
                        else if (is_string($l_value))
                        {
                            $l_values = explode(',', $l_value);
                        }
                        else if (is_array($l_value))
                        {
                            $l_values = $l_value;
                        } //if
                        $l_value = [];
                        foreach ($l_values as $l_numeric)
                        {
                            if (is_numeric($l_numeric))
                            {
                                $l_value[] = intval($l_numeric);
                            } //if
                        } //foreach

                        if (count($l_value) == 0)
                        {
                            $l_value = null;
                        } //if

                        $l_transformed = true;
                        break;
                } //switch

                // Transform into right type:
                if ($l_transformed == false && array_key_exists(C__PROPERTY__DATA__TYPE, $l_property_info[C__PROPERTY__DATA]))
                {
                    switch ($l_property_info[C__PROPERTY__DATA][C__PROPERTY__DATA__TYPE])
                    {
                        case 'varchar': // @todo  Check if "varchar" is ever used as a data type
                        case C__TYPE__TEXT:
                            if (empty($l_value))
                            {
                                // Treat empty string as NULL:
                                $l_value = null;
                            } //if
                            break;
                        case C__TYPE__INT:
                            if (!is_numeric($l_value) && empty($l_value))
                            {
                                // Treat empty string as NULL:
                                $l_value = null;
                            }
                            else if (substr($l_property_info[C__PROPERTY__UI][C__PROPERTY__UI__TYPE], -6) === 'matrix')
                            {
                                // This is an array of integers:
                                $l_arr = [];
                                if (is_array($l_value))
                                {
                                    foreach ($l_value as $l_num)
                                    {
                                        $l_arr[] = intval($l_num);
                                    } //foreach
                                }
                                $l_value = $l_arr;
                            }
                            else
                            {
                                $l_value = intval($l_value);
                            } //if
                            break;
                        case C__TYPE__FLOAT:
                            // Treat empty string as NULL:
                            if (!is_numeric($l_value) && empty($l_value))
                            {
                                $l_value = null;
                            }
                            else
                            {
                                $l_value = floatval($l_value);
                            } //if
                            break;
                        case 'datetime':
                        case C__TYPE__DATE_TIME:
                            $l_timestamp = strtotime($l_value);

                            // Treat '1970-01-01 01:00:00' as NULL:
                            if ($l_timestamp === false || $l_timestamp === 0)
                            {
                                $l_value = null;
                            }
                            else
                            {
                                $l_value = date('Y-m-d H:i:s', $l_timestamp);
                            }
                            break;
                    } //switch
                } //if
                $l_data[$l_property_id] = $l_value;
            } //if

            // Use default value, if nothing is given, but it's required:
            if ($l_value === null && $l_property_info[C__PROPERTY__CHECK][C__PROPERTY__CHECK__MANDATORY] === true && array_key_exists(
                    'default',
                    $l_property_info[C__PROPERTY__DATA]
                )
            )
            {
                $l_data[$l_property_id] = $l_property_info[C__PROPERTY__DATA]['default'];
            } // if
        } //foreach

        return $l_data;
    } //function

    /**
     * Validates properties' data.
     *
     * @param   array   $p_properties
     * @param   array   $p_data
     * @param   boolean $p_ignore (optional) Ignore missing properties which could be mandatory. Defaults to false.
     *
     * @return  array Associative array of integers
     * @author  Benjamin Heisig <bheisig@synetics.de>
     */
    protected function validate_property_data($p_properties, $p_data, $p_ignore = false)
    {
        $l_result = [];

        foreach ($p_properties as $l_property_id => $l_property_info)
        {
            $l_result[$l_property_id] = isys_module_dao::C__VALIDATION_RESULT__NOTHING;

            // Field is missing, but it will be ignored:
            if (($p_ignore === true && !array_key_exists($l_property_id, $p_data)) ||
                $l_property_info[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_bReadonly'] === 'true' ||
                $l_property_info[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_bReadonly'] > 0)
            {
                $l_result[$l_property_id] = isys_module_dao::C__VALIDATION_RESULT__IGNORED;
                continue;
            } // if

            // Mandatory field is missing:
            if ($p_ignore === false && (!array_key_exists(
                        $l_property_id,
                        $p_data
                    ) || !isset($p_data[$l_property_id])) && $l_property_info[C__PROPERTY__CHECK][C__PROPERTY__CHECK__MANDATORY] === true
            )
            {
                $l_result[$l_property_id] = isys_module_dao::C__VALIDATION_RESULT__MISSING;
                continue;
            } // if

            if (isset($p_data[$l_property_id]) && array_key_exists(C__PROPERTY__CHECK, $l_property_info) && array_key_exists(
                    C__PROPERTY__CHECK__VALIDATION,
                    $l_property_info[C__PROPERTY__CHECK]
                ) && filter_var(
                    $p_data[$l_property_id],
                    $l_property_info[C__PROPERTY__CHECK][C__PROPERTY__CHECK__VALIDATION][0],
                    $l_property_info[C__PROPERTY__CHECK][C__PROPERTY__CHECK__VALIDATION][1]
                ) === false
            )
            {
                $l_result[$l_property_id] = isys_module_dao::C__VALIDATION_RESULT__INVALID;
            } // if
        } // foreach

        return $l_result;
    } //function

    /**
     * We need this constructor until the 1.0, so we don't break the core.
     */
    public function __construct()
    {
        $this->m_register = new isys_array();
    } // function
} // class