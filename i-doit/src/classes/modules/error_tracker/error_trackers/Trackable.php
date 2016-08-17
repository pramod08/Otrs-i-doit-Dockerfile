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
 * Error Tracker Module
 *
 * @package     modules
 * @subpackage  error_tracker
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     1.5
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.5
 */
namespace error_trackers;

interface Trackable
{
    /**
     * Report an exception
     *
     * @param \Exception $exc
     *
     * @return Trackable
     */
    public function exception(\Exception $exc);

    /**
     * Initialize with optional config parameters
     *
     * @param $config
     *
     * @return Trackable
     */
    public function initialize($config = []);

    /**
     * Just report a message
     *
     * @param        $message
     * @param string $level
     * @param array  $data
     *
     * @return Trackable
     */
    public function message($message, $level = 'error', $data = []);

    /**
     * Flush messages and force sending them to the tracking instance
     *
     * @return Trackable
     */
    public function send();
}