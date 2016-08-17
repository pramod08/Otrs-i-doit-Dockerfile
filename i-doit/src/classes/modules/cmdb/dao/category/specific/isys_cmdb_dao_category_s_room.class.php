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
 * DAO: specific category for rooms.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_s_room extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'room';

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
            'type'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_plus(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__TYPE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Typ'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD      => 'isys_cats_room_list__isys_room_type__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_room_type',
                            'isys_room_type__id'
                        ]
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CATS__ROOM_TYPE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strTable' => 'isys_room_type'
                        ]
                    ]
                ]
            ),
            'number'      => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__ROOM_NUMBER',
                        C__PROPERTY__INFO__DESCRIPTION => 'Room number'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_room_list__number'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATS__ROOM_NUMBER'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ]
                ]
            ),
            'floor'       => array_replace_recursive(
                isys_cmdb_dao_category_pattern::text(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__ROOM_FLOOR',
                        C__PROPERTY__INFO__DESCRIPTION => 'Floor'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_cats_room_list__floor'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID => 'C__CATS__ROOM_FLOOR'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH => false
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
                        C__PROPERTY__DATA__FIELD => 'isys_cats_room_list__description'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . C__CATS__ROOM
                    ]
                ]
            )
        ];
    } // function
} // class