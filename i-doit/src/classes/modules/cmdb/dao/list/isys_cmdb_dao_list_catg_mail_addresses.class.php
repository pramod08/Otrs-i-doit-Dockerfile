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
 * DAO: global category list for e-mail addresses
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Van Quyen Hoang <qhoang@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_catg_mail_addresses extends isys_cmdb_dao_list
{
    /**
     * Gets category identifier.
     *
     * @return  integer
     */
    public function get_category()
    {
        return $this->m_cat_dao->get_category_id();
    } //function

    /**
     * Gets category type.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return $this->m_cat_dao->get_category_type();
    } //function

    /**
     * Modifies output of every row
     *
     * @param array $p_aRow
     */
    public function modify_row(&$p_aRow)
    {
        $l_table = $this->m_cat_dao->get_table();

        $p_aRow[$l_table . '__primary'] = $p_aRow[$l_table . '__primary'] ? '<span class="green">' . _L('LC__UNIVERSAL__YES') . '</span>' : '<span class="red">' . _L(
                'LC__UNIVERSAL__NO'
            ) . '</span>';
    } //function

    /**
     * Gets fields to display in the list view.
     *
     * @return  array
     */
    public function get_fields()
    {
        $l_table      = $this->m_cat_dao->get_table();
        $l_properties = $this->m_cat_dao->get_properties();

        return [
            $l_table . '__title'   => $l_properties['title'][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE],
            $l_table . '__primary' => $l_properties['primary'][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE]
        ];
    } // functions
} // class