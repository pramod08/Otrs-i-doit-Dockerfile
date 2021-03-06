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
namespace idoit\Model;

/**
 * i-doit Breadcrumb Model
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class Breadcrumb
{

    /**
     * URL Parameters
     *
     * @var array
     */
    public $parameters = [];
    /**
     * View title
     *
     * @var string title
     */
    public $title;

    /**
     * @param string $title
     * @param string $moduleID
     */
    public function __construct($title, $moduleID, $parameters = [])
    {
        $this->title      = $title;
        $this->parameters = $parameters + [
                C__GET__MODULE_ID => $moduleID
            ];
    }

}