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
namespace idoit\Module\Search\Query\Engine\Mysql;

use idoit\Module\Search\Index\Protocol\Engine;
use idoit\Module\Search\Query\Condition;
use idoit\Module\Search\Query\Protocol\QueryResult;
use idoit\Module\Search\Query\StringCondition;
use isys_application as Application;

/**
 * i-doit
 *
 * MySQL Query Engine
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class SearchEngine implements Engine
{

    /**
     * Process search via conditions
     *
     * @param Condition[] $conditions
     *
     * @return QueryResult
     */
    public function search(array $conditions)
    {
        $query = new Query(Application::instance()->database);
        
        return $query->search($conditions);
    }

    /**
     * Process search via string keyword
     *
     * @param string $keyword
     *
     * @return QueryResult
     */
    public function searchString($keyword)
    {
        $query = new Query(Application::instance()->database);

        return $query->search(
            [
                new StringCondition($keyword)
            ]
        );
    }

    /**
     * Is engine active and running?
     *
     * @return bool
     */
    public function isActive()
    {
        return true;
    }

    /**
     * Is engine running and available?
     *
     * @return bool
     */
    public function isAvailable()
    {
        return \isys_application::instance()->database->is_connected();
    }

}