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
 * DAO: global category for Certificate
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Selcuk Kekec <skekec@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_certificate extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'certificate';
    /**
     * Category's constant.
     *
     * @var   string
     * @todo  No standard behavior!
     */
    protected $m_category_const = 'C__CATG__CERTIFICATE';
    /**
     * Category's identifier.
     *
     * @var   integer
     * @todo  No standard behavior!
     */
    protected $m_category_id = C__CATG__CERTIFICATE;
    /**
     * Category entry is purgable
     *
     * @var bool
     */
    protected $m_is_purgable = true;
    /**
     * Main table where properties are stored persistently.
     *
     * @var   string
     * @todo  No standard behavior!
     */
    protected $m_table = 'isys_catg_certificate_list';
    /**
     * Category's template file.
     *
     * @var   string
     * @todo  No standard behavior!
     */
    protected $m_tpl = 'catg__certificate.tpl';
    /**
     * Category's user interface.
     *
     * @var   string
     * @todo  No standard behavior!
     */
    protected $m_ui = 'isys_cmdb_ui_category_g_certificate';

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'type'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__TYPE',
                        C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATG__TYPE'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_certificate_list__isys_certificate_type__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_certificate_type',
                            'isys_certificate_type__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__CERTIFICATE__TYPE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_certificate_type'
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
            'create_date' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::date(),
                [
                    C__PROPERTY__INFO   => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__CERTIFICATE__CREATE_DATE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Creation date'
                    ],
                    C__PROPERTY__DATA   => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_certificate_list__created',
                    ],
                    C__PROPERTY__UI     => [
                        C__PROPERTY__UI__ID => 'C__CATG__CERTIFICATE__CREATE_DATE'
                    ],
                    C__PROPERTY__FORMAT => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'date'
                        ]
                    ]
                ]
            ),
            'expire_date' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::date(),
                [
                    C__PROPERTY__INFO   => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__CERTIFICATE__EXPIRE_DATE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Expire date'
                    ],
                    C__PROPERTY__DATA   => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_certificate_list__expire',
                    ],
                    C__PROPERTY__UI     => [
                        C__PROPERTY__UI__ID => 'C__CATG__CERTIFICATE__EXPIRE_DATE'
                    ],
                    C__PROPERTY__FORMAT => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'date'
                        ]
                    ]
                ]
            ),
            'description' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_certificate_list__description'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CATG__CERTIFICATE
                    ]
                ]
            )
        ];
    } // function
} // class
?>