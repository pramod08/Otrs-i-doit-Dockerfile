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
 * DAO: global category for fiber/lead
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Benjamin Heisig <bheisig@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_fiber_lead extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'fiber_lead';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'label'       => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__FIBER_LEAD__LABEL',
                        C__PROPERTY__INFO__DESCRIPTION => 'Fiber label'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_fiber_lead_list__label'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__FIBER_LEAD__LABEL'
                    ]
                ]
            ),
            'category'    => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__FIBER_LEAD__CATEGORY',
                        C__PROPERTY__INFO__DESCRIPTION => 'Fiber category'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_fiber_lead_list__isys_fiber_category__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_fiber_category',
                            'isys_fiber_category__id'
                        ]
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CATG__FIBER_LEAD__CATEGORY',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable'   => 'isys_fiber_category',
                            'p_bDbFieldNN' => 1
                        ]
                    ]
                ]
            ),
            'color'       => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__FIBER_LEAD__COLOR',
                        C__PROPERTY__INFO__DESCRIPTION => 'Fiber color'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_fiber_lead_list__isys_cable_colour__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_cable_colour',
                            'isys_cable_colour__id'
                        ]
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CATG__FIBER_LEAD__COLOR',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_cable_colour'
                        ]
                    ]
                ]
            ),
            'description' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Categories description'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_fiber_lead_list__description',
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CATG__FIBER_LEAD
                    ]
                ]
            )
        ];
    } // function
} // class
