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
namespace idoit\Module\Search\Index;

use idoit\Module\Search\Index\Protocol\ObservableIndexManager;
use idoit\Module\Search\Index\Traits\IndexCounterTrait;

/**
 * i-doit
 *
 * Search index manager
 *
 * Uses observable pattern to inform several handles about the indexed documents's.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class Indexer
{
    use IndexCounterTrait;

    /**
     * @var ObservableIndexManager[]
     */
    private static $indexManagers = [];

    /**
     * @param ObservableIndexManager $manager
     */
    public static function attachManager(ObservableIndexManager $manager)
    {
        self::$indexManagers[] = $manager;
    }

    /**
     * Create index
     *
     * @return Indexer
     */
    public function create()
    {
        foreach (self::$indexManagers as $manager)
        {
            $manager->create();

            $this->indexedDocuments += $manager->getIndexedDocuments();
            $this->indexedItems += $manager->getIndexedItems();
        }

        return $this;
    }

    /**
     * @param $referenceID
     */
    public function remove($referenceID)
    {
        foreach (self::$indexManagers as $manager)
        {
            $manager->remove($referenceID);
        }
    }
}