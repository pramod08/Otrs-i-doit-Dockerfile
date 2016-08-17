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
 * Nagios Settings.
 *
 * @todo        In this first version there is no validation, because of the new template-logic.
 * @package     i-doit
 * @subpackage  Modules
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @since       1.1
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_nagios_export
{
    /**
     * This will be used at several points to find out, if debug information shall be displayed.
     *
     * @var  boolean
     */
    protected static $m_debug = false;
    /**
     * Logger instance for the export.
     *
     * @var  isys_log
     */
    protected static $m_log = null;
    /**
     * Static array for saving various information for several methods.
     *
     * @var  array
     */
    protected static $m_tmp = [
        'added_contacts'                      => [],
        'added_contactsgroups'                => [],
        'added_hosts'                         => [],
        'added_hostgroups'                    => [],
        'service_escalations'                 => [],
        'exportables'                         => [],

        // All of the below are used to check, if a host (or one of its templates) has it's mandatory fields filled.
        'hosts_have_max_check_attempts'       => [],
        'hosts_have_check_period'             => [],
        'hosts_have_contacts'                 => [],
        'hosts_have_contact_groups'           => [],
        'hosts_have_notification_interval'    => [],
        'hosts_have_notification_period'      => [],

        // All of the below are used to check, if a service (or one of its templates) has it's mandatory fields filled.
        'services_have_check_commands'        => [],
        'services_have_max_check_attempts'    => [],
        'services_have_check_interval'        => [],
        'services_have_retry_interval'        => [],
        'services_have_check_period'          => [],
        'services_have_notification_interval' => [],
        'services_have_notification_period'   => [],
        'services_have_contacts'              => [],
        'services_have_contact_groups'        => [],
    ];
    /**
     * This variable decides, if the exports will be validated. Set it via "init_export".
     *
     * @var  boolean
     */
    protected static $m_validation = true;
    /**
     * Singleton instance variable.
     *
     * @var  isys_nagios_export
     */
    private static $m_instance = null;
    /**
     * This variable is used to determine, if contacts shall be exported via "login" or "contact"-name.
     *
     * @var  integer
     */
    protected $m_contact_option = null;
    /**
     * This variable will hold the database component.
     *
     * @var  isys_component_database
     */
    protected $m_database_component = null;
    /**
     * This variable will hold the configuration from "isys_monitoring_export_config".
     *
     * @var  array
     */
    protected $m_export_config = [];
    /**
     * This variable will hold the path for saving the exports.
     *
     * @var  string
     */
    protected $m_export_dir = null;
    /**
     * This variable will hold the sub-directory for saving the object-exports.
     *
     * @var  string
     */
    protected $m_export_subdir = null;
    /**
     * This is an indicator for "Has the export been initialized correctly"?
     *
     * @var  boolean
     */
    protected $m_initialized = false;
    /**
     * This variable will hold the nagios component DAO.
     *
     * @var  isys_component_dao_nagios
     */
    protected $m_nagios_component = null;

    /**
     * Public static "instance" method - Singleton!
     *
     * @return  isys_nagios_export
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function instance()
    {
        if (self::$m_instance === null)
        {
            self::$m_instance = new self;
        } // if

        return self::$m_instance;
    } // function

    /**
     * Method for retrieving the nagios log.
     *
     * @return  isys_log
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function get_log()
    {
        return self::$m_log;
    } // function

    /**
     * Method for retrieving a well formed nagios configuration.
     *
     * @param   string $p_name
     * @param   array  $p_attributes
     * @param   array  $p_mandatory
     * @param   string $l_additional
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected static function prepare_config($p_name, array $p_attributes, array $p_mandatory = [], $l_additional = '')
    {
        $l_values = [];

        if (!self::$m_validation)
        {
            $p_mandatory = [];
        } // if

        foreach ($p_attributes as $l_key => $l_value)
        {
            if (!empty($l_value) || $l_value === 0 || $l_value === '0')
            {
                $l_values[] = '  ' . $l_key . ' ' . $l_value;
            }
            else
            {
                // If one of the mandatory fields is empty, we return nothing but a note!
                if (in_array($l_key, $p_mandatory))
                {
                    self::$m_log->notice('Export skipped: Mandatory field "' . $l_key . '" is empty.');
                    self::$m_log->debug(var_export($p_attributes, true));

                    return (self::$m_debug) ? "# Export skipped: Mandatory field '" . $l_key . "' is empty.\n" : '';
                } // if
            } // if
        } // foreach

        if (!empty($l_additional))
        {
            $l_values[] = '  ' . $l_additional;
        } // if

        return "define " . $p_name . "{\n" . implode("\n", $l_values) . "\n}\n\n";
    } // function

    /**
     * Initialize method for the configuration export.
     * There are several options which can be set by this method:
     *
     *    'export_dir' : destination for the nagios export (default "nagiosexport")
     *    'export_subdir' : destination for the nagios definition export (default "objects")
     *    'debug' : This will activate some "debug" information (in the export files via comment and log via "debug" message) (default false)
     *    'validation' : This will determine, if mandatory fields shall be checked for "not empty" (default true)
     *
     * @param   array $p_options An optional set of options. Javascript style!
     *
     * @throws  isys_exception_filesystem
     * @return  isys_nagios_export
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function init_export(array $p_options = [])
    {
        global $g_comp_database, $g_absdir;

        $l_default_options = [
            'export_dir'    => 'nagiosexport',
            'export_subdir' => 'objects',
            'debug'         => false,
            'validation'    => false
        ];

        $l_options = array_merge($l_default_options, $p_options);

        $this->m_database_component = $g_comp_database;
        $this->m_export_dir         = $l_options['export_dir'] . DS;
        $this->m_export_subdir      = $l_options['export_subdir'] . DS;
        $this->m_export_config      = $l_options['export_config'];

        if (!file_exists($l_options['export_dir']))
        {
            if (!mkdir($l_options['export_dir']))
            {
                throw new isys_exception_filesystem(
                    'Directory ' . $l_options['export_dir'] . ' could not be written',
                    'The directory ' . $l_options['export_dir'] . ' could not be written by the i-doit system, please check the rights on your filesystem.'
                );
            } // if
        } // if

        if (!file_exists($this->m_export_dir . $l_options['export_subdir']))
        {
            if (!mkdir($this->m_export_dir . $l_options['export_subdir']))
            {
                throw new isys_exception_filesystem(
                    'Directory ' . $this->m_export_dir . $l_options['export_subdir'] . ' could not be written',
                    'The directory ' . $this->m_export_dir . $l_options['export_subdir'] . ' could not be written by the i-doit system, please check the rights on your filesystem.'
                );
            } // if
        } // if

        $this->m_nagios_component = isys_component_dao_nagios::instance($this->m_database_component);

        // Saving some static data...
        self::$m_validation = $l_options['validation'];
        self::$m_debug      = $l_options['debug'];
        self::$m_log        = isys_log::get_instance('nagios-export')
            ->set_log_file($g_absdir . DS . 'temp' . DS . 'nagios-export_' . date('Y-m-d_H-i') . '.log')
            ->set_log_level(~isys_log::C__DEBUG);

        // Loading the "exportable" objects (objects with status = normal etc.).
        self::$m_tmp['exportables'] = [
            'hosts'    => isys_cmdb_dao_category_g_nagios::instance($this->m_database_component)
                ->get_exportable_objects(),
            'services' => isys_cmdb_dao_category_g_nagios_service_def::instance($this->m_database_component)
                ->get_exportable_objects()
        ];

        // Setting log options.
        if (self::$m_debug)
        {
            self::$m_log->set_log_level(isys_log::C__ALL);
        } // if

        $this->m_initialized = true;

        return $this;
    } // function

    /**
     * Method for starting the export.
     *
     * @return  isys_nagios_export
     * @throws  isys_exception_filesystem
     * @throws  isys_exception_general
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function start_export()
    {
        if ($this->m_initialized !== true)
        {
            throw new isys_exception_general('You need to initialize the export, before starting it!');
        } // if

        self::$m_log->info('Starting export.');

        $this->export_main_config();

        // We can only initialize the helper, when the main configuration was exported.
        isys_nagios_helper::init(
            [
                'contact_option' => $this->m_contact_option
            ]
        );

        $this->export_timeperiods()
            ->export_commands()
            ->export_contacts()
            ->export_host_templates()
            ->export_hosts()
            ->export_host_groups()
            ->export_host_dependencies()
            ->export_service_templates()
            ->export_services()
            ->export_service_escalations()
            ->export_service_groups()
            ->export_service_dependencies();

        if (self::$m_debug)
        {
            self::$m_log->flush_log();
        } // if

        return $this;
    } // function

    /**
     * Method for exporting the service escalations.
     *
     * @return  isys_nagios_export
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function export_service_escalations()
    {
        $l_seHandle = fopen($this->m_export_dir . $this->m_export_subdir . 'serviceescalations.cfg', 'w');

        self::$m_log->info('Exporting: service escalations');

        $l_daoContact = isys_contact_dao_reference::instance($this->m_database_component);
        $l_daoNagios  = isys_cmdb_dao_category_g_nagios::instance($this->m_database_component);

        $l_res = $this->m_nagios_component->getServiceEscalationsResult();

        if (count($l_res) > 0)
        {
            while ($l_row = $l_res->get_row())
            {
                // Check, if this service escalation is used by any service
                if (!array_key_exists($l_row['isys_nagios_service_escalations__id'], self::$m_tmp['service_escalations']))
                {
                    self::$m_log->warning(' -> service escalation "' . $l_row['isys_nagios_service_escalations__title'] . '" has no referenced hosts.');

                    continue;
                } // if

                $l_daoContact->load($l_row['isys_nagios_service_escalations__isys_contact__id']);
                $l_data_items = $l_daoContact->get_data_item_array();

                $l_contacts = $l_contact_groups = [];

                foreach ($l_data_items as $l_objID => $l_val)
                {
                    $l_name   = $l_daoNagios->get_obj_name_by_id_as_string($l_objID);
                    $l_typeID = $l_daoNagios->get_objTypeID($l_objID);

                    if ($l_typeID == C__OBJTYPE__PERSON && in_array($l_name, self::$m_tmp['added_contacts']))
                    {
                        $l_contacts[] = isys_nagios_helper::prepare_valid_name(isys_nagios_helper::get_correct_contact_name($l_objID));
                    }
                    else if ($l_typeID == C__OBJTYPE__PERSON_GROUP && in_array($l_name, self::$m_tmp['added_contactsgroups']))
                    {
                        $l_contact_groups[] = isys_nagios_helper::prepare_valid_name($l_name);
                    } // if
                } // foreach

                if (count($l_contacts) > 0 || count($l_contact_groups) > 0)
                {
                    foreach (self::$m_tmp['service_escalations'][$l_row['isys_nagios_service_escalations__id']] as $l_service_description => $l_hosts)
                    {
                        $l_attributes = [
                            'host_name'             => $l_hosts['hosts'],
                            'hostgroup_name'        => $l_hosts['hostgroups'],
                            'service_description'   => $l_service_description,
                            'contacts'              => implode(',', $l_contacts),
                            'contact_groups'        => implode(',', $l_contact_groups),
                            'first_notification'    => $l_row["isys_nagios_service_escalations__first_notification"],
                            'last_notification'     => $l_row["isys_nagios_service_escalations__last_notification"],
                            'notification_interval' => $l_row["isys_nagios_service_escalations__notification_interval"],
                            'escalation_period'     => isys_nagios_helper::get_timeperiod(
                                $l_row["isys_nagios_service_escalations__escalation_period"],
                                $l_row["isys_nagios_service_escalations__escalation_period_plus"]
                            ),
                            'escalation_options'    => $l_row["isys_nagios_service_escalations__escalation_options"]
                        ];

                        $l_mandatory = [
                            'host_name',
                            'service_description',
                            'contacts',
                            'contact_groups',
                            'first_notification',
                            'last_notification',
                            'notification_interval'
                        ];

                        if (count($l_contacts) || count($l_contact_groups))
                        {
                            unset($l_mandatory[2], $l_mandatory[3]);
                        } // if

                        self::$m_log->debug(' -> writing service escalation for service_description "' . $l_service_description . '"');
                        fputs($l_seHandle, self::prepare_config('serviceescalation', $l_attributes, $l_mandatory));
                    }
                }
                else
                {
                    self::$m_log->warning(
                        ' -> service escalation "' . $l_row['isys_nagios_service_escalations__title'] . '" has ' . count($l_contacts) . ' contacts and ' . count(
                            $l_contact_groups
                        ) . ' contact-groups.'
                    );
                } // if
            } // while
        } // if

        fclose($l_seHandle);

        return $this;
    } // function

    /**
     * Method for exporting the main nagios configuration.
     *
     * @return  isys_nagios_export
     * @throws  isys_exception_filesystem
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function export_main_config()
    {
        self::$m_log->info('Exporting: main configuration');

        $l_nagios_config_file = 'nagios.cfg';
        $l_handle             = fopen($this->m_export_dir . $l_nagios_config_file, 'w');

        if ($l_handle === false)
        {
            throw new isys_exception_filesystem(
                'File ' . $this->m_export_dir . $l_nagios_config_file . ' not writable',
                'The file "' . $l_nagios_config_file . '", located at "' . $this->m_export_dir . '", could not be opened for writing by the i-doit system, please check the rights on your filesystem.'
            );
        } // if

        // Write main config to file.
        $l_config_res = $this->m_nagios_component->getConfig();

        if (count($l_config_res) > 0)
        {
            while ($l_row = $l_config_res->get_row())
            {
                if ($l_row['key'] == 'PERSON_NAME_OPTION')
                {
                    $this->m_contact_option = $l_row['value'];
                } // if

                // The check on "empty" and "0" are necessary!
                if (!in_array(
                        $l_row['key'],
                        [
                            'PERSON_NAME_OPTION',
                            'resource_file',
                            'broker_module',
                            'broker_module',
                            'cfg_file',
                            'cfg_dir'
                        ]
                    ) && (!empty($l_row['value']) || $l_row['value'] === '0' || $l_row['value'] === 0)
                )
                {
                    fputs($l_handle, $l_row['key'] . '=' . $l_row['value'] . "\n");
                } // if

                if (in_array(
                    $l_row['key'],
                    [
                        'resource_file',
                        'broker_module',
                        'cfg_file',
                        'cfg_dir'
                    ]
                ))
                {
                    $l_array = @unserialize($l_row['value']);

                    if (is_array($l_array))
                    {
                        foreach ($l_array as $l_item)
                        {
                            fputs($l_handle, $l_row['key'] . '=' . $l_item . "\n");
                        } // foreach
                    } // if
                } // if
            } // while
        } // if

        // Adding our sub-directory to the configuration.
        fputs($l_handle, 'cfg_dir=' . $this->m_export_subdir);
        fclose($l_handle);

        return $this;
    } // function

    /**
     * Method for exporting the nagios commands.
     *
     * @return  isys_nagios_export
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function export_commands()
    {
        self::$m_log->info('Exporting: commands');

        $l_handle      = fopen($this->m_export_dir . $this->m_export_subdir . 'commands.cfg', 'w');
        $l_command_res = $this->m_nagios_component->getCommands();

        if (count($l_command_res) > 0)
        {
            while ($l_row = $l_command_res->get_row())
            {
                $l_attributes = [
                    'command_name' => $l_row['name'],
                    'command_line' => $l_row['line']
                ];

                fputs($l_handle, self::prepare_config('command', $l_attributes, array_keys($l_attributes)));
            } // while
        } // if

        fclose($l_handle);

        return $this;
    } // function

    /**
     * Method for exporting the nagios timeperiods.
     *
     * @return  isys_nagios_export
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function export_timeperiods()
    {
        self::$m_log->info('Exporting: timeperiods');

        $l_handle = fopen($this->m_export_dir . $this->m_export_subdir . 'timeperiods.cfg', 'w');

        $l_timeperiod_res = $this->m_nagios_component->getTimeperiods();

        if (count($l_timeperiod_res) > 0)
        {
            while ($l_row = $l_timeperiod_res->get_row())
            {
                // Only export this setting, when both values are not empty.
                if (!empty($l_row['name']) && !empty($l_row['alias']))
                {
                    $l_attributes = [
                        'timeperiod_name' => $l_row['name'],
                        'alias'           => $l_row['alias']
                    ];

                    fputs($l_handle, self::prepare_config('timeperiod', $l_attributes, array_keys($l_attributes), $l_row['definition']));
                } // if
            } // while
        } // if

        fclose($l_handle);

        return $this;
    } // function

    /**
     * Method for exporting the nagios contacts and contact groups.
     *
     * @return  isys_nagios_export
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function export_contacts()
    {
        self::$m_log->info('Exporting: contacts and contactgroups');

        $l_daoContactGroup = isys_contact_dao_group::instance($this->m_database_component);
        $l_handle          = fopen($this->m_export_dir . $this->m_export_subdir . 'contacts.cfg', 'w');

        $l_contact_res = $this->m_nagios_component->getContacts();
        if (count($l_contact_res) > 0)
        {
            while ($l_row = $l_contact_res->get_row())
            {
                if ($l_row['isys_cats_person_nagios_list__is_exportable'] == 0)
                {
                    continue;
                } // if

                $l_service_commands = $l_host_commands = [];

                $l_temp = explode(',', $l_row['isys_cats_person_nagios_list__host_notification_commands']);
                foreach ($l_temp as $l_val)
                {
                    if (!empty($l_val))
                    {
                        $l_command         = $this->m_nagios_component->getCommand($l_val);
                        $l_host_commands[] = $l_command['name'];
                    } // if
                } // foreach

                $l_temp = explode(',', $l_row['isys_cats_person_nagios_list__service_notification_commands']);
                foreach ($l_temp as $l_val)
                {
                    if (!empty($l_val))
                    {
                        $l_command            = $this->m_nagios_component->getCommand($l_val);
                        $l_service_commands[] = $l_command['name'];
                    } // if
                } // foreach

                $l_attributes = [
                    'contact_name'                  => isys_nagios_helper::prepare_valid_name(isys_nagios_helper::get_correct_contact_name($l_row['isys_obj__id'])),
                    'alias'                         => $l_row['isys_cats_person_nagios_list__alias'],
                    'contactgroups'                 => '',
                    'host_notifications_enabled'    => (int) $l_row['isys_cats_person_nagios_list__host_notification_enabled'],
                    'service_notifications_enabled' => (int) $l_row['isys_cats_person_nagios_list__service_notification_enabled'],
                    'host_notification_period'      => isys_nagios_helper::get_timeperiod(
                        $l_row['isys_cats_person_nagios_list__host_notification_period'],
                        $l_row['isys_cats_person_nagios_list__host_notification_period_plus']
                    ),
                    'service_notification_period'   => isys_nagios_helper::get_timeperiod(
                        $l_row['isys_cats_person_nagios_list__service_notification_period'],
                        $l_row['isys_cats_person_nagios_list__service_notification_period_plus']
                    ),
                    'host_notification_options'     => $l_row['isys_cats_person_nagios_list__host_notification_options'],
                    'service_notification_options'  => $l_row['isys_cats_person_nagios_list__service_notification_options'],
                    'host_notification_commands'    => implode(',', $l_host_commands),
                    'service_notification_commands' => implode(',', $l_service_commands),
                    'email'                         => $l_row['isys_cats_person_list__mail_address'],
                    'pager'                         => $l_row['isys_cats_person_list__pager'],
                    'addressx'                      => '',
                    'can_submit_commands'           => (int) $l_row['isys_cats_person_nagios_list__can_submit_commands'],
                    'retain_status_information'     => (int) $l_row['isys_cats_person_nagios_list__retain_status_information'],
                    'retain_nonstatus_information'  => (int) $l_row['isys_cats_person_nagios_list__retain_nonstatus_information']
                ];

                $l_mandatory = [
                    'contact_name',
                    'host_notifications_enabled',
                    'service_notifications_enabled',
                    'host_notification_period',
                    'service_notification_period',
                    'host_notification_options',
                    'service_notification_options',
                    'host_notification_commands',
                    'service_notification_commands'
                ];

                self::$m_log->debug(' -> writing contact "' . $l_attributes['contact_name'] . '" (alias "' . $l_attributes['alias'] . '")');
                fputs($l_handle, self::prepare_config('contact', $l_attributes, $l_mandatory, $l_row['isys_cats_person_nagios_list__custom_obj_vars']));

                self::$m_tmp['added_contacts'][] = $l_row['isys_obj__title'];
            } // foreach
        } // if

        $l_contact_group_res = $this->m_nagios_component->getContactGroups();

        if (count($l_contact_group_res) > 0)
        {
            while (($l_row = $l_contact_group_res->get_row()))
            {
                if ($l_row['isys_cats_person_group_nagios_list__is_exportable'] == 0)
                {
                    continue;
                } // if

                $l_persons = $l_daoContactGroup->get_persons_by_id($l_row['isys_obj__id']);

                $l_members = [];

                while (($l_person_row = $l_persons->get_row()))
                {
                    if (in_array($l_person_row['isys_obj__title'], self::$m_tmp['added_contacts']))
                    {
                        $l_members[] = isys_nagios_helper::prepare_valid_name(isys_nagios_helper::get_correct_contact_name($l_person_row['isys_obj__id']));
                    } // if
                } // while

                if (count($l_members) > 0)
                {
                    $l_attributes = [
                        'contactgroup_name'    => isys_nagios_helper::prepare_valid_name($l_row['isys_obj__title']),
                        'alias'                => $l_row['isys_cats_person_group_nagios_list__alias'],
                        'members'              => implode(',', $l_members),
                        'contactgroup_members' => ''
                    ];

                    self::$m_log->debug(' -> writing contactgroup "' . $l_attributes['contactgroup_name'] . '" (alias "' . $l_attributes['alias'] . '")');
                    fputs(
                        $l_handle,
                        self::prepare_config(
                            'contactgroup',
                            $l_attributes,
                            [
                                'contactgroup_name',
                                'alias'
                            ]
                        )
                    );

                    self::$m_tmp['added_contactsgroups'][] = $l_row['isys_obj__title'];
                }
                else
                {
                    self::$m_log->warning(' -> contactgroup "' . $l_attributes['contactgroup_name'] . '" (alias "' . $l_attributes['alias'] . '") has 0 members!');
                } // if
            } // while
        } // if

        fclose($l_handle);

        return $this;
    } // function

    /**
     * Method for exporting the nagios hosts and host escalations.
     *
     * @return  isys_nagios_export
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function export_hosts()
    {
        self::$m_log->info('Exporting: hosts and hostescalations');

        $l_host_handle           = fopen($this->m_export_dir . $this->m_export_subdir . 'hosts.cfg', 'a');
        $l_hostescalation_handle = fopen($this->m_export_dir . $this->m_export_subdir . 'hostescalations.cfg', 'w');

        $l_daoNagios    = isys_cmdb_dao_category_g_nagios::instance($this->m_database_component);
        $l_daoNagiosTpl = isys_cmdb_dao_category_g_nagios_host_tpl_def::instance($this->m_database_component);
        $l_daoIP        = isys_cmdb_dao_category_g_ip::instance($this->m_database_component);
        $l_daoContact   = isys_contact_dao_reference::instance($this->m_database_component);

        $l_hosts_res = $l_daoNagios->getHosts(true, $this->m_export_config['isys_monitoring_export_config__id']);
        if (count($l_hosts_res) > 0)
        {
            while ($l_row = $l_hosts_res->get_row())
            {
                $l_nagios_parents = [];

                if ($l_row['isys_catg_nagios_list__is_parent'])
                {
                    $l_parents = $this->m_nagios_component->getParents($l_row['isys_obj__id']);

                    foreach ($l_parents as $l_parent)
                    {
                        $l_nagios_parents[] = $l_parent['rendered_host_name'];
                    } // foreach
                } // if

                $l_parents = $this->m_nagios_component->get_additional_parents($l_row['isys_obj__id']);

                if (count($l_parents) > 0)
                {
                    foreach ($l_parents as $l_parent)
                    {
                        $l_nagios_parents[] = $l_parent['rendered_host_name'];
                    } // foreach
                } // if

                $l_host_name = isys_nagios_helper::render_export_hostname($l_row['isys_obj__id']);

                $l_contact_res = $this->m_nagios_component->getNagiosContacts($l_row['isys_catg_nagios_list__isys_obj__id']);
                $l_contacts    = $l_contact_groups = [];

                if (count($l_contact_res) > 0)
                {
                    while ($l_contact_row = $l_contact_res->get_row())
                    {
                        if ($l_contact_row['isys_obj_type__const'] == 'C__OBJTYPE__PERSON' && in_array($l_contact_row['isys_obj__title'], self::$m_tmp['added_contacts']))
                        {
                            $l_contacts[] = isys_nagios_helper::prepare_valid_name(isys_nagios_helper::get_correct_contact_name($l_contact_row['isys_obj__id']));
                        }
                        else if ($l_contact_row['isys_obj_type__const'] == 'C__OBJTYPE__PERSON_GROUP' && in_array(
                                $l_contact_row['isys_obj__title'],
                                self::$m_tmp['added_contactsgroups']
                            )
                        )
                        {
                            $l_contact_groups[] = isys_nagios_helper::prepare_valid_name($l_contact_row['isys_obj__title']);
                        } // if
                    } // while
                } // if

                if (!empty($l_row['isys_catg_nagios_list__escalations']))
                {
                    $l_arEscalations = explode(',', $l_row['isys_catg_nagios_list__escalations']);

                    foreach ($l_arEscalations as $l_esc)
                    {
                        $l_escData = $this->m_nagios_component->getHostEscalation($l_esc);

                        $l_daoContact->load($l_escData['isys_nagios_host_escalations__isys_contact__id']);
                        $l_data_items  = $l_daoContact->get_data_item_array();
                        $l_he_contacts = $l_he_contact_groups = [];

                        foreach ($l_data_items as $l_objID => $l_val)
                        {
                            $l_name   = $l_daoNagios->get_obj_name_by_id_as_string($l_objID);
                            $l_typeID = $l_daoNagios->get_objTypeID($l_objID);

                            if ($l_typeID == C__OBJTYPE__PERSON && in_array($l_name, self::$m_tmp['added_contacts']))
                            {
                                $l_he_contacts[] = isys_nagios_helper::prepare_valid_name(isys_nagios_helper::get_correct_contact_name($l_objID));
                            }
                            else if ($l_typeID == C__OBJTYPE__PERSON_GROUP && in_array($l_name, self::$m_tmp['added_contactsgroups']))
                            {
                                $l_he_contact_groups[] = isys_nagios_helper::prepare_valid_name($l_name);
                            } // if
                        } // foreach

                        $l_attributes = [
                            'host_name'             => $l_host_name,
                            'hostgroup_name'        => '',
                            'contacts'              => implode(',', $l_he_contacts),
                            'contact_groups'        => implode(',', $l_he_contact_groups),
                            'first_notification'    => $l_escData['isys_nagios_host_escalations__first_notification'],
                            'last_notification'     => $l_escData['isys_nagios_host_escalations__last_notification'],
                            'notification_interval' => $l_escData['isys_nagios_host_escalations__notification_interval'],
                            'escalation_period'     => isys_nagios_helper::get_timeperiod(
                                $l_escData['isys_nagios_host_escalations__escalation_period'],
                                $l_escData['isys_nagios_host_escalations__escalation_period_plus']
                            ),
                            'escalation_options'    => $l_escData['isys_nagios_host_escalations__escalation_options']
                        ];

                        $l_mandatory = [
                            'host_name',
                            'contacts',
                            'contact_groups',
                            'first_notification',
                            'last_notification',
                            'notification_interval'
                        ];

                        if (count($l_he_contacts) || count($l_he_contact_groups))
                        {
                            unset($l_mandatory[1], $l_mandatory[2]);
                        }

                        self::$m_log->debug(' -> writing hostescalation for host "' . $l_host_name . '"');
                        fputs($l_hostescalation_handle, self::prepare_config('hostescalation', $l_attributes, $l_mandatory));
                    } // foreach
                } // if

                $l_attributes = [
                    'host_name'                    => $l_host_name,
                    'alias'                        => $l_row['isys_catg_nagios_list__alias'],
                    'address'                      => isys_nagios_helper::render_export_address($l_row['isys_obj__id']),
                    'parents'                      => implode(',', $l_nagios_parents),
                    'check_command'                => isys_nagios_helper::get_command(
                        $l_row['isys_catg_nagios_list__check_command'],
                        $l_row['isys_catg_nagios_list__check_command_plus'],
                        $l_row['isys_catg_nagios_list__check_command_parameters']
                    ),
                    'check_interval'               => $l_row['isys_catg_nagios_list__check_interval'],
                    'retry_interval'               => $l_row['isys_catg_nagios_list__retry_interval'],
                    'max_check_attempts'           => $l_row['isys_catg_nagios_list__max_check_attempts'],
                    'check_period'                 => isys_nagios_helper::get_timeperiod(
                        $l_row['isys_catg_nagios_list__check_period'],
                        $l_row['isys_catg_nagios_list__check_period_plus']
                    ),
                    'active_checks_enabled'        => $l_row['isys_catg_nagios_list__active_checks_enabled'],
                    'passive_checks_enabled'       => $l_row['isys_catg_nagios_list__passive_checks_enabled'],
                    'notifications_enabled'        => $l_row['isys_catg_nagios_list__notifications_enabled'],
                    'notification_options'         => $l_row['isys_catg_nagios_list__notification_options'],
                    'notification_interval'        => $l_row['isys_catg_nagios_list__notification_interval'],
                    'notification_period'          => isys_nagios_helper::get_timeperiod(
                        $l_row['isys_catg_nagios_list__notification_period'],
                        $l_row['isys_catg_nagios_list__notification_period_plus']
                    ),
                    'initial_state'                => $l_row['isys_catg_nagios_list__initial_state'],
                    'obsess_over_host'             => $l_row['isys_catg_nagios_list__obsess_over_host'],
                    'check_freshness'              => $l_row['isys_catg_nagios_list__check_freshness'],
                    'freshness_threshold'          => $l_row['isys_catg_nagios_list__freshness_threshold'],
                    'flap_detection_enabled'       => $l_row['isys_catg_nagios_list__flap_detection_enabled'],
                    'flap_detection_options'       => $l_row['isys_catg_nagios_list__flap_detection_options'],
                    'low_flap_threshold'           => $l_row['isys_catg_nagios_list__low_flap_threshold'],
                    'high_flap_threshold'          => $l_row['isys_catg_nagios_list__high_flap_threshold'],
                    'event_handler_enabled'        => $l_row['isys_catg_nagios_list__event_handler_enabled'],
                    'event_handler'                => isys_nagios_helper::get_command(
                        $l_row['isys_catg_nagios_list__event_handler'],
                        $l_row['isys_catg_nagios_list__event_handler_plus'],
                        $l_row['isys_catg_nagios_list__event_handler_parameters']
                    ),
                    'process_perf_data'            => $l_row['isys_catg_nagios_list__process_perf_data'],
                    'retain_status_information'    => $l_row['isys_catg_nagios_list__retain_status_information'],
                    'retain_nonstatus_information' => $l_row['isys_catg_nagios_list__retain_nonstatus_information'],
                    'first_notification_delay'     => $l_row['isys_catg_nagios_list__first_notification_delay'],
                    'stalking_options'             => $l_row['isys_catg_nagios_list__stalking_options'],
                    'action_url'                   => $l_row['isys_catg_nagios_list__action_url'],
                    'icon_image'                   => $l_row['isys_catg_nagios_list__icon_image'],
                    'icon_image_alt'               => $l_row['isys_catg_nagios_list__icon_image_alt'],
                    'vrml_image'                   => $l_row['isys_catg_nagios_list__vrml_image'],
                    'statusmap_image'              => $l_row['isys_catg_nagios_list__statusmap_image'],
                    '2d_coords'                    => $l_row['isys_catg_nagios_list__2d_coords'],
                    '3d_coords'                    => $l_row['isys_catg_nagios_list__3d_coords'],
                    'notes'                        => $l_row['isys_catg_nagios_list__notes'],
                    'notes_url'                    => $l_row['isys_catg_nagios_list__notes_url'],
                    'display_name'                 => ($l_row['isys_catg_nagios_list__alias'] ?: $l_host_name),
                    // 'hostgroups' => '', // Unused...
                    'contacts'                     => implode(',', $l_contacts),
                    'contact_groups'               => implode(',', $l_contact_groups),
                ];

                $l_mandatory = [
                    'host_name',
                    'alias',
                    'address',
                    'max_check_attempts',
                    'check_period',
                    'contacts',
                    'contact_groups',
                    'notification_interval',
                    'notification_period'
                ];

                // If we choose to use a template for this host.
                if (!empty($l_row['isys_catg_nagios_list__host_tpl']))
                {
                    $l_templates = $l_tpl_selection = [];

                    if (isys_format_json::is_json($l_row['isys_catg_nagios_list__host_tpl']))
                    {
                        $l_tpl_selection = isys_format_json::decode($l_row['isys_catg_nagios_list__host_tpl'], true);
                    } // if

                    // This can happen, when only one template is selected.
                    if (!is_array($l_tpl_selection))
                    {
                        $l_tpl_selection = [$l_tpl_selection];
                    } // if

                    if (is_array($l_tpl_selection))
                    {
                        // Sadly this is the easiest way of preserving the sorting.
                        foreach ($l_tpl_selection as $l_tpl)
                        {
                            $l_tpl_row     = $l_daoNagiosTpl->get_data(null, $l_tpl, '', null, C__RECORD_STATUS__NORMAL)
                                ->get_row();
                            $l_templates[] = $l_tpl_row['isys_catg_nagios_host_tpl_def_list__name1'];

                            // Here we check, if the current template already handles the mandatory fields.
                            if (in_array($l_tpl, self::$m_tmp['hosts_have_max_check_attempts']))
                            {
                                self::$m_log->debug('Template "' . $l_tpl_row['isys_catg_nagios_host_tpl_def_list__name1'] . '" already handles max_check_attempts');
                                unset($l_mandatory[3]);
                            } // if

                            if (in_array($l_tpl, self::$m_tmp['hosts_have_check_period']))
                            {
                                self::$m_log->debug('Template "' . $l_tpl_row['isys_catg_nagios_host_tpl_def_list__name1'] . '" already handles check_period');
                                unset($l_mandatory[4]);
                            } // if

                            if (in_array($l_tpl, self::$m_tmp['hosts_have_contacts']))
                            {
                                self::$m_log->debug('Template "' . $l_tpl_row['isys_catg_nagios_host_tpl_def_list__name1'] . '" already handles contacts');
                                // Contacts OR contact groups are necessary - not both.
                                unset($l_mandatory[5], $l_mandatory[6]);
                            } // if

                            if (in_array($l_tpl, self::$m_tmp['hosts_have_contact_groups']))
                            {
                                self::$m_log->debug('Template "' . $l_tpl_row['isys_catg_nagios_host_tpl_def_list__name1'] . '" already handles contact_groups');
                                // Contacts OR contact groups are necessary - not both.
                                unset($l_mandatory[5], $l_mandatory[6]);
                            } // if

                            if (in_array($l_tpl, self::$m_tmp['hosts_have_notification_interval']))
                            {
                                self::$m_log->debug('Template "' . $l_tpl_row['isys_catg_nagios_host_tpl_def_list__name1'] . '" already handles notification_interval');
                                unset($l_mandatory[7]);
                            } // if

                            if (in_array($l_tpl, self::$m_tmp['hosts_have_notification_period']))
                            {
                                self::$m_log->debug('Template "' . $l_tpl_row['isys_catg_nagios_host_tpl_def_list__name1'] . '" already handles notification_period');
                                unset($l_mandatory[8]);
                            } // if
                        } // forach
                    } // if

                    if (count($l_templates) > 0)
                    {
                        $l_attributes['use'] = implode(',', $l_templates);
                    } // if
                } // if

                if (count($l_contacts) || count($l_contact_groups))
                {
                    $l_contacts_index = array_search('contacts', $l_mandatory);

                    if ($l_contacts_index !== false)
                    {
                        unset($l_mandatory[$l_contacts_index]);
                    } // if

                    $l_contactgroups_index = array_search('contact_groups', $l_mandatory);

                    if ($l_contactgroups_index !== false)
                    {
                        unset($l_mandatory[$l_contactgroups_index]);
                    } // if
                } // if

                self::$m_log->debug(' -> writing host "' . $l_host_name . '" (alias "' . $l_row['isys_catg_nagios_list__alias'] . '")');
                $l_config = self::prepare_config('host', $l_attributes, $l_mandatory, $l_row['isys_catg_nagios_list__custom_obj_vars']);

                fputs($l_host_handle, $l_config);

                if (strpos($l_config, 'define ') === 0)
                {
                    self::$m_tmp['added_hosts'][] = $l_attributes['host_name'];
                } // if

                unset($l_config);
            } // while
        } // if

        fclose($l_host_handle);
        fclose($l_hostescalation_handle);

        return $this;
    } // function

    /**
     * Method for exporting the nagios host templates.
     *
     * @return  isys_nagios_export
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function export_host_templates()
    {
        self::$m_log->info('Exporting: host templates');
        $l_host_handle = fopen($this->m_export_dir . $this->m_export_subdir . 'hosts.cfg', 'w');

        $l_host_tpl_dao = isys_cmdb_dao_category_g_nagios_host_tpl_def::instance($this->m_database_component);
        $l_daoContact   = isys_contact_dao_reference::instance($this->m_database_component);

        $l_hosts_res = $l_host_tpl_dao->get_data(
            null,
            null,
            ' AND isys_catg_nagios_host_tpl_def_list__is_exportable = 1 AND isys_catg_nagios_host_tpl_def_list__export_host = ' . $l_host_tpl_dao->convert_sql_id(
                $this->m_export_config['isys_monitoring_export_config__id']
            ),
            null,
            C__RECORD_STATUS__NORMAL
        );

        if (count($l_hosts_res) > 0)
        {
            while ($l_row = $l_hosts_res->get_row())
            {
                // We do not want to export archived, deleted or template-objects.
                if ($l_row['isys_obj__status'] != C__RECORD_STATUS__NORMAL)
                {
                    continue;
                } // if

                $l_contact_res = $this->m_nagios_component->getNagiosContacts($l_row['isys_obj__id']);
                $l_contacts    = $l_contact_groups = [];

                if (count($l_contact_res) > 0)
                {
                    while (($l_contact_row = $l_contact_res->get_row()))
                    {
                        if ($l_contact_row['isys_obj_type__const'] == 'C__OBJTYPE__PERSON' && in_array($l_contact_row['isys_obj__title'], self::$m_tmp['added_contacts']))
                        {
                            $l_contacts[] = isys_nagios_helper::prepare_valid_name(isys_nagios_helper::get_correct_contact_name($l_contact_row['isys_obj__id']));

                            if (!in_array($l_row['isys_obj__id'], self::$m_tmp['hosts_have_contacts']))
                            {
                                self::$m_tmp['hosts_have_contacts'][] = $l_row['isys_obj__id'];
                            } // if
                        }
                        else if ($l_contact_row['isys_obj_type__const'] == 'C__OBJTYPE__PERSON_GROUP' && in_array(
                                $l_contact_row['isys_obj__title'],
                                self::$m_tmp['added_contactsgroups']
                            )
                        )
                        {
                            $l_contact_groups[] = isys_nagios_helper::prepare_valid_name($l_contact_row['isys_obj__title']);

                            if (!in_array($l_row['isys_obj__id'], self::$m_tmp['hosts_have_contact_groups']))
                            {
                                self::$m_tmp['hosts_have_contact_groups'][] = $l_row['isys_obj__id'];
                            } // if
                        } // if
                    } // while
                } // if

                if (!empty($l_row['isys_catg_nagios_host_tpl_def_list__escalations']))
                {
                    $l_arEscalations = explode(',', $l_row['isys_catg_nagios_host_tpl_def_list__escalations']);

                    foreach ($l_arEscalations as $l_esc)
                    {
                        $l_escData = $this->m_nagios_component->getHostEscalation($l_esc);

                        $l_daoContact->load($l_escData['isys_nagios_host_escalations__isys_contact__id']);
                        $l_data_items = $l_daoContact->get_data_item_array();
                        $l_contacts   = $l_contact_groups = [];

                        foreach ($l_data_items as $l_objID => $l_val)
                        {
                            $l_name   = $l_host_tpl_dao->get_obj_name_by_id_as_string($l_objID);
                            $l_typeID = $l_host_tpl_dao->get_objTypeID($l_objID);

                            if ($l_typeID == C__OBJTYPE__PERSON && in_array($l_name, self::$m_tmp['added_contacts']))
                            {
                                $l_contacts[] = isys_nagios_helper::prepare_valid_name(isys_nagios_helper::get_correct_contact_name($l_objID));

                                if (!in_array($l_row['isys_obj__id'], self::$m_tmp['hosts_have_contacts']))
                                {
                                    self::$m_tmp['hosts_have_contacts'][] = $l_row['isys_obj__id'];
                                } // if
                            }
                            else if ($l_typeID == C__OBJTYPE__PERSON_GROUP && in_array($l_name, self::$m_tmp['added_contactsgroups']))
                            {
                                $l_contact_groups[] = isys_nagios_helper::prepare_valid_name($l_name);

                                if (!in_array($l_row['isys_obj__id'], self::$m_tmp['hosts_have_contact_groups']))
                                {
                                    self::$m_tmp['hosts_have_contact_groups'][] = $l_row['isys_obj__id'];
                                } // if
                            } // if
                        } // foreach
                    } // foreach
                } // if

                // What about "escalations" ? Separate "host escalations" for host templates?
                $l_attributes = [
                    'name'                         => $l_row['isys_catg_nagios_host_tpl_def_list__name1'],
                    'host_name'                    => '',
                    'alias'                        => '',
                    'max_check_attempts'           => $l_row['isys_catg_nagios_host_tpl_def_list__max_check_attempts'],
                    'check_period'                 => isys_nagios_helper::get_timeperiod(
                        $l_row['isys_catg_nagios_host_tpl_def_list__check_period'],
                        $l_row['isys_catg_nagios_host_tpl_def_list__check_period_plus']
                    ),
                    'notification_interval'        => $l_row['isys_catg_nagios_host_tpl_def_list__notification_interval'],
                    'notification_period'          => isys_nagios_helper::get_timeperiod(
                        $l_row['isys_catg_nagios_host_tpl_def_list__notification_period'],
                        $l_row['isys_catg_nagios_host_tpl_def_list__notification_period_plus']
                    ),
                    'notifications_enabled'        => $l_row['isys_catg_nagios_host_tpl_def_list__notifications_enabled'],
                    'notification_options'         => $l_row['isys_catg_nagios_host_tpl_def_list__notification_options'],
                    'check_command'                => isys_nagios_helper::get_command(
                        $l_row['isys_catg_nagios_host_tpl_def_list__check_command'],
                        $l_row['isys_catg_nagios_host_tpl_def_list__check_command_plus'],
                        $l_row['isys_catg_nagios_host_tpl_def_list__check_command_parameters']
                    ),
                    'check_interval'               => $l_row['isys_catg_nagios_host_tpl_def_list__check_interval'],
                    'retry_interval'               => $l_row['isys_catg_nagios_host_tpl_def_list__retry_interval'],
                    'active_checks_enabled'        => $l_row['isys_catg_nagios_host_tpl_def_list__active_checks_enabled'],
                    'passive_checks_enabled'       => $l_row['isys_catg_nagios_host_tpl_def_list__passive_checks_enabled'],
                    'initial_state'                => $l_row['isys_catg_nagios_host_tpl_def_list__initial_state'],
                    'obsess_over_host'             => $l_row['isys_catg_nagios_host_tpl_def_list__obsess_over_host'],
                    'check_freshness'              => $l_row['isys_catg_nagios_host_tpl_def_list__check_freshness'],
                    'freshness_threshold'          => $l_row['isys_catg_nagios_host_tpl_def_list__freshness_threshold'],
                    'flap_detection_enabled'       => $l_row['isys_catg_nagios_host_tpl_def_list__flap_detection_enabled'],
                    'flap_detection_options'       => $l_row['isys_catg_nagios_host_tpl_def_list__flap_detection_options'],
                    'low_flap_threshold'           => $l_row['isys_catg_nagios_host_tpl_def_list__low_flap_threshold'],
                    'high_flap_threshold'          => $l_row['isys_catg_nagios_host_tpl_def_list__high_flap_threshold'],
                    'event_handler_enabled'        => $l_row['isys_catg_nagios_host_tpl_def_list__event_handler_enabled'],
                    'event_handler'                => isys_nagios_helper::get_command(
                        $l_row['isys_catg_nagios_host_tpl_def_list__event_handler'],
                        $l_row['isys_catg_nagios_host_tpl_def_list__event_handler_plus'],
                        $l_row['isys_catg_nagios_host_tpl_def_list__event_handler_parameters']
                    ),
                    'process_perf_data'            => $l_row['isys_catg_nagios_host_tpl_def_list__process_perf_data'],
                    'retain_status_information'    => $l_row['isys_catg_nagios_host_tpl_def_list__retain_status_information'],
                    'retain_nonstatus_information' => $l_row['isys_catg_nagios_host_tpl_def_list__retain_nonstatus_information'],
                    'first_notification_delay'     => $l_row['isys_catg_nagios_host_tpl_def_list__first_notification_delay'],
                    'stalking_options'             => $l_row['isys_catg_nagios_host_tpl_def_list__stalking_options'],
                    'action_url'                   => $l_row['isys_catg_nagios_host_tpl_def_list__action_url'],
                    'icon_image'                   => $l_row['isys_catg_nagios_host_tpl_def_list__icon_image'],
                    'icon_image_alt'               => $l_row['isys_catg_nagios_host_tpl_def_list__icon_image_alt'],
                    'vrml_image'                   => $l_row['isys_catg_nagios_host_tpl_def_list__vrml_image'],
                    'statusmap_image'              => $l_row['isys_catg_nagios_host_tpl_def_list__statusmap_image'],
                    'notes'                        => $l_row['isys_catg_nagios_host_tpl_def_list__notes'],
                    'notes_url'                    => $l_row['isys_catg_nagios_host_tpl_def_list__notes_url'],
                    'display_name'                 => $l_row['isys_catg_nagios_host_tpl_def_list__name1'],
                    'hostgroups'                   => '',
                    // Unused...
                    'contacts'                     => implode(',', $l_contacts),
                    'contact_groups'               => implode(',', $l_contact_groups),
                    'register'                     => 0
                    // This marks the template in nagios!
                ];

                $l_mandatory = [
                    'name',
                ];

                if ($l_attributes['max_check_attempts'] > 0)
                {
                    self::$m_tmp['hosts_have_max_check_attempts'][] = $l_row['isys_obj__id'];
                } // if

                if (!empty($l_attributes['check_period']))
                {
                    self::$m_tmp['hosts_have_check_period'][] = $l_row['isys_obj__id'];
                } // if

                if ($l_attributes['notification_interval'] > 0)
                {
                    self::$m_tmp['hosts_have_notification_interval'][] = $l_row['isys_obj__id'];
                } // if

                if (!empty($l_attributes['notification_period']))
                {
                    self::$m_tmp['hosts_have_notification_period'][] = $l_row['isys_obj__id'];
                } // if

                self::$m_log->debug(' -> writing host template "' . $l_row['isys_catg_nagios_host_tpl_def_list__name1'] . '"');
                fputs($l_host_handle, self::prepare_config('host', $l_attributes, $l_mandatory, $l_row['isys_catg_nagios_host_tpl_def_list__custom_obj_vars']));
            } // while
        } // if

        fclose($l_host_handle);

        return $this;
    } // function

    /**
     * Method for exporting the nagios host groups.
     *
     * @return  isys_nagios_export
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function export_host_groups()
    {
        global $g_comp_database_system;

        $l_handle = fopen($this->m_export_dir . $this->m_export_subdir . 'hostgroups.cfg', 'w');

        self::$m_log->info('Exporting: host groups');
        $l_host_dao = isys_cmdb_dao_category_g_nagios::instance($this->m_database_component);

        $l_host_res = $this->m_nagios_component->getHostGroups();

        if (count($l_host_res) > 0)
        {
            $l_group_dao = isys_cmdb_dao_category_s_group::instance($this->m_database_component);

            while ($l_row = $l_host_res->get_row())
            {
                if ($l_row['isys_catg_nagios_group_list__is_exportable'] == 0)
                {
                    continue;
                } // if

                $l_members = [];

                $l_group_type = $l_group_dao->get_group_type($l_row['isys_obj__id']);

                // @todo  There should be a single method "get_group_objects" which retrieves the object IDs according to the set type!
                if ($l_group_type == 0)
                {
                    $l_hostgroup_member_res = $l_group_dao->get_data(null, $l_row['isys_obj__id'], null, null, C__RECORD_STATUS__NORMAL);

                    if (count($l_hostgroup_member_res) > 0)
                    {
                        while ($l_hostgroup_member_row = $l_hostgroup_member_res->get_row())
                        {
                            $l_host = $l_host_dao->get_data(null, $l_hostgroup_member_row['connected_id'], null, null, C__RECORD_STATUS__NORMAL)
                                ->get_row();

                            if ($l_host === false || $l_host['isys_catg_nagios_list__is_exportable'] == 0 || $l_host['isys_obj__status'] != C__RECORD_STATUS__NORMAL)
                            {
                                continue;
                            } // if

                            $l_members[] = isys_nagios_helper::render_export_hostname($l_host['isys_obj__id']);
                        } // while
                    } // if
                }
                else
                {
                    $l_group_data = isys_cmdb_dao_category_s_group_type::instance($this->m_database_component)
                        ->get_data(null, $l_row['isys_obj__id'], null, null, C__RECORD_STATUS__NORMAL)
                        ->get_row();

                    $l_report_data = isys_report_dao::instance($g_comp_database_system)
                        ->get_report($l_group_data['isys_cats_group_type_list__isys_report__id']);

                    if (!empty($l_report_data['isys_report__query']))
                    {
                        $l_sql = 'SELECT obj_main.isys_obj__id AS __id__, obj_main.isys_obj__title, objtype.isys_obj_type__title ';
                        $l_sql .= substr(
                            $l_report_data['isys_report__query'],
                            strpos($l_report_data['isys_report__query'], 'FROM'),
                            strlen($l_report_data['isys_report__query'])
                        );
                        $l_sql = str_replace(
                            'isys_obj AS obj_main',
                            'isys_obj AS obj_main INNER JOIN isys_obj_type AS objtype ON objtype.isys_obj_type__id = obj_main.isys_obj__isys_obj_type__id ',
                            $l_sql
                        );

                        $l_group_res = $l_group_dao->retrieve($l_sql);

                        if (count($l_group_res))
                        {
                            $l_found_ids = true;

                            while ($l_group_row = $l_group_res->get_row())
                            {
                                if (!isset($l_group_row['__id__']))
                                {
                                    $l_found_ids = false;
                                    continue;
                                } // if

                                $l_host = $l_host_dao->get_data(null, $l_group_row['__id__'], null, null, C__RECORD_STATUS__NORMAL)
                                    ->get_row();

                                if ($l_host === false || $l_host['isys_catg_nagios_list__is_exportable'] == 0 || $l_host['isys_obj__status'] != C__RECORD_STATUS__NORMAL)
                                {
                                    continue;
                                } // if

                                $l_members[] = isys_nagios_helper::render_export_hostname($l_host['isys_obj__id']);
                            } // while

                            if (!$l_found_ids)
                            {
                                self::$m_log->warning(
                                    'The selected report "' . $l_report_data['isys_report__title'] . '" needs to select the object ID in the "__id__" field '
                                );
                            } // if
                        } // if
                    } // if
                } // if

                $l_hostgroup_name = $l_row['isys_catg_nagios_group_list__name'];

                if ($l_row['isys_catg_nagios_group_list__name_selection'] == C__CATG_NAGIOS__NAME_SELECTION__OBJ_ID)
                {
                    $l_hostgroup_name = $l_row['isys_obj__title'];
                } // if

                if (count($l_members) > 0)
                {
                    $l_attributes = [
                        'hostgroup_name' => $l_hostgroup_name,
                        'alias'          => $l_row['isys_catg_nagios_group_list__alias'],
                        'members'        => implode(',', $l_members),
                        'notes'          => $l_row['isys_catg_nagios_group_list__notes'],
                        'notes_url'      => $l_row['isys_catg_nagios_group_list__notes_url'],
                        'action_url'     => $l_row['isys_catg_nagios_group_list__action_url']
                    ];

                    self::$m_log->debug(' -> writing hostgroup "' . $l_attributes['hostgroup_name'] . '" (alias "' . $l_attributes['alias'] . '")');
                    fputs(
                        $l_handle,
                        self::prepare_config(
                            'hostgroup',
                            $l_attributes,
                            [
                                'hostgroup_name',
                                'alias'
                            ]
                        )
                    );
                }
                else
                {
                    self::$m_log->warning(' -> hostgroup "' . $l_hostgroup_name . '" (alias "' . $l_row['isys_catg_nagios_group_list__alias'] . '") has 0 members!');
                } // if
            } // while
        } // if

        fclose($l_handle);

        return $this;
    } // function

    /**
     * Method for exporting the nagios host dependencies.
     *
     * @todo    This needs to be completely renewed.
     * @return  isys_nagios_export
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function export_host_dependencies()
    {
        return $this;

//		self::$m_log->info('Exporting: host dependencies');
//		$l_handle = fopen($this->m_export_dir . $this->m_export_subdir . 'hostdependencies.cfg', 'w');
//
//		$l_deps_res = $this->m_nagios_component->getHostDepedencies();
//
//		if (count($l_deps_res) > 0)
//		{
//			while ($l_row = $l_deps_res->get_row())
//			{
//				$l_depPeriod = $this->m_nagios_component->getTimeperiod($l_row['isys_nagios_dependency__dependency_period']);
//
//				$l_attributes = array(
//					'inherits_parent' => $l_row['isys_nagios_dependency__inherits_parent'],
//					'execution_failure_criteria' => $l_row['isys_nagios_dependency__execution_failure_criteria'],
//					'notification_failure_criteria' => $l_row['isys_nagios_dependency__notification_failure_criteria'],
//					'dependency_period' => $l_depPeriod['name']
//				);
//
//				if ($l_row['master_type'] == 'host' && $l_row['slave_type'] == 'host')
//				{
//					if (in_array($l_row['slave'], self::$m_tmp['added_hosts']) === false || in_array($l_row['master'], self::$m_tmp['added_hosts']) === false)
//					{
//						continue;
//					} // if
//
//					$l_attributes['dependent_host_name'] = $l_row['slave'];
//					$l_attributes['host_name'] = $l_row['master'];
//				}
//				else if ($l_row['master_type'] == 'host' && $l_row['slave_type'] == 'group')
//				{
//					if (in_array($l_row['slave'], self::$m_tmp['added_hostgroups']) === false || in_array($l_row['master'], self::$m_tmp['added_hosts']) === false)
//					{
//						continue;
//					} // if
//
//					$l_attributes['dependent_hostgroup_name'] = isys_nagios_helper::prepare_valid_name($l_row['slave']);
//					$l_attributes['host_name'] = $l_row['master'];
//				}
//				else if ($l_row['master_type'] == 'group' && $l_row['slave_type'] == 'host')
//				{
//					if (in_array($l_row['slave'], self::$m_tmp['added_hosts']) === false || in_array($l_row['master'], self::$m_tmp['added_hostgroups']) === false)
//					{
//						continue;
//					} // if
//
//					$l_attributes['dependent_host_name'] = $l_row['slave'];
//					$l_attributes['host_name'] = isys_nagios_helper::prepare_valid_name($l_row['master']);
//				}
//				else if ($l_row['master_type'] == 'group' && $l_row['slave_type'] == 'group')
//				{
//					if (in_array($l_row['slave'], self::$m_tmp['added_hostgroups']) === false || in_array($l_row['master'], self::$m_tmp['added_hostgroups']) === false)
//					{
//						continue;
//					} // if
//
//					$l_attributes['dependent_hostgroup_name'] = isys_nagios_helper::prepare_valid_name($l_row['slave']);
//					$l_attributes['hostgroup_name'] = isys_nagios_helper::prepare_valid_name($l_row['master']);
//				} // if
//
//				self::$m_log->debug(' -> writing hostdependency ".."');
//				fputs($l_handle, self::prepare_config('hostdependency', $l_attributes, array('dependent_host_name', 'host_name')));
//			} // while
//		} // if
//
//		fclose($l_handle);
//
//		return $this;
    } // function

    /**
     * Method for exporting the nagios service templates.
     *
     * @return  isys_nagios_export
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function export_service_templates()
    {
        $l_handle = fopen($this->m_export_dir . $this->m_export_subdir . 'services.cfg', 'w');

        self::$m_log->info('Exporting: service templates');

        $l_dao = isys_cmdb_dao::instance($this->m_database_component);

        $l_service_tpl_res = isys_cmdb_dao_category_g_nagios_service_tpl_def::instance($this->m_database_component)
            ->get_data(null, null, ' AND isys_catg_nagios_service_tpl_def_list__is_exportable = 1 ', null, C__RECORD_STATUS__NORMAL);

        if (count($l_service_tpl_res) > 0)
        {
            while ($l_row = $l_service_tpl_res->get_row())
            {
                // We do not want to export archived, deleted or template-objects.
                if ($l_row['isys_obj__status'] != C__RECORD_STATUS__NORMAL)
                {
                    continue;
                } // if

                $l_contacts     = $l_contact_groups = [];
                $l_display_name = $l_row['isys_catg_nagios_service_tpl_def_list__display_name'];

                if ($l_row['isys_catg_nagios_service_tpl_def_list__display_name_selection'] == C__CATG_NAGIOS__NAME_SELECTION__OBJ_ID)
                {
                    $l_display_name = $l_dao->get_obj_name_by_id_as_string($l_row['isys_connection__isys_obj__id']);
                } // switch

                $l_contact_res = $this->m_nagios_component->getNagiosContacts($l_row['isys_obj__id']);

                if (count($l_contact_res) > 0)
                {
                    while ($l_contact_row = $l_contact_res->get_row())
                    {
                        if ($l_contact_row['isys_obj_type__const'] == 'C__OBJTYPE__PERSON' && in_array($l_contact_row['isys_obj__title'], self::$m_tmp['added_contacts']))
                        {
                            $l_contacts[] = isys_nagios_helper::prepare_valid_name(isys_nagios_helper::get_correct_contact_name($l_contact_row['isys_obj__id']));

                            if (!in_array($l_row['isys_obj__id'], self::$m_tmp['services_have_contacts']))
                            {
                                self::$m_tmp['services_have_contacts'][] = $l_row['isys_obj__id'];
                            } // if
                        }
                        else if ($l_contact_row['isys_obj_type__const'] == 'C__OBJTYPE__PERSON_GROUP' && in_array(
                                $l_contact_row['isys_obj__title'],
                                self::$m_tmp['added_contactsgroups']
                            )
                        )
                        {
                            $l_contact_groups[] = isys_nagios_helper::prepare_valid_name($l_contact_row['isys_obj__title']);

                            if (!in_array($l_row['isys_obj__id'], self::$m_tmp['services_have_contact_groups']))
                            {
                                self::$m_tmp['services_have_contact_groups'][] = $l_row['isys_obj__id'];
                            } // if
                        } // if
                    } // while
                } // if

                $l_attributes = [
                    'host_name'                    => '',
                    'hostgroup_name'               => '',
                    'name'                         => $l_row['isys_catg_nagios_service_tpl_def_list__name'],
                    'service_description'          => $l_row['isys_catg_nagios_service_tpl_def_list__service_description'],
                    'display_name'                 => $l_display_name,
                    'servicegroups'                => '',
                    'is_volatile'                  => $l_row['isys_catg_nagios_service_tpl_def_list__is_volatile'],
                    'check_command'                => isys_nagios_helper::get_command(
                        $l_row['isys_catg_nagios_service_tpl_def_list__check_command'],
                        $l_row['isys_catg_nagios_service_tpl_def_list__check_command_plus'],
                        $l_row['isys_catg_nagios_service_tpl_def_list__check_command_parameters']
                    ),
                    'initial_state'                => $l_row['isys_catg_nagios_service_tpl_def_list__initial_state'],
                    'max_check_attempts'           => $l_row['isys_catg_nagios_service_tpl_def_list__max_check_attempts'],
                    'check_interval'               => $l_row['isys_catg_nagios_service_tpl_def_list__check_interval'],
                    'retry_interval'               => $l_row['isys_catg_nagios_service_tpl_def_list__retry_interval'],
                    'active_checks_enabled'        => $l_row['isys_catg_nagios_service_tpl_def_list__active_checks_enabled'],
                    'passive_checks_enabled'       => $l_row['isys_catg_nagios_service_tpl_def_list__passive_checks_enabled'],
                    'check_period'                 => isys_nagios_helper::get_timeperiod(
                        $l_row['isys_catg_nagios_service_tpl_def_list__check_period'],
                        $l_row['isys_catg_nagios_service_tpl_def_list__check_period_plus']
                    ),
                    'obsess_over_service'          => $l_row['isys_catg_nagios_service_tpl_def_list__obsess_over_service'],
                    'check_freshness'              => $l_row['isys_catg_nagios_service_tpl_def_list__check_freshness'],
                    'freshness_threshold'          => $l_row['isys_catg_nagios_service_tpl_def_list__freshness_threshold'],
                    'event_handler'                => isys_nagios_helper::get_command(
                        $l_row['isys_catg_nagios_service_tpl_def_list__event_handler'],
                        $l_row['isys_catg_nagios_service_tpl_def_list__event_handler_plus']
                    ),
                    'event_handler_enabled'        => $l_row['isys_catg_nagios_service_tpl_def_list__event_handler_enabled'],
                    'low_flap_threshold'           => $l_row['isys_catg_nagios_service_tpl_def_list__low_flap_threshold'],
                    'high_flap_threshold'          => $l_row['isys_catg_nagios_service_tpl_def_list__high_flap_threshold'],
                    'flap_detection_enabled'       => $l_row['isys_catg_nagios_service_tpl_def_list__flap_detection_enabled'],
                    'flap_detection_options'       => $l_row['isys_catg_nagios_service_tpl_def_list__flap_detection_options'],
                    'process_perf_data'            => $l_row['isys_catg_nagios_service_tpl_def_list__process_perf_data'],
                    'retain_status_information'    => $l_row['isys_catg_nagios_service_tpl_def_list__retain_status_information'],
                    'retain_nonstatus_information' => $l_row['isys_catg_nagios_service_tpl_def_list__retain_nonstatus_information'],
                    'notification_interval'        => $l_row['isys_catg_nagios_service_tpl_def_list__notification_interval'],
                    'first_notification_delay'     => $l_row['isys_catg_nagios_service_tpl_def_list__first_notification_delay'],
                    'notification_period'          => isys_nagios_helper::get_timeperiod(
                        $l_row['isys_catg_nagios_service_tpl_def_list__notification_period'],
                        $l_row['isys_catg_nagios_service_tpl_def_list__notification_period_plus']
                    ),
                    'notification_options'         => $l_row['isys_catg_nagios_service_tpl_def_list__notification_options'],
                    'notifications_enabled'        => $l_row['isys_catg_nagios_service_tpl_def_list__notifications_enabled'],
                    'contacts'                     => implode(',', $l_contacts),
                    'contact_groups'               => implode(',', $l_contact_groups),
                    'stalking_options'             => $l_row['isys_catg_nagios_service_tpl_def_list__stalking_options'],
                    'notes'                        => $l_row['isys_catg_nagios_service_tpl_def_list__notes'],
                    'notes_url'                    => $l_row['isys_catg_nagios_service_tpl_def_list__notes_url'],
                    'action_url'                   => $l_row['isys_catg_nagios_service_tpl_def_list__action_url'],
                    'icon_image'                   => $l_row['isys_catg_nagios_service_tpl_def_list__icon_image'],
                    'icon_image_alt'               => $l_row['isys_catg_nagios_service_tpl_def_list__icon_image_alt'],
                    'register'                     => 0
                ];

                $l_mandatory = [
                    'name',
                ];

                if (!empty($l_attributes['check_command']))
                {
                    self::$m_tmp['services_have_check_commands'][] = $l_row['isys_obj__id'];
                } // if

                if ($l_attributes['max_check_attempts'] > 0)
                {
                    self::$m_tmp['services_have_max_check_attempts'][] = $l_row['isys_obj__id'];
                } // if

                if ($l_attributes['check_interval'] > 0)
                {
                    self::$m_tmp['services_have_check_interval'][] = $l_row['isys_obj__id'];
                } // if

                if ($l_attributes['retry_interval'] > 0)
                {
                    self::$m_tmp['services_have_retry_interval'][] = $l_row['isys_obj__id'];
                } // if

                if (!empty($l_attributes['check_period']))
                {
                    self::$m_tmp['services_have_check_period'][] = $l_row['isys_obj__id'];
                } // if

                if ($l_attributes['notification_interval'] > 0)
                {
                    self::$m_tmp['services_have_notification_interval'][] = $l_row['isys_obj__id'];
                } // if

                if (!empty($l_attributes['notification_period']))
                {
                    self::$m_tmp['services_have_notification_period'][] = $l_row['isys_obj__id'];
                } // if

                self::$m_log->debug(' -> writing service template "' . $l_attributes['name'] . '"');
                fputs($l_handle, self::prepare_config('service', $l_attributes, $l_mandatory, $l_row['isys_catg_nagios_service_tpl_def_list__custom_obj_vars']));
            } // while
        } // if

        fclose($l_handle);

        return $this;
    } // function

    /**
     * Method for exporting the nagios services.
     *
     * @return  isys_nagios_export
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function export_services()
    {
        $l_sHandle = fopen($this->m_export_dir . $this->m_export_subdir . 'services.cfg', 'a');

        self::$m_log->info('Exporting: services');

        /**
         * @var $l_dao isys_cmdb_dao_object_type
         */
        $l_dao                 = isys_cmdb_dao_object_type::instance($this->m_database_component);
        $l_group_dao           = isys_cmdb_dao_category_s_group::instance($this->m_database_component);
        $l_daoNagiosServiceTpl = isys_cmdb_dao_category_g_nagios_service_tpl_def::instance($this->m_database_component);
        $l_service_res         = isys_cmdb_dao_category_g_nagios_service_def::instance($this->m_database_component)
            ->get_data(null, null, ' AND isys_catg_nagios_service_def_list__is_exportable = 1 ', null, C__RECORD_STATUS__NORMAL);

        if (count($l_service_res) > 0)
        {
            while ($l_row = $l_service_res->get_row())
            {
                // We do not want to export archived, deleted or template-objects.
                if ($l_row['isys_obj__status'] != C__RECORD_STATUS__NORMAL)
                {
                    continue;
                } // if

                switch ($l_row['isys_catg_nagios_service_def_list__display_name_selection'])
                {
                    case C__CATG_NAGIOS__NAME_SELECTION__OBJ_ID:
                        $l_display_name = $l_dao->get_obj_name_by_id_as_string($l_row['isys_connection__isys_obj__id']);
                        break;

                    default:
                        $l_display_name = $l_row['isys_catg_nagios_service_def_list__display_name'];
                } // switch

                $l_contacts    = $l_contact_groups = [];
                $l_contact_res = $this->m_nagios_component->getNagiosContacts($l_row['isys_catg_nagios_service_def_list__isys_obj__id']);

                if (count($l_contact_res) > 0)
                {
                    while ($l_contact_row = $l_contact_res->get_row())
                    {
                        if ($l_contact_row['isys_obj_type__const'] == 'C__OBJTYPE__PERSON' && in_array($l_contact_row['isys_obj__title'], self::$m_tmp['added_contacts']))
                        {
                            $l_contacts[] = isys_nagios_helper::prepare_valid_name(isys_nagios_helper::get_correct_contact_name($l_contact_row['isys_obj__id']));
                        }
                        else if ($l_contact_row['isys_obj_type__const'] == 'C__OBJTYPE__PERSON_GROUP' && in_array(
                                $l_contact_row['isys_obj__title'],
                                self::$m_tmp['added_contactsgroups']
                            )
                        )
                        {
                            $l_contact_groups[] = isys_nagios_helper::prepare_valid_name($l_contact_row['isys_obj__title']);
                        } // if
                    } // while
                } // if

                // Get all hosts, which have this service assigned.
                $l_hosts         = [];
                $l_service_hosts = isys_cmdb_dao_category_g_nagios_refs_services_backwards::instance($this->m_database_component)
                    ->get_data(null, $l_row['isys_obj__id'], null, null, C__RECORD_STATUS__NORMAL);

                $l_dao_app = isys_cmdb_dao_category_s_application_assigned_obj::instance($this->m_database_component);

                while ($l_host_row = $l_service_hosts->get_row())
                {
                    if ($l_dao->has_cat($l_host_row['isys_obj__isys_obj_type__id'], 'C__CATG__NAGIOS_APPLICATION_FOLDER'))
                    {
                        $l_app_assignment_res = $l_dao_app->get_data(null, $l_host_row['isys_obj__id'], null, null, C__RECORD_STATUS__NORMAL);

                        if (count($l_app_assignment_res) > 0)
                        {
                            while ($l_host_row2 = $l_app_assignment_res->get_row())
                            {
                                if ($l_host_row2['isys_catg_application_list__bequest_nagios_services'] == 0)
                                {
                                    continue;
                                } // if

                                $l_inheritance_sql = 'SELECT *
									FROM isys_catg_nagios_service_inheritance
									WHERE isys_catg_nagios_service_inheritance__host__isys_obj__id = ' . $l_dao->convert_sql_id(
                                        $l_host_row2['isys_catg_application_list__isys_obj__id']
                                    ) . '
									AND isys_catg_nagios_service_inheritance__service__isys_obj__id = ' . $l_dao->convert_sql_id($l_row['isys_obj__id']) . ';';

                                if (count($l_dao->retrieve($l_inheritance_sql)))
                                {
                                    continue;
                                } // if

                                $l_hostname = isys_nagios_helper::render_export_hostname($l_host_row2['isys_catg_application_list__isys_obj__id']);

                                if (in_array($l_hostname, self::$m_tmp['added_hosts']))
                                {
                                    $l_hosts[] = $l_hostname;
                                } // if
                            } // while
                        } // if
                    }
                    else
                    {
                        $l_hostname = isys_nagios_helper::render_export_hostname($l_host_row['isys_obj__id']);

                        if (in_array($l_hostname, self::$m_tmp['added_hosts']))
                        {
                            $l_hosts[] = $l_hostname;
                        } // if
                    } // if
                } // while

                // Just in case we've got several same or empty items...
                $l_hosts = array_filter(array_unique($l_hosts));

                // Retrieve all service groups, the current service is assigned to.
                $l_service_groups = [];

                /* @var  $l_nagios_group_dao  isys_cmdb_dao_category_g_nagios_group */
                // This code is commented out because the reference is double (once in 'service.cfg' and once in 'servicegroup.cfg'). See RT #16451.
                /*
                $l_nagios_group_dao = isys_cmdb_dao_category_g_nagios_group::instance($this->m_database_component);
                $l_group_res = $l_group_dao->get_data(null, null, ' AND other.isys_obj__id = ' . $l_group_dao->convert_sql_id($l_row['isys_obj__id']));

                if (count($l_group_res))
                {
                    while ($l_group_row = $l_group_res->get_row())
                    {
                        $l_nagios_group_row = $l_nagios_group_dao->get_data(null, $l_group_row['isys_obj__id'])->get_row();

                        if (is_array($l_nagios_group_row))
                        {
                            $l_servicegroup_name = $l_nagios_group_row['isys_catg_nagios_group_list__name'];

                            if ($l_nagios_group_row['isys_catg_nagios_group_list__name_selection'] == C__CATG_NAGIOS__NAME_SELECTION__OBJ_ID)
                            {
                                $l_servicegroup_name = $l_nagios_group_row['isys_obj__title'];
                            } // if

                            $l_service_groups[] = $l_servicegroup_name;
                        } // if
                    } // while
                } // if
                */

                $l_attributes = [
                    'host_name'                    => implode(',', $l_hosts),
                    'hostgroup_name'               => '',
                    'service_description'          => $l_row['isys_catg_nagios_service_def_list__service_description'],
                    'display_name'                 => $l_display_name,
                    'servicegroups'                => implode(',', $l_service_groups),
                    'is_volatile'                  => $l_row['isys_catg_nagios_service_def_list__is_volatile'],
                    'check_command'                => isys_nagios_helper::get_command(
                        $l_row['isys_catg_nagios_service_def_list__check_command'],
                        $l_row['isys_catg_nagios_service_def_list__check_command_plus'],
                        $l_row['isys_catg_nagios_service_def_list__check_command_parameters']
                    ),
                    'initial_state'                => $l_row['isys_catg_nagios_service_def_list__initial_state'],
                    'max_check_attempts'           => $l_row['isys_catg_nagios_service_def_list__max_check_attempts'],
                    'check_interval'               => $l_row['isys_catg_nagios_service_def_list__check_interval'],
                    'retry_interval'               => $l_row['isys_catg_nagios_service_def_list__retry_interval'],
                    'active_checks_enabled'        => $l_row['isys_catg_nagios_service_def_list__active_checks_enabled'],
                    'passive_checks_enabled'       => $l_row['isys_catg_nagios_service_def_list__passive_checks_enabled'],
                    'check_period'                 => isys_nagios_helper::get_timeperiod(
                        $l_row['isys_catg_nagios_service_def_list__check_period'],
                        $l_row['isys_catg_nagios_service_def_list__check_period_plus']
                    ),
                    'obsess_over_service'          => $l_row['isys_catg_nagios_service_def_list__obsess_over_service'],
                    'check_freshness'              => $l_row['isys_catg_nagios_service_def_list__check_freshness'],
                    'freshness_threshold'          => $l_row['isys_catg_nagios_service_def_list__freshness_threshold'],
                    'event_handler'                => isys_nagios_helper::get_command(
                        $l_row['isys_catg_nagios_service_def_list__event_handler'],
                        $l_row['isys_catg_nagios_service_def_list__event_handler_plus']
                    ),
                    'event_handler_enabled'        => $l_row['isys_catg_nagios_service_def_list__event_handler_enabled'],
                    'low_flap_threshold'           => $l_row['isys_catg_nagios_service_def_list__low_flap_threshold'],
                    'high_flap_threshold'          => $l_row['isys_catg_nagios_service_def_list__high_flap_threshold'],
                    'flap_detection_enabled'       => $l_row['isys_catg_nagios_service_def_list__flap_detection_enabled'],
                    'flap_detection_options'       => $l_row['isys_catg_nagios_service_def_list__flap_detection_options'],
                    'process_perf_data'            => $l_row['isys_catg_nagios_service_def_list__process_perf_data'],
                    'retain_status_information'    => $l_row['isys_catg_nagios_service_def_list__retain_status_information'],
                    'retain_nonstatus_information' => $l_row['isys_catg_nagios_service_def_list__retain_nonstatus_information'],
                    'notification_interval'        => $l_row['isys_catg_nagios_service_def_list__notification_interval'],
                    'first_notification_delay'     => $l_row['isys_catg_nagios_service_def_list__first_notification_delay'],
                    'notification_period'          => isys_nagios_helper::get_timeperiod(
                        $l_row['isys_catg_nagios_service_def_list__notification_period'],
                        $l_row['isys_catg_nagios_service_def_list__notification_period_plus']
                    ),
                    'notification_options'         => $l_row['isys_catg_nagios_service_def_list__notification_options'],
                    'notifications_enabled'        => $l_row['isys_catg_nagios_service_def_list__notifications_enabled'],
                    'contacts'                     => implode(',', $l_contacts),
                    'contact_groups'               => implode(',', $l_contact_groups),
                    'stalking_options'             => $l_row['isys_catg_nagios_service_def_list__stalking_options'],
                    'notes'                        => $l_row['isys_catg_nagios_service_def_list__notes'],
                    'notes_url'                    => $l_row['isys_catg_nagios_service_def_list__notes_url'],
                    'action_url'                   => $l_row['isys_catg_nagios_service_def_list__action_url'],
                    'icon_image'                   => $l_row['isys_catg_nagios_service_def_list__icon_image'],
                    'icon_image_alt'               => $l_row['isys_catg_nagios_service_def_list__icon_image_alt']
                ];

                $l_mandatory = [
                    'host_name',
                    'service_description',
                    'check_command',
                    'max_check_attempts',
                    'check_interval',
                    'retry_interval',
                    'check_period',
                    'notification_interval',
                    'notification_period',
                    'contacts',
                    'contact_groups'
                ];

                // If we choose to use a template for this service.
                if (!empty($l_row['isys_catg_nagios_service_def_list__service_template']))
                {
                    $l_templates = $l_tpl_selection = [];

                    if (isys_format_json::is_json($l_row['isys_catg_nagios_service_def_list__service_template']))
                    {
                        $l_tpl_selection = isys_format_json::decode($l_row['isys_catg_nagios_service_def_list__service_template'], true);
                    } // if

                    // This can happen, when only one template is selected.
                    if (!is_array($l_tpl_selection))
                    {
                        $l_tpl_selection = [$l_tpl_selection];
                    } // if

                    if (is_array($l_tpl_selection))
                    {
                        foreach ($l_tpl_selection as $l_tpl)
                        {
                            $l_tpl_row     = $l_daoNagiosServiceTpl->get_data(null, $l_tpl, '', null, C__RECORD_STATUS__NORMAL)
                                ->get_row();
                            $l_templates[] = $l_tpl_row['isys_catg_nagios_service_tpl_def_list__name'];

                            // Here we check, if the current template already handles the mandatory fields.
                            if (in_array($l_tpl, self::$m_tmp['services_have_check_commands']))
                            {
                                self::$m_log->debug('Template "' . $l_tpl_row['isys_catg_nagios_service_tpl_def_list__name'] . '" already handles check_commands');
                                unset($l_mandatory[2]);
                            } // if

                            if (in_array($l_tpl, self::$m_tmp['services_have_max_check_attempts']))
                            {
                                self::$m_log->debug('Template "' . $l_tpl_row['isys_catg_nagios_service_tpl_def_list__name'] . '" already handles max_check_attempts');
                                unset($l_mandatory[3]);
                            } // if

                            if (in_array($l_tpl, self::$m_tmp['services_have_check_interval']))
                            {
                                self::$m_log->debug('Template "' . $l_tpl_row['isys_catg_nagios_service_tpl_def_list__name'] . '" already handles check_interval');
                                unset($l_mandatory[4]);
                            } // if

                            if (in_array($l_tpl, self::$m_tmp['services_have_retry_interval']))
                            {
                                self::$m_log->debug('Template "' . $l_tpl_row['isys_catg_nagios_service_tpl_def_list__name'] . '" already handles retry_interval');
                                unset($l_mandatory[5]);
                            } // if

                            if (in_array($l_tpl, self::$m_tmp['services_have_check_period']))
                            {
                                self::$m_log->debug('Template "' . $l_tpl_row['isys_catg_nagios_service_tpl_def_list__name'] . '" already handles check_period');
                                unset($l_mandatory[6]);
                            } // if

                            if (in_array($l_tpl, self::$m_tmp['services_have_check_period']))
                            {
                                self::$m_log->debug('Template "' . $l_tpl_row['isys_catg_nagios_service_tpl_def_list__name'] . '" already handles check_period');
                                unset($l_mandatory[6]);
                            } // if

                            if (in_array($l_tpl, self::$m_tmp['services_have_notification_interval']))
                            {
                                self::$m_log->debug('Template "' . $l_tpl_row['isys_catg_nagios_service_tpl_def_list__name'] . '" already handles notification_interval');
                                unset($l_mandatory[7]);
                            } // if

                            if (in_array($l_tpl, self::$m_tmp['services_have_notification_period']))
                            {
                                self::$m_log->debug('Template "' . $l_tpl_row['isys_catg_nagios_service_tpl_def_list__name'] . '" already handles notification_period');
                                unset($l_mandatory[8]);
                            } // if

                            if (in_array($l_tpl, self::$m_tmp['services_have_contacts']))
                            {
                                self::$m_log->debug('Template "' . $l_tpl_row['isys_catg_nagios_service_tpl_def_list__name'] . '" already handles contacts');
                                unset($l_mandatory[9], $l_mandatory[10]);
                            } // if

                            if (in_array($l_tpl, self::$m_tmp['services_have_contact_groups']))
                            {
                                self::$m_log->debug('Template "' . $l_tpl_row['isys_catg_nagios_service_tpl_def_list__name'] . '" already handles contact_groups');
                                unset($l_mandatory[9], $l_mandatory[10]);
                            } // if
                        } // foreach
                    } // if

                    if (count($l_templates) > 0)
                    {
                        $l_attributes['use'] = implode(',', $l_templates);
                    } // if
                } // if

                if (!empty($l_attributes['contacts']) || !empty($l_attributes['contact_groups']))
                {
                    unset($l_mandatory[9], $l_mandatory[10]);
                } // if

                self::$m_log->debug(' -> writing service "' . $l_attributes['service_description'] . '"');
                fputs($l_sHandle, self::prepare_config('service', $l_attributes, $l_mandatory, $l_row['isys_catg_nagios_service_def_list__custom_obj_vars']));

                // Write all escalations of this service.
                if (!empty($l_row['isys_catg_nagios_service_def_list__escalations']))
                {
                    $l_arEscalations = explode(',', $l_row['isys_catg_nagios_service_def_list__escalations']);

                    foreach ($l_arEscalations as $l_esc)
                    {
                        self::$m_tmp['service_escalations'][$l_esc][$l_row['isys_catg_nagios_service_def_list__service_description']]['hosts'] = implode(',', $l_hosts);
                    } // foreach
                } // if
            } // while
        } // if

        fclose($l_sHandle);

        return $this;
    } // function

    /**
     * Method for exporting the nagios service groups.
     *
     * @return  isys_nagios_export
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function export_service_groups()
    {
        global $g_comp_database_system;

        $l_handle         = fopen($this->m_export_dir . $this->m_export_subdir . 'servicegroups.cfg', 'w');
        $l_found_services = [];

        self::$m_log->info('Exporting: service groups');

        $l_servicegroup_res = $this->m_nagios_component->getServiceGroups();

        if (count($l_servicegroup_res) > 0)
        {
            $l_relation_dao = isys_cmdb_dao_category_g_relation::instance($this->m_database_component);
            $l_group_dao    = isys_cmdb_dao_category_s_group::instance($this->m_database_component);
            $l_host_dao     = isys_cmdb_dao_category_g_nagios::instance($this->m_database_component);
            $l_service_dao  = isys_cmdb_dao_category_g_nagios_service_def::instance($this->m_database_component);

            while ($l_row = $l_servicegroup_res->get_row())
            {
                if ($l_row['isys_catg_nagios_group_list__is_exportable'] == 0)
                {
                    continue;
                } // if

                $l_members = [];

                $l_group_type = $l_group_dao->get_group_type($l_row['isys_obj__id']);

                // @todo  There should be a single method "get_group_objects" which retrieves the object IDs according to the set type!
                if ($l_group_type == 0)
                {
                    $l_group_member_res = $l_group_dao->get_data(null, $l_row['isys_obj__id'], null, null, C__RECORD_STATUS__NORMAL);

                    if (count($l_group_member_res) > 0)
                    {
                        while ($l_group_member_row = $l_group_member_res->get_row())
                        {
                            $l_service_obj_id = $l_group_member_row['connected_id'];

                            if (in_array($l_service_obj_id, $l_found_services))
                            {
                                continue;
                            } // if

                            $l_found_services[] = $l_service_obj_id;

                            // First we'll retrieve the service data. If the given object has no service data, we skip this iteration.
                            $l_service = $l_service_dao->get_data(null, $l_service_obj_id, '', null, C__RECORD_STATUS__NORMAL)
                                ->get_row();

                            if ($l_service === false || $l_service['isys_catg_nagios_service_def_list__is_exportable'] == 0 || $l_service['isys_obj__status'] != C__RECORD_STATUS__NORMAL)
                            {
                                continue;
                            } // if

                            // Now we try to receive all hosts which "somehow" inherit the current service.
                            $l_hosts = $this->get_all_hosts_inheriting_service($l_service_obj_id);

                            if (count($l_hosts))
                            {
                                foreach ($l_hosts as $l_host)
                                {
                                    // Then we'll read the "master" object (server, etc.) because of the nagios category.
                                    $l_host_data = $l_host_dao->get_data(null, $l_host, '', null, C__RECORD_STATUS__NORMAL)
                                        ->get_row();

                                    if ($l_host_data === false || $l_host_data['isys_catg_nagios_list__is_exportable'] == 0 || $l_host_data['isys_obj__status'] != C__RECORD_STATUS__NORMAL)
                                    {
                                        continue;
                                    } // if

                                    $l_members[] = isys_nagios_helper::render_export_hostname($l_host);
                                    $l_members[] = $l_service['isys_catg_nagios_service_def_list__service_description'];
                                } // foreach
                            } // if
                        } // while
                    } // if
                }
                else
                {
                    $l_group_data = isys_cmdb_dao_category_s_group_type::instance($this->m_database_component)
                        ->get_data(null, $l_row['isys_obj__id'], null, null, C__RECORD_STATUS__NORMAL)
                        ->get_row();

                    $l_report_data = isys_report_dao::instance($g_comp_database_system)
                        ->get_report($l_group_data['isys_cats_group_type_list__isys_report__id']);

                    if (!empty($l_report_data['isys_report__query']))
                    {
                        $l_sql = 'SELECT obj_main.isys_obj__id AS __id__, obj_main.isys_obj__title, objtype.isys_obj_type__title ';
                        $l_sql .= substr(
                            $l_report_data['isys_report__query'],
                            strpos($l_report_data['isys_report__query'], 'FROM'),
                            strlen($l_report_data['isys_report__query'])
                        );
                        $l_sql = str_replace(
                            'isys_obj AS obj_main',
                            'isys_obj AS obj_main INNER JOIN isys_obj_type AS objtype ON objtype.isys_obj_type__id = obj_main.isys_obj__isys_obj_type__id ',
                            $l_sql
                        );

                        $l_group_res = $l_group_dao->retrieve($l_sql);

                        if (count($l_group_res))
                        {
                            $l_found_ids = true;

                            while ($l_group_row = $l_group_res->get_row())
                            {
                                if (!isset($l_group_row['__id__']))
                                {
                                    $l_found_ids = false;
                                    continue;
                                } // if

                                $l_service_obj_id = $l_group_row['__id__'];

                                if (in_array($l_service_obj_id, $l_found_services))
                                {
                                    continue;
                                } // if

                                $l_found_services[] = $l_service_obj_id;

                                // First we'll retrieve the service data. If the given object has no service data, we skip this iteration.
                                $l_service = $l_service_dao->get_data(null, $l_service_obj_id, '', null, C__RECORD_STATUS__NORMAL)
                                    ->get_row();

                                if ($l_service === false || $l_service['isys_catg_nagios_service_def_list__is_exportable'] == 0 || $l_service['isys_obj__status'] != C__RECORD_STATUS__NORMAL)
                                {
                                    continue;
                                } // if

                                // Now we try to receive all hosts which "somehow" inherit the current service.
                                $l_hosts = $this->get_all_hosts_inheriting_service($l_service_obj_id);

                                if (count($l_hosts))
                                {
                                    foreach ($l_hosts as $l_host)
                                    {
                                        // Then we'll read the "master" object (server, etc.) because of the nagios category.
                                        $l_host_data = $l_host_dao->get_data(null, $l_host, '', null, C__RECORD_STATUS__NORMAL)
                                            ->get_row();

                                        if ($l_host_data === false || $l_host_data['isys_catg_nagios_list__is_exportable'] == 0 || $l_host_data['isys_obj__status'] != C__RECORD_STATUS__NORMAL)
                                        {
                                            continue;
                                        } // if

                                        $l_members[] = isys_nagios_helper::render_export_hostname($l_host);
                                        $l_members[] = $l_service['isys_catg_nagios_service_def_list__service_description'];
                                    } // foreach
                                } // if
                            } // while

                            if (!$l_found_ids)
                            {
                                self::$m_log->warning(
                                    'The selected report "' . $l_report_data['isys_report__title'] . '" needs to select the object ID in the "__id__" field '
                                );
                            } // if
                        } // if
                    } // if
                } // if

                $l_group_member_res = $l_group_dao->get_data(null, $l_row['isys_obj__id'], null, null, C__RECORD_STATUS__NORMAL);

                if (count($l_group_member_res) > 0)
                {
                    while ($l_group_member_row = $l_group_member_res->get_row())
                    {
                        // We just want to select "software"-relation objects.
                        $l_relation = $l_relation_dao->get_data(
                            null,
                            null,
                            ' AND isys_catg_relation_list__isys_obj__id = ' . $l_host_dao->convert_sql_id($l_group_member_row['connected_id']),
                            null,
                            C__RECORD_STATUS__NORMAL
                        )
                            ->get_row();

                        if ($l_relation === false)
                        {
                            continue;
                        } // if

                        // Then we'll read the "master" object (server, etc.) because of the nagios category.
                        $l_host = $l_host_dao->get_data(null, $l_relation['isys_catg_relation_list__isys_obj__id__master'], '', null, C__RECORD_STATUS__NORMAL)
                            ->get_row();

                        if ($l_host === false || $l_host['isys_catg_nagios_list__is_exportable'] == 0 || $l_host['isys_obj__status'] != C__RECORD_STATUS__NORMAL)
                        {
                            continue;
                        } // if

                        $l_service = $l_service_dao->get_data(null, $l_relation['isys_catg_relation_list__isys_obj__id__slave'], '', null, C__RECORD_STATUS__NORMAL)
                            ->get_row();

                        if ($l_service === false || $l_service['isys_catg_nagios_service_def_list__is_exportable'] == 0 || $l_service['isys_obj__status'] != C__RECORD_STATUS__NORMAL)
                        {
                            continue;
                        } // if

                        $l_members[] = isys_nagios_helper::render_export_hostname($l_relation['isys_catg_relation_list__isys_obj__id__master']);
                        $l_members[] = $l_service['isys_catg_nagios_service_def_list__service_description'];
                    } // while
                } // if

                $l_servicegroup_name = $l_row['isys_catg_nagios_group_list__name'];

                if ($l_row['isys_catg_nagios_group_list__name_selection'] == C__CATG_NAGIOS__NAME_SELECTION__OBJ_ID)
                {
                    $l_servicegroup_name = $l_row['isys_obj__title'];
                } // if

                if (count($l_members) > 0)
                {
                    $l_attributes = [
                        'servicegroup_name'    => $l_servicegroup_name,
                        'alias'                => $l_row["isys_catg_nagios_group_list__alias"],
                        'members'              => implode(',', $l_members),
                        'servicegroup_members' => '',
                        'notes'                => $l_row["isys_catg_nagios_group_list__notes"],
                        'notes_url'            => $l_row["isys_catg_nagios_group_list__notes_url"],
                        'action_url'           => $l_row["isys_catg_nagios_group_list__action_url"],
                    ];

                    self::$m_log->debug(' -> writing service group "' . $l_attributes['servicegroup_name'] . '" (alias "' . $l_attributes['alias'] . '")');
                    fputs(
                        $l_handle,
                        self::prepare_config(
                            'servicegroup',
                            $l_attributes,
                            [
                                'servicegroup_name',
                                'alias'
                            ]
                        )
                    );
                }
                else
                {
                    self::$m_log->warning(' -> service group "' . $l_servicegroup_name . '" (alias "' . $l_row["isys_catg_nagios_group_list__alias"] . '") has 0 members');
                } // if
            } // while
        } // if

        fclose($l_handle);

        return $this;
    } // function

    /**
     * Method for exporting the service dependencies.
     *
     * @return  isys_nagios_export
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function export_service_dependencies()
    {
        $l_handle = fopen($this->m_export_dir . $this->m_export_subdir . 'servicedependencies.cfg', 'w');

        self::$m_log->info('Exporting: service dependencies');

        $l_service_dep_res = isys_cmdb_dao_category_g_nagios_service_dep::instance($this->m_database_component)
            ->get_data(null, null, '', null, C__RECORD_STATUS__NORMAL);

        if (count($l_service_dep_res) > 0)
        {
            while ($l_row = $l_service_dep_res->get_row())
            {
                if ($l_row['isys_obj__status'] != C__RECORD_STATUS__NORMAL)
                {
                    continue;
                } // if

                $l_dep_service = isys_cmdb_dao_category_g_nagios_service_def::instance($this->m_database_component)
                    ->get_data(null, $l_row['isys_catg_nagios_service_dep_list__isys_obj__id'])
                    ->get_row();

                $l_local_service = isys_cmdb_dao_category_g_nagios_service_def::instance($this->m_database_component)
                    ->get_data(null, $l_row['isys_catg_nagios_service_dep_list__service_dep_connection'])
                    ->get_row();

                $l_local_host = isys_cmdb_dao_category_g_nagios_refs_services::instance($this->m_database_component)
                    ->get_data($l_row['isys_catg_nagios_service_dep_list__host_connection'])
                    ->get_row();

                $l_dep_host = isys_cmdb_dao_category_g_nagios_refs_services::instance($this->m_database_component)
                    ->get_data($l_row['isys_catg_nagios_service_dep_list__host_dep_connection'])
                    ->get_row();

                // Check for objects, which don't inherit "normal" status.
                if ($l_local_service['isys_obj__status'] != C__RECORD_STATUS__NORMAL || $l_dep_service['isys_obj__status'] != C__RECORD_STATUS__NORMAL || $l_local_host['isys_obj__status'] != C__RECORD_STATUS__NORMAL || $l_dep_host['isys_obj__status'] != C__RECORD_STATUS__NORMAL)
                {
                    // Continue, because these field are mandatory.
                    continue;
                } // if

                $l_attributes = [
                    'dependent_host_name'           => isys_nagios_helper::render_export_hostname($l_dep_host['isys_obj__id']),
                    'dependent_hostgroup_name'      => '',
                    'dependent_service_description' => $l_dep_service['isys_catg_nagios_service_def_list__service_description'],
                    'host_name'                     => isys_nagios_helper::render_export_hostname($l_local_host['isys_obj__id']),
                    'hostgroup_name'                => '',
                    'service_description'           => $l_local_service['isys_catg_nagios_service_def_list__service_description'],
                    'inherits_parent'               => $l_row['isys_catg_nagios_service_dep_list__inherits_parent'],
                    'execution_failure_criteria'    => $l_row['isys_catg_nagios_service_dep_list__exec_fail_criteria'],
                    'notification_failure_criteria' => $l_row['isys_catg_nagios_service_dep_list__notif_fail_criteria'],
                    'dependency_period'             => isys_nagios_helper::get_timeperiod(
                        $l_row['isys_catg_nagios_service_dep_list__dep_period'],
                        $l_row['isys_catg_nagios_service_dep_list__dep_period_plus']
                    )
                ];

                $l_mandatory = [
                    'dependent_host_name',
                    'dependent_service_description',
                    'host_name',
                    'service_description'
                ];

                self::$m_log->debug(' -> writing dependency "' . $l_attributes['servicegroup_name'] . '" (alias "' . $l_attributes['alias'] . '")');
                fputs($l_handle, self::prepare_config('servicedependency', $l_attributes, $l_mandatory));
            } // while
        } // if

        fclose($l_handle);

        return $this;
    } // function

    /**
     * Private clone method - Singleton!
     */
    private function __clone()
    {
        ;
    } // function

    /**
     * Retrieves all hosts, that are "somehow" connected to a certain service.
     *
     * @param   integer $p_service_obj_id
     *
     * @return  array
     * @throws  isys_exception_general
     */
    private function get_all_hosts_inheriting_service($p_service_obj_id)
    {
        /**
         * @var  $l_dao               isys_cmdb_dao_category_g_nagios_refs_services_backwards
         * @var  $l_cmdb_dao          isys_cmdb_dao
         * @var  $l_installation_dao  isys_cmdb_dao_category_s_application_assigned_obj
         */
        $l_dao              = isys_cmdb_dao_category_g_nagios_refs_services_backwards::instance($this->m_database_component);
        $l_cmdb_dao         = isys_cmdb_dao::instance($this->m_database_component);
        $l_installation_dao = isys_cmdb_dao_category_s_application_assigned_obj::instance($this->m_database_component);
        $l_return           = [];

        $l_res = $l_dao->get_selected_objects($p_service_obj_id);

        if (count($l_res))
        {
            while ($l_row = $l_res->get_row())
            {
                if ($l_dao->objtype_is_catg_assigned($l_row['isys_obj__isys_obj_type__id'], C__CATG__NAGIOS_HOST_FOLDER) || $l_dao->objtype_is_catg_assigned(
                        $l_row['isys_obj__isys_obj_type__id'],
                        C__CATG__NAGIOS
                    )
                )
                {
                    // According to the category we have a host!
                    $l_return[] = $l_row['isys_obj__id'];
                }
                else if ($l_dao->objtype_is_cats_assigned($l_row['isys_obj__isys_obj_type__id'], C__CATS__APPLICATION) || $l_dao->objtype_is_cats_assigned(
                        $l_row['isys_obj__isys_obj_type__id'],
                        C__CATS__APPLICATION_ASSIGNED_OBJ
                    )
                )
                {
                    // We assume we got a SOFTWARE. Meaning: Get all hosts, this object is installed on and check if the service shall be inherited.
                    $l_app_res = $l_installation_dao->get_data(null, $l_row['isys_obj__id']);

                    if (count($l_app_res))
                    {
                        while ($l_app_row = $l_app_res->get_row())
                        {
                            $l_return[] = $l_cmdb_dao->get_object_by_id($l_app_row['isys_catg_application_list__isys_obj__id'])
                                ->get_row_value('isys_obj__id');
                        } // while
                    } // if
                } // if
            } // while
        } // if

        return array_unique($l_return);
    } // function

    /**
     * Private constructor - Singleton!
     */
    private function __construct()
    {
        ;
    } // function
} // class