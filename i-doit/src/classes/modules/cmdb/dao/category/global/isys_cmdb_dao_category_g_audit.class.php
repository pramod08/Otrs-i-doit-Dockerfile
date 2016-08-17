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
 * DAO: global category for audits
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Benjamin Heisig <bheisig@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_audit extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'audit';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Callback method for the multiselection object-browser.
     *
     * @global  isys_component_database $g_comp_database
     *
     * @param   isys_request            $p_request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_commission(isys_request $p_request)
    {
        global $g_comp_database;
        $l_return = [];

        $l_audit_dao = isys_cmdb_dao_category_g_audit::instance($g_comp_database)
            ->get_data(null, $p_request->get_object_id())
            ->get_row();

        $l_person_res = isys_cmdb_dao_category_g_contact::instance($g_comp_database)
            ->get_assigned_contacts_by_relation_id($l_audit_dao["isys_catg_audit_list__commission"]);

        while ($l_row = $l_person_res->get_row())
        {
            $l_return[] = $l_row['isys_obj__id'];
        } // while

        return $l_return;
    } // function

    /**
     * Callback method for the multiselection object-browser.
     *
     * @global  isys_component_database $g_comp_database
     *
     * @param   isys_request            $p_request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_responsible(isys_request $p_request)
    {
        global $g_comp_database;
        $l_return = [];

        $l_audit_dao = isys_cmdb_dao_category_g_audit::instance($g_comp_database)
            ->get_data(null, $p_request->get_object_id())
            ->get_row();

        $l_person_res = isys_cmdb_dao_category_g_contact::instance($g_comp_database)
            ->get_assigned_contacts_by_relation_id($l_audit_dao["isys_catg_audit_list__responsible"]);

        while ($l_row = $l_person_res->get_row())
        {
            $l_return[] = $l_row['isys_obj__id'];
        } // while

        return $l_return;
    } // function

    /**
     * Callback method for the multiselection object-browser.
     *
     * @global  isys_component_database $g_comp_database
     *
     * @param   isys_request            $p_request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_involved(isys_request $p_request)
    {
        global $g_comp_database;
        $l_return = [];

        $l_audit_dao = isys_cmdb_dao_category_g_audit::instance($g_comp_database)
            ->get_data(null, $p_request->get_object_id())
            ->get_row();

        $l_person_res = isys_cmdb_dao_category_g_contact::instance($g_comp_database)
            ->get_assigned_contacts_by_relation_id($l_audit_dao["isys_catg_audit_list__involved"]);

        while ($l_row = $l_person_res->get_row())
        {
            $l_return[] = $l_row['isys_obj__id'];
        } // while

        return $l_return;
    } // function

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function properties()
    {
        return [
            'title'               => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__AUDIT__TITLE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Title'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_audit_list__title'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CATG__AUDIT__TITLE'
                    ]
                ]
            ),
            'type'                => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__AUDIT__TYPE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Type'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_audit_list__type',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_audit_type',
                            'isys_catg_audit_type__id'
                        ]
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATG__AUDIT__TYPE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_catg_audit_type'
                        ]
                    ]
                ]
            ),
            'commission'          => array_replace_recursive(
                isys_cmdb_dao_category_pattern::object_browser(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__AUDIT__COMMISSION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Commission'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_audit_list__commission',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_contact',
                            'isys_contact__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATG__AUDIT__COMMISSION',
                        C__PROPERTY__UI__PARAMS => [
                            'catFilter'              => 'C__CATS__PERSON;C__CATS__PERSON_GROUP;C__CATS__ORGANIZATION',
                            'multiselection'         => true,
                            'p_bReadonly'            => 1,
                            'p_image'                => true,
                            'p_strFormSubmit'        => 0,
                            'p_iSelectedTab'         => 1,
                            'p_iEnabledPreselection' => 1,
                            'p_strValue'             => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_audit',
                                    'callback_property_commission'
                                ]
                            )
                        ]
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'contact'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => true,
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ]
                ]
            ),
            'responsible'         => array_replace_recursive(
                isys_cmdb_dao_category_pattern::object_browser(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__AUDIT__RESPONSIBLE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Responsible'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_audit_list__responsible',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_contact',
                            'isys_contact__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATG__AUDIT__RESPONSIBLE',
                        C__PROPERTY__UI__PARAMS => [
                            'catFilter'              => 'C__CATS__PERSON;C__CATS__PERSON_GROUP;C__CATS__ORGANIZATION',
                            'multiselection'         => true,
                            'p_bReadonly'            => 1,
                            'p_image'                => true,
                            'p_strFormSubmit'        => 0,
                            'p_iSelectedTab'         => 1,
                            'p_iEnabledPreselection' => 1,
                            'p_strValue'             => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_audit',
                                    'callback_property_responsible'
                                ]
                            )
                        ]
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'contact'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => true,
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ]
                ]
            ),
            'involved'            => array_replace_recursive(
                isys_cmdb_dao_category_pattern::object_browser(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__AUDIT__INVOLVED',
                        C__PROPERTY__INFO__DESCRIPTION => 'Involved contacts'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_audit_list__involved',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_contact',
                            'isys_contact__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATG__AUDIT__INVOLVED',
                        C__PROPERTY__UI__PARAMS => [
                            'catFilter'              => 'C__CATS__PERSON;C__CATS__PERSON_GROUP;C__CATS__ORGANIZATION',
                            'multiselection'         => true,
                            'p_bReadonly'            => 1,
                            'p_image'                => true,
                            'p_strFormSubmit'        => 0,
                            'p_iSelectedTab'         => 1,
                            'p_iEnabledPreselection' => 1,
                            'p_strValue'             => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_audit',
                                    'callback_property_involved'
                                ]
                            )
                        ]
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'contact'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => true,
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ]
                ]
            ),
            'period_manufacturer' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::date(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__AUDIT__PERIOD_MANUFACTURER',
                        C__PROPERTY__INFO__DESCRIPTION => 'Period manufacturer'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_audit_list__period_manufacturer'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CATG__AUDIT__PERIOD_MANUFACTURER'
                    ]
                ]
            ),
            'period_operator'     => array_replace_recursive(
                isys_cmdb_dao_category_pattern::date(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__AUDIT__PERIOD_OPERATOR',
                        C__PROPERTY__INFO__DESCRIPTION => 'Period manufacturer'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_audit_list__period_operator'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CATG__AUDIT__PERIOD_OPERATOR'
                    ]
                ]
            ),
            'apply'               => array_replace_recursive(
                isys_cmdb_dao_category_pattern::date(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__AUDIT__APPLY',
                        C__PROPERTY__INFO__DESCRIPTION => 'Applied'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_audit_list__apply'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CATG__AUDIT__APPLY'
                    ]
                ]
            ),
            'result'              => array_replace_recursive(
                isys_cmdb_dao_category_pattern::textarea(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__AUDIT__RESULT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Result'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_audit_list__result'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CATG__AUDIT__RESULT'
                    ]
                ]
            ),
            'fault'               => array_replace_recursive(
                isys_cmdb_dao_category_pattern::textarea(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__AUDIT__FAULT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Faults'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_audit_list__fault'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CATG__AUDIT__FAULT'
                    ]
                ]
            ),
            'incident'            => array_replace_recursive(
                isys_cmdb_dao_category_pattern::textarea(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__AUDIT__INCIDENT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Incidents'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_audit_list__incident'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CATG__AUDIT__INCIDENT'
                    ]
                ]
            ),
            'description'         => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Categories description'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_audit_list__description',
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CATG__AUDIT
                    ]
                ]
            )
        ];
    } // function
} // class
?>