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
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_sla extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'sla';

    /**
     * Field for singlevalue categories which determines if the entry is purgable or not.
     *
     * @var  boolean
     */
    protected $m_is_purgable = true;

    /**
     * Method for calculating a string ("hh:mm") to an amount of seconds.
     *
     * @param   string $p_time
     *
     * @return  integer
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function calculate_time_to_seconds($p_time)
    {
        $p_time = trim($p_time);

        if (preg_match('~([01]?[0-9]|2[0-3]):?([0-5]?[0-9])?~', $p_time))
        {
            list($l_hour, $l_minute) = explode(':', $p_time);

            // A bit validation...
            if ($l_hour >= 24)
            {
                $l_hour = 23;
            }
            else if ($l_hour < 0)
            {
                $l_hour = 0;
            } // if

            if ($l_minute >= 60)
            {
                $l_minute = 59;
            }
            else if ($l_minute < 0)
            {
                $l_minute = 0;
            } // if

            return ($l_minute * isys_convert::MINUTE) + ($l_hour * isys_convert::HOUR);
        } // if

        return 0;
    } // function

    /**
     * This method calculates from an integer of seconds to a string ("hh:mm").
     *
     * @param   integer $p_seconds
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function calculate_seconds_to_time($p_seconds)
    {
        $l_hour   = (int) $p_seconds / isys_convert::HOUR;
        $l_minute = ($p_seconds % isys_convert::HOUR) / isys_convert::MINUTE;

        return sprintf('%02d:%02d', $l_hour, $l_minute);
    } // function

    /**
     * Callback method for the ports dialog-field.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_service_level(isys_request $p_request)
    {
        return [
            1 => _L('LC__CMDB__CATS__SLA_MINIMAL_AVAILABILITY_IN_PERCENT'),
            2 => _L('LC__CMDB__CATS__SLA_FAILURE_CONTINGENT_IN_HOURS')
        ];
    } // function

    /**
     * Creates new entity.
     *
     * @param   array $p_data Properties in a associative array with tags as keys and their corresponding values as values.
     *
     * @return  mixed  Returns created entity's identifier (int) or false (bool).
     */
    public function create($p_data)
    {
        return parent::create($this->prepare_data($p_data));
    } // function

    /**
     * Updates existing entity.
     *
     * @param   integer $p_category_data_id Entity's identifier
     * @param   array   $p_data             Properties in a associative array with tags as keys and their corresponding values as values.
     *
     * @return  boolean
     */
    public function save($p_category_data_id, $p_data)
    {
        return parent::save($p_category_data_id, $this->prepare_data($p_data));
    } // function

    /**
     * Method for preparing the data before save / create action.
     *
     * @param   array $p_data
     *
     * @return  array
     */
    protected function prepare_data($p_data)
    {
        if (count($_POST) > 0)
        {
            $l_post_keys = [
                'C__CATG__SLA__WEEK_DAY__SUNDAY',
                'C__CATG__SLA__WEEK_DAY__SATURDAY',
                'C__CATG__SLA__WEEK_DAY__FRIDAY',
                'C__CATG__SLA__WEEK_DAY__THURSDAY',
                'C__CATG__SLA__WEEK_DAY__WEDNESDAY',
                'C__CATG__SLA__WEEK_DAY__TUESDAY',
                'C__CATG__SLA__WEEK_DAY__MONDAY',
            ];
            $l_check     = false;
            // check if post key exists
            while (count($l_post_keys) > 0)
            {
                $l_check = array_key_exists(array_pop($l_post_keys), $_POST);
                if ($l_check) $l_post_keys = [];
            }

            if ($l_check)
            {
                $l_days = ($_POST['C__CATG__SLA__WEEK_DAY__SUNDAY'] == 1) ? 1 : 0;
                $l_days += ($_POST['C__CATG__SLA__WEEK_DAY__SATURDAY'] == 1) ? 2 : 0;
                $l_days += ($_POST['C__CATG__SLA__WEEK_DAY__FRIDAY'] == 1) ? 4 : 0;
                $l_days += ($_POST['C__CATG__SLA__WEEK_DAY__THURSDAY'] == 1) ? 8 : 0;
                $l_days += ($_POST['C__CATG__SLA__WEEK_DAY__WEDNESDAY'] == 1) ? 16 : 0;
                $l_days += ($_POST['C__CATG__SLA__WEEK_DAY__TUESDAY'] == 1) ? 32 : 0;
                $l_days += ($_POST['C__CATG__SLA__WEEK_DAY__MONDAY'] == 1) ? 64 : 0;

                $p_data['days'] = decbin($l_days);
            }
        }

        if (isset($_POST['C__CATG__SLA__WEEK_DAY__MONDAY_TIME_FROM']) && isset($_POST['C__CATG__SLA__WEEK_DAY__MONDAY_TIME_FROM']))
        {
            $p_data['monday_time'] = isys_format_json::encode(
                [
                    'from' => self::calculate_time_to_seconds($_POST['C__CATG__SLA__WEEK_DAY__MONDAY_TIME_FROM']),
                    'to'   => self::calculate_time_to_seconds($_POST['C__CATG__SLA__WEEK_DAY__MONDAY_TIME_TO'])
                ]
            );
        }
        if (isset($_POST['C__CATG__SLA__WEEK_DAY__TUESDAY_TIME_FROM']) && isset($_POST['C__CATG__SLA__WEEK_DAY__TUESDAY_TIME_TO']))
        {
            $p_data['tuesday_time'] = isys_format_json::encode(
                [
                    'from' => self::calculate_time_to_seconds($_POST['C__CATG__SLA__WEEK_DAY__TUESDAY_TIME_FROM']),
                    'to'   => self::calculate_time_to_seconds($_POST['C__CATG__SLA__WEEK_DAY__TUESDAY_TIME_TO'])
                ]
            );
        }
        if (isset($_POST['C__CATG__SLA__WEEK_DAY__WEDNESDAY_TIME_FROM']) && isset($_POST['C__CATG__SLA__WEEK_DAY__WEDNESDAY_TIME_TO']))
        {
            $p_data['wednesday_time'] = isys_format_json::encode(
                [
                    'from' => self::calculate_time_to_seconds($_POST['C__CATG__SLA__WEEK_DAY__WEDNESDAY_TIME_FROM']),
                    'to'   => self::calculate_time_to_seconds($_POST['C__CATG__SLA__WEEK_DAY__WEDNESDAY_TIME_TO'])
                ]
            );
        }
        if (isset($_POST['C__CATG__SLA__WEEK_DAY__THURSDAY_TIME_FROM']) && isset($_POST['C__CATG__SLA__WEEK_DAY__THURSDAY_TIME_TO']))
        {
            $p_data['thursday_time'] = isys_format_json::encode(
                [
                    'from' => self::calculate_time_to_seconds($_POST['C__CATG__SLA__WEEK_DAY__THURSDAY_TIME_FROM']),
                    'to'   => self::calculate_time_to_seconds($_POST['C__CATG__SLA__WEEK_DAY__THURSDAY_TIME_TO'])
                ]
            );
        }
        if (isset($_POST['C__CATG__SLA__WEEK_DAY__FRIDAY_TIME_FROM']) && isset($_POST['C__CATG__SLA__WEEK_DAY__FRIDAY_TIME_TO']))
        {
            $p_data['friday_time'] = isys_format_json::encode(
                [
                    'from' => self::calculate_time_to_seconds($_POST['C__CATG__SLA__WEEK_DAY__FRIDAY_TIME_FROM']),
                    'to'   => self::calculate_time_to_seconds($_POST['C__CATG__SLA__WEEK_DAY__FRIDAY_TIME_TO'])
                ]
            );
        }
        if (isset($_POST['C__CATG__SLA__WEEK_DAY__SATURDAY_TIME_FROM']) && isset($_POST['C__CATG__SLA__WEEK_DAY__SATURDAY_TIME_TO']))
        {
            $p_data['saturday_time'] = isys_format_json::encode(
                [
                    'from' => self::calculate_time_to_seconds($_POST['C__CATG__SLA__WEEK_DAY__SATURDAY_TIME_FROM']),
                    'to'   => self::calculate_time_to_seconds($_POST['C__CATG__SLA__WEEK_DAY__SATURDAY_TIME_TO'])
                ]
            );
        }
        if (isset($_POST['C__CATG__SLA__WEEK_DAY__SUNDAY_TIME_FROM']) && isset($_POST['C__CATG__SLA__WEEK_DAY__SUNDAY_TIME_TO']))
        {
            $p_data['sunday_time'] = isys_format_json::encode(
                [
                    'from' => self::calculate_time_to_seconds($_POST['C__CATG__SLA__WEEK_DAY__SUNDAY_TIME_FROM']),
                    'to'   => self::calculate_time_to_seconds($_POST['C__CATG__SLA__WEEK_DAY__SUNDAY_TIME_TO'])
                ]
            );
        }

        return $p_data;
    } // function

    /**
     * Method for returning the properties.
     *
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     * @return  array
     */
    protected function properties()
    {
        return [
            'service_id'           => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__SLA_SERVICE_ID',
                        C__PROPERTY__INFO__DESCRIPTION => 'Service-ID'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_sla_list__service_id'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__SLA__SERVICE_ID'
                    ]
                ]
            ),
            'service_level'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO   => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__SLA_SERVICELEVEL',
                        C__PROPERTY__INFO__DESCRIPTION => 'Servicelevel'
                    ],
                    C__PROPERTY__DATA   => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_sla_list__service_level'
                    ],
                    C__PROPERTY__UI     => [
                        C__PROPERTY__UI__ID     => 'C__CATG__SLA__SERVICE_LEVEL',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData'   => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_sla',
                                    'callback_property_service_level'
                                ]
                            ),
                            'p_strClass' => 'input-dual-large'
                        ]
                    ],
                    C__PROPERTY__FORMAT => [
                        C__PROPERTY__PROVIDES__REPORT => false
                    ]
                ]
            ),
            'service_level_dialog' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__SLA_SERVICELEVEL',
                        C__PROPERTY__INFO__DESCRIPTION => 'Servicelevel'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_sla_list__isys_sla_service_level__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_sla_service_level',
                            'isys_sla_service_level__id',
                        ]
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CATG__SLA__SERVICE_LEVEL_DIALOG',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable'        => 'isys_sla_service_level',
                            'p_strClass'        => 'input-dual-small',
                            'p_bInfoIconSpacer' => 0
                        ]
                    ]
                ]
            ),
            'days'                 => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__UNIVERSAL__WEEKDAY',
                        C__PROPERTY__INFO__DESCRIPTION => 'Weekday'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_sla_list__days'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__NONE'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT     => false,
                        C__PROPERTY__PROVIDES__LIST       => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                        C__PROPERTY__PROVIDES__VALIDATION => false,
                        C__PROPERTY__PROVIDES__SEARCH     => false
                    ]
                ]
            ),
            'monday_time'          => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__UNIVERSAL__CALENDAR__DAYS_MONDAY',
                        C__PROPERTY__INFO__DESCRIPTION => 'Monday timing'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_sla_list__monday_time'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__SLA__WEEK_DAY__MONDAY_TIME_FROM',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strStyle'       => 'width:50px;',
                            'p_strPlaceholder' => 'hh:mm',
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'sla_property_servicetimes'
                        ]
                    ]
                ]
            ),
            'tuesday_time'         => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__UNIVERSAL__CALENDAR__DAYS_TUESDAY',
                        C__PROPERTY__INFO__DESCRIPTION => 'Tuesday timing'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_sla_list__tuesday_time'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__SLA__WEEK_DAY__TUESDAY_TIME_FROM',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strStyle'       => 'width:50px;',
                            'p_strPlaceholder' => 'hh:mm',
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'sla_property_servicetimes'
                        ]
                    ]
                ]
            ),
            'wednesday_time'       => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__UNIVERSAL__CALENDAR__DAYS_WEDNESDAY',
                        C__PROPERTY__INFO__DESCRIPTION => 'Wednesday timing'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_sla_list__wednesday_time'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__SLA__WEEK_DAY__WEDNESDAY_TIME_FROM',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strStyle'       => 'width:50px;',
                            'p_strPlaceholder' => 'hh:mm',
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'sla_property_servicetimes'
                        ]
                    ]
                ]
            ),
            'thursday_time'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__UNIVERSAL__CALENDAR__DAYS_THURSDAY',
                        C__PROPERTY__INFO__DESCRIPTION => 'Thursday timing'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_sla_list__thursday_time'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__SLA__WEEK_DAY__THURSDAY_TIME_FROM',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strStyle'       => 'width:50px;',
                            'p_strPlaceholder' => 'hh:mm',
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'sla_property_servicetimes'
                        ]
                    ]
                ]
            ),
            'friday_time'          => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__UNIVERSAL__CALENDAR__DAYS_FRIDAY',
                        C__PROPERTY__INFO__DESCRIPTION => 'Friday timing'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_sla_list__friday_time'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__SLA__WEEK_DAY__FRIDAY_TIME_FROM',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strStyle'       => 'width:50px;',
                            'p_strPlaceholder' => 'hh:mm',
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'sla_property_servicetimes'
                        ]
                    ]
                ]
            ),
            'saturday_time'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__UNIVERSAL__CALENDAR__DAYS_SATURDAY',
                        C__PROPERTY__INFO__DESCRIPTION => 'Saturday timing'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_sla_list__saturday_time'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__SLA__WEEK_DAY__SATURDAY_TIME_FROM',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strStyle'       => 'width:50px;',
                            'p_strPlaceholder' => 'hh:mm',
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'sla_property_servicetimes'
                        ]
                    ]
                ]
            ),
            'sunday_time'          => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__UNIVERSAL__CALENDAR__DAYS_SUNDAY',
                        C__PROPERTY__INFO__DESCRIPTION => 'Sunday timing'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_sla_list__sunday_time'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__SLA__WEEK_DAY__SUNDAY_TIME_FROM',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strStyle'       => 'width:50px;',
                            'p_strPlaceholder' => 'hh:mm',
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'sla_property_servicetimes'
                        ]
                    ]
                ]
            ),
            'reaction_time'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO   => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__SLA_REACTIONTIME',
                        C__PROPERTY__INFO__DESCRIPTION => 'Reaction time'
                    ],
                    C__PROPERTY__DATA   => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_sla_list__reaction_time'
                    ],
                    C__PROPERTY__UI     => [
                        C__PROPERTY__UI__ID     => 'C__CATG__SLA__REACTION_INTERVAL',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strClass' => 'input-mini'
                        ]
                    ],
                    C__PROPERTY__FORMAT => [
                        C__PROPERTY__FORMAT__UNIT => 'reaction_time_unit'
                    ]
                ]
            ),
            'reaction_time_unit'   => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'Reaction time unit',
                        C__PROPERTY__INFO__DESCRIPTION => 'Reaction time unit'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_sla_list__reaction_time_unit',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_unit_of_time',
                            'isys_unit_of_time__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__SLA__REACTION_INTERVAL_UNIT',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_unit_of_time',
                            'p_strClass' => 'input input-mini'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => false
                    ]
                ]
            ),
            'recovery_time'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO   => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__SLA_RECOVERYTIME',
                        C__PROPERTY__INFO__DESCRIPTION => 'Recovery time'
                    ],
                    C__PROPERTY__DATA   => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_sla_list__recovery_time'
                    ],
                    C__PROPERTY__UI     => [
                        C__PROPERTY__UI__ID     => 'C__CATG__SLA__RECOVERY_INTERVAL',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strClass' => 'input-mini'
                        ]
                    ],
                    C__PROPERTY__FORMAT => [
                        C__PROPERTY__FORMAT__UNIT => 'recovery_time_unit'
                    ]
                ]
            ),
            'recovery_time_unit'   => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'Recovery time unit',
                        C__PROPERTY__INFO__DESCRIPTION => 'Recovery time unit'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_sla_list__recovery_time_unit',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_unit_of_time',
                            'isys_unit_of_time__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__SLA__RECOVERY_INTERVAL_UNIT',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_unit_of_time',
                            'p_strClass' => 'input input-mini'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => false
                    ]
                ]
            ),
            'calendar'             => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__SLA_CALENDAR',
                        C__PROPERTY__INFO__DESCRIPTION => 'Calendar'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_sla_list__isys_calendar__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_calendar',
                            'isys_calendar__id',
                        ]
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATS__SLA_CALENDAR',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_calendar',
                            'p_strClass' => 'input-small',
                        ]
                    ]
                ]
            ),
            'description'          => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_sla_list__description'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CATG__SLA
                    ]
                ]
            )
        ];
    } // function
} // class