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
 * i-doit category data value
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_data_value implements JsonSerializable
{

    /**
     * @var bool
     */
    public static $m_store_data = false;
    /**
     * Category value
     *
     * @var string
     */
    public $m_value = null;

    /**
     * String conversion
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->m_value;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'value' => $this->m_value
        ] + $this->m_data;
    }

    /**
     * @return string
     */
    public function toJSON()
    {
        return isys_format_json::encode($this->toArray());
    }

    /**
     * @return mixed|string
     */
    public function jsonSerialize()
    {
        return $this->m_value;
    }

    /**
     * @param       $p_value
     * @param array $p_data
     */
    public function __construct($p_value, $p_data = [])
    {
        $this->m_value = $p_value;

        if (self::$m_store_data)
        {
            if (isset($p_data['title'])) unset($p_data['title']);

            $this->m_data = $p_data;
        }
    }

}