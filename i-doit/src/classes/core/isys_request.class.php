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
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_request
{
    /**
     * Category data-ID.
     *
     * @var  integer
     */
    protected $m_category_data_id = null;

    /**
     * Category-type.
     *
     * @var  integer
     */
    protected $m_category_type = null;

    /**
     * Additional data.
     *
     * @var  mixed
     */
    protected $m_data = [];

    /**
     * Object-ID.
     *
     * @var  integer
     */
    protected $m_object_id = null;

    /**
     * Object-type-ID.
     *
     * @var  integer
     */
    protected $m_object_type_id = null;

    /**
     * The row data.
     *
     * @var  array
     */
    protected $m_row = [];

    /**
     * Factory method for instant method chaining.
     *
     * @static
     * @return  isys_request
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function factory()
    {
        return new self();
    } // function

    /**
     * Category-ID getter.
     *
     * @return  integer
     */
    public function get_category_data_id()
    {
        return $this->m_category_data_id;
    } // function

    /**
     * Category-type getter.
     *
     * @return  integer
     */
    public function get_categoy_type()
    {
        return $this->m_category_type;
    } // function

    /**
     * Data getter.
     *
     * @param   string $p_key
     * @param   mixed  $p_default
     *
     * @return  array
     */
    public function get_data($p_key = null, $p_default = false)
    {
        if ($p_key === null)
        {
            return $this->m_data;
        } // if

        if (array_key_exists($p_key, $this->m_data))
        {
            return $this->m_data[$p_key];
        } // if

        return $p_default;
    } // function

    /**
     * Object-ID getter.
     *
     * @return  integer
     */
    public function get_object_id()
    {
        return $this->m_object_id;
    } // function

    /**
     * Object-type-ID getter.
     *
     * @return  integer
     */
    public function get_object_type_id()
    {
        return $this->m_object_type_id;
    } // function

    /**
     * Method for filling the row-data to the request object.
     *
     * @param   string $p_key
     * @param   mixed  $p_default Set a default value, if the key could not be found in the row-data.
     *
     * @return  mixed
     */
    public function get_row($p_key = null, $p_default = false)
    {
        if ($p_key === null)
        {
            return $this->m_row;
        } // if

        if (array_key_exists($p_key, $this->m_row))
        {
            return $this->m_row[$p_key];
        } // if

        return $p_default;
    } // function

    /**
     * Returns the contents of this class as a JSON encoded string.
     *
     * @return  string
     */
    public function serialize()
    {
        return isys_format_json::encode(
            [
                'm_object_id'        => $this->m_object_id,
                'm_object_type_id'   => $this->m_object_type_id,
                'm_category_data_id' => $this->m_category_data_id,
                'm_category_type'    => $this->m_category_type,
                'm_data'             => $this->m_data,
                'm_row'              => $this->m_row
            ]
        );
    } // function

    /**
     * Category Data-ID setter.
     *
     * @param   integer $p_category_data_id
     *
     * @return  isys_request
     */
    public function set_category_data_id($p_category_data_id)
    {
        $this->m_category_data_id = (int) $p_category_data_id;

        return $this;
    } // function

    /**
     * Category-type setter.
     *
     * @param   integer $p_category_type
     *
     * @return  isys_request
     */
    public function set_category_type($p_category_type)
    {
        $this->m_category_type = (int) $p_category_type;

        return $this;
    } // function

    /**
     * Data setter.
     *
     * @param   string $p_key
     * @param   mixed  $p_data
     *
     * @return  isys_request
     */
    public function set_data($p_key, $p_data)
    {
        $this->m_data[$p_key] = $p_data;

        return $this;
    } // function

    /**
     * Object-ID setter.
     *
     * @param   integer $p_object_id
     *
     * @return  isys_request
     */
    public function set_object_id($p_object_id)
    {
        $this->m_object_id = (int) $p_object_id;

        return $this;
    } // function

    /**
     * Object-type-ID setter.
     *
     * @param   integer $p_object_type_id
     *
     * @return  isys_request
     */
    public function set_object_type_id($p_object_type_id)
    {
        $this->m_object_type_id = (int) $p_object_type_id;

        return $this;
    } // function

    /**
     * Method for filling the row-data to the request object.
     *
     * @param   array $p_row
     *
     * @return  isys_request
     */
    public function set_row(array $p_row = [])
    {
        $this->m_row = $p_row;

        return $this;
    } // function

    /**
     * Fills the member-variables with contents from the given JSON string.
     *
     * @param   string $p_json_data
     *
     * @return  isys_request
     */
    public function unserialize($p_json_data)
    {
        $l_data = isys_format_json::decode($p_json_data, true);

        $this->m_object_id        = (int) $l_data['m_object_id'];
        $this->m_object_type_id   = (int) $l_data['m_object_type_id'];
        $this->m_category_data_id = (int) $l_data['m_category_data_id'];
        $this->m_category_type    = (int) $l_data['m_category_type'];
        $this->m_data             = $l_data['m_data'];
        $this->m_row              = $l_data['m_row'];

        return $this;
    } // function

    /**
     * Constructor
     */
    public function __construct($p_data = [])
    {
        $this->m_data = $p_data;

        if (isset($_GET[C__CMDB__GET__OBJECT]) && $_GET[C__CMDB__GET__OBJECT])
        {
            $this->m_object_id = $_GET[C__CMDB__GET__OBJECT];
        }
    }
} // class