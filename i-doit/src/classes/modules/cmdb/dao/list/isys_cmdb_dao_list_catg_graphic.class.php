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
 * @package     i-doit
 * @subpackage
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_catg_graphic extends isys_cmdb_dao_list implements isys_cmdb_dao_list_interface
{
    /**
     * Retrieves the category ID.
     *
     * @return  integer
     */
    public function get_category()
    {
        return C__CATG__GRAPHIC;
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
     * @param  array &$p_row
     */
    public function modify_row(&$p_row)
    {
        $p_row['concat_memory'] = isys_convert::memory(
                $p_row['isys_catg_graphic_list__memory'],
                $p_row['isys_catg_graphic_list__isys_memory_unit__id'],
                C__CONVERT_DIRECTION__BACKWARD
            ) . ' ' . $p_row['isys_memory_unit__title'];
    } // function

    /**
     * Retrieve an array of fields to display.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            'isys_graphic_manufacturer__title' => 'LC__CMDB__CATG__MANUFACTURER',
            'isys_catg_graphic_list__title'    => 'LC__CMDB__CATG__TITLE',
            'concat_memory'                    => 'LC__CMDB__CATG__MEMORY'
        ];
    } // function
} // class