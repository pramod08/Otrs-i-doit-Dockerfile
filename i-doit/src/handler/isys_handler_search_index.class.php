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

use isys_cmdb_dao as CmdbDao;

/**
 * i-doit
 *
 * Search index controller
 *
 * @package    i-doit
 * @subpackage General
 * @author     Dennis Stücken <dstuecken@i-doit.com>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 *
 */
class isys_handler_search_index extends isys_handler implements \idoit\Module\Search\Index\Protocol\Observer
{
    /**
     * @var array
     */
    public static $defaultObjectTypeBlacklist = [
        C__OBJTYPE__CONTAINER,
        C__OBJTYPE__GENERIC_TEMPLATE,
        C__OBJTYPE__PARALLEL_RELATION,
        C__OBJTYPE__RELATION,
        C__OBJTYPE__LOCATION_GENERIC,
        C__OBJTYPE__MIGRATION_OBJECT,
        C__OBJTYPE__NAGIOS_HOST_TPL,
        C__OBJTYPE__NAGIOS_SERVICE_TPL,
        C__OBJTYPE__CABLE
    ];

    /**
     *   Skip indexing of the following categories:
     *
     *   - logbook, relation: these categories should not be searched
     *   - connector: this category is a unification of ports, fc-ports and ui, search results should lead into these categories
     *   - indentifier: this category is for referencing i-doit objects to foreign data sources
     *   - cats application
     *   - person login: because of the password
     *
     * @var array
     */
    public static $defaultCategoryBlacklist = [
        'C__CATG__LOGBOOK',
        'C__CATG__RELATION',
        'C__CATG__CONNECTOR',
        'C__CATG__IDENTIFIER',
        'C__CATS__APPLICATION',
        'C__CATS__PERSON_LOGIN',
    ];

    /**
     * @var array
     */
    public static $defaultCategoryTypeBlacklist = [
        isys_cmdb_dao_category::TYPE_ASSIGN,
        isys_cmdb_dao_category::TYPE_FOLDER,
        isys_cmdb_dao_category::TYPE_REAR,
        isys_cmdb_dao_category::TYPE_VIEW
    ];

    /**
     * @var array
     */
    public static $defaultCategoryTypes = [
        isys_cmdb_dao_category::TYPE_EDIT
    ];

    /**
     * @return array
     */
    public static function defaultCategoryTypes()
    {
        return self::$defaultCategoryTypes;
    }

    /**
     * @return array
     */
    public static function defaultCategoryTypeBlacklist()
    {
        return self::$defaultCategoryTypeBlacklist;
    }

    /**
     * @return array
     */
    public static function initCategoryBlacklist()
    {
        return self::$defaultCategoryBlacklist;
    }

    /**
     * @return array
     */
    public static function defaultObjectTypeBlacklist()
    {
        return self::$defaultObjectTypeBlacklist;
    }

    /**
     * Get array of category for the full default index
     *
     * @return array
     */
    public static function defaultCategoryBlacklist()
    {
        // Get dao instance
        $cmdbDao = CmdbDao::instance(isys_application::instance()->database);

        // Initialize default category blacklist
        $categoryBlacklist = self::initCategoryBlacklist();

        // Retrieve all categories of type $categoryTypes, we should only get categories here that should not be indexed!
        $allCategories = $cmdbDao->get_all_categories(self::defaultCategoryTypeBlacklist());

        foreach ([
                     C__CMDB__CATEGORY__TYPE_GLOBAL,
                     C__CMDB__CATEGORY__TYPE_SPECIFIC,
                     C__CMDB__CATEGORY__TYPE_CUSTOM
                 ] as $categoryType)
        {
            foreach ($allCategories[$categoryType] as $l_cat)
            {
                // Blacklist this category as "not-to-be-indexed"
                $categoryBlacklist[] = $l_cat['const'];
            }
        }

        return $categoryBlacklist;
    }

    /**
     * Show usage information
     */
    public function usage($p_error = false)
    {
        echo "\n\n" . C__COLOR__LIGHT_RED . "Create a search index for search auto-suggestion.\n" . C__COLOR__NO_COLOR;

        if ($p_error)
        {
            echo "\n" . C__COLOR__LIGHT_RED . "Missing parameter!\n" . C__COLOR__NO_COLOR;
        }

        echo "\nUsage:\n" . "reindex - build a full-text search index.\n\n" . "fullindex - build a more complete index (could be very slow and memory intensive).\n\n" .
            "search \"keyword\" - search within the index.\n\n" . "reindex - build the complete index.\n\n" . "Example:\n" . "controller -v -m search_index reindex\n" .
            PHP_EOL;
        die;
    }

    /**
     * @param \idoit\Module\Search\Index\Protocol\Document               $ci
     * @param \idoit\Module\Search\Index\Protocol\ObservableIndexManager $indexManager
     */
    public function update(\idoit\Module\Search\Index\Protocol\Document $ci, \idoit\Module\Search\Index\Protocol\ObservableIndexManager $indexManager)
    {
        $this->progress(
            $indexManager->getName(),
            $indexManager->getIndexedDocuments(),
            $indexManager->getDocumentsToBeIndexed(),
            42
        );
    }

    /**
     * Index Creation
     *
     * @param array $observers
     *
     * @return \idoit\Module\Search\Index\Indexer
     */
    public function createIndex($observers = [], $onlyIndexManagerNamed = '')
    {
        // Create Indexer instance
        $indexer = new \idoit\Module\Search\Index\Indexer();
        {
            /**
             * Retrieve all observers and merge with defaults
             */
            $observers = array_merge($observers, \idoit\Module\Search\Index\Observer\ObserverRegistry::get());

            // Attach Json for debugging purposes (writes jsonized index into temp/objects.json)
            //$observers[] = new \idoit\Module\Search\Index\Observer\JsonDump(isys_application::instance()->container),

            $categoryBlacklist   = self::defaultCategoryBlacklist();
            $objectTypeBlacklist = self::defaultObjectTypeBlacklist();

            // Iterate through registered index managers
            foreach (\idoit\Module\Search\Index\Registry::get($observers, $categoryBlacklist, $objectTypeBlacklist) as $name => $ciIndexManager)
            {
                if ($onlyIndexManagerNamed !== '' && $name !== $onlyIndexManagerNamed)
                {
                    continue;
                }

                foreach ($observers as $observerName => $observer)
                {
                    $ciIndexManager->attach($observer);
                }

                // Attach index manager to indexer
                $indexer->attachManager($ciIndexManager);
            }
        }

        // Start index creation
        $indexer->create();

        return $indexer;
    }

    /**
     * Create a search index
     */
    public function reindex($params)
    {
        $time_start = microtime(true);

        $observers = [];
        if ($GLOBALS['argc'])
        {
            // Attach self to retrieve status updates for cli commands
            $observers['Handler'] = $this;
        }

        // Use OnelineIndexer for fast index processing:
        \idoit\Module\Search\Index\Registry::unregister('CMDB');

        \idoit\Module\Search\Index\Registry::register(
            'CMDB-Reindex',
            function ()
            {
                return new \idoit\Module\Cmdb\Search\IndexExtension\OnelineIndexer(isys_application::instance()->database);
            }
        );

        $indexer = $this->createIndex($observers, isset($params[0]) ? $params[0] : '');

        // Sum it all up
        $time_end = microtime(true);
        $time     = number_format($time_end - $time_start, 2);

        if (function_exists('verbose'))
        {
            verbose(
                sprintf(
                    "Finished indexing in " . C__COLOR__GREEN . "%s" . C__COLOR__NO_COLOR . " seconds. Indexed %s keys from %s items(s).",
                    $time,
                    $indexer->getIndexedItems(),
                    $indexer->getIndexedDocuments()
                ) . ' ' . sprintf(
                    'Memory peak usage: ' . C__COLOR__RED . '%s' . C__COLOR__NO_COLOR . ' mb.',
                    number_format(memory_get_peak_usage(true) / 1024 / 1024, 2)
                ) . "\n"
            );
        }

    }

    /**
     * Create a search index
     */
    public function fullindex()
    {
        try
        {
            ini_set('memory_limit', isys_settings::get('system.memory-limit.searchindex', '4G'));

            $time_start = microtime(true);

            $observers = [];
            if ($GLOBALS['argc'])
            {
                // Attach self to retrieve status updates for cli commands
                $observers['Handler'] = $this;
            }

            $indexer = $this->createIndex($observers);

            // Sum it all up
            $time_end = microtime(true);
            $time     = number_format($time_end - $time_start, 2);

            verbose(
                sprintf(
                    "\nFinished indexing in " . C__COLOR__GREEN . "%s" . C__COLOR__NO_COLOR . " seconds. Indexed %s properties from %s objects(s).",
                    $time,
                    $indexer->getIndexedItems(),
                    $indexer->getIndexedDocuments()
                ) . ' ' . sprintf(
                    'Memory peak usage: ' . C__COLOR__RED . '%s' . C__COLOR__NO_COLOR . ' mb.',
                    number_format(memory_get_peak_usage(true) / 1024 / 1024, 2)
                ) . "\n"
            );

        }
        catch (Exception $e)
        {
            //var_dump($e);
            error($e->getMessage());
        }
    }

    /**
     * Initialize.
     */
    public function init()
    {
        global $argv;

        verbose("--------------------------------------------------");
        verbose(C__CONSOLE_LOGO__IDOIT . " search index");
        verbose("--------------------------------------------------");
        verbose("");
        verbose("");

        if (count($argv) === 0)
        {
            $this->usage();
        }
        else
        {

            if (method_exists($this, $argv[0]))
            {
                call_user_func(
                    [
                        $this,
                        $argv[0]
                    ],
                    array_slice($argv, 1)
                );
            }
        }
    }

    /**
     * @return bool
     */
    public function needs_login()
    {
        return true;
    }

    /**
     * @param $params
     */
    protected function search($params)
    {
        try
        {
            $time_start = microtime(true);

            if (!$params[0])
            {
                throw new Exception(
                    'You have to provide a valid search string. (controller -v -m search_index search "xyz")'
                );
            }

            $searchString = $params[0];

            verbose(
                "\n" . sprintf(
                    "Searching for string \"" . C__COLOR__GREEN . "%s" . C__COLOR__NO_COLOR . "\"",
                    $searchString
                ) . "\n"
            );

            $manager = \idoit\Module\Search\Query\QueryManager::factory()
                ->attachEngine(
                    new \idoit\Module\Search\Query\Engine\Mysql\SearchEngine()
                )
                ->addSearchKeyword($searchString);

            $queryResult = $manager->search();
            $result      = $queryResult->getResult();

            $consoleOutput = new \Symfony\Component\Console\Output\ConsoleOutput();
            $table         = new \Symfony\Component\Console\Helper\Table($consoleOutput);
            $table->setHeaders(
                [
                    'ID',
                    'Key',
                    'Found Match',
                    'Score'
                ]
            );

            foreach ($result as $item)
            {
                $table->addRow(
                    [
                        $item->getDocumentId(),
                        $item->getKey(),
                        C__COLOR__GREEN . $item->getValue() . C__COLOR__NO_COLOR,
                        C__COLOR__RED . number_format(floatval($item->getScore()), 2) . C__COLOR__NO_COLOR,
                    ]
                );
            }
            $table->render();

            // Sum it all up
            $time_end = microtime(true);
            $time     = number_format($time_end - $time_start, 4);

            verbose(
                sprintf(
                    "Search process took " . C__COLOR__GREEN . "%s" . C__COLOR__NO_COLOR . "s.",
                    $time
                ) . ' ' . "\n"
            );
        }
        catch (Exception $e)
        {
            error($e->getMessage());
        }
    }
}