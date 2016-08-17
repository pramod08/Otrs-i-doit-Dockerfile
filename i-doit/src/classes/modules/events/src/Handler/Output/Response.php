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
namespace idoit\Module\Events\Handler\Output;

/**
 * event handlers
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class Response
{

    /**
     * @var string
     */
    public $output = '';
    /**
     * @var int
     */
    public $returnCode = 0;
    /**
     * @var bool
     */
    public $success = false;

    /**
     * @param           $p_output
     * @param           $p_code
     * @param bool|true $p_success
     */
    public function __construct($p_output, $p_code, $p_success = true)
    {
        $this->returnCode = $p_code;
        $this->output     = $p_output;
        $this->success    = $p_success;
    }

}