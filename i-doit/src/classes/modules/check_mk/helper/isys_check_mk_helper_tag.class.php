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
 * Check_MK helper for generic tags.
 *
 * @package     Modules
 * @subpackage  Check_MK
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.4.0
 */
class isys_check_mk_helper_tag
{
    /**
     * Static array for caching the object-type specific tag data.
     *
     * @var  array
     */
    protected static $m_cache = [];
    /**
     * Static instance of the modules DAO.
     *
     * @var  isys_check_mk_dao_generic_tag
     */
    protected static $m_dao = null;
    /**
     * Static instance variable.
     *
     * @var  isys_check_mk_helper_tag
     */
    protected static $m_instances = [];
    /**
     * This static array will hold all selected properties of a request.
     *
     * @var  array
     * @see  self::save_exported_tags_to_database()
     */
    protected static $m_used_properties = [];
    /**
     * This variable stores the current object type.
     *
     * @var  integer
     */
    protected $m_obj_type_id = null;

    /**
     * Initialize method for setting some initial stuff.
     *
     * @static
     *
     * @param   integer $p_obj_type
     *
     * @return  isys_check_mk_helper_tag
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function factory($p_obj_type)
    {
        self::$m_dao = isys_factory::get_instance('isys_check_mk_dao_generic_tag', isys_application::instance()->database);

        if (!isset(self::$m_instances[$p_obj_type]))
        {
            self::$m_instances[$p_obj_type] = new self($p_obj_type);
        } // if

        return self::$m_instances[$p_obj_type];
    } // function

    /**
     * Saves all the collected generic tags to the database.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function save_exported_tags_to_database()
    {
        global $g_loc;

        $l_data = [];

        /* @var  isys_cmdb_dao_category_property $l_dao */
        $l_dao = isys_factory::get_instance('isys_cmdb_dao_category_property', isys_application::instance()->database);
        $l_res = $l_dao->retrieve_properties(array_keys(self::$m_used_properties));

        if (count($l_res))
        {
            while ($l_row = $l_res->get_row())
            {
                $l_cat_dao    = $l_dao->get_dao_instance($l_row['class'], ($l_row['catg_custom'] ?: null));
                $l_properties = $l_cat_dao->get_properties();
                $l_prop_data  = $l_properties[$l_row['key']];
                $l_cat_title  = $l_dao->get_category_by_const_as_string($l_row['const']);
                $l_tags       = [];

                if ($l_prop_data[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__DIALOG_PLUS || $l_prop_data[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__DIALOG)
                {
                    $l_dialog_data = isys_factory_cmdb_dialog_dao::get_instance($l_prop_data[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0], isys_application::instance()->database)
                        ->get_data();

                    if (is_array($l_dialog_data))
                    {
                        foreach ($l_dialog_data as $l_dialog_item)
                        {
                            $l_tag_name = _L($l_dialog_item[$l_prop_data[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '__title']);

                            if (empty($l_tag_name) || is_numeric($l_tag_name))
                            {
                                continue;
                            } // if

                            $l_tags[] = [
                                'id'   => self::prepare_valid_tag_name($l_tag_name),
                                'name' => $l_tag_name
                            ];
                        } // foreach
                    } // if

                    $l_data[$l_row['const'] . '__' . $l_row['key'] . '::' . ($l_cat_title !== false ? _L($l_cat_title) . ' ' : '') . _L($l_row['title'])] = $l_tags;
                }
                else
                {
                    // This "else" is necessary for simple text-fields.
                    $l_lang      = $g_loc->get_setting(LC_LANG);
                    $l_group     = $l_row['const'] . '__' . $l_row['key'] . '::' . ($l_cat_title !== false ? _L($l_cat_title) . ' ' : '') . _L($l_row['title']);

                    $l_sql = 'SELECT isys_check_mk_exported_tags__tags
						FROM isys_check_mk_exported_tags
						WHERE isys_check_mk_exported_tags__group = ' . $l_dao->convert_sql_text($l_group) . '
						AND isys_check_mk_exported_tags__language = ' . $l_dao->convert_sql_int($l_lang) . '
						LIMIT 1;';

                    // We'll try to find any "already exported" values to fill up the tag-array.
                    $l_exported_tags = isys_format_json::decode(
                        $l_dao->retrieve($l_sql)
                            ->get_row_value('isys_check_mk_exported_tags__tags')
                    );

                    if (is_array($l_exported_tags) && count($l_exported_tags))
                    {
                        foreach ($l_exported_tags as $l_tag)
                        {
                            $l_tag_exists = !!count(
                                array_filter(
                                    self::$m_used_properties[$l_row['id']],
                                    function ($l_used_tag) use ($l_tag)
                                    {
                                        return $l_used_tag['id'] == $l_tag['id'];
                                    }
                                )
                            );

                            if (!$l_tag_exists)
                            {
                                self::$m_used_properties[$l_row['id']][] = $l_tag;
                            } // if
                        } // foreach
                    } // if

                    $l_data[$l_group] = self::$m_used_properties[$l_row['id']];
                } // if
            } // while
        } // if

        // Now get the data from "isys_check_mk_tags" and do the same.
        $l_static_tags = isys_factory::get_instance('isys_check_mk_dao', isys_application::instance()->database)
            ->get_configured_tags();

        foreach ($l_static_tags as $l_tag_data)
        {
            if ($l_tag_data['isys_check_mk_tags__exportable'] > 0)
            {
                $l_data[_L($l_tag_data['isys_check_mk_tag_groups__title'])][] = [
                    'id'   => $l_tag_data['isys_check_mk_tags__unique_name'],
                    'name' => $l_tag_data['isys_check_mk_tags__display_name']
                ];
            } // if
        } // foreach

        if (count($l_data))
        {
            $l_lang = $g_loc->get_setting(LC_LANG);

            // Do the SQL magic.
            foreach ($l_data as $l_group => $l_tags)
            {
                $l_sql_count = 'SELECT isys_check_mk_exported_tags__id
					FROM isys_check_mk_exported_tags
					WHERE isys_check_mk_exported_tags__group = ' . $l_dao->convert_sql_text($l_group) . '
					AND isys_check_mk_exported_tags__language = ' . $l_dao->convert_sql_int($l_lang) . '
					LIMIT 1;';

                if (count($l_dao->retrieve($l_sql_count)))
                {
                    // If the group exists, we update it.
                    $l_sql = 'UPDATE isys_check_mk_exported_tags
						SET isys_check_mk_exported_tags__tags = ' . $l_dao->convert_sql_text(isys_format_json::encode($l_tags)) . '
						WHERE isys_check_mk_exported_tags__group = ' . $l_dao->convert_sql_text($l_group) . '
						AND isys_check_mk_exported_tags__language = ' . $l_dao->convert_sql_int($l_lang) . ';';
                }
                else
                {
                    // Else we create it.
                    $l_sql = 'INSERT INTO isys_check_mk_exported_tags
						SET isys_check_mk_exported_tags__group = ' . $l_dao->convert_sql_text($l_group) . ',
						isys_check_mk_exported_tags__language = ' . $l_dao->convert_sql_int($l_lang) . ',
						isys_check_mk_exported_tags__tags = ' . $l_dao->convert_sql_text(isys_format_json::encode($l_tags)) . ';';
                } // if

                $l_dao->update($l_sql) && $l_dao->apply_update();
            } // foreach
        } // if
    } // function

    /**
     * Method for retrieving all generic tags, of the current object-type.
     *
     * @param   integer $p_lang
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function get_tags_for_export($p_lang = null)
    {
        global $g_loc;

        if ($p_lang === null)
        {
            $p_lang = $g_loc->get_setting(LC_LANG);
        } // if

        if (self::$m_dao === null)
        {
            self::$m_dao = isys_factory::get_instance('isys_check_mk_dao_generic_tag', isys_application::instance()->database);
        } // if

        $l_return = [];
        $l_res    = self::$m_dao->get_exported_tags_from_database(($p_lang > 0) ? $p_lang : null);

        if (count($l_res) > 0)
        {
            while ($l_row = $l_res->get_row())
            {
                $l_return[$l_row['isys_check_mk_exported_tags__group']] = isys_format_json::decode($l_row['isys_check_mk_exported_tags__tags']);
            } // while
        } // if

        return $l_return;
    } // function

    /**
     * Method for converting invalid names like "Standort > Düsseldorf " to valid "standort_dusseldorf".
     *
     * @param   string $p_value
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function prepare_valid_tag_name($p_value)
    {
        return trim(preg_replace('~[^a-z0-9|_-]+~i', '_', isys_glob_replace_accent(trim($p_value))), '_');
    } // function

    /**
     * @param   integer $p_obj_id
     *
     * @return  array
     */
    public static function get_dynamic_tags($p_obj_id)
    {
        $l_return = $l_tags = [];

        $l_dao     = isys_factory::get_instance('isys_cmdb_dao', isys_application::instance()->database);
        $l_configs = isys_check_mk_helper::get_dynamic_tag_by_condition(C__MODULE__CMK__DYNAMIC_TAG__LOCATION);

        if (is_array($l_configs) && count($l_configs) > 0)
        {
            $l_dao_location = isys_factory::get_instance('isys_cmdb_dao_category_g_location', isys_application::instance()->database);
            $l_obj_path     = $l_dao_location->get_location_path($p_obj_id);

            foreach ($l_configs as $l_config)
            {
                if (in_array($l_config['param'], $l_obj_path))
                {
                    $l_tags = array_merge($l_tags, array_values($l_config['tags']));
                } // if
            } // foreach
        } // if

        $l_configs = isys_check_mk_helper::get_dynamic_tag_by_condition(C__MODULE__CMK__DYNAMIC_TAG__OBJECT_TYPE);

        if (is_array($l_configs) && count($l_configs) > 0)
        {
            $l_obj_type = $l_dao->get_type_by_object_id($p_obj_id)
                ->get_row_value('isys_obj_type__const');

            foreach ($l_configs as $l_config)
            {
                if ($l_config['param'] == $l_obj_type)
                {
                    $l_tags = array_merge($l_tags, array_values($l_config['tags']));
                } // if
            } // foreach
        } // if

        $l_configs = isys_check_mk_helper::get_dynamic_tag_by_condition(C__MODULE__CMK__DYNAMIC_TAG__PURPOSE);

        if (is_array($l_configs) && count($l_configs) > 0)
        {
            $l_purpose = $l_dao->retrieve(
                'SELECT isys_catg_global_list__isys_purpose__id FROM isys_catg_global_list WHERE isys_catg_global_list__isys_obj__id = ' . $l_dao->convert_sql_id(
                    $p_obj_id
                ) . ';'
            )
                ->get_row_value('isys_catg_global_list__isys_purpose__id');

            foreach ($l_configs as $l_config)
            {
                if ($l_config['param'] == $l_purpose)
                {
                    $l_tags = array_merge($l_tags, array_values($l_config['tags']));
                } // if
            } // foreach
        } // if

        $l_tags = array_unique($l_tags);

        foreach ($l_tags as $l_tag)
        {
            $l_tag_data = isys_factory_cmdb_dialog_dao::get_instance(isys_application::instance()->database, 'isys_check_mk_tags')
                ->get_data($l_tag);

            $l_return[] = self::prepare_valid_tag_name(_L($l_tag_data['isys_check_mk_tags__unique_name']));
        } // foreach

        return $l_return;
    } // function

    /**
     * This small method will remove any duplicates or empty values from the tag-list.
     *
     * @param   array $p_tags
     *
     * @return  string
     */
    public static function make_unique(array $p_tags)
    {
        return array_unique(array_filter($p_tags));
    } // function

    /**
     * Method for retrieving all CMDB tags, of the current object-type.
     *
     * @param   integer $p_obj_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_cmdb_tags($p_obj_id)
    {
        if ($this->m_obj_type_id === null)
        {
            return [];
        } // if

        if (self::$m_dao === null)
        {
            self::$m_dao = isys_factory::get_instance('isys_check_mk_dao_generic_tag', isys_application::instance()->database);
        } // if

        $l_tags         = [];
        $l_property_ids = self::$m_dao->get_configured_properties($this->m_obj_type_id);
        /* @var  isys_cmdb_dao_category_property $l_property_dao */
        $l_property_dao = isys_factory::get_instance('isys_cmdb_dao_category_property', isys_application::instance()->database);

        if (count($l_property_ids))
        {
            try
            {
                $l_tags = self::$m_dao->retrieve(
                    $l_property_dao->reset()
                        ->create_property_query_for_lists($l_property_ids, null, $p_obj_id, [], true, true)
                )
                    ->get_row();
            }
            catch (Exception $e)
            {
                isys_notify::error($e->getMessage(), ['sticky' => true]);
            } // try

            // We don't need the object ID.
            unset($l_tags['__id__']);
        } // if

        // Adding the generic-location tag.
        $l_generic_location = self::$m_dao->get_configured_generic_location_tag($p_obj_id, $this->m_obj_type_id);

        if ($l_generic_location !== false)
        {
            $l_prop_id = $l_property_dao
                ->retrieve('SELECT isys_property_2_cat__id FROM isys_property_2_cat WHERE isys_property_2_cat__cat_const = "C__CATG__LOCATION" AND isys_property_2_cat__prop_key = "parent" LIMIT 1;')
                ->get_row_value('isys_property_2_cat__id');

            if ($l_prop_id)
            {
                $l_tags[$l_prop_id] = $l_generic_location;
            } // if
        } // if

        // Filtering "NULL" entries.
        $l_tags = array_filter(
            $l_tags,
            function ($p_item)
            {
                return (!empty($p_item) && !is_numeric($p_item));
            }
        );

        foreach ($l_tags as $l_prop_id => &$l_tag)
        {
            if (empty($l_tag) || is_numeric($l_tag))
            {
                continue;
            } // if

            if (!isset(self::$m_used_properties[$l_prop_id]))
            {
                self::$m_used_properties[$l_prop_id] = [];
            } // if

            // Save the raw data for later use, see "self::save_exported_tags_to_database()".
            self::$m_used_properties[$l_prop_id][] = [
                'id'   => self::prepare_valid_tag_name(_L($l_tag)),
                'name' => $l_tag
            ];

            // Translating and formatting.
            $l_tag = self::prepare_valid_tag_name(_L($l_tag));
        } // foreach

        return $l_tags;
    } // function

    /**
     * Private clone method - Singleton!
     */
    private function __clone()
    {
        ;
    } // function

    /**
     * Private constructor - Singleton!
     *
     * @param  integer $p_obj_type
     */
    private function __construct($p_obj_type)
    {
        $this->m_obj_type_id = $p_obj_type;
    } // function
} // class