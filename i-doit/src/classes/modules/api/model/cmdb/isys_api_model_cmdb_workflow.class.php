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
 * @author     Selcuk Kekec <skekec@synetics.de>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_api_model_cmdb_workflow extends isys_api_model_cmdb implements isys_api_model_interface
{
    /**
     * Data formatting used in format methods
     *
     * @var array
     */
    protected $m_mapping = [
        'isys_workflow__id'                         => 'id',
        'isys_workflow__title'                      => 'title',
        'isys_workflow__isys_workflow__id'          => 'parent_id',
        'isys_workflow__isys_contact__id'           => 'contact_id',
        'isys_workflow__isys_workflow_type__id'     => 'type',
        'isys_workflow__isys_workflow_category__id' => 'category',
        'isys_workflow__isys_obj__id'               => 'object_id',
        'wf_object_title'                           => 'object_title',
        'startdate'                                 => 'start_date',
    ];
    /**
     * Possible options and their parameters
     *
     * @var array
     */
    protected $m_options = [
        'read'   => [
            'id'        => [
                'type'        => 'int',
                'description' => 'ID of Wirkflow entry',
                'reference'   => 'isys_workflow__id',
                'optional'    => true
            ],
            'object_id' => [
                'type'        => 'int',
                'description' => 'ID of object',
                'reference'   => 'isys_obj__id',
                'optional'    => true
            ],
            'parent_id' => [
                'type'        => 'int',
                'description' => 'ID of object',
                'reference'   => 'isys_obj__id',
                'optional'    => true
            ],
            'type'      => [
                'type'        => 'int',
                'description' => 'Workflow-Type: Use 1. Task or 2. Checklist',
                'reference'   => 'isys_workflow_type__id',
                'optional'    => false
            ],
            'filter'    => [
                'type'        => 'string',
                'description' => 'Specifies an custom filter condition',
                'reference'   => '',
                'optional'    => true
            ],
            'date_from' => [
                'type'        => 'string [YYYY-mm-dd]',
                'description' => 'Start-Date',
                'reference'   => 'isys_workflow_action_parameter__datetime',
                'optional'    => true
            ],
            'date_to'   => [
                'type'        => 'string [YYYY-mm-dd]',
                'description' => 'End-Date',
                'reference'   => 'isys_workflow_action_parameter__datetime',
                'optional'    => true
            ],
            'order_by'  => [
                'type'        => 'int',
                'description' => 'Custom SQL condition',
                'reference'   => '',
                'optional'    => true
            ],
            /*'user_id'       => array(
                'type'		  => 'int',
                'description' => 'Custom SQL condition',
                'reference'   => '',
                'optional'	  => true
            ),*/
            /*'owner_mode'       => array(
                'type'		  => 'int',
                'description' => 'Custom SQL condition',
                'reference'   => '',
                'optional'	  => true
            ),*/
            'status'    => [
                'type'        => 'int',
                'description' => 'Workflow-Status',
                'reference'   => 'isys_workflow__status',
                'optional'    => true
            ],
        ],
        'create' => [
            'object_id'   => [
                'type'        => 'int',
                'description' => 'ID of the object to attach the logbook message to',
                'reference'   => 'isys_obj__id',
                'optional'    => false
            ],
            'message'     => [
                'type'        => 'string',
                'description' => 'Message',
                'reference'   => 'isys_logbook__event_static',
                'optional'    => false
            ],
            'description' => [
                'type'        => 'string',
                'description' => 'Description',
                'reference'   => 'isys_logbook__description',
                'optional'    => true
            ],
            'comment'     => [
                'type'        => 'string',
                'description' => 'User comment',
                'reference'   => 'isys_logbook__comment',
                'optional'    => true
            ],
            'source'      => [
                'type'        => 'string',
                'description' => 'Constant or ID of Logbook-source',
                'reference'   => 'isys_logbook__isys_logbook_source__id',
                'optional'    => true
            ],
            'alert_level' => [
                'type'        => 'string',
                'description' => 'Constant or ID of Logbook-alert-level',
                'reference'   => 'isys_logbook__isys_logbook_level__id',
                'optional'    => true
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
     * Retrieve a Workflow
     *
     * @param array $p_params
     *
     * @return array
     */
    public function read($p_params)
    {
        /* Init */
        $l_return       = [];
        $l_data         = null;
        $l_workflow_dao = new isys_workflow_dao($this->m_db);
        $l_return       = [];
        $l_data         = $l_workflow_dao->get_workflows(
            @$p_params['id'],
            @$p_params['parent_id'],
            @$p_params['type'],
            /*actiontype*/
            null,
            @$p_params['filter'],
            /*limit*/
            null,
            @$p_params['date_from'],
            @$p_params['date_to'],
            @$p_params['order_by'],
            @$p_params['user_id'],
            @$p_params['owner_mode'],
            @$p_params['status']
        );

        if ($l_data->num_rows())
        {
            while ($l_row = $l_data->get_row())
            {
                $l_return[] = $this->format_by_mapping($this->m_mapping, $l_row);
            }
        }

        return $l_return;
    } // function

    /**
     * Creates new data.
     *
     * @param array $p_params Parameters:
     *                        'object_id' (int) Object identifier -or-
     *                        'object_ids' (JSON string|array) List of object identifiers;
     *                        'title' (string) Workflows title,
     *                        'creator' (int | string) Creators ID
     *                        'type'  (int) Workflow-Type-Id (1: Task, 2: Checklist)
     *                        'category' (int) Workflow-Category-Id
     *
     * @return isys_api_model_cmdb Returns itself.
     * @throws isys_exception_api
     */
    public function create($p_params)
    {
        $l_object_ids   = [];
        $l_workflow_ids = [];
        $l_dao_dialog   = new isys_cmdb_dao_dialog_admin($this->m_db);

        if (isset($p_params['object_id']))
        {
            if (!is_numeric($p_params['object_id']) || $p_params['object_id'] <= 0)
            {
                throw new isys_exception_api(
                    sprintf(
                        'Object identifier "%s" is invalid.',
                        $p_params['object_id']
                    )
                );
            } //if

            $l_object_ids[] = $p_params['object_id'];
        } //if

        if (isset($p_params['object_ids']))
        {
            $l_ids = [];

            if (is_string($p_params['object_ids']))
            {
                try
                {
                    $l_ids = isys_format_json::decode($p_params['object_ids']);
                }
                catch (Exception $e)
                {
                    throw new isys_exception_api('Invalid JSON array for object identifiers.');
                } //try/catch
            }
            else if (is_array($p_params['object_ids']))
            {
                $l_ids = $p_params['object_ids'];
            }
            else
            {
                throw new isys_exception_api('Object identifiers are invalid.');
            } //if

            foreach ($l_ids as $l_object_id)
            {
                if (!is_numeric($l_object_id) || $l_object_id <= 0)
                {
                    throw new isys_exception_api(
                        sprintf(
                            'Object identifier "%s" is invalid.',
                            $l_object_id
                        )
                    );
                } //if

                $l_object_ids[] = $l_object_id;
            } //foreach
        } //if

        if (count($l_object_ids) === 0)
        {
            throw new isys_exception_api('There are no object identifiers given.');
        } //if

        $l_workflow_dao = new isys_workflow_dao($this->m_db);

        /* Create a contact bundle for the workflow assignment */
        if ($p_params['creator'])
        {
            /* Retrieve the creator-id */
            if (is_string($p_params['creator']))
            {
                $l_title             = $p_params['creator'];
                $p_params['creator'] = $l_dao_dialog->get_obj_id_by_title($p_params['creator'], C__OBJTYPE__PERSON);

                if (!$p_params['creator']) return $this->api_success(
                    false,
                    'Object \'' . $l_title . '\' of type \'Person\' does not exist!'
                );
            }

            $l_contact_dao = new isys_contact_dao_reference($this->m_db);
            $l_contact_dao->insert_data_item($p_params['creator']);
            if ($l_contact_dao->save(null)) $p_params['creator'] = $l_contact_dao->get_id();
        }

        /* Create a contact bundle for the workflow assignment */
        if (is_array($p_params['contact_id']))
        {
            $l_contact_dao = new isys_contact_dao_reference($this->m_db);
            $l_contact_dao->set_data_items($p_params['contact_id']);
            if ($l_contact_dao->save(null)) $p_params['contact_id'] = $l_contact_dao->get_id();
        }

        foreach ($l_object_ids as $l_object_id)
        {

            /* Type-Handling */
            if (!is_numeric($p_params['type']) || $l_dao_dialog->get_data("isys_workflow_type", $p_params['type'])
                    ->num_rows() <= 0
            )
            {
                return $this->api_success(
                    false,
                    'The \'type\' property is invalid. Use [ 1. Task ] or [  2. Checklist  ].'
                );
            }

            // Category-Handling.
            if (isset($p_params['category']) && !is_numeric($p_params['category']))
            {
                $l_res = $l_dao_dialog->get_by_title("isys_workflow_category", $p_params['category']);

                if ($l_res->num_rows())
                {
                    $p_params['category'] = $l_res->get_row_value('isys_workflow_category__id');
                }
                else
                {
                    $p_params['category'] = $l_dao_dialog->create("isys_workflow_category", $p_params['category'], null, null, 2);
                } // if
            } // if

            /* Create Workflow */
            $l_status = $l_workflow_dao->create_workflow(
                $p_params['title'],
                $p_params['creator'],
                $p_params['type'],
                $p_params['category'],
                $l_object_id,
                null,
                null,
                null
            );

            if ($l_status)
            {
                /* Workflow Request */
                $l_workflow_request = new isys_workflow_request(
                    [
                        'task__start_date'  => $p_params['start_date'],
                        'task__end_date'    => $p_params['end_date'],
                        'task__description' => $p_params['description'],
                        'task__cc_to'       => $p_params['cc_to']
                    ], 0, 0
                );

                /* Save the Actions */
                $l_workflow_action_new = new isys_workflow_action_new();
                $l_status              = $l_workflow_action_new->save($l_workflow_dao->get_id(), $l_workflow_request, $p_params['contact_id']);

                if ($l_status === false)
                {
                    return $this->api_success(
                        false,
                        'Unknown database error while creating Workflow entry.'
                    );
                }
                else
                {
                    $l_workflow_ids[] = $l_workflow_dao->get_id();
                }
            }
        } //foreach

        return $this->api_success(
            true,
            'Workflow entry/entries successfully created.',
            $l_workflow_ids
        );
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
