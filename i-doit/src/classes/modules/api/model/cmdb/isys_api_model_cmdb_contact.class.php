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
 * @author     Van Quyen Hoang <qhoang@synetics.de>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_api_model_cmdb_contact extends isys_api_model_cmdb implements isys_api_model_interface
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
        ]
    ];
    /**
     * Possible options and their parameters
     *
     * @var array
     */
    protected $m_options = [
        'read' => [
            'email' => [
                'type'     => 'string',
                'optional' => true
            ],
            'id'    => [
                'type'     => 'int',
                'optional' => true
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
     * Fetches information of the person
     *
     * @author Van Quyen Hoang <qhoang@synetics.de>
     */
    public function read($p_params)
    {
        assert('is_array($p_params)');
        assert('array_key_exists("filter", $p_params)');

        // One or more filters have to be set:
        $l_filter = [];

        /**
         * @deprecated still handle deprecated call parameter
         */
        if (isset($p_params['call']))
        {
            $l_method = $p_params['call'];
        }

        else
        {
            if (isset($p_params['method']))
            {
                $l_method = $p_params['method'];
            }
            else throw new isys_exception_api('You need to specify the parameter "method"');
        }

        if (isset($p_params['filter']) && is_array($p_params['filter']))
        {
            $l_filter = $p_params['filter'];
            if (isset($l_filter['id']))
            {
                $l_filter['id'] = (int) $l_filter['id'];
            }
        }

        if (count($l_filter) === 0)
        {
            throw new isys_exception_api(
                'Invalid parameter. At least one filter is required.'
            );
        }

        if (method_exists($this, $l_method))
        {
            return $this->$l_method($l_filter);
        }
        else
        {
            return false;
        }
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
     * Retrieves all assigned objects by contact
     *
     * @param $p_filter
     *
     * @return array
     */
    private function assigned_objects_by_contact($p_filter)
    {
        $l_return = [];

        /* Data retrieval */
        $l_cats_person_contact_assign = new isys_cmdb_dao_category_g_contact($this->m_db);

        if (isset($p_filter['email']))
        {
            $this->m_log->info('Retrieve object(s) by email address "' . $p_filter['email'] . '".');
            $l_assigned_objects = $l_cats_person_contact_assign->get_assigned_objects_by_contact(null, $p_filter['email']);
        }
        else if (isset($p_filter['id']))
        {
            $this->m_log->info('Retrieve object(s) by ID "' . $p_filter['id'] . '".');
            $l_assigned_objects = $l_cats_person_contact_assign->get_assigned_objects_by_contact($p_filter['id']);
        }

        if (isset($l_assigned_objects))
        {
            /* Iterate through found persons */
            while ($l_row = $l_assigned_objects->get_row())
            {
                $l_return[$l_row['isys_obj__id']]            = $this->format_by_mapping($this->m_mapping, $l_row);
                $l_return[$l_row['isys_obj__id']]['primary'] = ($l_row['isys_catg_contact_list__primary_contact'] > 0) ? _L('LC__UNIVERSAL__YES') : _L('LC__UNIVERSAL__NO');
                $l_return[$l_row['isys_obj__id']]['role']    = _L($l_row['isys_contact_tag__title']);
            } // while
        }

        return $l_return;
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