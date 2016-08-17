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
 * DAO: global category for smartcard certificate
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_smartcard_certificate extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'smartcard_certificate';

    /**
     * Category entry is purgable
     *
     * @var bool
     */
    protected $m_is_purgable = true;

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    protected function properties()
    {
        return [
            'cardnumber'       => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__SMARTCARD_CERTIFICATE__CARDNUMBER',
                        C__PROPERTY__INFO__DESCRIPTION => 'Cardnumber'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_smartcard_certificate_list__cardnumber',
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__SMARTCARD_CERTIFICATE__CARDNUMBER',
                    ]
                ]
            ),
            'barring_password' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__SMARTCARD_CERTIFICATE__BARRING_PASSWORD',
                        C__PROPERTY__INFO__DESCRIPTION => 'Barring password'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_smartcard_certificate_list__barring_password',
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__SMARTCARD_CERTIFICATE__BARRING_PASSWORD',
                    ]
                ]
            ),
            'pin_nr'           => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__SMARTCARD_CERTIFICATE__PIN_NR',
                        C__PROPERTY__INFO__DESCRIPTION => 'PIN-Nr.'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_smartcard_certificate_list__pin_number',
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__SMARTCARD_CERTIFICATE__PIN_NR',
                    ]
                ]
            ),
            'reference'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__SMARTCARD_CERTIFICATE__REFERENCE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Reference'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_smartcard_certificate_list__reference',
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__SMARTCARD_CERTIFICATE__REFERENCE',
                    ]
                ]
            ),
            'expires_on'       => array_replace_recursive(
                isys_cmdb_dao_category_pattern::date(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__SMARTCARD_CERTIFICATE__EXPIRES_ON',
                        C__PROPERTY__INFO__DESCRIPTION => 'Expires on'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_smartcard_certificate_list__expires_on',
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATG__SMARTCARD_CERTIFICATE__EXPIRES_ON',
                    ]
                    /*,
                                        C__PROPERTY__FORMAT => array(
                                            C__PROPERTY__FORMAT__CALLBACK => array(
                                                'isys_export_helper',
                                                'date'
                                            )
                                        )*/
                ]
            ),
            'description'      => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_smartcard_certificate_list__description'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CATG__TELEPHONE_FAX
                    ]
                ]
            )
        ];
    } // function

} // class
?>