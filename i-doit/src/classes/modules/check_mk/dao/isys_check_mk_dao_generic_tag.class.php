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
 * Check_MK DAO for generic tags.
 *
 * @package     Modules
 * @subpackage  Check_MK
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.4.0
 */
class isys_check_mk_dao_generic_tag extends isys_module_dao
{
    /**
     * This variable will hold all the configuration data.
     *
     * @var  array
     */
    protected static $m_config = null;

    /**
     * Method for retrieving the complete Check_MK configuration.
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_data()
    {
        return $this->retrieve('SELECT 1+1;');
    } // function

    /**
     * Method for loading the generic tag configuration.
     *
     * @param   string $p_key
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_config($p_key = null)
    {
        if (self::$m_config === null)
        {
            $l_res = $this->retrieve('SELECT * FROM isys_check_mk_generic_tag_config');

            if (count($l_res) > 0)
            {
                while ($l_row = $l_res->get_row())
                {
                    self::$m_config[$l_row['isys_check_mk_generic_tag_config__isys_obj_type__id'] ?: 0] = isys_format_json::decode(
                        $l_row['isys_check_mk_generic_tag_config__config'],
                        true
                    );
                } // while
            } // if
        } // if

        if ($p_key === null)
        {
            return self::$m_config;
        } // if

        return self::$m_config[$p_key];
    } // function

    /**
     * This method will retrieve the prepared syntax for the property-selector of a object-type configuration.
     *
     * @param   integer $p_obj_type_id
     *
     * @return  array
     */
    public function get_config_for_property_selector($p_obj_type_id)
    {
        $l_return = [];
        $l_config = $this->get_config($p_obj_type_id);

        if (is_array($l_config['properties']) && count($l_config['properties']) > 0)
        {
            foreach ($l_config['properties'] as $l_configuration)
            {
                list($l_cat_const, $l_prop) = explode('::', $l_configuration);

                if (strpos($l_cat_const, '__CATG__') !== false)
                {
                    $l_cat_type = 'g';
                }
                else
                {
                    $l_cat_type = 's';
                } // if

                $l_return[] = [
                    $l_cat_type => [
                        constant($l_cat_const) => [
                            $l_prop
                        ]
                    ]
                ];
            } // foreach
        } // if

        return $l_return;
    } // function

    /**
     * Get the selected object-type properties.
     *
     * @param   integer $p_obj_type_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_configured_properties($p_obj_type_id)
    {
        $l_return = [];
        $l_config = $this->get_config($p_obj_type_id);
        $l_dao    = isys_cmdb_dao_category_property::instance($this->m_db);

        // ID-2691 - If the configuration does not overwrite the default, we'll add them.
        if (!$l_config['overwrite_global'])
        {
            $l_default_config = $this->get_config(0);

            if (!$l_config['location']['export'])
            {
                $l_config['location'] = $l_default_config['location'];
            } // if

            $l_config['properties'] = [];

            // Merge the tags.
            if (is_array($l_config['properties']) && is_array($l_default_config['properties']))
            {
                $l_config['properties'] = array_merge($l_config['properties'], $l_default_config['properties']);
            } // if
        } // if

        if (is_array($l_config['properties']) && count($l_config['properties']) > 0)
        {
            foreach ($l_config['properties'] as $l_configuration)
            {
                list($l_cat_const, $l_prop) = explode('::', $l_configuration);

                $l_res = $l_dao->retrieve_properties(
                    null,
                    null,
                    null,
                    null,
                    'AND isys_property_2_cat__cat_const = ' . $this->convert_sql_text($l_cat_const) . ' AND isys_property_2_cat__prop_key = ' . $this->convert_sql_text(
                        $l_prop
                    )
                );

                if (count($l_res) > 0)
                {
                    while ($l_row = $l_res->get_row())
                    {
                        $l_return[] = $l_row['id'];
                    } // while
                } // if
            } // foreach
        } // if

        return $l_return;
    } // function

    /**
     * This method will simply update the configuration.
     *
     * @param   integer $p_obj_type
     * @param   array   $p_config The array should contain strings like "C_CATEGORY_CONSTANT::property_name".
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function save_config($p_obj_type, array $p_config)
    {
        if (!is_array($this->get_config($p_obj_type)))
        {
            $l_sql = 'INSERT INTO isys_check_mk_generic_tag_config
				SET isys_check_mk_generic_tag_config__isys_obj_type__id = ' . $this->convert_sql_id($p_obj_type) . ';';

            $this->update($l_sql);
        } // if

        $l_sql = 'UPDATE isys_check_mk_generic_tag_config
			SET isys_check_mk_generic_tag_config__config = ' . $this->convert_sql_text(isys_format_json::encode($p_config)) . '
			WHERE isys_check_mk_generic_tag_config__isys_obj_type__id ' . ($p_obj_type === 0 ? 'IS NULL' : '= ' . $this->convert_sql_id($p_obj_type)) . ';';

        // We reset the cache to reload it, the next time "get_config()" is called.
        self::$m_config = null;

        return ($this->update($l_sql) && $this->apply_update());
    } // function

    /**
     * This method will return a formatted property-config by a given property-ID.
     *
     * @param   mixed $p_property_id May be an integer or an array of integers.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_formatted_config_by_property_id($p_property_id)
    {
        $l_return = [];
        $l_res    = isys_cmdb_dao_category_property::instance($this->m_db)
            ->retrieve_properties($p_property_id);

        if (count($l_res) > 0)
        {
            while ($l_row = $l_res->get_row())
            {
                $l_return[] = $l_row['const'] . '::' . $l_row['key'];
            } // while
        } // if

        return $l_return;
    } // function

    /**
     * Retrieve the generic location tag, by a given object-ID.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_obj_type_id
     *
     * @return  mixed
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_configured_generic_location_tag($p_obj_id, $p_obj_type_id = null)
    {
        $l_cmdb_dao = isys_cmdb_dao::instance($this->m_db);

        if ($p_obj_type_id === null)
        {
            $p_obj_type_id = $l_cmdb_dao->get_type_by_object_id($p_obj_id)
                ->get_row();

            $p_obj_type_id = $p_obj_type_id['isys_obj_type__id'];
        } // if

        $l_config = $this->get_config($p_obj_type_id);

        if (is_array($l_config) && $l_config['location']['export'] == 1 && $l_config['location']['obj_type'] > 0)
        {
            // Get the location path.
            $l_tmp          = null;
            $l_location_dao = new isys_cmdb_dao_location($this->m_db);
            $l_location_res = $l_location_dao->get_path_by_obj_id($p_obj_id, $l_tmp);

            if (count($l_location_res) > 0)
            {
                while ($l_location_row = $l_location_res->get_row())
                {
                    $l_location_row['isys_catg_location_list__isys_obj__id'];

                    $l_object = $l_cmdb_dao->get_object_by_id($l_location_row['isys_catg_location_list__isys_obj__id'])
                        ->get_row();

                    // As soon as we find the first upcoming defined object-type, we return it's title.
                    if ($l_object['isys_obj_type__id'] == $l_config['location']['obj_type'])
                    {
                        return $l_object['isys_obj__title'];
                    } // if
                } // while
            } // if
        } // if

        return false;
    } // function

    /**
     * Method for retrieving the generic tags from the database.
     *
     * @param   integer $p_lang
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_exported_tags_from_database($p_lang = null)
    {
        $l_condition = '';

        if ($p_lang !== null)
        {
            $l_condition = ' WHERE isys_check_mk_exported_tags__language = ' . $this->convert_sql_id($p_lang);
        } // if

        return $this->retrieve('SELECT * FROM isys_check_mk_exported_tags' . $l_condition . ';');
    } // function

    /**
     * Method for deleting all exported tags from the database.
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function delete_exported_tags_from_database()
    {
        return $this->update('TRUNCATE isys_check_mk_exported_tags;') && $this->apply_update();
    } // function
} // class