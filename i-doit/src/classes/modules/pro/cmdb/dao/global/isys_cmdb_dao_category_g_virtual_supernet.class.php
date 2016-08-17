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
 * DAO: global category for objecttype supernet
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_virtual_supernet extends isys_cmdb_dao_category_g_virtual
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'virtual_supernet';

    /**
     * Dynamic property handling for getting the supernet link.
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_supernet_link(array $p_row)
    {
        $l_link = isys_application::instance()->www_path . isys_helper_link::create_url(
                [
                    C__CMDB__GET__OBJECT   => $p_row['isys_obj__id'],
                    C__CMDB__GET__TREEMODE => C__CMDB__VIEW__TREE_OBJECT,
                    C__CMDB__GET__CATG     => C__CATG__VIRTUAL_SUPERNET
                ]
            );

        return '<a href="' . $l_link . '">' . $p_row['isys_obj__title'] . '</a>';
    } // function

    /**
     * Method for receiving all subnets by a given IP range.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_from_long
     * @param   integer $p_to_long
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function get_subnets($p_obj_id, $p_from_long, $p_to_long, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_subquery = 'SELECT COUNT(*)
			FROM isys_cats_net_ip_addresses_list
			INNER JOIN isys_catg_ip_list ON isys_catg_ip_list__isys_cats_net_ip_addresses_list__id = isys_cats_net_ip_addresses_list__id
			WHERE isys_cats_net_ip_addresses_list__title != ""
			AND isys_cats_net_ip_addresses_list__isys_obj__id = isys_obj__id
			AND isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

        $l_sql = 'SELECT isys_cats_net_list.*, isys_obj.*, isys_net_type.*, (' . $l_subquery . ') AS used_adresses
			FROM isys_cats_net_list
			INNER JOIN isys_obj ON isys_cats_net_list__isys_obj__id = isys_obj__id
			LEFT OUTER JOIN isys_net_type ON isys_net_type__id = isys_cats_net_list__isys_net_type__id
			WHERE (isys_cats_net_list__address_range_from_long >= ' . $this->convert_sql_text($p_from_long) . '
			AND isys_cats_net_list__address_range_to_long <= ' . $this->convert_sql_text($p_to_long) . ')
			AND isys_obj__id != ' . $this->convert_sql_id($p_obj_id) . '
			AND isys_obj__status = ' . $this->convert_sql_int($p_status) . '
			ORDER BY isys_cats_net_list__address_range_from_long ASC;';

        return $this->retrieve($l_sql);
    } // function

    /**
     * Method for retrieving the dynamic properties, used by the new list component.
     *
     * @return  array
     */
    protected function dynamic_properties()
    {
        return [
            '_supernet_link' => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__SUPERNET__OPEN_SUPERNET',
                    C__PROPERTY__INFO__DESCRIPTION => 'Open supernet'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_obj__id'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_callback_supernet_link'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST => true
                ]
            ]
        ];
    } // function
} // class