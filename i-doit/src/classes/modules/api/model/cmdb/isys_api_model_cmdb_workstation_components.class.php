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
class isys_api_model_cmdb_workstation_components extends isys_api_model_cmdb implements isys_api_model_interface
{

    /**
     * Data formatting used in format methods
     *
     * @var array
     */
    protected $m_mapping = [
        'isys_obj__id'                => 'id',
        'isys_obj__title'             => 'title',
        'isys_obj__sysid'             => 'sysid',
        'isys_obj__isys_obj_type__id' => 'type',
        'isys_obj_type__title'        => [
            '_L',
            'type_title'
        ],
        'isys_obj__status'            => 'status',
        'isys_cmdb_status__id'        => 'cmdb_status'
    ];
    /**
     * Possible options and their parameters
     *
     * @var array
     */
    protected $m_options = [
        'read' => [
            'email' => [
                'type'     => 'array',
                'optional' => false
            ]
        ]
    ];
    /**
     * Validation
     *
     * @var array
     */
    protected $m_validation = [

    ];

    /**
     * Fetches workplace components for an user.
     *
     * @param array $p_params Parameters. Structure:
     *                        string $p_params['filter']['email'] User's email address
     *                        arrays $p_params['filter']['emails'] Users' email addresses
     *
     * @return array Returns an empty array on error. Example:
     * array(
     *     123 => array(
     *         'data' => array(
     *             'title' => 'John Doe',
     *             'email' => 'john.doe@example.net '
     *         ),
     *         'children' => array(
     *             23 => array(
     *                 'data' => array(
     *                   'title' => 'Workplace'
     *                 ),
     *                 'children' => array(
     *                     42 => array(
     *                         'data' => array(
     *                             'title' => 'Client #01'
     *                         )
     *                     ),
     *                     'children' => false
     *                 )
     *             )
     *         )
     *     )
     * )
     *
     * @author Dennis Stücken <dstuecken@synetics.de>
     * @author Benjamin Heisig <bheisig@synetics.de>
     */
    public function read($p_params)
    {
        assert('is_array($p_params)');
        assert('array_key_exists("filter", $p_params)');
        $l_return = [];

        // One or more filters have to be set:
        $l_filter = [];

        foreach ($p_params['filter'] as $l_key => $l_value)
        {
            switch ($l_key)
            {
                case 'id':
                    assert('is_numeric($l_value)');
                    $l_filter['ids'] = [intval($l_value)];
                    break;
                case 'ids':
                    assert('is_array($l_value)');
                    foreach ($l_value as $l_sub_value)
                    {
                        assert('is_numeric($l_sub_value)');
                        $l_filter['ids'][] = intval($l_sub_value);
                    }
                    break;
                case 'email':
                    assert('is_string($l_value)');
                    $l_filter['emails'] = [$l_value];
                    break;
                case 'emails':
                    assert('is_array($l_value)');
                    foreach ($l_value as $l_sub_value)
                    {
                        assert('is_string($l_sub_value)');
                        $l_filter['emails'][] = $l_sub_value;
                    }
                    break;
            }
        }

        if (count($l_filter) === 0)
        {
            throw new isys_exception_api(
                'Invalid parameter. At least one filter is required.'
            );
        }

        /* Data retrieval */
        $l_catg_logunit = new isys_cmdb_dao_category_g_logical_unit($this->m_db);
        $l_cats_person  = new isys_cmdb_dao_category_s_person_master($this->m_db);

        if (isset($l_filter['emails']) && count($l_filter['emails']))
        {
            $this->m_log->info('Retrieve object(s) by email address(es) "' . implode(', ', $l_filter['emails']) . '".');
            $l_persons = $l_cats_person->get_persons_by_email($l_filter['emails']);
        }
        else if (isset($l_filter['ids']) && $l_filter['ids'])
        {
            $this->m_log->info('Retrieve object(s) by IDs "' . implode(', ', $l_filter['ids']) . '".');
            $l_persons = $l_cats_person->get_person_by_id($l_filter['ids']);
        }

        /* Get object type id of workstation */
        $l_workstation_id = $this->m_dao->get_objtype_id_by_const_string('C__OBJTYPE__WORKSTATION');

        /* Iterate through found persons */
        while ($l_row = $l_persons->get_row())
        {

            $l_return[$l_row['isys_obj__id']]['data']               = $this->format_by_mapping($this->m_mapping, $l_row);
            $l_return[$l_row['isys_obj__id']]['data']['email']      = $l_row['isys_cats_person_list__mail_address'];
            $l_return[$l_row['isys_obj__id']]['data']['first_name'] = $l_row['isys_cats_person_list__first_name'];
            $l_return[$l_row['isys_obj__id']]['data']['last_name']  = $l_row['isys_cats_person_list__last_name'];
            $l_return[$l_row['isys_obj__id']]['children']           = false;

            /* Ok at least one person was found. Now try to get its children.. */
            $l_workstation = $l_catg_logunit->get_data_by_parent($l_row['isys_cats_person_list__isys_obj__id']);
            while ($l_wrow = $l_workstation->get_row())
            {

                /* If this child is a workstation, go further and .. */
                if ($this->m_dao->get_objTypeID($l_wrow['isys_obj__id']) == $l_workstation_id)
                {

                    /* .. store data of this workstation .. */
                    $l_return[$l_row['isys_obj__id']]['children'][$l_wrow['isys_obj__id']]['data']     = $this->format_by_mapping($this->m_mapping, $l_wrow);
                    $l_return[$l_row['isys_obj__id']]['children'][$l_wrow['isys_obj__id']]['children'] = false;

                    /* .. and load its components. */
                    $l_components = $l_catg_logunit->get_data_by_parent($l_wrow['isys_obj__id']);
                    while ($l_crow = $l_components->get_row())
                    {

                        /* Ok we are finally there.. Now we can attach the component as a child to the workstation */
                        $l_return[$l_row['isys_obj__id']]['children'][$l_wrow['isys_obj__id']]['children'][$l_crow['isys_obj__id']]['data']     = $this->format_by_mapping(
                            $this->m_mapping,
                            $l_crow
                        );
                        $l_return[$l_row['isys_obj__id']]['children'][$l_wrow['isys_obj__id']]['children'][$l_crow['isys_obj__id']]['children'] = false;
                    }
                }
            }
        }

        return $l_return;
    } // function

    /**
     * @param string $p_method Data method
     * @param array  $p_params Parameters (depends on data method)
     *
     * @return isys_api_model_cmdb Returns itself.
     */
    public function create($p_params)
    {
        throw new isys_exception_api('Creating is not possible here.');
    } // function

    /**
     * @param string $p_method Data method
     * @param array  $p_params Parameters (depends on data method)
     *
     * @return isys_api_model_cmdb Returns itself.
     */
    public function delete($p_params)
    {
        throw new isys_exception_api('Deleting is not possible here.');
    } // function

    /**
     * @param string $p_method Data method
     * @param array  $p_params Parameters (depends on data method)
     *
     * @return isys_api_model_cmdb Returns itself.
     */
    public function update($p_params)
    {
        throw new isys_exception_api('Updating is not possible here.');
    } // function

    /**
     * Constructor
     */
    public function __construct(isys_cmdb_dao &$p_dao)
    {
        $this->m_dao = $p_dao;
        parent::__construct();
    } // function

} // class

?>