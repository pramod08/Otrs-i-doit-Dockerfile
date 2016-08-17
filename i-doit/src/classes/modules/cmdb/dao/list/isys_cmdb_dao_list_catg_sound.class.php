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
 *
 * @package    i-doit
 * @author     Dennis Stücken <dstuecken@i-doit.org>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_catg_sound extends isys_cmdb_dao_list implements isys_cmdb_dao_list_interface
{
    /**
     *
     * @return  integer
     */
    public function get_category()
    {
        return C__CATG__SOUND;
    } // function

    /**
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    } // function

    /**
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "isys_sound_manufacturer__title" => "LC__CMDB__CATG__MANUFACTURER",
            "isys_catg_sound_list__title"    => "LC__CMDB__CATG__TITLE"
        ];
    } // function
} // class