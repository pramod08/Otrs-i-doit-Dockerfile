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
 * i-doit core classes
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_register implements Countable
{
    /**
     * Instance array.
     *
     * @var  array
     */
    protected static $m_instances = [];

    /**
     * Data array.
     *
     * @var  array
     */
    protected $m_data = [];

    /**
     * Static factory method.
     *
     * @static
     *
     * @param   $p_name
     *
     * @return  isys_register
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function factory($p_name)
    {
        if (!array_key_exists($p_name, self::$m_instances))
        {
            self::$m_instances[$p_name] = new self;
        } // if

        return self::$m_instances[$p_name];
    } // function

    /**
     * Method for checking, if a certain value is set.
     *
     * @param   string $p_name
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function has($p_name)
    {
        return isset($this->m_data[$p_name]);
    } // function

    /**
     * Setter.
     *
     * @param   string $p_name
     * @param   mixed  $p_value
     *
     * @return  isys_register
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function set($p_name, $p_value = null)
    {
        $this->m_data[$p_name] = $p_value;

        return $this;
    } // function

    /**
     * Merge attributes
     *
     * @param array   $p_data The data to merge into the collection
     * @param boolean $p_replace
     *
     * @access public
     * @return isys_register
     */
    public function merge(array $p_data = [], $p_replace = false)
    {
        // Don't waste our time with an "array_merge" call if the array is empty
        if (!empty($p_data))
        {
            if ($p_replace)
            {
                $this->m_data = array_replace(
                    $this->m_data,
                    $p_data
                );
            }
            else
            {
                $this->m_data = array_merge(
                    $this->m_data,
                    $p_data
                );
            }
        }

        return $this;
    }

    /**
     * Getter.
     *
     * @param   string $p_name
     * @param   mixed  $p_default
     *
     * @return  mixed
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get($p_name = null, $p_default = null)
    {
        if ($p_name !== null)
        {
            if (isset($this->m_data[$p_name]))
            {
                return $this->m_data[$p_name];
            } // if

            return $p_default;
        } // if

        return $this->m_data;
    } // function

    /**
     * Magic isset method.
     *
     * @param   string $p_name
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function __isset($p_name)
    {
        return $this->has($p_name);
    } // function

    /**
     * Magic getter.
     *
     * @param   string $p_name
     *
     * @return  mixed
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function __get($p_name)
    {
        return $this->get($p_name);
    } // function

    /**
     * Magic setter.
     *
     * @param   string $p_name
     * @param   mixed  $p_value
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function __set($p_name, $p_value = null)
    {
        $this->set($p_name, $p_value);
    } // function

    /**
     * Magic toString method.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function __toString()
    {
        return var_export($this->m_data, true);
    } // function

    /**
     * Counts all elements of an register.
     *
     * @return  integer
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function count()
    {
        return count($this->m_data);
    } // function
} // class