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
 * DAO: specific category for printers.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_s_prt extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'prt';

    /**
     * Category entry is purgable.
     *
     * @var  boolean
     */
    protected $m_is_purgable = true;

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'type'         => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__PRT_TYPE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Type'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD      => 'isys_cats_prt_list__isys_cats_prt_type__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_cats_prt_type',
                            'isys_cats_prt_type__id'
                        ]
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID      => 'C__CATS__PRT_TYPE',
                        C__PROPERTY__UI__PARAMS  => [
                            'p_strTable'   => 'isys_cats_prt_type',
                            'p_bDbFieldNN' => '1',
                        ],
                        C__PROPERTY__UI__DEFAULT => C__CATS_PRT_TYPE__OTHER
                    ]
                ]
            ),
            'is_color'     => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__PRT_ISCOLOR',
                        C__PROPERTY__INFO__DESCRIPTION => 'Color'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_prt_list__iscolor'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID      => 'C__CATS__PRT_ISCOLOR',
                        C__PROPERTY__UI__PARAMS  => [
                            'p_arData'     => serialize(get_smarty_arr_YES_NO()),
                            'p_bDbFieldNN' => '1',
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
            'is_duplex'    => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__PRT_ISDUPLEX',
                        C__PROPERTY__INFO__DESCRIPTION => 'Duplex'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_prt_list__isduplex'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID      => 'C__CATS__PRT_ISDUPLEX',
                        C__PROPERTY__UI__PARAMS  => [
                            'p_arData'     => serialize(get_smarty_arr_YES_NO()),
                            'p_bDbFieldNN' => '1',
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
            'emulation'    => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__PRT_EMULATION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Emulation'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD      => 'isys_cats_prt_list__isys_cats_prt_emulation__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_cats_prt_emulation',
                            'isys_cats_prt_emulation__id'
                        ]
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID      => 'C__CATS__PRT_EMULATION',
                        C__PROPERTY__UI__PARAMS  => [
                            'p_strTable'   => 'isys_cats_prt_emulation',
                            'p_bDbFieldNN' => '1',
                        ],
                        C__PROPERTY__UI__DEFAULT => C__CATS_PRT_EMULATION__OTHER
                    ]
                ]
            ),
            'paper_format' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__PRT_PAPER',
                        C__PROPERTY__INFO__DESCRIPTION => 'Paper format'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD      => 'isys_cats_prt_list__isys_cats_prt_paper__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_cats_prt_paper',
                            'isys_cats_prt_paper__id'
                        ]
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CATS__PRT_PAPER',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable'   => 'isys_cats_prt_paper',
                            'p_bDbFieldNN' => '0',
                        ]
                    ]
                ]
            ),
            'description'  => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_prt_list__description'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . C__CATS__PRT
                    ]
                ]
            )
        ];
    } // function
} // class