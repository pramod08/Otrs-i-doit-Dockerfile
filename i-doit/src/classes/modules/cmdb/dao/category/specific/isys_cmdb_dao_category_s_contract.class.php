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
 * DAO: specific category for contracts
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Selcuk Kekec <skekec@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_s_contract extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'contract';

    /**
     * Category entry is purgable
     *
     * @var  boolean
     */
    protected $m_is_purgable = true;

    /**
     * Dynamic property handling for getting creation time of an object.
     *
     * @param   $p_value
     *
     * @return  string
     */
    public static function dynamic_property_callback_date($p_value)
    {
        global $g_loc;

        // In order to sort the fields correctly, surrounding elements are not allowed.
        return '<span data-date="' . $p_value . '" class="hide"></span>' . $g_loc->fmt_date($p_value);
    } // function

    /**
     * Dynamic property handling for getting start date of the contract
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_start(array $p_row)
    {
        // This can happen, if the object-type list query does not join / select the start date.
        if ($p_row['isys_cats_contract_list__start_date'] === null)
        {
            global $g_comp_database;

            $p_row['isys_cats_contract_list__start_date'] = isys_cmdb_dao_category_s_contract::instance($g_comp_database)
                ->get_data(null, $p_row['isys_obj__id'])
                ->get_row_value('isys_cats_contract_list__start_date');
        } // if

        return self::dynamic_property_callback_date($p_row['isys_cats_contract_list__start_date']);
    } // function

    /**
     * Dynamic property handling for getting end date of the contract
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_end(array $p_row)
    {
        // This can happen, if the object-type list query does not join / select the end date.
        if ($p_row['isys_cats_contract_list__end_date'] === null)
        {
            global $g_comp_database;

            $p_row['isys_cats_contract_list__end_date'] = isys_cmdb_dao_category_s_contract::instance($g_comp_database)
                ->get_data(null, $p_row['isys_obj__id'])
                ->get_row_value('isys_cats_contract_list__end_date');
        } // if

        return self::dynamic_property_callback_date($p_row['isys_cats_contract_list__end_date']);
    } // function

    /**
     * Dynamic property handling for getting notice date of the contract
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_notice_date(array $p_row)
    {
        // This can happen, if the object-type list query does not join / select the end date.
        if ($p_row['isys_cats_contract_list__notice_date'] === null)
        {
            global $g_comp_database;

            $p_row['isys_cats_contract_list__notice_date'] = isys_cmdb_dao_category_s_contract::instance($g_comp_database)
                ->get_data(null, $p_row['isys_obj__id'])
                ->get_row_value('isys_cats_contract_list__notice_date');
        } // if

        return self::dynamic_property_callback_date($p_row['isys_cats_contract_list__notice_date']);
    } // function

    /**
     * Callback method for the notification type dialog-field.
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function callback_property_notice_type()
    {
        return serialize(
            [
                1 => _L("LC__CATG__CONTRACT__FROM_NOTICE_DATE"),
                2 => _L("LC__CATG__CONTRACT__ON_CONTRACT_END")
            ]
        );
    } // function

    /**
     * Create method. We need to overwrite this method, to properly filter the money property.
     *
     * @param   array $p_data
     *
     * @return  mixed
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function create(array $p_data)
    {
        $p_data['costs'] = isys_helper::filter_number($p_data['costs']);

        if (!isset($p_data['run_time']) && isset($p_data['start_date']) && isset($p_data['end_date']))
        {
            // Calculate run_time with start_date and end_date
            $p_data['run_time_unit'] = C__GUARANTEE_PERIOD_UNIT_DAYS;
            $p_data['run_time']      = (strtotime($p_data['end_date']) - strtotime($p_data['start_date'])) / 60 / 60 / 24;
        } // if

        return parent::create_data($p_data);
    } // function

    /**
     * @param $p_row
     *
     * @return string
     * @throws isys_exception_locale
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function dynamic_property_callback_costs($p_row)
    {
        return ($p_row['isys_cats_contract_list__costs'] > 0) ? $p_row['isys_cats_contract_list__costs'] . ' ' . isys_locale::get_instance()
                ->get_currency() : isys_tenantsettings::get('gui.empty_values', '-');
    } // function

    /**
     * Dynamic property handling for retrieving the notice period + unit.
     *
     * @global  isys_component_database $g_comp_database
     *
     * @param   array                   $p_row
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function dynamic_property_callback_notice_period(array $p_row)
    {
        global $g_comp_database;

        $l_return = '';

        /**
         * @var $l_dao               isys_cmdb_dao_category_s_contract
         * @var $l_dao_dialog_period isys_cmdb_dao_dialog
         * @var $l_dao_dialog_type   isys_cmdb_dao_dialog
         */
        $l_dao               = isys_cmdb_dao_category_s_contract::instance($g_comp_database);
        $l_dao_dialog_period = isys_factory_cmdb_dialog_dao::get_instance('isys_guarantee_period_unit', $g_comp_database);
        $l_dao_dialog_type   = isys_factory_cmdb_dialog_dao::get_instance('isys_contract_notice_period_type', $g_comp_database);

        $l_row = $l_dao->get_data($p_row['isys_cats_contract_list__id'], $p_row['isys_obj__id'])
            ->get_row();

        if ($l_row !== null && $l_row['isys_cats_contract_list__notice_period_unit__id'] !== null)
        {
            $l_unit_row = $l_dao_dialog_period->get_data($l_row['isys_cats_contract_list__notice_period_unit__id']);
            $l_type_row = $l_dao_dialog_type->get_data($l_row['isys_cats_contract_list__isys_contract_notice_period_type__id']);

            $l_return = $l_row['isys_cats_contract_list__notice_period'] . ' ' . _L($l_unit_row['isys_guarantee_period_unit__title']) . ' ' . _L(
                    $l_type_row['isys_contract_notice_period_type__title']
                );
        } // if

        return $l_return;
    } // function

    /**
     * Dynamic property handling for retrieving the maintenance period + unit.
     *
     * @global  isys_component_database $g_comp_database
     *
     * @param   array                   $p_row
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function dynamic_property_callback_maintenance_period(array $p_row)
    {
        global $g_comp_database;

        $l_return     = '';
        $l_dao        = isys_cmdb_dao_category_s_contract::instance($g_comp_database);
        $l_dao_dialog = isys_factory_cmdb_dialog_dao::get_instance('isys_guarantee_period_unit', $g_comp_database);

        $l_row = $l_dao->get_data($p_row['isys_cats_contract_list__id'], $p_row['isys_obj__id'])
            ->get_row();

        if ($l_row !== null && $l_row['isys_cats_contract_list__maintenance_period_unit__id'] !== null)
        {
            $l_unit_row = $l_dao_dialog->get_data($l_row['isys_cats_contract_list__maintenance_period_unit__id']);
            $l_return   = $l_row['isys_cats_contract_list__maintenance_period'] . ' ' . _L($l_unit_row['isys_guarantee_period_unit__title']);
        } // if

        return $l_return;
    } // function

    /**
     * Calculates end of noticeperiod.
     *
     * @param    string $p_contract_end_date
     * @param   integer $p_noticeperiod_value
     * @param   mixed   $p_noticeperiod_unit
     *
     * @return  mixed
     */
    public function calculate_noticeperiod($p_contract_end_date = null, $p_noticeperiod_value = null, $p_noticeperiod_unit = null)
    {
        global $g_loc;

        // @see RT #24703 the notice-period can be "0", so we use "is_numeric" instead of "empty".
        if (!empty($p_contract_end_date) && !empty($p_noticeperiod_unit) && is_numeric($p_noticeperiod_value))
        {
            switch ($p_noticeperiod_unit)
            {
                case C__GUARANTEE_PERIOD_UNIT_MONTH:
                    $l_month        = ((int) date('m', strtotime($p_contract_end_date))) - $p_noticeperiod_value;
                    $l_year_counter = 0;
                    while ($l_month <= 0)
                    {
                        $l_year_counter++;
                        $l_month += 12;
                    } // while
                    $l_day  = date('d', strtotime($p_contract_end_date));
                    $l_year = date('Y', strtotime($p_contract_end_date)) - $l_year_counter;

                    // Get max days of the selected month, year
                    $l_max_days = date('t', strtotime($l_year . '-' . $l_month . '-01'));

                    // in case the days of the month is < from the days of the contract end
                    if ($l_day > $l_max_days)
                    {
                        $l_day = $l_max_days;
                    } // if

                    $l_month    = ($l_month < 10) ? '0' . $l_month : $l_month;
                    $l_new_date = $l_day . '.' . $l_month . '.' . $l_year;

                    return date($g_loc->get_date_format(), strtotime($l_new_date));
                    break;
                case C__GUARANTEE_PERIOD_UNIT_YEARS:
                    $l_year  = ((int) date('Y', strtotime($p_contract_end_date))) - $p_noticeperiod_value;
                    $l_day   = date('d', strtotime($p_contract_end_date));
                    $l_month = date('m', strtotime($p_contract_end_date));

                    // Get max days of the selected month, year
                    $l_max_days = date('t', strtotime($l_year . '-' . $l_month . '-01'));

                    // in case the days of the month is < from the days of the contract end
                    if ($l_day > $l_max_days)
                    {
                        $l_day = $l_max_days;
                    } // if

                    $l_new_date = $l_day . '.' . $l_month . '.' . $l_year;

                    return date($g_loc->get_date_format(), strtotime($l_new_date));
                    break;
                default:
                    return date(
                        $g_loc->get_date_format(),
                        (strtotime($p_contract_end_date) - isys_convert::period_to_seconds($p_noticeperiod_value, $p_noticeperiod_unit, '-'))
                    );
                    break;
            } // switch
        }
        else
        {
            return null;
        } // if
    } // function

    /**
     * Calculates next contract end.
     *
     * @param   string  $p_expiration_date
     * @param   integer $p_noticeperiod_value
     * @param   mixed   $p_noticeperiod_unit
     *
     * @return  mixed
     */
    public function calculate_next_contract_end_date($p_expiration_date = null, $p_noticeperiod_value = null, $p_noticeperiod_unit = null)
    {
        global $g_loc;

        if (!empty($p_expiration_date) && !empty($p_noticeperiod_unit) && !empty($p_noticeperiod_value))
        {
            switch ($p_noticeperiod_unit)
            {
                case C__GUARANTEE_PERIOD_UNIT_MONTH:
                    $l_month        = ((int) date('m', strtotime($p_expiration_date))) + $p_noticeperiod_value;
                    $l_year_counter = 0;
                    while ($l_month > 12)
                    {
                        $l_year_counter++;
                        $l_month -= 12;
                    }
                    $l_day      = date('d', strtotime($p_expiration_date));
                    $l_year     = date('Y', strtotime($p_expiration_date)) + $l_year_counter;
                    $l_month    = ($l_month < 10) ? '0' . $l_month : $l_month;
                    $l_new_date = $l_day . '.' . $l_month . '.' . $l_year;

                    return date($g_loc->get_date_format(), strtotime($l_new_date));
                    break;
                case C__GUARANTEE_PERIOD_UNIT_YEARS:
                    $l_year     = ((int) date('Y', strtotime($p_expiration_date))) + $p_noticeperiod_value;
                    $l_day      = date('d', strtotime($p_expiration_date));
                    $l_month    = date('m', strtotime($p_expiration_date));
                    $l_new_date = $l_day . '.' . $l_month . '.' . $l_year;

                    return date($g_loc->get_date_format(), strtotime($l_new_date));
                    break;
                default:
                    return date($g_loc->get_date_format(), (strtotime($p_expiration_date) + isys_convert::period_to_seconds($p_noticeperiod_value, $p_noticeperiod_unit)));
                    break;
            }
        }
        else
        {
            return null;
        } // if
    } // function

    /**
     * Calculates end of maintenanceperiod.
     *
     * @param   string  $p_startdate
     * @param   integer $p_maintenanceperiod_value
     * @param   mixed   $p_maintenanceperiod_unit
     *
     * @return  mixed
     */
    public function calculate_maintenanceperiod($p_startdate = null, $p_maintenanceperiod_value = null, $p_maintenanceperiod_unit = null)
    {
        global $g_loc;

        if (!empty($p_startdate) && !empty($p_maintenanceperiod_value) && !empty($p_maintenanceperiod_unit))
        {
            switch ($p_maintenanceperiod_unit)
            {
                case C__GUARANTEE_PERIOD_UNIT_MONTH:
                    $l_month        = ((int) date('m', strtotime($p_startdate))) + $p_maintenanceperiod_value;
                    $l_year_counter = 0;
                    while ($l_month > 12)
                    {
                        $l_year_counter++;
                        $l_month -= 12;
                    }
                    $l_day  = date('d', strtotime($p_startdate));
                    $l_year = date('Y', strtotime($p_startdate)) + $l_year_counter;

                    $l_month    = ($l_month < 10) ? '0' . $l_month : $l_month;
                    $l_new_date = $l_day . '.' . $l_month . '.' . $l_year;

                    return date($g_loc->get_date_format(), strtotime($l_new_date));
                    break;
                case C__GUARANTEE_PERIOD_UNIT_YEARS:
                    $l_year     = ((int) date('Y', strtotime($p_startdate))) + $p_maintenanceperiod_value;
                    $l_day      = date('d', strtotime($p_startdate));
                    $l_month    = date('m', strtotime($p_startdate));
                    $l_new_date = $l_day . '.' . $l_month . '.' . $l_year;

                    return date($g_loc->get_date_format(), strtotime($l_new_date));
                    break;
                default:
                    return date($g_loc->get_date_format(), strtotime($p_startdate) + isys_convert::period_to_seconds($p_maintenanceperiod_value, $p_maintenanceperiod_unit));
                    break;
            }
        }
        else
        {
            return null;
        } // if
    } // function

    /**
     * Method for retrieving the dynamic properties, used by the new list component.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function dynamic_properties()
    {
        $l_dao = new isys_cmdb_dao_category_s_contract(isys_application::instance()->database);

        return [
            '_notice_period'      => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CONTRACT__NOTICE_VALUE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Cancellation period'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_contract_list__id'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $l_dao,
                        'dynamic_property_callback_notice_period'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST   => true,
                    C__PROPERTY__PROVIDES__REPORT => true
                ]
            ],
            '_maintenance_period' => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CONTRACT__MAINTENANCE_PERIOD',
                    C__PROPERTY__INFO__DESCRIPTION => 'Maintenance-/guarantee period'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_contract_list__id'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $l_dao,
                        'dynamic_property_callback_maintenance_period'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST   => true,
                    C__PROPERTY__PROVIDES__REPORT => true
                ]
            ],
            '_start_date'         => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CONTRACT__START_DATE',
                    C__PROPERTY__INFO__DESCRIPTION => ''
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_contract_list__start_date'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $l_dao,
                        'dynamic_property_callback_start'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST => true
                ]
            ],
            '_end_date'           => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CONTRACT__END_DATE',
                    C__PROPERTY__INFO__DESCRIPTION => ''
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_contract_list__end_date'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $l_dao,
                        'dynamic_property_callback_end'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST => true
                ]
            ],
            '_notice_date'        => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CONTRACT__NOTICE_DATE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Cancellation date'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_contract_list__id'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $l_dao,
                        'dynamic_property_callback_notice_date'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST => true
                ]
            ],
            '_costs'              => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CONTRACT__COSTS',
                    C__PROPERTY__INFO__DESCRIPTION => 'Costs'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_contract_list__costs'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $l_dao,
                        'dynamic_property_callback_costs'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST   => true,
                    C__PROPERTY__PROVIDES__REPORT => true
                ]
            ]
        ];
    } // function

    /**
     * Returns how many entries exists. The folder only needs to know if there are any entries in its subcategories.
     *
     * @param null $p_obj_id
     *
     * @return int
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_count($p_obj_id = null)
    {
        if ($this->get_category_id() == C__CATS__CONTRACT)
        {
            $l_sql = 'SELECT
				(
				IFNULL((SELECT isys_cats_contract_list__id AS cnt FROM isys_cats_contract_list
					WHERE isys_cats_contract_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ' LIMIT 1), 0)
				+
				IFNULL((SELECT isys_catg_contract_assignment_list__id AS cnt FROM  isys_catg_contract_assignment_list
					INNER JOIN isys_connection ON isys_connection__id = isys_catg_contract_assignment_list__isys_connection__id
					WHERE isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ' LIMIT 1), 0)
				)
				AS cnt';

            return ($this->retrieve($l_sql)
                    ->get_row_value('cnt') > 0) ? 1 : 0;
        }
        else
        {
            return parent::get_count($p_obj_id);
        } // if
    } // function

    /**
     * Return Category Data.
     *
     * @param   integer $p_cats_list_id
     * @param   mixed   $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_cats_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = 'SELECT *, notice_unit.isys_guarantee_period_unit__title AS notice_title, maintenance_unit.isys_guarantee_period_unit__title AS main_title FROM isys_cats_contract_list
			INNER JOIN isys_obj ON isys_obj__id = isys_cats_contract_list__isys_obj__id
			LEFT JOIN isys_contract_type ON isys_contract_type__id = isys_cats_contract_list__isys_contract_type__id
			LEFT JOIN isys_contract_status ON isys_contract_status__id = isys_cats_contract_list__isys_contract_status__id
			LEFT JOIN isys_contract_end_type ON isys_contract_end_type__id = isys_cats_contract_list__isys_contract_end_type__id
			LEFT JOIN isys_contract_reaction_rate ON isys_contract_reaction_rate__id = isys_cats_contract_list__isys_contract_reaction_rate__id
			LEFT JOIN isys_guarantee_period_unit AS notice_unit ON isys_cats_contract_list__notice_period_unit__id = notice_unit.isys_guarantee_period_unit__id
			LEFT JOIN isys_guarantee_period_unit AS maintenance_unit ON isys_cats_contract_list__maintenance_period_unit__id = maintenance_unit.isys_guarantee_period_unit__id
			LEFT JOIN isys_contract_payment_period AS payment_period ON isys_cats_contract_list__isys_contract_payment_period__id = payment_period.isys_contract_payment_period__id
			WHERE TRUE ' . $p_condition . $this->prepare_filter($p_filter);

        if ($p_obj_id !== null)
        {
            $l_sql .= $this->get_object_condition($p_obj_id);
        } // if

        if ($p_cats_list_id !== null)
        {
            $l_sql .= ' AND isys_cats_contract_list__id = ' . $this->convert_sql_id($p_cats_list_id);
        } // if

        if ($p_status !== null)
        {
            $l_sql .= ' AND isys_cats_contract_list__status = ' . $this->convert_sql_int($p_status);
        } // if

        return $this->retrieve($l_sql);
    } // function

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'type'                    => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CONTRACT__TYPE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Contract type'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD      => 'isys_cats_contract_list__isys_contract_type__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_contract_type',
                            'isys_contract_type__id'
                        ]
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CATS__CONTRACT__TYPE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_contract_type'
                        ]
                    ]
                ]
            ),
            'contract_no'             => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CONTRACT__CONTRACT_NO',
                        C__PROPERTY__INFO__DESCRIPTION => 'Contract id'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_contract_list__contract_no'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATS__CONTRACT__CONTRACT_NO'
                    ]
                ]
            ),
            'customer_no'             => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CONTRACT__CUSTOMER_NO',
                        C__PROPERTY__INFO__DESCRIPTION => 'Customer id'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_contract_list__customer_no'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATS__CONTRACT__CUSTOMER_NO'
                    ]
                ]
            ),
            'internal_no'             => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CONTRACT__INTERNAL_NO',
                        C__PROPERTY__INFO__DESCRIPTION => 'Internal id'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_contract_list__internal_no'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATS__CONTRACT__INTERNAL_NO'
                    ]
                ]
            ),
            'costs'                   => array_replace_recursive(
                isys_cmdb_dao_category_pattern::money(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CONTRACT__COSTS',
                        C__PROPERTY__INFO__DESCRIPTION => 'Costs'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_contract_list__costs'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATS__CONTRACT__COSTS'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ]
                ]
            ),
            'product'                 => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CONTRACT__PRODUCT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Product'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_contract_list__product'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATS__CONTRACT__PRODUCT'
                    ]
                ]
            ),
            'reaction_rate'           => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CONTRACT__REACTION_RATE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Reaction rate'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD      => 'isys_cats_contract_list__isys_contract_reaction_rate__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_contract_reaction_rate',
                            'isys_contract_reaction_rate__id'
                        ]
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CATS__CONTRACT__REACTION_RATE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_contract_reaction_rate'
                        ]
                    ]
                ]
            ),
            'contract_status'         => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CONTRACT__STATUS',
                        C__PROPERTY__INFO__DESCRIPTION => 'Contract status'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD      => 'isys_cats_contract_list__isys_contract_status__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_contract_status',
                            'isys_contract_status__id'
                        ]
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CATS__CONTRACT__CONTRACT_STATUS',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_contract_status'
                        ]
                    ]
                ]
            ),
            'start_date'              => array_replace_recursive(
                isys_cmdb_dao_category_pattern::date(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CONTRACT__START_DATE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Start of contract'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_contract_list__start_date'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATS__CONTRACT__START_DATE'
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'date'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST => false,
                    ]
                ]
            ),
            'end_date'                => array_replace_recursive(
                isys_cmdb_dao_category_pattern::date(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CONTRACT__END_DATE',
                        C__PROPERTY__INFO__DESCRIPTION => 'End of contract'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_contract_list__end_date'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATS__CONTRACT__END_DATE'
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'date'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST => false,
                    ]
                ]
            ),
            'run_time'                => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__LEASING__RUNTIME',
                        C__PROPERTY__INFO__DESCRIPTION => 'Running time of contract'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_contract_list__runtime'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATS__CONTRACT__RUNTIME',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strClass' => 'input-dual-large',
                            'p_onChange' => 'window.date_callback_runtime();'
                        ]
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'timeperiod'
                        ],
                        C__PROPERTY__FORMAT__UNIT     => 'run_time_unit',
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        C__PROPERTY__PROVIDES__REPORT    => true,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false
                    ]
                ]
            ),
            'run_time_unit'           => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CONTRACT__RUNTIME_UNIT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Running time lof contract'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_cats_contract_list__runtime_unit',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_guarantee_period_unit',
                            'isys_guarantee_period_unit__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATS__CONTRACT__RUNTIME_PERIOD_UNIT',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable'        => 'isys_guarantee_period_unit',
                            'p_strClass'        => 'input-dual-small',
                            'p_onChange'        => 'window.date_callback_runtime();',
                            'p_bInfoIconSpacer' => 0
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH    => false,
                        //C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false
                    ]
                ]
            ),
            'next_contract_end_date'  => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CONTRACT__CONTRACT_END',
                        C__PROPERTY__INFO__DESCRIPTION => 'Next possible end of contract'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_contract_list__id'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATS__CONTRACT__CONTRACT_END',
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__IMPORT    => false,
                        C__PROPERTY__PROVIDES__EXPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__SEARCH    => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'contract_property_next_contract_end_date'
                        ]
                    ]
                ]
            ),
            'end_type'                => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CONTRACT__END_TYPE',
                        C__PROPERTY__INFO__DESCRIPTION => 'End of contract by'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD      => 'isys_cats_contract_list__isys_contract_end_type__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_contract_end_type',
                            'isys_contract_end_type__id'
                        ]
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CATS__CONTRACT__END_TYPE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_contract_end_type'
                        ]
                    ]
                ]
            ),
            'next_notice_end_date'    => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CONTRACT__CONTRACT_NOTICE_END',
                        C__PROPERTY__INFO__DESCRIPTION => 'Next possible notice end date'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_contract_list__id'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATS__CONTRACT__CONTRACT_END',
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__IMPORT    => false,
                        C__PROPERTY__PROVIDES__EXPORT    => false,
                        C__PROPERTY__PROVIDES__LIST      => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false,
                        C__PROPERTY__PROVIDES__REPORT    => false,
                        C__PROPERTY__PROVIDES__SEARCH    => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'contract_property_next_notice_end_date'
                        ]
                    ]
                ]
            ),
            'notice_date'             => array_replace_recursive(
                isys_cmdb_dao_category_pattern::date(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CONTRACT__NOTICE_DATE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Cancellation date'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_contract_list__notice_date'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATS__CONTRACT__NOTICE_DATE'
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'date'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST => false,
                    ]
                ]
            ),
            'notice_period'           => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CONTRACT__NOTICE_VALUE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Cancellation period'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_contract_list__notice_period'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATS__CONTRACT__NOTICE_VALUE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strStyle' => 'width:133px;',
                            'p_onChange' => 'window.calculate_next_end_date();'
                        ]
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'timeperiod'
                        ],
                        C__PROPERTY__FORMAT__UNIT     => 'notice_period_unit'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__REPORT => false
                    ]
                ]
            ),
            'notice_period_unit'      => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CONTRACT__NOTICE_VALUE_UNIT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Unit'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_cats_contract_list__notice_period_unit__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_guarantee_period_unit',
                            'isys_guarantee_period_unit__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATS__CONTRACT__NOTICE_UNIT',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable'        => 'isys_guarantee_period_unit',
                            'p_onChange'        => 'window.calculate_next_end_date();',
                            'p_strClass'        => 'input-mini',
                            'p_bInfoIconSpacer' => 0
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ]
                ]
            ),
            'notice_type'             => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CONTRACT__NOTICE_TYPE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Notice type'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_cats_contract_list__isys_contract_notice_period_type__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_contract_notice_period_type',
                            'isys_contract_notice_period_type__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATS__CONTRACT__NOTICE_PERIOD_TYPE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable'        => 'isys_contract_notice_period_type',
                            'p_onChange'        => 'window.calculate_next_end_date();',
                            'p_strClass'        => 'input-small',
                            'p_bInfoIconSpacer' => 0
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ]
                ]
            ),
            'maintenance_period'      => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CONTRACT__MAINTENANCE_PERIOD',
                        C__PROPERTY__INFO__DESCRIPTION => 'Maintenance-/guarantee period'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_contract_list__maintenance_period'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATS__CONTRACT__MAINTENANCE_PERIOD',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strClass' => 'input-dual-large'
                        ]
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'timeperiod'
                        ],
                        C__PROPERTY__FORMAT__UNIT     => 'maintenance_period_unit'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__REPORT => false
                    ]
                ]
            ),
            'maintenance_period_unit' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CONTRACT__MAINTENANCE_PERIOD_UNIT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Unit'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_cats_contract_list__maintenance_period_unit__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_guarantee_period_unit',
                            'isys_guarantee_period_unit__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATS__CONTRACT__MAINTENANCE_PERIOD_UNIT',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable'        => 'isys_guarantee_period_unit',
                            'p_strClass'        => 'input-dual-small',
                            'p_bInfoIconSpacer' => 0
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ]
                ]
            ),
            'payment_period'          => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CONTRACT__PAYMENT_PERIOD',
                        C__PROPERTY__INFO__DESCRIPTION => 'Payment period'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD      => 'isys_cats_contract_list__isys_contract_payment_period__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_contract_payment_period',
                            'isys_contract_payment_period__id'
                        ]
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CATS__CONTRACT__PAYMENT_PERIOD',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_contract_payment_period'
                        ]
                    ]
                ]
            ),
            'description'             => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_contract_list__description'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . $this->get_category_type() . $this->get_category_id()
                    ]
                ]
            )
        ];
    } // function

    /**
     * Create method. We need to overwrite this method, to properly filter the money property.
     *
     * @param   integer $p_category_data_id
     * @param   array   $p_data
     *
     * @return  mixed
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function save_data($p_category_data_id, $p_data)
    {
        $p_data['costs'] = isys_helper::filter_number($p_data['costs']);

        if (empty($_POST['C__CATS__CONTRACT__START_DATE']))
        {
            // ID-1837 We assume the user has removed the "end date" willingly.
            $p_data['start_date'] = '';
        } // if

        if (empty($_POST['C__CATS__CONTRACT__END_DATE']))
        {
            // ID-1837 We assume the user has removed the "end date" willingly.
            $p_data['end_date'] = '';
        } // if

        if (!isset($p_data['run_time']) && isset($p_data['start_date']) && isset($p_data['end_date']))
        {
            // Calculate run_time with start_date and end_date
            $p_data['run_time_unit'] = C__GUARANTEE_PERIOD_UNIT_DAYS;
            $p_data['run_time']      = (strtotime($p_data['end_date']) - strtotime($p_data['start_date'])) / isys_convert::DAY;
        } // if

        return parent::save_data($p_category_data_id, $p_data);
    } // function
} // class