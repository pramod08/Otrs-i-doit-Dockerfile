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
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Stuecken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_cats_database_objects extends isys_cmdb_dao_list
{
    /**
     *
     * @return  integer
     */
    public function get_category()
    {
        return C__CATS__DATABASE_OBJECTS;
    } // function

    /**
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_SPECIFIC;
    } // function

    /**
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "isys_cats_database_objects_list__title" => "LC__UNIVERSAL__TITLE",
            "isys_database_objects__title"           => "LC__CMDB__CATG__TYPE"
        ];
    } // function
} // class