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
 * DAO: ObjectType list for CPU
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @author      Dennis Blümer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_catg_cpu extends isys_cmdb_dao_list implements isys_cmdb_dao_list_interface
{
    /**
     * Return constant of category.
     *
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category()
    {
        return C__CATG__CPU;
    } // function

    /**
     * Return constant of category type.
     *
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    } // function

    /**
     * Method which helps modifying each row.
     *
     * @param  array $p_row
     */
    public function modify_row(&$p_row)
    {
        $p_row["isys_catg_cpu_list__frequency"] = isys_convert::frequency(
                $p_row["isys_catg_cpu_list__frequency"],
                $p_row['isys_catg_cpu_list__isys_frequency_unit__id'],
                C__CONVERT_DIRECTION__BACKWARD
            ) . ' ' . $p_row['isys_frequency_unit__title'];
    } // function

    /**
     * Method for retrieving the displayable fields.
     *
     * @return   array
     * @version  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_fields()
    {
        return [
            "isys_catg_cpu_list__title"         => "LC__CMDB__CATG__TITLE",
            "isys_catg_cpu_manufacturer__title" => "LC__CMDB__CATG__MANUFACTURER",
            "isys_catg_cpu_type__title"         => "LC__CMDB__CATG__TYPE",
            "isys_catg_cpu_list__frequency"     => "LC__CMDB__CATG__FREQUENCY"
        ];
    } // function
} // class