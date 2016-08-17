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
 * User interface: Specific category for contract
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Selcuk Kekec <skekec@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_s_contract extends isys_cmdb_ui_category_specific
{
    /**
     * Show the detail-template for specific category contract.
     *
     * @param  isys_cmdb_dao_category_s_contract $p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $g_loc;

        $l_catdata = $p_cat->get_general_data();

        parent::process($p_cat);

        $l_maintenance_end = $l_contract_end = $l_expiration_date = isys_tenantsettings::get('gui.empty_value', '-');

        if ($l_catdata["isys_cats_contract_list__isys_contract_notice_period_type__id"] == C__CONTRACT__ON_CONTRACT_END)
        {
            if (!empty($l_catdata['isys_cats_contract_list__end_date']) && $l_catdata['isys_cats_contract_list__end_date'] != '1970-01-01 00:00:00' && $l_catdata['isys_cats_contract_list__end_date'] != '0000-00-00 00:00:00')
            {
                $l_contract_end    = $g_loc->fmt_date(strtotime(rtrim($l_catdata['isys_cats_contract_list__end_date'], '00:00:00')));
                $l_expiration_date = $p_cat->calculate_noticeperiod(
                    $l_contract_end,
                    $l_catdata['isys_cats_contract_list__notice_period'],
                    $l_catdata['isys_cats_contract_list__notice_period_unit__id']
                );
            }
            else
            {
                $l_contract_end    = _L('LC__CMDB__CATS__CONTRACT__CONTRACT_EXPIRATION_DATE_IS_NOT_DEFINED');
                $l_expiration_date = _L('LC__CMDB__CATS__CONTRACT__CONTRACT_EXPIRATION_DATE_IS_NOT_DEFINED');
            } // if
        }
        elseif ($l_catdata["isys_cats_contract_list__isys_contract_notice_period_type__id"] == C__CONTRACT__FROM_NOTICE_DATE)
        {
            if (!empty($l_catdata['isys_cats_contract_list__notice_date']) && $l_catdata['isys_cats_contract_list__notice_date'] != '1970-01-01 00:00:00' && $l_catdata['isys_cats_contract_list__notice_date'] != '0000-00-00 00:00:00')
            {
                $l_contract_end    = $p_cat->calculate_next_contract_end_date(
                    $l_catdata['isys_cats_contract_list__notice_date'],
                    $l_catdata['isys_cats_contract_list__notice_period'],
                    $l_catdata['isys_cats_contract_list__notice_period_unit__id']
                );
                $l_expiration_date = _L('LC__UNIVERSAL__ANYTIME');
            }
            else
            {
                $l_contract_end    = _L('LC__CMDB__CATS__CONTRACT__CONTRACT_EXPIRATION_DATE_IS_NOT_DEFINED');
                $l_expiration_date = _L('LC__CMDB__CATS__CONTRACT__CONTRACT_EXPIRATION_DATE_IS_NOT_DEFINED');
            } // if
        } // if

        if (!empty($l_catdata["isys_cats_contract_list__maintenance_period"]) && !empty($l_catdata["isys_cats_contract_list__maintenance_period_unit__id"]) && !empty($l_catdata["isys_cats_contract_list__start_date"]))
        {
            $l_maintenance_end = $p_cat->calculate_maintenanceperiod(
                $l_catdata["isys_cats_contract_list__start_date"],
                $l_catdata["isys_cats_contract_list__maintenance_period"],
                $l_catdata["isys_cats_contract_list__maintenance_period_unit__id"]
            );
        } // if

        $l_date_format = $g_loc->get_date_format();
        $this->get_template_component()
            ->assign('current_date_format_splitter', (strpos($l_date_format, '.') ? '.' : '-'))
            ->assign(
                'current_date_format',
                str_replace(
                    [
                        '.',
                        '-'
                    ],
                    [
                        '',
                        ''
                    ],
                    $l_date_format
                )
            )
            ->assign("description_date_format", _L('LC__CATG__OVERVIEW__DATE_FORMAT') . ': ' . $g_loc->get_date_format() . ' (' . date($l_date_format, time()) . ')')
            ->assign("contract_end", $l_contract_end)
            ->assign("maintenance_end", $l_maintenance_end)
            ->assign("expiration_date", $l_expiration_date)
            ->assign("date_format", $g_loc->get_date_format());
    } // function
} // class