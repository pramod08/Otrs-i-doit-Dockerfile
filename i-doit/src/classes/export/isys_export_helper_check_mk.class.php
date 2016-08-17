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
use idoit\Component\Helper\Ip;

/**
 * i-doit
 *
 * Export helper for Check_MK
 *
 * @package     i-doit
 * @subpackage  Export
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_export_helper_check_mk extends isys_export_helper
{
    /**
     * Get dialog plus information by id.
     *
     * @param   integer $p_id
     * @param   boolean $p_table
     *
     * @return  array
     */
    public function hostaddress($p_id, $p_table = false)
    {
        if ($p_id === 0 || $p_id === '0')
        {
            return [
                'id'         => 0,
                'title'      => _L('LC__CATG__IP__PRIMARY_IP_ADDRESS'),
                'const'      => '',
                'title_lang' => 'LC__CATG__IP__PRIMARY_IP_ADDRESS'
            ];
        } // if

        return $this->dialog_plus($p_id, $p_table);
    } // function

    /**
     * Converts dialog plus properties. Matches given value's language constant with property table.
     * This table's name is given as C__CATEGORY_DATA__PARAM by category DAO's property information array.
     *
     * @param   mixed  $p_data
     * @param   string $p_table
     *
     * @return  integer  Value's valid identifier existing in database
     */
    public function hostaddress_import($p_data, $p_table = null)
    {
        if (is_array($p_data))
        {
            if ($p_data['title_lang'] == 'LC__CATG__IP__PRIMARY_IP_ADDRESS')
            {
                return 0;
            }
            else if (Ip::validate_ip($p_data[C__DATA__VALUE]))
            {
                return $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__IP][$p_data['id']];
            } // if
        } // if

        return $this->dialog_plus_import($p_data, $p_table);
    } // function
} // class