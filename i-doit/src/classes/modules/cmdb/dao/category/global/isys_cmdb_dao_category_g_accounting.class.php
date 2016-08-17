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
 * DAO: global category for accounting
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Bluemer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_accounting extends isys_cmdb_dao_category_global
{
    protected static $m_placeholder_counter = [];
    protected static $m_placeholder_counter_arr = [];

    // Current counter for each object type
    protected static $m_placeholder_date_data = null;

    // Array for the placeholder %COUNTER% or %COUNTER#n%
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'accounting';

    // Array with date data
    /**
     * Category entry is purgable
     *
     * @var bool
     */
    protected $m_is_purgable = true;

    /**
     * Get all possible placeholders
     *
     * @param int    $p_obj_id
     * @param int    $p_obj_type_id
     * @param string $p_obj_title
     * @param string $p_obj_sysid
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@synetics.de>
     */
    public static function get_placeholders_info_with_data($p_as_description = false, $p_obj_id = null, $p_obj_type_id = null, $p_obj_title = null, $p_obj_sysid = null)
    {
        $l_timestamp = time();
        if (self::$m_placeholder_date_data === null)
        {
            $l_date_data                          = explode('_', date('Y_y_m_d', $l_timestamp));
            self::$m_placeholder_date_data['%Y%'] = $l_date_data[0];
            self::$m_placeholder_date_data['%y%'] = $l_date_data[1];
            self::$m_placeholder_date_data['%m%'] = $l_date_data[2];
            self::$m_placeholder_date_data['%d%'] = $l_date_data[3];
        } // if
        self::$m_placeholder_date_data['%TIMESTAMP%'] = $l_timestamp;

        $l_arr = [];

        if ($p_obj_id !== null)
        {
            $l_arr['%OBJID%'] = $p_obj_id;
        } // if

        if ($p_obj_type_id !== null)
        {
            $l_arr['%OBJTYPEID%'] = $p_obj_type_id;
        } // if

        if ($p_obj_title !== null)
        {
            $l_arr['%OBJTITLE%'] = $p_obj_title;
        } // if

        if ($p_obj_sysid !== null)
        {
            $l_arr['%SYSID%'] = $p_obj_sysid;
        } // if

        $l_arr['%TIMESTAMP%'] = ($p_as_description) ? _L('LC__UNIVERSAL__PLACEHOLDER__TIMESTAMP') . ' (' . self::$m_placeholder_date_data['%TIMESTAMP%'] .
            ')' : self::$m_placeholder_date_data['%TIMESTAMP%'];
        $l_arr['%Y%']         = ($p_as_description) ? _L('LC__UNIVERSAL__PLACEHOLDER__FULL_YEAR') . ' (' . self::$m_placeholder_date_data['%Y%'] .
            ')' : self::$m_placeholder_date_data['%Y%'];
        $l_arr['%y%']         = ($p_as_description) ? _L(
                'LC__UNIVERSAL__PLACEHOLDER__YEAR'
            ) . ' (' . self::$m_placeholder_date_data['%y%'] . ')' : self::$m_placeholder_date_data['%y%'];
        $l_arr['%m%']         = ($p_as_description) ? _L(
                'LC__UNIVERSAL__PLACEHOLDER__MONTH'
            ) . ' (' . self::$m_placeholder_date_data['%m%'] . ')' : self::$m_placeholder_date_data['%m%'];
        $l_arr['%d%']         = ($p_as_description) ? _L(
                'LC__UNIVERSAL__PLACEHOLDER__DAY'
            ) . ' (' . self::$m_placeholder_date_data['%d%'] . ')' : self::$m_placeholder_date_data['%d%'];

        if ($p_as_description)
        {
            $l_arr['%COUNTER%']   = _L('LC__UNIVERSAL__PLACEHOLDER__COUNTER') . ' (42)';
            $l_arr['%COUNTER#N%'] = _L('LC__UNIVERSAL__PLACEHOLDER__COUNTER_N') . ' (' . _L('LC__UNIVERSAL__PLACEHOLDER__COUNTER_N_EXAMPLE') . ')';
        }

        return $l_arr;
    } // function

    /**
     * Checks if string has any placeholders
     *
     * @param $p_check_string
     *
     * @return bool
     */
    public static function has_placeholders($p_check_string)
    {
        $p_check_string = strtolower($p_check_string);
        if (strpos($p_check_string, '%objid%') !== false || strpos($p_check_string, '%objtypeid%') !== false || strpos($p_check_string, '%objtitle%') !== false || strpos(
                $p_check_string,
                '%sysid%'
            ) !== false || strpos($p_check_string, '%timestamp%') !== false || strpos($p_check_string, '%y%') !== false || strpos($p_check_string, '%m%') !== false || strpos(
                $p_check_string,
                '%d%'
            ) !== false || strpos($p_check_string, '%counter') !== false
        )
        {
            return true;
        } // if
        return false;
    } // function

    /**
     * Resets the member variables for the %COUNTER% placeholder.
     */
    public static function reset_placeholder_data()
    {
        //self::$m_placeholder_counter_arr = array();
        self::$m_placeholder_counter = [];
    } // function

    /**
     * Dynamic property handling for price
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_price($p_row)
    {
        if (!empty($p_row['isys_catg_accounting_list__price']) || !empty($p_row['isys_obj__id']))
        {
            if(!isset($p_row['isys_catg_accounting_list__price']))
            {
                $l_dao  = isys_cmdb_dao_category_g_accounting::instance(isys_application::instance()->database);
                $p_row['isys_catg_accounting_list__price'] = $l_dao->get_data(null, $p_row['isys_obj__id'])
                    ->get_row_value('isys_catg_accounting_list__price');
            } // if

            // Decimal seperator from the user configuration.
            $l_monetary_tmp = explode(
                " ",
                isys_locale::get_instance()
                    ->fmt_monetary($p_row['isys_catg_accounting_list__price'])
            );

            return $l_monetary_tmp[0] . ' ' . $l_monetary_tmp[1];
        } // if

        return isys_tenantsettings::get('gui.empty_value', '-');
    } // function

    /**
     * Dynamic property handling for operation expense
     *
     * @param $p_row
     *
     * @return null|string
     * @throws Exception
     * @throws isys_exception_general
     */
    public function dynamic_property_callback_operation_expense($p_row)
    {
        global $g_comp_database;

        $l_return = null;
        if (!empty($p_row['isys_catg_accounting_list__id']) || !empty($p_row['isys_obj__id']))
        {
            $l_dao  = isys_cmdb_dao_category_g_accounting::instance($g_comp_database);
            $l_data = $l_dao->get_data($p_row['isys_catg_accounting_list__id'], $p_row['isys_obj__id'])
                ->get_row();

            if ($l_data['isys_catg_accounting_list__operation_expense'] > 0)
            {
                $l_objLoc     = isys_locale::get_instance();
                // Decimal seperator from the user configuration.
                $l_monetary     = $l_objLoc->fmt_monetary($l_data['isys_catg_accounting_list__operation_expense']);
                $l_monetary_tmp = explode(" ", $l_monetary);
                $l_return       = $l_monetary_tmp[0] . ' ' . $l_monetary_tmp[1];
                if ($l_data['isys_catg_accounting_list__isys_interval__id'] > 0)
                {
                    $l_return .= ' ' . _L(
                            isys_factory_cmdb_dialog_dao::get_instance('isys_interval', $g_comp_database)
                                ->get_data($l_data['isys_catg_accounting_list__isys_interval__id'])['isys_interval__title']
                        );
                } // if
            }
        } // if

        return $l_return;
    } // function

    /**
     * Dynamic property callback to handle contact only for object list
     *
     * @param $p_row
     *
     * @return string
     * @throws isys_exception_general
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function dynamic_property_callback_contact($p_row)
    {
        global $g_comp_database;
        $l_quick_info = new isys_ajax_handler_quick_info();
        $l_strOut     = '-';

        $l_contacts = isys_cmdb_dao_category_g_accounting::instance($g_comp_database)
            ->get_purchased_at(null, $p_row);

        if (count($l_contacts) > 0)
        {
            $l_strOut = "<ul>";
            foreach ($l_contacts AS $l_cont_obj_id => $l_cont_obj_title)
            {
                $l_strOut .= '<li>' . $l_quick_info->get_quick_info($l_cont_obj_id, $l_cont_obj_title, C__LINK__OBJECT, false) . '</li>';
            } // foreach
            $l_strOut .= '</ul>';
        } // if
        return $l_strOut;
    } // function

    /**
     * Callback method for the device dialog-field.
     *
     * @global  isys_component_database $g_comp_database
     *
     * @param   isys_request            $p_request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_contact(isys_request $p_request)
    {
        global $g_comp_database;

        return isys_cmdb_dao_category_g_accounting::instance($g_comp_database)
            ->get_purchased_at($p_request);
    } // function

    /**
     * Method for calculating the guarantee status.
     *
     * @param   string  $p_acquirementdate
     * @param   integer $p_guarantee_period
     * @param   mixed   $p_guarantee_period_unit
     *
     * @return  mixed
     */
    public function calculate_guarantee_status($p_acquirementdate, $p_guarantee_period, $p_guarantee_period_unit)
    {
        if (is_numeric($p_guarantee_period) && $p_guarantee_period_unit != '')
        {
            $l_period_unit = (!is_numeric($p_guarantee_period_unit)) ? constant($p_guarantee_period_unit) : $p_guarantee_period_unit;

            switch ($l_period_unit)
            {
                case C__GUARANTEE_PERIOD_UNIT_DAYS:
                    $l_guarantee_enddate = strtotime("+{$p_guarantee_period} days", $p_acquirementdate);
                    break;

                case C__GUARANTEE_PERIOD_UNIT_WEEKS:
                    $l_guarantee_enddate = strtotime("+{$p_guarantee_period} weeks", $p_acquirementdate);
                    break;

                case C__GUARANTEE_PERIOD_UNIT_MONTH:
                    $l_guarantee_enddate = strtotime("+{$p_guarantee_period} months", $p_acquirementdate);
                    break;

                case C__GUARANTEE_PERIOD_UNIT_YEARS:
                    $l_guarantee_enddate = strtotime("+{$p_guarantee_period} years", $p_acquirementdate);
                    break;

                default:
                    $l_guarantee_enddate = 0;
                    break;
            } // switch

            if (time() < $l_guarantee_enddate)
            {
                $l_guarantee_enddate_OBJ = new DateTime();
                $l_guarantee_enddate_OBJ->setTimestamp($l_guarantee_enddate);
                $l_date_diff = (array) date_diff(new DateTime(), $l_guarantee_enddate_OBJ);

                $l_calc_result = [];

                if ($l_date_diff["y"] > 0)
                {
                    $l_calc_result[] = $l_date_diff["y"] . ' ' . ($l_date_diff["y"] == 1 ? _L("LC__UNIVERSAL__YEAR") : _L("LC__UNIVERSAL__YEARS"));
                } // if

                if ($l_date_diff["m"] > 0)
                {
                    $l_calc_result[] = $l_date_diff["m"] . ' ' . ($l_date_diff["m"] == 1 ? _L("LC__UNIVERSAL__MONTH") : _L("LC__UNIVERSAL__MONTHS"));
                } // if

                if ($l_date_diff["w"] > 0)
                {
                    $l_calc_result[] = $l_date_diff["w"] . ' ' . ($l_date_diff["w"] == 1 ? _L("LC__UNIVERSAL__WEEK") : _L("LC__UNIVERSAL__WEEKS"));
                } // if

                if ($l_date_diff["d"] > 0)
                {
                    $l_calc_result[] = $l_date_diff["d"] . ' ' . ($l_date_diff["d"] == 1 ? _L("LC__UNIVERSAL__DAY") : _L("LC__UNIVERSAL__DAYS"));
                } // if

                // Rendering a nice output!
                if (count($l_calc_result) > 1)
                {
                    $l_calc_result = implode(', ', array_slice($l_calc_result, 0, -1)) . ' ' . _L('LC__UNIVERSAL__AND') . ' ' . end($l_calc_result);
                }
                else
                {
                    $l_calc_result = current($l_calc_result);
                } // if
            }
            else
            {
                if ($p_guarantee_period > 0)
                {
                    $l_calc_result = _L("LC__UNIVERSAL__GUARANTEE_EXPIRED");
                } // if
            } // if

            return $l_calc_result;
        }
        else
        {
            return false;
        } // if
    } // function

    /**
     * Replaces all placeholders in the passed string
     *
     * @param string      $p_data_string
     * @param int|null    $p_obj_id
     * @param int|null    $p_obj_type_id
     * @param string|null $p_strTitle
     * @param string|null $p_strSYSID
     *
     * @return string
     * @author Van Quyen Hoang <qhoang@synetics.de>
     */
    public function replace_placeholders($p_data_string, $p_obj_id = null, $p_obj_type_id = null, $p_strTitle = null, $p_strSYSID = null, $p_table = 'isys_catg_accounting_list')
    {
        try
        {
            if (strpos(' ' . $p_data_string, '%COUNTER'))
            {
                // Set current counter
                if (!isset(self::$m_placeholder_counter[$p_table]))
                {
                    $l_sql = 'SELECT MAX(' . $p_table . '__id) AS cnt FROM ' . $p_table;

                    self::$m_placeholder_counter[$p_table] = (string) $this->retrieve($l_sql)
                        ->get_row_value('cnt');
                } // if

                // Set placeholders
                if (!isset(self::$m_placeholder_counter_arr[$p_table]))
                {
                    self::$m_placeholder_counter_arr[$p_table] = '';
                    preg_match_all("/\%COUNTER([\#\,\:])\d*\%|\%COUNTER\%/", $p_data_string, $l_matches);

                    if ($l_matches !== false)
                    {
                        if (count($l_matches[0]) > 0)
                        {
                            foreach ($l_matches[0] AS $l_placeholder)
                            {
                                if (strpos($l_placeholder, '#'))
                                {
                                    $l_length = substr($l_placeholder, strpos($l_placeholder, '#') + 1, -1);
                                }
                                else
                                {
                                    $l_length = 0;
                                } // if

                                self::$m_placeholder_counter_arr[$p_table][] = $l_length > 0 ? [
                                    '%COUNTER#' . $l_length . '%',
                                    $l_length
                                ] : ['%COUNTER%'];
                            } // foreach

                        } // if
                    } // if
                } // if

                // Replace placeholder %COUNTER% and %COUNTER#N% in string
                if (isset(self::$m_placeholder_counter_arr[$p_table]) && is_array(self::$m_placeholder_counter_arr[$p_table]))
                {
                    $l_counter = self::$m_placeholder_counter[$p_table]++;
                    array_map(
                        function ($p_placeholder) use (&$p_data_string, $l_counter)
                        {
                            $l_zeros = '';

                            if (is_array($p_placeholder))
                            {
                                $l_replace = isset($p_placeholder[0]) ? $p_placeholder[0] : '';

                                if (isset($p_placeholder[1]) && is_numeric($p_placeholder[1]))
                                {
                                    $l_placeholder_cnt = (int) $p_placeholder[1];
                                    $l_cnt             = strlen($l_counter);
                                    if ($l_cnt < $l_placeholder_cnt)
                                    {
                                        $l_zeros = str_repeat('0', $l_placeholder_cnt - $l_cnt);
                                    }
                                }

                                if ($l_replace !== '')
                                {
                                    $p_data_string = str_replace($l_replace, $l_zeros . $l_counter, $p_data_string);
                                }
                            }
                        },
                        self::$m_placeholder_counter_arr[$p_table]
                    );
                } // if
            } // if

            // Return string with replaced placeholders
            return strtr($p_data_string, self::get_placeholders_info_with_data(false, $p_obj_id, $p_obj_type_id, $p_strTitle, $p_strSYSID));
        }
        catch (Exception $e)
        {
            throw new Exception('Placeholders in ' . $p_data_string . ' could not be replaced. With message: ' . $e->getMessage());
        }
    } // function

    /**
     * Dynamic property price
     *
     * @return array
     */
    protected function dynamic_properties()
    {
        return [
            '_price'             => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__GLOBAL_PRICE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Cash value / Price'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_accounting_list__price'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_callback_price'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST       => false,
                    C__PROPERTY__PROVIDES__REPORT     => true,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__EXPORT     => false,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false
                ]
            ],
            '_operation_expense' => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__ACCOUNTING__OPERATION_EXPENSE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Operational expense'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_accounting_list__id'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_callback_operation_expense'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST       => false,
                    C__PROPERTY__PROVIDES__REPORT     => true,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__EXPORT     => false,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false
                ]
            ],
            '_contact'           => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__GLOBAL_PURCHASED_AT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Purchased at'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_callback_contact'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST       => true,
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__EXPORT     => false,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false
                ]
            ]
        ];
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
            'inventory_no'               => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__ACCOUNTING_INVENTORY_NO',
                        C__PROPERTY__INFO__DESCRIPTION => 'Inventory number'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_accounting_list__inventory_no'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__ACCOUNTING_INVENTORY_NO'
                    ]
                ]
            ),
            'account'                    => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__ACCOUNTING_ACCOUNT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Account'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_accounting_list__isys_account__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_account',
                            'isys_account__id'
                        ]
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CATG__ACCOUNTING__ACCOUNT',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_account'
                        ]
                    ]
                ]
            ),
            'acquirementdate'            => array_replace_recursive(
                isys_cmdb_dao_category_pattern::date(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__GLOBAL_AQUIRE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Acquirement date'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_accounting_list__acquirementdate'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__ACCOUNTING_ACQUIRE'
                    ]
                ]
            ),
            'contact'                    => array_replace_recursive(
                isys_cmdb_dao_category_pattern::object_browser(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__GLOBAL_PURCHASED_AT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Purchased at'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_accounting_list__isys_contact__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_contact',
                            'isys_contact__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__PURCHASE_CONTACT',
                        C__PROPERTY__UI__PARAMS => [
                            'multiselection'  => true,
                            'catFilter'       => 'C__CATS__PERSON;C__CATS__PERSON_GROUP;C__CATS__ORGANIZATION',
                            'p_strSelectedID' => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_accounting',
                                    'callback_property_contact'
                                ]
                            )
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => true,
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'contact'
                        ]
                    ]
                ]
            ),
            'price'                      => array_replace_recursive(
                isys_cmdb_dao_category_pattern::money(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__GLOBAL_PRICE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Cash value / Price'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_accounting_list__price'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATG__ACCOUNTING_PRICE'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => true,
                        C__PROPERTY__PROVIDES__REPORT => true
                    ]
                ]
            ),
            'operation_expense'          => array_replace_recursive(
                isys_cmdb_dao_category_pattern::money(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__ACCOUNTING__OPERATION_EXPENSE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Operational expense'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_accounting_list__operation_expense'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CATG__ACCOUNTING__OPERATION_EXPENSE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strClass' => 'input-dual-large'
                        ]
                    ],
                ]
            ),
            'operation_expense_interval' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__ACCOUNTING__OPERATION_EXPENSE__UNIT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Interval unit of expense'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_accounting_list__isys_interval__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_interval',
                            'isys_interval__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__ACCOUNTING__OPERATION_EXPENSE_INTERVAL',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable'        => 'isys_interval',
                            'p_strClass'        => 'input-dual-small',
                            'p_bInfoIconSpacer' => 0
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__REPORT => false
                    ]
                ]
            ),
            'cost_unit'                  => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__ACCOUNTING_COST_UNIT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Cost unit'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_accounting_list__isys_catg_accounting_cost_unit__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_accounting_cost_unit',
                            'isys_catg_accounting_cost_unit__id'
                        ]
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CATG__ACCOUNTING_COST_UNIT',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_catg_accounting_cost_unit'
                        ]
                    ]
                ]
            ),
            'delivery_note_no'           => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__ACCOUNTING_DELIVERY_NOTE_NO',
                        C__PROPERTY__INFO__DESCRIPTION => 'Delivery note no.'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_accounting_list__delivery_note_no'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__ACCOUNTING_DELIVERY_NOTE_NO'
                    ]
                ]
            ),
            'procurement'                => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__ACCOUNTING_PROCUREMENT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Procurement'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_accounting_list__isys_catg_accounting_procurement__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_accounting_procurement',
                            'isys_catg_accounting_procurement__id'
                        ]
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CATG__ACCOUNTING_PROCUREMENT',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_catg_accounting_procurement'
                        ]
                    ]
                ]
            ),
            'delivery_date'              => array_replace_recursive(
                isys_cmdb_dao_category_pattern::date(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__ACCOUNTING_DELIVERY_DATE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Delivery date'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_accounting_list__delivery_date'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CATG__ACCOUNTING_DELIVERY_DATE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strPopupType' => 'calendar',
                            'p_bTime'        => 0
                        ]
                    ]
                ]
            ),
            'invoice_no'                 => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__GLOBAL_INVOICE_NO',
                        C__PROPERTY__INFO__DESCRIPTION => 'Invoice no.'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_accounting_list__invoice_no'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__ACCOUNTING_INVOICE_NO'
                    ]
                ]
            ),
            'order_no'                   => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__GLOBAL_ORDER_NO',
                        C__PROPERTY__INFO__DESCRIPTION => 'Order no.'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_accounting_list__order_no'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__ACCOUNTING_ORDER_NO'
                    ]
                ]
            ),
            'guarantee_period'           => array_replace_recursive(
                isys_cmdb_dao_category_pattern::int(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__GLOBAL_GUARANTEE_PERIOD',
                        C__PROPERTY__INFO__DESCRIPTION => 'Period of warranty'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_accounting_list__guarantee_period'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__ACCOUNTING_GUARANTEE_PERIOD',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strClass' => 'input-dual-large'
                        ]
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'timeperiod',
                            [null],
                        ],
                        C__PROPERTY__FORMAT__UNIT     => 'guarantee_period_unit'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ]
                ]
            ),
            'guarantee_period_unit'      => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__GLOBAL_GUARANTEE_PERIOD_UNIT',
                        C__PROPERTY__INFO__DESCRIPTION => 'guarantee period unit field'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_accounting_list__isys_guarantee_period_unit__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_guarantee_period_unit',
                            'isys_guarantee_period_unit__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__ACCOUNTING_GUARANTEE_PERIOD_UNIT',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable'        => 'isys_guarantee_period_unit',
                            'p_strClass'        => 'input-dual-small',
                            'p_bInfoIconSpacer' => 0
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__REPORT => false
                    ]
                ]
            ),
            'guarantee_period_status'    => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__GLOBAL_GUARANTEE_STATUS',
                        C__PROPERTY__INFO__DESCRIPTION => 'Order no.'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_accounting_list__id'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH     => false,
                        C__PROPERTY__PROVIDES__REPORT     => false,
                        C__PROPERTY__PROVIDES__VALIDATION => false,
                        C__PROPERTY__PROVIDES__IMPORT     => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                        C__PROPERTY__PROVIDES__LIST       => false,
                        C__PROPERTY__PROVIDES__EXPORT     => true
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'get_guarantee_status'
                        ]
                    ]
                ]
            ),
            'description'                => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_accounting_list__description'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CATG__ACCOUNTING
                    ]
                ]
            )
        ];
    } // function

    /**
     * Private method which handles the property contact for object list and report
     *
     * @param isys_request|null $p_request
     * @param null              $p_row
     *
     * @return array|null
     * @throws isys_exception_general
     */
    private function get_purchased_at(isys_request $p_request = null, $p_row = null)
    {
        global $g_comp_database;
        $l_return = [];

        $l_request   = false;
        $l_object_id = null;
        if (is_object($p_request))
        {
            $l_object_id = $p_request->get_object_id();
            $l_request   = true;
        }
        elseif ($p_row !== null)
        {
            $l_object_id = $p_row['isys_obj__id'];
        } // if

        if ($l_object_id === null) return null;

        $l_accounting_data = $this->get_data(null, $l_object_id)
            ->get_row();

        /**
         * IDE Typehinting.
         *
         * @var  $l_person_dao  isys_cmdb_dao_category_g_contact
         */
        $l_person_res = isys_cmdb_dao_category_g_contact::instance($g_comp_database)
            ->get_assigned_contacts_by_relation_id($l_accounting_data["isys_catg_accounting_list__isys_contact__id"]);

        while ($l_row = $l_person_res->get_row())
        {
            if ($l_request)
            {
                $l_return[] = $l_row['isys_obj__id'];
            }
            else
            {
                $l_return[$l_row['isys_obj__id']] = $l_row['isys_obj__title'];
            } // if
        } // while

        return $l_return;
    } // function
} // class