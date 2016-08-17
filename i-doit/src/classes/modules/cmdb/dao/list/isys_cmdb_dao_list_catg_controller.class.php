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
 * DAO: ObjectType list for storage controllers.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_catg_controller extends isys_cmdb_dao_list
{
    /**
     * Return constant of category
     *
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category()
    {
        return C__CATG__CONTROLLER;
    } // function

    /**
     * Return constant of category type
     *
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    } // function

    /**
     * Returns array with table headers.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "isys_catg_controller_list__title"    => "LC__CATG__STORAGE_CONTROLLER_TITLE",
            "isys_controller_type__title"         => "LC__CATG__STORAGE_CONTROLLER_TYPE",
            "isys_controller_manufacturer__title" => "LC__CATG__STORAGE_CONTROLLER_MANUFACTURER",
            "isys_controller_model__title"        => "LC__CATG__STORAGE_CONTROLLER_MODEL"
        ];
    } // function
} // class