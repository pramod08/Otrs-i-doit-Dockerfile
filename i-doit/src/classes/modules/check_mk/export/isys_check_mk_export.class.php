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
 * Check_MK export class.
 * The export will be called like this: isys_check_mk_export::instance()->init_export->start_export();
 *
 * @deprecated
 * @package     Modules
 * @subpackage  Check_MK
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.4.0
 */
class isys_check_mk_export
{
    /**
     * These constants will be used to determine the export structure type.
     */
    const STRUCTURE_NONE          = 0;
    const STRUCTURE_PHYS_LOCATION = 1;
    const STRUCTURE_LOG_LOCATION  = 2;
    const STRUCTURE_OBJECT_TYPE   = 3;
    /**
     * This will be used at several points to find out, if debug information shall be displayed.
     *
     * @var  boolean
     */
    protected static $m_debug = false;
    /**
     * This variable will hold the information, in which structure the configuration files shall be exported.
     *
     * @var  integer
     */
    protected static $m_export_structure = null;
    /**
     * Logger instance for the export.
     *
     * @var  isys_log
     */
    protected static $m_log = null;
    /**
     * Singleton instance variable.
     *
     * @var  isys_check_mk_export
     */
    private static $m_instance = null;
    /**
     * This variable will hold an instance of isys_cmdb_dao_category_g_cmk.
     *
     * @var  isys_cmdb_dao_category_g_cmk
     */
    protected $m_dao = null;
    /**
     * This variable will hold the database component.
     *
     * @var  isys_component_database
     */
    protected $m_database_component = null;
    /**
     * This variable will hold all the object location paths.
     *
     * @var  array
     */
    protected $m_directory_cache = [];
    /**
     * This variable will hold the paths for saving the single export files.
     *
     * @var  array
     */
    protected $m_export_dirs = [];
    /**
     * This variable will hold the paths to all the exported config-files.
     *
     * @var  array
     */
    protected $m_exported_files = [];
    /**
     * This variable will save all exported hosts, including the "hostname" as key.
     *
     * @var  array
     */
    protected $m_hosts_data = [];
    /**
     * Options array.
     *
     * @var  array
     */
    protected $m_options = [];
    /**
     * This variable will store the original "umask" value to reset it, after the export is done.
     *
     * @var  integer
     */
    protected $m_umask = 22;
    /**
     * This array will hold the information which tag (+tag-group) is attached to which host.
     *
     * @var  array
     */
    protected $m_wato_host_tag_connections = [];

    /**
     * Public static "instance" method - Singleton!
     *
     * @return  isys_check_mk_export
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
     * Method for retrieving the check_mk log.
     *
     * @return  isys_log
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function get_log()
    {
        return self::$m_log;
    } // function

    /**
     * Method for retrieving the available structure options.
     *
     * @return  array
     */
    public static function get_structure_options()
    {
        return [
            self::STRUCTURE_NONE          => _L('LC__MODULE__CHECK_MK__EXPORT_WITHOUT_STRUCTURE'),
            self::STRUCTURE_PHYS_LOCATION => _L('LC__MODULE__CHECK_MK__EXPORT_IN_LOCATION_PATH'),
            // self::STRUCTURE_LOG_LOCATION => _L('LC__MODULE__CHECK_MK__EXPORT_IN_LOG_LOCATION_PATH'),
            self::STRUCTURE_OBJECT_TYPE   => _L('LC__MODULE__CHECK_MK__EXPORT_OBJECT_TYPES'),
        ];
    } // function

    /**
     * Initialize method for the configuration export.
     * There are several options which can be set by this method:
     *
     *    'export_dir' : destination for the check_mk export (default "check_mk_export")
     *
     * @param   array $p_options An optional set of options. Javascript style!
     *
     * @throws  isys_exception_filesystem
     * @return  isys_check_mk_export
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function init_export(array $p_options = [])
    {
        global $g_comp_database, $g_absdir;

        $this->m_umask              = umask(0);
        $this->m_database_component = $g_comp_database;
        $this->m_dao                = isys_cmdb_dao_category_g_cmk::instance($this->m_database_component);

        // This is necessary to initialize the class (maybe create a "init" method..?).
        isys_check_mk_helper_tag::factory(C__OBJTYPE__SERVER);

        $this->m_options = array_merge(
            [
                'export_dir'       => 'check_mk_export',
                'debug'            => true,
                'export_structure' => self::STRUCTURE_NONE,
                'language'         => null
            ],
            $p_options
        );

        // Saving some static data...
        self::$m_debug            = $this->m_options['debug'];
        self::$m_export_structure = $this->m_options['export_structure'];
        self::$m_log              = isys_log::get_instance('check_mk-export')
            ->set_log_file($g_absdir . DS . 'temp' . DS . 'check_mk-export_' . date('Y-m-d_H-i') . '.log')
            ->set_log_level(isys_log::C__ALL & ~isys_log::C__DEBUG);

        // Setting log options.
        if (self::$m_debug)
        {
            self::$m_log->set_log_level(isys_log::C__ALL);
        } // if

        // Now load the export paths, which are actually beeing used to prepare the directories.
        $l_hosts_res = isys_cmdb_dao_category_g_cmk::instance($this->m_database_component)
            ->get_used_export_paths();

        while ($l_row = $l_hosts_res->get_row())
        {
            if (empty($l_row['isys_monitoring_export_config__path']))
            {
                $l_row['isys_monitoring_export_config__path'] = $this->m_options['export_dir'];
                self::$m_log->warning(
                    _L(
                        'LC__MODULE__CHECK_MK__EXPORT_EXCEPTION__NO_EXPORT_PATH_SET',
                        [
                            $l_row['isys_monitoring_export_config__title'],
                            $this->m_options['export_dir']
                        ]
                    )
                );
            } // if

            $this->m_export_dirs[$l_row['isys_monitoring_export_config__id']] = rtrim($l_row['isys_monitoring_export_config__path'], DS) . DS;
        } // while

        return $this;
    } // function

    /**
     * Method for retrieving the exported files.
     *
     * @return  array
     */
    public function get_exported_files()
    {
        return $this->m_exported_files;
    } // function

    /**
     * Method for starting the export.
     *
     * @return  isys_check_mk_export
     * @throws  isys_exception_filesystem
     * @throws  isys_exception_general
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function start_export()
    {
        global $g_loc, $g_comp_template_language_manager, $g_comp_session;

        isys_auth_check_mk::instance()
            ->check(isys_auth::EXECUTE, 'EXPORT');

        self::$m_log->info('Starting export.');
        $l_system_lang = false;

        if ($this->m_options['language'] > 0 && $g_loc->get_setting(LC_LANG) != $this->m_options['language'])
        {
            $l_system_lang = $g_loc->resolve_language_by_constant($g_loc->get_setting(LC_LANG));
            $l_export_lang = $g_loc->resolve_language_by_constant($this->m_options['language']);

            self::$m_log->info('Your language is "' . $l_system_lang . '" but you try to export in "' . $l_export_lang . '". Switching language!');

            $g_comp_session->set_language($l_export_lang);
            isys_application::instance()
                ->language($l_export_lang);
            $g_comp_template_language_manager->load($l_export_lang);
            $g_comp_template_language_manager->load_custom($l_export_lang);
        } // if

        $this->create_directory_tree()
            ->export_hosts()
            ->create_remaining_wato_files()
            ->save_exported_generic_tags()
            ->export_host_tags()
            ->create_tarball_file()
            ->create_zip_file();

        if (is_array($this->m_export_dirs) && count($this->m_export_dirs))
        {
            foreach ($this->m_export_dirs as $l_export_dir)
            {
                $l_directory_iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($l_export_dir));

                if (count($l_directory_iterator))
                {
                    foreach ($l_directory_iterator as $l_dir)
                    {
                        self::$m_log->debug('Changing rights for "' . $l_dir . '" to 0777...');
                        chmod($l_dir, 0777);
                    } // foreach
                } // if
            } // foreach
        } // if

        // Resetting the languages.
        if ($l_system_lang)
        {
            $g_comp_session->set_language($l_system_lang);
            isys_application::instance()
                ->language($l_system_lang);
            // $g_comp_template_language_manager->load($l_system_lang);
            // $g_comp_template_language_manager->load_custom($l_system_lang);
        } // if

        self::$m_log->info('Finished export.');

        if (self::$m_debug)
        {
//			self::$m_log->flush_log();
        } // if

        umask($this->m_umask);

        return $this;
    } // function

    /**
     * Method for saving all the exported generic tags for the next step...
     *
     * @return  isys_check_mk_export
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function save_exported_generic_tags()
    {
        isys_check_mk_helper_tag::save_exported_tags_to_database();

        return $this;
    } // function

    /**
     * This method will prepare the directory tree, if the option is set.
     *
     * @throws  isys_exception_filesystem
     * @return  isys_check_mk_export
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function create_directory_tree()
    {
        self::$m_log->info('Creating the export directories.');

        // We need the filemanager to delete the contents of our export directory.
        $l_comp_filemanager = new isys_component_filemanager();

        foreach ($this->m_export_dirs as $l_export_path)
        {
            // Here we remove all data inside the directory, so we always export a "fresh" configuration.
            $l_comp_filemanager->remove_files($l_export_path);

            if (!file_exists($l_export_path))
            {
                if (!mkdir($l_export_path, 0777, true))
                {
                    throw new isys_exception_filesystem(
                        'Directory ' . $l_export_path . ' could not be written',
                        'The directory ' . $l_export_path . ' could not be written by the i-doit system, please check the rights on your filesystem.'
                    );
                } // if
            } // if
        } // foreach

        if (self::$m_export_structure === self::STRUCTURE_NONE)
        {
            return $this;
        } // if

        $l_host_res = isys_cmdb_dao_category_g_cmk::instance($this->m_database_component)
            ->get_data(null, null, 'AND isys_catg_cmk_list__exportable = 1');

        if (count($l_host_res) > 0)
        {
            if (self::$m_export_structure === self::STRUCTURE_PHYS_LOCATION)
            {
                $l_location_dao = isys_cmdb_dao_category_g_location::instance($this->m_database_component);

                while ($l_host = $l_host_res->get_row())
                {
                    $l_dir_path = $l_raw_path = [];
                    $l_hostname = isys_check_mk_helper::render_export_hostname($l_host['isys_obj__id']);

                    // If the current object has no hostname, we skip and log it.
                    if (empty($l_hostname))
                    {
                        continue;
                    } // if

                    // Find out, if we need to create the location directory-tree.
                    $l_path = array_reverse($l_location_dao->get_location_path($l_host['isys_obj__id']));

                    foreach ($l_path as $l_location)
                    {
                        $l_location_title = $this->m_dao->obj_get_title_by_id_as_string($l_location);

                        $l_dir_path[] = preg_replace(
                            [
                                '/\W+/',
                                '/^_|_$/'
                            ],
                            [
                                '_',
                                ''
                            ],
                            isys_glob_replace_accent($l_location_title)
                        );
                        $l_raw_path[] = $l_location_title;
                    } // foreach

                    $this->m_directory_cache[end($l_path)] = [
                        'path'     => implode(DS, $l_dir_path) . DS,
                        'dirs'     => $l_dir_path,
                        'dirs_raw' => $l_raw_path
                    ];
                } // while
            }
            else if (self::$m_export_structure === self::STRUCTURE_LOG_LOCATION)
            {
                ; // This is not implemented.
            }
            else if (self::$m_export_structure === self::STRUCTURE_OBJECT_TYPE)
            {
                while ($l_host = $l_host_res->get_row())
                {
                    $l_hostname = isys_check_mk_helper::render_export_hostname($l_host['isys_obj__id']);

                    // If the current object has no hostname, we skip and log it.
                    if (empty($l_hostname))
                    {
                        continue;
                    } // if

                    $l_location_title = _L($l_host['isys_obj_type__title']);

                    $l_dir_path = strtolower(trim(preg_replace('/\W+/', '_', isys_glob_replace_accent($l_location_title)), '_'));

                    $this->m_directory_cache[(int) $l_host['isys_obj_type__id']] = [
                        'path'     => $l_dir_path . DS,
                        'dirs'     => [$l_dir_path],
                        'dirs_raw' => [$l_location_title]
                    ];
                } // while
            } // if
        } // if

        return $this;
    } // function

    /**
     * Method for exporting the check_mk all_hosts.
     *
     * @return  isys_check_mk_export
     * @throws  isys_exception_filesystem
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function export_hosts()
    {
        self::$m_log->info('Exporting: all_hosts');

        // Select all "Check_MK Tag" category entries, which shall be exported (active = 1).
        $l_host_res = isys_cmdb_dao_category_g_cmk::instance($this->m_database_component)
            ->get_data(
                null,
                null,
                'AND isys_catg_cmk_list__exportable = 1 AND isys_obj__status = ' . $this->m_dao->convert_sql_int(C__RECORD_STATUS__NORMAL),
                null,
                C__RECORD_STATUS__NORMAL
            );

        if (count($l_host_res) > 0)
        {
            $l_host_tags = [];
            /* @var  isys_cmdb_dao_category_property $l_dao */
            $l_dao          = isys_cmdb_dao_category_property::instance($this->m_database_component);
            $l_static_tags  = isys_factory_cmdb_dialog_dao::get_instance('isys_check_mk_tags', $this->m_database_component);
            $l_tag_groups   = isys_factory_cmdb_dialog_dao::get_instance('isys_check_mk_tag_groups', $this->m_database_component);
            $l_tag_dao      = isys_cmdb_dao_category_g_cmk_tag::instance($this->m_database_component);
            $l_location_dao = isys_cmdb_dao_category_g_location::instance($this->m_database_component);

            while ($l_host = $l_host_res->get_row())
            {
                if ($l_host['isys_obj__status'] != C__RECORD_STATUS__NORMAL)
                {
                    continue;
                } // if

                $l_hostname = isys_check_mk_helper::render_export_hostname($l_host['isys_obj__id']);

                // If the current object has no hostname, we skip and log it.
                if (empty($l_hostname))
                {
                    self::$m_log->warning('> The object "' . $l_host['isys_obj__title'] . '" (#' . $l_host['isys_obj__id'] . ') has no defined hostname!');
                    continue;
                } // if

                // Get the cmdb tags of the current object.
                $l_tags = isys_check_mk_helper_tag::factory($l_host['isys_obj_type__id'])
                    ->get_cmdb_tags($l_host['isys_obj__id']);

                if (count($l_tags))
                {
                    $l_res = $l_dao->retrieve_properties(array_keys($l_tags));

                    if (count($l_res))
                    {
                        while ($l_row = $l_res->get_row())
                        {
                            $this->m_wato_host_tag_connections[$l_hostname][$l_row['const'] . '__' . $l_row['key']] = $l_tags[$l_row['id']];
                        } // while
                    } // if
                } // if

                // Get the dynamic tags of the current object.
                $l_dynamic_tags = isys_check_mk_helper_tag::factory($l_host['isys_obj_type__id'])
                    ->get_dynamic_tags($l_host['isys_obj__id']);

                foreach ($l_dynamic_tags as $l_dynamic_tag)
                {
                    $l_tags[] = $l_dynamic_tag;
                } // foreach

                // Now retrieve the tags, defined explicitly.
                $l_defined_tags = isys_format_json::decode(
                    $l_tag_dao->get_data(null, $l_host['isys_obj__id'])
                        ->get_row_value('isys_catg_cmk_tag_list__tags')
                );

                if (is_array($l_defined_tags) && count($l_defined_tags) > 0)
                {
                    foreach ($l_defined_tags as $l_tag)
                    {
                        $l_static_tag = $l_static_tags->get_data($l_tag);

                        if ($l_static_tag !== false && is_array($l_static_tag))
                        {
                            $l_group = $l_tag_groups->get_data($l_static_tag['isys_check_mk_tags__isys_check_mk_tag_groups__id']);
                            $l_group = isys_check_mk_helper_tag::prepare_valid_tag_name(_L($l_group['isys_check_mk_tag_groups__title']));
                            $l_tag   = isys_check_mk_helper_tag::prepare_valid_tag_name($l_static_tag['isys_check_mk_tags__unique_name']);

                            // We memorize which object has which tag of which tag-group.
                            $this->m_wato_host_tag_connections[$l_hostname][$l_group] = $l_tag;

                            // Finally we prepare the tag-name to be valid.
                            $l_tags[] = $l_tag;
                        } // if
                    } // foreach
                } // if

                // This part is not really a tag, but it helps us displaying the correct amount of pipes ("|").
                $l_tags[] = '/" + FOLDER_PATH + "/",';

                self::$m_log->info('> Adding host to array "' . $l_hostname . '" (#' . $l_host['isys_obj__id'] . ')');

                $l_parent = 0;

                $l_host_data     = $this->m_dao->get_data(null, $l_host['isys_obj__id'])
                    ->get_row();
                $l_check_mk_host = $l_host_data['isys_catg_cmk_list__isys_monitoring_export_config__id'];
                $l_hostaddress   = $l_host_data['isys_catg_cmk_list__isys_catg_ip_list__id'];

                if ($l_hostaddress > 0)
                {
                    $l_ip_address = isys_cmdb_dao_category_g_ip::instance($this->m_database_component)
                        ->get_data($l_hostaddress, $l_host['isys_obj__id'])
                        ->get_row_value('isys_cats_net_ip_addresses_list__title');
                }
                else if ($l_hostaddress === 0 || $l_hostaddress === "0" || $l_hostaddress === null)
                {
                    // The ID "0" (zero) means: use the primary IP address.
                    $l_ip_address = isys_cmdb_dao_category_g_ip::instance($this->m_database_component)
                        ->get_data(null, $l_host['isys_obj__id'], 'AND isys_catg_ip_list__primary = 1')
                        ->get_row_value('isys_cats_net_ip_addresses_list__title');
                }
                else
                {
                    // No ID at all means: use no IP.
                    $l_ip_address = '';
                } // if

                $this->m_hosts_data[$l_hostname] = [
                    'hostname'             => $l_hostname,
                    'ip-address'           => $l_ip_address,
                    'check_mk_export_host' => $l_check_mk_host
                ];

                if (self::$m_export_structure === self::STRUCTURE_PHYS_LOCATION)
                {
                    $l_parent = (int) $l_location_dao->get_data(null, $l_host['isys_obj__id'])
                        ->get_row_value('isys_catg_location_list__parentid');
                }
                else if (self::$m_export_structure === self::STRUCTURE_LOG_LOCATION)
                {
                    // ...
                }
                else if (self::$m_export_structure === self::STRUCTURE_OBJECT_TYPE)
                {
                    $l_parent = (int) $l_host['isys_obj_type__id'];
                } // if

                $l_host_tags[$l_check_mk_host][$l_parent][] = '  "' . $l_hostname . '|' . implode('|', isys_check_mk_helper_tag::make_unique($l_tags));
            } // while

            // Here we write all the files, according to their export paths.
            foreach ($l_host_tags as $l_host_id => $l_locations)
            {
                foreach ($l_locations as $l_parent => $l_hosts)
                {
                    $l_location = $this->m_export_dirs[$l_host_id] . ltrim($this->m_directory_cache[$l_parent]['path'], DS);

                    if (empty($l_location))
                    {
                        throw new isys_exception_filesystem('The host #' . $l_host_id . ' has no configured export path! Export aborted', '');
                    } // if

                    $this->m_directory_cache[$l_parent]['export_to'] = $this->m_export_dirs[$l_host_id];

                    // At first we try to create the location path.
                    if (!file_exists($l_location))
                    {
                        if (!mkdir($l_location, 0777, true))
                        {
                            throw new isys_exception_filesystem(
                                'Directory ' . $l_location . ' could not be written',
                                'The directory ' . $l_location . ' could not be written by the i-doit system, please check the rights on your filesystem.'
                            );
                        } // if
                    } // if

                    $l_dir_name = $this->m_directory_cache[$l_parent]['export_to'];

                    if (self::$m_export_structure && end($this->m_directory_cache[$l_parent]['dirs_raw']) != '')
                    {
                        $l_dir_name = end($this->m_directory_cache[$l_parent]['dirs_raw']);
                    } // if

                    self::$m_log->debug('> Writing hosts to file: "' . $l_location . 'hosts.mk"');

                    $l_currently_exporting = array_map(
                        function ($p_string)
                        {
                            return current(explode('|', ltrim($p_string, '" ')));
                        },
                        $l_hosts
                    );

                    $l_return = implode(
                        PHP_EOL,
                        [
                            '# Written by i-doit',
                            '# encoding: utf-8',
                            '',
                            'all_hosts += [',
                            '%s',
                            ']',
                            ''
                        ]
                    );

                    $l_return .= $this->render_ip_addresses_by_host($l_currently_exporting) . PHP_EOL . PHP_EOL . '# Lock the file!' . PHP_EOL . '_lock = True';

                    file_put_contents($l_location . 'hosts.mk', utf8_decode(str_replace('%s', implode(PHP_EOL, $l_hosts ?: []), $l_return)));
                    $this->m_exported_files[] = str_replace(DS, '/', $l_location) . 'hosts.mk';

                    // Writing the ".wato".
                    self::$m_log->debug('> Writing .wato file: "' . $l_location . '.wato"');

                    file_put_contents(
                        $l_location . '.wato',
                        utf8_decode("{'attributes': {}, 'num_hosts': " . count($l_hosts) . ", 'title': u'" . $l_dir_name . "', 'lock': True}")
                    );
                    $this->m_exported_files[] = str_replace(DS, '/', $l_location) . '.wato';
                } // foreach
            } // foreach
        }
        else
        {
            self::$m_log->info('> No hosts to write');
        } // if

        return $this;
    } // function

    /**
     * This method will create a ".wato" file in every empty folder.
     *
     * @return  isys_check_mk_export
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function create_remaining_wato_files()
    {
        if (self::$m_export_structure === self::STRUCTURE_NONE)
        {
            return $this;
        } // if

        self::$m_log->info('Creating remaining ".wato" files...');

        foreach ($this->m_directory_cache as $l_path_array)
        {
            $l_path = $l_path_array['export_to'];

            foreach ($l_path_array['dirs'] as $l_index => $dir_part)
            {
                $l_path .= $dir_part . DS;

                if (!file_exists($l_path . '.wato'))
                {
                    self::$m_log->debug('> Writing ".wato" file: "' . $l_path . '.wato"');
                    $this->m_exported_files[] = str_replace(DS, '/', $l_path) . '.wato';

                    file_put_contents(
                        $l_path . '.wato',
                        utf8_decode("{'attributes': {}, 'num_hosts': 0, 'title': u'" . $l_path_array['dirs_raw'][$l_index] . "', 'lock': True}")
                    );
                } // if
            } // foreach
        } // foreach

        return $this;
    } // function

    /**
     * Method for exporting all tags (saved generic tags + static tags).
     *
     * @return  isys_check_mk_export
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function export_host_tags()
    {
        self::$m_log->info('Exporting: host_tags');

        isys_check_mk_helper::init();

        $l_return = [
            '# Written by i-doit',
            '# encoding: utf-8',
            '',
            'wato_host_tags += ['
        ];

        // Get the generic tags of the current object.
        $l_tags = isys_check_mk_helper_tag::get_tags_for_export($this->m_options['language']);

        $l_return_a = [];

        if (is_array($l_tags) && count($l_tags))
        {
            foreach ($l_tags as $l_group => $l_tag_data)
            {
                $l_return_b = [];

                foreach ($l_tag_data as $l_tag)
                {
                    $l_tag['aux'] = [];

                    if (strpos($l_tag['id'], '|') !== false)
                    {
                        $l_tag['aux'] = array_slice(explode('|', $l_tag['id']), 1);
                        $l_tag['id']  = current(explode('|', $l_tag['id']));
                    } // if

                    $l_return_b[] = '    ("' . $l_tag['id'] . '", u"' . $l_tag['name'] . '", ' . isys_format_json::encode($l_tag['aux']) . ')';
                } // foreach

                if (strpos($l_group, 'C__') !== false && strpos($l_group, '::') > 0)
                {
                    list($l_group, $l_group_name) = explode('::', $l_group);
                }
                else if (strpos($l_group, '__') > 0)
                {
                    list($l_group, $l_group_name) = explode('__', $l_group);
                }
                else
                {
                    $l_group_name = $l_group;
                } // if

                $l_return_a[] = '  ("' . isys_check_mk_helper_tag::prepare_valid_tag_name($l_group) . '", u"i-doit/' . $l_group_name . '", [' . PHP_EOL . implode(
                        ',' . PHP_EOL,
                        $l_return_b
                    ) . '])';
            } // foreach

            $l_return[] = implode(',' . PHP_EOL, $l_return_a) . ']';
            $l_return[] = '';
            $l_return[] = '# Lock the file!';
            $l_return[] = '_lock = True';

            foreach ($this->m_export_dirs as $l_export_dir)
            {
                self::$m_log->debug('> Writing hosts and tags to file: "' . $l_export_dir . 'idoit_hosttags.mk"');
                $this->m_exported_files[] = str_replace(DS, '/', $l_export_dir) . 'idoit_hosttags.mk';

                file_put_contents($l_export_dir . 'idoit_hosttags.mk', utf8_decode(implode(PHP_EOL, $l_return)));
            } // foreach
        }
        else
        {
            self::$m_log->info('> No host tags to write');
        } // if

        return $this;
    } // function

    /**
     * This method renders the necessary hostnames and IP addresses for each given host object.
     *
     * @param   array $p_hosts
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function render_ip_addresses_by_host($p_hosts)
    {
        $l_return = $l_ipaddress = $l_host_attributes = [];

        foreach ($p_hosts as $l_hostname)
        {
            $l_attributes = [];
            $l_obj_data   = $this->m_hosts_data[$l_hostname];

            if (!empty($l_obj_data['ip-address']))
            {
                $l_ipaddress[] = '  "' . $l_obj_data['hostname'] . '": u"' . $l_obj_data['ip-address'] . '"';
            } // if

            if (!empty($l_obj_data['ip-address']))
            {
                $l_attributes[] = '"ipaddress": u"' . $l_obj_data['ip-address'] . '"';
            } // if

            if (count($this->m_wato_host_tag_connections[$l_hostname]))
            {
                foreach ($this->m_wato_host_tag_connections[$l_hostname] as $l_group => $l_tag)
                {
                    $l_attributes[] = '"tag_' . $l_group . '": u"' . $l_tag . '"';
                } // foreach
            } // if

            $l_host_attributes[] = '  "' . $l_obj_data['hostname'] . '": {' . implode(', ', $l_attributes) . '}';
        } // foreach

        if (count($l_ipaddress))
        {
            $l_return[] = "";
            $l_return[] = "# Explicit IP addresses";
            $l_return[] = "ipaddresses.update({";
            $l_return[] = implode(',' . PHP_EOL, $l_ipaddress);
            $l_return[] = "})";
        } // if

        if (count($l_host_attributes))
        {
            $l_return[] = "";
            $l_return[] = "# Host attributes (needed for WATO)";
            $l_return[] = "host_attributes.update({";
            $l_return[] = implode("," . PHP_EOL, $l_host_attributes);
            $l_return[] = "})";
        } // if

        return implode(PHP_EOL, $l_return);
    } // function

    /**
     * Method for creating a ZIP file which contains the exported files.
     *
     * @return  isys_check_mk_export
     */
    protected function create_zip_file()
    {
        self::$m_log->info('Creating ZIP files with the exported files.');

        if (!extension_loaded('zlib') || !class_exists('ZipArchive'))
        {
            self::$m_log->warning('The zlip extension seems to be missing. The ZIP file could not be created!');

            return $this;
        } // if

        $l_zip = new ZipArchive();

        foreach ($this->m_export_dirs as $l_export_dir)
        {
            $l_cut         = isys_strlen($l_export_dir);
            $l_dir_segment = end(explode(DS, trim($l_export_dir, DS)));

            if ($l_zip->open($l_export_dir . $l_dir_segment . '.zip', ZipArchive::CREATE) === true)
            {
                self::$m_log->info('Creating ZIP "' . $l_export_dir . $l_dir_segment . '.zip' . '"... ');

                foreach ($this->m_exported_files as $file)
                {
                    self::$m_log->debug('Adding file "' . $file . '" to ZIP...');
                    $l_zip->addFile($file, substr($file, $l_cut));
                } // foreach

                $l_zip->close();

                self::$m_log->notice('Finished!');
            } // if
        } // foreach

        return $this;
    } // function

    /**
     * Method for creating a TarGZ file which contains the exported files.
     *
     * @return  isys_check_mk_export
     */
    protected function create_tarball_file()
    {
        self::$m_log->info('Creating TarGZ files with the exported files.');

        if (!extension_loaded('phar') || !class_exists('Phar'))
        {
            self::$m_log->warning('The zlip extension seems to be missing. The TarGZ file could not be created!');

            return $this;
        } // if

        foreach ($this->m_export_dirs as $l_host_id => $l_export_dir)
        {
            $l_dir_segment = end(explode(DS, trim($l_export_dir, DS)));

            $l_phar = new PharData($l_export_dir . $l_dir_segment . '.tar');
            self::$m_log->info('Creating TarGZ "' . $l_export_dir . $l_dir_segment . '.tar' . '"... ');

            $l_phar->buildFromDirectory($l_export_dir);

            $l_phar->compress(Phar::GZ);

            try
            {
                // Try to unlink the ".tar" file, sinze "compress" has created a new ".tar.gz" file.
                if (file_exists($l_export_dir . 'check_mk_config.tar'))
                {
                    @unlink($l_export_dir . 'check_mk_config.tar');
                } // if
            }
            catch (Exception $e)
            {
                self::$m_log->warning('Tried to remove the unused file "' . $l_export_dir . $l_dir_segment . '.tar", but got "permission denied" error.');
            } // try

            self::$m_log->notice('Finished!');
        } // foreach

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
     * Private constructor - Singleton!
     */
    private function __construct()
    {
        ;
    } // function
} // class