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
 * DAO: specific category for applications variants
 *
 * @package       i-doit
 * @subpackage    CMDB_Categories
 * @copyright     synetics GmbH
 * @author        Van Quyen Hoang <qhoang@i-doit.de>
 * @license       http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_s_application_variant extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'application_variant';
    /**
     * Category entry is purgable
     *
     * @var bool
     */
    protected $m_is_purgable = true;
    /**
     * Determines if Category is multivalued or not
     *
     * @var bool
     */
    protected $m_multivalued = true;
    /**
     * Category's table name
     *
     * @var string
     */
    protected $m_table = 'isys_cats_app_variant_list';

    /**
     * Method for returning the properties.
     *
     * @author Van Quyen Hoang <qhoang@i-doit.de>
     * @return  array
     */
    protected function properties()
    {
        return [
            'title'       => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__APPLICATION_VARIANT__TITLE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Title'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_app_variant_list__title'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATS__APPLICATION_VARIANT__TITLE'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__REPORT => true
                    ]
                ]
            ),
            'variant'     => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__APPLICATION_VARIANT__VARIANT',
                        C__PROPERTY__INFO__DESCRIPTION => 'Variant'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_app_variant_list__variant',
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CATS__APPLICATION_VARIANT__VARIANT',
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__REPORT => true
                    ]
                ]
            ),
            'description' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_app_variant_list__description'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . C__CATS__APPLICATION_VARIANT
                    ]
                ]
            )
        ];
    }


    /**
     * Method for retrieving the dynamic properties, used by the new list component.
     *
     * @return  array
     */
    protected function dynamic_properties()
    {
        return [
            '_variant' => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__APPLICATION_VARIANT__VARIANT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Variant'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_app_variant_list__isys_obj__id'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_callback_variant'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST   => true,
                    C__PROPERTY__PROVIDES__REPORT => false
                ]
            ]
        ];
    } // function

    /**
     * Dynamic property handling for getting the connected objects.
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_variant(array $p_row)
    {
        global $g_comp_database;

        $l_variant = [];
        $l_dao     = isys_cmdb_dao_category_s_application_variant::instance($g_comp_database);

        $l_data = $l_dao->get_data(null, (isset($p_row['__obj_id__']))? $p_row['__obj_id__']: $p_row['isys_obj__id']);

        while ($l_row = $l_data->get_row())
        {
            $l_variant_title = ($l_row['isys_cats_app_variant_list__title'] != '')
                ? $l_row['isys_cats_app_variant_list__variant'] . ' (' . $l_row['isys_cats_app_variant_list__title'] . ')'
                : $l_row['isys_cats_app_variant_list__variant'];
            $l_variant[] = $l_variant_title;
        } // while

        if(count($l_variant))
        {
            return '<ul><li>- ' . implode('</li><li>- ', $l_variant) . '</li></ul>';
        }
        else
        {
            return '';
        }
    } // function
} // class

?>
