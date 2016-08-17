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
 * DAO: specific category list for custom identifier
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Selcuk Kekec <skekec@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_catg_identifier extends isys_cmdb_dao_list
{

    /**
     * Gets category identifier.
     *
     * @return  integer
     */
    public function get_category()
    {
        return $this->m_cat_dao->get_category_id();
    } // function

    /**
     * Gets category type.
     *
     * @author Selcuk Kekec <skekec@i-doit.com>
     * @return  integer
     */
    public function get_category_type()
    {
        return $this->m_cat_dao->get_category_type();
    } // function

    /**
     * Gets fields to display in the list view.
     *
     * @author Selcuk Kekec <skekec@i-doit.com>
     * @return  array
     */
    public function get_fields()
    {
        $l_table      = $this->m_cat_dao->get_table();
        $l_properties = $this->m_cat_dao->get_properties();

        return [
            $l_table . '__id'                  => 'ID',
            $l_table . '__key'                 => $l_properties['key'][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE],
            $l_table . '__value'               => $l_properties['value'][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE],
            $l_table . '__group'               => $l_properties['group'][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE],
            'isys_catg_identifier_type__title' => $l_properties['type'][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE]
        ];
    } // function
} // class