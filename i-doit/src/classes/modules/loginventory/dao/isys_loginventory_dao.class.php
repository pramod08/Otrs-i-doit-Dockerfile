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
use idoit\Component\Helper\Ip;

/**
 * i-doit
 *
 * Loginventory module DAO
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       1.0
 */
class isys_loginventory_dao extends isys_component_dao
{
    const C__LI_CONFIGURATION = 'configuration';

    const C__LI_DATABASES = 'databases';
    /**
     * Assign only known applications
     *
     * @var bool
     */
    protected $m_assign_known_applications = true;
    /**
     * Cache for used daos
     *
     * @var array
     */
    protected $m_cache_dao_sets = [];
    /**
     * Default object type
     *
     * @var int
     */
    protected $m_default_obj_type = C__OBJTYPE__SERVER;
    /**
     * Dialog tables
     *
     * @var array
     */
    protected $m_dialog_cache = [
        'units' => [
            'isys_frequency_unit' => [],
            'isys_depth_unit'     => [],
        ],
        'lists' => [
            'isys_catg_cpu_manufacturer' => [],
            'isys_model_manufacturer'    => [],
            'isys_controller_type'       => [],
            'isys_contact_tag'           => [],
            'isys_stor_type'             => []
        ]
    ];
    /**
     * Logger
     *
     * @var isys_log
     */
    protected $m_log;
    /**
     * Determine if logbook entries should be created or not
     *
     * @var bool
     */
    protected $m_logbook_active = true;
    /**
     * Objects ids mapping
     *
     * @var array
     */
    protected $m_object_ids = [];
    /**
     * PDO driver
     *
     * @var pdo
     */
    protected $m_pdo;
    private $m_pdo_type = 'dblib';

    /**
     * Does nothing
     */
    public function get_data()
    {
        // do nothing
    }

    public function get_properties()
    {
        return $this->m_properties;
    }

    /**
     * Properties for import
     */
    public function build_properties()
    {
        $this->m_properties = [
            'model'                => [
                'manufacturer' => [
                    C__PROPERTY__INFO => 'LC__CMDB__CATG__MODEL_MANUFACTURE',
                    C__PROPERTY__DATA => [
                        'params' => [
                            'title_lang',
                        ]
                    ]
                ],
                'title'        => [
                    C__PROPERTY__INFO => 'LC__CMDB__CATG__MODEL',
                    C__PROPERTY__DATA => [
                        'params' => [
                            'title_lang',
                        ]
                    ],
                ],
                'service_tag'  => [
                    C__PROPERTY__INFO => 'LC__CMDB__CATG__MODEL_SERVICE_TAG',
                    C__PROPERTY__DATA => [
                        'params' => [
                            'value',
                        ]
                    ],
                ],
                'description'  => [
                    C__PROPERTY__INFO => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__DATA => [
                        'params' => [
                            'value'
                        ]
                    ]
                ]
            ],
            'application_specific' => [
                'manufacturer'         => [
                    C__PROPERTY__INFO => 'LC__CMDB__CATG__MANUFACTURE',
                    C__PROPERTY__DATA => [
                        'params' => [
                            'title_lang'
                        ]
                    ]
                ],
                'release'              => [
                    C__PROPERTY__INFO => 'LC__CMDB__CATS__APPLICATION_RELEASE',
                    C__PROPERTY__DATA => [
                        'params' => [
                            'value'
                        ]
                    ]
                ],
                'description_specific' => [
                    C__PROPERTY__INFO => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__DATA => [
                        'params' => [
                            'value'
                        ]
                    ]
                ]
            ],
            'memory'               => [
                'title'    => [
                    C__PROPERTY__INFO => 'LC__CMDB__CATG__TITLE',
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_memory_title__title',
                        'params'                 => [
                            'title_lang'
                        ]
                    ]
                ],
                'type'     => [
                    C__PROPERTY__INFO => 'LC__CMDB_CATG__MEMORY_TYPE',
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_memory_type__title',
                        'params'                 => [
                            'title_lang'
                        ]
                    ]
                ],
                'unit'     => [
                    C__PROPERTY__INFO => 'LC__CATG__MEMORY_UNIT',
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_memory_list__isys_memory_unit__id',
                        'params'                 => [
                            'title_lang'
                        ],
                        'default'                => C__MEMORY_UNIT__MB
                    ]
                ],
                'capacity' => [
                    C__PROPERTY__INFO => 'LC__CMDB__CATS__SAN_CAPACITY_VALUE',
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_memory_list__capacity',
                        'params'                 => [
                            'value',
                            [
                                'convert',
                                'unit',
                                'memory'
                            ]
                        ]
                    ]
                ]
            ],
            'share'                => [
                'title'       => [
                    C__PROPERTY__INFO => 'LC__CMDB__CATG__SHARES__SHARE_NAME',
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_shares_list__title',
                        'params'                 => [
                            'value'
                        ]
                    ]
                ],
                'path'        => [
                    C__PROPERTY__INFO => 'LC__CMDB__CATG__SHARES__LOCAL_PATH',
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_shares_list__path',
                        'params'                 => [
                            'value'
                        ]
                    ]
                ],
                'description' => [
                    C__PROPERTY__INFO => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_shares_list__description',
                        'params'                 => [
                            'value'
                        ]
                    ]
                ]
            ],
            'sound'                => [
                'title' => [
                    C__PROPERTY__INFO => 'LC__UNIVERSAL__TITLE',
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_sound_list__title',
                        'params'                 => [
                            'value'
                        ]
                    ]
                ]
            ],
            'controller'           => [
                'title' => [
                    C__PROPERTY__INFO => 'LC__CMDB__CATG__TITLE',
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_controller_list__title',
                        'params'                 => [
                            'value'
                        ]
                    ]
                ],
                'type'  => [
                    C__PROPERTY__INFO => 'LC__CATG__STORAGE_CONTROLLER_TYPE',
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_controller_type__title',
                        'params'                 => [
                            'title_lang'
                        ]
                    ]
                ]
            ],
            'graphic'              => [
                'title'  => [
                    C__PROPERTY__INFO => 'LC__CMDB__CATG__TITLE',
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_graphic_list__title',
                        'params'                 => [
                            'value'
                        ]
                    ]
                ],
                'unit'   => [
                    C__PROPERTY__INFO => 'LC__CATG__MEMORY_UNIT',
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_graphic_list__isys_memory_unit__id',
                        'params'                 => [
                            'title_lang'
                        ],
                        'default'                => C__MEMORY_UNIT__MB
                    ]
                ],
                'memory' => [
                    C__PROPERTY__INFO => 'LC__CMDB__CATG__MEMORY',
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_graphic_list__memory',
                        'params'                 => [
                            'value',
                            [
                                'convert',
                                'unit',
                                'memory'
                            ]
                        ]
                    ]
                ]
            ],
            'storage'              => [
                'title'    => [
                    C__PROPERTY__INFO => 'LC__CMDB__LOGBOOK__TITLE',
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_stor_list__title',
                        'params'                 => [
                            'value'
                        ]
                    ]
                ],
                'type'     => [
                    C__PROPERTY__INFO   => 'LC__CMDB__CATG__TYPE',
                    C__PROPERTY__DATA   => [
                        C__PROPERTY__DATA__FIELD => 'isys_stor_type__title',
                        'params'                 => [
                            'title_lang'
                        ]
                    ],
                    C__PROPERTY__FORMAT => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'dialog',
                            [
                                'lists',
                                'isys_stor_type'
                            ]
                        ]
                    ]
                ],
                'unit'     => [
                    C__PROPERTY__INFO => 'LC__CATG__MEMORY_UNIT',
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_memory_unit__title',
                        'params'                 => [
                            'title_lang'
                        ],
                        'default'                => C__MEMORY_UNIT__GB
                    ]
                ],
                'capacity' => [
                    C__PROPERTY__INFO => 'LC__CMDB_CATG__MEMORY_CAPACITY',
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_stor_list__capacity',
                        'params'                 => [
                            'value',
                            [
                                'convert',
                                'unit',
                                'memory'
                            ]
                        ]
                    ]
                ]
            ],
            'cpu'                  => [
                'title'        => [
                    C__PROPERTY__INFO => 'LC__CMDB__CATG__CPU_TITLE',
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_cpu_list__title',
                        'params'                 => [
                            'value'
                        ]
                    ],
                ],
                'type'         => [
                    C__PROPERTY__INFO => 'LC__CMDB__CATG__CPU_TYPE',
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_cpu_type__title',
                        'params'                 => [
                            'title_lang'
                        ]
                    ]
                ],
                'manufacturer' => [
                    C__PROPERTY__INFO   => 'LC__CATG__STORAGE_CONTROLLER_MANUFACTURER',
                    C__PROPERTY__DATA   => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_cpu_manufacturer__title',
                        'params'                 => [
                            'title_lang',
                            [
                                'dialog_strstr',
                                'lists',
                                'isys_catg_cpu_manufacturer'
                            ]
                        ]
                    ],
                    C__PROPERTY__FORMAT => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'dialog_strstr',
                            [
                                'lists',
                                'isys_catg_cpu_manufacturer'
                            ]
                        ]
                    ]
                ],
                'unit'         => [
                    C__PROPERTY__INFO => 'LC__CMDB__CATG__CPU_FREQUENCY_UNIT',
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_frequency_unit__title',
                        'params'                 => [
                            'title_lang'
                        ],
                        'default'                => C__FREQUENCY_UNIT__GHZ
                    ]
                ],
                'frequency'    => [
                    C__PROPERTY__INFO => 'LC__CMDB__CATG__FREQUENCY',
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_cpu_list__frequency',
                        'params'                 => [
                            'value',
                            [
                                'convert',
                                'unit',
                                'frequency'
                            ]
                        ]
                    ]
                ],
                'cores'        => [
                    C__PROPERTY__INFO => 'LC__CMDB__CATG__CPU_CORES',
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_cpu_list__cores',
                        'params'                 => [
                            'value'
                        ]
                    ]
                ],
                'description'  => [
                    C__PROPERTY__INFO => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_cpu_list__description',
                        'params'                 => [
                            'value'
                        ]
                    ]
                ]
            ],
            'contact'              => [
                'contact' => [
                    C__PROPERTY__INFO => 'LC__CMDB__CATG__CONTACT',
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_connection__isys_obj__id',
                        'params'                 => [
                            'value'
                        ]
                    ]
                ],
                'role'    => [
                    C__PROPERTY__INFO => 'LC__CMDB__CONTACT_ROLE',
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_contact_tag__title',
                        'params'                 => [
                            'title_lang'
                        ]
                    ]
                ]
            ],
            'ip'                   => [
                'type' => [
                    C__PROPERTY__INFO => 'LC__CMDB__CATS__NET__TYPE'
                ],
                'net'  => [
                    C__PROPERTY__INFO => 'LC__CMDB__NET_ASSIGNMENT'
                ],
                'ip'   => [
                    C__PROPERTY__INFO => 'LC__CATP__IP__ADDRESS'
                ]
            ],
            'interface'            => [
                'title' => [
                    C__PROPERTY__INFO => 'LC__CMDB__CATG__INTERFACE_P_TITLE'
                ]
            ],
            'port'                 => [
                'title'     => [
                    C__PROPERTY__INFO => 'LC__CMDB__CATG__PORT__TITLE'
                ],
                'interface' => [
                    C__PROPERTY__INFO => 'LC__CMDB__CATG__PORT__CON_INTERFACE'
                ],
                'unit'      => [
                    C__PROPERTY__INFO => 'LC__CMDB__CATG__PORT__SPEED_UNIT'
                ],
                'speed'     => [
                    C__PROPERTY__INFO => 'LC__CMDB__CATG__PORT__SPEED'
                ],
                'mac'       => [
                    C__PROPERTY__INFO => 'LC__CMDB__CATG__PORT__MAC'
                ],
                'ip'        => [
                    C__PROPERTY__INFO => 'LC__CATG__IP_ADDRESS'
                ]
            ]
        ];
    }

    /**
     * Gets dialog
     *
     * @param $p_type
     * @param $p_table
     *
     * @return mixed
     */
    public function get_dialog($p_type, $p_table)
    {
        return $this->m_dialog_cache[$p_type][$p_table];
    }

    /**
     * Gets the database connection of the selected server
     *
     * How to connect to the database:
     * First of all the 'pdo' extension has to be enabled. And secondly the pdo driver 'dblib' must be installed.
     * If these requirements are met open the file /etc/freetds/freetds.conf and change tds version to 7.0 or 8.0 under
     * the global config. Also the option mssql.secure_connection in the php.ini file has to be turned on.
     *
     * @param $p_config_id
     *
     * @return isys_component_database_pdo
     */
    public function get_connection($p_config_id)
    {
        if (isset($this->m_pdo))
        {
            return $this->m_pdo;
        } //if

        $this->m_log->debug('Providing access to Loginventory\'s database...');

        $l_db_config = $this->get_loginventory_databases($p_config_id);

        // Connection by odbc driver
        //$db = new PDO('odbc:odbc_loginventory', 'DENNIS-DC\administrator', '');
        /*$this->m_pdo = new isys_component_database_pdo(
            $this->m_pdo_type,
            $l_db_config['isys_loginventory_db__host'],
            $l_db_config['isys_loginventory_db__port'],
            $l_db_config['isys_loginventory_db__user'],
            $l_db_config['isys_loginventory_db__pass'],
            $l_db_config['isys_loginventory_db__schema'],
            'odbc:odbc_loginventory'
        );*/

        //$this->m_pdo = new PDO('dblib:host=192.168.10.210:3108;dbname=loginventory', 'DENNIS-DC\administrator', '');

        try
        {
            $this->m_pdo = new isys_component_database_pdo(
                $this->m_pdo_type,
                $l_db_config['isys_loginventory_db__host'],
                $l_db_config['isys_loginventory_db__port'],
                $l_db_config['isys_loginventory_db__user'],
                $l_db_config['isys_loginventory_db__pass'],
                $l_db_config['isys_loginventory_db__schema']
            );
        }
        catch (Exception $e)
        {
            throw new isys_exception_database('Could not connect to LOGINventory database.');
        }

        return $this->m_pdo;
    } //function

    /**
     * Gets the loginventory configuration
     *
     * @return array
     */
    public function get_configuration()
    {
        $l_sql = 'SELECT * FROM isys_loginventory_config';

        $l_res  = $this->m_db->query($l_sql);
        $l_data = [];
        if ($this->m_db->num_rows($l_res) > 0)
        {
            $l_data = $this->m_db->fetch_row_assoc($l_res);
        }

        return $l_data;
    }

    /**
     * Saves the loginventory configuration
     *
     * @param null $p_data
     *
     * @return bool
     */
    public function save_loginventory_config($p_data = null)
    {

        $l_dao = isys_cmdb_dao::instance($this->m_db);

        if (empty($p_data) && empty($p_id))
        {
            $l_update = 'INSERT INTO isys_loginventory_config SET ' . 'isys_loginventory_config__isys_obj_type__id = NULL, ' . 'isys_loginventory_config__isys_loginventory_db__id = NULL, ' . 'isys_loginventory_config__logbook_active = 0, ' . 'isys_loginventory_config__applications = 0 ';
        }
        else
        {
            $l_update = 'UPDATE isys_loginventory_config SET ' . 'isys_loginventory_config__isys_obj_type__id = ' . $l_dao->convert_sql_id(
                    $p_data['C__LOGINVENTORY__OBJTYPE']
                ) . ', ' . 'isys_loginventory_config__isys_loginventory_db__id = ' . $l_dao->convert_sql_id(
                    $p_data['C__LOGINVENTORY__DEFAULT_DB']
                ) . ', ' . 'isys_loginventory_config__logbook_active = ' . $l_dao->convert_sql_int(
                    $p_data['C__LOGINVENTORY__LOGBOOK']
                ) . ', ' . 'isys_loginventory_config__applications = ' . $l_dao->convert_sql_int($p_data['C__LOGINVENTORY__APPLICATION']);
        }

        if ($l_dao->update($l_update)) return $l_dao->apply_update();
        else return false;
    }

    /**
     * Saves a database configuration
     *
     * @param null $p_db_id
     * @param      $p_posts
     *
     * @return bool
     */
    public function save_loginventory_db($p_db_id = null, $p_posts)
    {
        $l_dao   = isys_cmdb_dao::instance($this->m_db);
        $l_where = '';

        if ($p_db_id > 0)
        {
            // Update
            $l_update = 'UPDATE isys_loginventory_db SET ';
            $l_where  = ' WHERE isys_loginventory_db__id = ' . $l_dao->convert_sql_id($p_db_id);
        }
        else
        {
            // Create
            $l_update = 'INSERT INTO isys_loginventory_db SET ';
        }

        foreach ($p_posts AS $l_key => $l_value)
        {
            if (is_int(strpos($l_key, 'C__MODULE__IMPORT__LOGINVENTORY_')))
            {
                $l_update .= 'isys_loginventory_db__' . strtolower(str_replace('C__MODULE__IMPORT__LOGINVENTORY_', '', $l_key)) . ' = ' . $l_dao->convert_sql_text(
                        $l_value
                    ) . ', ';
            }
        }
        $l_update = rtrim($l_update, ', ') . $l_where;

        if ($l_dao->update($l_update)) return $l_dao->apply_update();
        else return false;
    }

    /**
     * Deletes one or more database configurations
     *
     * @param $p_ids
     *
     * @return mixed
     */
    public function delete_loginventory_db($p_ids)
    {
        if (count($p_ids) > 0)
        {
            $l_delete = 'DELETE FROM isys_loginventory_db WHERE isys_loginventory_db__id IN (';
            foreach ($p_ids AS $l_id)
            {
                $l_delete .= $l_id . ',';
            }
            $l_delete = rtrim($l_delete, ',') . ')';

            return $this->m_db->query($l_delete);
        }
    }

    /**
     * Gets one or all database configurations
     *
     * @param null $p_id
     *
     * @return array
     */
    public function get_loginventory_databases($p_id = null)
    {
        $l_sql = 'SELECT * FROM isys_loginventory_db';

        if (!empty($p_id))
        {
            $l_sql .= ' WHERE isys_loginventory_db__id = \'' . $p_id . '\'';
        }

        $l_res  = $this->m_db->query($l_sql);
        $l_data = [];
        if ($this->m_db->num_rows($l_res) > 0)
        {
            if (!empty($p_id))
            {
                $l_data = $this->m_db->fetch_row_assoc($l_res);
            }
            else
            {
                while ($l_row = $this->m_db->fetch_row_assoc($l_res))
                {
                    $l_data[] = $l_row;
                }
            }
        }

        return $l_data;
    }

    /**
     * Sets cache for dialog tables
     */
    public function set_dialog_cache()
    {
        if (is_array($this->m_dialog_cache['units']))
        {
            foreach ($this->m_dialog_cache['units'] AS $l_unit_table => $l_value)
            {
                $l_sql = 'SELECT * FROM ' . $l_unit_table . ' ORDER BY ' . $l_unit_table . '__id ASC';
                $l_res = $this->m_db->query($l_sql);
                while ($l_row = $this->m_db->fetch_row_assoc($l_res))
                {
                    $this->m_dialog_cache['units'][$l_unit_table][$l_row[$l_unit_table . '__const']]                 = $l_row[$l_unit_table . '__id'];
                    $this->m_dialog_cache['units'][$l_unit_table][strtoupper(_L($l_row[$l_unit_table . '__title']))] = $l_row[$l_unit_table . '__id'];
                }
            }
        }
        if (is_array($this->m_dialog_cache['lists']))
        {
            foreach ($this->m_dialog_cache['lists'] AS $l_unit_table => $l_value)
            {
                $l_sql = 'SELECT * FROM ' . $l_unit_table . ' ORDER BY ' . $l_unit_table . '__id ASC';
                $l_res = $this->m_db->query($l_sql);
                while ($l_row = $this->m_db->fetch_row_assoc($l_res))
                {
                    $this->m_dialog_cache['lists'][$l_unit_table]['_' . $l_row[$l_unit_table . '__id']]              = _L($l_row[$l_unit_table . '__title']);
                    $this->m_dialog_cache['lists'][$l_unit_table][strtoupper($l_row[$l_unit_table . '__title'])]     = $l_row[$l_unit_table . '__id'];
                    $this->m_dialog_cache['lists'][$l_unit_table][strtoupper(_L($l_row[$l_unit_table . '__title']))] = $l_row[$l_unit_table . '__id'];
                }
            }
        }

        return;
    }

    public function set_config($p_config)
    {
        $this->m_default_obj_type          = $p_config['isys_loginventory_config__isys_obj_type__id'];
        $this->m_assign_known_applications = (bool) $p_config['isys_loginventory_config__applications'];
        $this->m_logbook_active            = (bool) $p_config['isys_loginventory_config__logbook_active'];
    }

    public function import($p_db_id, $p_config, $p_li_ids, $p_default_obj_type = null)
    {
        $l_pdo = $this->get_connection($p_db_id);
        $this->set_config($p_config);

        if (!empty($p_default_obj_type))
        {
            $this->m_default_obj_type = $p_default_obj_type;
        }

        $this->build_properties();

        $l_mod_li_data = new isys_loginventory_dao_data($this->m_db, $l_pdo);

        //$l_dao = isys_cmdb_dao::instance($g_comp_database);
        $l_dao                                         = new isys_cmdb_dao($this->m_db);
        $l_dao_logb                                    = new isys_module_logbook();
        $this->m_cache_dao_sets['isys_module_logbook'] = $l_dao_logb;
        $l_mod_event_manager                           = isys_event_manager::getInstance();
        $this->m_cache_dao_sets['isys_event_manager']  = $l_mod_event_manager;
        $l_import_data                                 = [];
        $l_success                                     = true;

        if (is_array($p_li_ids))
        {
            foreach ($p_li_ids AS $l_li_id)
            {

                $l_data = $l_mod_li_data->get_loginventory_objects($l_li_id);

                if (count($l_data) > 0)
                {
                    foreach ($l_data AS $l_object)
                    {
                        unset($l_obj_data);
                        $l_obj_data['object'] = $l_object;

                        $l_active_categories = $l_mod_li_data->get_category_mapping();
                        $l_categories        = explode(';', $l_obj_data['object']['LI_CATEGORY']);

                        foreach ($l_categories AS $l_li_category)
                        {
                            if (array_key_exists($l_li_category, $l_active_categories)) $l_active_categories[$l_li_category] = true;
                        }

                        // Check if object already exists (hostname, ip, mac)
                        $l_obj_id = $l_dao->get_object_by_hostname_serial_mac($l_obj_data['object']['LI_PCNAME'], null, $l_obj_data['object']['LI_MACADDRESS']);

                        $this->m_log->info('Importing Object: ' . $l_obj_data['object']['LI_PCNAME']);
                        if ($l_obj_id)
                        {
                            $this->m_object_ids[$l_obj_data['object']['LA_ID']] = $l_obj_id;
                            $l_obj_type                                         = $l_dao->get_objTypeID($l_obj_id);
                            $this->m_log->info('Object found. Updating object ' . $l_obj_data['object']['LI_PCNAME'] . '.');
                        }
                        else
                        {

                            if ($l_active_categories['PRINTSRVMARKER'])
                            {
                                $l_obj_type = C__OBJTYPE__PRINTER;
                            }
                            /*elseif($l_active_categories['NET'] && $l_active_categories['PCINFO'])
                            {

                            }*/
                            else
                            {
                                $l_obj_type = $this->m_default_obj_type;
                            }
                            $this->m_log->info(
                                'Object not found. Creating new object ' . $l_obj_data['object']['LI_PCNAME'] . ', Type: ' . _L(
                                    $l_dao->get_objtype_name_by_id_as_string($l_obj_type)
                                )
                            );
                            $l_obj_id                                           = $l_dao->insert_new_obj(
                                $l_obj_type,
                                false,
                                $l_obj_data['object']['LI_PCNAME'],
                                null,
                                C__RECORD_STATUS__NORMAL,
                                $l_obj_data['object']['LI_PCNAME'],
                                null,
                                'NOW()'
                            );
                            $this->m_object_ids[$l_obj_data['object']['LA_ID']] = $l_obj_id;
                        }

                        // Contact
                        if ($l_active_categories['LOCALUSER'])
                        {
                            $this->m_log->notice('Global category Contact found. Importing category to i-doit.');
                            $l_obj_data['localuser'] = $l_mod_li_data->prepare_localuser($l_li_id);

                            if (!isset($this->m_cache_dao_sets['isys_cmdb_dao_category_g_contact']))
                            {
                                $this->m_cache_dao_sets['isys_cmdb_dao_category_g_contact'] = isys_cmdb_dao_category_g_contact::instance($this->m_db);
                            }
                            /**
                             * @var $l_cat_dao isys_cmdb_dao_category_g_contact
                             */
                            $l_cat_dao = $this->m_cache_dao_sets['isys_cmdb_dao_category_g_contact'];

                            $l_res        = $l_cat_dao->get_data(null, $l_obj_id);
                            $l_check_data = [];

                            if ($l_res->num_rows() > 0)
                            {
                                $this->m_log->notice('Contact assignments found. Updating category.');
                                while ($l_row = $l_res->get_row())
                                {
                                    $l_check_data[] = [
                                        'data_id' => $l_row['isys_catg_contact_list__id'],
                                        'contact' => $l_row['isys_cats_person_list__title'],
                                        'role'    => $l_row['isys_contact_tag__title']
                                    ];

                                }

                                foreach ($l_obj_data['localuser'] AS $l_key => $l_val)
                                {
                                    // check if contact exists
                                    $l_sql = 'SELECT * FROM isys_cats_person_list WHERE ' . 'isys_cats_person_list__title = ' . $l_dao->convert_sql_text(
                                            $l_val['contact']
                                        ) . ' ' . 'AND isys_cats_person_list__status = ' . C__RECORD_STATUS__NORMAL;

                                    $l_res_person = $l_dao->retrieve($l_sql);
                                    if ($l_res_person->num_rows() > 0)
                                    {

                                        foreach ($l_check_data AS $l_key2 => $l_val2)
                                        {
                                            if ($l_val2['contact'] == $l_val['contact'] && $l_val['role'] == $l_val2['role'])
                                            {
                                                unset($l_check_data[$l_key]);
                                                continue 2;
                                            }
                                        }

                                        $l_row_person           = $l_res_person->get_row();
                                        $l_contact_object_title = $l_val['contact'];
                                        $l_val['contact']       = $l_row_person['isys_cats_person_list__isys_obj__id'];
                                        $l_import_data          = $l_cat_dao->parse_import_array($l_val);

                                        if ($this->m_logbook_active)
                                        {
                                            $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                                                'contact' => ['value' => $l_contact_object_title],
                                                'role'    => ['title_lang' => $l_val['role']]
                                            ];

                                            $l_changes = $l_dao_logb->prepare_changes($l_cat_dao, null, $l_category_values);
                                            if (count($l_changes) > 0) $l_mod_event_manager->triggerCMDBEvent(
                                                'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                                "-modified from LOGINventory-",
                                                $l_obj_id,
                                                $l_obj_type,
                                                'LC__CMDB__CATG__CONTACT',
                                                serialize($l_changes)
                                            );
                                        }

                                        $l_success = $l_cat_dao->sync(
                                            $l_import_data,
                                            $l_obj_id,
                                            ((!empty($l_val['data_id'])) ? isys_import_handler_cmdb::C__UPDATE : isys_import_handler_cmdb::C__CREATE)
                                        );
                                    }
                                }
                            }
                            else
                            {
                                $this->m_log->notice('No contacts found. Creating assigning contacts to object.');
                                // Create
                                foreach ($l_obj_data['localuser'] AS $l_contact_data)
                                {
                                    $l_contact_data['data_id'] = null;

                                    // check if contact exists
                                    $l_sql        = 'SELECT * FROM isys_cats_person_list WHERE ' . 'isys_cats_person_list__title = ' . $l_dao->convert_sql_text(
                                            $l_contact_data['contact']
                                        ) . ' ' . 'AND isys_cats_person_list__status = ' . C__RECORD_STATUS__NORMAL;
                                    $l_res_person = $l_dao->retrieve($l_sql);
                                    if ($l_res_person->num_rows() > 0)
                                    {

                                        $l_row_person              = $l_res_person->get_row();
                                        $l_contact_object_title    = $l_contact_data['contact'];
                                        $l_contact_data['contact'] = $l_row_person['isys_cats_person_list__isys_obj__id'];

                                        if ($this->m_logbook_active)
                                        {
                                            $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                                                'contact' => ['value' => $l_contact_object_title],
                                                'role'    => ['title_lang' => $l_contact_data['role']]
                                            ];

                                            $l_changes = $l_dao_logb->prepare_changes($l_cat_dao, null, $l_category_values);
                                            $l_mod_event_manager->triggerCMDBEvent(
                                                'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                                "-modified from LOGINventory-",
                                                $l_obj_id,
                                                $l_obj_type,
                                                'LC__CMDB__CATG__CONTACT',
                                                serialize($l_changes)
                                            );
                                        }
                                        $l_import_data = $l_cat_dao->parse_import_array($l_contact_data);
                                        $l_success     = $l_cat_dao->sync($l_import_data, $l_obj_id, isys_import_handler_cmdb::C__CREATE);
                                    }
                                }
                            }

                            if ($l_cat_dao)
                            {
                                // Emit category signal (afterCategoryEntrySave).
                                isys_component_signalcollection::get_instance()
                                    ->emit(
                                        "mod.cmdb.afterCategoryEntrySave",
                                        $l_cat_dao,
                                        isset($l_import_data['data_id']) ? $l_import_data['data_id'] : null,
                                        isset($l_success) ? $l_success : true,
                                        $l_obj_id,
                                        isset($l_import_data) ? $l_import_data : [],
                                        isset($l_changes) ? $l_changes : []
                                    );
                            }
                        }

                        // SOFTWARE
                        if ($l_active_categories['SOFTWARE'])
                        {
                            $this->m_log->notice('Global category application (Software) found.');
                            $l_obj_data['application'] = $l_mod_li_data->prepare_application($l_li_id);
                            $this->import_software(
                                $l_obj_id,
                                $l_obj_type,
                                C__OBJTYPE__APPLICATION,
                                'isys_cmdb_dao_category_g_application',
                                'LC__CMDB__CATG__APPLICATION',
                                $l_obj_data['application']
                            );
                        }
                        // Service
                        if ($l_active_categories['SERVICE'])
                        {
                            $this->m_log->notice('Global category application (Service) found.');
                            $l_obj_data['service'] = $l_mod_li_data->prepare_service($l_li_id);
                            $this->import_software(
                                $l_obj_id,
                                $l_obj_type,
                                C__OBJTYPE__SERVICE,
                                'isys_cmdb_dao_category_g_application',
                                'LC__CMDB__CATG__APPLICATION',
                                $l_obj_data['service']
                            );
                        }
                        // Operating System
                        if ($l_active_categories['OS'])
                        {
                            $this->m_log->notice('Global category application (Operating System) found.');
                            $l_obj_data['os'] = $l_mod_li_data->prepare_os($l_li_id);
                            $this->import_software(
                                $l_obj_id,
                                $l_obj_type,
                                C__OBJTYPE__OPERATING_SYSTEM,
                                'isys_cmdb_dao_category_g_application',
                                'LC__CMDB__CATG__APPLICATION',
                                $l_obj_data['os']
                            );
                        }
                        // MODEL
                        if ($l_active_categories['SYSTEM'])
                        {
                            $this->m_log->notice('Global category model found.');
                            $l_obj_data['model'] = $l_mod_li_data->prepare_model($l_li_id);
                            $this->import_single_category(
                                $l_obj_id,
                                $l_obj_type,
                                'isys_cmdb_dao_category_g_model',
                                'model',
                                'isys_catg_model_list',
                                'LC__CMDB__CATG__MODEL',
                                $l_obj_data['model']
                            );
                        }
                        // CPU
                        if ($l_active_categories['CPU'])
                        {
                            $this->m_log->notice('Global category cpu found.');
                            $l_obj_data['cpu'] = $l_mod_li_data->prepare_cpu($l_li_id);
                            $this->import_multi_category(
                                $l_obj_id,
                                $l_obj_type,
                                'isys_cmdb_dao_category_g_cpu',
                                'cpu',
                                'isys_catg_cpu_list',
                                'LC__CMDB__CATG__CPU',
                                $l_obj_data['cpu']
                            );
                        }
                        // Memory
                        if ($l_active_categories['RAM'])
                        {
                            $this->m_log->notice('Global category memory found.');
                            $l_obj_data['memory'] = $l_mod_li_data->prepare_memory($l_li_id);
                            $this->import_multi_category(
                                $l_obj_id,
                                $l_obj_type,
                                'isys_cmdb_dao_category_g_memory',
                                'memory',
                                'isys_catg_memory_list',
                                'LC__CMDB__CATG__MEMORY',
                                $l_obj_data['memory']
                            );
                        }
                        // Share
                        if ($l_active_categories['SHARE'])
                        {
                            $this->m_log->notice('Global category share found.');
                            $l_obj_data['share'] = $l_mod_li_data->prepare_share($l_li_id);
                            $this->import_multi_category(
                                $l_obj_id,
                                $l_obj_type,
                                'isys_cmdb_dao_category_g_shares',
                                'share',
                                'isys_catg_shares_list',
                                'LC__CMDB__CATG__SHARES',
                                $l_obj_data['share']
                            );
                        }
                        // Sound
                        if ($l_active_categories['SOUND'])
                        {
                            $this->m_log->notice('Global category sound card found.');
                            $l_obj_data['sound'] = $l_mod_li_data->prepare_sound($l_li_id);
                            $this->import_multi_category(
                                $l_obj_id,
                                $l_obj_type,
                                'isys_cmdb_dao_category_g_sound',
                                'sound',
                                'isys_catg_sound_list',
                                'LC__CMDB__CATG__SOUND',
                                $l_obj_data['sound']
                            );
                        }
                        // Controller
                        if ($l_active_categories['CONTROLLER'])
                        {
                            $this->m_log->notice('Global category controller found.');
                            $l_obj_data['controller'] = $l_mod_li_data->prepare_controller($l_li_id);
                            $this->import_multi_category(
                                $l_obj_id,
                                $l_obj_type,
                                'isys_cmdb_dao_category_g_controller',
                                'controller',
                                'isys_catg_controller_list',
                                'LC__STORAGE_CONTROLLER',
                                $l_obj_data['controller']
                            );
                        }
                        // Graphic
                        if ($l_active_categories['VID'])
                        {
                            $this->m_log->notice('Global category graphic card found.');
                            $l_obj_data['graphic'] = $l_mod_li_data->prepare_graphic($l_li_id);
                            $this->import_multi_category(
                                $l_obj_id,
                                $l_obj_type,
                                'isys_cmdb_dao_category_g_graphic',
                                'graphic',
                                'isys_catg_graphic_list',
                                'LC__CMDB__CATG__GRAPHIC',
                                $l_obj_data['graphic']
                            );
                        }
                        // Storage
                        if ($l_active_categories['DRIVE'])
                        {
                            $this->m_log->notice('Global category storage found.');
                            $l_obj_data['storage'] = $l_mod_li_data->prepare_storage($l_li_id);
                            $this->import_multi_category(
                                $l_obj_id,
                                $l_obj_type,
                                'isys_cmdb_dao_category_g_stor',
                                'storage',
                                'isys_catg_stor_list',
                                'LC__STORAGE_DEVICE',
                                $l_obj_data['storage']
                            );
                        }
                        // NET
                        if ($l_active_categories['NET'])
                        {
                            $this->m_log->notice('Global category interface/ip/port found.');
                            $l_obj_data['net'] = $l_mod_li_data->prepare_net($l_li_id);

                            if (!isset($this->m_cache_dao_sets['isys_cmdb_dao_category_g_network_interface']))
                            {
                                $l_cat_dao_iface = $this->m_cache_dao_sets['isys_cmdb_dao_category_g_network_interface'] = isys_cmdb_dao_category_g_network_interface::instance(
                                    $this->m_db
                                );
                            }
                            elseif (empty($l_cat_dao_iface))
                            {
                                $l_cat_dao_iface = $this->m_cache_dao_sets['isys_cmdb_dao_category_g_network_interface'];
                            }
                            if (!isset($this->m_cache_dao_sets['isys_cmdb_dao_category_g_network_port']))
                            {
                                $l_cat_dao_port = $this->m_cache_dao_sets['isys_cmdb_dao_category_g_network_port'] = isys_cmdb_dao_category_g_network_port::instance(
                                    $this->m_db
                                );
                            }
                            elseif (empty($l_cat_dao_port))
                            {
                                $l_cat_dao_port = $this->m_cache_dao_sets['isys_cmdb_dao_category_g_network_port'];
                            }
                            if (!isset($this->m_cache_dao_sets['isys_cmdb_dao_category_g_ip']))
                            {
                                $l_cat_dao_ip = $this->m_cache_dao_sets['isys_cmdb_dao_category_g_ip'] = isys_cmdb_dao_category_g_ip::instance($this->m_db);
                            }
                            elseif (empty($l_cat_dao_ip))
                            {
                                $l_cat_dao_ip = $this->m_cache_dao_sets['isys_cmdb_dao_category_g_ip'];
                            }
                            if (!isset($this->m_cache_dao_sets['isys_cmdb_dao_category_s_net']))
                            {
                                $l_cat_dao_net = $this->m_cache_dao_sets['isys_cmdb_dao_category_s_net'] = isys_cmdb_dao_category_s_net::instance($this->m_db);
                            }
                            elseif (empty($l_cat_dao_net))
                            {
                                $l_cat_dao_net = $this->m_cache_dao_sets['isys_cmdb_dao_category_s_net'];
                            }

                            $l_res_ip           = $l_cat_dao_ip->get_data(null, $l_obj_id);
                            $l_check_data['ip'] = [];
                            while ($l_row_ip = $l_res_ip->get_row())
                            {
                                $l_check_data['ip'][] = [
                                    'data_id' => $l_row_ip['isys_catg_ip_list__id'],
                                    'title'   => $l_row_ip['isys_cats_net_ip_addresses_list__title']
                                ];
                            }

                            $l_res_port           = $l_cat_dao_port->get_data(null, $l_obj_id);
                            $l_check_data['port'] = [];
                            while ($l_row_port = $l_res_port->get_row())
                            {
                                $l_check_data['port'][] = [
                                    'data_id'    => $l_row_port['isys_catg_port_list__id'],
                                    'title'      => $l_row_port['isys_catg_port_list__title'],
                                    'mac'        => $l_row_port['isys_catg_port_list__mac'],
                                    'speed_type' => $l_row_port['isys_catg_port_list__isys_port_speed__id'],
                                    'speed'      => $l_row_port['isys_catg_port_list__port_speed_value']
                                ];
                            }

                            foreach ($l_obj_data['net'] AS $l_net_data)
                            {
                                $l_connect_interface = false;
                                $l_connect_ip        = false;
                                $l_iface_title       = null;
                                $l_iface_id          = null;

                                if (isset($l_net_data['interface']))
                                {

                                    $l_res_iface = $l_cat_dao_iface->get_data(
                                        null,
                                        $l_obj_id,
                                        ' AND isys_catg_netp_list__title = ' . $l_cat_dao_iface->convert_sql_text($l_net_data['interface']['title'])
                                    );
                                    if ($l_res_iface->num_rows() > 0)
                                    {
                                        // if exists do nothing take the data id for referencing
                                        $l_row_iface   = $l_res_iface->get_row();
                                        $l_iface_title = $l_row_iface['isys_catg_netp_list__title'];
                                        $l_iface_id    = $l_row_iface['isys_catg_netp_list__id'];
                                    }
                                    else
                                    {
                                        // just create it
                                        $l_iface_data['data_id'] = null;
                                        $l_iface_data['title']   = $l_net_data['interface']['title'];
                                        $l_iface_title           = $l_iface_data['title'];
                                        // check if contact exists

                                        if ($this->m_logbook_active)
                                        {
                                            $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                                                'title' => ['value' => $l_net_data['interface']['title']],
                                            ];

                                            $l_changes = $l_dao_logb->prepare_changes($l_cat_dao_iface, null, $l_category_values);
                                            $l_mod_event_manager->triggerCMDBEvent(
                                                'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                                "-modified from LOGINventory-",
                                                $l_obj_id,
                                                $l_obj_type,
                                                'LC__CMDB__CATG__NETWORK_TREE_CONFIG_INTERFACE',
                                                serialize($l_changes)
                                            );
                                        }
                                        $l_import_data = $l_cat_dao_iface->parse_import_array($l_iface_data);
                                        $l_iface_id    = $l_cat_dao_iface->sync($l_import_data, $l_obj_id, isys_import_handler_cmdb::C__CREATE);

                                        // Emit category signal (afterCategoryEntrySave).
                                        isys_component_signalcollection::get_instance()
                                            ->emit(
                                                "mod.cmdb.afterCategoryEntrySave",
                                                $l_cat_dao_iface,
                                                isset($l_import_data['data_id']) ? $l_import_data['data_id'] : null,
                                                isset($l_iface_id) ? $l_iface_id : true,
                                                $l_obj_id,
                                                isset($l_import_data) ? $l_import_data : [],
                                                isset($l_changes) ? $l_changes : []
                                            );
                                    }
                                    $l_connect_interface = true;
                                }

                                if (isset($l_net_data['ip']))
                                {

                                    // Check gateway
                                    $l_res_gw  = $l_cat_dao_ip->get_data(
                                        null,
                                        null,
                                        'AND (ipv4.isys_cats_net_ip_addresses_list__title = ' . $l_cat_dao_ip->convert_sql_text(
                                            $l_net_data['ip']['gateway']
                                        ) . ' OR ipv6.isys_cats_net_ip_addresses_list__title = ' . $l_cat_dao_ip->convert_sql_text($l_net_data['ip']['gateway']) . ') '
                                    );
                                    $l_gw_id   = null;
                                    $l_dns_ids = null;

                                    if ($l_res_gw->num_rows() > 0)
                                    {
                                        $l_row_gw = $l_res_gw->get_row();
                                        $l_gw_id  = $l_row_gw['isys_catg_ip_list__id'];
                                    }
                                    // Check DNS Server
                                    /*if(count($l_net_data['ip']['dnsserver']) > 0){
                                        foreach($l_net_data['ip']['dnsserver'] AS $l_dnsserver){
                                            $l_res_dns = $l_cat_dao_ip->get_data(null, null, 'AND (ipv4.isys_cats_net_ip_addresses_list__title = '.$l_cat_dao_ip->convert_sql_text($l_dnsserver).' OR ipv6.isys_cats_net_ip_addresses_list__title = '.$l_cat_dao_ip->convert_sql_text($l_dnsserver).') ');
                                            if($l_res_dns->num_rows() > 0){
                                                $l_row_dns = $l_res_dns->get_row();
                                                $l_dns_ids[$l_dnsserver] = $l_row_dns['isys_catg_ip_list__id'];
                                            } else{
                                                $l_dns_ids[$l_dnsserver] = null;
                                            }
                                        }
                                    }*/

                                    foreach ($l_net_data['ip']['title'] AS $l_key => $l_ip)
                                    {

                                        $l_res_ip = $l_cat_dao_ip->get_ips_by_obj_id(
                                            $l_obj_id,
                                            false,
                                            false,
                                            false,
                                            'AND isys_cats_net_ip_addresses_list__title = ' . $l_cat_dao_ip->convert_sql_text($l_ip)
                                        );

                                        if (Ip::validate_ip($l_ip))
                                        {
                                            // IPV4
                                            $l_net_ip      = Ip::validate_net_ip($l_ip, $l_net_data['ip']['subnetmask'][$l_key], null, true);
                                            $l_net_mask    = $l_net_data['ip']['subnetmask'][$l_key];
                                            $l_cidr_suffix = Ip::calc_cidr_suffix($l_net_mask);
                                            $l_range       = Ip::calc_ip_range($l_net_ip, $l_net_mask);
                                            $l_net_type    = C__CATS_NET_TYPE__IPV4;
                                        }
                                        elseif (Ip::validate_ipv6($l_ip))
                                        {
                                            // IPV6
                                            $l_net_ip      = Ip::validate_net_ipv6($l_ip, $l_net_data['ip']['subnetmask'][$l_key]);
                                            $l_net_mask    = Ip::calc_subnet_by_cidr_suffix_ipv6($l_net_data['ip']['subnetmask'][$l_key]);
                                            $l_cidr_suffix = $l_net_data['ip']['subnetmask'][$l_key];
                                            $l_range       = Ip::calc_ip_range_ipv6($l_net_ip, $l_net_data['ip']['subnetmask'][$l_key]);
                                            $l_net_type    = C__CATS_NET_TYPE__IPV6;
                                        }

                                        if ($l_res_ip->num_rows() > 0)
                                        {
                                            // check and update or create
                                            $l_row_ip = $l_res_ip->get_row();
                                            foreach ($l_check_data['ip'] AS $l_ip_key => $l_check_data_ip)
                                            {
                                                if ($l_check_data_ip['title'] == $l_row_ip['isys_cats_net_ip_addresses_list__title'])
                                                {
                                                    unset($l_check_data['ip'][$l_ip_key]);
                                                }
                                            }
                                            $l_ip_id_arr[] = $l_row_ip['isys_catg_ip_list__id'];
                                        }
                                        else
                                        {
                                            // just create it
                                            $l_res_net = $l_cat_dao_net->get_data(
                                                null,
                                                null,
                                                ' AND isys_cats_net_list__address = ' . $l_cat_dao_net->convert_sql_text(
                                                    $l_net_ip
                                                ) . ' AND isys_cats_net_list__isys_net_type__id = ' . $l_cat_dao_net->convert_sql_id($l_net_type)
                                            );
                                            if ($l_res_net->num_rows() > 0)
                                            {
                                                $l_row_net     = $l_res_net->get_row();
                                                $l_net_id      = $l_row_net['isys_cats_net_list__isys_obj__id'];
                                                $l_net_data_id = $l_row_net['isys_cats_net_list__id'];
                                            }
                                            else
                                            {
                                                $l_net_id      = $l_cat_dao_net->insert_new_obj(C__OBJTYPE__LAYER3_NET, false, $l_net_ip, null, C__RECORD_STATUS__NORMAL);
                                                $l_net_data_id = $l_cat_dao_net->create(
                                                    $l_net_id,
                                                    C__RECORD_STATUS__NORMAL,
                                                    null,
                                                    $l_net_type,
                                                    $l_net_ip,
                                                    $l_net_mask,
                                                    null,
                                                    null,
                                                    $l_range['from'],
                                                    $l_range['to'],
                                                    null,
                                                    null,
                                                    '',
                                                    $l_cidr_suffix,
                                                    null,
                                                    null,
                                                    0
                                                );
                                            }
                                            if (!empty($l_net_data['ip']['gateway']))
                                            {
                                                if (empty($l_gw_id))
                                                {
                                                    // create gateway and update net
                                                    $l_gw_obj_id = $l_cat_dao_net->insert_new_obj(
                                                        C__OBJTYPE__ROUTER,
                                                        false,
                                                        $l_net_data['ip']['gateway'],
                                                        null,
                                                        C__RECORD_STATUS__NORMAL
                                                    );
                                                    if (Ip::validate_ip($l_net_data['ip']['gateway']))
                                                    {
                                                        $l_gw_net_type = C__CATS_NET_TYPE__IPV4;
                                                    }
                                                    elseif (Ip::validate_ipv6($l_net_data['ip']['gateway']))
                                                    {
                                                        $l_gw_net_type = C__CATS_NET_TYPE__IPV6;
                                                    }
                                                    $l_gw_id = $l_cat_dao_ip->create(
                                                        $l_gw_obj_id,
                                                        null,
                                                        null,
                                                        $l_net_data['ip']['gateway'],
                                                        true,
                                                        null,
                                                        null,
                                                        null,
                                                        true,
                                                        $l_gw_net_type,
                                                        $l_net_id,
                                                        ''
                                                    );
                                                }
                                                else
                                                {
                                                    // Update net
                                                    $l_update = 'UPDATE isys_cats_net_list SET isys_cats_net_list__isys_catg_ip_list__id = ' . $l_cat_dao_net->convert_sql_id(
                                                            $l_gw_id
                                                        ) . ' ' . 'WHERE isys_cats_net_list__id = ' . $l_cat_dao_net->convert_sql_id($l_net_data_id);
                                                    $l_cat_dao_net->update($l_update) && $l_cat_dao_net->apply_update();
                                                }
                                            }
                                            /*if(!empty($l_net_data['ip']['dnsserver'])){
                                                $l_cat_dao_net->clear_dns_server_attachments($l_net_data_id);
                                                foreach($l_dns_ids AS $l_dns_ip => $l_dns_id){
                                                    if(empty($l_dns_id)){
                                                        // Create new dns server
                                                    } else{
                                                        // Add dns server
                                                    }
                                                }
                                            }*/
                                            // ($p_object_id, $p_hostname, $p_ip_assignment, $p_address, $p_primary, $p_gw, $p_dns_server, $p_dns_domain, $p_active, $p_net_type, $p_net_connection, $p_description, $p_status = C__RECORD_STATUS__NORMAL, $p_port_assignment = null, $p_log_port_assignment = null, $p_ipv6_scope = null, $p_ip6_assignment = null)
                                            $l_ip_id_arr[] = $l_cat_dao_ip->create(
                                                $l_obj_id,
                                                null,
                                                null,
                                                $l_ip,
                                                (($l_net_data['ip']['index'] == 1) ? true : false),
                                                null,
                                                null,
                                                null,
                                                true,
                                                $l_net_type,
                                                $l_net_id,
                                                ''
                                            );
                                        }
                                    }
                                    $l_connect_ip = true;
                                }

                                // Ports
                                if (isset($l_net_data['port']))
                                {
                                    $l_port_data = $l_net_data['port'];
                                    $l_port_id   = null;

                                    foreach ($l_check_data['port'] AS $l_port_key => $l_check_data_port)
                                    {
                                        if ($l_check_data_port['title'] == $l_port_data['title'] && $l_check_data_port['mac'] == $l_port_data['mac'] && $l_check_data_port['speed_type'] == $l_port_data['speed_type'] && $l_check_data_port['speed'] == isys_convert::speed(
                                                $l_port_data['speed'],
                                                $l_port_data['speed_type']
                                            )
                                        )
                                        {
                                            unset($l_check_data['port'][$l_port_key]);
                                            $l_port_id = $l_check_data_port['data_id'];
                                            break;
                                        }
                                    }

                                    if ($l_port_id > 0)
                                    {
                                        // UPDATE

                                        if ($l_connect_interface)
                                        {
                                            $l_cat_dao_port->attach_interface($l_port_id, $l_iface_id);
                                        }

                                        // Assign interface and hostaddress
                                        if ($l_connect_ip)
                                        {
                                            $l_cat_dao_port->clear_ip_attachments($l_port_id);
                                            if (is_array($l_ip_id_arr) && count($l_ip_id_arr) > 0)
                                            {
                                                foreach ($l_ip_id_arr AS $l_ip_id)
                                                {
                                                    $l_cat_dao_port->attach_ip($l_port_id, $l_ip_id);
                                                }
                                            }
                                        }
                                    }
                                    else
                                    {
                                        // CREATE
                                        $l_port_data['data_id']   = null;
                                        $l_port_data['interface'] = $l_iface_id;

                                        if ($this->m_logbook_active)
                                        {
                                            $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = [
                                                'title'      => ['value' => $l_port_data['title']],
                                                'mac'        => ['value' => $l_port_data['mac']],
                                                'speed_type' => ['title_lang' => 'Mbit/s'],
                                                'speed'      => [
                                                    'value'     => $l_port_data['speed'],
                                                    'interface' => ['title_lang' => $l_iface_title]
                                                ]
                                            ];

                                            $l_changes = $l_dao_logb->prepare_changes($l_cat_dao_port, null, $l_category_values);
                                            $l_mod_event_manager->triggerCMDBEvent(
                                                'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                                "-modified from LOGINventory-",
                                                $l_obj_id,
                                                $l_obj_type,
                                                'LC__CMDB__CATG__NETWORK_TREE_CONFIG_PORT',
                                                serialize($l_changes)
                                            );
                                        }

                                        $l_import_data = $l_cat_dao_port->parse_import_array($l_port_data);
                                        $l_port_id     = $l_cat_dao_port->sync($l_import_data, $l_obj_id, isys_import_handler_cmdb::C__CREATE);

                                        // Emit category signal (afterCategoryEntrySave).
                                        isys_component_signalcollection::get_instance()
                                            ->emit(
                                                "mod.cmdb.afterCategoryEntrySave",
                                                $l_cat_dao_port,
                                                isset($l_import_data['data_id']) ? $l_import_data['data_id'] : null,
                                                isset($l_success) ? $l_success : true,
                                                $l_obj_id,
                                                isset($l_import_data) ? $l_import_data : [],
                                                isset($l_changes) ? $l_changes : []
                                            );

                                        if ($l_connect_ip)
                                        {
                                            if (method_exists($l_cat_dao_port, 'clear_ip_attachments'))
                                            {
                                                $l_cat_dao_port->clear_ip_attachments($l_port_id);
                                                if (isset($l_ip_id_arr) && is_array($l_ip_id_arr) && count($l_ip_id_arr) > 0)
                                                {
                                                    foreach ($l_ip_id_arr AS $l_ip_id)
                                                    {
                                                        $l_cat_dao_port->attach_ip($l_port_id, $l_ip_id);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            if (count($l_check_data['ip']) > 0)
                            {
                                $this->delete_entries_from_category($l_check_data['ip'], $l_cat_dao_ip, 'isys_catg_ip_list', $l_obj_id, $l_obj_type, 'LC__CATG__IP_ADDRESS');
                            }

                            if (count($l_check_data['port']) > 0)
                            {
                                $this->delete_entries_from_category(
                                    $l_check_data['port'],
                                    $l_cat_dao_port,
                                    'isys_catg_port_list',
                                    $l_obj_id,
                                    $l_obj_type,
                                    'LC__CATG__IP_ADDRESS'
                                );
                            }
                        }

                        $this->m_log->info('Object ' . $l_obj_data['object']['LI_PCNAME'] . ' successfully imported.');
                    }
                }
            }
        }

        return true;
    }

    /**
     * Imports multi value categories
     *
     * @param $p_obj_id
     * @param $p_obj_type
     * @param $p_class
     * @param $p_type
     * @param $p_table
     * @param $p_category_title
     * @param $p_data
     */
    private function import_multi_category($p_obj_id, $p_obj_type, $p_class, $p_type, $p_table, $p_category_title, $p_data)
    {

        if (!isset($this->m_cache_dao_sets[$p_class]))
        {
            $this->m_cache_dao_sets[$p_class] = call_user_func(
                [
                    $p_class,
                    'instance'
                ],
                $this->m_db
            );
        }
        $l_cat_dao = $this->m_cache_dao_sets[$p_class];

        $l_res        = $l_cat_dao->get_data(null, $p_obj_id);
        $l_mem_amount = $l_res->num_rows();
        if ($l_mem_amount > 0)
        {

            // Get data in i-doit
            $l_counter = 0;
            while ($l_rowMemory = $l_res->get_row())
            {
                $l_check_data[] = [
                    'data_id' => $l_rowMemory[$p_table . '__id'],
                ];
                foreach ($this->m_properties[$p_type] AS $l_property => $l_prop_value)
                {
                    $l_check_data[$l_counter][$l_property] = $l_rowMemory[$l_prop_value[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]];
                }
                //$l_check_data[$l_counter]['description'] = $l_rowMemory[$p_table.'__description'];
                $l_counter++;
            }

            foreach ($p_data AS $l_rowMemory)
            {
                $l_check = [];
                // Check data from ocs with data from i-doit

                foreach ($l_check_data AS $l_key => $l_val)
                {
                    foreach ($this->m_properties[$p_type] AS $l_property => $l_prop_value)
                    {
                        $l_arr = [];
                        if (isset($l_prop_value[C__PROPERTY__DATA]['params'][1]))
                        {
                            if ($l_prop_value[C__PROPERTY__DATA]['params'][1][0] == 'convert')
                            {
                                $l_arr[] = $p_type;    // Property
                                $l_arr[] = $l_prop_value[C__PROPERTY__DATA]['params'][1][1]; // property field
                                $l_arr[] = $l_prop_value[C__PROPERTY__DATA]['params'][1][2]; // convert method
                            }
                            else
                            {
                                $l_arr = $l_prop_value[C__PROPERTY__DATA]['params'][1];
                                array_shift($l_arr);
                            }
                            $l_arr[] = $l_rowMemory[$l_property];

                            $l_check_prop = call_user_func_array(
                                [
                                    $this,
                                    $l_prop_value[C__PROPERTY__DATA]['params'][1][0]
                                ],
                                $l_arr
                            );
                        }
                        else
                        {
                            $l_check_prop = $l_rowMemory[$l_property];
                        }

                        if ($l_val[$l_property] != $l_check_prop && $l_property != 'description')
                        {
                            $l_check[$l_key][$l_property] = false;
                        }
                        elseif ($l_val[$l_property] == $l_check_prop)
                        {
                            $l_check[$l_key][$l_property] = true;
                        }
                    }
                }

                foreach ($l_check AS $l_key => $l_chk)
                {
                    $l_max = count($l_chk);
                    foreach ($l_chk AS $l_chk_import)
                    {
                        if ($l_chk_import) $l_max--;
                    }
                    if ($l_max == 0)
                    {
                        // found
                        unset($l_check_data[$l_key]);
                        continue 2;
                    }
                }

                foreach ($this->m_properties[$p_type] AS $l_property => $l_prop_value)
                {
                    if (isset($l_prop_value[C__PROPERTY__DATA]['default']))
                    {
                        $l_data[$l_property] = $l_prop_value[C__PROPERTY__DATA]['default'];
                    }
                    else
                    {
                        $l_arr = [];
                        if (isset($l_prop_value[C__PROPERTY__DATA]['params'][1]) && $l_prop_value[C__PROPERTY__DATA]['params'][1][0] != 'convert')
                        {
                            $l_arr = $l_prop_value[C__PROPERTY__DATA]['params'][1];
                            array_shift($l_arr);
                            $l_arr[] = $l_rowMemory[$l_property];

                            $l_data[$l_property] = call_user_func_array(
                                [
                                    $this,
                                    $l_prop_value[C__PROPERTY__DATA]['params'][1][0]
                                ],
                                $l_arr
                            );
                        }
                        else
                        {
                            $l_data[$l_property] = $l_rowMemory[$l_property];
                        }
                    }
                }

                if ($this->m_logbook_active)
                {
                    foreach ($this->m_properties[$p_type] AS $l_property => $l_prop_value)
                    {
                        if (isset($l_prop_value[C__PROPERTY__FORMAT]))
                        {
                            $l_method   = $l_prop_value[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0];
                            $l_params   = $l_prop_value[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1];
                            $l_params[] = $l_rowMemory[$l_property];

                            $l_category_values[isys_import_handler_cmdb::C__PROPERTIES][$l_property] = [
                                $l_prop_value[C__PROPERTY__DATA]['params'][0] => call_user_func_array(
                                    [
                                        $this,
                                        $l_method
                                    ],
                                    $l_params
                                )
                            ];
                        }
                        else
                        {
                            $l_category_values[isys_import_handler_cmdb::C__PROPERTIES][$l_property] = [
                                $l_prop_value[C__PROPERTY__DATA]['params'][0] => $l_rowMemory[$l_property]
                            ];
                        }
                    }

                    $l_changes = $this->m_cache_dao_sets['isys_module_logbook']->prepare_changes($l_cat_dao, null, $l_category_values);
                    if (count($l_changes) > 0) $this->m_cache_dao_sets['isys_event_manager']->triggerCMDBEvent(
                        'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                        "-modified from LOGINventory-",
                        $p_obj_id,
                        $p_obj_type,
                        $p_category_title,
                        serialize($l_changes)
                    );
                }

                $l_cat_dao->sync($l_cat_dao->parse_import_array($l_data), $p_obj_id, isys_import_handler_cmdb::C__CREATE);
            }
            // Delete entries
            if (count($l_check_data) > 0) $this->delete_entries_from_category($l_check_data, $l_cat_dao, $p_table, $p_obj_id, $p_obj_type, $p_category_title);

        }
        else
        {
            // Create entries
            foreach ($p_data AS $l_rowMemory)
            {
                foreach ($this->m_properties[$p_type] AS $l_property => $l_prop_value)
                {
                    if (isset($l_prop_value[C__PROPERTY__DATA]['default']))
                    {
                        $l_data[$l_property] = $l_prop_value[C__PROPERTY__DATA]['default'];
                    }
                    elseif (isset($l_prop_value[C__PROPERTY__FORMAT]))
                    {
                        $l_method   = $l_prop_value[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0];
                        $l_params   = $l_prop_value[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1];
                        $l_params[] = $l_rowMemory[$l_property];

                        $l_data[$l_property] = call_user_func_array(
                            [
                                $this,
                                $l_method
                            ],
                            $l_params
                        );
                    }
                    else
                    {
                        $l_data[$l_property] = $l_rowMemory[$l_property];
                    }
                }

                if ($this->m_logbook_active)
                {
                    foreach ($this->m_properties[$p_type] AS $l_property => $l_prop_value)
                    {
                        if (isset($l_prop_value[C__PROPERTY__FORMAT]))
                        {
                            $l_method   = $l_prop_value[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0];
                            $l_params   = $l_prop_value[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1];
                            $l_params[] = $l_rowMemory[$l_property];

                            $l_category_values[isys_import_handler_cmdb::C__PROPERTIES][$l_property] = [
                                $l_prop_value[C__PROPERTY__DATA]['params'][0] => call_user_func_array(
                                    [
                                        $this,
                                        $l_method
                                    ],
                                    $l_params
                                )
                            ];
                        }
                        else
                        {
                            $l_category_values[isys_import_handler_cmdb::C__PROPERTIES][$l_property] = [
                                $l_prop_value[C__PROPERTY__DATA]['params'][0] => $l_rowMemory[$l_property]
                            ];
                        }
                    }

                    $l_changes = $this->m_cache_dao_sets['isys_module_logbook']->prepare_changes($l_cat_dao, null, $l_category_values);
                    if (count($l_changes) > 0) $this->m_cache_dao_sets['isys_event_manager']->triggerCMDBEvent(
                        'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                        "-modified from LOGINventory-",
                        $p_obj_id,
                        $p_obj_type,
                        $p_category_title,
                        serialize($l_changes)
                    );
                }

                $l_cat_dao->sync($l_cat_dao->parse_import_array($l_data), $p_obj_id, isys_import_handler_cmdb::C__CREATE);
            }
        }
    }

    /**
     * Deletes entries from category
     *
     * @param array $p_arr
     * @param       $p_dao
     * @param       $p_table
     * @param       $p_obj_id
     * @param       $p_obj_type
     * @param       $p_category_title
     * @param       $p_logb_active
     *
     * @return null
     */
    private function delete_entries_from_category(array $p_arr, $p_dao, $p_table, $p_obj_id, $p_obj_type, $p_category_title)
    {

        $l_mod_event_manager = isys_event_manager::getInstance();

        if (empty($p_table)) return null;

        foreach ($p_arr AS $l_val)
        {
            $p_dao->delete_entry($l_val['data_id'], $p_table);

            if ($this->m_logbook_active) $l_mod_event_manager->triggerCMDBEvent(
                'C__LOGBOOK_EVENT__CATEGORY_PURGED',
                "-modified from LOGINventory-",
                $p_obj_id,
                $p_obj_type,
                $p_category_title,
                null
            );
        }
    }

    /**
     * Imports software object types
     *
     * @param $p_obj_id
     * @param $p_obj_type
     * @param $p_obj_type_app
     * @param $p_class
     * @param $p_category_title
     * @param $p_data
     */
    private function import_software($p_obj_id, $p_obj_type, $p_obj_type_app, $p_class, $p_category_title, $p_data)
    {

        if (!isset($this->m_cache_dao_sets[$p_class]))
        {
            $this->m_cache_dao_sets[$p_class] = call_user_func(
                [
                    $p_class,
                    'instance'
                ],
                $this->m_db
            );
        }
        $l_cat_dao = $this->m_cache_dao_sets[$p_class];

        $l_check_data      = [];
        $l_res_app         = $l_cat_dao->retrieve(
            "SELECT isys_obj__title, isys_catg_application_list__id, isys_obj__id FROM isys_catg_application_list " . "INNER JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id " . "INNER JOIN isys_obj ON isys_connection__isys_obj__id = isys_obj__id " . "INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id " . "WHERE isys_catg_application_list__isys_obj__id = '" . $p_obj_id . "' " . "AND isys_obj_type__id = " . $l_cat_dao->convert_sql_id(
                $p_obj_type_app
            )
        );
        $l_double_assigned = [];
        while ($l_rowApp = $l_res_app->get_row())
        {
            if (isset($l_check_data[$l_rowApp['isys_obj__id']])) $l_double_assigned[] = ['data_id' => $l_rowApp['isys_catg_application_list__id']];

            $l_check_data[$l_rowApp['isys_obj__id']] = [
                'data_id' => $l_rowApp['isys_catg_application_list__id'],
            ];
        }

        $l_swIDs = [];
        // Assign Application
        foreach ($p_data AS $l_row)
        {
            $l_swID = false;

            // Application
            if ($p_obj_type_app == C__OBJTYPE__APPLICATION || $p_obj_type_app == C__OBJTYPE__OPERATING_SYSTEM)
            {
                $l_sql_app = "SELECT isys_obj__id, isys_cats_application_list.* FROM isys_obj " . "INNER JOIN isys_cats_application_list ON isys_obj__id = isys_cats_application_list__isys_obj__id " . "WHERE isys_obj__title = '" . $l_row["application"] . "' " . "AND isys_cats_application_list__release = " . $l_cat_dao->convert_sql_text(
                        $l_row['release']
                    ) . " " . "AND isys_obj__isys_obj_type__id = '" . $p_obj_type_app . "'";
            }
            elseif ($p_obj_type_app == C__OBJTYPE__SERVICE)
            {
                $l_sql_app = "SELECT isys_obj__id, isys_cats_application_list.* FROM isys_obj " . "INNER JOIN isys_cats_application_list ON isys_obj__id = isys_cats_application_list__isys_obj__id " . "WHERE isys_obj__title = '" . $l_row["application"] . "' " . "AND isys_obj__isys_obj_type__id = '" . C__OBJTYPE__SERVICE . "'";
            }

            $l_resSW = $l_cat_dao->retrieve($l_sql_app);

            if ($l_resSW->num_rows() > 0)
            {
                // Application object exists
                $l_rowSW = $l_resSW->get_row();
                $l_swID  = $l_rowSW["isys_obj__id"];
            }
            else if ($this->m_assign_known_applications == "0")
            {
                // Creat new application object
                $l_swID = $l_cat_dao->insert_new_obj($p_obj_type_app, false, $l_row["application"], null, C__RECORD_STATUS__NORMAL);
                $this->m_cache_dao_sets['isys_event_manager']->triggerCMDBEvent(
                    "C__LOGBOOK_EVENT__OBJECT_CREATED",
                    "-object imported from LOGINventory-",
                    $l_swID,
                    $p_obj_type_app
                );
                $this->import_single_category(
                    $l_swID,
                    $p_obj_type_app,
                    'isys_cmdb_dao_category_s_application',
                    'application_specific',
                    'isys_cats_application_list',
                    'LC__CMDB__CATS__APPLICATION',
                    $l_row
                );
            }

            // Licence
            $l_licence_id = null;
            if ($l_row['assigned_license'] != '')
            {
                if (!isset($this->m_cache_dao_sets['isys_cmdb_dao_category_s_lic']))
                {
                    $this->m_cache_dao_sets['isys_cmdb_dao_category_s_lic'] = isys_cmdb_dao_category_s_lic::instance($this->m_db);
                }
                /**
                 * @var $l_cat_dao_lic isys_cmdb_dao_category_s_lic
                 */
                $l_cat_dao_lic = $this->m_cache_dao_sets['isys_cmdb_dao_category_s_lic'];

                $l_res_lic = $l_cat_dao_lic->retrieve(
                    "SELECT isys_obj__id FROM isys_obj " . "WHERE isys_obj__title = '" . $l_row["application"] . "' " . "AND isys_obj__isys_obj_type__id = '" . C__OBJTYPE__LICENCE . "'"
                );

                if ($l_res_lic->num_rows() > 0)
                {
                    // Licence exists
                    $l_rowLic = $l_res_lic->get_row();
                    $l_licID  = $l_rowLic["isys_obj__id"];

                    $l_res_lic_item = $l_cat_dao_lic->retrieve(
                        "SELECT * FROM isys_cats_lic_list " . "WHERE isys_cats_lic_list__isys_obj__id = " . $l_cat_dao_lic->convert_sql_id($l_licID)
                    );
                    while ($l_row_lic = $l_res_lic_item->get_row())
                    {
                        if ($l_row_lic['isys_cats_lic_list__key'] == $l_row['assigned_license'])
                        {
                            $l_licence_id = $l_row_lic['isys_cats_lic_list__id'];
                            $l_amount     = ((int) $l_row_lic['isys_cats_lic_list__amount'] + 1);
                            break;
                        } // if
                    } // while

                    if (is_null($l_licence_id))
                    {
                        $l_lic_data = [
                            'isys_obj__id' => $l_licID,
                            'amount'       => 1,
                            'key'          => $l_row['assigned_license']
                        ];

                        $l_licence_id = $l_cat_dao_lic->create_data($l_lic_data);

                        $this->m_cache_dao_sets['isys_event_manager']->triggerCMDBEvent(
                            "C__LOGBOOK_EVENT__OBJECT_CREATED",
                            "-object imported from LOGINventory-",
                            $l_licID,
                            C__OBJTYPE__LICENCE
                        );
                    }
                    else
                    {
                        $l_update = 'UPDATE isys_cats_lic_list
							SET isys_cats_lic_list__amount = ' . $l_cat_dao_lic->convert_sql_int($l_amount) . '
							WHERE isys_cats_lic_list__id = ' . $l_cat_dao_lic->convert_sql_id($l_licence_id) . ';';

                        $l_cat_dao_lic->update($l_update);
                        $l_cat_dao_lic->apply_update();
                    } // if
                }
                else
                {
                    // Create new licence
                    $l_licID = $l_cat_dao_lic->insert_new_obj(C__OBJTYPE__LICENCE, false, $l_row["application"], null, C__RECORD_STATUS__NORMAL);

                    $l_lic_data = [
                        'isys_obj__id' => $l_licID,
                        'amount'       => 1,
                        'key'          => $l_row['assigned_license']
                    ];

                    $l_licence_id = $l_cat_dao_lic->create_data($l_lic_data);

                    $this->m_cache_dao_sets['isys_event_manager']->triggerCMDBEvent("C__LOGBOOK_EVENT__OBJECT_CREATED", "-Licence created-", $l_licID, C__OBJTYPE__LICENCE);
                }
            }

            if ($l_swID && !in_array($l_swID, $l_swIDs))
            {
                $l_swIDs[] = $l_swID;
                if (count($l_check_data) > 0 && array_key_exists($l_swID, $l_check_data))
                {
                    // Application found
                    unset($l_check_data[$l_swID]);
                    continue;
                }
                else
                {
                    $l_app_data = [
                        'application' => $l_swID,
                    ];
                    if (isset($l_row['description']))
                    {
                        $l_app_data['description'] = trim($l_row['description']);
                    }
                    if ($l_licence_id > 0)
                    {
                        $l_app_data['assigned_license'] = $l_licence_id;
                    }
                }

                if ($this->m_logbook_active)
                {
                    $l_category_values[isys_import_handler_cmdb::C__PROPERTIES] = ['application' => ['value' => $l_swID]];

                    $l_changes = $this->m_cache_dao_sets['isys_module_logbook']->prepare_changes($l_cat_dao, null, $l_category_values);

                    if (count($l_changes) > 0) $this->m_cache_dao_sets['isys_event_manager']->triggerCMDBEvent(
                        'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                        "-modified from LOGINventory-",
                        $p_obj_id,
                        $p_obj_type,
                        $p_category_title,
                        serialize($l_changes)
                    );
                }

                $l_cat_dao->sync($l_cat_dao->parse_import_array($l_app_data), $p_obj_id, isys_import_handler_cmdb::C__CREATE);
            }
        }

        // Detach Applications
        if (count($l_check_data) > 0)
        {
            $this->delete_entries_from_category($l_check_data, $l_cat_dao, 'isys_catg_application_list', $p_obj_id, $p_obj_type, 'LC__CMDB__CATG__APPLICATION');
        }
        if (count($l_double_assigned) > 0)
        {
            $this->delete_entries_from_category($l_double_assigned, $l_cat_dao, 'isys_catg_application_list', $p_obj_id, $p_obj_type, 'LC__CMDB__CATG__APPLICATION');
        }
    }

    /**
     * Imports single value categories
     *
     * @param $p_obj_id
     * @param $p_obj_type
     * @param $p_class
     * @param $p_type
     * @param $p_table
     * @param $p_category_type
     * @param $p_category_id
     * @param $p_category_title
     * @param $p_data
     */
    private function import_single_category($p_obj_id, $p_obj_type, $p_class, $p_type, $p_table, $p_category_title, $p_data)
    {
        if (!isset($this->m_cache_dao_sets[$p_class]))
        {
            $this->m_cache_dao_sets[$p_class] = call_user_func(
                [
                    $p_class,
                    'instance'
                ],
                $this->m_db
            );
        }
        $l_cat_dao = $this->m_cache_dao_sets[$p_class];

        $l_row = null;
        $l_res = $l_cat_dao->get_data(null, $p_obj_id);

        if ($l_res->num_rows() > 0)
        {
            $l_row             = $l_res->get_row();
            $p_data['data_id'] = $l_row[$p_table . '__id'];
        }

        if ($this->m_logbook_active)
        {

            foreach ($this->m_properties[$p_type] AS $l_property => $l_prop_value)
            {
                if (isset($l_prop_value[C__PROPERTY__FORMAT]))
                {
                    $l_method   = $l_prop_value[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0];
                    $l_params   = $l_prop_value[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1];
                    $l_params[] = $p_data[$l_property];

                    $l_category_values[isys_import_handler_cmdb::C__PROPERTIES][$l_property] = [
                        $l_prop_value[C__PROPERTY__DATA]['params'][0] => call_user_func_array(
                            [
                                $this,
                                $l_method
                            ],
                            $l_params
                        )
                    ];
                }
                else
                {
                    $l_category_values[isys_import_handler_cmdb::C__PROPERTIES][$l_property] = [
                        $l_prop_value[C__PROPERTY__DATA]['params'][0] => $p_data[$l_property]
                    ];
                }
            }

            if (isset($p_data['description_specific']))
            {
                $p_data['description'] = $p_data['description_specific'];
            }

            $l_changes = $this->m_cache_dao_sets['isys_module_logbook']->prepare_changes($l_cat_dao, $l_row, $l_category_values);
            if (count($l_changes) > 0) $this->m_cache_dao_sets['isys_event_manager']->triggerCMDBEvent(
                'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                "-modified from LOGINventory-",
                $p_obj_id,
                $p_obj_type,
                $p_category_title,
                serialize($l_changes)
            );
        }
        $l_cat_dao->sync(
            $l_cat_dao->parse_import_array($p_data),
            $p_obj_id,
            ((!empty($p_data['data_id'])) ? isys_import_handler_cmdb::C__UPDATE : isys_import_handler_cmdb::C__CREATE)
        );
    }

    private function convert($p_type, $p_property_field, $p_convert_method, $p_value)
    {
        global $g_convert;
        $l_unit_id = $this->m_properties[$p_type][$p_property_field][C__PROPERTY__DATA]['default'];

        return $g_convert->$p_convert_method($p_value, $l_unit_id);
    }

    private function dialog_strstr($p_dialog_type, $p_table, $p_value)
    {
        foreach ($this->m_dialog_cache[$p_dialog_type][$p_table] AS $l_key => $l_value)
        {
            if (strstr(strtoupper($p_value), $l_key))
            {
                //return $l_value;
                return $this->m_dialog_cache[$p_dialog_type][$p_table]['_' . $l_value];
                break;
            }
        }

        return $p_value;
    }

    /**
     *
     * @param   string $p_dialog_type
     * @param   string $p_table
     * @param   string $p_value
     *
     * @return  string
     */
    private function dialog($p_dialog_type, $p_table, $p_value)
    {
        foreach ($this->m_dialog_cache[$p_dialog_type][$p_table] AS $l_key => $l_value)
        {
            if (strtoupper($p_value) == $l_key)
            {
                return $l_value;
            } // if
        } // foreach

        return $p_value;
    } // function

    /**
     * Constructor.
     *
     * @param  isys_component_database $p_db  Database component
     * @param  isys_log                $p_log Logger
     */
    public function __construct(isys_component_database $p_db, isys_log $p_log)
    {
        parent::__construct($p_db);

        $this->m_log = $p_log;
    } // function
} // class
?>
