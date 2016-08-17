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
 * IT-Service DAO for the CMDB algorithm.
 *
 * @package     modules
 * @subpackage  itservice
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.4
 */
class isys_itservice_dao_algorithm extends isys_module_dao
{
    /**
     * This variable will cache all found object nodes.
     *
     * @var  array
     */
    private $m_node_cache = [];

    /**
     * This variable will hold the selected service filter.
     *
     * @var  array
     */
    private $m_service_filter = [];

    /**
     * Memory helper.
     * @var  \idoit\Component\Helper\Memory
     */
    private $m_memory = null;

    /**
     * Memory counter - this will help us to only check the memory on every n'th iteration.
     * @var  integer
     */
    private $m_memory_cnt = 0;

    /**
     * This condition can be used to filter the SQL.
     *
     * @var string
     */
    protected $m_sql_condition = null;

    /**
     * Method for setting a service filter.
     *
     * @param  array $p_filter
     *
     * @return  isys_itservice_dao_algorithm
     */
    public function set_filter(array $p_filter)
    {
        $this->m_service_filter = $p_filter;

        return $this;
    } // function

    /**
     * Method for clearing the node cache.
     *
     * @return  isys_itservice_dao_algorithm
     */
    public function clear_node_cache()
    {
        $this->m_node_cache = [];

        return $this;
    } // function

    /**
     * The get_data method always retrieves the data of the main table of this module.
     */
    public function get_data()
    {
        // TODO: Implement get_data() method.
    } // function

    /**
     * This method will recursively walk through every object and follow all relations.
     *
     * @param   isys_tree_node_explorer $p_parent_node
     * @param   integer                 $p_level
     * @param   boolean                 $p_by_master
     *
     * @author  Dennis Stücken <dstuecken@i-doit.de>
     */
    public function relation_walk(&$p_parent_node, $p_level = null, $p_by_master = false)
    {
        if ($p_level !== null && is_numeric($p_level))
        {
            if ($p_level > 0)
            {
                $p_level--;
            }
            else
            {
                return;
            } // if
        } // if

        // Retrieve all related relations and objects, which do not match the filters.
        $l_res = $this->get_object_relations_by_filters($p_parent_node->get_id(), $p_by_master);

        if ($l_res->count())
        {
            while ($l_row = $l_res->get_row())
            {
                $this->m_memory_cnt ++;

                if ($this->m_memory_cnt === 1000)
                {
                    // 1000 iterations will use about 2MB...
                    $this->m_memory->outOfMemoryBreak(2048000);

                    $this->m_memory_cnt = 0;
                } // if

                $l_rel_type = $l_row['isys_catg_relation_list__isys_relation_type__id'];

                if ($l_row['subrelation_count'] > 0)
                {
                    if (!isset($this->m_node_cache[$l_row['isys_catg_relation_list__isys_obj__id'] . '-' . $l_rel_type]))
                    {
                        // Adding the master or slave object as a "faked" relation member to the current node.
                        if (!isset($this->m_node_cache[$l_row['isys_obj__id'] . '-' . $l_rel_type]))
                        {
                            $this->m_node_cache[$l_row['isys_obj__id'] . '-' . $l_rel_type] = $this->format_node($l_row['isys_obj__id'], false, $l_row);
                        } // if

                        // Changing headline to relation type instead of showing the object type title.
                        $l_row['headline'] = $l_row['isys_relation_type__title'];

                        $this->m_node_cache[$l_row['isys_catg_relation_list__isys_obj__id'] . '-' . $l_rel_type] = $this->format_node(
                            $l_row['isys_catg_relation_list__isys_obj__id'],
                            false,
                            $l_row
                        );

                        // Recursing from the beginning of the relation instead of the master or slave object.
                        $this->relation_walk(
                            $this->m_node_cache[$l_row['isys_catg_relation_list__isys_obj__id'] . '-' . $l_rel_type]->add(
                                $this->m_node_cache[$l_row['isys_obj__id'] . '-' . $l_rel_type]
                            ),
                            $p_level,
                            $p_by_master
                        );

                        // Adding the new traversed sub-tree.
                        $p_parent_node->add($this->m_node_cache[$l_row['isys_catg_relation_list__isys_obj__id'] . '-' . $l_rel_type]);
                    }
                }
                else
                {
                    // Check if this node is already attached anywhere.
                    if (!isset($this->m_node_cache[$l_row['isys_obj__id'] . '-' . $l_rel_type]))
                    {
                        $this->m_node_cache[$l_row['isys_obj__id'] . '-' . $l_rel_type] = $this->format_node($l_row['isys_obj__id'], false, $l_row);

                        // Iterate relations
                        $this->relation_walk($this->m_node_cache[$l_row['isys_obj__id'] . '-' . $l_rel_type], $p_level, $p_by_master);

                        $p_parent_node->add($this->m_node_cache[$l_row['isys_obj__id'] . '-' . $l_rel_type]);

                        // Attach the master/slave object to a relation object and recurse (happens on full-traversal of the tree).
                        if ($l_row['relation_ms'])
                        {
                            $this->m_node_cache[$l_row['relation_ms'] . '-' . $l_rel_type] = $this->format_node($l_row['relation_ms'], false);
                            $this->relation_walk($this->m_node_cache[$l_row['relation_ms'] . '-' . $l_rel_type], $p_level, $p_by_master);
                            $this->m_node_cache[$l_row['isys_obj__id'] . '-' . $l_rel_type]->add($this->m_node_cache[$l_row['relation_ms'] . '-' . $l_rel_type]);
                        } // if

                        // Attach the master/slave object to a relation object and recurse (happens on single reload / node-click only).
                        if ($p_parent_node->get_data()
                                ->path('data.obj_type_id') == C__OBJTYPE__RELATION
                        )
                        {
                            $l_master_data = $this->retrieve(
                                'SELECT isys_catg_relation_list__isys_obj__id__' . ($p_by_master ? 'slave' : 'master') . ' AS id
								FROM isys_catg_relation_list
								WHERE isys_catg_relation_list__isys_obj__id = ' . $this->convert_sql_id($p_parent_node->get_id())
                            )
                                ->get_row();

                            if (!isset($this->m_node_cache[$l_master_data['id'] . '-' . $l_rel_type]))
                            {
                                $this->m_node_cache[$l_master_data['id'] . '-' . $l_rel_type] = $this->format_node($l_master_data['id'], false);
                                $this->relation_walk($this->m_node_cache[$l_master_data['id'] . '-' . $l_rel_type], $p_level, $p_by_master);

                                if (!$p_parent_node->has($this->m_node_cache[$l_master_data['id'] . '-' . $l_rel_type]))
                                {
                                    $p_parent_node->add($this->m_node_cache[$l_master_data['id'] . '-' . $l_rel_type]);
                                } // if
                            } // if
                        } // if
                    }
                    else
                    {
                        // Only attach the node if it has not already been attached to the parent.
                        if (!$p_parent_node->has($this->m_node_cache[$l_row['isys_obj__id'] . '-' . $l_rel_type]) && !$p_parent_node->has(
                                $this->m_node_cache[$l_row['isys_obj__id'] . 'D-' . $l_rel_type]
                            )
                        )
                        {
                            // Add the "doubling" to the cache.
                            $this->m_node_cache[$l_row['isys_obj__id'] . 'D-' . $l_rel_type] = $this->format_node($l_row['isys_obj__id'], true, $l_row);

                            $p_parent_node->add($this->m_node_cache[$l_row['isys_obj__id'] . 'D-' . $l_rel_type]);
                        } // if
                    } // if
                } // if
            } // while

            // Free up some memory.
            $l_res->free_result();
        } // if
    } // function

    /**
     * Method for retrieving auth SQL-condition.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function get_auth_sql_condition()
    {
        if ($this->m_sql_condition === null)
        {
            $this->m_sql_condition = '';

            // ID-2896 - Only append the auth-condition, if this feature is enabled.
            if (!!isys_tenantsettings::get('auth.use-in-cmdb-explorer', false))
            {
                $this->m_sql_condition = str_replace('isys_obj__id IN', 'ms.isys_obj__id IN', isys_auth_cmdb_objects::instance()->get_allowed_objects_condition());
            } // if
        } // if

        return $this->m_sql_condition;
    } // function

    /**
     * Method for retrieving all relations which meet the given filter criteria.
     *
     * @param   integer $p_obj_id
     * @param   boolean $p_by_master
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.com>
     * @version Dennis Stücken <dstuecken@i-doit.com> - SQL structure changed; Subqueries added
     */
    public function get_object_relations_by_filters($p_obj_id, $p_by_master = false)
    {
        $l_cmdb_status_filter = [];

        if (isset($this->m_service_filter['cmdb-status']) && is_array($this->m_service_filter['cmdb-status']))
        {
            $l_cmdb_status_filter = $this->m_service_filter['cmdb-status'];
        } // if

        $l_cmdb_status_filter[] = C__CMDB_STATUS__IDOIT_STATUS;
        $l_cmdb_status_filter[] = C__CMDB_STATUS__IDOIT_STATUS_TEMPLATE;

        $p_obj_id      = $this->convert_sql_id($p_obj_id);
        $l_status      = $this->convert_sql_int(C__RECORD_STATUS__NORMAL);
        $l_cmdb_status = $this->prepare_in_condition($l_cmdb_status_filter, true);

        $l_relation_column = 'isys_catg_relation_list__isys_obj__id__' . ($p_by_master ? 'slave' : 'master');

        // We select all relationships, which inherit a certain object.
        // We only select master- and slave-objects which are no templates and have the "normal" status.
        //IF ((SELECT COUNT(*) FROM isys_catg_relation_list relation_sub WHERE relation_sub.isys_catg_relation_list__isys_obj__id__slave = relation.isys_obj__id) > 0, isys_catg_relation_list__isys_obj__id, ms.isys_obj__id) AS isys_obj__id,
        $l_sql = 'SELECT
			 isys_catg_relation_list__isys_obj__id__slave, isys_catg_relation_list__isys_obj__id__master, isys_catg_relation_list__isys_obj__id, isys_catg_relation_list__isys_relation_type__id, isys_relation_type__title,
			 (SELECT COUNT(*) FROM isys_catg_relation_list relation_sub WHERE relation_sub.isys_catg_relation_list__isys_obj__id__slave = relation.isys_obj__id) AS subrelation_count,
			 ms.isys_obj__id, ms.isys_obj__title,
			 isys_obj_type__color, isys_obj_type__id,

			 CASE isys_obj_type__id
			    WHEN ' . (int) C__OBJTYPE__RELATION . ' THEN isys_relation_type__title
			    ELSE isys_obj_type__title
			 END AS headline,

			 CASE isys_obj_type__id
			    WHEN ' . (int) C__OBJTYPE__RELATION . ' THEN (SELECT ' . $l_relation_column . ' FROM isys_catg_relation_list WHERE isys_catg_relation_list__isys_obj__id = ms.isys_obj__id)
			    ELSE NULL
			 END AS relation_ms

			 FROM isys_catg_relation_list
			 INNER JOIN isys_obj AS relation ON relation.isys_obj__id = isys_catg_relation_list__isys_obj__id
			 INNER JOIN isys_obj AS ms ON ms.isys_obj__id = ' . $l_relation_column . '
			 INNER JOIN isys_obj_type ON ms.isys_obj__isys_obj_type__id = isys_obj_type__id
			 INNER JOIN isys_relation_type ON isys_relation_type__id = isys_catg_relation_list__isys_relation_type__id
			 WHERE TRUE
			 ' . $this->get_auth_sql_condition() . '
			 AND isys_catg_relation_list__status = ' . $l_status . '
			 AND relation.isys_obj__status = ' . $l_status . '
			 AND relation.isys_obj__isys_cmdb_status__id ' . $l_cmdb_status . '
			 AND ms.isys_obj__status = ' . $l_status . '
			 AND ms.isys_obj__isys_cmdb_status__id ' . $l_cmdb_status . '
			 AND isys_catg_relation_list__isys_obj__id__' . ($p_by_master ? 'master' : 'slave') . ' = ' . $p_obj_id;

        if ($this->m_service_filter['priority'] !== null)
        {
            $l_sql .= ' AND isys_catg_relation_list__isys_weighting__id < ' . $this->convert_sql_id($this->m_service_filter['priority']);
        } // if

        if (count($this->m_service_filter['relation-type']))
        {
            $l_sql .= ' AND isys_catg_relation_list__isys_relation_type__id ' . $this->prepare_in_condition($this->m_service_filter['relation-type'], true);
        } // if

        if (count($this->m_service_filter['object-type']))
        {
            $l_sql .= ' AND ms.isys_obj__isys_obj_type__id ' . $this->prepare_in_condition($this->m_service_filter['object-type'], true);
        } // if

        return $this->retrieve($l_sql . ';');
    } // function

    /**
     * Method for formatting the single object nodes.
     *
     * @param   integer $p_obj_id
     * @param   boolean $p_doubling
     * @param   array   $p_row_data
     *
     * @return  isys_tree_node_explorer
     * @author  Leonard Fischer <lfischer@i-doit.com>
     * @author  Dennis Stücken <dstuecken@i-doit.com>
     */
    public function format_node($p_obj_id, $p_doubling = false, $p_row_data = [])
    {
        if (count($p_row_data) === 0)
        {
            // Load row_data in case it is not present.
            $p_row_data = $this->retrieve(
                'SELECT isys_obj__title, isys_obj_type__id, isys_obj_type__color, isys_obj_type__title AS headline FROM isys_obj
				LEFT JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
				LEFT JOIN isys_cmdb_status ON isys_cmdb_status__id = isys_obj__isys_cmdb_status__id
				WHERE isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ';'
            )
                ->get_row();
        } // if

        return new isys_tree_node_explorer(
            [
                'id'       => $p_obj_id,
                'name'     => trim($p_row_data['isys_obj__title']),
                'children' => [],
                'data'     => [
                    'doubling'        => (bool) $p_doubling,
                    'member'          => $p_row_data['isys_obj__id'],
                    'obj_id'          => $p_obj_id,
                    'obj_title'       => trim($p_row_data['isys_obj__title']),
                    'obj_type_id'     => $p_row_data['isys_obj_type__id'],
                    'obj_type_title'  => _L($p_row_data['headline']),
                    'obj_type_color'  => '#' . $p_row_data['isys_obj_type__color'],
                    'relation_obj_id' => $p_row_data['isys_catg_relation_list__isys_obj__id'] ?: null
                ]
            ]
        );
    } // function


    /**
     * Constructor. Assigns database component.
     *
     * @param   isys_component_database $p_db
     *
     * @author  Dennis Stücken <dstuecken@i-doit.org>
     */
    public function __construct(isys_component_database $p_db)
    {
        parent::__construct($p_db);

        $this->m_memory = \idoit\Component\Helper\Memory::instance();
    } // function
} // class