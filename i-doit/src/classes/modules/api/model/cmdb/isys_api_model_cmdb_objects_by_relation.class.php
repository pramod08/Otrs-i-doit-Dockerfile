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
 * @author     Benjamin Heisig <bheisig@synetics.de>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_api_model_cmdb_objects_by_relation extends isys_api_model_cmdb implements isys_api_model_interface
{

    /**
     * Data formatting used in format methods
     *
     * @var array
     */
    protected $m_mapping = [
        'isys_obj__id'                                  => 'id',
        'isys_obj__title'                               => 'title',
        'isys_cmdb_status__id'                          => 'cmdb_status',
        'isys_cmdb_status__title'                       => [
            '_L',
            'cmdb_status_title'
        ],
        'related'                                       => 'related_object',
        'related_title'                                 => 'related_title',
        'related_type'                                  => 'related_type',
        'related_type_title'                            => [
            '_L',
            'related_type_title'
        ],
        'related_cmdb_status'                           => 'related_cmdb_status',
        'related_cmdb_status_title'                     => [
            '_L',
            'related_cmdb_status_title'
        ],
        'isys_catg_relation_list__isys_obj__id__master' => 'master',
        'isys_catg_relation_list__isys_obj__id__slave'  => 'slave',
        'isys_obj_type__id'                             => 'type',
        'isys_obj_type__title'                          => [
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
     * Return objects by relation.
     * Result is something like:
     *    0 =>
     *    array (size=3)
     *      'id' => string '5845' (length=4)
     *      'related_object' => string '616' (length=3)
     *      'title' => string 'Clientgruppe ist Mitglied von Datenbankserver NEU "' (length=51)
     *    1 =>
     *    array (size=3)
     *      'id' => string '11234' (length=5)
     *      'related_object' => string '617' (length=3)
     *      'title' => string 'Datenbankserver NEU " liefert Strom an PDU' (length=42)
     *
     * $p_params = array(
     *    'raw' => false,
     *  'id' => null,
     *  'relation_type' => 5
     * );
     *
     * @param array $p_params
     *
     * @return array
     */
    public function read($p_params)
    {
        /* Prepare return array */
        $l_return = [];

        /* Prepare filters */
        $l_raw           = (isset($p_params['raw']) && filter_var($p_params['raw'], FILTER_VALIDATE_BOOLEAN));
        $l_id            = (isset($p_params['id'])) ? $p_params['id'] : null;
        $l_relation_type = (isset($p_params['relation_type'])) ? $p_params['relation_type'] : null;

        /* Check if relation_type is a string constant */
        if (is_string($l_relation_type) && defined($l_relation_type))
        {
            $l_relation_type = constant($l_relation_type);
        }

        if (!$l_id)
        {
            throw new isys_exception_api(
                'No object id given. Specify parameter "id" in order to filter objects by relation.'
            );
        }

        /* Start looking for objects */
        $l_dao  = new isys_cmdb_dao_relation($this->m_db);
        $l_data = $l_dao->get_related_objects($l_id, $l_relation_type);

        /* Iterate through result set*/
        while ($l_row = $l_data->get_row())
        {
            // Fetch more information about the related object:
            $l_related = $this->m_dao->get_object_by_id($l_row['related'])
                ->__to_array();

            $l_row['related_title']             = $l_related['isys_obj__title'];
            $l_row['related_type']              = $l_related['isys_obj_type__id'];
            $l_row['related_type_title']        = $l_related['isys_obj_type__title'];
            $l_row['related_cmdb_status_id']    = $l_related['isys_cmdb_status__id'];
            $l_row['related_cmdb_status_title'] = $l_related['isys_cmdb_status__title'];

            $l_key = $l_row['isys_obj__id'];

            if (!$l_raw)
            {
                /* Format output */
                $l_row = $this->format_by_mapping($this->m_mapping, $l_row);
            }

            $l_value = [
                'data'     => $l_row,
                'children' => false
            ];

            $l_return[$l_key] = $l_value;
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