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
 * DAO: global category for computing resources.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Blümer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_computing_resources extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'computing_resources';

    /**
     * Category entry is purgable
     *
     * @var bool
     */
    protected $m_is_purgable = true;

    /**
     * Dynamic property handling for retrieving the RAM with unit.
     *
     * @param   array $p_row
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function dynamic_property_callback_ram(array $p_row)
    {
        global $g_comp_database;

        $l_return = '';

        $l_dao = isys_cmdb_dao_category_g_computing_resources::instance($g_comp_database);

        $l_row = $l_dao->get_data(null, $p_row['isys_obj__id'])
            ->get_row();

        if ($l_row !== false)
        {
            $l_unit_row = $l_dao->get_dialog('isys_memory_unit', $l_row['isys_catg_computing_resources_list__ram__isys_memory_unit__id'])
                ->get_row();

            $l_return = isys_convert::memory(
                    $l_row['isys_catg_computing_resources_list__ram'],
                    $l_row['isys_catg_computing_resources_list__ram__isys_memory_unit__id'],
                    C__CONVERT_DIRECTION__BACKWARD
                ) . ' ' . $l_unit_row['isys_memory_unit__title'];
        } // if

        return $l_return;
    } // function

    /**
     * Dynamic property handling for retrieving the CPU with unit.
     *
     * @param   array $p_row
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function dynamic_property_callback_cpu(array $p_row)
    {
        global $g_comp_database;

        $l_return = '';

        $l_dao = isys_cmdb_dao_category_g_computing_resources::instance($g_comp_database);

        $l_row = $l_dao->get_data(null, $p_row['isys_obj__id'])
            ->get_row();

        if ($l_row !== false)
        {
            $l_unit_row = $l_dao->get_dialog('isys_frequency_unit', $l_row['isys_catg_computing_resources_list__cpu__isys_frequency_unit__id'])
                ->get_row();

            $l_return = isys_convert::frequency(
                    $l_row['isys_catg_computing_resources_list__cpu'],
                    $l_row['isys_catg_computing_resources_list__cpu__isys_frequency_unit__id'],
                    C__CONVERT_DIRECTION__BACKWARD
                ) . ' ' . $l_unit_row['isys_frequency_unit__title'];
        } // if

        return $l_return;
    } // function

    /**
     * Dynamic property handling for retrieving the disc space with unit.
     *
     * @param   array $p_row
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function dynamic_property_callback_disc_space(array $p_row)
    {
        global $g_comp_database;

        $l_return = '';

        $l_dao = isys_cmdb_dao_category_g_computing_resources::instance($g_comp_database);

        $l_row = $l_dao->get_data(null, $p_row['isys_obj__id'])
            ->get_row();

        if ($l_row !== false)
        {
            $l_unit_row = $l_dao->get_dialog('isys_memory_unit', $l_row['isys_catg_computing_resources_list__ds__isys_memory_unit__id'])
                ->get_row();

            $l_return = isys_convert::memory(
                    $l_row['isys_catg_computing_resources_list__disc_space'],
                    $l_row['isys_catg_computing_resources_list__ds__isys_memory_unit__id'],
                    C__CONVERT_DIRECTION__BACKWARD
                ) . ' ' . $l_unit_row['isys_memory_unit__title'];
        } // if

        return $l_return;
    } // function

    /**
     * Dynamic property handling for retrieving the network bandwith with unit.
     *
     * @param   array $p_row
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function dynamic_property_callback_network_bandwidth(array $p_row)
    {
        global $g_comp_database;

        $l_return = '';

        $l_dao = isys_cmdb_dao_category_g_computing_resources::instance($g_comp_database);

        $l_row = $l_dao->get_data(null, $p_row['isys_obj__id'])
            ->get_row();

        if ($l_row !== false)
        {
            $l_unit_row = $l_dao->get_dialog('isys_port_speed', $l_row['isys_catg_computing_resources_list__nb__isys_port_speed__id'])
                ->get_row();

            $l_return = isys_convert::speed(
                    $l_row['isys_catg_computing_resources_list__network_bandwidth'],
                    $l_row['isys_catg_computing_resources_list__nb__isys_port_speed__id'],
                    C__CONVERT_DIRECTION__BACKWARD
                ) . ' ' . $l_unit_row['isys_port_speed__title'];
        } // if

        return $l_return;
    } // function

    /**
     * Executes the query to create the category entry referenced by isys_catg_cluster_members__id $p_fk_id.
     *
     * @param   integer $p_objID
     * @param   integer $p_newRecStatus
     * @param   float   $p_ram
     * @param   integer $p_ram_unit
     * @param   float   $p_cpu
     * @param   integer $p_cpu_unit
     * @param   float   $p_disc_space
     * @param   integer $p_disc_space_unit
     * @param   float   $p_bandwidth
     * @param   integer $p_bandwidth_unit
     * @param   String  $p_description
     *
     * @return  mixed  Integer with the newly created ID or boolean false
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function create($p_objID, $p_newRecStatus, $p_ram = null, $p_ram_unit = null, $p_cpu = null, $p_cpu_unit = null, $p_disc_space = null, $p_disc_space_unit = null, $p_bandwidth = null, $p_bandwidth_unit = null, $p_description = null)
    {
        $l_id = $this->create_connector('isys_catg_computing_resources_list', $p_objID);
        if ($this->save($l_id, $p_newRecStatus, $p_ram, $p_ram_unit, $p_cpu, $p_cpu_unit, $p_disc_space, $p_disc_space_unit, $p_bandwidth, $p_bandwidth_unit, $p_description))
        {
            return $l_id;
        }

        return false;
    } // function

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level.
     *
     * @param   integer $p_cat_level
     * @param   integer $p_newRecStatus
     * @param   float   $p_ram
     * @param   integer $p_ram_unit
     * @param   float   $p_cpu
     * @param   integer $p_cpu_unit
     * @param   float   $p_disc_space
     * @param   integer $p_disc_space_unit
     * @param   float   $p_bandwidth
     * @param   integer $p_bandwidth_unit
     * @param   string  $p_description
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function save($p_cat_level, $p_newRecStatus, $p_ram = null, $p_ram_unit = null, $p_cpu = null, $p_cpu_unit = null, $p_disc_space = null, $p_disc_space_unit = null, $p_bandwidth = null, $p_bandwidth_unit = null, $p_description = null)
    {
        // Convert ram from user's locale to invariant locale.
        $p_ram = isys_convert::memory(isys_helper::filter_number($p_ram), $p_ram_unit);

        // Convert disc space from user's locale to invariant locale.
        $p_disc_space = isys_convert::memory(isys_helper::filter_number($p_disc_space), $p_disc_space_unit);

        // Convert bandwidth from user's locale to invariant locale.
        $p_bandwidth = isys_convert::speed(isys_helper::filter_number($p_bandwidth), $p_bandwidth_unit);

        // Convert bandwidth from user's locale to invariant locale.
        $p_cpu = isys_convert::frequency(isys_helper::filter_number($p_cpu), $p_cpu_unit);

        $l_strSql = "UPDATE isys_catg_computing_resources_list SET " . "isys_catg_computing_resources_list__status = " . $this->convert_sql_id(
                $p_newRecStatus
            ) . ", " . "isys_catg_computing_resources_list__ram = " . $this->convert_sql_text(
                $p_ram
            ) . ", " . "isys_catg_computing_resources_list__ram__isys_memory_unit__id = " . $this->convert_sql_id(
                $p_ram_unit
            ) . ", " . "isys_catg_computing_resources_list__cpu = " . $this->convert_sql_text(
                $p_cpu
            ) . ", " . "isys_catg_computing_resources_list__cpu__isys_frequency_unit__id = " . $this->convert_sql_id(
                $p_cpu_unit
            ) . ", " . "isys_catg_computing_resources_list__disc_space = " . $this->convert_sql_text(
                $p_disc_space
            ) . ", " . "isys_catg_computing_resources_list__ds__isys_memory_unit__id = " . $this->convert_sql_id(
                $p_disc_space_unit
            ) . ", " . "isys_catg_computing_resources_list__network_bandwidth = " . $this->convert_sql_text(
                $p_bandwidth
            ) . ", " . "isys_catg_computing_resources_list__nb__isys_port_speed__id = " . $this->convert_sql_id(
                $p_bandwidth_unit
            ) . ", " . "isys_catg_computing_resources_list__description = " . $this->convert_sql_text(
                $p_description
            ) . " " . "WHERE isys_catg_computing_resources_list__id = " . $this->convert_sql_id($p_cat_level);

        return ($this->update($l_strSql) && $this->apply_update());
    } // function

    /**
     * Save global category cluster members element.
     *
     * @param   integer $p_cat_level
     * @param   integer & $p_intOldRecStatus
     * @param   bool    $p_create
     *
     * @return  mixed  Null oder an integer
     */
    public function save_element(&$p_cat_level, &$p_intOldRecStatus, $p_create = false)
    {
        $l_intErrorCode = -1;

        $l_catdata = $this->get_general_data();

        $p_intOldRecStatus = $l_catdata["isys_catg_computing_resources_list__status"];

        if (!empty($l_catdata["isys_catg_computing_resources_list__id"]))
        {

            $l_bRet = $this->save(
                $l_catdata["isys_catg_computing_resources_list__id"],
                C__RECORD_STATUS__NORMAL,
                $_POST['C__CATG__COMPUTING_RESOURCES__RAM'],
                $_POST['C__CATG__COMPUTING_RESOURCES__RAM__MEMORY_UNIT'],
                $_POST['C__CATG__COMPUTING_RESOURCES__CPU'],
                $_POST['C__CATG__COMPUTING_RESOURCES__CPU__FREQUENCY_UNIT'],
                $_POST['C__CATG__COMPUTING_RESOURCES__DISC_SPACE'],
                $_POST['C__CATG__COMPUTING_RESOURCES__DISC_SPACE__MEMORY_UNIT'],
                $_POST['C__CATG__COMPUTING_RESOURCES__NETWORK_BANDWIDTH'],
                $_POST['C__CATG__COMPUTING_RESOURCES__NETWORK_BANDWIDTH__SPEED'],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
            );

            $this->m_strLogbookSQL = $this->get_last_query();
        } // if

        return $l_bRet == true ? null : $l_intErrorCode;
    } // function

    /**
     * Method for retrieving the dynamic properties, used by the new list component.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function dynamic_properties()
    {
        return [
            '_ram'               => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__COMPUTING_RESOURCES__RAM',
                    C__PROPERTY__INFO__DESCRIPTION => 'RAM'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_obj__id'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_callback_ram'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST   => true,
                    C__PROPERTY__PROVIDES__REPORT => true
                ]
            ],
            '_cpu'               => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__FREQUENCY',
                    C__PROPERTY__INFO__DESCRIPTION => 'CPU'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_obj__id'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_callback_cpu'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST   => true,
                    C__PROPERTY__PROVIDES__REPORT => true
                ]
            ],
            '_disc_space'        => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__COMPUTING_RESOURCES__DISC_SPACE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Disc space'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_obj__id'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_callback_disc_space'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST   => true,
                    C__PROPERTY__PROVIDES__REPORT => true
                ]
            ],
            '_network_bandwidth' => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__COMPUTING_RESOURCES__NETWORK_BANDWIDTH',
                    C__PROPERTY__INFO__DESCRIPTION => 'Network bandwidth'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_obj__id'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_callback_network_bandwidth'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST   => true,
                    C__PROPERTY__PROVIDES__REPORT => true
                ]
            ]
        ];
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
            'ram'                    => array_replace_recursive(
                isys_cmdb_dao_category_pattern::float(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__COMPUTING_RESOURCES__RAM',
                        C__PROPERTY__INFO__DESCRIPTION => 'RAM'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_computing_resources_list__ram'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__COMPUTING_RESOURCES__RAM',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strClass' => 'input-dual-small'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'convert',
                            ['memory']
                        ],
                        C__PROPERTY__FORMAT__UNIT     => 'ram_unit'
                    ]
                ]
            ),
            'ram_unit'               => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB_CATG__MEMORY_UNIT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Unit'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD       => 'isys_catg_computing_resources_list__ram__isys_memory_unit__id',
                        C__PROPERTY__DATA__FIELD_ALIAS => 'ram_unit',
                        C__PROPERTY__DATA__TABLE_ALIAS => 'mem1',
                        C__PROPERTY__DATA__REFERENCES  => [
                            'isys_memory_unit',
                            'isys_memory_unit__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__COMPUTING_RESOURCES__RAM__MEMORY_UNIT',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable'        => 'isys_memory_unit',
                            'p_strClass'        => 'input-dual-large',
                            'p_bInfoIconSpacer' => 0
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ]
                ]
            ),
            'cpu'                    => array_replace_recursive(
                isys_cmdb_dao_category_pattern::float(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__FREQUENCY',
                        C__PROPERTY__INFO__DESCRIPTION => 'CPU frequency'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_computing_resources_list__cpu'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__COMPUTING_RESOURCES__CPU',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strClass' => 'input-dual-small'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'convert',
                            ['frequency']
                        ],
                        C__PROPERTY__FORMAT__UNIT     => 'cpu_unit'
                    ]
                ]
            ),
            'cpu_unit'               => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__CPU_FREQUENCY_UNIT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Frequence unit'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_computing_resources_list__cpu__isys_frequency_unit__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_frequency_unit',
                            'isys_frequency_unit__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__COMPUTING_RESOURCES__CPU__FREQUENCY_UNIT',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable'        => 'isys_frequency_unit',
                            'p_strClass'        => 'input-dual-large',
                            'p_bInfoIconSpacer' => 0
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ]
                ]
            ),
            'disc_space'             => array_replace_recursive(
                isys_cmdb_dao_category_pattern::float(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__COMPUTING_RESOURCES__DISC_SPACE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Disc space'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_computing_resources_list__disc_space'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__COMPUTING_RESOURCES__DISC_SPACE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strClass' => 'input-dual-small'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'convert',
                            ['memory']
                        ],
                        C__PROPERTY__FORMAT__UNIT     => 'disc_space_unit'
                    ]
                ]
            ),
            'disc_space_unit'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__COMPUTING_RESOURCES__DISC_SPACE_UNIT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Disc space unit'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD       => 'isys_catg_computing_resources_list__ds__isys_memory_unit__id',
                        C__PROPERTY__DATA__FIELD_ALIAS => 'ds_unit',
                        C__PROPERTY__DATA__TABLE_ALIAS => 'mem2',
                        C__PROPERTY__DATA__REFERENCES  => [
                            'isys_memory_unit',
                            'isys_memory_unit__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__COMPUTING_RESOURCES__DISC_SPACE__MEMORY_UNIT',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable'        => 'isys_memory_unit',
                            'p_strClass'        => 'input-dual-large',
                            'p_bInfoIconSpacer' => 0,
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ]
                ]
            ),
            'network_bandwidth'      => array_replace_recursive(
                isys_cmdb_dao_category_pattern::float(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__COMPUTING_RESOURCES__NETWORK_BANDWIDTH',
                        C__PROPERTY__INFO__DESCRIPTION => 'Network bandwidth'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_computing_resources_list__network_bandwidth'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__COMPUTING_RESOURCES__NETWORK_BANDWIDTH',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strClass' => 'input-dual-small'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'convert',
                            ['speed']
                        ],
                        C__PROPERTY__FORMAT__UNIT     => 'network_bandwidth_unit'
                    ]
                ]
            ),
            'network_bandwidth_unit' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__COMPUTING_RESOURCES__NETWORK_BANDWIDTH_UNIT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Network bandwidth unit'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_computing_resources_list__nb__isys_port_speed__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_port_speed',
                            'isys_port_speed__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__COMPUTING_RESOURCES__NETWORK_BANDWIDTH__SPEED',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable'        => 'isys_port_speed',
                            'p_strClass'        => 'input-dual-large',
                            'p_bInfoIconSpacer' => 0,
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ]
                ]
            ),
            'description'            => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_computing_resources_list__description'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CATG__COMPUTING_RESOURCES
                    ]
                ]
            )
        ];
    } // function

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param   array $p_category_data Values of category data to be saved.
     * @param   int   $p_object_id     Current object identifier (from database)
     * @param   int   $p_status        Decision whether category data should be created or just updated.
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
                            C__RECORD_STATUS__NORMAL,
                            $p_category_data['properties']['ram'][C__DATA__VALUE],
                            $p_category_data['properties']['ram_unit'][C__DATA__VALUE],
                            $p_category_data['properties']['cpu'][C__DATA__VALUE],
                            $p_category_data['properties']['cpu_unit'][C__DATA__VALUE],
                            $p_category_data['properties']['disc_space'][C__DATA__VALUE],
                            $p_category_data['properties']['disc_space_unit'][C__DATA__VALUE],
                            $p_category_data['properties']['network_bandwidth'][C__DATA__VALUE],
                            $p_category_data['properties']['network_bandwidth_unit'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE]
                        );
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    if ($p_category_data['data_id'] > 0)
                    {
                        $this->save(
                            $p_category_data['data_id'],
                            C__RECORD_STATUS__NORMAL,
                            $p_category_data['properties']['ram'][C__DATA__VALUE],
                            $p_category_data['properties']['ram_unit'][C__DATA__VALUE],
                            $p_category_data['properties']['cpu'][C__DATA__VALUE],
                            $p_category_data['properties']['cpu_unit'][C__DATA__VALUE],
                            $p_category_data['properties']['disc_space'][C__DATA__VALUE],
                            $p_category_data['properties']['disc_space_unit'][C__DATA__VALUE],
                            $p_category_data['properties']['network_bandwidth'][C__DATA__VALUE],
                            $p_category_data['properties']['network_bandwidth_unit'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE]
                        );

                        return $p_category_data['data_id'];
                    }
                    break;
            } // switch
        }

        return false;
    } // function
} // class
?>