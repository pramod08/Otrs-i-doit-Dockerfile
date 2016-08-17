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
 * DAO: Gloabl category Hostadapter (HBA)
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Bluemer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_catg_hba extends isys_cmdb_dao_list
{
    /**
     * Return constant of category.
     *
     * @return  integer
     */
    public function get_category()
    {
        return C__CATG__HBA;
    } // function

    /**
     * Return constant of category type.
     *
     * @return  integer
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
            "isys_catg_hba_list__title"           => "LC__CATG__STORAGE_CONTROLLER_TITLE",
            "isys_hba_type__title"                => "LC__CATG__STORAGE_CONTROLLER_TYPE",
            "isys_controller_manufacturer__title" => "LC__CATG__STORAGE_CONTROLLER_MANUFACTURER",
            "isys_controller_model__title"        => "LC__CATG__STORAGE_CONTROLLER_MODEL"
        ];
    } // function
} // class