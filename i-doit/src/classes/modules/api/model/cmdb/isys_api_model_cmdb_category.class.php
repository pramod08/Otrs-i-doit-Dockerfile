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
 * API model
 *
 * @package    i-doit
 * @subpackage API
 * @author     Dennis Stücken <dstuecken@synetics.de>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_api_model_cmdb_category extends isys_api_model_cmdb implements isys_api_model_interface
{

    /**
     * Category DAO
     *
     * @var isys_cmdb_dao_category
     */
    protected $m_category_dao = null;

    /**
     * Category get-param
     *
     * @var string
     */
    protected $m_category_get = null;

    /**
     * Category id
     *
     * @var int
     */
    protected $m_category_id = null;

    /**
     * Category info from isysgui
     *
     * @var array
     */
    protected $m_category_info = null;

    /**
     * Category source table
     *
     * @var string
     */
    protected $m_category_source_table = null;

    /**
     * Category suffix
     *
     * @var string
     */
    protected $m_category_suffix = null;

    /**
     * Category type: global, specific or custom
     *
     * @var int
     */
    protected $m_category_type = null;

    /**
     * Field which includes the content
     *
     * @var string
     */
    protected $m_content_field = null;

    /**
     * Data formatting used in format methods
     *
     * @var array
     */
    protected $m_mapping = [];

    /**
     * Possible options and their parameters
     *
     * @var array
     */
    protected $m_options = [
        'read' => []
    ];

    /**
     * Validation
     *
     * @var array
     */
    protected $m_validation = [];

    /**
     * Read category data
     *
     * $p_param structure:
     *    array (
     *        'objID'        => 1,
     *        'catgID'    => 10
     *    )
     *
     * or
     *    array(
     *        'objID'        => 1,
     *        'catsID'    => 12
     *    )
     *
     * @param array $p_params
     *
     * @throws isys_exception_api
     * @return array
     */
    public function read($p_params)
    {
        // Init
        $l_return    = [];
        $i           = 0;
        $l_object_id = @$p_params[C__CMDB__GET__OBJECT];

        if (!is_scalar($l_object_id) || !is_numeric($l_object_id))
        {
            throw new isys_exception_api('Parameter ' . C__CMDB__GET__OBJECT . ' has to be numeric.', -32602);
        }

        // Validate object id
        if (!$l_object_id || $l_object_id < 1)
        {
            throw new isys_exception_api('Parameter ' . C__CMDB__GET__OBJECT . ' invalid: ID must be positive and higher than one.', -32602);
        } // if

        // Process data
        if (($l_cat = $this->prepare($p_params)))
        {
            if (method_exists($l_cat, 'get_data'))
            {
                // Status-Handling
                if (isset($p_params['status']))
                {
                    $l_status = is_numeric($p_params['status']) ? $p_params['status'] : (defined($p_params['status']) ? constant($p_params['status']) : null);
                }
                else
                {
                    $l_status = C__RECORD_STATUS__NORMAL;
                } // if

                // Condition-Handling and retrieve data
                if (isset($p_params['condition']))
                {
                    $l_catentries = $l_cat->get_data_as_array(
                        null,
                        addslashes($l_object_id),
                        urldecode($p_params['condition']),
                        null,
                        $l_status
                    );
                }
                else
                {
                    $l_catentries = $l_cat->get_data_as_array(
                        null,
                        $l_object_id,
                        null,
                        null,
                        $l_status
                    );
                } // if

                // Count category result
                if (count($l_catentries))
                {
                    if (!isset($p_params['raw']) || !$p_params['raw'])
                    {
                        $l_properties = $l_cat->get_properties();

                        // Get content field
                        $l_content_field = $this->get_content_field();

                        // Format category result
                        foreach ($l_catentries AS $l_row)
                        {
                            // Set object and category entry id
                            $l_return[$i]['id']    = $l_row[$this->get_category_source_table('__id')];
                            $l_return[$i]['objID'] = $l_row[$this->get_category_source_table('__isys_obj__id')];

                            // Property-Handling
                            foreach ($l_properties AS $l_key => $l_propdata)
                            {
                                if (is_string($l_key))
                                {
                                    if (isset($l_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0]) &&
                                        isset($l_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1])
                                    )
                                    {

                                        if (is_object($l_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0]))
                                        {
                                            $l_method             = $l_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1];
                                            $l_return[$i][$l_key] = $l_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0]->$l_method(
                                                $l_row[$l_propdata[C__PROPERTY__DATA][$l_content_field]],
                                                $l_row
                                            );
                                        }
                                        // Call helper object to retrieve more information
                                        else
                                        {
                                            if (class_exists($l_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0]))
                                            {

                                                $l_helper = new $l_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0](
                                                    $l_row,
                                                    $this->m_dao->get_database_component(),
                                                    $l_propdata[C__PROPERTY__DATA],
                                                    $l_propdata[C__PROPERTY__FORMAT],
                                                    $l_propdata[C__PROPERTY__UI]
                                                );

                                                // Set the Unit constant for the convert-helper
                                                if ($l_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1] == 'convert')
                                                {
                                                    if (method_exists($l_helper, 'set_unit_const'))
                                                    {
                                                        $l_row_unit = $l_properties[$l_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__UNIT]][C__PROPERTY__DATA][$l_content_field];
                                                        $l_helper->set_unit_const($l_row[$l_row_unit]);
                                                    } // if
                                                } // if

                                                // Call callback
                                                $l_return[$i][$l_key] = call_user_func(
                                                    [
                                                        $l_helper,
                                                        $l_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1]
                                                    ],
                                                    $l_row[$l_propdata[C__PROPERTY__DATA][$l_content_field]]
                                                );

                                                // Set data id
                                                $p_params['data']['id'] = $this->get_category_id();

                                                // Retrieve data from isys_export_data
                                                if ($l_return[$i][$l_key] instanceof isys_export_data)
                                                {
                                                    $l_export_data = $l_return[$i][$l_key];

                                                    /** @var isys_export_data $l_export_data */
                                                    $l_return[$i][$l_key] = $l_export_data->get_data();
                                                } // if
                                            }
                                        } // if

                                    }
                                    else
                                    {
                                        $l_return[$i][$l_key] = $l_row[$l_propdata[C__PROPERTY__DATA][$l_content_field]];
                                    } // if

                                    unset($l_helper_class, $l_helper);
                                } // if
                            } // foreach
                            $i++;
                        } // foreach

                    }
                    else
                    {
                        $l_return = $l_catentries;
                    } // if
                } // if

            }
            else
            {
                throw new isys_exception_api('get_data method does not exist for ' . get_class($l_cat), -32601);
            } // if
        } // if

        return $l_return;
    } // function

    /**
     * Get category id
     *
     * @return int
     */
    public function get_category_id()
    {
        return $this->m_category_id;
    } // function

    /**
     * Create category entry
     *
     * @param array $p_params Parameters (depends on data method)
     *
     * @internal param string $p_method Data method
     * @return isys_api_model_cmdb Returns itself.
     */
    public function create($p_params)
    {
        $l_id = $this->sync_wrapper($p_params, isys_import_handler_cmdb::C__CREATE);

        if ($l_id === true || $l_id > 0)
        {
            // Update 'isys_obj__updated'
            $this->m_dao->object_changed($p_params[C__CMDB__GET__OBJECT]);

            if (is_bool($l_id))
            {
                $l_return['id'] = null;

                if ($l_id === true)
                {
                    $l_return['message'] = 'Category entry was already existing and has been updated.';
                }
            }
            else
            {
                $l_return['id']      = $l_id;
                $l_return['message'] = 'Category entry successfully created.';
            }

            $l_return['success'] = true;
        }
        else
        {
            $l_return['id']      = null;
            $l_return['message'] = 'Error while creating category entry';
            $l_return['success'] = false;
        } // if

        return $l_return;
    } // function

    /**
     * Rank category entry:
     *
     * This method only moves the given category entry
     * to the next deletion status: normal -> archived, archived -> deleted...
     * It will not purge the entry!
     *
     * @param array $p_params Parameters (depends on data method)
     *
     * @throws isys_exception_api
     * @internal param string $p_method Data method
     * @return isys_api_model_cmdb Returns itself.
     */
    public function delete($p_params)
    {
        // Init
        $l_object_id = @$p_params[C__CMDB__GET__OBJECT];

        // Validate object id
        if (!$l_object_id || $l_object_id < 2)
        {
            throw new isys_exception_api('Object id invalid. ID must be positive and higher than one.', -32602);
        } // if

        // Get category params
        if (($l_cat = $this->prepare($p_params)))
        {
            $l_cat_suffix   = $this->get_category_suffix();
            $l_isysgui      = $this->get_category_info();
            $l_source_table = $this->get_category_source_table();

            if (@$p_params[C__CMDB__GET__CATLEVEL])
            {
                $l_category_id = $p_params[C__CMDB__GET__CATLEVEL];
            }
            else
            {
                if (@$p_params['id'])
                {
                    $l_category_id = $p_params['id'];
                }
                else
                {
                    throw new isys_exception_api('Category ID missing. You must specify the parameter \'id\' with the content of the entry ID of the category.');
                } // if
            } // if

            // Check class and instantiate it
            if ($l_isysgui["isysgui_cat{$l_cat_suffix}__list_multi_value"] == 1)
            {
                if ($this->get_category_source_table() != 'isys_catg_virtual_list')
                {
                    if (method_exists($l_cat, 'rank_record'))
                    {

                        $l_return = $l_cat->rank_record(
                            $l_category_id,
                            C__CMDB__RANK__DIRECTION_DELETE,
                            $l_source_table,
                            null,
                            (strpos($this->get_category_source_table(), '_2_') ? true: false) // @fixes ID-3195
                        );

                        if ($l_return)
                        {
                            return [
                                'success' => true,
                                'message' => 'Category entry \'' . $l_category_id . '\' successfully deleted'
                            ];
                        }
                        else
                        {
                            return [
                                'success' => false,
                                'message' => 'Category entry \'' . $l_category_id . '\' was not deleted. It may not exists.'
                            ];
                        } // if
                    }
                    else
                    {
                        throw new isys_exception_api('CMDB-Category-Error! Rank method not supported by the category.');
                    } // if

                }
                else
                {
                    throw new isys_exception_api('The category does not support deletion.');
                } // if
            }
            else
            {
                throw new isys_exception_api('Your category is not multi-valued. It is not possible to delete a single value category.');
            } // if
        } // if

        return false;
    } // function

    /**
     * Save category
     *
     * @param array $p_params Parameters (depends on data method)
     *
     * @internal param string $p_method Data method
     * @return isys_api_model_cmdb Returns itself.
     */
    public function update($p_params)
    {
        if ($this->sync_wrapper($p_params, isys_import_handler_cmdb::C__UPDATE))
        {
            // Update 'isys_obj__updated'
            $this->m_dao->object_changed($p_params[C__CMDB__GET__OBJECT]);

            return [
                'success' => true,
                'message' => 'Category entry successfully saved'
            ];
        }
        else
        {
            return [
                'success' => false,
                'message' => 'Error while saving category'
            ];
        } // if
    } // function

    /**
     * Get category suffix
     *
     * @return string
     */
    public function get_category_suffix()
    {
        return $this->m_category_suffix;
    } // function

    /**
     * Get category info
     *
     * @return array
     */
    public function get_category_info()
    {
        return $this->m_category_info;
    } // function

    /**
     * Quickpurge category entry:
     * This method will purge the entry and therefore remove it from the database.
     *
     * @param   array $p_params Parameters (depends on data method).
     *
     * @throws  isys_exception_api
     * @return  isys_api_model_cmdb  Returns itself.
     */
    public function quickpurge($p_params)
    {
        if (!isys_settings::get('cmdb.quickpurge', false))
        {
            throw new isys_exception_api('Quickpurge is not enabled');
        } // if

        // Init.
        $l_object_id = @$p_params[C__CMDB__GET__OBJECT];

        // Validate object id.
        if (!$l_object_id || $l_object_id < 2)
        {
            throw new isys_exception_api('Object id invalid. ID must be positive and higher than one.', -32602);
        } // if

        $l_isysgui = $this->get_category_info();

        // Get category params
        if (($l_cat = $this->prepare($p_params)))
        {
            $l_source_table = $this->get_category_source_table();

            if (@$p_params[C__CMDB__GET__CATLEVEL])
            {
                $l_category_id = $p_params[C__CMDB__GET__CATLEVEL];
            }
            else
            {
                if (@$p_params['id'])
                {
                    $l_category_id = $p_params['id'];
                }
                else
                {
                    throw new isys_exception_api('Category ID missing. You must specify the parameter \'id\' with the content of the entry ID of the category.');
                } // if
            } // if

            // Check class and instantiate it.
            if ($this->get_category_source_table() != 'isys_catg_virtual_list' ||
                ($l_cat->category_entries_purgable() && $l_isysgui["isysgui_cat" . $this->get_category_suffix() . "__list_multi_value"] == 0)
            )
            {
                if (method_exists($l_cat, 'rank_record'))
                {

                    if ($l_cat->rank_record($l_category_id, C__CMDB__RANK__DIRECTION_DELETE, $l_source_table, null, true))
                    {
                        return [
                            'success' => true,
                            'message' => 'Category entry \'' . $l_category_id . '\' successfully purged'
                        ];
                    }
                    else
                    {
                        return [
                            'success' => false,
                            'message' => 'Category entry \'' . $l_category_id . '\' was not purged. It may not exists.'
                        ];
                    } // if
                }
                else
                {
                    throw new isys_exception_api('CMDB-Category-Error! Rank method not supported by the category.');
                } // if
            }
            else
            {
                throw new isys_exception_api('The category does not support purge.');
            } // if
        } // if

        return false;
    } // function

    /**
     * Prepare api request
     *
     * @param array $p_params
     *
     * @throws isys_exception_api
     * @return bool|isys_cmdb_dao_category
     */
    protected function prepare($p_params)
    {
        // Get category params
        list($l_get_param, $l_cat_suffix) = $this->prepare_category_params($p_params);

        $this->set_category_suffix($l_cat_suffix);

        // Convert constant to ID
        if (is_string($p_params[$l_get_param]) && defined($p_params[$l_get_param]))
        {
            $p_params[$l_get_param] = constant($p_params[$l_get_param]);
        } // if

        $l_isysgui = [];

        // Get category info
        if (is_numeric($p_params[$l_get_param]))
        {
            $l_isysgui = $this->m_dao->get_isysgui('isysgui_cat' . $l_cat_suffix, (int) $p_params[$l_get_param])
                ->__to_array();

            $this->set_category_id($p_params[$l_get_param]);
        } // if

        // Category exists?
        if (count($l_isysgui))
        {
            $this->set_category_info($l_isysgui);

            // Set source table
            if ($this->is_global())
            {
                $this->set_category_source_table($l_isysgui["isysgui_cat{$l_cat_suffix}__source_table"] . '_list');
            }
            else
            {
                $this->set_category_source_table($l_isysgui["isysgui_cat{$l_cat_suffix}__source_table"]);
            } // if

            // Check class and instantiate it
            if (class_exists($l_isysgui["isysgui_cat{$l_cat_suffix}__class_name"]))
            {

                $_GET[$l_get_param] = $this->get_category_id();

                // Set category DAO
                if (($l_cat = new $l_isysgui["isysgui_cat{$l_cat_suffix}__class_name"]($this->m_dao->get_database_component())))
                {
                    if (method_exists($l_cat, 'set_catg_custom_id'))
                    {
                        /** @var $l_cat isys_cmdb_dao_category_g_custom_fields */
                        $l_cat->set_catg_custom_id($this->get_category_id());
                    } // if

                    return $l_cat;
                }
                else
                {
                    // Unable to instantiate DAO class
                    throw new isys_exception_api('Unable to instantiate category DAO "' . $l_isysgui["isysgui_cat{$l_cat_suffix}__class_name"] . '".', -32602);
                } // if
            }
            else
            {
                // DAO does not exist
                throw new isys_exception_api('Category DAO "' . $l_isysgui["isysgui_cat{$l_cat_suffix}__class_name"] . '" does not exist.', -32602);
            } // if
        }
        else
        {
            // Category does not exist
            throw new isys_exception_api(
                'Unable to find desired category with ID "' . $p_params[$l_get_param] . '". Please check the delivered category ID and try again', -32602
            );
        } // if
    } // function

    /**
     * Set the category type
     *
     * @param int $p_category_type
     *
     * @return $this
     */
    protected function set_category_type($p_category_type)
    {
        $this->m_category_type = $p_category_type;

        return $this;
    } // function

    /**
     * Set the content field
     *
     * @param string $p_content_field
     *
     * @return $this
     */
    protected function set_content_field($p_content_field)
    {
        $this->m_content_field = $p_content_field;

        return $this;
    } // function

    /**
     * Set category suffix
     *
     * @param string $p_category_suffix
     *
     * @return $this
     */
    protected function set_category_suffix($p_category_suffix)
    {
        $this->m_category_suffix = $p_category_suffix;

        return $this;
    } // function

    /**
     * Set category id
     *
     * @param int $p_category_id
     *
     * @return $this
     */
    protected function set_category_id($p_category_id)
    {
        $this->m_category_id = $p_category_id;

        return $this;
    } // function

    /**
     * Set category info from isysgui
     *
     * @param array $p_category_info
     *
     * @return $this
     */
    protected function set_category_info($p_category_info)
    {
        $this->m_category_info = $p_category_info;

        return $this;
    } // function

    /**
     * Is category type global?
     *
     * @return bool
     */
    protected function is_global()
    {
        return (C__CMDB__CATEGORY__TYPE_GLOBAL == $this->get_category_type());
    } // function

    /**
     * Set category source table
     *
     * @param string $p_category_source_table
     *
     * @return $this
     */
    protected function set_category_source_table($p_category_source_table)
    {
        $this->m_category_source_table = $p_category_source_table;

        return $this;
    } // function

    /**
     * Is category type custom?
     *
     * @return bool
     */
    protected function is_custom()
    {
        return (C__CMDB__CATEGORY__TYPE_CUSTOM == $this->get_category_type());
    } // function

    /**
     * Get category's source table
     *
     * @param string $p_add
     *
     * @return string
     */
    protected function get_category_source_table($p_add = '')
    {
        return $this->m_category_source_table . $p_add;
    } // function

    /**
     * Get content field
     *
     * @return string
     */
    protected function get_content_field()
    {
        return $this->m_content_field;
    } // function

    /**
     * Sync data
     *
     * @param array $p_params
     * @param int   $p_mode
     *
     * @return bool
     * @throws \Exception
     * @throws \isys_exception_api
     * @throws \isys_exception_api_validation
     */
    protected function sync_wrapper($p_params, $p_mode)
    {
        global $g_comp_database;

        /** @var isys_cmdb_dao_dialog_admin $l_dialog_admin */
        $l_dialog_admin = isys_cmdb_dao_dialog_admin::instance($g_comp_database);

        // Init
        $l_object_id = isset($p_params[C__CMDB__GET__OBJECT]) ? $p_params[C__CMDB__GET__OBJECT] : false;

        // Validate object id
        if (!$l_object_id || $l_object_id < 2)
        {
            throw new isys_exception_api('Object id invalid. ID must be positive and higher than two.', -32602);
        } // if

        if (!isset($p_params['data']) || !is_array($p_params['data']))
        {
            throw new isys_exception_api('Mandatory array parameter \'data\' missing or wrong format given.');
        } // if

        try
        {
            // Process data
            if (($l_cat = $this->prepare($p_params)))
            {
                // Prevent foreign key issues and check for object existence
                if (!$l_cat->obj_exists($l_object_id))
                {
                    throw new isys_exception_api(sprintf('Object with id "%s" does not exist.', $l_object_id));
                }

                $l_cat_suffix = $this->get_category_suffix();
                $l_isysgui    = $this->get_category_info();

                $l_cat->set_object_id($l_object_id);
                $l_cat->set_object_type_id($l_cat->get_objTypeID($l_object_id));

                if (method_exists($l_cat, 'sync'))
                {
                    // Get properties
                    $l_properties = $l_cat->get_properties();

                    // Create sync conform data array
                    $l_data = [];

                    // Prepare category ressource
                    if ($l_isysgui["isysgui_cat{$l_cat_suffix}__list_multi_value"] == 1)
                    {
                        // Mode: Update
                        if ($p_mode == isys_import_handler_cmdb::C__UPDATE)
                        {
                            // Set category id
                            if (isset($p_params['data']['category_id']) && $p_params['data']['category_id'] > 0)
                            {
                                $l_data['data_id'] = $p_params['data']['category_id'];
                            }
                            else
                            {
                                if (isset($p_params['data']['id']) && $p_params['data']['id'] > 0)
                                {
                                    $l_data['data_id'] = $p_params['data']['id'];
                                }
                                else
                                {
                                    $l_data['data_id'] = null;
                                } // if
                            } // if

                            // Check for category entry id first
                            if (isset($l_data['data_id']))
                            {
                                // Try to get specified entry
                                $l_catentries = $l_cat->get_data_as_array($l_data['data_id'], $l_object_id);

                                if (count($l_catentries) != 1)
                                {
                                    // Category entry does not exist or the object does not own it.
                                    throw new isys_exception_api(
                                        sprintf('Unable to find a category entry with id %d for object %d.', $l_data['data_id'], $l_object_id)
                                    );
                                } // if
                            }
                            else
                            {
                                throw new isys_exception_api('Please define the category entry you want to update by setting data.id or data.category_id.');
                            } // if
                        } // if

                        $l_cat->set_list_id($l_data['data_id']);
                        $l_catentries = $l_cat->get_data_as_array($l_data['data_id'], $l_object_id);
                    }
                    else
                    {

                        /*
                         * If we are updating a single value category we obviously only need the object id for identifying the right category entry.
                         * So let's check for it.
                         */
                        $l_catentries = $l_cat->get_data_as_array(null, $l_object_id);
                    } // if

                    // Update-Handling
                    if ($l_isysgui["isysgui_cat{$l_cat_suffix}__list_multi_value"] != 1)
                    {
                        if (count($l_catentries))
                        {
                            $l_data['data_id'] = $l_catentries[0][$this->get_category_source_table('__id')];

                            if (!$l_data['data_id'])
                            {
                                $p_mode = isys_import_handler_cmdb::C__CREATE;
                            }
                            else
                            {
                                $p_params['data_id'] = $p_params['data']['id'] = $l_data['data_id'];
                                $p_mode              = isys_import_handler_cmdb::C__UPDATE;
                            } // if
                        }
                        else
                        {
                            $p_mode = isys_import_handler_cmdb::C__CREATE;
                        } // if
                    } // if

                    // Build changes array
                    $l_changes = [];
                    foreach ($p_params['data'] AS $l_key => $l_value)
                    {
                        if (is_string($l_value) && defined($l_value))
                        {
                            $l_changes[isys_import_handler_cmdb::C__PROPERTIES][$l_key][C__DATA__VALUE] = constant($l_value);
                        }
                        else
                        {
                            $l_changes[isys_import_handler_cmdb::C__PROPERTIES][$l_key][C__DATA__VALUE] = $l_value;
                            $l_changes[isys_import_handler_cmdb::C__PROPERTIES][$l_key]['title_lang']   = $l_value;
                        } // if
                    } // foreach

                    // Dialog+ handling before merge
                    foreach ($p_params['data'] AS $l_key => $l_value)
                    {
                        // Is it a dialog_plus or a custom_dialog_plus field
                        if (is_string($l_value))
                        {
                            $l_identifier = null;
                            $l_table      = null;

                            if(($l_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1] == 'dialog_plus' ||
                                $l_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1] == 'custom_category_property_dialog_plus'))
                            {
                                // Get custom dialog identifier
                                if (isset($l_properties[$l_key][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['identifier']) && is_scalar(
                                        $l_properties[$l_key][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['identifier']
                                    )
                                )
                                {
                                    $l_identifier = $l_properties[$l_key][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['identifier'];
                                } // if

                                $p_params['data'][$l_key] = $l_dialog_admin->get_id(
                                    $l_properties[$l_key][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0],
                                    $p_params['data'][$l_key],
                                    $l_identifier,
                                    false
                                );
                            }
                            elseif($l_properties[$l_key][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1] == 'dialog_multiselect')
                            {
                                $l_dialog_value = $l_value;
                                if(strpos($l_value, ',') !== false)
                                {
                                    $l_dialog_value = explode(',', $l_value);
                                } // if

                                if(is_array($l_dialog_value))
                                {
                                    $l_new_value = [];
                                    foreach($l_dialog_value AS $l_val)
                                    {
                                        $l_new_value[] = $l_dialog_admin->get_id(
                                            $l_properties[$l_key][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strTable'],
                                            trim($l_val),
                                            $l_identifier,
                                            false
                                        );
                                    }
                                    $p_params['data'][$l_key] = $l_new_value;
                                }
                                else
                                {
                                    $p_params['data'][$l_key] = $l_dialog_admin->get_id(
                                        $l_properties[$l_key][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strTable'],
                                        $l_dialog_value,
                                        $l_identifier,
                                        false
                                    );
                                } // if
                            } // if
                        } // if
                    } // foreach

                    // Merge new and old data from the database to prevent overwrites through nulls
                    if (isset($l_data['data_id']) && is_numeric($l_data['data_id']) && count($l_catentries))
                    {
                        $p_params['data'] = array_merge(
                            self::to_sync_structure($l_catentries[0], $l_cat->get_properties()),
                            $p_params['data']
                        );
                    } // if

                    // Build Data-Structure for the sync-routine
                    foreach ($p_params['data'] AS $l_key => $l_value)
                    {
                        if (is_string($l_value) && defined($l_value))
                        {
                            $l_data['properties'][$l_key][C__DATA__VALUE] = constant($l_value);
                        }
                        else
                        {
                            $l_data['properties'][$l_key][C__DATA__VALUE] = $l_value;
                        } // if
                    } // foreach

                    // Prepare validation data
                    $l_validate_data = [];
                    foreach ($l_properties as $l_key => $l_value)
                    {
                        $l_validate_data[$l_key] = $l_data['properties'][$l_key][C__DATA__VALUE];
                    } // foreach property

                    // Try to validate incoming data
                    $l_validation = $l_cat->validate($l_validate_data);
                    unset($l_validate_data);

                    if (is_array($l_validation) && count($l_validation) > 0)
                    {

                        $l_validation_errors = '';
                        foreach ($l_validation AS $l_field => $l_problem)
                        {
                            $l_validation_errors .= $l_field . '(' . $l_properties[$l_field][C__PROPERTY__DATA][C__PROPERTY__DATA__TYPE] . '): ' . $l_problem . ', ';
                        } // foreach

                        throw new isys_exception_api_validation(
                            'There was a validation error: ' . rtrim(
                                $l_validation_errors,
                                ', '
                            ), $l_validation
                        );
                    }

                    /*
                     * Emit signal
                     */
                    isys_component_signalcollection::get_instance()
                        ->emit(
                            'mod.api.beforeCmdbCategorySync',
                            $l_data,
                            $l_object_id,
                            $p_mode,
                            $l_cat
                        );

                    // Call sync of corresponding category
                    $l_sync = $l_cat->sync($l_data, $l_object_id, $p_mode);

                    if ($l_sync)
                    {
                        // Get category title
                        $l_category_info  = $this->get_category_info();
                        $l_category_title = $l_category_info['isysgui_cat' . $this->get_category_suffix() . '__title'];

                        // Create array of changes
                        $l_changes = isys_factory::get_instance('isys_module_logbook')
                            ->prepare_changes($l_cat, $l_catentries[0], $l_changes);

                        if (count($l_changes))
                        {
                            // Create logbook entry
                            isys_event_manager::getInstance()
                                ->triggerCMDBEvent(
                                    'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                    null,
                                    $p_params['objID'],
                                    $this->m_dao->get_objTypeID($l_object_id),
                                    $l_category_title,
                                    serialize($l_changes)
                                );
                        } // if

                    } // if

                    // Emit category signal (afterCategoryEntrySave).
                    isys_component_signalcollection::get_instance()
                        ->emit(
                            "mod.cmdb.afterCategoryEntrySave",
                            $l_cat,
                            isset($l_data['data_id']) ? $l_data['data_id'] : null,
                            $l_sync,
                            $l_object_id,
                            $l_data,
                            isset($l_changes) ? $l_changes : []
                        );

                    return $l_sync;
                } // if
            }
            else
            {
                throw new isys_exception_api('Unable to prepare requested category data. Please make sure the given category ID or constant exists.');
            } // if
        }
        catch (isys_exception_api_validation $e)
        {
            throw $e;
        } // try
        catch (isys_exception_api $e)
        {
            throw $e;
        } // try

        return false;

    } // function

    /**
     * Transform stored data
     * for merging it with the delivered data
     * from the API
     *
     * @author Selcuk Kekec <skekec@i-doit.com>
     *
     * @param array $p_data          API-DATA
     * @param array $p_category_info Category Properties
     *
     * @return array                            Array-Structure for merging
     */
    protected function to_sync_structure($p_data, $p_category_info)
    {
        $l_result = [];

        if (is_array($p_category_info) && count($p_category_info) && is_array($p_data) && count($p_data))
        {
            foreach ($p_category_info AS $l_property_key => $l_property_data)
            {
                // ID-3044 We might need to format some data, to prevent errors like "1 MBit/s" => "1000000 MBit/s".
                if (isset($l_property_data[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__UNIT]) && !empty($l_property_data[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__UNIT]))
                {
                    $l_value = $p_data[$l_property_data[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]];
                    $l_unit = $p_data[$p_category_info[$l_property_data[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__UNIT]][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]];

                    if ($l_value > 0 && $l_unit > 0)
                    {
                        if (is_array($l_property_data[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK]) && count($l_property_data[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK]) === 3 && $l_property_data[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1] == 'convert')
                        {
                            if (is_string($l_property_data[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][2][0]) && method_exists('isys_convert', $l_property_data[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][2][0]))
                            {
                                $p_data[$l_property_data[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]] = isys_convert::$l_property_data[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][2][0]($l_value, $l_unit, C__CONVERT_DIRECTION__BACKWARD);
                            } // if
                        } // if
                    } // if
                } // if

                // ID-3103 n2m fields will mostly use the category ID as their data field... But that is wrong.
                if ($l_property_data[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__N2M)
                {
                    continue;
                } // if

                if (isset($p_data[$l_property_data[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]]))
                {
                    // Handling for connectionFields
                    if (isset($l_property_data[C__PROPERTY__FORMAT]['callback'][1]) && $l_property_data[C__PROPERTY__FORMAT]['callback'][1] == 'connection')
                    {
                        $l_result[$l_property_key] = $p_data['isys_connection__isys_obj__id'];
                    }
                    else
                    {
                        $l_result[$l_property_key] = $p_data[$l_property_data[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]];
                    } // if
                } // if
            } // foreach
        } // if

        return $l_result;
    } // function

    /**
     * Set the get-param for
     * retrieving the categoryID
     *
     * @param   string $p_category_get
     *
     * @return $this
     */
    protected function set_category_get($p_category_get)
    {
        $this->m_category_get = $p_category_get;

        return $this;
    } // function

    /**
     * Get category get paramater: catgID, catsID, customID
     *
     * @return string
     */
    protected function get_category_get()
    {
        return $this->m_category_get;
    } // function

    /**
     * Is category type specific?
     *
     * @return bool
     */
    protected function is_specific()
    {
        return (C__CMDB__CATEGORY__TYPE_SPECIFIC == $this->get_category_type());
    } // function

    /**
     * Get the current category type
     *
     * @return int
     */
    protected function get_category_type()
    {
        return $this->m_category_type;
    } // function

    /**
     * Prepare category parameter for globa, specific and custom
     *
     * @param $p_params
     *
     * @return array
     * @throws isys_exception_api
     */
    private function prepare_category_params(&$p_params)
    {
        // Process Parameters
        if (isset($p_params[C__CMDB__GET__CATS]) && $p_params[C__CMDB__GET__CATS])
        {
            $l_get_param  = C__CMDB__GET__CATS;
            $l_cat_suffix = 's';

            $this->set_category_type(C__CMDB__CATEGORY__TYPE_SPECIFIC);
            $this->set_content_field(C__PROPERTY__DATA__FIELD);
        }
        else
        {
            if (isset($p_params[C__CMDB__GET__CATG]) && $p_params[C__CMDB__GET__CATG])
            {
                $l_get_param  = C__CMDB__GET__CATG;
                $l_cat_suffix = 'g';

                $this->set_category_type(C__CMDB__CATEGORY__TYPE_GLOBAL);
                $this->set_content_field(C__PROPERTY__DATA__FIELD);
            }
            else
            {
                if (isset($p_params[C__CMDB__GET__CATG_CUSTOM]) && $p_params[C__CMDB__GET__CATG_CUSTOM])
                {
                    $l_get_param  = C__CMDB__GET__CATG_CUSTOM;
                    $l_cat_suffix = 'g_custom';

                    $this->set_category_type(C__CMDB__CATEGORY__TYPE_CUSTOM);
                    $this->set_content_field(C__PROPERTY__DATA__FIELD_ALIAS);
                }
                else
                {
                    if (isset($p_params['category']) && is_string($p_params['category']))
                    {
                        if (defined($p_params['category']))
                        {
                            // Default is a global category
                            $l_get_param  = C__CMDB__GET__CATG;
                            $l_cat_suffix = 'g';

                            if (strpos($p_params['category'], 'C__CATG') === 0 && strpos($p_params['category'], 'C__CATG__CUSTOM_FIELDS_') !== 0)
                            {
                                $this->set_category_type(C__CMDB__CATEGORY__TYPE_GLOBAL);
                                $this->set_content_field(C__PROPERTY__DATA__FIELD);
                            }
                            else
                            {
                                if (strpos($p_params['category'], 'C__CATS') === 0)
                                {
                                    $l_get_param  = C__CMDB__GET__CATS;
                                    $l_cat_suffix = 's';

                                    $this->set_category_type(C__CMDB__CATEGORY__TYPE_SPECIFIC);
                                    $this->set_content_field(C__PROPERTY__DATA__FIELD);
                                }
                                else
                                {
                                    if (strpos($p_params['category'], 'C__CMDB__SUBCAT') === 0)
                                    {
                                        /**
                                         * @todo see ID-948 and ID-934
                                         */
                                        $l_dao = new isys_cmdb_dao($this->m_dao->get_database_component());

                                        // Try to retrieve category by constant of any type
                                        $l_cat_data = $l_dao->get_cat_by_const($p_params['category']);

                                        // Is there a result?
                                        if (is_array($l_cat_data) && count($l_cat_data))
                                        {
                                            if (isset($l_cat_data['type']))
                                            {
                                                // Check for type now
                                                if ($l_cat_data['type'] == C__CMDB__CATEGORY__TYPE_SPECIFIC)
                                                {
                                                    $l_get_param  = C__CMDB__GET__CATS;
                                                    $l_cat_suffix = 's';

                                                    $this->set_category_type(C__CMDB__CATEGORY__TYPE_SPECIFIC);
                                                    $this->set_content_field(C__PROPERTY__DATA__FIELD);
                                                }
                                                else
                                                {
                                                    if ($l_cat_data['type'] == C__CMDB__CATEGORY__TYPE_GLOBAL)
                                                    {
                                                        $l_get_param  = C__CMDB__GET__CATG;
                                                        $l_cat_suffix = 'g';

                                                        $this->set_category_type(C__CMDB__CATEGORY__TYPE_GLOBAL);
                                                        $this->set_content_field(C__PROPERTY__DATA__FIELD);
                                                    }
                                                } // if
                                            } // if
                                        } // if
                                    }
                                    else
                                    {
                                        $l_get_param  = C__CMDB__GET__CATG_CUSTOM;
                                        $l_cat_suffix = 'g_custom';

                                        $this->set_category_type(C__CMDB__CATEGORY__TYPE_CUSTOM);
                                        $this->set_content_field(C__PROPERTY__DATA__FIELD_ALIAS);
                                    }
                                } // if
                            } // if

                            $p_params[$l_get_param] = $p_params['category'];
                        }
                        else
                        {
                            throw new isys_exception_api(
                                sprintf('Category "%s" not found. Delete your i-doit cache if you are sure it exists.', $p_params['category']), -32602
                            );
                        }
                    }
                    else
                    {
                        throw new isys_exception_api(
                            'Category type missing. You must specify the parameter int \'catsID\', int \'catgID\', int \'customID\' or string \'category\' ' .
                            'in order to identify the corresponding category you would like to use.', -32602
                        );
                    } // if
                } // if
            } // if
        } // if

        return [
            $l_get_param,
            $l_cat_suffix
        ];
    } // function

    /**
     * Constructor
     *
     * @param \isys_cmdb_dao $p_dao
     */
    public function __construct(isys_cmdb_dao &$p_dao)
    {
        $this->m_dao = $p_dao;
        parent::__construct();
    } // function

} // class