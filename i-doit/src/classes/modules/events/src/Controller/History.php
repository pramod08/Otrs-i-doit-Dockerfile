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
namespace idoit\Module\Events\Controller;

use isys_controller as Controllable;

/**
 * i-doit cmdb controller
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class History extends Main implements Controllable
{

    /**
     * Default request handler, gets called in every /events request
     *
     * @param \isys_register $p_request
     *
     * @return \idoit\View\Renderable
     */
    public function handle(\isys_register $p_request, \isys_application $p_application)
    {

    }

    /**
     * @param \isys_register $p_request
     *
     * @return \idoit\View\Renderable
     */
    public function onDefault(\isys_register $p_request, \isys_application $p_application)
    {
        // Check for view right first
        \isys_auth_events::instance()
            ->history(\isys_auth::VIEW);

        $dao = $this->dao($p_application);

        $view = new \idoit\Module\Events\View\HistoryList($p_request, $dao);
        $view->setDaoResult($dao->getEventHistory());

        // Return the view
        return $view;
    }

}