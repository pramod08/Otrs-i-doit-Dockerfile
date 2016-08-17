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
namespace idoit\Module\Cmdb\Search\IndexExtension\Manager;

use idoit\Component\Provider\Factory;
use idoit\Module\Cmdb\Model\Ci;
use idoit\Module\Cmdb\Search\IndexExtension\Config;
use idoit\Module\Cmdb\Search\IndexExtension\ModelLoader;
use idoit\Module\Search\Index\Document;
use idoit\Module\Search\Index\Protocol\ObservableIndexManager;
use idoit\Module\Search\Index\Traits\IndexCounterTrait;
use idoit\Module\Search\Index\Traits\IndexNameTrait;
use idoit\Module\Search\Index\Traits\IndexObserverTrait;
use isys_application as Application;
use isys_cmdb_dao as CmdbDao;
use isys_cmdb_dao_category_data as CategoryData;

/**
 * i-doit
 *
 * SubjectObserver Manager for CMDB indexes
 *
 * Retrieves all indexed cis and informes all observers about them (notify)
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class CmdbIndexManager implements ObservableIndexManager
{
    use IndexObserverTrait, IndexCounterTrait, IndexNameTrait, Factory;

    /**
     * Indexing options
     *
     * @var Config
     */
    private $config;

    /**
     * Flatten the object structure into a Document
     *
     * @param Ci         $object
     * @param bool|false $withDynamicProperties
     *
     * @return Document
     */
    private function flatten(Ci $object, $withDynamicProperties = false)
    {
        $document = Document::factory()
            ->setType('cmdb')
            ->setId($object->id)
            ->setTitle($object->title);

        $documentData = [];

        foreach ($object->getData() as $data)
        {
            foreach ($data->data as $index => $categories)
            {
                foreach ($categories->getDataValues() as $key => $value)
                {
                    if (($withDynamicProperties || $key[0] !== '_') && $value != '<br>' && $value !== '')
                    {
                        $documentData[$object->type->id . '.' . $data->key . '.' . $index . '.' . $key] = trim($value);
                    }
                }
            }
        }

        $document->setData($documentData);

        return $document;
    }

    /**
     * Create Ci Index
     */
    public function create()
    {
        //$memoryWatcher =

        /**
         * Bugfix: create a quick index for custom categories, since they are not indexed well
         */
        /*
        $onelineIndexer = new \idoit\Module\Cmdb\Search\IndexExtension\OnelineIndexer(Application::instance()->database);
        $onelineIndexer->setObjectIds($this->config->getObjectIds());

        if (!in_array('C__CATG__CUSTOM_FIELDS', $this->config->getCategoryBlacklist()))
        {
            $onelineIndexer->indexCustomCategories();
        }
        if (!in_array('C__CATG__IP', $this->config->getCategoryBlacklist()))
        {
            $onelineIndexer->indexIpAddresses(); // ip addresses are not indexed as well, so use the quick variant here
        }
        unset($onelineIndexer);
        */

        $cmdbDao = CmdbDao::factory(Application::instance()->database);

        // Get ModelLoader
        $modelLoader = new ModelLoader(
            new CategoryData()
        );

        // Retrieve all objects
        $allObjectsResult = $cmdbDao->get_objects(
            [
                // Retrieve only a range of objects
                'ids'          => $this->config->getObjectIds(),
                // Filter by object type blacklist
                'exclude_type' => $this->config->getObjectTypeBlacklist()
            ],
            'isys_obj__title',
            'ASC'
        );

        $this->documentsToBeIndexed = count($allObjectsResult);

        // Iterate through objects and create index
        while ($objectRow = $allObjectsResult->get_row())
        {
            try
            {
                // Increase indexedCi counter now, so that observers are aware of the current status
                $this->indexedDocuments++;

                // Get Ci Model
                $ciModel = $modelLoader->loadWith(
                    $objectRow,
                    $this->config->getCategoryBlacklist(),
                    $this->config->getProvidesFlags()
                );

                $this->notify(
                    $flatCi = $this->flatten($ciModel), // Document
                    $this
                );

                $this->indexedItems += count($flatCi->getData());
                unset($object, $flatObject, $objectRow, $flatCi, $ciModel);
            }
            catch (\Exception $e)
            {
                //var_dump($e);
            }
        }

        // Free mysql result
        $allObjectsResult->free_result();

        unset($allObjectsResult, $modelLoader, $cmdbDao);
    }

    /**
     * @param $referenceId
     */
    public function remove($referenceId)
    {

    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * CmdbIndexManager constructor.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

}