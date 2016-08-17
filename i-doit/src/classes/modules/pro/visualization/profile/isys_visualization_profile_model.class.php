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
 * Visualization profile model.
 *
 * @package     modules
 * @subpackage  pro
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.5.0
 */
class isys_visualization_profile_model extends isys_component_dao
{
    /**
     * This variable will be used as internal content cache, when getting profile contents.
     *
     * @var  array
     */
    private $m_content_cache = [];

    /**
     * This array holds all available table fields. Use this for validation.
     *
     * @var  array
     */
    private $m_fields = [
        'isys_visualization_profile__id',
        'isys_visualization_profile__title',
        'isys_visualization_profile__const',
        'isys_visualization_profile__defaults',
        'isys_visualization_profile__obj_info_config',
        'isys_visualization_profile__config'
    ];

    /**
     * Method for retrieving the profiles.
     *
     * @param   mixed $p_id May be an array or an integer.
     *
     * @return  isys_component_dao_result
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function get_profile($p_id = null)
    {
        $l_sql = 'SELECT * FROM isys_visualization_profile WHERE TRUE';

        if ($p_id !== null)
        {
            if (!is_array($p_id))
            {
                $p_id = [$p_id];
            } // if

            $l_sql .= ' AND isys_visualization_profile__id ' . $this->prepare_in_condition($p_id);
        } // if

        return $this->retrieve($l_sql);
    } // function

    /**
     * Returns the bare profile configuration.
     *
     * @param   integer $p_id
     *
     * @return  array
     * @throws  Exception
     */
    public function get_profile_config($p_id)
    {
        if (!($p_id > 0))
        {
            return [];
        } // if

        $l_profile = $this->get_profile($p_id)
            ->get_row();

        return isys_format_json::decode($l_profile['isys_visualization_profile__config']);
    } // function

    /**
     * @param   integer $p_id
     * @param   array   $p_data
     *
     * @return  boolean
     */
    public function save_profile($p_id = null, array $p_data = [])
    {
        $l_data = [];

        foreach ($p_data as $l_key => $l_value)
        {
            if (in_array($l_key, $this->m_fields))
            {
                $l_data[] = $l_key . ' = ' . $this->convert_sql_text($l_value);
            } // if
        } // foreach

        if ($p_id > 0)
        {
            $l_sql = 'UPDATE isys_visualization_profile SET %s WHERE isys_visualization_profile__id = ' . $this->convert_sql_id($p_id) . ';';
        }
        else
        {
            $l_sql = 'INSERT INTO isys_visualization_profile SET %s;';
        } // if

        if (count($l_data))
        {
            return $this->update(sprintf($l_sql, implode(', ', $l_data))) && $this->apply_update();
        } // if

        return false;
    } // function

    /**
     * Method for deleting a given profile.
     *
     * @param   integer $p_id
     * @param   boolean $p_force
     *
     * @return  boolean
     * @throws  Exception
     * @throws  isys_exception_database
     * @throws  isys_exception_general
     */
    public function delete_profile($p_id, $p_force = false)
    {
        $l_profile_const = $this->get_profile($p_id)
            ->get_row_value('isys_visualization_profile__const');

        if (!empty($l_profile_const) && !$p_force)
        {
            throw new isys_exception_general('This profile can not be deleted!');
        } // if

        return $this->update('DELETE FROM isys_visualization_profile WHERE isys_visualization_profile__id = ' . $this->convert_sql_id($p_id) . ';') && $this->apply_update();
    } // function

    /**
     * Method for duplicating a profile.
     *
     * @param   integer $p_id
     *
     * @return  integer
     * @throws  isys_exception_database
     */
    public function duplicate_profile($p_id)
    {
        $l_profile = $this->get_profile($p_id)
            ->get_row();

        $l_profile['isys_visualization_profile__title'] = _L('LC__VISUALIZATION_PROFILES__DUPLICATE') . ': ' . _L($l_profile['isys_visualization_profile__title']);

        // We don't need these two fields.
        unset($l_profile['isys_visualization_profile__id'], $l_profile['isys_visualization_profile__const']);

        if ($this->save_profile(null, $l_profile))
        {
            return $this->get_last_insert_id();
        } // if

        throw new isys_exception_database($this->m_last_error);
    } // function

    /**
     * Method for getting profile option content.
     *
     * @param   isys_tree|isys_tree_node $p_node
     * @param   string                   $p_option
     *
     * @return  mixed
     */
    public function get_profile_options_content($p_node, $p_option)
    {
        $l_node_data = $p_node->get_data();
        $l_obj_id    = $l_node_data['data']['obj_id'];

        if ($l_node_data['data']['obj_type_id'] == -1)
        {
            // Objects with "obj_type_id" == -1 are doubled objects. They need no data.
            return null;
        } // if

        if (isset($this->m_content_cache[$l_obj_id]) && isset($this->m_content_cache[$l_obj_id][$p_option]))
        {
            return $this->m_content_cache[$l_obj_id][$p_option];
        } // if

        $l_empty_value = isys_tenantsettings::get('gui.empty_value', '-');
        $l_return      = null;

        switch ($p_option)
        {
            default:
            case C__VISUALIZATION_PROFILE__OBJ_TITLE:
                $l_return = $l_node_data['data']['obj_title'];
                break;

            case C__VISUALIZATION_PROFILE__OBJ_ID:
                $l_return = '#' . $l_obj_id;
                break;

            case C__VISUALIZATION_PROFILE__OBJ_SYS_ID:
                $l_return = $this->retrieve('SELECT isys_obj__sysid FROM isys_obj WHERE isys_obj__id = ' . $this->convert_sql_id($l_obj_id) . ';')
                    ->get_row_value('isys_obj__sysid');
                break;

            case C__VISUALIZATION_PROFILE__OBJ_TYPE_TITLE:
            case C__VISUALIZATION_PROFILE__OBJ_TYPE_TITLE_ICON:
                $l_return = $l_node_data['data']['obj_type_title'];
                break;

            case C__VISUALIZATION_PROFILE__OBJ_TITLE_CMDB_STATUS:
                $l_sql = 'SELECT isys_obj__title, isys_cmdb_status__color FROM isys_obj
 					LEFT JOIN isys_cmdb_status ON isys_cmdb_status__id = isys_obj__isys_cmdb_status__id
					WHERE isys_obj__id = ' . $this->convert_sql_id($l_obj_id) . ';';

                $l_row = $this->retrieve($l_sql)
                    ->get_row();

                $l_return = [
                    'obj-title'  => _L($l_row['isys_obj__title']),
                    'cmdb-color' => '#' . $l_row['isys_cmdb_status__color']
                ];
                break;

            case C__VISUALIZATION_PROFILE__OBJ_TITLE_TYPE_TITLE_ICON_CMDB_STATUS:
                $l_sql = 'SELECT isys_cmdb_status__color FROM isys_obj
 					LEFT JOIN isys_cmdb_status ON isys_cmdb_status__id = isys_obj__isys_cmdb_status__id
					WHERE isys_obj__id = ' . $this->convert_sql_id($l_obj_id) . ';';

                $l_row = $this->retrieve($l_sql)
                    ->get_row();

                $l_return = [
                    'obj-title'      => $l_node_data['data']['obj_title'],
                    'obj-type-title' => $l_node_data['data']['obj_type_title'],
                    'cmdb-color'     => '#' . $l_row['isys_cmdb_status__color']
                ];
                break;

            case C__VISUALIZATION_PROFILE__CMDB_STATUS:
                $l_sql = 'SELECT isys_cmdb_status__title, isys_cmdb_status__color FROM isys_obj
 					LEFT JOIN isys_cmdb_status ON isys_cmdb_status__id = isys_obj__isys_cmdb_status__id
					WHERE isys_obj__id = ' . $this->convert_sql_id($l_obj_id) . ';';

                $l_row = $this->retrieve($l_sql)
                    ->get_row();

                $l_return = [
                    'color' => '#' . $l_row['isys_cmdb_status__color'],
                    'title' => _L($l_row['isys_cmdb_status__title'])
                ];
                break;

            case C__VISUALIZATION_PROFILE__PRIMARY_IP:
                $l_sql = 'SELECT isys_cats_net_ip_addresses_list__title FROM isys_catg_ip_list
					LEFT JOIN isys_cats_net_ip_addresses_list ON isys_cats_net_ip_addresses_list__id = isys_catg_ip_list__isys_cats_net_ip_addresses_list__id
					WHERE isys_catg_ip_list__isys_obj__id =' . $this->convert_sql_id($l_obj_id) . '
					AND isys_catg_ip_list__primary = 1 LIMIT 1;';

                $l_return = $this->retrieve($l_sql)
                    ->get_row_value('isys_cats_net_ip_addresses_list__title') ?: $l_empty_value;
                break;

            case C__VISUALIZATION_PROFILE__PRIMARY_HOSTNAME:
                $l_sql = 'SELECT isys_catg_ip_list__hostname FROM isys_catg_ip_list
					LEFT JOIN isys_cats_net_ip_addresses_list ON isys_cats_net_ip_addresses_list__id = isys_catg_ip_list__isys_cats_net_ip_addresses_list__id
					WHERE isys_catg_ip_list__isys_obj__id =' . $this->convert_sql_id($l_obj_id) . '
					AND isys_catg_ip_list__primary = 1 LIMIT 1;';

                $l_return = $this->retrieve($l_sql)
                    ->get_row_value('isys_catg_ip_list__hostname') ?: $l_empty_value;
                break;

            case C__VISUALIZATION_PROFILE__PRIMARY_HOSTNAME_FQDN:
                $l_sql = 'SELECT * FROM isys_catg_ip_list ip
					LEFT JOIN isys_cats_net_ip_addresses_list iplist ON iplist.isys_cats_net_ip_addresses_list__id = ip.isys_catg_ip_list__isys_cats_net_ip_addresses_list__id
					LEFT JOIN isys_catg_ip_list_2_isys_net_dns_domain dns2ip ON dns2ip.isys_catg_ip_list__id = ip.isys_catg_ip_list__id
					LEFT JOIN isys_net_dns_domain dns ON dns.isys_net_dns_domain__id = dns2ip.isys_net_dns_domain__id
					WHERE ip.isys_catg_ip_list__isys_obj__id = ' . $this->convert_sql_id($l_obj_id) . '
					AND ip.isys_catg_ip_list__primary = 1 LIMIT 1;';

                $l_row = $this->retrieve($l_sql)
                    ->get_row();

                if (is_array($l_row) && isset($l_row['isys_catg_ip_list__hostname']) && isset($l_row['isys_net_dns_domain__title']))
                {
                    $l_return = $l_row['isys_catg_ip_list__hostname'] . '.' . $l_row['isys_net_dns_domain__title'];
                    break;
                } // if

                $l_return = $l_empty_value;
                break;

            case C__VISUALIZATION_PROFILE__CATEGORY:
                $l_sql = 'SELECT isys_catg_global_category__title FROM isys_catg_global_list
					LEFT JOIN isys_catg_global_category ON isys_catg_global_category__id = isys_catg_global_list__isys_catg_global_category__id
					WHERE isys_catg_global_list__isys_obj__id = ' . $this->convert_sql_id($l_obj_id) . ' LIMIT 1;';

                $l_return = _L(
                    $this->retrieve($l_sql)
                        ->get_row_value('isys_catg_global_category__title')
                ) ?: $l_empty_value;
                break;

            case C__VISUALIZATION_PROFILE__PURPOSE:
                $l_sql = 'SELECT isys_purpose__title FROM isys_catg_global_list
					LEFT JOIN isys_purpose ON isys_purpose__id = isys_catg_global_list__isys_purpose__id
					WHERE isys_catg_global_list__isys_obj__id = ' . $this->convert_sql_id($l_obj_id) . ' LIMIT 1;';

                $l_return = _L(
                    $this->retrieve($l_sql)
                        ->get_row_value('isys_purpose__title')
                ) ?: $l_empty_value;
                break;

            case C__VISUALIZATION_PROFILE__PRIMARY_CONTACT:
                $l_person_row = isys_cmdb_dao_category_g_contact::instance($this->m_db)
                    ->get_contacts_by_obj_id($l_obj_id, true)
                    ->get_row();

                if (is_array($l_person_row))
                {
                    $l_return = $l_person_row['isys_cats_person_list__first_name'] . ' ' . $l_person_row['isys_cats_person_list__last_name'] . (!empty($l_person_row['isys_contact_tag__title']) ? ' (' . $l_person_row['isys_contact_tag__title'] . ')' : '');
                    break;
                } // if

                $l_return = $l_empty_value;
                break;

            case C__VISUALIZATION_PROFILE__PRIMARY_ACCESS_URL:
                $l_access_row = isys_cmdb_dao_category_g_access::instance($this->m_db)
                    ->get_primary_element($l_obj_id)
                    ->get_row();

                if (is_array($l_access_row))
                {
                    $l_return = $l_access_row['isys_catg_access_list__url'];
                    break;
                } // if

                $l_return = $l_empty_value;
                break;

            case C__VISUALIZATION_PROFILE__RELATION_TYPE:
                $l_return = $l_empty_value;

                if ($l_node_data['data']['relation_obj_id'] > 0)
                {
                    $l_return = _L(
                        isys_cmdb_dao_category_s_relation_details::instance($this->m_db)
                            ->get_data(null, $l_node_data['data']['relation_obj_id'])
                            ->get_row_value('isys_relation_type__title')
                    );
                } // if

                break;
        } // switch

        return $this->m_content_cache[$l_obj_id][$p_option] = $l_return;
    } // function
} // class