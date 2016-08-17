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
namespace idoit\Module\Cmdb\Search\IndexExtension;

use idoit\Component\Provider\Singleton;
use isys_component_signalcollection as SignalCollection;

/**
 * i-doit
 *
 * Signal manager for search index signals
 *
 * @package     i-doit
 * @subpackage  Cmdb
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class Signals
{
    use Singleton;

    /**
     * @var int
     */
    private $startTime;

    /**
     * Disconnect afterCategoryEntrySave Event
     *
     * @throws \Exception
     */
    public function disconnectOnAfterCategoryEntrySave()
    {
        SignalCollection::get_instance()
            ->disconnect(
                'mod.cmdb.afterCategoryEntrySave',
                [
                    $this,
                    'onAfterCategoryEntrySave'
                ]
            );
    }

    /**
     * @param \isys_cmdb_dao_category $dao
     * @param                         $categoryID
     * @param                         $saveSuccess
     * @param                         $objectID
     * @param                         $posts
     * @param                         $changes
     */
    public function onAfterCategoryEntrySave(\isys_cmdb_dao_category $dao, $categoryID, $saveSuccess, $objectID, $posts, $changes)
    {
        if ($dao instanceof \isys_cmdb_dao_category_g_custom_fields)
        {
            $customId = $dao->get_catg_custom_id();

            if ($customId > 0)
            {
                $onelineIndexer = new OnelineIndexer($dao->get_database_component());
                $onelineIndexer->indexCustomCategory($customId);

                return;
            }
        }
        else
        {
            CategoryIndexer::index([$objectID], [$dao->get_category_const()]);
        }
    }

    /**
     * @param        $importStartTime
     * @param array  $importedGlobalCategories
     * @param array  $importedSpecifcCategories
     * @param string $importType
     * @param array  $rawData
     *
     * @todo use $categoryMap to only index imported categories
     */
    public function onAfterCsvImport(/*$modCsvImport, $transformedData, $cretedObjects, $categoryMap*/)
    {
        $this->onPostImport($this->startTime);
    }

    /**
     * @param int   $importStartTime
     * @param array $importedGlobalCategories
     * @param array $importedSpecifcCategories
     */
    public function onPostImport($importStartTime, $importedGlobalCategories = null, $importedSpecifcCategories = null /*, $importType = '', $rawData = []*/)
    {
        if ($importStartTime > 0)
        {
            $l_changed_objects = [];
            $l_objects         = \isys_cmdb_dao_nexgen::instance(\isys_application::instance()->database)
                ->get_objects(
                    [
                        'changed_after' => date(
                            'Y-m-d H:i:s',
                            $importStartTime - 180
                        )
                    ]
                );
            while ($l_row = $l_objects->get_row())
            {
                $l_changed_objects[] = $l_row['isys_obj__id'];
            }

            $onelineIndexer = new \idoit\Module\Cmdb\Search\IndexExtension\OnelineIndexer(
                \isys_application::instance()->database
            );

            if ($importedGlobalCategories)
            {
                $onelineIndexer->setCategoriesToBeIndexed($importedGlobalCategories, $importedSpecifcCategories);
            }

            $onelineIndexer->setObjectIds($l_changed_objects)
                ->index();
        }
    }

    /**
     * @param \isys_cmdb_dao_category $dao
     * @param array                   $rawData
     * @param array                   $changes
     */
    public function onMultiEditSaved(\isys_cmdb_dao_category $dao, $rawData, $changes)
    {
        CategoryIndexer::index(array_keys($changes), [$dao->get_category_const()]);
    }

    /**
     * @param array                     $objects
     * @param int                       $templateID
     * @param \isys_import_handler_cmdb $importHandler
     */
    public function onMassChangeApplied(array $objects, $templateID, \isys_import_handler_cmdb $importHandler)
    {
        CategoryIndexer::index(array_keys($objects), \isys_handler_search_index::defaultCategoryBlacklist());
    }

    /**
     * @param int            $p_objectID
     * @param \isys_cmdb_dao $dao
     */
    public function onObjectDeleted($objectID, $dao)
    {
        $database = $dao->get_database_component();

        /**
         * @todo We need an observer pattern here as well as soon as elasticserach is implemented
         */
        if ($objectID > 0)
        {
            if ($database)
            {
                $database->query(
                    'DELETE FROM isys_search_idx WHERE isys_search_idx__reference = ' . (int) $objectID
                );
            }
            else
            {
                \isys_application::instance()->logger->warning(
                    'Search-Index error: Object with id ' . $objectID . ' not removed from index. Database not available at this stage.',
                    ['trace' => debug_backtrace()]
                );
            }
        }
    }

    /**
     * @param \isys_cmdb_dao_category $dao
     * @param                         $p_objectID
     */
    public function onBeforeRankRecord(\isys_cmdb_dao $dao, $objectID, $categoryID = null, $title, $row, $table, $currentStatus, $newStatus, $categoryType, $direction)
    {
        if ($objectID > 0 && $categoryID > 0 && $newStatus == C__RECORD_STATUS__PURGE)
        {
            $dao->get_database_component()
                ->query(
                    'DELETE FROM isys_search_idx WHERE ' . 'isys_search_idx__reference = ' . (int) $objectID . ' AND ' . 'isys_search_idx__key LIKE ' . $dao->convert_sql_text(
                        '%.' . $dao->get_category() . '.' . $categoryID . '%'
                    ) . ';'
                );
        }
    }

    /**
     * Connect all signals
     */
    public function connect()
    {
        $this->startTime = microtime(true);

        SignalCollection::get_instance()
            ->connect(
                'mod.cmdb.afterCategoryEntrySave',
                [
                    $this,
                    'onAfterCategoryEntrySave'
                ]
            )
            ->connect(
                'mod.cmdb.objectDeleted',
                [
                    $this,
                    'onObjectDeleted'
                ]
            )
            ->connect(
                'mod.cmdb.beforeRankRecord',
                [
                    $this,
                    'onBeforeRankRecord'
                ]
            )
            ->connect(
                'mod.cmdb.massChangeApplied',
                [
                    $this,
                    'onMassChangeApplied'
                ]
            )
            ->connect(
                'mod.cmdb.multiEditSaved',
                [
                    $this,
                    'onMultiEditSaved'
                ]
            )
            ->connect(
                'mod.import_csv.afterImport',
                [
                    $this,
                    'onAfterCsvImport'
                ]
            )
            ->connect(
                'mod.cmdb.afterLegacyImport',
                [
                    $this,
                    'onPostImport'
                ]
            );
    }
}