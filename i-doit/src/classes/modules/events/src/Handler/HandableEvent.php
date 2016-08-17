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
namespace idoit\Module\Events\Handler;

/**
 * HandableEvent interface
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */

use idoit\Module\Events\Handler\Output\Response;

/**
 * Interface HandableEvent
 *
 * @package idoit\Module\Events\Handler
 */
interface HandableEvent
{

    /**
     * @param array $event
     * @param array $args
     *
     * @return Response
     */
    public function handleLive($event, $args);

    /**
     * @param array $event
     * @param array $args
     *
     * @return Response
     */
    public function handleQueued($event, $args);

}