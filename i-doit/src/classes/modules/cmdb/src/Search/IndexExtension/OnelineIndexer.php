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

use idoit\Module\Search\Index\Document;
use idoit\Module\Search\Index\Protocol\ObservableIndexManager;
use idoit\Module\Search\Index\Traits\IndexCounterTrait;
use idoit\Module\Search\Index\Traits\IndexNameTrait;
use idoit\Module\Search\Index\Traits\IndexObserverTrait;
use isys_component_database as Database;

/**
 * i-doit
 *
 * Model manager for indexing Ci documents
 *
 * @package     i-doit
 * @subpackage  Cmdb
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class OnelineIndexer implements ObservableIndexManager
{
    use IndexCounterTrait, IndexObserverTrait, IndexNameTrait;

    /**
     * Database component
     *
     * @var Database
     */
    private $database;

    /**
     * Index Version
     *
     * @var int
     */
    private $version = 1;

    /**
     * Categories to be indexed
     *
     * @var array
     */
    private $categories = null;

    /**
     * Object ids to be indexed
     *
     * @var array
     */
    private $objectIds = null;

    /**
     * @var string
     */
    private $replaceIntoString = 'REPLACE INTO isys_search_idx (isys_search_idx__version, isys_search_idx__type, isys_search_idx__key, isys_search_idx__value, isys_search_idx__reference) SELECT ';

    /**
     * @param  array $objectIds
     *
     * @return $this
     */
    public function setObjectIds(array $objectIds)
    {
        $this->objectIds = $objectIds;

        return $this;
    }

    /**
     * @return string
     */
    private function getObjectInCondition()
    {
        if ($this->objectIds !== null && count($this->objectIds))
        {
            if (count($this->objectIds) < 1000)
            {
                return ' AND isys_obj__id IN(' . implode(',', $this->objectIds) . ')';
            }
            else
            {
                //throw new \Exception('Too many objects.');
            }
        }

        return '';
    }

    /**
     * Prepare the indexing mechanism on the basis of category arrays with their ids as it's value.
     *
     * $catgList example:
     *  [1, 5, 10, 30] for global categories with ids 1, 5, 10 and 30.
     *
     * @param array $catgList
     * @param array $catsList
     * @param array $catcList
     *
     * @return $this|OnelineIndexer
     */
    public function setCategoriesToBeIndexed(array $catgList = [], array $catsList = [], array $catcList = [])
    {
        $dao           = \isys_cmdb_dao::instance(\isys_application::instance()->database);
        $allCategories = $dao->get_all_categories(\isys_handler_search_index::defaultCategoryTypes());

        $catgList = array_flip($catgList);
        $catsList = array_flip($catsList);
        $catcList = array_flip($catcList);

        foreach ($allCategories[C__CMDB__CATEGORY__TYPE_GLOBAL] as $key => $cat)
        {
            if (!isset($catgList[$cat['id']]))
            {
                unset($allCategories[C__CMDB__CATEGORY__TYPE_GLOBAL][$key]);
            }
        }

        foreach ($allCategories[C__CMDB__CATEGORY__TYPE_SPECIFIC] as $key => $cat)
        {
            if (!isset($catsList[$cat['id']]))
            {
                unset($allCategories[C__CMDB__CATEGORY__TYPE_SPECIFIC][$key]);
            }
        }

        foreach ($allCategories[C__CMDB__CATEGORY__TYPE_CUSTOM] as $key => $cat)
        {
            if (!isset($catcList[$cat['id']]))
            {
                unset($allCategories[C__CMDB__CATEGORY__TYPE_CUSTOM][$key]);
            }
        }

        $this->categories = $allCategories;

        return $this;
    }

    /**
     * @return $this
     */
    public function indexObjectTitles()
    {
        $this->database->query(
            $this->replaceIntoString . $this->version .
            ', \'cmdb\', CONCAT(isys_obj__isys_obj_type__id, \'.global.\', isys_catg_global_list__id, \'.title\'), isys_obj__title, isys_obj__id FROM isys_catg_global_list INNER JOIN isys_obj ON isys_catg_global_list__isys_obj__id = isys_obj__id INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id ' .
            'WHERE isys_obj_type__const NOT IN (\'C__OBJTYPE__RELATION\', \'C__OBJTYPE__CONTAINER\', \'C__OBJTYPE__GENERIC_TEMPLATE\', \'C__OBJTYPE__PARALLEL_RELATION\', \'C__OBJTYPE__LOCATION_GENERIC\', \'C__OBJTYPE__MIGRATION_OBJECT\', \'C__OBJTYPE__NAGIOS_HOST_TPL\', \'C__OBJTYPE__NAGIOS_SERVICE_TPL\', \'C__OBJTYPE__CABLE\')' .
            $this->getObjectInCondition() . ';'
        );

        return $this;
    }

    /**
     * @param array $objectIDs
     *
     * @return $this
     */
    public function indexCustomCategories()
    {
        $this->database->query(
            $this->replaceIntoString . $this->version .
            ', \'cmdb\', CONCAT(isys_obj__isys_obj_type__id, \'.custom_fields.\', isys_catg_custom_fields_list__data__id, \'.\', REPLACE(isysgui_catg_custom__title, \'.\', \'_\'), \'.\', isysgui_catg_custom__id, \'.\', isys_catg_custom_fields_list__field_key), isys_catg_custom_fields_list__field_content, isys_obj__id FROM isys_catg_custom_fields_list INNER JOIN isys_obj ON isys_catg_custom_fields_list__isys_obj__id = isys_obj__id INNER JOIN isysgui_catg_custom ON isys_catg_custom_fields_list__isysgui_catg_custom__id = isysgui_catg_custom__id WHERE isys_catg_custom_fields_list__field_type IN (\'f_text\', \'f_textarea\', \'f_link\')' .
            $this->getObjectInCondition() . ';'
        );

        return $this;
    }

    /**
     * Index content of one custom category
     *
     * @param int $customId isysgui_catg_custom id
     *
     * @return $this
     */
    public function indexCustomCategory($customId)
    {
        $this->database->query(
            $this->replaceIntoString . $this->version .
            ', \'cmdb\', CONCAT(isys_obj__isys_obj_type__id, \'.custom_fields.\', isys_catg_custom_fields_list__data__id, \'.\', REPLACE(isysgui_catg_custom__title, \'.\', \'_\'), \'.\', isysgui_catg_custom__id, \'.\', isys_catg_custom_fields_list__field_key), isys_catg_custom_fields_list__field_content, isys_obj__id FROM isys_catg_custom_fields_list INNER JOIN isys_obj ON isys_catg_custom_fields_list__isys_obj__id = isys_obj__id INNER JOIN isysgui_catg_custom ON isys_catg_custom_fields_list__isysgui_catg_custom__id = isysgui_catg_custom__id WHERE isys_catg_custom_fields_list__field_type IN (\'f_text\', \'f_textarea\', \'f_link\') AND isys_catg_custom_fields_list__isysgui_catg_custom__id = ' .
            (int) $customId . ';'
        );

        return $this;
    }

    /**
     * @param array $objectIDs
     *
     * @return $this
     */
    public function indexIpAddresses()
    {
        $this->database->query(
            $this->replaceIntoString . $this->version .
            ', \'cmdb\', CONCAT(isys_obj__isys_obj_type__id, \'.ip.\', isys_catg_ip_list__id, \'.hostaddress\'), isys_cats_net_ip_addresses_list__title, isys_catg_ip_list__isys_obj__id FROM isys_catg_ip_list INNER JOIN isys_obj ON isys_catg_ip_list__isys_obj__id = isys_obj__id INNER JOIN isys_cats_net_ip_addresses_list ON isys_catg_ip_list__isys_cats_net_ip_addresses_list__id = isys_cats_net_ip_addresses_list__id' .
            $this->getObjectInCondition() . ';'
        );

        return $this;
    }

    /**
     * @return $this
     */
    public function indexSerialNumbers()
    {
        $this->database->query(
            $this->replaceIntoString . $this->version .
            ', \'cmdb\', CONCAT(isys_obj__isys_obj_type__id, \'.model.\', isys_catg_model_list__id, \'.serial\'), isys_catg_model_list__serial, isys_catg_model_list__isys_obj__id FROM isys_catg_model_list INNER JOIN isys_obj ON isys_catg_model_list__isys_obj__id = isys_obj__id;'
        );

        return $this;
    }

    /**
     * @return $this
     */
    public function indexInventoryNumber()
    {
        $this->database->query(
            $this->replaceIntoString . $this->version .
            ', \'cmdb\', CONCAT(isys_obj__isys_obj_type__id, \'.accounting.\', isys_catg_accounting_list__id, \'.inventory_no\'), isys_catg_accounting_list__inventory_no, isys_catg_accounting_list__isys_obj__id FROM isys_catg_accounting_list INNER JOIN isys_obj ON isys_catg_accounting_list__isys_obj__id = isys_obj__id;'
        );

        return $this;
    }

    /**
     * @return $this
     */
    public function indexInvoiceNumber()
    {
        $this->database->query(
            $this->replaceIntoString . $this->version .
            ', \'cmdb\', CONCAT(isys_obj__isys_obj_type__id, \'.accounting.\', isys_catg_accounting_list__id, \'.invoice_no\'), isys_catg_accounting_list__invoice_no, isys_catg_accounting_list__isys_obj__id FROM isys_catg_accounting_list INNER JOIN isys_obj ON isys_catg_accounting_list__isys_obj__id = isys_obj__id;'
        );

        return $this;
    }

    /**
     * @return $this
     */
    public function indexOrderNumber()
    {
        $this->database->query(
            $this->replaceIntoString . $this->version .
            ', \'cmdb\', CONCAT(isys_obj__isys_obj_type__id, \'.accounting.\', isys_catg_accounting_list__id, \'.invoice_no\'), isys_catg_accounting_list__order_no, isys_catg_accounting_list__isys_obj__id FROM isys_catg_accounting_list INNER JOIN isys_obj ON isys_catg_accounting_list__isys_obj__id = isys_obj__id;'
        );

        return $this;
    }

    /**
     * @return $this
     */
    public function indexPortTitle()
    {
        $this->database->query(
            $this->replaceIntoString .
            ' 1, \'cmdb\', CONCAT(isys_obj__isys_obj_type__id, \'.network_port.\', isys_catg_port_list__id, \'.title\'), isys_catg_port_list__title, isys_catg_port_list__isys_obj__id ' .
            'FROM isys_catg_port_list ' . 'INNER JOIN isys_obj ON isys_catg_port_list__isys_obj__id = isys_obj__id WHERE isys_catg_port_list__title != \'\';'
        );

        return $this;
    }

    /**
     * @return $this
     */
    public function indexMacAddress()
    {
        $this->database->query(
            $this->replaceIntoString .
            ' 1, \'cmdb\', CONCAT(isys_obj__isys_obj_type__id, \'.network_port.\', isys_catg_port_list__id, \'.title\'), isys_catg_port_list__mac, isys_catg_port_list__isys_obj__id FROM isys_catg_port_list INNER JOIN isys_obj ON isys_catg_port_list__isys_obj__id = isys_obj__id WHERE isys_catg_port_list__mac != \'\';'
        );

        return $this;
    }

    /**
     *
     * Starts a quick indexing mechanism
     *
     * @using $this->categories as the categories "to-be-indexed"
     * @using $this->objectIds as the objects "to-be-indexed"
     */
    public function index()
    {
        $this->indexObjectTitles();

        if ($this->categories === null)
        {
            $dao           = \isys_cmdb_dao::instance($this->database);
            $allCategories = $dao->get_all_categories(\isys_handler_search_index::defaultCategoryTypes());
        }
        else
        {
            $allCategories = $this->categories;
        }

        $categoryBlacklist = \isys_handler_search_index::initCategoryBlacklist();
        $dummyDocument     = new Document();

        // Global and custom categories are indexed separately, because their properties are quite confusing for the indexer
        $categoryBlacklist[] = 'C__CATG__GLOBAL';
        $categoryBlacklist[] = 'C__CATG__CUSTOM';

        /**
         * Prepare object ids to be indexed
         */
        $objectCondition = $this->getObjectInCondition();

        try
        {
            $this->database->begin();

            $this->documentsToBeIndexed = count($allCategories[C__CMDB__CATEGORY__TYPE_GLOBAL]) + count($allCategories[C__CMDB__CATEGORY__TYPE_SPECIFIC]) + 3;

            foreach ([
                         C__CMDB__CATEGORY__TYPE_GLOBAL,
                         C__CMDB__CATEGORY__TYPE_SPECIFIC
                     ] as $categoryType)
            {
                if (!isset($allCategories[$categoryType]) || !is_array($allCategories[$categoryType]))
                {
                    continue;
                }

                foreach ($allCategories[$categoryType] as $l_cat)
                {
                    if (!in_array($l_cat['const'], $categoryBlacklist) && $l_cat['source_table'] != 'isys_catg_virtual')
                    {
                        $class = $l_cat['class_name'];

                        if (class_exists($class))
                        {
                            /**
                             * @var $dao \isys_cmdb_dao_category
                             */
                            $dao = call_user_func(
                                [
                                    $class,
                                    'instance'
                                ],
                                $this->database
                            );
                            $dao->set_source_table($l_cat['source_table'] . ($categoryType == C__CMDB__CATEGORY__TYPE_GLOBAL ? '_list' : ''));

                            $properties    = [];
                            $allProperties = $dao->get_properties();

                            foreach ($allProperties as $key => $prop)
                            {
                                if ($prop[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__TEXT ||
                                    $prop[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__TEXTAREA ||
                                    $prop[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__COMMENTARY
                                )
                                {
                                    $properties[] = [
                                        'key'       => $key,
                                        'table'     => $prop[C__PROPERTY__DATA][C__PROPERTY__DATA__TABLE_ALIAS] ? $prop[C__PROPERTY__DATA][C__PROPERTY__DATA__TABLE_ALIAS] : $dao->get_source_table(
                                        ),
                                        'column'    => $prop[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD],
                                        'reference' => $prop[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES]
                                    ];
                                }

                                unset($prop);
                            }

                            foreach ($properties as $prop)
                            {
                                try
                                {
                                    // Skip this property, if it is part of a foreign table
                                    if (!strstr($prop['column'], $dao->get_source_table()))
                                    {
                                        continue;
                                    }

                                    // Skip referenced fields
                                    if ($prop['reference'])
                                    {
                                        continue;
                                    }

                                    $col = $prop['table'] ? $prop['table'] . '.' . $prop['column'] : $prop['column'];

                                    $sql = 'REPLACE INTO isys_search_idx ' .
                                        '(isys_search_idx__version, isys_search_idx__type, isys_search_idx__key, isys_search_idx__value, isys_search_idx__reference) ' .
                                        'SELECT 1, \'cmdb\', CONCAT(isys_obj__isys_obj_type__id, \'.' . $dao->get_category() . '.\', ' . $dao->get_source_table() .
                                        '__id, \'.' . $prop['key'] . '\'), ' . $col . ', ' . $dao->get_source_table() . '__isys_obj__id FROM ' . $dao->get_source_table() .
                                        ' ' . 'INNER JOIN isys_obj ON ' . $dao->get_source_table() . '__isys_obj__id = isys_obj__id ' . 'WHERE ' . $col . ' != \'\'' .
                                        $objectCondition . ';';

                                    $this->database->query($sql);

                                    $this->indexedItems += $this->database->affected_rows();

                                    $this->notify($dummyDocument, $this);

                                }
                                catch (\Exception $e)
                                {
                                    \isys_application::instance()->logger->error('OnelineIndexer Error: ' . $e->getMessage());
                                }
                            }

                        }

                    }

                    $this->indexedDocuments++;
                }
            }

            $this->indexIpAddresses();
            $this->indexedItems += $this->database->affected_rows();
            $this->indexedDocuments++;
            $this->notify($dummyDocument, $this);

            $this->indexObjectTitles();
            $this->indexedItems += $this->database->affected_rows();
            $this->indexedDocuments++;
            $this->notify($dummyDocument, $this);

            if (isset($allCategories[C__CMDB__CATEGORY__TYPE_CUSTOM]) && count($allCategories[C__CMDB__CATEGORY__TYPE_CUSTOM]))
            {
                $this->indexCustomCategories();
                $this->indexedItems += $this->database->affected_rows();
                $this->indexedDocuments++;
                $this->notify($dummyDocument, $this);
            }

            $this->database->commit();

        }
        catch (\Exception $e)
        {
            $this->database->rollback();
        }

        return $this;
    }

    /**
     * Create is automatically called from Indexer::create.
     *
     * @return $this
     */
    public function create()
    {
        $this->index();

        return $this;
    }

    /**
     * @param $ref
     */
    public function remove($ref)
    {
        // not implemented, yet
    }

    /**
     * OnelineIndexer constructor.
     *
     * @param Database $database
     */
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

}