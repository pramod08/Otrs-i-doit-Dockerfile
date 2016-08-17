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
 * Visualization ajax handler class.
 *
 * @package     modules
 * @subpackage  pro
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.5.0
 */
class isys_ajax_handler_visualization extends isys_ajax_handler
{
    /**
     * Init method, which gets called from the framework.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function init()
    {
        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json');

        $l_return = [
            'success' => true,
            'data'    => null,
            'message' => null
        ];

        try
        {
            switch ($_GET['func'])
            {
                case 'load-it-services-by-type':
                    $l_return['data'] = $this->load_it_services_by_type($_POST['type']);
                    break;

                case 'load-object-infobox':
                    $l_return['data'] = $this->load_object_infobox_data($_POST['object'], $_POST['profile-id'], $_POST['relation']);
                    break;

                case 'load-profiles-for-dialog':
                    $l_return['data'] = $this->load_profiles_for_dialog($_POST['type'] ?: C__CMDB__VISUALIZATION_TYPE__TREE);
                    break;

                case 'load-profile-config':
                    $l_return['data'] = $this->load_profile_config($_POST['profile-id']);
                    break;

                case 'save-profile-config':
                    $l_return['data'] = $this->save_profile_config($_POST['profile-id'], isys_format_json::decode($_POST['profile-config']));
                    break;

                case 'delete-profile':
                    $l_return['data'] = $this->delete_profile($_POST['profile-id']);
                    break;

                case 'duplicate-profile':
                    $l_return['data'] = $this->duplicate_profile($_POST['profile-id']);
                    break;

                case 'set-profile-as-default':
                    $l_return['data'] = $this->default_profile($_POST['profile-id']);
                    break;
            } // switch
        }
        catch (Exception $e)
        {
            $l_return['success'] = false;
            $l_return['message'] = $e->getMessage();
        }

        echo isys_format_json::encode($l_return);

        $this->_die();
    } // function

    /**
     * Method for retrieving all IT-Services of a given type.
     *
     * @param   integer $p_type
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function load_it_services_by_type($p_type = null)
    {
        $l_return = [];
        $l_dao    = new isys_cmdb_dao_category_g_its_type($this->m_database_component);

        $l_res = $l_dao->get_services_by_type(($p_type == -1) ? null : $p_type);

        if (count($l_res))
        {
            while ($l_row = $l_res->get_row())
            {
                $l_return[$l_row['isys_obj__id']] = $l_row['isys_obj__title'];
            } // while
        } // if

        uasort($l_return, 'strcasecmp');

        return $l_return;
    } // function

    /**
     * Method for retrieving the infobox data.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_profile
     * @param   integer $p_relation_obj_id
     *
     * @return  array
     */
    protected function load_object_infobox_data($p_obj_id, $p_profile, $p_relation_obj_id = null)
    {
        return isys_factory::get_instance('isys_visualization_model', $this->m_database_component)
            ->load_object_infobox_data($p_obj_id, $p_profile, $p_relation_obj_id);
    } // function

    /**
     * Method for reloading the profile dialog data.
     *
     * @param   string $p_type
     *
     * @return  array
     */
    protected function load_profiles_for_dialog($p_type = C__CMDB__VISUALIZATION_TYPE__TREE)
    {
        $l_profiles = [];
        $l_res      = isys_factory::get_instance('isys_visualization_profile_model', $this->m_database_component)
            ->get_profile();

        if (count($l_res))
        {
            while ($l_row = $l_res->get_row())
            {
                // Only display profiles, which are not blacklisted for the given type.
                if (strpos($l_row['isys_visualization_profile__type_blacklist'], $p_type) !== false)
                {
                    continue;
                } // if

                $l_profiles[$l_row['isys_visualization_profile__id']] = _L($l_row['isys_visualization_profile__title']);
            } // while
        } // if

        return $l_profiles;
    } // fnuction

    /**
     * Method for retrieving profile configuration.
     *
     * @param   integer $p_profile
     *
     * @return  array
     */
    protected function load_profile_config($p_profile = null)
    {
        if ($p_profile === null)
        {
            return [];
        } // if

        return isys_factory::get_instance('isys_visualization_profile_model', $this->m_database_component)
            ->get_profile($p_profile)
            ->get_row();
    } // function

    /**
     * Method for saving profile configuration.
     *
     * @param   integer $p_profile
     * @param   array   $p_profile_config
     *
     * @return  mixed
     */
    protected function save_profile_config($p_profile = null, array $p_profile_config = [])
    {
        $l_defaults = [
            'orientation'    => $p_profile_config['C__VISUALIZATION_PROFILES__DEFAULT_ORIENTATION'],
            'service-filter' => $p_profile_config['C__VISUALIZATION_PROFILES__DEFAULT_SERVICE_FILTER']
        ];

        if (isset($p_profile_config['C__VISUALIZATION_PROFILES__DEFAULT_OBJECT_TYPE_FILTER__selected_values']) && !empty($p_profile_config['C__VISUALIZATION_PROFILES__DEFAULT_OBJECT_TYPE_FILTER__selected_values']))
        {
            $l_defaults['obj-type-filter'] = explode(',', $p_profile_config['C__VISUALIZATION_PROFILES__DEFAULT_OBJECT_TYPE_FILTER__selected_values']);
        }
        else if (isset($p_profile_config['C__VISUALIZATION_PROFILES__DEFAULT_OBJECT_TYPE_FILTER__selected_box']) && !empty($p_profile_config['C__VISUALIZATION_PROFILES__DEFAULT_OBJECT_TYPE_FILTER__selected_box']))
        {
            if (is_array($p_profile_config['C__VISUALIZATION_PROFILES__DEFAULT_OBJECT_TYPE_FILTER__selected_box']))
            {
                $l_defaults['obj-type-filter'] = $p_profile_config['C__VISUALIZATION_PROFILES__DEFAULT_OBJECT_TYPE_FILTER__selected_box'];
            }
            else if (isys_format_json::is_json_array($p_profile_config['C__VISUALIZATION_PROFILES__DEFAULT_OBJECT_TYPE_FILTER__selected_box']))
            {
                $l_defaults['obj-type-filter'] = isys_format_json::decode($p_profile_config['C__VISUALIZATION_PROFILES__DEFAULT_OBJECT_TYPE_FILTER__selected_box']);
            } // if
        } // if

        $l_obj_info          = $p_profile_config['C__VISUALIZATION_PROFILES__OBJ_INFO_CONFIG'];
        $l_obj_info['query'] = isys_cmdb_dao_category_property::instance($this->m_database_component)
            ->set_query_as_report(true)
            ->create_property_query_for_report(1, $p_profile_config['C__VISUALIZATION_PROFILES__OBJ_INFO_CONFIG__COMPLETE'], true, ' AND obj_main.isys_obj__id = %s ');

        $p_profile_config['C__VISUALIZATION_PROFILES__OBJ_INFO_CONFIG__COMPLETE'];

        $l_config = [
            'width'           => ($p_profile_config['C__VISUALIZATION_PROFILES__WIDTH'] ?: 120),
            'master_top'      => (bool) $p_profile_config['C__VISUALIZATION_PROFILES__MASTER_TOP'],
            'highlight-color' => $p_profile_config['C__VISUALIZATION_PROFILES__HIGHLIGHT_COLOR'],
            'show-cmdb-path'  => (bool) $p_profile_config['C__VISUALIZATION_PROFILES__SHOW_PATH'],
            'tooltip'         => (bool) $p_profile_config['C__VISUALIZATION_PROFILES__SHOW_TOOLTIP'],
            'rows'            => []
        ];

        for ($i = 1;$i <= 8;$i++)
        {
            if ($p_profile_config['C__VISUALIZATION_PROFILES__R' . $i . '__ROW'] == 'on')
            {
                $l_row = [
                    'fontcolor' => $p_profile_config['C__VISUALIZATION_PROFILES__R' . $i . '__FONTCOLOR'],
                    'option'    => $p_profile_config['C__VISUALIZATION_PROFILES__R' . $i . '__OPTION']
                ];

                // Font styles.
                if ($p_profile_config['C__VISUALIZATION_PROFILES__R' . $i . '__FONT_BOLD'])
                {
                    $l_row['font-bold'] = $p_profile_config['C__VISUALIZATION_PROFILES__R' . $i . '__FONT_BOLD'];
                } // if

                if ($p_profile_config['C__VISUALIZATION_PROFILES__R' . $i . '__FONT_ITALIC'])
                {
                    $l_row['font-italic'] = $p_profile_config['C__VISUALIZATION_PROFILES__R' . $i . '__FONT_ITALIC'];
                } // if

                if ($p_profile_config['C__VISUALIZATION_PROFILES__R' . $i . '__FONT_UNDERLINE'])
                {
                    $l_row['font-underline'] = $p_profile_config['C__VISUALIZATION_PROFILES__R' . $i . '__FONT_UNDERLINE'];
                } // if

                // Text alignment.
                if ($p_profile_config['C__VISUALIZATION_PROFILES__R' . $i . '__FONT_ALIGN_MIDDLE'])
                {
                    $l_row['font-align-middle'] = $p_profile_config['C__VISUALIZATION_PROFILES__R' . $i . '__FONT_ALIGN_MIDDLE'];
                } // if

                if ($p_profile_config['C__VISUALIZATION_PROFILES__R' . $i . '__FONT_ALIGN_RIGHT'])
                {
                    $l_row['font-align-right'] = $p_profile_config['C__VISUALIZATION_PROFILES__R' . $i . '__FONT_ALIGN_RIGHT'];
                } // if

                if ($p_profile_config['C__VISUALIZATION_PROFILES__R' . $i . '__FILLCOLOR_OBJ_TYPE'])
                {
                    $l_row['fillcolor_obj_type'] = $p_profile_config['C__VISUALIZATION_PROFILES__R' . $i . '__FILLCOLOR_OBJ_TYPE'];
                }
                else
                {
                    $l_row['fillcolor'] = $p_profile_config['C__VISUALIZATION_PROFILES__R' . $i . '__FILLCOLOR'];
                } // if

                $l_config['rows'][] = $l_row;
            } // if
        } // for

        $l_dao     = isys_factory::get_instance('isys_visualization_profile_model', $this->m_database_component);
        $l_success = $l_dao->save_profile(
            $p_profile,
            [
                'isys_visualization_profile__title'           => $p_profile_config['C__VISUALIZATION_PROFILES__TITLE'],
                'isys_visualization_profile__description'     => $p_profile_config['C__VISUALIZATION_PROFILES__DESCRIPTION'],
                'isys_visualization_profile__defaults'        => isys_format_json::encode($l_defaults),
                'isys_visualization_profile__obj_info_config' => isys_format_json::encode($l_obj_info),
                'isys_visualization_profile__config'          => isys_format_json::encode($l_config)
            ]
        );

        if (empty($p_profile) && $l_success)
        {
            return $l_dao->get_last_insert_id();
        }
        else
        {
            return null;
        } // if
    } // function

    /**
     * Method for deleting a profile.
     *
     * @param   integer $p_profile
     *
     * @return  boolean
     * @throws  isys_exception_general
     */
    protected function delete_profile($p_profile)
    {
        if ($p_profile > 0)
        {
            return isys_factory::get_instance('isys_visualization_profile_model', $this->m_database_component)
                ->delete_profile($p_profile);
        } // if

        throw new isys_exception_general('No profile ID given');
    } // function

    /**
     * Method for duplicating a profile.
     *
     * @param   integer $p_profile
     *
     * @return  array
     */
    protected function duplicate_profile($p_profile)
    {
        /* @var  isys_visualization_profile_model $l_dao */
        $l_dao = isys_factory::get_instance('isys_visualization_profile_model', $this->m_database_component);

        $l_id = $l_dao->duplicate_profile($p_profile);

        return $l_dao->get_profile($l_id)
            ->get_row();
    } // function

    /**
     * Method for defining the given profile as default.
     *
     * @param   integer $p_profile
     *
     * @return  boolean
     */
    protected function default_profile($p_profile)
    {
        isys_usersettings::set('cmdb-explorer.default-profile', $p_profile);

        return true;
    } // function
} // class