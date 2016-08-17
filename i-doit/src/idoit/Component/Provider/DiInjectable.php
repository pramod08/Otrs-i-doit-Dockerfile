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
namespace idoit\Component\Provider;

use idoit\Component\ContainerFacade;
use isys_application as Application;

/**
 * i-doit Container Aware Trait
 *
 * @package     i-doit
 * @subpackage  Component
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
trait DiInjectable
{
    /**
     * @var ContainerFacade
     */
    protected $container;

    /**
     * @param ContainerFacade $container
     *
     * @return mixed
     */
    public function setDi(ContainerFacade $container)
    {
        $this->container = $container;
    }

    /**
     * @return ContainerFacade
     */
    public function getDi()
    {
        if (!$this->container)
        {
            $this->container = Application::instance()->container;
        }

        return $this->container;
    }

}