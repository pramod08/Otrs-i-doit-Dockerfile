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
abstract class isys_cache
{
    /**
     * Cache options.
     *
     * @var  array
     */
    protected $m_options = [];

    /**
     * Get any available keyvalue cache with the following default priority:
     *  1) memcache 2) apc 3) xcache 4) file system 5) no cache
     *
     * Cache priority can be overridden by parameter $p_cache_priority in the following format:
     *   array('xcache', 'memcache', 'apc')
     * This will not use filesystem caching by changing the priority order to xcache > memcache > apc.
     *
     * @param   $p_cache_priority  array
     *
     * @return  isys_cache_keyvalue
     */
    public static function keyvalue($p_cache_priority = null)
    {
        try
        {
            $l_cache_register = $p_cache_priority ?: [
                'memcache',
                'apc',
                'xcache',
                'fs'
            ];

            foreach ($l_cache_register as $l_cache_type)
            {
                $l_cacheclass = 'isys_cache_' . $l_cache_type;

                // Return first available cache.
                if (class_exists($l_cacheclass) && call_user_func(
                        [
                            $l_cacheclass,
                            'available'
                        ]
                    )
                )
                {
                    return new $l_cacheclass;
                } // if
            } // foreach
        }
        catch (isys_exception_cache $e)
        {
            ;
        } // try

        return new isys_cache_keyvalue_dummy();
    } // function

    /**
     * Set options for cache handlers.
     *
     * @param array $p_options
     */
    public function set_options(array $p_options = [])
    {
        $this->m_options = $p_options;

        return $this;
    }

    /**
     * Get default expiration time in seconds
     *
     * @param int $p_ttl
     *
     * @return int
     */
    public function default_expiration($p_ttl = -1)
    {
        // Return default if $p_ttl lower then 0
        if ($p_ttl < 0)
        {
            // Default = 1 day
            return isys_tenantsettings::get('cache.default-expiration-time', 86400);
        }
        else
        {
            return $p_ttl;
        }
    } // function
} // class