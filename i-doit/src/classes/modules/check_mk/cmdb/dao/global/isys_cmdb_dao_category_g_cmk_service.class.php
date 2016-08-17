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
 * DAO: global Check_MK service folder category for software objects.
 *
 * @package     Modules
 * @subpackage  Check_MK
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.4.0
 */
class isys_cmdb_dao_category_g_cmk_service extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'cmk_service';
    /**
     * @var string
     */
    protected $m_entry_identifier = 'service';
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
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function properties()
    {
        return [
            'host'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__MODULE__CHECK_MK__HOST',
                        C__PROPERTY__INFO__DESCRIPTION => 'Host'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_cmk_service_list__host'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CATG__CMK_SERVICE__HOST',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_monitoring',
                                    'callback_property_monitoring_host'
                                ]
                            )
                        ]
                    ]
                ]
            ),
            'service'     => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__CMK_SERVICE__CHECK_MK_SERVICES',
                        C__PROPERTY__INFO__DESCRIPTION => ''
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_cmk_service_list__service'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__CMK_SERVICE__CHECK_MK_SERVICES'
                    ]
                ]
            ),
            'description' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_cmk_service_list__description'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CATG__CMK_SERVICE
                    ]
                ]
            )
        ];
    } // function
} // class