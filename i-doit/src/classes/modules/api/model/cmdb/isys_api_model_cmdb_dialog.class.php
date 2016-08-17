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
 * @author     Selcuk Kekec <skekec@i-doit.de>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_api_model_cmdb_dialog extends isys_api_model_cmdb implements isys_api_model_interface
{

    /**
     * Mode-Constants
     */
    const CL__DIALOG__UPDATE = 'update';
    const CL__DIALOG__CREATE = 'create';
    const CL__DIALOG__DELETE = 'delete';
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
        'read' => [
            "property",
            "catgID",
            "catsID"
        ]
    ];
    /**
     * Validation
     *
     * @var array
     */
    protected $m_validation = [
        'read'   => [
            'property',
        ],
        'create' => [
            'property',
            'value',
        ],
        'update' => [
            'property',
            'value',
            'entry_id',
        ],
        'delete' => [
            'property',
            'entry_id'
        ],
    ];

    /**
     * Retrieve the category dao by given Parameter
     *
     * @author Selcuk Kekec <skekec@i-doit.com>
     * @global isys_component_database $g_comp_database
     *
     * @param array                    $p_params
     * @param isys_cmdb_dao            $p_dao
     *
     * @return array|boolean
     * @throws isys_exception_api
     */
    private static function get_dao_class($p_params, $p_dao = null)
    {
        global $g_comp_database;
        $l_dao = (!empty($p_dao) ? $p_dao : new isys_cmdb_dao($g_comp_database));

        /* Process Parameters */
        if (@$p_params[C__CMDB__GET__CATS])
        {
            $l_get_param  = C__CMDB__GET__CATS;
            $l_cat_suffix = 's';
        }
        else if (@$p_params[C__CMDB__GET__CATG])
        {
            $l_get_param  = C__CMDB__GET__CATG;
            $l_cat_suffix = 'g';
        }
        else if (isset($p_params['category']) && is_string($p_params['category']))
        {
            if (strstr($p_params['category'], 'C__CATG'))
            {
                $l_get_param  = C__CMDB__GET__CATG;
                $l_cat_suffix = 'g';
            }
            else
            {
                $l_get_param  = C__CMDB__GET__CATS;
                $l_cat_suffix = 's';
            }

            $l_get_param = 'category';
        }
        else
        {
            throw new isys_exception_api(
                'Category ID missing. You must specify the parameter \'catsID\' or \'catgID\' ' . 'in order to identify the corresponding category you would like to read data from.',
                -32602
            );
        }

        /* Get category info */
        if (is_numeric(addslashes($p_params[$l_get_param])))
        {
            $l_isysgui = $l_dao->get_isysgui('isysgui_cat' . $l_cat_suffix, (int) $p_params[$l_get_param])
                ->__to_array();
        }
        else
        {
            $l_isysgui = $l_dao->get_isysgui('isysgui_cat' . $l_cat_suffix, null, null, addslashes($p_params[$l_get_param]))
                ->__to_array();
        }

        /* Check class and instantiate it */
        if (class_exists($l_isysgui["isysgui_cat{$l_cat_suffix}__class_name"]))
        {
            /* Process data */
            if (($l_cat = new $l_isysgui["isysgui_cat{$l_cat_suffix}__class_name"]($l_dao->get_database_component())))
            {
                return $l_cat;
            }
        }

        return false;
    }

    /**
     * Read category data
     *
     * $p_param structure:
     *     array (
     *         'property'  => 'manufacturer' | array('title', 'manufacturer),
     *         'category'  => 'C__CATG__CPU'
     *     )
     *
     * @param array $p_params
     *
     * @return array
     */
    public function read($p_params)
    {
        if (is_array($p_params["property"]))
        {
            $l_property = $p_params["property"];
        }
        else
        {
            $l_property = [
                $p_params['property']
            ];
        }

        /**
         * Get DAO class
         *
         * @var isys_cmdb_dao_category
         */
        $l_cat = self::get_dao_class($p_params);

        /* Process data */
        if ($l_cat)
        {
            $l_properties = $l_cat->get_properties();
            $l_response   = [];

            if (is_array($l_properties) && count($l_property))
            {
                foreach ($l_property AS $l_prop_value)
                {
                    $l_property = $l_properties[$l_prop_value];

                    if ($l_property["ui"]["type"] == 'dialog' || $l_property["ui"]["params"]['p_strPopupType'] == "dialog_plus")
                    {
                        $l_condition = null;
                        if (!isset($p_params['ignoreCondition']) || !$p_params['ignoreCondition'])
                        {
                            if (isset($l_property["ui"]["params"]["condition"]))
                            {
                                $l_condition = $l_property["ui"]["params"]["condition"];
                            }
                        }

                        $l_dao   = new isys_cmdb_dao_dialog_admin($this->m_dao->get_database_component());
                        $l_table = $l_property["ui"]["params"]["p_strTable"];
                        $l_res   = $l_dao->get_data($l_property["ui"]["params"]["p_strTable"], null, $l_condition);

                        if ($l_res->num_rows())
                        {
                            while ($l_row = $l_res->get_row())
                            {
                                $l_response[] = [
                                    "id"    => $l_row[$l_table . "__id"],
                                    "const" => isset($l_row[$l_table . "__const"]) ? $l_row[$l_table . "__const"] : '',
                                    "title" => _L($l_row[$l_table . "__title"]),
                                ];
                            }
                        }
                    }
                }

                return $l_response;
            }
            else
            {
                throw new isys_exception_api("You have to deliver at least one property parameter.");
            }
        }

        return false;
    } // function

    /**
     * Create Dialog+ Entry
     *
     * @author Selcuk Kekec <skekec@i-doit.com>
     * $p_param structure:
     *     array (
     *         'property'       =>      'manufacturer',
     *         'cat(g|s)ID'     =>      'C__CATG__CPU',
     *         'value'          =>      'New Dialog+ Entry Title'
     *     )
     *
     * @return isys_api_model_cmdb Returns itself.
     */
    public function create($p_params)
    {
        return $this->dialog_routine($p_params, self::CL__DIALOG__CREATE);
    }

    /**
     * Delete Dialog+ Entry
     *
     * @author Selcuk Kekec <skekec@i-doit.com>
     *
     * @param array $p_params
     *
     * $p_param structure:
     *     array (
     *         'property'       =>      'manufacturer',
     *         'cat(g|s)ID'     =>      'C__CATG__CPU',
     *         'entry_id'       =>      DIALOG_ENTRY_ID
     *     )
     *
     * @return type
     */
    public function delete($p_params)
    {
        return $this->dialog_routine($p_params, self::CL__DIALOG__DELETE);
    }

    /**
     * Update Dialog+ Entry
     *
     * @author Selcuk Kekec <skekec@i-doit.com>
     *
     * @param array $p_params
     *
     * $p_param structure:
     *     array (
     *         'property'       =>      'manufacturer',
     *         'cat(g|s)ID'     =>      'C__CATG__CPU',
     *         'value'          =>      'New Dialog+ Entry Title',
     *         'entry_id'       =>      DIALOG_ENTRY_ID
     *     )
     *
     * @return type
     */
    public function update($p_params)
    {
        return $this->dialog_routine($p_params, self::CL__DIALOG__UPDATE);
    }

// function

    /**
     * Create/Update/Delete Dialog+
     *
     * @author Selcuk Kekec <skekec@i-doit.com>
     *
     * @param array  $p_params
     * @param string $p_mode CL__DIALOG__[CREATE|UPDATE|DELETE]
     *
     * @return boolean
     * @throws isys_exception_api
     */
    public function dialog_routine($p_params, $p_mode = self::CL__DIALOG__CREATE)
    {
        /* Response Array */
        $l_response = ["success" => false,];

        /* Check: Category [G/S] identifier */
        if ((isset($p_params[C__CMDB__GET__CATG]) && !empty($p_params[C__CMDB__GET__CATG])) || (isset($p_params[C__CMDB__GET__CATS]) && !empty($p_params[C__CMDB__GET__CATS])) || (isset($p_params['category']) && !empty($p_params['category'])))
        {
            /* Retrieve Category-DAO */
            if (($l_cat = self::get_dao_class($p_params, $this->m_dao)))
            {

                /* SET value to dummy */
                if ($p_mode == self::CL__DIALOG__DELETE) $p_params["value"] = "DUMMY";

                /* Parameter: Property and Value are setted and not empty */
                if (isset($p_params["property"]) && !empty($p_params["property"]) && isset($p_params["value"]) && !empty($p_params["value"]))
                {

                    /* Check for id */
                    if ($p_mode == self::CL__DIALOG__UPDATE)
                    {
                        if (!isset($p_params['entry_id']) || empty($p_params['entry_id'])) throw new isys_exception_api("Please specify an Entry-ID.");
                    }

                    $l_properties = $l_cat->get_properties();

                    /* Does Property exists in category? */
                    if (is_string($p_params["property"]) && isset($l_properties[$p_params["property"]]))
                    {
                        /* Is Property a Dialog+ */
                        if (isset($l_properties[$p_params["property"]]["ui"]["params"]["p_strPopupType"]) && $l_properties[$p_params["property"]]["ui"]["params"]["p_strPopupType"] == 'dialog_plus')
                        {

                            /* Initialize Dialog-Admin */
                            $l_dao      = new isys_cmdb_dao_dialog_admin($this->m_dao->get_database_component());
                            $l_strTable = $l_properties[$p_params["property"]]["ui"]["params"]["p_strTable"];

                            /* Create / Update */
                            switch ($p_mode)
                            {
                                case self::CL__DIALOG__CREATE:
                                    /* Create new Dialog-Entry */
                                    if (!($l_intID = $l_dao->create($l_strTable, $p_params["value"], null, null, 1)))
                                    {
                                        throw new isys_exception_api("Unable to create a dialog entry.");
                                    } // if
                                    break;
                                case self::CL__DIALOG__UPDATE:
                                    if (!($l_intID = $l_dao->save($p_params["entry_id"], $l_strTable, $p_params["value"], null, null, 1)))
                                    {
                                        throw new isys_exception_api("Unable to update dialog entry.");
                                    }
                                    else
                                    {
                                        $l_intID = $p_params["entry_id"];
                                    }
                                    break;
                                case self::CL__DIALOG__DELETE:
                                    if (!($l_intID = $l_dao->delete($l_strTable, $p_params["entry_id"])))
                                    {
                                        throw new isys_exception_api("Unable to delete dialog entry.");
                                    }
                                    else
                                    {
                                        $l_intID = $p_params["entry_id"];
                                    }
                                    break;
                            } // switch

                            /* Set Response array */
                            list($l_response["entry_id"], $l_response["success"]) = [
                                $l_intID,
                                true
                            ];
                        }
                        else
                        {
                            throw new isys_exception_api("Property is not a Dialog+.");
                        }
                    }
                    else
                    {
                        throw new isys_exception_api("Property '" . $p_params["property"] . "' does not exist in Category");
                    }
                }
                else
                {
                    throw new isys_exception_api("Required Parameter is not setted or empty. Please be sure that 'property' and 'value' are setted.");
                }
            }
            else
            {
                throw new isys_exception_api("Unable to retrieve category DAO by given identifier");
            }
        }
        else
        {
            throw new isys_exception_api("Given category identifier is not valid or not setted.");
        }

        return $l_response;
    } // function

    public function __construct(isys_cmdb_dao &$p_dao)
    {
        $this->m_dao = $p_dao;
        parent::__construct();
    } // function

} // class