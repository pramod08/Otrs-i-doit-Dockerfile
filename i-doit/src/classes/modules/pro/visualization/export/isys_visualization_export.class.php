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
 * i-doit
 *
 * Visualization export interface.
 *
 * @package     modules
 * @subpackage  pro
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.5.0
 */
interface isys_visualization_export
{
    /**
     * Export method.
     *
     * @return  mixed
     */
    public function export();

    /**
     * Initialization method.
     *
     * @param   array $p_options
     *
     * @return  isys_visualization_export
     */
    public function init(array $p_options = []);

    /**
     * Static factory method for.
     *
     * @return  object
     */
    public static function factory();
} // class