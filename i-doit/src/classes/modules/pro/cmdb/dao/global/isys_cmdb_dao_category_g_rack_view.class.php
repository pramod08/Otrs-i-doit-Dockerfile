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
 * DAO: global category for the rack-view.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       1.0
 */
class isys_cmdb_dao_category_g_rack_view extends isys_cmdb_dao_category_g_virtual
{
    /**
     * Category name.
     *
     * @var  string
     */
    protected $m_category = 'rack_view';

    /**
     * Marks the category as "filled" as soon as at least one rack is inside.
     *
     * @return  integer
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_count($p_objid = null)
    {
        $l_res = isys_factory::get_instance('isys_cmdb_dao_category_g_location', $this->get_database_component())
            ->get_data(null, null, 'AND isys_catg_location_list__parentid = ' . $this->convert_sql_id($_GET[C__CMDB__GET__OBJECT]));

        while ($l_row = $l_res->get_row())
        {
            if ($l_row['isys_obj__status'] == C__RECORD_STATUS__NORMAL && $l_row['isys_obj_type__const'] == 'C__OBJTYPE__ENCLOSURE')
            {
                return 1;
            } // if
        } // while

        return 0;
    } // function
} // class