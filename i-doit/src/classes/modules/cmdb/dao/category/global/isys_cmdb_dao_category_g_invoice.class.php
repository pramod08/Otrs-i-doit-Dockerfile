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
 * DAO: global category for invoices
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_invoice extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'invoice';
    /**
     * Name of property which should be used as identifier
     *
     * @var string
     */
    protected $m_entry_identifier = 'denotation';
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
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function properties()
    {
        return [
            'denotation'                    => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__TITLE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Title'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_invoice_list__denotation'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CATG__INVOICE__DENOTATION'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ]
                ]
            ),
            'date'                          => array_replace_recursive(
                isys_cmdb_dao_category_pattern::date(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__INVOICE__DATE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Invoice Date'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_invoice_list__date'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATG__INVOICE__DATE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_calSelDate' => '',
                            'p_bTime'      => 0
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ]
                ]
            ),
            'amount'                        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::money(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__INVOICE__AMOUNT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Amount'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_invoice_list__amount'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CATG__INVOICE__AMOUNT'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ]
                ]
            ),
            'edited'                        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::date(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__INVOICE__EDITED',
                        C__PROPERTY__INFO__DESCRIPTION => 'edited'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_invoice_list__edited'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATG__INVOICE__EDITED',
                        C__PROPERTY__UI__PARAMS => [
                            'p_calSelDate' => '',
                            'p_bTime'      => 0
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ]
                ]
            ),
            'financial_accounting_delivery' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::date(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__INVOICE__FINANCIAL_ACCOUNTING_DELIVERY',
                        C__PROPERTY__INFO__DESCRIPTION => 'Dispensing Tax Accounting'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_invoice_list__financial_accounting_delivery'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATG__INVOICE__FINANCIAL_ACCOUNTING_DELIVERY',
                        C__PROPERTY__UI__PARAMS => [
                            'p_calSelDate' => '',
                            'p_bTime'      => 0
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ]
                ]
            ),
            'charged'                       => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__INVOICE__CHARGED',
                        C__PROPERTY__INFO__DESCRIPTION => 'Charged'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_invoice_list__charged'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CMDB__CATG__INVOICE__CHARGED',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData'     => serialize(get_smarty_arr_YES_NO()),
                            'p_bDbFieldNN' => 1
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false,
                        C__PROPERTY__PROVIDES__LIST   => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'get_yes_or_no'
                        ]
                    ]
                ]
            ),
            'description'                   => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_invoice_list__description'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CATG__INVOICE
                    ]
                ]
            )
        ];
    } // function
} // class

?>