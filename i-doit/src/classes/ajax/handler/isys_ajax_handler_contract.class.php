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
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_ajax_handler_contract extends isys_ajax_handler
{
    /**
     * Initialization method.
     *
     * @global  isys_component_template $g_comp_template
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function init()
    {
        global $g_comp_database;

        $l_contract_information = [
            'LC__CMDB__CATS__CONTRACT__TYPE'               => 'isys_contract_type__title',
            'LC__CMDB__CATS__CONTRACT__CONTRACT_NO'        => 'isys_cats_contract_list__contract_no',
            'LC__CMDB__CATS__CONTRACT__CUSTOMER_NO'        => 'isys_cats_contract_list__customer_no',
            'LC__CMDB__CATS__CONTRACT__INTERNAL_NO'        => 'isys_cats_contract_list__internal_no',
            'LC__CMDB__CATS__CONTRACT__COSTS'              => 'isys_cats_contract_list__costs',
            'LC__CMDB__CATS__CONTRACT__PRODUCT'            => 'isys_cats_contract_list__product',
            'LC__CMDB__CATS__CONTRACT__REACTION_RATE'      => 'isys_contract_reaction_rate__title',
            'LC__CMDB__CATS__CONTRACT__STATUS'             => 'isys_contract_status__title',
            'LC__CMDB__CATS__CONTRACT__START_DATE'         => 'isys_cats_contract_list__start_date',
            'LC__CMDB__CATS__CONTRACT__END_DATE'           => 'isys_cats_contract_list__end_date',
            'LC__CMDB__CATS__CONTRACT__END_TYPE'           => 'isys_contract_end_type__title',
            'LC__CMDB__CATS__CONTRACT__NOTICE_DATE'        => 'isys_cats_contract_list__notice_date',
            'LC__CMDB__CATS__CONTRACT__NOTICE_VALUE'       => '',
            'LC__CMDB__CATS__CONTRACT__MAINTENANCE_PERIOD' => ''
        ];

        $l_contractDao = new isys_cmdb_dao_category_s_contract($g_comp_database);
        $l_contractRow = $l_contractDao->get_data(null, $_POST['contractID']);
        $l_output      = "";

        if ($l_contractRow->num_rows() > 0)
        {
            $l_contractRow = $l_contractRow->get_row();

            $l_contract_information['LC__CMDB__CATS__CONTRACT__NOTICE_VALUE']       = $l_contractRow['isys_cats_contract_list__notice_period'] . " " . _L(
                    $l_contractRow['notice_title']
                );
            $l_contract_information['LC__CMDB__CATS__CONTRACT__MAINTENANCE_PERIOD'] = $l_contractRow['isys_cats_contract_list__maintenance_period'] . " " . _L(
                    $l_contractRow['main_title']
                );

            foreach ($l_contract_information AS $l_title => $l_value)
            {
                if (is_array($l_contractRow) && array_key_exists($l_value, $l_contractRow))
                {
                    $l_row_value = $l_contractRow[$l_value];
                    if (strstr($l_row_value, "00:00:00"))
                    {
                        $l_row_value = date('d.m.Y', strtotime($l_row_value));
                    }
                }
                else
                {
                    $l_row_value = $l_value;
                }
                $l_output .= "<tr>" . "<td >" . _L($l_title) . ": </td>" . "<td style='padding-left:5px;'>" . _L($l_row_value) . "</td>" . "</tr>";
            }

            if (!empty($l_contractRow['isys_cats_contract_list__notice_period']) && !empty($l_contractRow["isys_cats_contract_list__notice_period_unit__id"]))
            {
                $l_contractRow['notice_end']                                      = $l_contractDao->calculate_noticeperiod(
                    $l_contractRow['isys_cats_contract_list__end_date'],
                    $l_contractRow['isys_cats_contract_list__notice_period'],
                    $l_contractRow["isys_cats_contract_list__notice_period_unit__id"]
                );
                $l_contract_information['LC__CMDB__CATS__CONTRACT__CONTRACT_END'] = 'notice_end';
            }

            if (!empty($l_contractRow["isys_cats_contract_list__maintenance_period"]) && !empty($l_contractRow["isys_cats_contract_list__maintenance_period_unit__id"]) && !empty($l_contractRow["isys_cats_contract_list__start_date"]))
            {
                $l_contractRow['maintenance_end']                                    = $l_contractDao->calculate_maintenanceperiod(
                    $l_contractRow["isys_cats_contract_list__start_date"],
                    $l_contractRow["isys_cats_contract_list__maintenance_period"],
                    $l_contractRow["isys_cats_contract_list__maintenance_period_unit__id"]
                );
                $l_contract_information['LC__CMDB__CATS__CONTRACT__MAINTENANCE_END'] = 'maintenance_end';
            }

            echo $l_output . "<input type='hidden' id='assigned_contract__startdate' data-view='" . date(
                    "d.m.Y",
                    strtotime($l_contractRow['isys_cats_contract_list__start_date'])
                ) . "' value='" . $l_contractRow['isys_cats_contract_list__start_date'] . "' />" . "<input type='hidden' id='assigned_contract__enddate' data-view='" . date(
                    "d.m.Y",
                    strtotime($l_contractRow['isys_cats_contract_list__end_date'])
                ) . "' value='" . $l_contractRow['isys_cats_contract_list__end_date'] . "' />" . "<input type='hidden' id='reaction_rate' value='" . $l_contractRow['isys_cats_contract_list__isys_contract_reaction_rate__id'] . "' />";
        }
        else
        {
            foreach ($l_contract_information AS $l_title => $l_value)
            {
                $l_output .= "<tr>" . "<td style='text-align:right'>" . _L($l_title) . ": </td>" . "<td style='text-align:right; padding-left:5px;'></td>" . "</tr>";
            }
            echo $l_output;
        }

        $this->_die();
    } // function

    /**
     * This method defines, if the hypergate has to be included for this handler.
     *
     * @return  boolean
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public static function needs_hypergate()
    {
        return false;
    } // function
} // class