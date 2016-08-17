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
 * DAO: global category for SIM cards
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_sim_card extends isys_cmdb_dao_category_global
{

    /**
     * Category's name. Will be used for the identifier, constant, main table,
     * and many more.
     *
     * @var string
     */
    protected $m_category = 'sim_card';

    /**
     * Category entry is purgable
     *
     * @var bool
     */
    protected $m_is_purgable = true;

    /**
     * Dynamic property handling for retrieving the object ID.
     *
     * @param   array $p_row
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function dynamic_property_callback_assigned_mobile(array $p_row)
    {
        global $g_comp_database;

        $l_return    = '';
        $l_quicklink = new isys_ajax_handler_quick_info();

        $l_dao = isys_cmdb_dao_category_g_sim_card::instance($g_comp_database);

        $l_row = $l_dao->get_data(null, $p_row['isys_obj__id'])
            ->get_row();

        if ($l_row !== false && $l_row['isys_catg_assigned_cards_list__isys_obj__id'] > 0)
        {
            $l_cellphone_row = $l_dao->get_object_by_id($l_row['isys_catg_assigned_cards_list__isys_obj__id'])
                ->get_row();

            $l_return = $l_quicklink->get_quick_info(
                $l_cellphone_row['isys_obj__id'],
                _L($l_cellphone_row['isys_obj_type__title']) . ' &raquo; ' . $l_cellphone_row['isys_obj__title'],
                C__LINK__OBJECT
            );
        } // if

        return $l_return;
    }

    /**
     * Method for saving an element.
     *
     * @param   integer $p_cat_level
     * @param   integer & $p_intOldRecStatus
     *
     * @author  Dennis Stücken <dstuecken@synetics.de>
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        $l_catdata = $this->get_data(null, $_GET[C__CMDB__GET__OBJECT])
            ->__to_array();

        $l_list_id = null;

        if (!empty($l_catdata))
        {
            $p_intOldRecStatus = $l_catdata["isys_catg_sim_card_list__status"];
            $l_list_id         = $l_catdata["isys_catg_sim_card_list__id"];
        } // if

        if (empty($l_list_id))
        {
            $l_list_id         = $this->create_connector("isys_catg_sim_card_list", $_GET[C__CMDB__GET__OBJECT]);
            $p_intOldRecStatus = C__RECORD_STATUS__NORMAL;
        } // if

        $_POST['C__CATS__CP_CONTRACT__START_DATE__HIDDEN'] = isys_glob_mkdate($_POST['C__CATS__CP_CONTRACT__START_DATE__HIDDEN'], "Y-m-d");
        $_POST['C__CATS__CP_CONTRACT__END_DATE__HIDDEN']   = isys_glob_mkdate($_POST['C__CATS__CP_CONTRACT__END_DATE__HIDDEN'], "Y-m-d");
        $_POST['C__CATS__CP_CONTRACT__THRESHOLD__HIDDEN']  = isys_glob_mkdate($_POST['C__CATS__CP_CONTRACT__THRESHOLD__HIDDEN'], "Y-m-d");

        $l_bRet = $this->save(
            $l_list_id,
            C__RECORD_STATUS__NORMAL,
            $_POST['C__CATS__CP_CONTRACT__TYPE'],
            $_POST['C__CATS__CP_CONTRACT__CARD_NUMBER'],
            $_POST['C__CATS__CP_CONTRACT__SERIAL_NUMBER'],
            $_POST['C__CATS__CP_CONTRACT__PHONE_NUMBER'],
            $_POST['C__CATS__CP_CONTRACT__CLIENT_NUMBER'],
            $_POST['C__CATS__CP_CONTRACT__START_DATE__HIDDEN'],
            $_POST['C__CATS__CP_CONTRACT__END_DATE__HIDDEN'],
            $_POST['C__CATS__CP_CONTRACT__PIN'],
            $_POST['C__CATS__CP_CONTRACT__PIN2'],
            $_POST['C__CATS__CP_CONTRACT__PUK'],
            $_POST['C__CATS__CP_CONTRACT__PUK2'],
            $_POST['C__CMDB__CATG__SIM_CARD__TWINCARD'],
            $_POST['C__CATS__CP_CONTRACT__TC_CARD_NUMBER'],
            $_POST['C__CATS__CP_CONTRACT__TC_SERIAL_NUMBER'],
            $_POST['C__CATS__CP_CONTRACT__TC_PHONE_NUMBER'],
            $_POST['C__CATS__CP_CONTRACT__TC_PIN'],
            $_POST['C__CATS__CP_CONTRACT__TC_PIN2'],
            $_POST['C__CATS__CP_CONTRACT__TC_PUK'],
            $_POST['C__CATS__CP_CONTRACT__TC_PUK2'],
            $_POST['C__CATS__CP_CONTRACT__THRESHOLD__HIDDEN'],
            $_POST['C__CMDB__CAT__COMMENTARY_' . $this->get_category_type() . $this->get_category_id()],
            $_POST['C__CATS__CP_CONTRACT__TC_DESCRIPTION'],
            $_POST['C__CATS__CP_CONTRACT__NETWORK_PROVIDER'],
            $_POST['C__CATS__CP_CONTRACT__TELEPHONE_RATE'],
            $_POST['C__CATS__SIM_CARD__ASSIGNED_MOBILE_PHONE__HIDDEN']
        );

        $this->m_strLogbookSQL = $this->get_last_query();

        return $l_bRet == true ? $l_list_id : -1;
    } // function

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level
     *
     * @param int     $p_cat_level
     * @param int     $p_newRecStatus
     * @param int     $p_typeID
     * @param String  $p_cardNo
     * @param String  $p_serial
     * @param String  $p_phoneNo
     * @param String  $p_clientNo
     * @param date    $p_startDate
     * @param date    $p_end_date
     * @param String  $p_pin
     * @param String  $p_pin2
     * @param String  $p_puk
     * @param String  $p_puk2
     * @param boolean $p_twincard
     * @param String  $p_tcCardNo
     * @param String  $p_tcSerial
     * @param String  $p_tcPhoneNo
     * @param String  $p_tcPin
     * @param String  $p_tcPin2
     * @param String  $p_tcPuk
     * @param String  $p_tcPuk2
     * @param date    $p_thresholdDate
     * @param String  $p_description
     *
     * @return boolean true, if transaction executed successfully, else false
     * @author Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function save($p_cat_level, $p_newRecStatus = C__RECORD_STATUS__NORMAL, $p_typeID, $p_cardNo, $p_serial, $p_phoneNo, $p_clientNo, $p_startDate, $p_end_date, $p_pin, $p_pin2, $p_puk, $p_puk2, $p_twincard, $p_tcCardNo, $p_tcSerial, $p_tcPhoneNo, $p_tcPin, $p_tcPin2, $p_tcPuk, $p_tcPuk2, $p_thresholdDate, $p_description, $p_optional_info, $p_network_provider, $p_telephone_rate, $p_connected_obj)
    {
        $l_strSql = "UPDATE isys_catg_sim_card_list SET " . "isys_catg_sim_card_list__description = " . $this->convert_sql_text(
                $p_description
            ) . ", " . "isys_catg_sim_card_list__isys_cp_contract_type__id = " . $this->convert_sql_id(
                $p_typeID
            ) . ", " . "isys_catg_sim_card_list__card_number  = " . $this->convert_sql_text(
                $p_cardNo
            ) . ", " . "isys_catg_sim_card_list__serial_number = " . $this->convert_sql_text(
                $p_serial
            ) . ", " . "isys_catg_sim_card_list__phone_number = " . $this->convert_sql_text(
                $p_phoneNo
            ) . ", " . "isys_catg_sim_card_list__client_number = " . $this->convert_sql_text(
                $p_clientNo
            ) . ", " . "isys_catg_sim_card_list__start_date = " . $this->convert_sql_datetime(
                $p_startDate
            ) . ", " . "isys_catg_sim_card_list__end_date = " . $this->convert_sql_datetime($p_end_date) . ", " . "isys_catg_sim_card_list__pin = " . $this->convert_sql_text(
                $p_pin
            ) . ", " . "isys_catg_sim_card_list__pin2 = " . $this->convert_sql_text($p_pin2) . ", " . "isys_catg_sim_card_list__puk = " . $this->convert_sql_text(
                $p_puk
            ) . ", " . "isys_catg_sim_card_list__puk2 = " . $this->convert_sql_text($p_puk2) . ", " . "isys_catg_sim_card_list__twincard = " . $this->convert_sql_id(
                $p_twincard
            ) . ", " . "isys_catg_sim_card_list__tc_card_number = " . $this->convert_sql_text(
                $p_tcCardNo
            ) . ", " . "isys_catg_sim_card_list__tc_serial_number = " . $this->convert_sql_text(
                $p_tcSerial
            ) . ", " . "isys_catg_sim_card_list__tc_phone_number = " . $this->convert_sql_text(
                $p_tcPhoneNo
            ) . ", " . "isys_catg_sim_card_list__tc_pin = " . $this->convert_sql_text($p_tcPin) . ", " . "isys_catg_sim_card_list__tc_pin2 = " . $this->convert_sql_text(
                $p_tcPin2
            ) . ", " . "isys_catg_sim_card_list__tc_puk = " . $this->convert_sql_text($p_tcPuk) . ", " . "isys_catg_sim_card_list__tc_puk2 = " . $this->convert_sql_text(
                $p_tcPuk2
            ) . ", " . "isys_catg_sim_card_list__optional_info = " . $this->convert_sql_text(
                $p_optional_info
            ) . ", " . "isys_catg_sim_card_list__isys_network_provider__id = " . $this->convert_sql_id(
                $p_network_provider
            ) . ", " . "isys_catg_sim_card_list__isys_telephone_rate__id = " . $this->convert_sql_id(
                $p_telephone_rate
            ) . ", " . "isys_catg_sim_card_list__threshold_date = " . $this->convert_sql_datetime($p_thresholdDate) . ", ";

        $l_strSql .= "isys_catg_sim_card_list__status = " . $this->convert_sql_int($p_newRecStatus) . " " . "WHERE isys_catg_sim_card_list__id = " . $this->convert_sql_id(
                $p_cat_level
            ) . ";";

        if ($this->update($l_strSql) && $this->apply_update())
        {
            $l_dao     = new isys_cmdb_dao_category_g_assigned_cards($this->get_database_component());
            $l_catdata = $this->get_data($p_cat_level)
                ->get_row();

            if ($l_catdata['isys_catg_sim_card_list__isys_obj__id'] > 0)
            {
                if ($p_connected_obj > 0)
                {
                    $l_dao->remove_component(null, $l_catdata['isys_catg_sim_card_list__isys_obj__id']);
                    $l_dao->add_component($p_connected_obj, $l_catdata['isys_catg_sim_card_list__isys_obj__id']);
                }
                else
                {
                    $l_dao->remove_component(null, $l_catdata['isys_catg_sim_card_list__isys_obj__id']);
                } // if
            }

            return true;
        }
        else
        {
            return false;
        } // if
    } // function

    /**
     * Executes the query to create the category entry.
     *
     * @param   integer $p_objID
     * @param   integer $p_newRecStatus
     * @param   integer $p_typeID
     * @param   string  $p_cardNo
     * @param   string  $p_serial
     * @param   string  $p_phoneNo
     * @param   string  $p_clientNo
     * @param   string  $p_startDate
     * @param   string  $p_end_date
     * @param   string  $p_pin
     * @param   string  $p_pin2
     * @param   string  $p_puk
     * @param   string  $p_puk2
     * @param   boolean $p_twincard
     * @param   string  $p_tcCardNo
     * @param   string  $p_tcSerial
     * @param   string  $p_tcPhoneNo
     * @param   string  $p_tcPin
     * @param   string  $p_tcPin2
     * @param   string  $p_tcPuk
     * @param   string  $p_tcPuk2
     * @param   string  $p_thresholdDate
     * @param   string  $p_description
     *
     * @return  integer  The newly created ID or false.
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function create($p_objID, $p_newRecStatus = C__RECORD_STATUS__NORMAL, $p_typeID = null, $p_cardNo = null, $p_serial = null, $p_phoneNo = null, $p_clientNo = null, $p_startDate = null, $p_end_date = null, $p_pin = null, $p_pin2 = null, $p_puk = null, $p_puk2 = null, $p_twincard = null, $p_tcCardNo = null, $p_tcSerial = null, $p_tcPhoneNo = null, $p_tcPin = null, $p_tcPin2 = null, $p_tcPuk = null, $p_tcPuk2 = null, $p_thresholdDate = null, $p_description = null, $p_optional_info = null, $p_network_provider = null, $p_telephone_rate = null, $p_connected_obj = null)
    {

        $l_strSql = "INSERT IGNORE INTO isys_catg_sim_card_list SET " . "isys_catg_sim_card_list__description = " . $this->convert_sql_text(
                $p_description
            ) . ", " . "isys_catg_sim_card_list__isys_cp_contract_type__id = " . $this->convert_sql_id(
                $p_typeID
            ) . ", " . "isys_catg_sim_card_list__card_number  = " . $this->convert_sql_text(
                $p_cardNo
            ) . ", " . "isys_catg_sim_card_list__serial_number = " . $this->convert_sql_text(
                $p_serial
            ) . ", " . "isys_catg_sim_card_list__phone_number = " . $this->convert_sql_text(
                $p_phoneNo
            ) . ", " . "isys_catg_sim_card_list__client_number = " . $this->convert_sql_text(
                $p_clientNo
            ) . ", " . "isys_catg_sim_card_list__start_date = " . $this->convert_sql_datetime(
                $p_startDate
            ) . ", " . "isys_catg_sim_card_list__end_date = " . $this->convert_sql_datetime($p_end_date) . ", " . "isys_catg_sim_card_list__pin = " . $this->convert_sql_text(
                $p_pin
            ) . ", " . "isys_catg_sim_card_list__pin2 = " . $this->convert_sql_text($p_pin2) . ", " . "isys_catg_sim_card_list__puk = " . $this->convert_sql_text(
                $p_puk
            ) . ", " . "isys_catg_sim_card_list__puk2 = " . $this->convert_sql_text($p_puk2) . ", " . "isys_catg_sim_card_list__twincard = " . $this->convert_sql_id(
                $p_twincard
            ) . ", " . "isys_catg_sim_card_list__tc_card_number = " . $this->convert_sql_text(
                $p_tcCardNo
            ) . ", " . "isys_catg_sim_card_list__tc_serial_number = " . $this->convert_sql_text(
                $p_tcSerial
            ) . ", " . "isys_catg_sim_card_list__tc_phone_number = " . $this->convert_sql_text(
                $p_tcPhoneNo
            ) . ", " . "isys_catg_sim_card_list__tc_pin = " . $this->convert_sql_text($p_tcPin) . ", " . "isys_catg_sim_card_list__tc_pin2 = " . $this->convert_sql_text(
                $p_tcPin2
            ) . ", " . "isys_catg_sim_card_list__tc_puk = " . $this->convert_sql_text($p_tcPuk) . ", " . "isys_catg_sim_card_list__tc_puk2 = " . $this->convert_sql_text(
                $p_tcPuk2
            ) . ", " . "isys_catg_sim_card_list__optional_info = " . $this->convert_sql_text(
                $p_optional_info
            ) . ", " . "isys_catg_sim_card_list__isys_network_provider__id = " . $this->convert_sql_id(
                $p_network_provider
            ) . ", " . "isys_catg_sim_card_list__isys_telephone_rate__id = " . $this->convert_sql_id($p_telephone_rate) . ", ";

        if ($p_thresholdDate)
        {
            $l_strSql .= "isys_catg_sim_card_list__threshold_date = " . $this->convert_sql_datetime($p_thresholdDate) . ", ";
        }

        $l_strSql .= "isys_catg_sim_card_list__isys_obj__id = " . $this->convert_sql_id($p_objID) . ", " . "isys_catg_sim_card_list__status = " . $this->convert_sql_int(
                $p_newRecStatus
            );

        if ($this->update($l_strSql) && $this->apply_update())
        {
            $l_last_id = $this->get_last_insert_id();

            if ($p_connected_obj > 0)
            {
                $l_dao = new isys_cmdb_dao_category_g_assigned_cards($this->get_database_component());
                $l_dao->add_component($p_connected_obj, $p_objID);
            } // if

            return $l_last_id;
        }
        else
        {
            return false;
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
        return [
            '_assigned_mobile' => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__KRYPTO_CARD__ASSIGNED_MOBILE_PHONE',
                    C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATS__KRYPTO_CARD__ASSIGNED_MOBILE_PHONE'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_callback_assigned_mobile'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST => true
                ]
            ]
        ];
    } // function

    /**
     * Return Category Data.
     *
     * @param   integer $p_catg_list_id
     * @param   mixed   $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $p_condition .= $this->prepare_filter($p_filter);

        $l_sql = "SELECT * FROM isys_catg_sim_card_list " . "LEFT OUTER JOIN isys_cp_contract_type " . "ON isys_catg_sim_card_list__isys_cp_contract_type__id = isys_cp_contract_type__id " . "LEFT OUTER JOIN isys_network_provider " . "ON isys_catg_sim_card_list__isys_network_provider__id = isys_network_provider__id " . "LEFT OUTER JOIN isys_telephone_rate " . "ON isys_catg_sim_card_list__isys_telephone_rate__id = isys_telephone_rate__id " . "LEFT OUTER JOIN isys_catg_assigned_cards_list " . "ON isys_catg_assigned_cards_list__isys_obj__id__card = isys_catg_sim_card_list__isys_obj__id " . "INNER JOIN isys_obj " . "ON isys_obj__id = isys_catg_sim_card_list__isys_obj__id " . "WHERE TRUE " . $p_condition . " ";

        if ($p_obj_id !== null)
        {
            $l_sql .= $this->get_object_condition($p_obj_id);
        } // if

        if ($p_catg_list_id !== null)
        {
            $l_sql .= " AND (isys_catg_sim_card_list__id = " . $this->convert_sql_id($p_catg_list_id) . ")";
        } // if

        if ($p_status !== null)
        {
            $l_sql .= " AND (isys_catg_sim_card_list__status = " . $this->convert_sql_int($p_status) . ")";
        } // if

        return $this->retrieve($l_sql . ";");
    } // function

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'type'             => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__TYPE',
                        C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATG__TYPE'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_sim_card_list__isys_cp_contract_type__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_cp_contract_type',
                            'isys_cp_contract_type__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATS__CP_CONTRACT__TYPE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_cp_contract_type'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'dialog'
                        ]
                    ]
                ]
            ),
            'assigned_mobile'  => array_replace_recursive(
                isys_cmdb_dao_category_pattern::object_browser(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__SIM_CARD__ASSIGNED_MOBILE_PHONE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Assigned mobile phone'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD       => 'isys_catg_assigned_cards_list__isys_obj__id',
                        C__PROPERTY__DATA__TABLE_ALIAS => 'isys_catg_assigned_cards_list',
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATS__KRYPTO_CARD__ASSIGNED_MOBILE_PHONE',
                        C__PROPERTY__UI__PARAMS => [
                            isys_popup_browser_object_ng::C__CAT_FILTER => 'C__CATG__ASSIGNED_CARDS'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'object'
                        ]
                    ]
                ]
            ),
            'network_provider' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS_CP_CONTRACT__NETWORK_PROVIDER',
                        C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATS_CP_CONTRACT__NETWORK_PROVIDER'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_sim_card_list__isys_network_provider__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_network_provider',
                            'isys_network_provider__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATS__CP_CONTRACT__NETWORK_PROVIDER',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_network_provider'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'dialog_plus'
                        ]
                    ]
                ]
            ),
            'telephone_rate'   => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS_CP_CONTRACT__TELEPHONE_RATE',
                        C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATS_CP_CONTRACT__TELEPHONE_RATE'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_sim_card_list__isys_telephone_rate__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_telephone_rate',
                            'isys_telephone_rate__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATS__CP_CONTRACT__TELEPHONE_RATE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_telephone_rate'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'dialog_plus'
                        ]
                    ]
                ]
            ),
            'start'            => array_replace_recursive(
                isys_cmdb_dao_category_pattern::date(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS_CP_CONTRACT__START_DATE',
                        C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATS_CP_CONTRACT__START_DATE'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_sim_card_list__start_date'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATS__CP_CONTRACT__START_DATE'
                    ]
                ]
            ),
            'end'              => array_replace_recursive(
                isys_cmdb_dao_category_pattern::date(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS_CP_CONTRACT__END_DATE',
                        C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATS_CP_CONTRACT__END_DATE'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_sim_card_list__end_date'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATS__CP_CONTRACT__END_DATE'
                    ]
                ]
            ),
            'threshold_date'   => array_replace_recursive(
                isys_cmdb_dao_category_pattern::date(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS_CP_CONTRACT__THRESHOLD',
                        C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATS_CP_CONTRACT__THRESHOLD'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_sim_card_list__threshold_date'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATS__CP_CONTRACT__THRESHOLD'
                    ]
                ]
            ),
            'card_no'          => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS_CP_CONTRACT__CARD_NUMBER',
                        C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATS_CP_CONTRACT__CARD_NUMBER'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_sim_card_list__card_number'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATS__CP_CONTRACT__CARD_NUMBER'
                    ]
                ]
            ),
            'phone_no'         => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS_CP_CONTRACT__PHONE_NUMBER',
                        C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATS_CP_CONTRACT__PHONE_NUMBER'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_sim_card_list__phone_number'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATS__CP_CONTRACT__PHONE_NUMBER'
                    ]
                ]
            ),
            'client_no'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS_CP_CONTRACT__CLIENT_NUMBER',
                        C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATS_CP_CONTRACT__CLIENT_NUMBER'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_sim_card_list__client_number'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATS__CP_CONTRACT__CLIENT_NUMBER'
                    ]
                ]
            ),
            'pin'              => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS_CP_CONTRACT__PIN',
                        C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATS_CP_CONTRACT__PIN'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_sim_card_list__pin'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATS__CP_CONTRACT__PIN'
                    ]
                ]
            ),
            'pin2'             => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS_CP_CONTRACT__PIN2',
                        C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATS_CP_CONTRACT__PIN2'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_sim_card_list__pin2'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATS__CP_CONTRACT__PIN2'
                    ]
                ]
            ),
            'puk'              => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS_CP_CONTRACT__PUK',
                        C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATS_CP_CONTRACT__PUK'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_sim_card_list__puk'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATS__CP_CONTRACT__PUK'
                    ]
                ]
            ),
            'puk2'             => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS_CP_CONTRACT__PUK2',
                        C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATS_CP_CONTRACT__PUK2'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_sim_card_list__puk2'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATS__CP_CONTRACT__PUK2'
                    ]
                ]
            ),
            'serial'           => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__SERIAL',
                        C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATG__SERIAL'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_sim_card_list__serial_number'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATS__CP_CONTRACT__SERIAL_NUMBER'
                    ]
                ]
            ),
            'twincard'         => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS_CP_CONTRACT__TWINCARD',
                        C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATS_CP_CONTRACT__TWINCARD'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_sim_card_list__twincard'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID      => 'C__CMDB__CATG__SIM_CARD__TWINCARD',
                        C__PROPERTY__UI__PARAMS  => [
                            'p_arData'     => serialize(get_smarty_arr_YES_NO()),
                            'p_bDbFieldNN' => 1
                        ],
                        C__PROPERTY__UI__DEFAULT => '0'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'get_yes_or_no'
                        ]
                    ]
                ]
            ),
            'tc_card_no'       => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => _L('LC__CMDB__CATS_CP_CONTRACT__CARD_NUMBER') . ' (' . _L('LC__CMDB__CATS_CP_CONTRACT__TWINCARD') . ')',
                        C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATS_CP_CONTRACT__CARD_NUMBER'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_sim_card_list__tc_card_number'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATS__CP_CONTRACT__TC_CARD_NUMBER'
                    ]
                ]
            ),
            'tc_phone_no'      => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => _L('LC__CMDB__CATS_CP_CONTRACT__PHONE_NUMBER') . ' (' . _L('LC__CMDB__CATS_CP_CONTRACT__TWINCARD') . ')',
                        C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATS_CP_CONTRACT__PHONE_NUMBER'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_sim_card_list__tc_phone_number'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATS__CP_CONTRACT__TC_PHONE_NUMBER'
                    ]
                ]
            ),
            'tc_pin'           => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => _L('LC__CMDB__CATS_CP_CONTRACT__PIN') . ' (' . _L('LC__CMDB__CATS_CP_CONTRACT__TWINCARD') . ')',
                        C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATS_CP_CONTRACT__PIN'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_sim_card_list__tc_pin'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATS__CP_CONTRACT__TC_PIN'
                    ]
                ]
            ),
            'tc_pin2'          => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => _L('LC__CMDB__CATS_CP_CONTRACT__PIN2') . ' (' . _L('LC__CMDB__CATS_CP_CONTRACT__TWINCARD') . ')',
                        C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATS_CP_CONTRACT__PIN2'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_sim_card_list__tc_pin2'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATS__CP_CONTRACT__TC_PIN2'
                    ]
                ]
            ),
            'tc_puk'           => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => _L('LC__CMDB__CATS_CP_CONTRACT__PUK') . ' (' . _L('LC__CMDB__CATS_CP_CONTRACT__TWINCARD') . ')',
                        C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATS_CP_CONTRACT__PUK'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_sim_card_list__tc_puk'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATS__CP_CONTRACT__TC_PUK'
                    ]
                ]
            ),
            'tc_puk2'          => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => _L('LC__CMDB__CATS_CP_CONTRACT__PUK2') . ' (' . _L('LC__CMDB__CATS_CP_CONTRACT__TWINCARD') . ')',
                        C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATS_CP_CONTRACT__PUK2'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_sim_card_list__tc_puk2'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATS__CP_CONTRACT__TC_PUK2'
                    ]
                ]
            ),
            'tc_serial_no'     => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => _L('LC__CMDB__CATG__SERIAL') . ' (' . _L('LC__CMDB__CATS_CP_CONTRACT__TWINCARD') . ')',
                        C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATG__SERIAL'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_sim_card_list__tc_serial_number'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATS__CP_CONTRACT__TC_SERIAL_NUMBER'
                    ]
                ]
            ),
            'optional_info'    => array_replace_recursive(
                isys_cmdb_dao_category_pattern::textarea(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => _L('LC__CMDB__CATS_CP_CONTRACT__OPTIONAL_INFO') . ' (' . _L('LC__CMDB__CATS_CP_CONTRACT__TWINCARD') . ')',
                        C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATS_CP_CONTRACT__OPTIONAL_INFO'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_sim_card_list__optional_info'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATS__CP_CONTRACT__TC_DESCRIPTION'
                    ]
                ]
            ),
            'description'      => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__LOGBOOK__DESCRIPTION'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_sim_card_list__description'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CATG__SIM_CARD
                    ]
                ]
            )
        ];
    } // function

    public function rank_record($p_object_id, $p_direction, $p_table, $p_checkMethod = null, $p_purge = false)
    {
        if ($p_purge)
        {
            $l_dao_relation = isys_cmdb_dao_category_g_relation::instance($this->get_database_component());

            $l_sql         = 'SELECT  isys_catg_assigned_cards_list__isys_catg_relation_list__id FROM isys_catg_assigned_cards_list ' . 'INNER JOIN  isys_catg_sim_card_list ON isys_catg_assigned_cards_list__isys_obj__id__card =  isys_catg_sim_card_list__isys_obj__id ' . 'WHERE isys_catg_sim_card_list__id = ' . $this->convert_sql_id(
                    $p_object_id
                );
            $l_relation_id = $this->retrieve($l_sql)
                ->get_row_value('isys_catg_assigned_cards_list__isys_catg_relation_list__id');

            /**
             * Delete relation
             */
            if ($l_relation_id > 0)
            {
                $l_dao_relation->delete_relation($l_relation_id);
            }
        }

        return parent::rank_record($p_object_id, $p_direction, $p_table, $p_checkMethod, $p_purge);
    } // function

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param   array   $p_category_data Values of category data to be saved.
     * @param   integer $p_object_id     Current object identifier (from database).
     * @param   integer $p_status        Decision whether category data should be created or just updated.
     *
     * @return  mixed    Returns category data identifier (int) on success, true bool) if nothing had to be done, otherwise false.
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;
        if (is_array($p_category_data) && isset($p_category_data['properties']))
        {
            // Create category data identifier if needed:
            if ($p_status === isys_import_handler_cmdb::C__CREATE)
            {
                $p_category_data['data_id'] = $this->create_connector(
                    'isys_catg_sim_card_list',
                    $p_object_id
                );
            } // if
            if ($p_status === isys_import_handler_cmdb::C__CREATE || $p_status === isys_import_handler_cmdb::C__UPDATE)
            {
                // Save category data:
                $l_indicator = $this->save(
                    $p_category_data['data_id'],
                    C__RECORD_STATUS__NORMAL,
                    $p_category_data['properties']['type'][C__DATA__VALUE],
                    $p_category_data['properties']['card_no'][C__DATA__VALUE],
                    $p_category_data['properties']['serial'][C__DATA__VALUE],
                    $p_category_data['properties']['phone_no'][C__DATA__VALUE],
                    $p_category_data['properties']['client_no'][C__DATA__VALUE],
                    $p_category_data['properties']['start'][C__DATA__VALUE],
                    $p_category_data['properties']['end'][C__DATA__VALUE],
                    $p_category_data['properties']['pin'][C__DATA__VALUE],
                    $p_category_data['properties']['pin2'][C__DATA__VALUE],
                    $p_category_data['properties']['puk'][C__DATA__VALUE],
                    $p_category_data['properties']['puk2'][C__DATA__VALUE],
                    $p_category_data['properties']['twincard'][C__DATA__VALUE],
                    $p_category_data['properties']['tc_card_no'][C__DATA__VALUE],
                    $p_category_data['properties']['tc_serial_no'][C__DATA__VALUE],
                    $p_category_data['properties']['tc_phone_no'][C__DATA__VALUE],
                    $p_category_data['properties']['tc_pin'][C__DATA__VALUE],
                    $p_category_data['properties']['tc_pin2'][C__DATA__VALUE],
                    $p_category_data['properties']['tc_puk'][C__DATA__VALUE],
                    $p_category_data['properties']['tc_puk2'][C__DATA__VALUE],
                    $p_category_data['properties']['threshold_date'][C__DATA__VALUE],
                    $p_category_data['properties']['description'][C__DATA__VALUE],
                    $p_category_data['properties']['optional_info'][C__DATA__VALUE],
                    $p_category_data['properties']['network_provider'][C__DATA__VALUE],
                    $p_category_data['properties']['telephone_rate'][C__DATA__VALUE],
                    $p_category_data['properties']['assigned_mobile'][C__DATA__VALUE]
                );
            } // if
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

} // class
?>