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

/**
 * i-doit APi
 *
 * @package    i-doit
 * @subpackage API
 * @author     Dennis Stücken <dstuecken@synetics.de>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_api_model_idoit_search implements isys_api_model_interface
{
    /**
     * @param array $p_params
     *
     * @return array
     * @throws \idoit\Module\Search\Query\Exceptions\NoQueryEngineException
     * @throws isys_exception_api
     */
    public function read($p_params)
    {
        if (!isset($p_params['q']))
        {
            throw new isys_exception_api('Missing parameter: "q".', -32601);
        } // if

        return \idoit\Module\Search\Query\QueryManager::factory()
            ->attachEngine(new \idoit\Module\Search\Query\Engine\Mysql\SearchEngine())
            ->addSearchKeyword($p_params['q'])
            ->search()
            ->getResult();
    } // function

    /**
     * Constructor
     */
    public function __construct()
    {
        ;
    } // function
} // class