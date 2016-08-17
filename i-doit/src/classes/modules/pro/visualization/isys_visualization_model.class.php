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
 * Global visualization model.
 *
 * @package     modules
 * @subpackage  pro
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.5.0
 */
class isys_visualization_model extends isys_itservice_dao_algorithm
{
    /**
     * Method for loading the it-service by a given ID.
     *
     * @param   integer $p_filter
     *
     * @return  array
     */
    public function load_service_filter($p_filter)
    {
        $l_filter = [];

        // Prepare the filters.
        if ($p_filter > 0)
        {
            $l_filter = isys_itservice_dao_filter_config::instance($this->m_db)
                ->get_data($p_filter);

            $l_filter = $l_filter['formatted__data'];
        } // if

        if (empty($l_filter['level']))
        {
            $l_filter['level'] = 15;
        } // if

        return $l_filter;
    } // function

    /**
     * Method for retrieving the infobox data.
     *
     * @param   integer $p_obj
     * @param   integer $p_profile_id
     * @param   integer $p_relation_obj
     *
     * @return  array
     * @throws  isys_exception_general
     */
    public function load_object_infobox_data($p_obj, $p_profile_id, $p_relation_obj = null)
    {
        global $g_dirs;

        $l_relation_row = [];

        /* @var  isys_cmdb_dao $l_dao_cmdb */
        $l_dao_cmdb = isys_cmdb_dao::instance($this->m_db);

        $l_object_data = $l_dao_cmdb->get_object_by_id($p_obj)
            ->get_row();

        $l_object_image = $g_dirs['images'] . 'objecttypes/' . C__OBJTYPE_IMAGE__DEFAULT;

        if (!empty($l_object_data['isys_obj_type__obj_img_name']))
        {
            $l_object_image = $g_dirs['images'] . 'objecttypes/' . $l_object_data['isys_obj_type__obj_img_name'];
        } // if

        $l_image = isys_cmdb_dao_category_g_image::instance($this->m_db)
            ->get_data(null, $p_obj)
            ->get_row_value('isys_catg_image_list__image_link');

        if (!empty($l_image))
        {
            $l_object_image = isys_helper_link::create_url(
                [
                    C__GET__MODULE_ID => C__MODULE__CMDB,
                    'file_manager'    => 'image',
                    'file'            => urlencode($l_image)
                ]
            );
        } // if

        $l_dynamic_data = [];

        if ($p_profile_id > 0)
        {
            try
            {
                $l_profile = isys_factory::get_instance('isys_visualization_profile_model', $this->m_db)
                    ->get_profile($p_profile_id)
                    ->get_row_value('isys_visualization_profile__obj_info_config');

                if (isys_format_json::is_json($l_profile))
                {
                    $l_profile = isys_format_json::decode($l_profile);

                    if (!empty($l_profile['query']))
                    {
                        $l_row = $this->retrieve(sprintf($l_profile['query'], $this->convert_sql_id($p_obj)))
                            ->get_row();

                        foreach ($l_row as $l_key => $l_value)
                        {
                            $l_key = trim($l_key);

                            if (strpos($l_key, 'isys_cmdb_dao_category_') === 0)
                            {
                                $l_arr = explode('::', $l_key);

                                if (class_exists($l_arr[0]))
                                {
                                    $l_cat_dao = call_user_func(
                                        [
                                            $l_arr[0],
                                            'instance'
                                        ],
                                        $this->m_db
                                    );

                                    if ($l_value !== null)
                                    {
                                        $l_callback_row[$l_arr[2]] = $l_value;

                                        $l_dynamic_data[] = [
                                            _L($l_arr[3]),
                                            call_user_func(
                                                [
                                                    $l_cat_dao,
                                                    $l_arr[1]
                                                ],
                                                $l_callback_row
                                            )
                                        ];
                                    }
                                    else
                                    {
                                        $l_dynamic_data[] = [
                                            _L($l_arr[3]),
                                            ''
                                        ];
                                    } // if
                                } // if

                                continue;
                            } // if

                            if (strpos($l_key, '__') === 0)
                            {
                                continue;
                            } // if

                            $l_lc_key = strstr($l_key, '###', true);

                            if ($l_lc_key === false)
                            {
                                $l_lc_key = $l_key;
                            } // if

                            if (strpos($l_lc_key, '#') !== false)
                            {
                                $l_key_parts = array_map('_L', explode('#', $l_lc_key));

                                $l_format = '%s';

                                if (count($l_key_parts) == 3)
                                {
                                    $l_format = '%s (%s -> %s)';
                                }
                                else if (count($l_key_parts) == 2)
                                {
                                    $l_format = '%s (%s)';
                                } // if

                                $l_lc_key = vsprintf($l_format, $l_key_parts);
                            }
                            else
                            {
                                $l_lc_key = _L($l_lc_key);
                            } // if

                            $l_dynamic_data[] = [
                                $l_lc_key,
                                _L($l_value)
                            ];
                        } // foreach
                    } // if
                } // if
            }
            catch (Exception $e)
            {
                $l_dynamic_data = $e;
            } // try
        } // if

        if ($p_relation_obj > 0)
        {
            $l_relation_row = isys_cmdb_dao_category_s_relation_details::instance($this->m_db)
                ->get_data(null, $p_relation_obj)
                ->get_row();
        } // if

        return [
            'image'             => $l_object_image,
            'obj_id'            => $l_object_data['isys_obj__id'],
            'obj_title'         => $l_object_data['isys_obj__title'],
            'obj_type_id'       => $l_object_data['isys_obj_type__id'],
            'obj_type_title'    => _L($l_object_data['isys_obj_type__title']),
            'obj_type_icon'     => $l_object_data['isys_obj_type__icon'],
            'obj_type_color'    => '#' . $l_object_data['isys_obj_type__color'],
            'cmdb_status_title' => _L($l_object_data['isys_cmdb_status__title']),
            'cmdb_status_color' => '#' . $l_object_data['isys_cmdb_status__color'],
            'dynamic_data'      => $l_dynamic_data,
            'relation_type'     => isset($l_relation_row['isys_relation_type__title']) ? _L($l_relation_row['isys_relation_type__title']) : null
        ];
    } // function
} // class