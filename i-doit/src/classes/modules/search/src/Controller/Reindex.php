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
namespace idoit\Module\Search\Controller;

/**
 * i-doit cmdb object controller
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class Reindex implements \isys_controller
{
    /**
     * @var \isys_module_search
     */
    private $module;

    /**
     * @param \isys_application $p_application
     *
     * @return \isys_cmdb_dao_nexgen
     */
    public function dao(\isys_application $p_application)
    {
        return new \isys_cmdb_dao_nexgen($p_application->database);
    }

    /**
     * @action /search/reindex
     *
     * @param \isys_register $p_request
     *
     * @return \idoit\View\Renderable
     */
    public function handle(\isys_register $p_request, \isys_application $p_application)
    {
        /**
         * @todo create queue entry to postpone index creation
         */
        {
            ob_start();
            $handler = new \isys_handler_search_index();
            $handler->reindex([]);
            ob_end_clean();

            \isys_notify::info('Index created.');
        }
    }

    /**
     * @param \isys_register       $p_request
     * @param \isys_application    $p_application
     * @param \isys_component_tree $p_tree
     *
     * @return null
     */
    public function tree(\isys_register $p_request, \isys_application $p_application, \isys_component_tree $p_tree)
    {
        return null;
    }

    /**
     * Index constructor.
     *
     * @param \isys_module_search $p_module
     */
    public function __construct(\isys_module $p_module)
    {
        $this->module = $p_module;
    }

}