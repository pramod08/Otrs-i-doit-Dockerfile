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
 * CMDB DAO: Global category for VRRP members.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @since       1.7
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_category_g_vrrp_member extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'vrrp_member';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean  Defaults to false.
     */
    protected $m_multivalued = true;

    /**
     * Create new entity.
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
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function properties()
    {
        return [
            'member'      => array_replace_recursive(
                isys_cmdb_dao_category_pattern::object_browser(),
                [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__CATG__VRRP_MEMBER__MEMBER',
                        C__PROPERTY__INFO__DESCRIPTION => 'Member'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD      => 'isys_catg_vrrp_member_list__isys_catg_log_port_list__id',
                        C__PROPERTY__DATA__REFERENCES => [
                            'isys_catg_log_port_list',
                            'isys_catg_log_port_list__id'
                        ]
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__VRRP_MEMBER__MEMBER',
                        C__PROPERTY__UI__PARAMS => [
                            isys_popup_browser_object_ng::C__TITLE              => 'LC__BROWSER__TITLE__LOG_PORT',
                            isys_popup_browser_object_ng::C__CAT_FILTER         => 'C__CATG__NETWORK;C__CMDB__SUBCAT__NETWORK_INTERFACE_L',
                            isys_popup_browser_object_ng::C__SECOND_SELECTION   => true,
                            isys_popup_browser_object_ng::C__SECOND_LIST        => 'isys_cmdb_dao_category_g_vrrp_member::object_browser',
                            isys_popup_browser_object_ng::C__SECOND_LIST_FORMAT => 'isys_cmdb_dao_category_g_vrrp_member::format_selection',
                            isys_popup_browser_object_ng::C__READ_ONLY          => true
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__LIST   => false,
                        C__PROPERTY__PROVIDES__SEARCH => false
                    ],
                    C__PROPERTY__FORMAT   => [
                        C__PROPERTY__FORMAT__CALLBACK => [
                            'isys_export_helper',
                            'get_net_dns_server'
                        ]
                        // @todo
                    ]
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
                        C__PROPERTY__DATA__FIELD => 'isys_catg_vrrp_member_list__description'
                    ],
                    C__PROPERTY__UI   => [
                        C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CATG__VRRP_MEMBER
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
            $l_target_object = null;
            $l_relation_dao  = isys_cmdb_dao_category_g_relation::instance($this->get_database_component());

            $l_relation_id = $this->retrieve(
                'SELECT isys_catg_vrrp_member_list__isys_catg_relation_list__id FROM isys_catg_vrrp_member_list WHERE isys_catg_vrrp_member_list__id = ' . $this->convert_sql_id(
                    $p_id
                ) . ';'
            )
                ->get_row_value('isys_catg_vrrp_member_list__isys_catg_relation_list__id');

            if (isset($p_data['member']) && $p_data['member'] > 0)
            {
                $l_target_object = $this->retrieve(
                    'SELECT isys_catg_log_port_list__isys_obj__id FROM isys_catg_log_port_list WHERE isys_catg_log_port_list__id = ' . $this->convert_sql_id(
                        $p_data['member']
                    ) . ';'
                )
                    ->get_row_value('isys_catg_log_port_list__isys_obj__id');
            } // if

            // Handle the implicit relation, the relation-direction needs to be forced! No "default" direction here.
            $l_relation_dao->handle_relation($p_id, "isys_catg_vrrp_member_list", C__RELATION_TYPE__VRRP, $l_relation_id, $l_target_object, $p_data['isys_obj__id']);

            // Handle the parallel relation.
            $this->handle_parallel_relation($p_data['isys_obj__id']);
        } // if

        return $l_result;
    } // function

    /**
     * Method for second-selection object browser: handle preselection and provide content.
     *
     * @param   integer $p_context
     * @param   array   $p_parameters
     *
     * @return  mixed
     */
    public function object_browser($p_context, array $p_parameters)
    {
        if ($p_context == isys_popup_browser_object_ng::C__CALL_CONTEXT__REQUEST)
        {
            // Handle Ajax-Request.
            $l_return = [];

            $l_res = isys_cmdb_dao_category_g_network_ifacel::instance($this->get_database_component())
                ->get_data(null, $_GET[C__CMDB__GET__OBJECT]);

            if (count($l_res))
            {
                $l_title = _L('LC__CMDB__CATG__INTERFACE_L__TITLE');
                $l_mac   = _L('LC__CMDB__CATG__PORT__MAC');

                while ($l_row = $l_res->get_row())
                {
                    $l_return[] = [
                        '__checkbox__' => $l_row['isys_catg_log_port_list__id'],
                        $l_title       => $l_row['isys_catg_log_port_list__title'],
                        $l_mac         => $l_row['isys_catg_log_port_list__mac']
                    ];
                } // while
            } // if

            return isys_format_json::encode($l_return);
        } // if

        return [
            'category' => [],
            'first'    => [],
            'second'   => isys_format_json::is_json_array($p_parameters['preselection']) ? isys_format_json::decode($p_parameters['preselection']) : []
        ];
    } // function

    /**
     * Method for formatting the object browser selection (logical ports).
     *
     * @param   integer $p_port_cat_id
     * @param   boolean $p_plain
     *
     * @return  string
     */
    public function format_selection($p_port_cat_id, $p_plain = false)
    {
        $l_port_row = isys_cmdb_dao_category_g_network_ifacel::instance($this->get_database_component())
            ->get_data($p_port_cat_id)
            ->get_row();
        $l_title    = _L($l_port_row['isys_obj_type__title']) . ' >> ' . _L($l_port_row['isys_obj__title']) . ' >> ' . _L($l_port_row['isys_catg_log_port_list__title']);

        if ($p_plain)
        {
            return $l_title;
        } // if

        return isys_factory::get_instance('isys_ajax_handler_quick_info')
            ->get_quick_info(
                $l_port_row['isys_obj__id'],
                $l_title,
                C__LINK__CATG,
                false,
                [
                    C__CMDB__GET__CATG     => C__CMDB__SUBCAT__NETWORK_INTERFACE_L,
                    C__CMDB__GET__CATLEVEL => $p_port_cat_id
                ]
            );
    } // function

    /**
     * Method for retrieving all connected logical ports of a VRRP.
     *
     * @param   integer $p_vrrp_obj_id
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     * @throws  isys_exception_database
     */
    public function get_vrrp_members($p_vrrp_obj_id, $p_status = null)
    {
        $l_sql = 'SELECT
		    isys_obj__id,
		    isys_catg_log_port_list__id,
		    isys_catg_log_port_list__title
		    FROM isys_catg_vrrp_member_list
			INNER JOIN isys_catg_log_port_list ON isys_catg_log_port_list__id = isys_catg_vrrp_member_list__isys_catg_log_port_list__id
			INNER JOIN isys_obj ON isys_obj__id = isys_catg_log_port_list__isys_obj__id
			WHERE isys_catg_vrrp_member_list__isys_obj__id = ' . $this->convert_sql_id($p_vrrp_obj_id);

        if ($p_status !== null)
        {
            $p_status = $this->convert_sql_int($p_status);

            $l_sql .= ' AND isys_catg_vrrp_member_list__status = ' . $p_status . '
				AND isys_catg_log_port_list__status = ' . $p_status . '
				AND isys_obj__status = ' . $p_status . ';';
        } // if

        return $this->retrieve($l_sql);
    } // function

    /**
     * @param   integer $p_object_id
     *
     * @return  boolean
     * @throws  isys_exception_dao
     */
    protected function handle_parallel_relation($p_object_id)
    {
        $l_relation_pool_id = $this->get_relation_pool_by_id($p_object_id, _L('LC__CATG__STACK_MEMBER'));

        if ($l_relation_pool_id > 0)
        {
            $l_dao_relation = isys_cmdb_dao_category_g_relation::instance($this->m_db);
            $l_dao_relpool  = isys_cmdb_dao_category_s_parallel_relation::instance($this->m_db);

            if ($l_dao_relpool->clear($l_relation_pool_id))
            {
                // Get every stack member of the current object.
                $l_res = $this->get_data(null, $p_object_id, '', null, C__RECORD_STATUS__NORMAL);

                if (count($l_res))
                {
                    while ($l_row = $l_res->get_row())
                    {
                        if ($l_row['isys_catg_vrrp_member_list__isys_catg_relation_list__id'] > 0)
                        {
                            // And add it to the parallel relation.
                            $l_dao_relpool->attach_relation(
                                $l_relation_pool_id,
                                $l_dao_relation->get_data_by_id($l_row['isys_catg_vrrp_member_list__isys_catg_relation_list__id'])
                                    ->get_row_value('isys_obj__id')
                            );
                        } // if
                    } // while
                } // if
            } // if

            // Saves relpool ID.
            $this->update(
                "UPDATE isys_catg_vrrp_member_list
                SET isys_catg_vrrp_member_list__isys_cats_relpool_list__id = " . $this->convert_sql_id($l_relation_pool_id) . "
                WHERE isys_catg_vrrp_member_list__isys_obj__id = " . $this->convert_sql_id($p_object_id) . ";"
            );

            return $this->apply_update();
        } // if

        return false;
    } // function

    /**
     * Checks if a relation pool exists for the given object. Creates one if not.
     *
     * @param   integer $p_object_id
     * @param   string  $p_relation_title
     *
     * @return  integer
     */
    private function get_relation_pool_by_id($p_object_id, $p_relation_title)
    {
        $l_sql = 'SELECT isys_catg_vrrp_member_list__isys_cats_relpool_list__id, isys_cats_relpool_list__threshold, isys_cats_relpool_list__description
			FROM isys_catg_vrrp_member_list
			INNER JOIN isys_cats_relpool_list ON isys_cats_relpool_list__id = isys_catg_vrrp_member_list__isys_cats_relpool_list__id
			WHERE isys_catg_vrrp_member_list__isys_obj__id = ' . $this->convert_sql_id($p_object_id) . '
			LIMIT 1;';

        $l_ret = $this->retrieve($l_sql)
            ->get_row();

        $l_dao_relpool = isys_cmdb_dao_category_s_parallel_relation::instance($this->m_db);

        if ($l_ret["isys_catg_vrrp_member_list__isys_cats_relpool_list__id"] > 0)
        {
            $l_dao_relpool->save(
                $l_ret["isys_catg_vrrp_member_list__isys_cats_relpool_list__id"],
                $p_relation_title,
                $l_ret["isys_cats_relpool_list__threshold"],
                $l_ret["isys_cats_relpool_list__description"]
            );

            return $l_ret["isys_catg_vrrp_member_list__isys_cats_relpool_list__id"];
        }
        else
        {
            return $l_dao_relpool->create($this->create_object($p_relation_title, C__OBJTYPE__PARALLEL_RELATION), $p_relation_title, 0, '');
        } // if
    } // function
} // class