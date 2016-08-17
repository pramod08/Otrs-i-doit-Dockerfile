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
class isys_api_model_cmdb_location_tree extends isys_api_model_cmdb implements isys_api_model_interface
{

    /**
     * Data formatting used in format methods
     *
     * @var array
     */
    protected $m_mapping = [
        'isys_obj__id'                   => 'id',
        'isys_obj__title'                => 'title',
        'isys_obj__sysid'                => 'sysid',
        'isys_obj__isys_obj_type__id'    => 'type',
        'isys_obj_type__title'           => [
            '_L',
            'type_title'
        ],
        'isys_obj__status'               => 'status',
        'isys_obj__isys_cmdb_status__id' => 'cmdb_status',
        'isys_cmdb_status__title'        => [
            '_L',
            'cmdb_status_title'
        ]
    ];
    /**
     * Possible options and their parameters
     *
     * @var array
     */
    protected $m_options = [
        'read' => [

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
     * Documentation missing
     *
     * @param array $p_params
     *
     * @return array
     */
    public function read($p_params)
    {
        assert('is_array($p_params)');

        // Raw mode:
        $l_raw = false;
        if (array_key_exists('raw', $p_params))
        {
            $l_raw = filter_var($p_params['raw'], FILTER_VALIDATE_BOOLEAN);
        } // if

        // Location identifier:
        $l_id = null;
        if (array_key_exists('id', $p_params))
        {
            assert('is_numeric($p_params["id"]) && $p_params["id"] >= 0');
            $l_id = $p_params['id'];
        } // if

        // Data retrieval:
        $l_loc_dao = new isys_cmdb_dao_location($this->m_db);
        $l_data    = $l_loc_dao->get_child_locations($l_id);

        $l_return = [];

        // Data formatting:
        while ($l_row = $l_data->get_row())
        {
            $l_new = null;
            if ($l_raw)
            {
                $l_new = $l_row;
            }
            else
            {
                $l_new = $this->format_by_mapping($this->m_mapping, $l_row);
            } // if raw
            $l_return[] = $l_new;
        } // while

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