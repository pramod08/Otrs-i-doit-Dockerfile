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
 * DAO: specific category for stack members.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @since       1.7
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_stack_member extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'stack_member';

    /**
     * Flag which defines if the category is a multivalued category.
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Callback method for property mode.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function callback_property_mode(isys_request $p_request)
    {
        return [
            '0' => _L('LC__UNIVERSAL__PASSIVE'),
            '1' => _L('LC__UNIVERSAL__ACTIVE')
        ];
    } // function

    /**
     * Gets the connected meta stack object(s) (if the given object is part of stacking).
     *
     * @param   integer $p_obj_id
     *
     * @return  isys_component_dao_result
     */
    public function get_stacking_meta($p_obj_id)
    {
        $l_sql = 'SELECT * FROM isys_catg_stack_member_list
			INNER JOIN isys_obj ON isys_obj__id = isys_catg_stack_member_list__isys_obj__id
			INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
			WHERE isys_catg_stack_member_list__stack_member = ' . $this->convert_sql_id($p_obj_id) . ';';

        return $this->retrieve($l_sql);
    } // function

    /**
     * Gets all assigned objects of the given stack object.
     *
     * @param   integer $p_obj_id
     *
     * @return  isys_component_dao_result
     */
    public function get_connected_objects($p_obj_id)
    {
        $l_sql = 'SELECT *
			FROM isys_catg_stack_member_list
			INNER JOIN isys_obj ON isys_obj__id = isys_catg_stack_member_list__isys_obj__id
			INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
			WHERE isys_catg_stack_member_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ';';

        return $this->retrieve($l_sql);
    } // function

    /**
     * Create a new entity.
     *
     * @param   array $p_data Properties in a associative array with tags as keys and their corresponding values as values.
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function create_data($p_data)
    {
        $l_result = parent::create_data($p_data);

        if ($l_result > 0)
        {
            // This is necessary to create the relations and stuff.
            $this->save_data($l_result, $p_data);
        } // if

        return $l_result;
    } // function

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'assigned_object' => array_replace_recursive(
                isys_cmdb_dao_category_pattern::object_browser(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__STACK_MEMBER__STACK_MEMBER',
                        C__PROPERTY__INFO__DESCRIPTION => 'Member'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_stack_member_list__stack_member',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_obj',
                            'isys_obj__id'
                        ]
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CATG__STACK_MEMBER__STACK_MEMBER',
                        C__PROPERTY__UI__PARAMS => [
                            'p_strClass' => 'input-small'
                        ]
                    ]
                ]
            ),
            'mode'            => array_replace_recursive(
                isys_cmdb_dao_category_pattern::dialog(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__STACK_MEMBER__MODE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Mode'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_stack_member_list__mode'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID     => 'C__CATG__STACK_MEMBER__MODE',
                        C__PROPERTY__UI__PARAMS => [
                            'p_arData'   => new isys_callback(
                                [
                                    'isys_cmdb_dao_category_g_stack_member',
                                    'callback_property_mode'
                                ]
                            ),
                            'p_strClass' => 'input-mini'
                        ]
                    ]
                ]
            ),
            'description'     => array_replace_recursive(
                isys_cmdb_dao_category_pattern::commentary(),
                [
                    C__PROPERTY__INFO => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                        C__PROPERTY__INFO__DESCRIPTION => 'Description'
                    ],
                    C__PROPERTY__DATA => [
                        C__PROPERTY__DATA__FIELD => 'isys_catg_stack_member_list__description'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CATG__STACK_MEMBER
                    ]
                ]
            )
        ];
    } // function

    /**
     * Updates existing entity.
     *
     * @param   integer $p_id   Entity's identifier
     * @param   array   $p_data Properties in a associative array with tags as keys and their corresponding values as values.
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function save_data($p_id, $p_data)
    {
        $l_result = parent::save_data($p_id, $p_data);

        if ($l_result)
        {
            $l_target_object    = null;
            $l_relation_dao     = isys_cmdb_dao_category_g_relation::instance($this->get_database_component());
            $l_relation_default = $l_relation_dao->get_relation_type(C__RELATION_TYPE__STACKING)
                ->get_row_value('isys_relation_type__default');

            $l_relation_id = $this->retrieve(
                'SELECT isys_catg_stack_member_list__isys_catg_relation_list__id FROM isys_catg_stack_member_list WHERE isys_catg_stack_member_list__id = ' . $this->convert_sql_id(
                    $p_id
                ) . ';'
            )
                ->get_row_value('isys_catg_stack_member_list__isys_catg_relation_list__id');

            if ($l_relation_default == C__RELATION_DIRECTION__I_DEPEND_ON)
            {
                $l_master = $p_data['assigned_object'];
                $l_slave  = $p_data['isys_obj__id'];
            }
            else
            {
                $l_master = $p_data['isys_obj__id'];
                $l_slave  = $p_data['assigned_object'];
            } // if

            // Handle the implicit relation.
            $l_relation_dao->handle_relation($p_id, "isys_catg_stack_member_list", C__RELATION_TYPE__STACKING, $l_relation_id, $l_master, $l_slave);
        } // if

        return $l_result;
    } // function
} // class