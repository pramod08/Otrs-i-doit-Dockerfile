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
namespace idoit\Component;

use dstuecken\Notify\Handler\AbstractHandler;
use dstuecken\Notify\NotificationCenter;
use isys_component_database as Database;
use isys_component_signalcollection as SignalCollection;
use isys_component_template as Template;
use Pimple\Container;

/**
 * i-doit Container Facade
 *
 * Gives access to some of the most used container services by identifying them as a @property.
 *
 * @package     idoit\Component
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 *
 * @property Logger             logger
 * @property Database           database_system
 * @property Database           database
 * @property AbstractHandler[]  notifyHandler
 * @property NotificationCenter notify
 * @property SignalCollection   signals
 * @property Template           template
 *
 */
class ContainerFacade extends Container
{
    /**
     * @param $id
     * @param $value
     */
    public function __set($id, $value)
    {
        $this[$id] = $value;
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function __get($id)
    {
        return $this[$id];
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function __isset($id)
    {
        return isset($this[$id]);
    }

    /**
     * @param $id
     */
    public function __unset($id)
    {
        unset($this[$id]);
    }
}