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
namespace idoit\Component\Protocol;

use idoit\Component\ContainerFacade;

/**
 * i-doit Protocols
 *
 * @package     idoit\Component\Protocol
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
interface ContainerAware
{

    /**
     * @return ContainerFacade
     */
    public function getDi();

    /**
     * @param ContainerFacade $container
     *
     * @return mixed
     */
    public function setDi(ContainerFacade $container);

}