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
 * DAO: specific category list for fiber/lead
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Benjamin Heisig <bheisig@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_catg_fiber_lead extends isys_cmdb_dao_list
{

    /**
     * Gets fields to display in the list view.
     *
     * @return  array
     */
    public function get_fields()
    {
        $l_table      = $this->m_cat_dao->get_table();
        $l_properties = $this->m_cat_dao->get_properties();

        return [
            $l_table . '__id'            => 'ID',
            $l_table . '__label'         => _L('LC__CATG__FIBER_LEAD__LABEL'),
            'isys_fiber_category__title' => $l_properties['category'][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE],
            'isys_cable_colour__title'   => $l_properties['color'][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE]
        ];
    } // function
} // class