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
 * DAO: specific category list for network listener
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_catg_net_listener extends isys_cmdb_dao_list
{
    /**
     * Gets fields to display in the list view.
     *
     * @return  array
     */
    public function get_fields()
    {
        $l_properties = $this->m_cat_dao->get_properties();

        return [
            'software'                                                                                    => _L('LC__CMDB__CATG__NET_LISTENER__OPENED_BY_APPLICATION'),
            @$l_properties['ip_address'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '__title' => $l_properties['ip_address'][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE],
            'port_range'                                                                                  => _L('Port') . ' / ' . _L('LC__UNIVERSAL__PORT_RANGE'),
            @$l_properties['protocol'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '__title'   => $l_properties['protocol'][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE]
        ];
    } // function

    public function make_row_link($l_jumpgets)
    {
        $l_jumpgets['cateID'] = '[{isys_catg_net_listener_list__id}]';

        return '?' . isys_glob_http_build_query($l_jumpgets);
    }

    /**
     * Modifies single rows for displaying links or getting translations
     *
     * @param   array & $p_row
     */
    public function modify_row(&$p_row)
    {
        $p_row['isys_id'] = $p_row['isys_catg_net_listener_list__id'];;
        $p_row['port_range'] = $p_row['isys_catg_net_listener_list__port_from'];

        if ($p_row['isys_catg_net_listener_list__port_from'] != $p_row['isys_catg_net_listener_list__port_to'])
        {
            $p_row['port_range'] .= '-' . $p_row['isys_catg_net_listener_list__port_to'];
        } // if

        if ($p_row['isys_catg_net_listener_list__opened_by'] > 0)
        {
            $p_row['software'] = $this->m_cat_dao->get_obj_name_by_id_as_string($p_row['isys_catg_net_listener_list__opened_by']);
        }
        else
        {
            $p_row['software'] = isys_tenantsettings::get('gui.empty_value', '-');
        } // if
    } // function
} // class