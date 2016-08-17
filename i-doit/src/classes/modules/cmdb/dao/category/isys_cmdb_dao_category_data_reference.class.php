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
 * i-doit category data reference
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_data_reference extends isys_cmdb_dao_category_data_value
{
    /**
     * Category reference id
     *
     * @var int
     */
    public $m_id = null;

    /**
     * @param       $p_value
     * @param array $p_data
     */
    public function __construct($p_value, $p_id, $p_data = [])
    {
        $this->m_value = $p_value;
        $this->m_id    = $p_id;

        if (self::$m_store_data)
        {
            if (isset($p_data['id'])) unset($p_data['id']);
            if (isset($p_data['title'])) unset($p_data['title']);

            $this->m_data = $p_data;
        }
    }
}
