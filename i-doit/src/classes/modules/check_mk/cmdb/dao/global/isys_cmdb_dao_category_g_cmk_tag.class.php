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
 * DAO: global category for Check_MK.
 *
 * @package     Modules
 * @subpackage  Check_MK
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.4.0
 */
class isys_cmdb_dao_category_g_cmk_tag extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'cmk_tag';

    /**
     * Category entry is purgable
     *
     * @var  boolean
     */
    protected $m_is_purgable = true;

    /**
     * Callback method for property host.
     *
     * @param   isys_request $p_request
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function callback_property_tags(isys_request $p_request)
    {
        global $g_comp_database;

        return isys_cmdb_dao_category_g_cmk_tag::instance($g_comp_database)
            ->get_tags_for_dialog_list($p_request->get_object_id());
    } // function

    /**
     * Creates new entity.
     *
     * @param   array $p_data
     *
     * @return  mixed
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function create_data($p_data)
    {
        if (array_key_exists('tags', $p_data) && !isys_format_json::is_json_array($p_data['tags']))
        {
            // We convert the comma separated list to a json array and go sure, that there will only be 'integers'.
            $l_tags = array_map('intval', explode(',', $p_data['tags']));

            $p_data['tags'] = isys_format_json::encode($l_tags);
        } // if

        return parent::create_data($p_data);
    } // function

    /**
     * Get data method.
     *
     * @param   integer $p_category_data_id
     * @param   mixed   $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_data($p_category_data_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        $l_sql = 'SELECT * FROM isys_catg_cmk_tag_list
			LEFT JOIN isys_obj ON isys_obj__id = isys_catg_cmk_tag_list__isys_obj__id
			LEFT JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
			LEFT JOIN isys_catg_cmk_list ON isys_catg_cmk_tag_list__isys_obj__id = isys_catg_cmk_list__isys_obj__id
			WHERE TRUE ' . $p_condition . ' ' . $this->prepare_filter($p_filter) . ' ';

        if ($p_obj_id !== null)
        {
            $l_sql .= $this->get_object_condition($p_obj_id);
        } // if

        if ($p_category_data_id !== null)
        {
            $l_sql .= " AND isys_catg_cmk_tag_list__id = " . $this->convert_sql_id($p_category_data_id);
        } // if

        if ($p_status !== null)
        {
            $l_sql .= " AND isys_catg_cmk_tag_list__status = " . $this->convert_sql_int($p_status);
        } // if

        return $this->retrieve($l_sql . ';');
    } // function

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function properties()
    {
        return [
            'tags'        => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog_list(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__CMK_TAG__TAGS',
                        C__PROPERTY__INFO__DESCRIPTION => 'Tags'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_cmk_tag_list__tags',
                        C__PROPERTY__DATA__TYPE  => C__TYPE__TEXT
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__CMK_TAG__TAGS',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData' => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_cmk_tag',
                                    'callback_property_tags'
                                ]
                            )
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH     => false,
                        C__PROPERTY__PROVIDES__REPORT     => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                        C__PROPERTY__PROVIDES__LIST       => false,
                        C__PROPERTY__PROVIDES__VALIDATION => false
                    ],
                    C__PROPERTY__FORMAT   => null
                ]
            ),
            'description' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_cmk_tag_list__description'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CATG__CMK_TAG
                    ]
                ]
            )
        ];
    } // function

    /**
     * Save method for setting specific values by hand.
     *
     * @param   integer $p_category_data_id
     * @param   array   $p_data
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function save_data($p_category_data_id, $p_data)
    {
        if (array_key_exists('tags', $p_data) && !isys_format_json::is_json_array($p_data['tags']))
        {
            // We convert the comma separated list to a json array and go sure, that there will only be 'integers'.
            $l_tags = array_map('intval', explode(',', $p_data['tags']));

            $p_data['tags'] = isys_format_json::encode($l_tags);
        }
        else
        {
            // If the "tags" field is empty or is no JSON array, we remove the data.
            $p_data['tags'] = null;
        } // if

        return parent::save_data($p_category_data_id, $p_data);
    } // function

    /**
     * Retrieves the available tags, ready for a "dialog_list".
     *
     * @param   integer $p_obj_id
     * @param   array   $p_selection
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_tags_for_dialog_list($p_obj_id = null, array $p_selection = null)
    {
        $l_tags = $l_return = [];

        if ($p_obj_id !== null)
        {
            $l_res = $this->get_data(null, $p_obj_id);

            if (count($l_res) > 0)
            {
                $l_catdata = $l_res->get_row();

                $l_tags = [];

                if (isys_format_json::is_json_array($l_catdata['isys_catg_cmk_tag_list__tags']))
                {
                    $l_tags = isys_format_json::decode($l_catdata['isys_catg_cmk_tag_list__tags']) ?: [];
                } // if
            } // if
        } // if

        if ($p_selection !== null)
        {
            $l_tags = $p_selection;
        } // if

        $l_dialog = isys_check_mk_dao::instance($this->m_db)
            ->get_configured_tags();

        // Adding specific "p_arData" values...
        if (is_array($l_dialog) && count($l_dialog) > 0)
        {
            foreach ($l_dialog as $l_item)
            {
                $l_return[] = [
                    'id'  => $l_item['isys_check_mk_tags__id'],
                    'val' => '[' . _L($l_item['isys_check_mk_tag_groups__title']) . '] ' . $l_item['isys_check_mk_tags__display_name'],
                    'sel' => in_array($l_item['isys_check_mk_tags__id'], $l_tags)
                ];
            } // foreach
        } // if

        return $l_return;
    } // function
} // class