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
 * @author     Selcuk Kekec <skekec@synetics.de>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_api_model_cmdb_filter extends isys_api_model_cmdb implements isys_api_model_interface
{

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
     * Documentation missing
     *
     * @param array $p_params
     *
     * @return array
     */
    public function read($p_params)
    {
        if (isset($p_params["option"]) && method_exists($this, $p_params["option"]))
        {
            // "option" is set to read by isys_api_model_cmdb in case the method is called via "cmdb.filter.read"
            // just preventing an infinite loop here
            if ($p_params["option"] !== 'read')
            {
                return call_user_func_array(
                    [
                        $this,
                        $p_params["option"]
                    ],
                    [$p_params]
                );
            }
        }
        else
        {
            throw new isys_exception_api("Required parameter option not set.");
        }
    } // function

    /**
     * @param string $p_method Data method
     * @param array  $p_params Parameters (depends on data method)
     *
     * @return isys_api_model_cmdb Returns itself.
     */
    public function create($p_params)
    {
        throw new isys_exception_api('Creating is not possible here.');
    } // function

    /**
     * @param string $p_method Data method
     * @param array  $p_params Parameters (depends on data method)
     *
     * @return isys_api_model_cmdb Returns itself.
     */
    public function delete($p_params)
    {
        throw new isys_exception_api('Deleting is not possible here.');
    } // function

    /**
     * @param string $p_method Data method
     * @param array  $p_params Parameters (depends on data method)
     *
     * @return isys_api_model_cmdb Returns itself.
     */
    public function update($p_params)
    {
        throw new isys_exception_api('Updating is not possible here.');
    } // function

    /**
     * Get all objects with hostname and ip address which where updated in a specific time frame
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     * "before" => strtotime("2013-05-17")
     *              OR
     * "after"  => strtotime("2013-05-17")
     *              OR
     * "between" => array(
     *     "start" => strtotime("2013-05-17 09:20:26"),
     *     "end"   => strtotime("2013-05-17 09:46:09"),
     * )
     * "objTypeID" => 5 || array(4,5,6,...)
     * "objTypeConstant" => 'C__OBJTYPE__SERVER || array('C__OBJTYPE__SERVER', 'C__OBJTYPE__CLIENT',...)
     * "assignedNet" => ID of assigned net object
     * "hostfilter" => ["hostname"]
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     * @param array $p_params
     *
     * @return array
     */
    public function getUpdatedIPs($p_params)
    {
        /* Introduce variables */
        $l_api_model_category = new isys_api_model_cmdb_category($this->m_dao);
        $l_return             = [];
        $l_needed_fields      = [
            '*'
        ];

        $l_mapping = [
            'isys_obj__id'                   => 'id',
            'isys_obj__title'                => 'title',
            'isys_obj__sysid'                => 'sysid',
            'isys_obj__isys_obj_type__id'    => 'type',
            'isys_obj_type__title'           => [
                '_L',
                'type_title'
            ],
            'isys_obj_type_group__id'        => 'type_group',
            'isys_obj_type_group__title'     => [
                '_L',
                'type_group_title'
            ],
            'isys_obj__status'               => 'status',
            'isys_obj__isys_cmdb_status__id' => 'cmdb_status',
            'isys_cmdb_status__title'        => [
                '_L',
                'cmdb_status_title'
            ]
        ];

        // Raw mode:
        $l_raw = false;
        if (isset($p_params['raw']))
        {
            $l_raw = filter_var($p_params['raw'], FILTER_VALIDATE_BOOLEAN);
        } // if

        /* BuildQuery */
        $l_sql = "SELECT " . implode(",", $l_needed_fields) . " FROM isys_obj AS OBJECT " . /* JOIN: IPs */
            "INNER JOIN isys_catg_ip_list IP ON IP.isys_catg_ip_list__isys_obj__id = OBJECT.isys_obj__id " .
            "INNER JOIN isys_cats_net_ip_addresses_list NET ON NET.isys_cats_net_ip_addresses_list__id = IP.isys_catg_ip_list__isys_cats_net_ip_addresses_list__id " .
            /* JOIN: Logbook */
            "INNER JOIN isys_catg_logb_list CATLOG ON CATLOG.isys_catg_logb_list__isys_obj__id = OBJECT.isys_obj__id " .
            "INNER JOIN isys_logbook LOGBOOK ON LOGBOOK.isys_logbook__id = CATLOG.isys_catg_logb_list__isys_logbook__id " . /* JOIN: isys_obj_type */
            "INNER JOIN isys_obj_type OBJTYPE ON OBJTYPE.isys_obj_type__id = OBJECT.isys_obj__isys_obj_type__id ";

        $l_sql .= "WHERE " .

            /* IP/Hostname condition */
            "( (CHAR_LENGTH(IP.isys_catg_ip_list__hostname) > 0)
					AND
					(CHAR_LENGTH(NET.isys_cats_net_ip_addresses_list__title)>0) ) AND " .

            /* Logbook */
            "(" . "LOGBOOK.isys_logbook__category_static = 'LC__CATG__IP_ADDRESS' && " . "(
					(LOGBOOK.isys_logbook__description LIKE
						CONCAT('%UPDATE%WHERE%(isys_catg_ip_list__id%=%', CONCAT(IP.isys_catg_ip_list__id, '%'))
					)" . " OR " . "   (LOGBOOK.isys_logbook__description LIKE
						CONCAT('%INSERT INTO%isys_catg_ip_list__isys_cats_net_ip_addresses_list__id%=%', CONCAT(IP.isys_catg_ip_list__isys_cats_net_ip_addresses_list__id, ' %'))
					))" . ") ";

        /* Filter by hostname */
        if (isset($p_params['hostfilter']))
        {
            $l_sql_hostfilter = $l_hostfilter = [];
            if (is_string($p_params['hostfilter']) && strlen($p_params['hostfilter']) > 1)
            {
                $l_hostfilter[] = $p_params['hostfilter'];
            }
            elseif (is_array($p_params['hostfilter']))
            {
                $l_hostfilter = $p_params['hostfilter'];
            }

            foreach ($l_hostfilter as $l_filter)
            {
                if ($l_filter)
                {
                    $l_sql_hostfilter[] = "(IP.isys_catg_ip_list__hostname LIKE " . $this->m_dao->convert_sql_text('%' . $l_filter . '%') . ") ";
                }
            }

            if (count($l_sql_hostfilter) > 0)
            {
                $l_sql .= 'AND (' . implode(' OR ', $l_sql_hostfilter) . ')';
            }
        }

        /* Assigned Net Condition */
        if (isset($p_params['assignedNet']) && is_numeric($p_params['assignedNet']))
        {
            $l_sql .= "AND (NET.isys_cats_net_ip_addresses_list__isys_obj__id = " . $this->m_dao->convert_sql_id($p_params['assignedNet']) . ") ";
        }

        if (isset($p_params['before']) && is_numeric($p_params['before']))
        {
            /* Before */
            $l_sql .= "AND LOGBOOK.isys_logbook__date <= '" . date("Y-m-d H:i:s", $p_params['before']) . "' ";
        }
        else
        {
            if ($p_params['after'] && is_numeric($p_params['after']))
            {
                /* After */
                $l_sql .= "AND LOGBOOK.isys_logbook__date >= '" . date("Y-m-d H:i:s", $p_params['after']) . "' ";
            }
            else
            {
                if ($p_params['between'])
                {
                    /* Between */
                    $l_sql .= "AND ((LOGBOOK.isys_logbook__date >= '" . date("Y-m-d H:i:s", $p_params['between']['start']) . "') AND (LOGBOOK.isys_logbook__date <= '" . date(
                            "Y-m-d H:i:s",
                            $p_params['between']['end']
                        ) . "')) ";
                }
            }
        }

        /* Filter by object type */
        if (isset($p_params['objTypeID']) || isset($p_params['objTypeConstant']))
        {
            $l_conditionValues = null;
            if (isset($p_params['objTypeID']) && (is_numeric($p_params['objTypeID']) || is_array($p_params['objTypeID'])))
            {
                $l_conditionValues = [
                    'field' => 'isys_obj_type__id',
                    'value' => $p_params['objTypeID'],
                ];
            }
            else
            {
                if (isset($p_params['objTypeConstant']) && (is_string($p_params['objTypeConstant']) || is_array($p_params['objTypeConstant'])))
                {
                    $l_conditionValues = [
                        'field' => 'isys_obj_type__const',
                        'value' => $p_params['objTypeConstant'],
                    ];
                }
            }

            if ($l_conditionValues !== null)
            {
                if (!is_array($l_conditionValues['value']))
                {
                    $l_objTypeCondition = ' = \'' . $l_conditionValues['value'] . '\'';
                }
                else
                {
                    if (is_array($l_conditionValues['value']))
                    {
                        $l_objTypeCondition = ' IN(\'' . implode('\',\'', $l_conditionValues['value']) . '\')';
                    }
                    else
                    {
                        $l_objTypeCondition = ' ';
                    }
                }

                $l_sql .= ' AND (OBJTYPE.' . $l_conditionValues['field'] . ' ' . $l_objTypeCondition . ') ';
            }
        }

        /* Grouping/Sorting */
        $l_sql .= "GROUP BY NET.isys_cats_net_ip_addresses_list__id ";

        /* Get results */
        $l_res = $this->m_dao->retrieve($l_sql);
        if ($l_res->num_rows())
        {
            while ($l_row = $l_res->get_row())
            {
                if ($l_raw)
                {
                    $l_return[] = $l_row;
                }
                else
                {
                    $l_return[] = $this->format_by_mapping($l_mapping, $l_row);
                } // if

                $l_category_data = $l_api_model_category->read(
                    [
                        'raw'       => $l_raw,
                        'objID'     => $l_row['isys_obj__id'],
                        'catgID'    => 'C__CATG__IP',
                        'condition' => ' AND isys_catg_ip_list__id = ' . $l_row['isys_catg_ip_list__id']
                    ]
                );
                if (isset($l_category_data[0]))
                {
                    $l_return[count($l_return) - 1]['ip'] = $l_category_data[0];
                }
                else
                {
                    $l_return[count($l_return) - 1]['ip'] = [];
                }

                $l_return[count($l_return) - 1]['software'] = $l_api_model_category->read(
                    [
                        'raw'    => $l_raw,
                        'objID'  => $l_row['isys_obj__id'],
                        'catgID' => 'C__CATG__APPLICATION'
                    ]
                );
            }
        }

        return $l_return;
    } // function

    /**
     * Get all objects with recorded changes
     * in catgory ip or softwareassignment
     * between/before/after a specific date.
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     * "before" => strtotime("2013-05-17")
     *              OR
     * "after"  => strtotime("2013-05-17")
     *              OR
     * "between" => array(
     *     "start" => strtotime("2013-05-17 09:20:26"),
     *     "end"   => strtotime("2013-05-17 09:46:09"),
     * )
     * "objTypeID" => 5 || array(4,5,6,...)
     * "objTypeConstant" => 'C__OBJTYPE__SERVER || array('C__OBJTYPE__SERVER', 'C__OBJTYPE__CLIENT',...)
     * "orderBy" => TableColumn
     * "assignedNet" => ID of assigned net object
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     * @param array $p_params
     *
     * @return array
     */
    public function getValidObjects($p_params)
    {
        /* Introduce variables */
        $l_api_model_category = new isys_api_model_cmdb_category($this->m_dao);
        $l_return             = [];
        $l_needed_fields      = [
            '*'
        ];

        $l_mapping = [
            'isys_obj__id'                   => 'id',
            'isys_obj__title'                => 'title',
            'isys_obj__sysid'                => 'sysid',
            'isys_obj__isys_obj_type__id'    => 'type',
            'isys_obj_type__title'           => [
                '_L',
                'type_title'
            ],
            'isys_obj_type_group__id'        => 'type_group',
            'isys_obj_type_group__title'     => [
                '_L',
                'type_group_title'
            ],
            'isys_obj__status'               => 'status',
            'isys_obj__isys_cmdb_status__id' => 'cmdb_status',
            'isys_cmdb_status__title'        => [
                '_L',
                'cmdb_status_title'
            ]
        ];

        // Raw mode:
        $l_raw = false;
        if (isset($p_params['raw']))
        {
            $l_raw = filter_var($p_params['raw'], FILTER_VALIDATE_BOOLEAN);
        } // if

        /* BuildQuery */
        $l_sql = "SELECT " . implode(",", $l_needed_fields) . " FROM isys_obj AS OBJECT " . /* JOIN: IPs */
            "INNER JOIN isys_catg_ip_list IP ON IP.isys_catg_ip_list__isys_obj__id = OBJECT.isys_obj__id " .
            "INNER JOIN isys_cats_net_ip_addresses_list NET ON NET.isys_cats_net_ip_addresses_list__id = IP.isys_catg_ip_list__isys_cats_net_ip_addresses_list__id " .
            /* JOIN: Softwareassignment */
            "INNER JOIN isys_catg_application_list APP ON APP.isys_catg_application_list__isys_obj__id = OBJECT.isys_obj__id " .
            "INNER JOIN isys_connection APPCON ON APP.isys_catg_application_list__isys_connection__id = APPCON.isys_connection__id " . /* JOIN: isys_obj_type */
            "INNER JOIN isys_obj_type OBJTYPE ON OBJTYPE.isys_obj_type__id = OBJECT.isys_obj__isys_obj_type__id ";

        $l_sql .= "WHERE " .

            /* IP/Hostname condition */
            "( (CHAR_LENGTH(IP.isys_catg_ip_list__hostname) > 0)
					AND
					(CHAR_LENGTH(NET.isys_cats_net_ip_addresses_list__title)>0) ) " . " AND(NET.isys_cats_net_ip_addresses_list__title!='0.0.0.0') " .

            /* Software condition */
            "AND (APPCON.isys_connection__isys_obj__id IS NOT NULL) ";

        /* Assigned Net Condition */
        if (isset($p_params['assignedNet']) && is_numeric($p_params['assignedNet']))
        {
            $l_sql .= "AND (isys_cats_net_ip_addresses_list__isys_obj__id = " . $this->m_dao->convert_sql_id($p_params['assignedNet']) . ") ";
        }

        /* Filter by object type */
        if (isset($p_params['objTypeID']) || isset($p_params['objTypeConstant']))
        {
            $l_conditionValues = null;
            if (isset($p_params['objTypeID']) && (is_numeric($p_params['objTypeID']) || is_array($p_params['objTypeID'])))
            {
                $l_conditionValues = [
                    'field' => 'isys_obj_type__id',
                    'value' => $p_params['objTypeID'],
                ];
            }
            else
            {
                if (isset($p_params['objTypeConstant']) && (is_string($p_params['objTypeConstant']) || is_array($p_params['objTypeConstant'])))
                {
                    $l_conditionValues = [
                        'field' => 'isys_obj_type__const',
                        'value' => $p_params['objTypeConstant'],
                    ];
                }
            }

            if ($l_conditionValues !== null)
            {
                if (!is_array($l_conditionValues['value']))
                {
                    $l_objTypeCondition = ' = \'' . $l_conditionValues['value'] . '\'';
                }
                else
                {
                    if (is_array($l_conditionValues['value']))
                    {
                        $l_objTypeCondition = ' IN(\'' . implode('\',\'', $l_conditionValues['value']) . '\')';
                    }
                    else
                    {
                        $l_objTypeCondition = ' ';
                    }
                }

                $l_sql .= ' AND (OBJTYPE.' . $l_conditionValues['field'] . ' ' . $l_objTypeCondition . ') ';
            }
        }

        /* Grouping/Sorting */
        $l_sql .= "GROUP BY OBJECT.isys_obj__id ";

        if (isset($p_params['orderBy'])) $l_sql .= "ORDER BY " . $p_params['orderBy'] . " ";

        /* Get results */
        $l_res = $this->m_dao->retrieve($l_sql);
        if ($l_res->num_rows())
        {
            while ($l_row = $l_res->get_row())
            {
                if ($l_raw)
                {
                    $l_return[] = $l_row;
                }
                else
                {
                    $l_return[] = $this->format_by_mapping($l_mapping, $l_row);
                } // if

                /* Read: IPs */
                $l_categoryData = $l_api_model_category->read(
                    [
                        'raw'       => $l_raw,
                        'objID'     => $l_row['isys_obj__id'],
                        'catgID'    => 'C__CATG__IP',
                        'condition' => ' AND (isys_obj__id = ' . $l_row['isys_obj__id'] . ')
                                             AND (CHAR_LENGTH(isys_catg_ip_list__hostname) > 0)
                                             AND(CHAR_LENGTH(ipv4.isys_cats_net_ip_addresses_list__title)>0)
                                             AND(ipv4.isys_cats_net_ip_addresses_list__title!=\'0.0.0.0\')
                                             ORDER BY isys_catg_ip_list__primary DESC'
                    ]
                );

                $l_return[count($l_return) - 1]['ip'] = $l_categoryData[0];

                /* Read: Softwares */
                $l_return[count($l_return) - 1]['software'] = $l_api_model_category->read(
                    [
                        'raw'       => $l_raw,
                        'objID'     => $l_row['isys_obj__id'],
                        'catgID'    => 'C__CATG__APPLICATION',
                        'condition' => ' AND (isys_catg_application_list__isys_obj__id = ' . $l_row['isys_obj__id'] . ')AND(isys_connection__isys_obj__id IS NOT NULL) '
                    ]
                );

                /* Read: Global */
                $l_return[count($l_return) - 1]['global'] = reset(
                    $l_api_model_category->read(
                        [
                            'raw'    => $l_raw,
                            'objID'  => $l_row['isys_obj__id'],
                            'catgID' => 'C__CATG__GLOBAL'
                        ]
                    )
                );
            }
        }

        //die($l_sql);
        return $l_return;
    }

    /**
     * Retrieve all objects with valid ip and software assignment
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     * "objTypeID" => 5 || array(4,5,6,...)
     *                  OR
     * "objTypeConstant" => 'C__OBJTYPE__SERVER || array('C__OBJTYPE__SERVER', 'C__OBJTYPE__CLIENT',...)
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     * @param array $p_params
     *
     * @return array
     */
    public function getChangedObjects($p_params)
    {
        /* Introduce variables */
        $l_api_model_category = new isys_api_model_cmdb_category($this->m_dao);
        $l_return             = [];
        $l_needed_fields      = [
            '*'
        ];

        $l_mapping = [
            'isys_obj__id'                   => 'id',
            'isys_obj__title'                => 'title',
            'isys_obj__sysid'                => 'sysid',
            'isys_obj__isys_obj_type__id'    => 'type',
            'isys_obj_type__title'           => [
                '_L',
                'type_title'
            ],
            'isys_obj_type_group__id'        => 'type_group',
            'isys_obj_type_group__title'     => [
                '_L',
                'type_group_title'
            ],
            'isys_obj__status'               => 'status',
            'isys_obj__isys_cmdb_status__id' => 'cmdb_status',
            'isys_cmdb_status__title'        => [
                '_L',
                'cmdb_status_title'
            ]
        ];

        // Raw mode:
        $l_raw = false;
        if (isset($p_params['raw']))
        {
            $l_raw = filter_var($p_params['raw'], FILTER_VALIDATE_BOOLEAN);
        } // if

        /* BuildQuery */
        $l_sql = "SELECT " . implode(",", $l_needed_fields) . " FROM isys_obj AS OBJECT " . /* JOIN: IPs */
            "INNER JOIN isys_catg_ip_list IP ON IP.isys_catg_ip_list__isys_obj__id = OBJECT.isys_obj__id " .
            "INNER JOIN isys_cats_net_ip_addresses_list NET ON NET.isys_cats_net_ip_addresses_list__id = IP.isys_catg_ip_list__isys_cats_net_ip_addresses_list__id " .
            /* JOIN: Softwareassignment */
            "INNER JOIN isys_catg_application_list APP ON APP.isys_catg_application_list__isys_obj__id = OBJECT.isys_obj__id " .
            "INNER JOIN isys_connection APPCON ON APP.isys_catg_application_list__isys_connection__id = APPCON.isys_connection__id " . /* JOIN: Logbook */
            "INNER JOIN isys_catg_logb_list CATLOG ON CATLOG.isys_catg_logb_list__isys_obj__id = OBJECT.isys_obj__id " .
            "INNER JOIN isys_logbook LOGBOOK ON LOGBOOK.isys_logbook__id = CATLOG.isys_catg_logb_list__isys_logbook__id " . /* JOIN: isys_obj_type */
            "INNER JOIN isys_obj_type OBJTYPE ON OBJTYPE.isys_obj_type__id = OBJECT.isys_obj__isys_obj_type__id ";

        $l_sql .= "WHERE " .

            /* IP/Hostname condition */
            "( (CHAR_LENGTH(IP.isys_catg_ip_list__hostname) > 0)
					AND
					(CHAR_LENGTH(NET.isys_cats_net_ip_addresses_list__title)>0) ) " . " AND(NET.isys_cats_net_ip_addresses_list__title!='0.0.0.0') " .

            /* Software condition */
            "AND (APPCON.isys_connection__isys_obj__id IS NOT NULL) " .

            /* Logbook */
            "AND (" . "
                    LOGBOOK.isys_logbook__category_static = 'LC__CATG__IP_ADDRESS' OR
                    LOGBOOK.isys_logbook__category_static = 'LC__CMDB__CATG__APPLICATION' OR
                    LOGBOOK.isys_logbook__category_static = 'LC__CMDB__CATG__GLOBAL' OR
                    LOGBOOK.isys_logbook__event_static = 'C__LOGBOOK_EVENT__OBJECT_RECYCLED' OR
                    LOGBOOK.isys_logbook__event_static = 'C__LOGBOOK_EVENT__OBJECT_ARCHIVED' OR
                    LOGBOOK.isys_logbook__event_static = 'C__LOGBOOK_EVENT__OBJECT_DELETED'" . ") ";

        /* Logbook-Filter */
        if (isset($p_params['before']) && is_numeric($p_params['before']))
        {
            /* Before */
            $l_sql .= "AND LOGBOOK.isys_logbook__date <= '" . date("Y-m-d H:i:s", $p_params['before']) . "' ";
        }
        else
        {
            if ($p_params['after'] && is_numeric($p_params['after']))
            {
                /* After */
                $l_sql .= "AND LOGBOOK.isys_logbook__date >= '" . date("Y-m-d H:i:s", $p_params['after']) . "' ";
            }
            else
            {
                if ($p_params['between'])
                {
                    /* Between */
                    $l_sql .= "AND ((LOGBOOK.isys_logbook__date >= '" . date("Y-m-d H:i:s", $p_params['between']['start']) . "') AND (LOGBOOK.isys_logbook__date <= '" . date(
                            "Y-m-d H:i:s",
                            $p_params['between']['end']
                        ) . "')) ";
                }
            }
        }

        /* Filter by object type */
        if (isset($p_params['objTypeID']) || isset($p_params['objTypeConstant']))
        {
            $l_conditionValues = null;
            if (isset($p_params['objTypeID']) && (is_numeric($p_params['objTypeID']) || is_array($p_params['objTypeID'])))
            {
                $l_conditionValues = [
                    'field' => 'isys_obj_type__id',
                    'value' => $p_params['objTypeID'],
                ];
            }
            else
            {
                if (isset($p_params['objTypeConstant']) && (is_string($p_params['objTypeConstant']) || is_array($p_params['objTypeConstant'])))
                {
                    $l_conditionValues = [
                        'field' => 'isys_obj_type__const',
                        'value' => $p_params['objTypeConstant'],
                    ];
                }
            }

            /* Assigned Net Condition */
            if (isset($p_params['assignedNet']) && is_numeric($p_params['assignedNet']))
            {
                $l_sql .= "AND (isys_cats_net_ip_addresses_list__isys_obj__id = " . $this->m_dao->convert_sql_id($p_params['assignedNet']) . ") ";
            }

            if ($l_conditionValues !== null)
            {
                if (!is_array($l_conditionValues['value']))
                {
                    $l_objTypeCondition = ' = \'' . $l_conditionValues['value'] . '\'';
                }
                else
                {
                    if (is_array($l_conditionValues['value']))
                    {
                        $l_objTypeCondition = ' IN(\'' . implode('\',\'', $l_conditionValues['value']) . '\')';
                    }
                    else
                    {
                        $l_objTypeCondition = ' ';
                    }
                }

                $l_sql .= ' AND (OBJTYPE.' . $l_conditionValues['field'] . ' ' . $l_objTypeCondition . ') ';
            }
        }

        /* Grouping/Sorting */
        $l_sql .= "GROUP BY OBJECT.isys_obj__id ";

        if (isset($p_params['orderBy'])) $l_sql .= "ORDER BY " . $p_params['orderBy'] . " ";

        /* Get results */
        $l_res = $this->m_dao->retrieve($l_sql);
        if ($l_res->num_rows())
        {
            while ($l_row = $l_res->get_row())
            {
                if ($l_raw)
                {
                    $l_return[] = $l_row;
                }
                else
                {
                    $l_return[] = $this->format_by_mapping($l_mapping, $l_row);
                } // if

                /* Read: IPs */
                $l_return[count($l_return) - 1]['ip'] = $l_api_model_category->read(
                    [
                        'raw'       => $l_raw,
                        'objID'     => $l_row['isys_obj__id'],
                        'catgID'    => 'C__CATG__IP',
                        'condition' => ' AND (isys_obj__id = ' . $l_row['isys_obj__id'] . ')
                                             AND (CHAR_LENGTH(isys_catg_ip_list__hostname) > 0)
                                             AND(CHAR_LENGTH(ipv4.isys_cats_net_ip_addresses_list__title)>0)
                                             AND(ipv4.isys_cats_net_ip_addresses_list__title!=\'0.0.0.0\')
                                             ORDER BY isys_catg_ip_list__primary DESC'
                        //'condition' => ' AND isys_catg_ip_list__id = ' . $l_row['isys_catg_ip_list__id']
                    ]
                );

                /* Read: Softwares */
                $l_return[count($l_return) - 1]['software'] = $l_api_model_category->read(
                    [
                        'raw'       => $l_raw,
                        'objID'     => $l_row['isys_obj__id'],
                        'catgID'    => 'C__CATG__APPLICATION',
                        'condition' => ' AND (isys_catg_application_list__isys_obj__id = ' . $l_row['isys_obj__id'] . ')AND(isys_connection__isys_obj__id IS NOT NULL) '
                    ]
                );

                /* Read: Global */
                $l_return[count($l_return) - 1]['global'] = reset(
                    $l_api_model_category->read(
                        [
                            'raw'    => $l_raw,
                            'objID'  => $l_row['isys_obj__id'],
                            'catgID' => 'C__CATG__GLOBAL'
                        ]
                    )
                );
            }
        }

        return $l_return;
    }

    /**
     * Constructor
     */
    public function __construct(isys_cmdb_dao &$p_dao)
    {
        $this->m_dao = $p_dao;
        parent::__construct();
    }

} // class