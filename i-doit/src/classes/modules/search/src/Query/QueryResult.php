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
namespace idoit\Module\Search\Query;

use idoit\Module\Search\Query\Protocol\QueryResult as QueryResultProtocol;
use idoit\Module\Search\Query\Protocol\QueryResultItem;

/**
 * i-doit
 *
 * Query result protocol
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class QueryResult implements QueryResultProtocol
{

    /**
     * Found items
     *
     * @var QueryResultItem[]
     */
    private $items = [];

    /**
     * The Search condition this result is bound to
     *
     * @var Condition[]
     */
    private $conditions;

    /**
     * @return Condition[]
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * @return Condition
     */
    public function getCondition($index)
    {
        return $this->conditions[$index] ?: null;
    }

    /**
     * @return QueryResultItem[]
     */
    public function getResult()
    {
        return $this->items;
    }

    /**
     * @param QueryResultItem $doc
     *
     * @return $this
     */
    public function addItem(QueryResultItem $item)
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * QueryResult constructor.
     *
     * @param Condition[] $searchString
     */
    public function __construct(array $conditions)
    {
        $this->conditions = $conditions;
    }

}