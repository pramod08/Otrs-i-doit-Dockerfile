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
namespace idoit\Module\Search\Query\Engine;

use idoit\Module\Search\Query\Condition;
use idoit\Module\Search\Query\QueryResultItem;

/**
 * i-doit
 *
 * Abstract search query
 *
 * @package     idoit\Module\Search\Index
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
abstract class AbstractQuery
{

    /**
     * Get QueryResultItem instance
     *
     * @param string      $type
     * @param int         $id
     * @param string      $key
     * @param string      $value
     * @param double      $score
     * @param Condition[] $conditions
     *
     * @return QueryResultItem
     */
    protected function getQueryItemInstance($type, $id, $key, $value, $score, array $conditions)
    {
        $className = 'idoit\\Module\\' . ucfirst($type) . '\\Search\\QueryExtension\\QueryResultItem';

        if (class_exists($className))
        {
            return new $className($id, $key, $value, $score, $conditions);
        }

        return new QueryResultItem($id, $key, $value, $score, $conditions);
    }

}