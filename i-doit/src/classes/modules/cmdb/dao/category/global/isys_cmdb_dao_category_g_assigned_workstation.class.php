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
 * DAO: logical unit extension: assigned workstation.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_assigned_workstation extends isys_cmdb_dao_category_g_logical_unit
{
    protected $m_category_const = 'C__CATG__ASSIGNED_WORKSTATION';

    /**     * Method for retrieving the category UI class.
     *
     * @return  isys_cmdb_ui_category_g_assigned_workstation
     */
    public function &get_ui()
    {
        global $g_comp_template;

        return new isys_cmdb_ui_category_g_assigned_workstation($g_comp_template);
    } // function

    /**
     * Method for returning the properties. Unused because reverse category.
     * Why is it unused? We need the properties to use all necessary generic functions,
     * otherwise all important functions must be defined in this class.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function properties()
    {
        $l_properties = isys_cmdb_dao_category_g_logical_unit::properties();

        /* Reset commentary id to the right identificator */
        $l_properties['parent'][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['catFilter'] = 'C__CATG__ASSIGNED_LOGICAL_UNIT';
        $l_properties['description'][C__PROPERTY__UI][C__PROPERTY__UI__ID]             = 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CATG__ASSIGNED_WORKSTATION;

        return $l_properties;
    } // function
} // class
?>