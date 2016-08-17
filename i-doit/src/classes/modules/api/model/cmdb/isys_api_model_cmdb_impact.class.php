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
 * API model
 *
 * @package    i-doit
 * @subpackage API
 * @author     Dennis Stücken <dstuecken@synetics.de>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_api_model_cmdb_impact extends isys_api_model_cmdb implements isys_api_model_interface
{
    /**
     * Data formatting used in format methods
     *
     * @var  array
     */
    protected $m_mapping = [
        'isys_obj__isys_obj_type__id' => 'objTypeID',
        'isys_obj_type__title'        => [
            '_L',
            'objectType'
        ],
        'isys_obj__id'                => 'objID'
    ];
    /**
     * Possible options and their parameters.
     *
     * @var  array
     */
    protected $m_options = [
        'read' => [

        ]
    ];
    /**
     * Validation.
     *
     * @var  array
     */
    protected $m_validation = [

    ];
    /**
     * @var array
     */
    private $m_computed_objects = [];
    /**
     * @var isys_cmdb_dao_category_g_relation
     */
    private $m_dao_relation = null;
    /**
     * @var array
     */
    private $m_daocache = [];

    /**
     * Documentation missing.
     *
     * @param   array $p_params
     *
     * @return  array
     * @throws  isys_exception_api
     */
    public function read($p_params)
    {
        assert('is_array($p_params)');

        // Starting point (object id):
        $l_id = null;

        if (isset($p_params['id']))
        {
            $l_id = $p_params['id'];
        } // if

        // Relation type.
        $l_relation_type = null;

        if (isset($p_params['relation_type']))
        {
            if (is_array($p_params['relation_type']))
            {
                $l_relation_type = [];
                foreach ($p_params['relation_type'] as $l_rtype)
                {
                    if (defined($l_rtype))
                    {
                        $l_relation_type[] = constant($l_rtype);
                    }
                }
            }
            else
            {
                $l_relation_type = $p_params['relation_type'];

                if (is_string($l_relation_type) && defined($l_relation_type))
                {
                    $l_relation_type = constant($l_relation_type);
                } // if
            }
        } // if

        if ((is_numeric($l_relation_type) || is_array($l_relation_type)) && $l_id > 0)
        {
            // Init relation dao.
            $this->m_dao_relation = new isys_cmdb_dao_category_g_relation($this->m_dao->get_database_component());

            // Get first object.
            if (($l_object = $this->m_dao_relation->get_object_by_id($l_id)
                ->get_row())
            )
            {
                // Initialize tree.
                $l_tree = new isys_tree_node(
                    [
                        'id'   => $l_object['isys_obj__id'],
                        'name' => $l_object['isys_obj__title'],
                        'data' => $this->format_by_mapping($this->m_mapping, $l_object)
                    ]
                );

                // Create the relation condition.
                $l_relation_condition = (is_numeric($l_relation_type)) ? '= "' . (int) $l_relation_type . '"' : $this->m_dao->prepare_in_condition($l_relation_type);

                // Initialize used daos.
                $this->m_daocache[C__RELATION_TYPE__POWER_CONSUMER] = new isys_cmdb_dao_category_g_power_supplier($this->m_dao->get_database_component());
                $this->m_daocache[C__RELATION_TYPE__NETWORK_PORT]   = new isys_cmdb_dao_category_g_network_port($this->m_dao->get_database_component());

                // Traverse tree.
                $this->traverse($l_tree, $l_id, ' AND isys_relation_type__id ' . $l_relation_condition);

                // Return tree.
                return $l_tree->toArray();
            }
        }
        else
        {
            throw new isys_exception_api('Error: You have to specify the parameters \'id\' and \'relation_type\'');
        }
    } // function

    /**
     *
     * @param   array $p_params Parameters (depends on data method).
     *
     * @return  isys_api_model_cmdb Returns itself.
     * @throws  isys_exception_api
     */
    public function create($p_params)
    {
        throw new isys_exception_api('Creating is not possible here.');
    } // function

    /**
     *
     * @param   array $p_params Parameters (depends on data method)
     *
     * @return  isys_api_model_cmdb Returns itself.
     * @throws  isys_exception_api
     */
    public function delete($p_params)
    {
        throw new isys_exception_api('Deleting is not possible here.');
    } // function

    /**
     *
     * @param   array $p_params Parameters (depends on data method).
     *
     * @return  isys_api_model_cmdb Returns itself.
     * @throws  isys_exception_api
     */
    public function update($p_params)
    {
        throw new isys_exception_api('Updating is not possible here.');
    } // function

    /**
     *
     * @param   integer $p_object_id
     *
     * @return  array
     */
    private function get_additional_data($p_object_id)
    {
        $l_sql = 'SELECT isys_obj_type__color, isys_cmdb_status__color ' . 'FROM isys_obj ' . 'INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id ' . 'INNER JOIN isys_cmdb_status ON isys_obj__isys_cmdb_status__id = isys_cmdb_status__id ' . 'WHERE isys_obj__id = "' . (int) $p_object_id . '";';

        return $this->m_dao->retrieve($l_sql)
            ->get_row();
    } // function

    /**
     *
     * @param  isys_tree $p_tree
     * @param  integer   $p_start_object
     * @param  string    $p_relation_condition
     */
    private function traverse(&$p_tree, $p_start_object, $p_relation_condition)
    {
        if (isset($this->m_computed_objects[$p_start_object]))
        {
            return;
        }
        else
        {
            $this->m_computed_objects[$p_start_object] = true;
        } // if

        $l_relation_data = $this->m_dao_relation->get_data(
            null,
            null,
            'AND (isys_catg_relation_list__isys_obj__id__master = "' . (int) $p_start_object . '" ' . $p_relation_condition . ')'
        );

        while ($l_row = $l_relation_data->get_row())
        {
            // Retrieve additional data for object.
            $l_object = $this->get_additional_data($l_row['isys_catg_relation_list__isys_obj__id__slave']);

            // Individual data.
            $l_data = [];

            // Create a new tree node.
            $l_child = new isys_tree_node(
                [
                    'id'   => $l_row['isys_catg_relation_list__isys_obj__id__slave'],
                    'name' => $l_row['master_title'],
                    'data' => [
                            'relation'    => [
                                'type' => _L($l_row['isys_relation_type__title']),
                                'text' => _L($l_row['isys_relation_type__master'])
                            ],
                            'color'       => $l_object['isys_obj_type__color'],
                            'statusColor' => $l_object['isys_cmdb_status__color']
                        ] + $this->format_by_mapping(
                            $this->m_mapping,
                            $this->m_dao_relation->get_object_by_id($l_row['isys_catg_relation_list__isys_obj__id__slave'])
                                ->get_row()
                        ) + $l_data
                ]
            );

            // Add the child node to it's parent.
            $p_tree->add($l_child);

            // And traverse further down...
            $this->traverse($l_child, $l_row['isys_catg_relation_list__isys_obj__id__slave'], $p_relation_condition);
        } // while
    } // function

    /**
     * Constructor.
     *
     * @param  isys_cmdb_dao $p_dao
     */
    public function __construct(isys_cmdb_dao &$p_dao)
    {
        $this->m_dao = $p_dao;
        parent::__construct();
    } // function
} // class