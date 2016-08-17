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
namespace idoit\Module\Report\SqlQuery\Structure;

/**
 * Report Join Query
 *
 * @package     idoit\Module\Report\SqlQuery\Structure
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       1.7.1
 */
class JoinSubSelect
{

    /**
     * @var string
     */
    private $joinType = 'INNER';

    /**
     * @var string
     */
    private $selectQuery = '';

    /**
     * @return string
     */
    public function getJoinType()
    {
        return $this->joinType;
    }

    /**
     * @param $joinType
     */
    public function setJoinType($joinType)
    {
        $this->joinType = $joinType;
    }

    /**
     * @return string
     */
    public function getSelectQuery()
    {
        return $this->selectQuery;
    }

    /**
     * @param $selectQuery
     */
    public function setSelectQuery($selectQuery)
    {
        $this->selectQuery = $selectQuery;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->joinType . ' JOIN (' . $this->selectQuery . ')';
    }

    /**
     * @param $joinQuery
     *
     * @return JoinSubSelect
     */
    public static function factory($select, $joinType = 'INNER')
    {
        return new JoinSubSelect($select, $joinType);
    }

    /**
     * JoinQuery constructor.
     *
     * @param $joinQuery
     */
    public function __construct($selectQuery, $joinType = 'INNER')
    {
        $this->selectQuery = $selectQuery;
        $this->joinType    = $joinType;
    }
}