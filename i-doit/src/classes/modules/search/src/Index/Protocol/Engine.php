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
namespace idoit\Module\Search\Index\Protocol;

use idoit\Module\Search\Query\Condition;
use idoit\Module\Search\Query\Protocol\QueryResult;

/**
 * i-doit
 *
 * Query Engine Protocol
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
interface Engine
{

    /**
     * Process search via conditions
     *
     * @param Condition[] $conditions
     *
     * @return QueryResult
     */
    public function search(array $conditions);

    /**
     * Process search via string keyword
     *
     * @param string $keyword
     *
     * @return QueryResult
     */
    public function searchString($keyword);

    /**
     * Is engine activated
     *
     * @return bool
     */
    public function isActive();

    /**
     * Is engine running and available?
     *
     * @return bool
     */
    public function isAvailable();

}