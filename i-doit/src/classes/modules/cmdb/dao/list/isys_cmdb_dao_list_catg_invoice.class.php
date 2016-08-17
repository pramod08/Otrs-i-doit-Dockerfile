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
 * @package   i-doit
 * @subpackage
 * @author    Dennis Stücken <dstuecken@i-doit.org>
 * @version   1.0
 * @copyright synetics GmbH
 * @license   http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_catg_invoice extends isys_cmdb_dao_list implements isys_cmdb_dao_list_interface
{
    /**
     * Method for modifying single field contents before rendering.
     *
     * @param  array &$p_row
     */
    public function format_row(&$p_row)
    {
        global $g_loc;

        $l_date_format_user = $g_loc->get_user_settings(LC_TIME);
        $l_date_format      = str_replace('%', '', $l_date_format_user['d_fmt_m']);
        $l_empty_value      = isys_tenantsettings::get('gui.empty_value', '-');

        $p_row["isys_catg_invoice_list__amount"]                        = $g_loc->fmt_monetary($p_row["isys_catg_invoice_list__amount"]);
        $p_row["isys_catg_invoice_list__date"]                          = ($p_row["isys_catg_invoice_list__date"] != null) ? date(
            $l_date_format,
            strtotime($p_row["isys_catg_invoice_list__date"])
        ) : $l_empty_value;
        $p_row["isys_catg_invoice_list__edited"]                        = ($p_row["isys_catg_invoice_list__edited"] != null) ? $p_row["isys_catg_invoice_list__edited"] = date(
            $l_date_format,
            strtotime($p_row["isys_catg_invoice_list__edited"])
        ) : $l_empty_value;
        $p_row["isys_catg_invoice_list__financial_accounting_delivery"] = ($p_row["isys_catg_invoice_list__financial_accounting_delivery"] != null) ? $p_row["isys_catg_invoice_list__financial_accounting_delivery"] = date(
            $l_date_format,
            strtotime($p_row["isys_catg_invoice_list__financial_accounting_delivery"])
        ) : $l_empty_value;
    } // function

    /**
     * Retrieves the category ID.
     *
     * @return  integer
     */
    public function get_category()
    {
        return C__CATG__INVOICE;
    } // function

    /**
     * Retrieves the category type.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    } // function

    /**
     * Method for modifying single field contents before rendering.
     *
     * @param  array $p_row
     */
    public function modify_row(&$p_row)
    {
        $p_row["isys_catg_invoice_list__charged"] = ($p_row["isys_catg_invoice_list__charged"] == '1') ? _L("LC__UNIVERSAL__YES") : _L("LC__UNIVERSAL__NO");
    } // function

    /**
     * Retrieve an array of fields to display.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "isys_catg_invoice_list__denotation"                    => "LC__CMDB__CATG__TITLE",
            "isys_catg_invoice_list__amount"                        => "LC__CMDB__CATG__INVOICE__AMOUNT",
            "isys_catg_invoice_list__date"                          => "LC__CMDB__CATG__INVOICE__DATE",
            "isys_catg_invoice_list__edited"                        => "LC__CMDB__CATG__INVOICE__EDITED",
            "isys_catg_invoice_list__financial_accounting_delivery" => "LC__CMDB__CATG__INVOICE__FINANCIAL_ACCOUNTING_DELIVERY",
            "isys_catg_invoice_list__charged"                       => "LC__CMDB__CATG__INVOICE__CHARGED",
        ];
    } // function
} // class