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
 * DAO: global virtual category for the livestatus connection.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.3.0
 */
class isys_cmdb_dao_category_g_livestatus extends isys_cmdb_dao_category_g_virtual
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'livestatus';

    /**
     * Dynamic property handling for getting the livestatus state of an object.
     *
     * @global  isys_component_database $g_comp_database
     * @global  array                   $g_config
     *
     * @param   array                   $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_livestatus_state(array $p_row)
    {
        global $g_dirs, $g_comp_database;

        $l_row = isys_cmdb_dao_category_g_monitoring::instance($g_comp_database)
            ->get_data(null, $p_row['__id__'])
            ->get_row();

        if (is_array($l_row) && $l_row['isys_monitoring_hosts__type'] == C__MONITORING__TYPE_LIVESTATUS)
        {
            return '<span class="livestatus_state loading"><img src="' . $g_dirs['images'] . 'ajax-loading.gif" class="vam" /> <span class="vam">' . _L(
                'LC__UNIVERSAL__LOADING'
            ) . '</span></span>';
        } // if

        return '<span>' . isys_tenantsettings::get('gui.empty_value', '-') . '</span>';
    } // function

    /**
     * Dynamic property handling for getting the livestatus state of an object.
     *
     * @global  isys_component_database $g_comp_database
     * @global  array                   $g_config
     *
     * @param   array                   $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_livestatus_state_button(array $p_row)
    {
        global $g_comp_database;

        $l_row = isys_cmdb_dao_category_g_monitoring::instance($g_comp_database)
            ->get_data(null, $p_row['__id__'])
            ->get_row();

        if (is_array($l_row) && $l_row['isys_monitoring_hosts__type'] == C__MONITORING__TYPE_LIVESTATUS && $l_row['isys_monitoring_hosts__active'] == 1)
        {
            $l_livestatus_url = isys_helper_link::create_url(
                [
                    C__GET__AJAX      => 1,
                    C__GET__AJAX_CALL => 'monitoring_livestatus',
                    'func'            => 'load_livestatus_state'
                ]
            );

            return '<button type="button" class="btn btn-mini" onclick="load_livestatus_state_in_list(this);" data-url="' . $l_livestatus_url . '">' . _L(
                'LC__UNIVERSAL__LOAD'
            ) . '</button>';
        } // if

        return '<span>' . isys_tenantsettings::get('gui.empty_value', '-') . '</span>';
    } // function

    /**
     * Method for retrieving the dynamic properties, used by the new list component.
     *
     * @return  array
     */
    protected function dynamic_properties()
    {
        return [
            '_livestatus_state'        => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__MONITORING__LIVESTATUS_STATUS',
                    C__PROPERTY__INFO__DESCRIPTION => 'Livestatus status'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_callback_livestatus_state'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST => true
                ]
            ],
            '_livestatus_state_button' => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__MONITORING__LIVESTATUS_STATUS_BUTTON',
                    C__PROPERTY__INFO__DESCRIPTION => 'Livestatus status button'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_callback_livestatus_state_button'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST => true
                ]
            ]
        ];
    } // function

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function properties()
    {
        return [];
    } // function
} // class