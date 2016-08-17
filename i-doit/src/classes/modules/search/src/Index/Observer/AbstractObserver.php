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
namespace idoit\Module\Search\Index\Observer;

use idoit\Component\ContainerFacade;
use idoit\Component\Protocol\ContainerAware;
use idoit\Component\Provider\DiInjectable;
use idoit\Module\Cmdb\Model\Ci;

/**
 * i-doit
 *
 * Example/Demo Observer
 *
 * Retrieves all indexed documents and adds a debug log entry
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
abstract class AbstractObserver implements ContainerAware
{
    use DiInjectable;

    /**
     * AbstractObserver constructor.
     *
     * @param ContainerFacade $container
     */
    public function __construct(ContainerFacade $container)
    {
        $this->setDi($container);
    }

}