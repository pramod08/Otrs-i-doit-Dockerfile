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
 * A simple caching implementation.
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @since       0.9.9-7
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */

/**
 * This class will cache data as associative arrays in a file.
 *
 *     // Creating and setting new cache-data.
 *     $l_cache = isys_caching::factory('data-xyz')
 *         ->set_expiration(3600)
 *         ->set('data', 123)
 *         ->set('data2', array(1, 2, 3))
 *         ->set('data3', new Object())
 *         ->save();
 *
 *     // Getting cache-data.
 *     $l_cache = isys_caching::factory('data-xyz');
 *     $l_data = $l_cache->get('data');
 *     $l_data2 = $l_cache->get('data2');
 *     $l_data3 = $l_cache->get('data3');
 *
 * @author  Leonard Fischer <lfischer@i-doit.org>
 */
class isys_caching
{
    // Constant for cache-filename prefix.
    const C__CACHE__PREFIX = 'cache__';

    // Constant for cache-file extension.
    const C__CACHE__EXTENSION = 'php';
    /**
     * Here we will save all our data.
     *
     * @var  array
     */
    protected static $m_data = [];
    /**
     * A record of the methods, called during this request.
     *
     * @var  array
     */
    protected static $m_debug = [];
    /**
     * Array with the instance-names.
     *
     * @var  array
     */
    protected static $m_instance = [];
    /**
     * Has the cache tried to be loaded.
     *
     * @var  boolean
     */
    protected static $m_loaded = [];
    /**
     * Has the cache been updated/modified?
     *
     * @var  boolean
     */
    protected static $m_updated = [];
    /**
     * The cache-directory, so we don't have to use globals all the time.
     *
     * @var  string
     */
    protected $m_cachedir = null;
    /**
     * The complete dir + filename of the cache-file.
     *
     * @var  string
     */
    protected $m_cachefile = null;
    /**
     * The name for this cache-instance.
     *
     * @var  string
     */
    protected $m_cachename = null;
    /**
     * The default expiration is one week (as defined by isys_convert).
     *
     * @var  integer
     */
    protected $m_expiration = 604800;

    /**
     * Factory method for instant method-chaining. Requires a name for the cache.
     * If a cache with the same name already exists, it will be loaded.
     *
     * @param   string  $p_name       The name of this cache-instance.
     * @param   integer $p_expiration The expiration time of the cache.
     *
     * @return  isys_caching
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function factory($p_name = null, $p_expiration = null)
    {
        if (isset(isys_caching::$m_instance[$p_name]))
        {
            isys_caching::$m_debug[] = 'factory("' . $p_name . '", ' . $p_expiration . '); // Returning instance';

            return isys_caching::$m_instance[$p_name];
        }
        else
        {
            isys_caching::$m_debug[] = 'factory("' . $p_name . '", ' . $p_expiration . '); // Return new';

            return isys_caching::$m_instance[$p_name] = new isys_caching($p_name, $p_expiration);
        } // if
    } // function

    /**
     * This static "find" method will return an array of isys_caching instances, whose names match the "$p_name" parameter.
     *
     * @param   string $p_name It is possible to use "*" als wildchar (using "glob()" function).
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function find($p_name)
    {
        $l_found = [];

        global $g_comp_session;

        $l_cachedir = isys_glob_get_temp_dir();

        // If the session-object exists, we try to receive the mandator-cache directory.
        if (is_object($g_comp_session))
        {
            $l_mandator_data = $g_comp_session->get_mandator_data();

            $l_cachedir .= $l_mandator_data['isys_mandator__dir_cache'] . DS;
        } // if

        $l_matches = glob($l_cachedir . isys_caching::C__CACHE__PREFIX . $p_name);

        if (count($l_matches) > 0)
        {
            foreach ($l_matches as $l_match)
            {
                $l_cache_name = strstr(str_replace($l_cachedir . isys_caching::C__CACHE__PREFIX, '', $l_match), '.' . self::C__CACHE__EXTENSION, true);

                $l_found[$l_cache_name] = self::factory($l_cache_name);
            }
        }

        return $l_found;
    } // function

    /**
     * Method for retrieving the debug messages.
     *
     * @static
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function get_debug()
    {
        return self::$m_debug;
    } // function

    /**
     * Destructor method for saving cache, when the http request ends.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function __destruct()
    {
        try
        {
            $this->save();
        }
        catch (Exception $e)
        {
            /**
             * @todo log message to system log
             */
        }

    } // function

    /**
     * Magic getter.
     *
     * @param   string $p_key The key of the data, which should be returned.
     *
     * @return  mixed
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @uses    isys_caching::get()
     */
    public function __get($p_key)
    {
        isys_caching::$m_debug[] = '__get("' . $p_key . '");';

        return $this->get($p_key);
    } // function

    /**
     * Magic setter.
     *
     * @param   string $p_key   The key for the cached data.
     * @param   mixed  $p_value The cached data itself. Can contain a String, Boolean, Array or Object.
     *
     * @return  isys_caching
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @uses    isys_caching::add()
     */
    public function __set($p_key, $p_value)
    {
        isys_caching::$m_debug[] = '__set("' . $p_key . '", [ ' . gettype($p_value) . ' ]);';

        return $this->set($p_key, $p_value);
    } // function

    /**
     * Magic isset method.
     *
     * @param   string $p_key The key of the data, which should be checked.
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function __isset($p_key)
    {
        isys_caching::$m_debug[] = '__isset("' . $p_key . '");';

        return $this->has($p_key);
    } // function

    /**
     * Sets a new values to the cache. This can be a string, integer, array or object.
     *
     * @deprecated  Simply use "set" instead.
     *
     * @param   string $p_key   The key for the cached data.
     * @param   mixed  $p_value The cached data itself. Can contain a String, Boolean, Array or Object.
     *
     * @return  isys_caching
     * @author      Leonard Fischer <lfischer@i-doit.org>
     */
    public function add($p_key, $p_value)
    {
        return $this->set($p_key, $p_value);
    } // function

    /**
     * Delete method for deleting the current cache-content and file.
     *
     * @return  isys_caching
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function clear()
    {
        try
        {
            isys_caching::$m_debug[] = 'clear();';
            unset(isys_caching::$m_data[$this->m_cachename]);
            if (file_exists($this->m_cachefile))
            {
                if (is_writeable($this->m_cachefile))
                {
                    isys_caching::$m_debug[] = 'unlink(' . $this->m_cachefile . ');';
                    unlink($this->m_cachefile);
                }
                else
                {
                    isys_caching::$m_debug[] = 'unlink(' . $this->m_cachefile . ') File is not writeable.';
                }
            } // if
        }
        catch (ErrorException $e)
        {
            /**
             * @todo Log to system log
             */
        }

        return $this;
    } // function

    /**
     * This delete method deletes all cache-files.
     *
     *     // Delete every cache-file and data.
     *     isys_caching::factory()->delete_all();
     *
     * @return  isys_caching
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @uses    isys_caching::delete_all_except()
     */
    public function delete_all()
    {
        isys_caching::$m_debug[] = 'delete_all();';

        return isys_caching::delete_all_except([]);
    } // function

    /**
     * This delete method deletes all cache-files, except the ones given as parameter (array).
     *
     *     // Deleting every cache file and data except autoload-cache.
     *     isys_caching::factory()->delete_all_except(array('autoload'));
     *
     * @param   array $p_except Give an array of cache-files you don't want to delete.
     *
     * @return  isys_caching
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function delete_all_except(array $p_except)
    {
        isys_caching::$m_debug[] = 'delete_all_except([ Array count: ' . count($p_except) . ' ]);';
        if ($l_handle = opendir($this->m_cachedir))
        {
            while (false !== ($l_file = readdir($l_handle)))
            {
                $l_prefix_lenght = strlen(isys_caching::C__CACHE__PREFIX);
                $l_cachename     = substr($l_file, $l_prefix_lenght, -4);

                // Only delete cache files!
                if (substr($l_file, 0, $l_prefix_lenght) === isys_caching::C__CACHE__PREFIX && !in_array($l_cachename, $p_except) && !is_dir($this->m_cachedir . $l_file))
                {
                    if (is_writeable($this->m_cachedir . $l_file))
                    {
                        unlink($this->m_cachedir . $l_file);
                    }
                    unset(isys_caching::$m_data[$l_cachename]);
                } // if
            } // while

            closedir($l_handle);
        } // if

        return $this;
    } // function

    /**
     * Get one or all values from the cache.
     *
     * @param   string $p_key     The key, of the cache-data to get.
     * @param   mixed  $p_default This will be returned as default.
     *
     * @return  mixed
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get($p_key = null, $p_default = false)
    {
        isys_caching::$m_debug[] = 'get("' . $p_key . '")';
        if (!isset(isys_caching::$m_data[$this->m_cachename][$p_key]) && true !== isys_caching::$m_loaded[$this->m_cachename])
        {
            $this->load();
        } // if

        if (null === $p_key)
        {
            return isys_caching::$m_data[$this->m_cachename];
        }
        else if (is_string($p_key) && isset(isys_caching::$m_data[$this->m_cachename][$p_key]))
        {
            return isys_caching::$m_data[$this->m_cachename][$p_key];
        } // if

        return $p_default;
    } // function

    /**
     * Magic isset method.
     *
     * @param   string $p_key The key of the data, which should be checked.
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function has($p_key)
    {
        isys_caching::$m_debug[] = 'has("' . $p_key . '");';

        return isset(isys_caching::$m_data[$this->m_cachename][$p_key]);
    } // function

    /**
     * This method will load the cache file, if it exists.
     *
     * @return  isys_caching
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @uses    isys_caching::clear()
     */
    public function load()
    {
        isys_caching::$m_debug[] = 'load()';

        // If a cache-file already exists, load it.
        if (file_exists($this->m_cachefile) && (filemtime($this->m_cachefile) > (time() - $this->m_expiration)))
        {
            isys_caching::$m_data[$this->m_cachename] = unserialize(file_get_contents($this->m_cachefile));

            isys_caching::$m_loaded[$this->m_cachename]  = true;
            isys_caching::$m_updated[$this->m_cachename] = false;
        }
        else
        {
            // Cache is expired or corrupted.
            $this->clear();
        } // if

        return $this;
    } // function

    /**
     * This method saves the cache to a file on the filesystem.
     *
     * @param   boolean $p_force Shall the cache file be forced to be written?
     *
     * @return  isys_caching
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @throws  Exception
     */
    public function save($p_force = false)
    {
        isys_caching::$m_debug[] = 'save(' . var_export($p_force, true) . ')';

        // Was the cache updated or is this action beeing forced?
        if (isys_caching::$m_updated[$this->m_cachename] || $p_force)
        {
            // Check if the cache-file has been set.
            if (null === $this->m_cachefile)
            {
                $this->set_paths();
            } // if

            // Check, if the cache-directory exists and create it, if necessary.
            if (!is_dir($this->m_cachedir))
            {
                if (is_writeable(dirname($this->m_cachedir)))
                {
                    // Create the cache directory.
                    mkdir($this->m_cachedir, 0777, true);

                    // Set permissions (must be manually set to fix umask issues).
                    chmod($this->m_cachedir, 0777);
                }
                else
                {
                    throw new isys_exception_filesystem('Could not create "' . $this->m_cachedir . '", ' . dirname($this->m_cachedir) . ' is not writeable!');
                }
            } // if

            // Check if the directory is available.
            if (!is_dir($this->m_cachedir) || !is_writable($this->m_cachedir))
            {
                throw new isys_exception_filesystem('The cache-directory, located at "' . $this->m_cachedir . '", must be writeable!');
            } // if

            // Open the cache-file and chek, if this action was succesfull.
            if (!file_exists($this->m_cachefile) || $p_force)
            {
                if (is_writable(dirname($this->m_cachefile)))
                {

                    if (!$l_cachefile = fopen($this->m_cachefile, 'w'))
                    {
                        throw new isys_exception_filesystem('The cache-file, located at "' . $this->m_cachefile . '", can not be opened!');
                    } // if

                    try
                    {
                        // Start writing the content.
                        if (!fwrite($l_cachefile, serialize(isys_caching::$m_data[$this->m_cachename])))
                        {
                            return false;
                        }

                        // Close the file.
                        fclose($l_cachefile);

                        // Setting cache file to 777, so that it is globally writable in case it was written by root with the controller.
                        if (file_exists($this->m_cachefile) && is_writable($this->m_cachefile))
                        {
                            chmod($this->m_cachefile, 0777);
                        }
                    }
                    catch (ErrorException $e)
                    {
                        ;
                    }
                    catch (Exception $e)
                    {
                        ;
                    }
                }
                else
                {
                    throw new isys_exception_filesystem('The cache-file, located at "' . $this->m_cachefile . '" is not writeable!');
                }
            }

        } // if

        return $this;

    } // function

    /**
     * Sets a new values to the cache. This can be a string, integer, array or object.
     *
     * @param   string $p_key   The key for the cached data.
     * @param   mixed  $p_value The cached data itself. Can contain a String, Boolean, Array or Object.
     *
     * @return  isys_caching
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function set($p_key, $p_value)
    {
        isys_caching::$m_debug[]                          = 'set("' . $p_key . '", [ ' . gettype($p_value) . ' ]);';
        isys_caching::$m_updated[$this->m_cachename]      = true;
        isys_caching::$m_data[$this->m_cachename][$p_key] = $p_value;

        return $this;
    } // function

    /**
     * This method defines the expiration date - Must be called, before loading cached values.
     *
     * @param   integer $p_expiration The expiration time in seconds.
     *
     * @return  isys_caching
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function set_expiration($p_expiration)
    {
        isys_caching::$m_debug[] = 'set_expiration(' . $p_expiration . ')';
        $this->m_expiration      = (int) $p_expiration;

        return $this;
    } // function

    /**
     * This method sets the paths for the cache.
     *
     * @param   string $p_name Define a name for the cache-files.
     *
     * @return  isys_caching
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function set_paths($p_name = null)
    {
        isys_caching::$m_debug[] = 'set_paths("' . $p_name . '")';

        if (null === $p_name)
        {
            $p_name = 'default';
        } // if

        // Only set cache-name, if it was not set before.
        if (null === $this->m_cachename)
        {
            $this->m_cachename = isys_caching::C__CACHE__PREFIX . isys_glob_strip_accent(isys_glob_replace_accent(strtolower($p_name)));
        } // if

        global $g_comp_session;

        $this->m_cachedir = isys_glob_get_temp_dir();

        // If the session-object exists, we try to receive the mandator-cache directory.
        if (is_object($g_comp_session))
        {
            $l_mandator_data = $g_comp_session->get_mandator_data();

            $this->m_cachedir .= $l_mandator_data['isys_mandator__dir_cache'] . DS;
        } // if

        $this->m_cachefile = $this->m_cachedir . $this->m_cachename . '.' . self::C__CACHE__EXTENSION;

        return $this;
    } // function

    /**
     * Method to prevent cloning.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function __clone()
    {
        ;
    } // function

    /**
     * Constructor. Requires a name for the cache. If a cache with the same name already exists,
     * it will be loaded. Preferred way is to use the static factory method!
     *
     * @param   string  $p_name       The name of this cache-instance.
     * @param   integer $p_expiration The expiration time of the cache.
     *
     * @return  isys_caching
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @uses    isys_caching::set_expiration()
     */
    protected function __construct($p_name = null, $p_expiration = null)
    {
        isys_caching::$m_debug[] = '__construct("' . $p_name . '", ' . $p_expiration . ');';

        if (null !== $p_expiration)
        {
            $this->set_expiration($p_expiration);
        } // if

        $this->set_paths($p_name);

        isys_caching::$m_updated[$this->m_cachename] = false;
        isys_caching::$m_loaded[$this->m_cachename]  = false;
    } // function
} // class