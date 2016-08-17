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
use idoit\Module\Cmdb\Model\CiType;
use idoit\Module\Cmdb\Model\DataValue\Decimal;
use idoit\Module\Cmdb\Model\DataValue\Numeric;
use idoit\Module\Cmdb\Model\DataValue\Text;
use isys_cmdb_dao_category_data as DaoCategoryData;

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
class ModelLoader
{
    /**
     * @var DaoCategoryData
     */
    private $daoCategoryData;

    /**
     * @param array $row
     *
     * @return Ci
     */
    private function createCiWithDataRow(array $row)
    {
        if (!isset($row['isys_obj__id']))
        {
            throw new \InvalidArgumentException('Parameter isys_obj__id missing in $row');
        }

        return Ci::factory(
            $row['isys_obj__id'],
            $row['isys_obj__title'],
            CiType::factory(
                $row['isys_obj__isys_obj_type__id'],
                _L($row['isys_obj_type__title']),
                $row['isys_obj_type__const']
            ),
            $row['isys_obj__sysid']
        );
    }

    /**
     * @param  int  $objectID
     * @param array $categoryBlacklist
     *
     * @return Ci
     */
    public function load($objectID, $categoryBlacklist = [])
    {
        $dao = \isys_cmdb_dao::factory(\isys_application::instance()->database);

        $row = $dao->get_object($objectID)
            ->__to_array();

        if ($row)
        {
            return $this->loadWith(
                $row,
                $categoryBlacklist
            );
        }
        else
        {
            throw new \InvalidArgumentException(sprintf('Object id "%s" does not exist', $objectID));
        }
    }

    /**
     * $row has to be
     *
     * @param int   $objectId
     * @param array $categoryBlacklist
     * @param array $providesFlags
     *
     * @return Ci
     *
     * @throws \InvalidArgumentException
     */
    public function loadWith(array $objectRow, $categoryBlacklist = [], $providesFlags = [])
    {
        $object            = $this->createCiWithDataRow($objectRow);
        $object->createdBy = $objectRow['isys_obj__created_by'];
        $object->updatedBy = $objectRow['isys_obj__updated_by'];

        // Initialize category data with object id
        $catdata = $this->daoCategoryData->initialize(
            $object->id,
            $providesFlags
        );

        // Iterate through all categories
        foreach ($catdata as $cat)
        {
            $dao = $cat->get_dao();

            // Check wheather category should be loaded or not
            if (count($categoryBlacklist) === 0 || !isset($categoryBlacklist[$dao->get_category_const()]))
            {
                // Initialize Category instance
                $category = Category::factory(
                    $object->id,
                    ucwords(
                        str_replace(
                            '_',
                            ' ',
                            $dao->get_category()
                        )
                    ),
                    $dao->get_category_type(),
                    $dao->get_category_const(),
                    $dao->get_category(),
                    $dao->get_category_id()
                );

                // Iterate through evaluated category data
                foreach ($cat->data() as $categoryId => $categoryData)
                {
                    $values = [];

                    /**
                     * @var $value \isys_cmdb_dao_category_data_value|\isys_cmdb_dao_category_data_reference
                     */
                    foreach ((array) $categoryData as $key => $value)
                    {
                        if ($value->m_value)
                        {
                            if (is_a($value, 'isys_cmdb_dao_category_data_reference') && $value->m_id > 0)
                            {
                                /**
                                 * @todo isys_cmdb_dao_category_data_reference is not always an object reference...
                                 *
                                 * try
                                 * {
                                 * $objectData = $cmdbDao->get_object($value->m_id)
                                 * ->__to_array();
                                 *
                                 * if ($objectData)
                                 * {
                                 * $values[$key] = new \idoit\Module\Cmdb\Model\DataValue\Ci(
                                 * $this->createCiWithDataRow(
                                 * $objectData
                                 * )
                                 * );
                                 * }
                                 * }
                                 * catch (InvalidArgumentException $e)
                                 * {
                                 * ;
                                 * }
                                 */
                                $values[$key] = new Text($value->m_value);
                            }
                            else if (is_double($value->m_value))
                            {
                                $values[$key] = new Decimal($value->m_value);
                            }
                            else if (is_int($value->m_value) || /** @see ID-3157 : */ (strpos($value->m_value, '0') !== 0 && is_numeric($value->m_value)))
                            {
                                $values[$key] = new Numeric((int) $value->m_value);
                            }
                            else
                            {
                                $values[$key] = new Text(trim($value));
                            }
                        }
                    }

                    if (count($values) > 0)
                    {
                        // Add prepared data to category (property values)
                        $category->addData(
                            Category\Data::factory($values),
                            $categoryId
                        );
                    }
                }

                // Add category to object
                $object->addData($category);

                unset($category, $objectData, $values, $value);
            }
        }

        // Free reserverd memory
        $this->daoCategoryData->free($object->id);

        return $object;
    }

    /**
     * ModelLoader constructor.
     *
     * @param DaoCategoryData $categoryData
     */
    public function __construct(DaoCategoryData $categoryData)
    {
        $this->daoCategoryData = $categoryData;
    }

}