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

namespace idoit\Module\Check_mk;

use idoit\Component\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\TestHandler;

/**
 * i-doit Check_MK export class.
 *
 * @todo        Try making the tags truly "unique" (by adding a small hash of the group-name or something).
 * @package     DSHB
 * @subpackage  Controller
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.7.1
 * @version     1.0
 */
class Export
{
    /**
     * These constants will be used to determine the export structure type.
     */
    const STRUCTURE_NONE          = 0;
    const STRUCTURE_PHYS_LOCATION = 1;
    const STRUCTURE_LOG_LOCATION  = 2;
    const STRUCTURE_OBJECT_TYPE   = 3;
    /**
     * Export configuration array.
     *
     * @var  array
     */
    protected $configuration = [];
    /**
     * This variable will hold an instance of isys_check_mk_dao.
     *
     * @var  \isys_cmdb_dao_category_g_cmk
     */
    protected $dao = null;
    /**
     * Directory cache array. Will be used for complex folder hierarchies.
     *
     * @var  array
     */
    protected $directoryCache = [];
    /**
     * Array of exported files - will be displayed in the frontend.
     *
     * @var  \isys_array
     */
    protected $exportedFiles;
    /**
     * Array which will hold all hosts to be exported.
     *
     * @var  array
     */
    protected $hosts = [];
    /**
     * Logger instance.
     *
     * @var  Logger
     */
    protected $log;
    /**
     * This variable will hold the "TestHandler" - this handler can return a log array (for the frontend).
     *
     * @var  \Monolog\Handler\TestHandler
     */
    protected $logHandler;
    /**
     * Options array.
     *
     * @var  array
     */
    protected $options = [];
    /**
     * Ordered export configuration array.
     *
     * @var  array
     */
    protected $orderedConfiguration = [];
    /**
     * Will hold the umask value from "before" the export to reset it afterwards.
     *
     * @var  integer
     */
    protected $umask;
    /**
     * Cache array for connections between hosts and tags.
     *
     * @var array
     */
    protected $watoHostTagConnections = [];

    /**
     * Method for returning all possible structure options.
     *
     * @return array
     */
    public static function getStructureOptions ()
    {
        return [
            self::STRUCTURE_NONE          => _L('LC__MODULE__CHECK_MK__EXPORT_WITHOUT_STRUCTURE'),
            self::STRUCTURE_PHYS_LOCATION => _L('LC__MODULE__CHECK_MK__EXPORT_IN_LOCATION_PATH'),
            // self::STRUCTURE_LOG_LOCATION => _L('LC__MODULE__CHECK_MK__EXPORT_IN_LOG_LOCATION_PATH'),
            self::STRUCTURE_OBJECT_TYPE   => _L('LC__MODULE__CHECK_MK__EXPORT_OBJECT_TYPES'),
        ];
    } // function

    /**
     * This is the method which will start the export.
     *
     * @throws  \isys_exception_filesystem
     */
    public function export()
    {
        global $g_comp_template_language_manager;

        \isys_auth_check_mk::instance()
            ->check(\isys_auth::EXECUTE, 'EXPORT');

        $locale = \isys_locale::get_instance();
        $this->log->info('Starting export.');
        $systemLang = false;

        if ($this->options['language'] > 0 && $locale->get_setting(LC_LANG) != $this->options['language'])
        {
            $systemLang = $locale->resolve_language_by_constant($locale->get_setting(LC_LANG));
            $exportLang = $locale->resolve_language_by_constant($this->options['language']);

            $this->log->info('Your language is "' . $systemLang . '" but you try to export in "' . $exportLang . '". Switching language!');

            \isys_component_session::instance()
                ->set_language($exportLang);
            $g_comp_template_language_manager->load($exportLang);
            $g_comp_template_language_manager->load_custom($exportLang);
        } // if

        $this->collectObjects()
            ->createDirectoryCache()
            ->orderExportConfigurations()
            ->exportHostFiles()
            ->exportRemainingWATOFiles()
            ->saveExportedGenericTags()
            ->exportTags()
            ->exportContactGroups()
            ->createTarballFile()
            ->createZipFile();

        if (is_array($this->configuration) && count($this->configuration))
        {
            foreach ($this->configuration as $config)
            {
                if (empty($config['path']))
                {
                    continue;
                } // if

                $this->log->debug('Changing file and folder rights underneath "' . $config['path'] . '" to 0777...');
                $directoryIterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($config['path']));

                if (count($directoryIterator))
                {
                    foreach ($directoryIterator as $dir)
                    {
                        $this->log->debug(' > ' . $dir);
                        chmod($dir, 0777);
                    } // foreach
                } // if
            } // foreach
        } // if

        // Resetting the languages.
        if ($systemLang)
        {
            \isys_component_session::instance()->set_language($systemLang);
        } // if

        $this->log->info('Finished export.');

        umask($this->umask);

        return $this;
    } // function

    /**
     * Will return every log message.
     *
     * @return  array
     */
    public function getLogRecords()
    {
        return $this->logHandler->getRecords();
    } // function

    /**
     * Will return all exported files.
     *
     * @return  array
     */
    public function getExportedFiles()
    {
        return $this->exportedFiles->flatten();
    } // function

    /**
     * Method for preparing the export configurations.
     *
     * @return  $this
     * @throws  \idoit\Exception\JsonException
     */
    protected function prepareExportConfigurations()
    {
        $result = $this->dao->get_export_configurations();

        while ($row = $result->get_row())
        {
            // Next, we'll check if the current configuration is part of a multisite and has a master.
            $row['options'] = (\isys_format_json::is_json_array($row['options']) ? \isys_format_json::decode($row['options']) : []);

            if (!empty($row['path']))
            {
                // Unifying the slashes.
                $row['path'] = rtrim(
                        str_replace(
                            [
                                '/',
                                '\\'
                            ],
                            DS,
                            $row['path']
                        ),
                        DS
                    ) . DS;
            } // if

            $this->configuration[$row['id']] = $row;
        } // while

        // Yes, we need to run this loop AFTER collecting all export configurations.
        foreach ($this->configuration as $id => $config)
        {
            if ($config['options']['multisite'] && $config['options']['master'] > 0)
            {
                if (!isset($this->configuration[$config['options']['master']]))
                {
                    $this->log->warning(
                        'The export configuration "' . $config['title'] . '" (#' . $id . ') does refer to a master site that does not exist! Please update the configuration and try again.'
                    );
                    unset($this->configuration[$id]);
                } // if
            } // if
        } // foreach

        return $this;
    } // function

    /**
     * Method for collecting all necessary object data (hosts and clusters).
     *
     * @return  $this
     * @throws  \isys_exception_database
     */
    protected function collectObjects()
    {
        $sql = 'SELECT *
			FROM isys_obj
			INNER JOIN isys_catg_cmk_list ON isys_catg_cmk_list__isys_obj__id = isys_obj__id
			INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
			WHERE isys_catg_cmk_list__exportable = 1
			AND isys_catg_cmk_list__status = ' . $this->dao->convert_sql_int(C__RECORD_STATUS__NORMAL) . '
			AND isys_obj__status = ' . $this->dao->convert_sql_int(C__RECORD_STATUS__NORMAL) . '
			AND isys_catg_cmk_list__isys_monitoring_export_config__id > 0;';

        $result = $this->dao->retrieve($sql);

        while ($row = $result->get_row())
        {
            // Only add hosts, that refer to one of our export configurations.
            if (isset($this->configuration[$row['isys_catg_cmk_list__isys_monitoring_export_config__id']]))
            {
                $hostname = \isys_check_mk_helper::render_export_hostname($row['isys_obj__id']);

                // If the current object has no hostname, we skip and log it.
                if (empty($hostname))
                {
                    $this->log->warning('> The object "' . $row['isys_obj__title'] . '" (#' . $row['isys_obj__id'] . ') has no defined hostname!');
                    continue;
                } // if

                $row['hostname'] = $hostname;
                $row['tags']     = $this->retrieveTags($row);
                $row['cluster']  = $this->dao->objtype_is_catg_assigned($row['isys_obj__isys_obj_type__id'], C__CATG__CLUSTER_ROOT);
                $row['contacts'] = false;

                // Get assigned contacts.
                if (is_array($this->configuration[$row['isys_catg_cmk_list__isys_monitoring_export_config__id']]['options']['roles']))
                {
                    $contactRes = \isys_cmdb_dao_category_g_contact::instance(\isys_application::instance()->database)
                        ->get_contact_objects_by_tag(
                            $row['isys_obj__id'],
                            $this->configuration[$row['isys_catg_cmk_list__isys_monitoring_export_config__id']]['options']['roles']
                        );

                    if (count($contactRes))
                    {
                        $row['contacts'] = [];

                        while ($contactRow = $contactRes->get_row())
                        {
                            if ($contactRow['isys_obj__status'] == C__RECORD_STATUS__NORMAL && $contactRow['isys_catg_contact_list__status'] == C__RECORD_STATUS__NORMAL)
                            {
                                $row['contacts'][] = \isys_check_mk_helper::prepare_valid_name($contactRow['isys_obj__title']);
                            } // if
                        } // while
                    } // if
                } // if

                $this->hosts[$row['hostname']] = $row;
            } // if
        } // while

        return $this;
    } // function

    /**
     * This method will empty the "old" export folders and create the directory cache.
     *
     * @return  $this
     * @throws  \isys_exception_filesystem
     */
    protected function createDirectoryCache()
    {
        $this->log->info('Creating the export directories.');

        // We need the filemanager to delete the contents of our export directory.
        $filemanager = new \isys_component_filemanager();

        foreach ($this->configuration as $config)
        {
            // Skip empty paths (usually this should only occur with multisite configurations).
            if (empty($config['path']))
            {
                continue;
            } // if

            // Here we remove all data inside the directory, so we always export a "fresh" configuration.
            $filemanager->remove_files($config['path']);

            if (!file_exists($config['path']))
            {
                if (!mkdir($config['path'], 0777, true))
                {
                    throw new \isys_exception_filesystem(
                        'Directory ' . $config['path'] . ' could not be written',
                        'The directory ' . $config['path'] . ' could not be written by the i-doit system, please check the rights on your filesystem.'
                    );
                } // if
            } // if
        } // foreach

        // Structure "none" and "logical location" to not need any code.

        if ($this->options['export_structure'] === self::STRUCTURE_PHYS_LOCATION)
        {
            $locationDao = \isys_cmdb_dao_category_g_location::instance(\isys_application::instance()->database);

            foreach ($this->hosts as $host)
            {
                $dirPath = $rawPath = [];

                // Find out, if we need to create the location directory-tree.
                $path = array_reverse($locationDao->get_location_path($host['isys_obj__id']));

                foreach ($path as $location)
                {
                    $locationTitle = $this->dao->obj_get_title_by_id_as_string($location);

                    $dirPath[] = preg_replace(
                        [
                            '/\W+/',
                            '/^_|_$/'
                        ],
                        [
                            '_',
                            ''
                        ],
                        isys_glob_replace_accent($locationTitle)
                    );
                    $rawPath[] = $locationTitle;
                } // foreach

                $this->directoryCache[end($path)] = [
                    'path'      => implode(DS, $dirPath) . DS,
                    'dirs'      => $dirPath,
                    'dirs_raw'  => $rawPath,
                    'config_id' => $host['isys_catg_cmk_list__isys_monitoring_export_config__id']
                ];
            } // foreach
        }
        else if ($this->options['export_structure'] === self::STRUCTURE_OBJECT_TYPE)
        {
            foreach ($this->hosts as $host)
            {
                $locationTitle = _L($host['isys_obj_type__title']);

                $dirPath = strtolower(trim(preg_replace('/\W+/', '_', isys_glob_replace_accent($locationTitle)), '_'));

                $this->directoryCache[(int) $host['isys_obj__isys_obj_type__id']] = [
                    'path'      => $dirPath . DS,
                    'dirs'      => [$dirPath],
                    'dirs_raw'  => [$locationTitle],
                    'config_id' => $host['isys_catg_cmk_list__isys_monitoring_export_config__id']
                ];
            } // while
        } // if

        return $this;
    } // function

    /**
     * This method is necessary to order the "multisite" configurations.
     *
     * @return  $this
     */
    protected function orderExportConfigurations()
    {
        foreach ($this->configuration as $config)
        {
            if (!isset($config['options']['multisite']) || !$config['options']['multisite'])
            {
                // The current configuration is no part of a multisite - just add it.
                $this->orderedConfiguration[] = [(int) $config['id'] => true];
            }
            else if ($config['options']['master'] <= 0)
            {
                // Collect all configurations, that are connected (by multisite).
                $temp = array_flip(
                    array_map(
                        function ($iteration)
                        {
                            return (int) $iteration['id'];
                        },
                        array_filter(
                            $this->configuration,
                            function ($iteration) use ($config)
                            {
                                return $iteration['options']['master'] == $config['id'] || $iteration['id'] == $config['id'];
                            }
                        )
                    )
                );

                // Set all values to "false" because (later on) the "master" configuration will get set to true.
                $temp = array_map(
                    function ()
                    {
                        return false;
                    },
                    $temp
                );

                $this->orderedConfiguration[] = $temp;
            } // if
        } // foreach

        return $this;
    } // function

    /**
     * Method for exporting the host files physically.
     *
     * @return  $this
     * @throws  \isys_exception_filesystem
     */
    protected function exportHostFiles()
    {
        foreach ($this->orderedConfiguration as &$order)
        {
            $masterConfig = 0;
            $hostData     = $clusterData = [];

            // First we need to find the "master" export configuration.
            foreach ($order as $configID => &$master)
            {
                if (!isset($this->configuration[$configID]['options']['master']) || !($this->configuration[$configID]['options']['master'] > 0))
                {
                    $masterConfig = $configID;
                    $master       = true;
                    break;
                } // if
            } // foreach

            if ($masterConfig === 0)
            {
                $this->log->error(
                    'It seems as if none of the configurations (ID ' . \isys_helper_textformat::this_this_and_that(
                        $order
                    ) . ') are the "Master"! Please check your configuration.'
                );
                continue;
            } // if

            // Now finally we can write the host files.
            foreach ($this->hosts as &$host)
            {
                $parent     = 0;
                $host['ip'] = false;

                if (!isset($order[$host['isys_catg_cmk_list__isys_monitoring_export_config__id']]))
                {
                    continue;
                } // if

                if ($host['isys_catg_cmk_list__export_ip'])
                {
                    if ($host['isys_catg_cmk_list__isys_catg_ip_list__id'] > 0)
                    {
                        $host['ip'] = \isys_cmdb_dao_category_g_ip::instance(\isys_application::instance()->database)
                            ->get_data($host['isys_catg_cmk_list__isys_catg_ip_list__id'], $host['isys_obj__id'])
                            ->get_row_value('isys_cats_net_ip_addresses_list__title');
                    }
                    else
                    {
                        // If no specific IP-address has been selected, we simply use the primary one.
                        $host['ip'] = \isys_cmdb_dao_category_g_ip::instance(\isys_application::instance()->database)
                            ->get_data(null, $host['isys_obj__id'], 'AND isys_catg_ip_list__primary = 1')
                            ->get_row_value('isys_cats_net_ip_addresses_list__title');
                    } // if
                } // if

                if ($this->options['export_structure'] === self::STRUCTURE_PHYS_LOCATION)
                {
                    $parent = (int) \isys_cmdb_dao_category_g_location::instance(\isys_application::instance()->database)
                        ->get_data(null, $host['isys_obj__id'])
                        ->get_row_value('isys_catg_location_list__parentid');
                }
                else if ($this->options['export_structure'] === self::STRUCTURE_OBJECT_TYPE)
                {
                    $parent = (int) $host['isys_obj__isys_obj_type__id'];
                } // if

                if ($host['cluster'])
                {
                    // Check if the cluster member is part of this export:
                    $members   = [];
                    $memberRes = \isys_cmdb_dao_category_g_cluster::instance(\isys_application::instance()->database)
                        ->get_cluster_members($host['isys_obj__id']);

                    while ($memberRow = $memberRes->get_row())
                    {
                        $memberHostname = \isys_check_mk_helper::render_export_hostname($memberRow['isys_obj__id']);

                        if (isset($this->hosts[$memberHostname]))
                        {
                            $members[] = "'" . $memberHostname . "'";
                        } // if
                    }

                    if (count($members))
                    {
                        $clusterData[$parent][] = '  "' . $host['hostname'] . '|' . implode('|', \isys_check_mk_helper_tag::make_unique($host['tags'])) . " : [" . implode(
                                ', ',
                                $members
                            ) . "],";
                    } // if

                    // Add an empty host for the current parent, so that the file gets created later on...
                    if (!isset($hostData[$parent]))
                    {
                        $hostData[$parent] = [];
                    } // if
                }
                else
                {
                    $hostData[$parent][] = '  "' . $host['hostname'] . '|' . implode('|', \isys_check_mk_helper_tag::make_unique($host['tags'])) . ',';
                } // if
            } // foreach

            foreach ($hostData as $parent => $data)
            {
                $dataCount = count($data);
                $location  = $this->configuration[$masterConfig]['path'] . ltrim($this->directoryCache[$parent]['path'], DS);

                if (empty($location))
                {
                    throw new \isys_exception_filesystem('The host #' . $masterConfig . ' has no configured export path! Export aborted', '');
                } // if

                $this->directoryCache[$parent]['export_to'] = $this->configuration[$masterConfig]['path'];

                // At first we try to create the location path.
                if (!file_exists($location))
                {
                    if (!mkdir($location, 0777, true))
                    {
                        throw new \isys_exception_filesystem(
                            'Directory ' . $location . ' could not be written',
                            'The directory ' . $location . ' could not be written by the i-doit system, please check the rights on your filesystem.'
                        );
                    } // if
                } // if

                $dirName = $this->directoryCache[$parent]['export_to'];

                if ($this->options['export_structure'] && is_array($this->directoryCache[$parent]['dirs_raw']) && end($this->directoryCache[$parent]['dirs_raw']) != '')
                {
                    $dirName = end($this->directoryCache[$parent]['dirs_raw']);
                } // if

                $this->log->debug('> Writing file: "' . $location . 'hosts.mk" (' . $dataCount . ' host' . ($dataCount == 1 ? '' : 's') . ')');

                $currentlyExporting = array_map(
                    function ($p_string)
                    {
                        return current(explode('|', ltrim($p_string, '" ')));
                    },
                    $data
                );

                $content = '# Written by i-doit' . PHP_EOL . '# encoding: utf-8' . PHP_EOL;

                if ($dataCount)
                {
                    $content .= PHP_EOL . PHP_EOL . 'all_hosts += [' . PHP_EOL . '%s' . PHP_EOL . ']';
                } // if

                $content .= $this->renderIPAddressesByHost($currentlyExporting);

                if (isset($clusterData[$parent]) && is_array($clusterData[$parent]) && count($clusterData[$parent]))
                {
                    $content .= PHP_EOL . PHP_EOL . '# Add clusters' . PHP_EOL . 'clusters.update({' . PHP_EOL . implode(
                            ',' . PHP_EOL,
                            $clusterData[$parent]
                        ) . PHP_EOL . '})';
                } // if

                if ($this->configuration[$masterConfig]['options']['lock_hosts'])
                {
                    $content .= PHP_EOL . PHP_EOL . '# Lock the file!' . PHP_EOL . '_lock = True';
                } // if

                file_put_contents($location . 'hosts.mk', utf8_decode(str_replace('%s', implode(PHP_EOL, $data ?: []), $content)));
                $this->exportedFiles[$masterConfig][] = str_replace(DS, '/', $location) . 'hosts.mk';

                // Writing the ".wato".
                $this->log->debug('> Writing file: "' . $location . '.wato"');

                file_put_contents(
                    $location . '.wato',
                    utf8_decode(
                        "{'attributes': {}, 'num_hosts': " . $dataCount . ", 'title': u'" . $dirName . "'" . ($this->configuration[$masterConfig]['options']['lock_folders'] ? ", 'lock': True" : '') . "}"
                    )
                );

                $this->exportedFiles[$masterConfig][] = str_replace(DS, '/', $location) . '.wato';
            } // foreach
        } // foreach

        return $this;
    } // function

    /**
     * This method will create the remaining WATO files in folders without hosts.
     *
     * @return  $this
     */
    protected function exportRemainingWATOFiles()
    {
        $created = false;
        $this->log->info('Creating remaining ".wato" files...');

        foreach ($this->directoryCache as $paths)
        {
            $path = $paths['export_to'];

            if (is_array($paths['dirs']))
            {
                foreach ($paths['dirs'] as $index => $dirPart)
                {
                    $path .= $dirPart . DS;

                    if (!file_exists($path . '.wato'))
                    {
                        $created = true;
                        $this->log->debug('> Writing file: "' . $path . '.wato"');
                        $this->exportedFiles[$paths['config_id']][] = str_replace(DS, '/', $path) . '.wato';

                        file_put_contents(
                            $path . '.wato',
                            utf8_decode(
                                "{'attributes': {}, 'num_hosts': 0, 'title': u'" . $paths['dirs_raw'][$index] . "'" . ($this->configuration[$paths['config_id']]['options']['lock_folders'] ? ", 'lock': True" : '') . "}"
                            )
                        );
                    } // if
                } // foreach
            } // if
        } // foreach

        if (!$created)
        {
            $this->log->info(' > No remaining ".wato" files needed!');
        } // if

        return $this;
    } // function

    /**
     * This method will save all generic tags inside the database.
     *
     * @return  $this
     */
    protected function saveExportedGenericTags()
    {
        \isys_check_mk_helper_tag::save_exported_tags_to_database();

        return $this;
    } // function

    /**
     * Finally export all collected tags.
     *
     * @return  $this
     */
    protected function exportTags()
    {
        $this->log->info('Exporting: host tags');

        \isys_check_mk_helper::init();

        $return = [
            '# Written by i-doit',
            '# encoding: utf-8',
            '',
            'wato_host_tags += ['
        ];

        // Get the generic tags of the current object.
        $tags = \isys_check_mk_helper_tag::get_tags_for_export($this->options['language']);

        $returnGroup = [];

        if (is_array($tags) && count($tags))
        {
            foreach ($tags as $tagGroup => $tagData)
            {
                $returnTag = [];

                foreach ($tagData as $tag)
                {
                    $tag['aux'] = [];

                    if (strpos($tag['id'], '|') !== false)
                    {
                        $tag['aux'] = array_slice(explode('|', $tag['id']), 1);
                        $tag['id']  = current(explode('|', $tag['id']));
                    } // if

                    $returnTag[] = '    ("' . $tag['id'] . '", u"' . $tag['name'] . '", ' . \isys_format_json::encode($tag['aux']) . ')';
                } // foreach

                if (strpos($tagGroup, 'C__') !== false && strpos($tagGroup, '::') > 0)
                {
                    list($tagGroup, $tagGroupName) = explode('::', $tagGroup);
                }
                else if (strpos($tagGroup, '__') > 0)
                {
                    list($tagGroup, $tagGroupName) = explode('__', $tagGroup);
                }
                else
                {
                    $tagGroupName = $tagGroup;
                } // if

                $returnGroup[] = '  ("' . \isys_check_mk_helper_tag::prepare_valid_tag_name($tagGroup) . '", u"i-doit/' . $tagGroupName . '", [' . PHP_EOL . implode(
                        ',' . PHP_EOL,
                        $returnTag
                    ) . '])';
            } // foreach

            $return[] = implode(',' . PHP_EOL, $returnGroup) . ']';
            $return[] = '';
            $return[] = '# Lock the file!';
            $return[] = '_lock = True';

            foreach ($this->orderedConfiguration as $config)
            {
                $masterConfig = array_search(true, $config, true);

                if ($masterConfig === false)
                {
                    $this->log->error('The master configuration between ' . implode(', ', $config) . ' could not be found - need to skip.');
                    continue;
                } // if

                $this->log->debug('> Writing hosts and tags to file: "' . $this->configuration[$masterConfig]['path'] . 'idoit_hosttags.mk"');
                $this->exportedFiles[$masterConfig][] = str_replace(DS, '/', $this->configuration[$masterConfig]['path']) . 'idoit_hosttags.mk';

                file_put_contents($this->configuration[$masterConfig]['path'] . 'idoit_hosttags.mk', utf8_decode(implode(PHP_EOL, $return)));
            } // foreach
        }
        else
        {
            $this->log->info('> No host tags to write');
        } // if

        return $this;
    } // function

    /**
     * Also export all connected contact groups.
     *
     * @return  $this
     */
    protected function exportContactGroups()
    {
        $this->log->info('Exporting: contact groups');

        foreach ($this->orderedConfiguration as $config)
        {
            $masterConfig = array_search(true, $config, true);

            if ($masterConfig === false)
            {
                $this->log->error('The master configuration between ' . implode(', ', $config) . ' could not be found - need to skip.');
                continue;
            } // if

            $contactGroups = [];
            $return        = [
                '# Written by i-doit',
                '# encoding: utf-8',
                '',
                'host_contactgroups += ['
            ];

            foreach ($this->hosts as $hostName => $host)
            {
                if (!isset($config[$host['isys_catg_cmk_list__isys_monitoring_export_config__id']]))
                {
                    continue;
                } // if

                if (is_array($host['contacts']) && count($host['contacts']))
                {
                    foreach ($host['contacts'] as $contact)
                    {

                        if (!isset($contactGroups[$contact]))
                        {
                            $contactGroups[$contact] = [];
                        } // if

                        $contactGroups[$contact][] = $hostName;
                    } // foreach
                } // if
            } // foreach

            if (count($contactGroups))
            {
                foreach ($contactGroups as $contact => $hosts)
                {
                    $return[] = "  ('" . $contact . "', " . \isys_format_json::encode(array_unique(array_filter($hosts))) . "),";
                } // foreach

                $return[] = ']';
                $return[] = '';
                $return[] = '# Lock the file!';
                $return[] = '_lock = True';

                $this->log->debug('> Writing contact groups to file: "' . $this->configuration[$masterConfig]['path'] . 'rules.mk"');
                $this->exportedFiles[$masterConfig][] = str_replace(DS, '/', $this->configuration[$masterConfig]['path']) . 'rules.mk';

                file_put_contents($this->configuration[$masterConfig]['path'] . 'rules.mk', utf8_decode(implode(PHP_EOL, $return)));
            }
            else
            {
                $this->log->debug('> No contact groups need to be written ("' . $this->configuration[$masterConfig]['title'] . '").');
            } // if
        } // foreach

        return $this;
    } // function

    /**
     * Method for creating a TarGZ file which contains the exported files.
     *
     * @return  $this
     */
    protected function createTarballFile()
    {
        $this->log->info('Creating TarGZ files with the exported files.');

        if (!extension_loaded('phar') || !class_exists('Phar'))
        {
            $this->log->warning('The phar extension seems to be missing. The TarGZ file could not be created!');

            return $this;
        } // if

        foreach ($this->orderedConfiguration as $config)
        {
            $masterConfig = array_search(true, $config, true);

            if ($masterConfig === false)
            {
                $this->log->error('The master configuration between ' . implode(', ', $config) . ' could not be found - need to skip.');
                continue;
            } // if

            $path             = $this->configuration[$masterConfig]['path'];
            $directorySegment = end(explode(DS, trim($path, DS)));

            $phar = new \PharData($path . $directorySegment . '.tar');
            $this->log->info('Creating TarGZ "' . $path . $directorySegment . '.tar' . '"... ');

            $phar->buildFromDirectory($path);
            $phar->compress(\Phar::GZ);

            try
            {
                // Try to unlink the ".tar" file, sinze "compress" has created a new ".tar.gz" file.
                if (file_exists($path . 'check_mk_config.tar'))
                {
                    @unlink($path . 'check_mk_config.tar');
                } // if
            }
            catch (\Exception $e)
            {
                $this->log->warning('Tried to remove the unused file "' . $path . $directorySegment . '.tar", but got "permission denied" error.');
            } // try

            $this->log->notice(' > Finished!');
        } // foreach

        return $this;
    } // function

    /**
     * Method for creating a ZIP file which contains the exported files.
     *
     * @return  $this
     */
    protected function createZipFile()
    {
        $this->log->info('Creating ZIP files with the exported files.');

        if (!extension_loaded('zlib') || !class_exists('ZipArchive'))
        {
            $this->log->warning('The zlip extension seems to be missing. The ZIP file could not be created!');

            return $this;
        } // if

        $zip = new \ZipArchive();

        foreach ($this->orderedConfiguration as $config)
        {
            $masterConfig = array_search(true, $config, true);

            if ($masterConfig === false)
            {
                $this->log->error('The master configuration between ' . implode(', ', $config) . ' could not be found - need to skip.');
                continue;
            } // if

            $path       = $this->configuration[$masterConfig]['path'];
            $cut        = isys_strlen($path);
            $dirSegment = end(explode(DS, trim($path, DS)));

            if ($zip->open($path . $dirSegment . '.zip', \ZipArchive::CREATE) === true)
            {
                $this->log->info('Creating ZIP "' . $path . $dirSegment . '.zip' . '".');

                if (is_array($this->exportedFiles[$masterConfig]))
                {
                    foreach ($this->exportedFiles[$masterConfig] as $file)
                    {
                        $this->log->debug('Adding "' . $file . '"');
                        $zip->addFile($file, substr($file, $cut));
                    } // foreach
                } // if

                $zip->close();

                $this->log->notice(' > Finished!');
            } // if
        } // foreach

        return $this;
    } // function

    /**
     * Internal method for retrieving all tags of a given object (host or cluster).
     *
     * @param   array $row
     *
     * @return  array
     * @throws  \idoit\Exception\JsonException
     */
    protected function retrieveTags(array $row)
    {
        $dao           = \isys_cmdb_dao_category_property::instance(\isys_application::instance()->database);
        $daoTag        = \isys_cmdb_dao_category_g_cmk_tag::instance(\isys_application::instance()->database);
        $daoTagGroups  = \isys_factory_cmdb_dialog_dao::get_instance('isys_check_mk_tag_groups', \isys_application::instance()->database);
        $daoStaticTags = \isys_factory_cmdb_dialog_dao::get_instance('isys_check_mk_tags', \isys_application::instance()->database);

        $tags = \isys_check_mk_helper_tag::factory($row['isys_obj_type__id'])
            ->get_cmdb_tags($row['isys_obj__id']);

        if (count($tags))
        {
            $res = $dao->retrieve_properties(array_keys($tags));

            if (count($res))
            {
                while ($propertyRow = $res->get_row())
                {
                    $this->watoHostTagConnections[$row['hostname']][$propertyRow['const'] . '__' . $propertyRow['key']] = $tags[$propertyRow['id']];
                } // while
            } // if
        } // if

        // Get the dynamic tags of the current object.
        $dynamicTags = \isys_check_mk_helper_tag::factory($row['isys_obj_type__id'])
            ->get_dynamic_tags($row['isys_obj__id']);

        foreach ($dynamicTags as $dynamicTag)
        {
            $tags[] = $dynamicTag;
        } // foreach

        // Now retrieve the tags, defined explicitly.
        $definedTags = \isys_format_json::decode(
            $daoTag->get_data(null, $row['isys_obj__id'])
                ->get_row_value('isys_catg_cmk_tag_list__tags')
        );

        if (is_array($definedTags) && count($definedTags) > 0)
        {
            foreach ($definedTags as $tag)
            {
                $staticTag = $daoStaticTags->get_data($tag);

                if ($staticTag !== false && is_array($staticTag))
                {
                    $tagGroup = $daoTagGroups->get_data($staticTag['isys_check_mk_tags__isys_check_mk_tag_groups__id']);
                    $tagGroup = \isys_check_mk_helper_tag::prepare_valid_tag_name(_L($tagGroup['isys_check_mk_tag_groups__title']));
                    $tag      = \isys_check_mk_helper_tag::prepare_valid_tag_name($staticTag['isys_check_mk_tags__unique_name']);

                    // We memorize which object has which tag of which tag-group.
                    $this->watoHostTagConnections[$row['hostname']][$tagGroup] = $tag;

                    // Finally we prepare the tag-name to be valid.
                    $tags[] = $tag;
                } // if
            } // foreach
        } // if

        // Add the "site", if available.
        if ($row['isys_catg_cmk_list__isys_monitoring_export_config__id'] > 0 && isset($this->configuration[$row['isys_catg_cmk_list__isys_monitoring_export_config__id']]))
        {
            if ($this->configuration[$row['isys_catg_cmk_list__isys_monitoring_export_config__id']]['options']['multisite'])
            {
                // Bugfix RT#27414 - suggested by user.
                $sitename = $this->configuration[$row['isys_catg_cmk_list__isys_monitoring_export_config__id']]['options']['site'];

                $tags[]   = 'site:' . $sitename;

                $this->watoHostTagConnections[$row['hostname']]['_site_'] = $sitename;
            } // if
        } // if

        // This part is not really a tag, but it helps us displaying the correct amount of pipes ("|").
        $tags[] = '/" + FOLDER_PATH + "/"';

        return $tags;
    } // function

    /**
     * This method renders the necessary hostnames and IP addresses for each given host object.
     *
     * @param   array $hosts
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function renderIPAddressesByHost($hosts)
    {
        $return = $ipaddress = $hostAttributes = [];

        foreach ($hosts as $hostname)
        {
            $attributes = [];
            $data       = $this->hosts[$hostname];

            if (!empty($data['ip']))
            {
                $ipaddress[]  = '  "' . $data['hostname'] . '": u"' . $data['ip'] . '"';
                $attributes[] = '"ipaddress": u"' . $data['ip'] . '"';
            } // if

            if (count($this->watoHostTagConnections[$hostname]))
            {
                foreach ($this->watoHostTagConnections[$hostname] as $tagGroup => $tag)
                {
                    // Bugfix RT#27414 - suggested by user.
                    if ($tagGroup == "_site_")
                    {
                        $attributes[] = '"site": u"' . $tag . '"';
                    }
                    else
                    {
                        if (strpos($tag, "|") > 0)
                        {
                            $tag = str_split($tag, strpos($tag, "|"))[0];
                        } // if

                        $attributes[] = '"tag_' . $tagGroup . '": u"' . $tag . '"';
                    } // if
                } // foreach
            } // if

            if (count($attributes))
            {
                $hostAttributes[] = '  "' . $data['hostname'] . '": {' . implode(', ', $attributes) . '}';
            } // if
        } // foreach

        if (count($ipaddress))
        {
            $return[] = "";
            $return[] = "# Explicit IP addresses";
            $return[] = "ipaddresses.update({";
            $return[] = implode(',' . PHP_EOL, $ipaddress);
            $return[] = "})";
        } // if

        if (count($hostAttributes))
        {
            $return[] = "";
            $return[] = "# Host attributes (needed for WATO)";
            $return[] = "host_attributes.update({";
            $return[] = implode("," . PHP_EOL, $hostAttributes);
            $return[] = "})";
        } // if

        return implode(PHP_EOL, $return);
    } // function

    /**
     * Export constructor.
     *
     * @param  array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge(
            [
                'export_dir'       => 'check_mk_export',
                'debug'            => true,
                'export_structure' => self::STRUCTURE_NONE,
                'language'         => null
            ],
            $options
        );

        // Initialize the exported files array.
        $this->exportedFiles = new \isys_array;

        // Set the umask.
        $this->umask = umask(0);

        // Retrieve all export configurations - merge the "Multisite" configurations.
        $this->dao = \isys_cmdb_dao_category_g_cmk::instance(\isys_application::instance()->database);

        // Use the "TestHandler" to retrieve the log records later on.
        $this->logHandler = (new TestHandler())->setFormatter(new LineFormatter("%message% %context%\n", null, false, true));

        // Use the "StreamHandler" to write the log records to a log file.
        $streamHandler = (new StreamHandler(BASE_DIR . 'log/import_check_mk_export_' . date('Y-m-d_H-i-s') . '.log', Logger::DEBUG))->setFormatter(
            new LineFormatter("[%datetime%] %level_name%: %message%\n")
        );

        $this->log = new Logger(
            'i-doit Check_MK Export', [
                $streamHandler,
                $this->logHandler
            ]
        );

        $this->prepareExportConfigurations();
    } // function
} // class