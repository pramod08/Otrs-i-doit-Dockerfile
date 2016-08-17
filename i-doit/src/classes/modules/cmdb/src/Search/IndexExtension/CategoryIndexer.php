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

use idoit\Module\Cmdb\Model\Ci;
use idoit\Module\Cmdb\Model\Ci\Category;
use idoit\Module\Search\Index\Observer\ObserverRegistry;

/**
 * i-doit
 *
 * Specific object and category indexer
 *
 * @package     i-doit
 * @subpackage  Cmdb
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class CategoryIndexer
{

    /**
     * @param int[]    $objectID
     * @param string[] $categoryConst
     */
    public static function index(array $objectIDs, array $categoryConst = [])
    {
        if (count($objectIDs))
        {
            $dao = \isys_cmdb_dao_category_g_global::instance(\isys_application::instance()->database);

            $categoryBlacklist = [C__CATG__VIRTUAL];

            // Retrieve all categories of type $categoryTypes
            $allCategories = $dao->get_all_categories();

            $categoryKeys = array_flip($categoryConst);

            foreach ([
                         C__CMDB__CATEGORY__TYPE_GLOBAL,
                         C__CMDB__CATEGORY__TYPE_SPECIFIC
                     ] as $categoryType)
            {
                foreach ($allCategories[$categoryType] as $l_cat)
                {
                    // Blacklist every category but the current one
                    if (!isset($categoryKeys[$l_cat['const']]))
                    {
                        $categoryBlacklist[] = $l_cat['const'];
                    }
                }
            }

            // Get instance of ci index manager with current object id(s) as object range
            $ciIndexManager = new \idoit\Module\Cmdb\Search\IndexExtension\Manager\CmdbIndexManager(
                new \idoit\Module\Cmdb\Search\IndexExtension\Config(
                    [], $categoryBlacklist, $objectIDs, [
                        C__PROPERTY__PROVIDES__SEARCH
                    ]
                )
            );

            // Get registered observers
            foreach (ObserverRegistry::get() as $observer)
            {
                $ciIndexManager->attach($observer);
            }


            // re-create index for current object and current category
            $ciIndexManager->create();
        }

    }

}