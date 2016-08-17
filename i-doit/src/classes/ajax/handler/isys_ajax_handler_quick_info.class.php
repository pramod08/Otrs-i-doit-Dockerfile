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
use idoit\Component\Helper\Ip;

/**
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_ajax_handler_quick_info extends isys_ajax_handler
{
    /**
     * Variable which holds an CSS class.
     *
     * @var  string
     */
    private $m_class;
    /**
     * Array, which holds the information we want to display.
     *
     * @var  array
     */
    private $m_info = [];
    /**
     * Variable which holds the CSS styles.
     *
     * @var  string
     */
    private $m_style;

    /**
     * Setter method for the CSS class.
     *
     * @param   string $p_class
     *
     * @return  isys_ajax_handler_quick_info
     */
    public function set_class($p_class)
    {
        $this->m_class = $p_class;

        return $this;
    } // function

    /**
     * Setter method for the CSS styling.
     *
     * @param   string $p_style
     *
     * @return  isys_ajax_handler_quick_info
     */
    public function set_style($p_style)
    {
        $this->m_style = $p_style;

        return $this;
    } // function

    /**
     * Method for returning the info-array.
     *
     * @return  array
     */
    public function get_info_array()
    {
        return $this->m_info;
    } // function

    /**
     * Returns an html link and its corresponding quick info tooltip handler.
     *
     * @param   integer $p_object_id
     * @param   string  $p_title
     * @param   string  $p_link
     * @param   mixed   $p_str_stop
     * @param   array   $p_gets
     *
     * @return  string
     */
    public function get_quick_info($p_object_id, $p_title, $p_link = 'javascript:void(0);', $p_str_stop = false, $p_gets = [], $p_onclick = '')
    {
        global $g_comp_database;

        $l_aid = 'lb_' . rand(10, 99) . '_' . $p_object_id;

        switch ($p_link)
        {
            case C__LINK__OBJECT:
                $p_link = isys_helper_link::create_url([C__CMDB__GET__OBJECT => $p_object_id]);

                break;
            case C__LINK__CATG:
                $l_get = [
                    C__CMDB__GET__OBJECT   => $p_object_id,
                    C__CMDB__GET__VIEWMODE => C__CMDB__VIEW__CATEGORY_GLOBAL,
                    C__CMDB__GET__CATG     => $p_gets[C__CMDB__GET__CATG]
                ];

                if (isset($p_gets[C__CMDB__GET__TREEMODE]))
                {
                    $l_get[C__CMDB__GET__TREEMODE] = $p_gets[C__CMDB__GET__TREEMODE];
                }
                else
                {
                    $l_get[C__CMDB__GET__TREEMODE] = C__CMDB__VIEW__TREE_OBJECT;
                }

                if (isset($p_gets[C__SEARCH__GET__HIGHLIGHT]))
                {
                    $l_get[C__SEARCH__GET__HIGHLIGHT] = $p_gets[C__SEARCH__GET__HIGHLIGHT];
                }

                if (isset($p_gets[C__CMDB__GET__CATLEVEL]))
                {
                    $l_get[C__CMDB__GET__CATLEVEL] = $p_gets[C__CMDB__GET__CATLEVEL];
                }

                if (isset($p_gets[C__CMDB__GET__CATG_CUSTOM]))
                {
                    $l_get[C__CMDB__GET__CATG_CUSTOM] = $p_gets[C__CMDB__GET__CATG_CUSTOM];
                }

                $p_link = isys_helper_link::create_url($l_get);
                unset($l_get);

                // @todo  Find an easier way to handle contacts.
                if (isset($p_gets['C__CONTACT_PERSON_LINK']))
                {
                    $p_link = '?' . $p_gets['C__CONTACT_PERSON_LINK'];
                }

                break;
            case C__LINK__CATS:
                $l_get = [
                    C__CMDB__GET__OBJECT   => $p_object_id,
                    C__CMDB__GET__VIEWMODE => C__CMDB__VIEW__CATEGORY_GLOBAL,
                    C__CMDB__GET__CATS     => $p_gets[C__CMDB__GET__CATS]
                ];

                if (isset($p_gets[C__CMDB__GET__TREEMODE]))
                {
                    $l_get[C__CMDB__GET__TREEMODE] = $p_gets[C__CMDB__GET__TREEMODE];
                }
                else
                {
                    $l_get[C__CMDB__GET__TREEMODE] = C__CMDB__VIEW__TREE_OBJECT;
                } // if

                $l_get[C__CMDB__GET__CATLEVEL] = $p_gets[C__CMDB__GET__CATLEVEL];

                $p_link = isys_helper_link::create_url($l_get);
                unset($l_get);

                break;
        } // switch

        if (isys_usersettings::get('gui.quickinfo.active', 1))
        {
            return $this->get_link($l_aid, $p_title, $p_link, $p_str_stop, $p_onclick) . $this->get_script($l_aid, $p_object_id);
        }
        else
        {
            return $this->get_link(false, $p_title, $p_link, $p_str_stop, $p_onclick);
        } // if
    } // function

    /**
     * Returns a quick-link conform link.
     *
     * @param   string $p_aid
     * @param   string $p_title
     * @param   string $p_link
     * @param   mixed  $p_str_stop Boolean false or integer for "allowed characters".
     *
     * @return  string
     */
    public function get_link($p_aid, $p_title, $p_link = 'javascript:', $p_str_stop = false, $p_onclick = '')
    {
        $l_id = $l_class = $l_onclick = $l_style = '';

        if ($p_aid)
        {
            $l_id = ' id="' . $p_aid . '"';
        } // if

        if ($this->m_class)
        {
            $l_class = ' class="' . $this->m_class . '"';
        } // if

        if ($this->m_style)
        {
            $l_style = ' style="' . $this->m_style . '"';
        } // if

        if ($p_str_stop)
        {
            $p_title = isys_glob_str_stop($p_title, $p_str_stop);
        } // if

        if ($p_onclick)
        {
            $l_onclick = ' onclick="' . $p_onclick . '"';
        }

        return '<a name="' . str_replace('"', '', $p_title) . '" href="' . $p_link . '"' . $l_id . $l_class . $l_style . $l_onclick . '>' . $p_title . '</a>';
    } // function

    /**
     * $p_aid = ID of your DOM object
     * $p_object_id = isys_obj ID
     * $p_category = array of categories to display (only general information are displayed for each category)
     *
     * @param int   $p_aid
     * @param int   $p_object_id
     * @param array $p_categories
     *
     * @return string
     */
    public function get_script($p_aid, $p_object_id, $p_include_html_script = true)
    {
        global $g_config, $g_current_theme;

        if (isys_usersettings::get('gui.quickinfo.active', 1))
        {
            $l_delay = isys_usersettings::get('gui.quickinfo.delay', 0);
            if ($l_delay < 0 || !is_numeric($l_delay))
            {
                $l_delay = '0.5';
            }

            $l_script = '';
            if ($p_include_html_script)
            {
                $l_script .= '<script type=\'text/javascript\'>';
            }

            $l_script .= "if ($('" . $p_aid . "')) " . "new Tip('" . $p_aid . "', '', {" . "ajax: {" . "url: '" . $g_config["www_dir"] . "?ajax=1&call=quick_info&" .
                C__CMDB__GET__OBJECT . "=" . $p_object_id . "'" . "}, " . "delay: '" . $l_delay . "', " . "stem: 'topLeft', " . "style: '" . $g_current_theme . "', " .
                "className: 'objectinfo'" . "}" . ");";

            if ($p_include_html_script)
            {
                $l_script .= '</script>';
            }

            return $l_script;
        }

        return '';
    }

    /**
     * Get quick info's content.
     *
     * @param   integer $p_object_id
     * @param   array   $p_catg
     * @param   array   $p_cats
     *
     * @throws  InvalidArgumentException
     * @throws  Exception
     * @return  string
     */
    public function get_quick_info_content($p_object_id, array $p_catg = [], array $p_cats = [], $p_config = [])
    {
        global $g_comp_database, $g_dirs, $g_current_theme, $g_comp_session;

        $p_object_id = (int) $p_object_id;

        $l_cattitle = [];

        if ($p_object_id <= 0)
        {
            throw new Exception('Object ID missing!');
        } // if

        $l_info  = $l_global_categories = $l_specific_categories = [];
        $l_empty = isys_tenantsettings::get('gui.empty_value', '-');
        $l_out   = '';
        $l_cpu   = 0;

        if (count($p_catg) == 0 && count($p_cats) == 0)
        {
            throw new InvalidArgumentException('You should specify at least one category to display!');
        } // if

        foreach ($p_catg as $l_tmp)
        {
            $l_global_categories[$l_tmp] = true;
        } // if

        foreach ($p_cats as $l_tmp)
        {
            $l_specific_categories[$l_tmp] = true;
        } // if

        $l_global_dist   = new isys_cmdb_dao_distributor($g_comp_database, $p_object_id, C__CMDB__CATEGORY__TYPE_GLOBAL, null, $l_global_categories);
        $l_specific_dist = new isys_cmdb_dao_distributor($g_comp_database, $p_object_id, C__CMDB__CATEGORY__TYPE_SPECIFIC, null, $l_specific_categories);

        $l_object_data = $l_global_dist->get_object_by_id($p_object_id)
            ->get_row();

        $l_object_type_name  = _L($l_object_data['isys_obj_type__title']);
        $l_object_type_image = $l_object_data['isys_obj_type__obj_img_name'];

        if (empty($l_object_type_image))
        {
            $l_object_type_image = C__OBJTYPE_IMAGE__DEFAULT;
        } // if

        $l_object_type_image = $g_dirs['images'] . 'objecttypes/' . $l_object_type_image;

        $l_object_image = isys_cmdb_dao_category_g_image::instance($g_comp_database)
            ->get_data(null, $p_object_id)
            ->get_row();

        if ($l_object_image !== false && !empty($l_object_image['isys_catg_image_list__image_link']))
        {
            $l_object_type_image = 'upload/images/' . $l_object_image['isys_catg_image_list__image_link'];
        } // if

        if ($l_global_dist->count() == 0 && $l_specific_dist->count() == 0)
        {
            throw new Exception('No assigned categories found!');
        } // if

        isys_auth_cmdb::instance()
            ->check(
                isys_auth::VIEW,
                'OBJ_ID/' . $p_object_id
            );

        foreach ($p_catg as $l_category)
        {
            if (is_string($l_category) && defined($l_category))
            {
                $l_category = constant($l_category);
            } // if

            $l_cat     = $l_global_dist->get_category($l_category);
            $l_guidata = $l_global_dist->get_guidata($l_category);

            if (!$l_cat)
            {
                continue;
            } // if

            if ($l_category != C__CATG__GLOBAL)
            {
                $l_data = $l_cat->get_data(null, $p_object_id, '', null, C__RECORD_STATUS__NORMAL);
            }
            else
            {
                $l_data = $l_cat->get_data(null, $p_object_id);
            } // if

            $l_cattitle['g' . $l_category] = _L($l_guidata['isysgui_catg__title']);

            while ($l_row = $l_data->get_row())
            {
                // Add your static category information here.

                switch ($l_category)
                {
                    case C__CATG__GLOBAL:

                        $l_info['g' . $l_category]['LC__UNIVERSAL__TITLE']         = stripslashes($l_row['isys_obj__title']);
                        $l_info['g' . $l_category]['LC__UNIVERSAL__CMDB_STATUS']   = '<div class="cmdb-marker" style="background-color:#' . $l_row['isys_cmdb_status__color'] .
                            ';"></div>' . _L($l_row['isys_cmdb_status__title']);
                        $l_info['g' . $l_category]['LC__CMDB__CATG__GLOBAL_SYSID'] = $l_row['isys_obj__sysid'];

                        if ($l_row['isys_obj__isys_obj_type__id'] == C__OBJTYPE__RELATION)
                        {
                            $l_info['g' . $l_category]['LC__CATG__RELATION__RELATION_TYPE'] = _L(
                                isys_cmdb_dao_relation::instance($g_comp_database)
                                    ->get_relation_type_by_object_id($l_row['isys_obj__id'])
                            );
                        }
                        break;

                    case C__CATG__CONTACT:

                        $l_primID  = $l_primType = null;
                        $l_dao_row = $l_cat->contact_get_primary($l_primType, $l_primID);

                        if (count($l_dao_row) > 0)
                        {
                            $l_info['g' . $l_category]['LC__CMDB__CATG__CONTACT'] = $l_dao_row['isys_obj__title'];

                            if ($l_dao_row['isys_cats_person_list__title'])
                            {
                                $l_info['g' . $l_category]['LC__CMDB__CATG__CONTACT'] .= ' (' . $l_dao_row['isys_cats_person_list__title'] . ')';
                            } // if

                            if ($l_dao_row['isys_cats_person_list__phone_company'])
                            {
                                $l_info['g' . $l_category]['LC__CMDB__CATS_CP_CONTRACT__PHONE_NUMBER'] = $l_dao_row['isys_cats_person_list__phone_company'];
                            } // if
                        } // if
                        break;

                    case C__CATG__MODEL:

                        if (!empty($l_row['isys_model_manufacturer__title']))
                        {
                            $l_info['g' . $l_category]['LC__CMDB__CATG__MODEL_MANUFACTURE'] = _L($l_row['isys_model_manufacturer__title']);
                        } // if

                        if (!empty($l_row['isys_model_title__title']))
                        {
                            $l_info['g' . $l_category]['LC__CMDB__CATG__MODEL_TITLE'] = $l_row['isys_model_title__title'];
                        } // if

                        if (!empty($l_row['isys_catg_model_list__serial']))
                        {
                            $l_info['g' . $l_category]['LC__CMDB__CATG__MODEL_SERIAL'] = $l_row['isys_catg_model_list__serial'];
                        } // if
                        break;

                    case C__CATG__CPU:

                        $l_cpu++;
                        $l_cpu_title = $l_row['isys_catg_cpu_list__title'] . ' ' . $l_row['isys_catg_cpu_frequency__title'];

                        $l_info['g' . $l_category]['LC__CMDB__CATG__CPU_TITLE'] = '';

                        if (!empty($l_row['isys_catg_cpu_type__title']))
                        {
                            $l_info['g' . $l_category]['LC__CMDB__CATG__CPU_TYPE'] = $l_row['isys_catg_cpu_type__title'];
                        } // if
                        break;

                    case C__CATG__NETWORK:

                        if (!empty($l_row['isys_catg_netp_list__title']))
                        {
                            $l_info['g' . $l_category]['Interface'][] = $l_row['isys_catg_netp_list__title'];
                        }
                        else
                        {
                            if (!empty($l_row['isys_catg_hba_list__title']))
                            {
                                $l_info['g' . $l_category]['Interface'][] = $l_row['isys_catg_hba_list__title'];
                            }
                        } // if
                        break;

                    case C__CMDB__SUBCAT__NETWORK_PORT:
                        if ($l_row['isys_catg_port_list__id'] && !empty($l_row['isys_catg_port_list__title']))
                        {
                            $l_destination  = $l_empty;
                            $l_cable_dao    = isys_cmdb_dao_cable_connection::instance($g_comp_database);
                            $l_assigned_obj = $l_cable_dao->get_assigned_object($l_row["isys_cable_connection__id"], $l_row["isys_catg_connector_list__id"]);

                            if ($l_assigned_obj > 0)
                            {
                                $l_connector_name = $l_cable_dao->get_assigned_connector_name(
                                    $l_row["isys_catg_port_list__isys_catg_connector_list__id"],
                                    $l_row["isys_cable_connection__id"]
                                );
                                $l_destination    = $l_cat->get_obj_name_by_id_as_string($l_assigned_obj);

                                if (!empty($l_connector_name))
                                {
                                    $l_destination .= ' (' . $l_connector_name . ')';
                                } // if
                            } // if

                            $l_info['g' . $l_category][$l_row['isys_catg_port_list__title']] = $l_destination;
                        } // if
                        break;

                    case C__CATG__VERSION:

                        if (!empty($l_row['isys_catg_version_list__servicepack']))
                        {
                            $l_info['g' . $l_category]['LC__CATG__SERVICEPACK'] = $l_row['isys_catg_version_list__servicepack'];
                        } // if

                        if (!empty($l_row['isys_catg_version_list__hotfix']))
                        {
                            $l_info['g' . $l_category]['LC__CATG__PATCHES'] = $l_row['isys_catg_version_list__hotfix'];
                        } // if
                        break;
                } // switch
            } // while

            unset($l_row);
        } // foreach

        foreach ($p_cats as $l_category)
        {
            if (is_string($l_category) && defined($l_category))
            {
                $l_category = constant($l_category);
            } // if

            $l_cat     = $l_specific_dist->get_category($l_category);
            $l_guidata = $l_specific_dist->get_guidata($l_category);

            if (!$l_cat)
            {
                continue;
            } // if

            $l_data = $l_cat->get_data(null, $p_object_id, '', null, C__RECORD_STATUS__NORMAL);

            $l_cattitle['s' . $l_category] = _L($l_guidata['isysgui_cats__title']);

            while ($l_row = $l_data->get_row())
            {
                switch ($l_category)
                {
                    case C__CATS__NET:

                        // Display net address range and stuff.
                        if (!empty($l_row['isys_cats_net_list__address']) && !empty($l_row['isys_cats_net_list__cidr_suffix']))
                        {
                            if ($l_row['isys_cats_net_list__isys_net_type__id'] == C__CATS_NET_TYPE__IPV6)
                            {
                                $l_row['isys_cats_net_list__address'] = Ip::validate_ipv6($l_row['isys_cats_net_list__address'], true);
                            } // if

                            $l_info['s' . $l_category]['LC__CMDB__CATS__NET_IP_ADDRESSES__NETADDRESS'] = $l_row['isys_cats_net_list__address'] . ' /' .
                                $l_row['isys_cats_net_list__cidr_suffix'];
                        } // if

                        if (!empty($l_row['isys_cats_net_list__address_range_from']) && !empty($l_row['isys_cats_net_list__address_range_to']))
                        {
                            if ($l_row['isys_cats_net_list__isys_net_type__id'] == C__CATS_NET_TYPE__IPV6)
                            {
                                $l_row['isys_cats_net_list__address_range_from'] = Ip::validate_ipv6($l_row['isys_cats_net_list__address_range_from'], true);
                                $l_row['isys_cats_net_list__address_range_to']   = Ip::validate_ipv6($l_row['isys_cats_net_list__address_range_to'], true);
                            } // if

                            $l_info['s' . $l_category]['LC__CMDB__CATS__NET__ADDRESS_RANGE'] = $l_row['isys_cats_net_list__address_range_from'] . ' - ' .
                                $l_row['isys_cats_net_list__address_range_to'];
                        } // if
                        break;

                    case C__CATS__CONTRACT:

                        // Somehow "global $g_loc;" does not work...
                        $l_locale = isys_locale::get($g_comp_database, $g_comp_session->get_user_id());

                        if (!empty($l_row['isys_cats_contract_list__contract_no']))
                        {
                            $l_info['s' . $l_category]['LC__CMDB__CATS__MAINTENANCE_CONTRACT_NUMBER'] = $l_row['isys_cats_contract_list__contract_no'];
                        } // if

                        if ((int) $l_row['isys_contract_type__id'] > 0)
                        {
                            $l_info['s' . $l_category]['LC__CMDB__CATS__CONTRACT__TYPE'] = _L($l_row['isys_contract_type__title']);
                        } // if

                        if (strtotime($l_row['isys_cats_contract_list__start_date']) > 0)
                        {
                            $l_info['s' . $l_category]['LC__CMDB__CATS__CONTRACT__START_DATE'] = $l_locale->fmt_date($l_row['isys_cats_contract_list__start_date']);
                        } // if

                        if (strtotime($l_row['isys_cats_contract_list__end_date']) > 0)
                        {
                            $l_info['s' . $l_category]['LC__CMDB__CATS__CONTRACT__END_DATE'] = $l_locale->fmt_date($l_row['isys_cats_contract_list__end_date']);
                        } // if

                        if ($l_row['isys_cats_contract_list__runtime'] > 0 || $l_row['isys_cats_contract_list__runtime_unit'] > 0)
                        {
                            $l_unit                                                        = isys_factory_cmdb_dialog_dao::get_instance(
                                'isys_guarantee_period_unit',
                                $g_comp_database
                            )
                                ->get_data($l_row['isys_cats_contract_list__runtime_unit']);
                            $l_info['s' . $l_category]['LC__CMDB__CATG__LEASING__RUNTIME'] = $l_row['isys_cats_contract_list__runtime'] . ' ' .
                                _L($l_unit['isys_guarantee_period_unit__title']);
                        } // if
                        break;
                } // switch
            } // while

            unset($l_row);
        } // foreach

        // CPU Amount
        if ($l_cpu > 0 && !empty($l_cpu_title) && $l_cpu_title != ' ')
        {
            $l_info['g' . C__CATG__CPU]['LC__CMDB__CATG__CPU_TITLE'] = $l_cpu . 'x ' . $l_cpu_title;
        }
        else
        {
            unset($l_info['g' . C__CATG__CPU]);
        } // if

        // IP-Addresses.
        $l_ip_dao = new isys_cmdb_dao_category_g_ip($g_comp_database);
        $l_ips    = $l_ip_dao->get_primary_ip($p_object_id);

        while ($l_ip = $l_ips->get_row())
        {
            $l_info['g' . C__CATG__NETWORK]['LC__CATP__IP__ADDRESS'][]     = $l_ip['isys_cats_net_ip_addresses_list__title'];
            $l_info['g' . C__CATG__NETWORK]['LC__CMDB__CATS__NET__MASK'][] = $l_ip['isys_cats_net_list__mask'];
        } // while

        /* -------------------------------------------------------------------------------------------- */
        /* Prepare quick info content generation */
        /* -------------------------------------------------------------------------------------------- */
        $l_out .= '<div class="container">';

        if (!isset($p_config['hideCloseButton']) || !$p_config['hideCloseButton'])
        {
            $l_out .= '<a href="javascript:" onclick="Tips.hideAll();" class="fr m5"><img src="' . $g_dirs['images'] . '/prototip/styles/' . $g_current_theme .
                '/close.png" alt="x" /></a>';
        }

        $l_out .= '<h3 class="ot"><img src="' . $l_object_type_image . '" /> ' . $l_object_type_name . ' (' .
            $l_global_dist->get_record_status_as_string($l_object_data['isys_obj__status']) . ')</h3>';

        if (is_array($l_info))
        {
            if (isset($p_config['maxLen']))
            {
                $l_maxLen = $p_config['maxLen'];
            }
            else
            {
                $l_maxLen = 50;
            } // if

            foreach ($l_info as $l_category => $l_data)
            {
                $l_out .= '<h4 class="category bold">' . $l_cattitle[$l_category] . '</h4>' . '<table>' . '<colgroup><col width="85" /></colgroup>' . '<tbody>';

                foreach ($l_data as $l_key => $l_value)
                {
                    if ($l_value == '' || is_null($l_value))
                    {
                        $l_value = isys_tenantsettings::get('gui.empty_value', '-');
                    } // if

                    if (is_array($l_value))
                    {
                        $l_value = implode(', ', $l_value);
                    } // if

                    if ($l_key == 'LC__UNIVERSAL__TITLE')
                    {
                        if (isset($p_config['createObjectLink']) && $p_config['createObjectLink'])
                        {
                            $l_value = '<a href="?' . C__CMDB__GET__OBJECT . '=' . $p_object_id . '" class="red">' . $l_value . '</a>';
                        }
                        else
                        {
                            $l_value = '<u>' . $l_value . '</u>';
                        }
                    } // if

                    // The "max length" should not be used, when displaying HTML because this can break the output.
                    if (!in_array($l_key, ['LC__UNIVERSAL__CMDB_STATUS']))
                    {
                        $l_value = isys_glob_str_stop($l_value, $l_maxLen);
                    } // if

                    $l_out .= '<tr><td class="bold key">' . _L($l_key) . '</td><td>' . $l_value . '</td></tr>';
                } // foreach

                $l_out .= '</tbody></table>';
            } // foreach
        } // if

        $l_out .= '</div>';

        $this->m_info = $l_info;

        // Create cache.
        $l_global_dist->update('DELETE FROM isys_cache_qinfo WHERE isys_cache_qinfo__isys_obj__id = ' . $l_global_dist->convert_sql_id($p_object_id) . ';');
        $l_global_dist->update(
            'INSERT INTO isys_cache_qinfo SET ' . 'isys_cache_qinfo__data = ' . $l_global_dist->convert_sql_text($l_out) . ', ' . 'isys_cache_qinfo__expiration = ' .
            $l_global_dist->convert_sql_int(
                time() + ((int) isys_tenantsettings::get(
                    'cache.quickinfo.expiration',
                    isys_convert::DAY
                ))
            ) . ',' . 'isys_cache_qinfo__isys_obj__id = ' . $l_global_dist->convert_sql_id($p_object_id) . ';'
        );

        $l_global_dist->apply_update();

        return $l_out;
    } // function

    /**
     *
     *
     * @param   integer $p_object_id
     *
     * @return  mixed
     */
    public function quickinfo($p_object_id)
    {
        // Check for existing cache.
        if (($l_cache = $this->get_cache($p_object_id)))
        {
            return $l_cache;
        }
        else
        {
            return $this->get_quick_info_content(
                $p_object_id,
                [
                    C__CATG__GLOBAL,
                    C__CATG__CONTACT,
                    C__CATG__MODEL,
                    C__CATG__CPU,
                    C__CATG__NETWORK,
                    C__CATG__VERSION,
                    C__CMDB__SUBCAT__NETWORK_PORT
                ],
                [
                    C__CATS__NET,
                    C__CATS__CONTRACT
                ]
            );
        } // if
    } // function

    /**
     * Initialization method.
     */
    public function init()
    {
        global $g_comp_session;

        // Enable cache lifetime of 1 hour.
        isys_core::expire(isys_convert::HOUR);

        $g_comp_session->write_close();

        try
        {
            echo $this->quickinfo($_GET[C__CMDB__GET__OBJECT]);
        }
        catch (isys_exception_database $e)
        {
            echo 'Database error: ' . $e->getMessage();
        }
        catch (Exception $e)
        {
            echo 'General error: ' . $e->getMessage();
        } // function

        $this->_die();
    } // function

    /**
     * Method for retrieving the quickinfo-cache as string. If it does not exist, returns false.
     *
     * @param   integer $p_object_id
     *
     * @return  mixed
     */
    private function get_cache($p_object_id)
    {
        global $g_comp_database;

        $l_dao = isys_cmdb_dao::instance($g_comp_database);
        $l_sql = 'SELECT isys_cache_qinfo__data AS cache
			FROM isys_cache_qinfo
			WHERE isys_cache_qinfo__isys_obj__id = ' . $l_dao->convert_sql_id($p_object_id) . '
			AND isys_cache_qinfo__expiration >= ' . $l_dao->convert_sql_int(time()) . '
			LIMIT 1';

        $l_data = $l_dao->retrieve($l_sql)
            ->get_row_value('cache');

        return ($l_data !== null) ? $l_data : false;
    } // function

    /**
     * Constructor.
     *
     * @param  array $p_get
     * @param  array $p_post
     */
    public function __construct($p_get = null, $p_post = null)
    {
        if (!$p_get)
        {
            $p_get = $_GET;
        } // if

        if (!$p_post)
        {
            $p_post = $_POST;
        } // if

        $this->m_get  = $p_get;
        $this->m_post = $p_post;
    } // function
} // class