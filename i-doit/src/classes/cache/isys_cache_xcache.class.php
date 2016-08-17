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
class isys_cache_xcache extends isys_cache_keyvalue implements isys_cache_keyvaluable
{
    /**
     * Check wheather xcache is available or not.
     *
     * @return  boolean
     */
    public static function available()
    {
        return function_exists('xcache_set') && intval(ini_get('xcache.cacher')) == 1 && intval(ini_get('xcache.var_size')) > 0 && !(bool) (ini_get(
            'xcache.admin.enable_auth'
        ));
    } // function

    /**
     * Deletes a cache item from xcache.
     *
     * @param   string $p_key
     *
     * @return  isys_cache_xcache
     */
    public function delete($p_key)
    {
        $this->prepend_ns($p_key);

        xcache_unset($p_key);

        return $this;
    } // function

    /**
     * Determine whether a storage entry has been set for a key.
     *
     * @param   string $key The storage entry identifier.
     *
     * @return  boolean
     */
    public function exists($key)
    {
        $this->prepend_ns($p_key);

        return xcache_isset($key) === false;
    }

    /**
     * Flush cache
     *
     * @return boolean
     */
    public function flush()
    {
        if (defined('XC_TYPE_VAR'))
        {
            xcache_clear_cache(XC_TYPE_VAR);

            return true;
        }

        return false;
    }

    /**
     * Get value of $p_key from xcache.
     *
     * @param   string $p_key
     *
     * @return  mixed
     */
    public function get($p_key)
    {
        $this->prepend_ns($p_key);

        return xcache_get($p_key);
    } // function

    /**
     * Set $p_key to $p_value in xcache persistent cache.
     *
     * @param   string  $p_key
     * @param   mixed   $p_value
     * @param   integer $p_ttl
     *
     * @return  isys_cache_xcache
     */
    public function set($p_key, $p_value = null, $p_ttl = -1)
    {
        $this->prepend_ns($p_key);

        xcache_set($p_key, $p_value, $this->default_expiration($p_ttl));

        return $this;
    } // function
} // class