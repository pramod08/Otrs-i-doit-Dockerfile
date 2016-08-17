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
 * Event base class.
 *
 * @package     i-doit
 * @subpackage  Events
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
interface isys_event
{
    public function handle_event();

    public function init($p_strDesc, $p_nObjID, $p_nObjTypeID, $p_name = null);
} // interface