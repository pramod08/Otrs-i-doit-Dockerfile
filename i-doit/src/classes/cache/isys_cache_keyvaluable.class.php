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
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @version     1.6
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
interface isys_cache_keyvaluable
{
    /**
     * Removes a cached item.
     *
     * @param   string $p_key
     *
     * @return  isys_cache_keyvalue
     */
    public function delete($p_key);

    /**
     * Determine whether a storage entry has been set for a key.
     *
     * @param $p_key
     *
     * @return bool
     */
    public function exists($p_key);

    /**
     * Flush cache
     *
     * @return boolean
     */
    public function flush();

    /**
     * Retrieve value from cache.
     *
     * @param   string $p_key
     *
     * @return  mixed
     */
    public function get($p_key);

    /**
     * Set a cache value.
     *
     * @param string  $p_key
     * @param mixed   $p_value
     * @param integer $p_ttl "Time To Live" in seconds.
     *
     * @return  isys_cache_keyvalue
     */
    public function set($p_key, $p_value = null, $p_ttl = -1);

    /**
     * Set options for cache handlers.
     *
     * @param array $p_options
     *
     * @return
     */
    public function set_options(array $p_options = []);

    /**
     * Check wheather the cache type is available or not.
     *
     * @return  boolean
     */
    public static function available();
} // class