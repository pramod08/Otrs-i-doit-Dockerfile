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
 * API model.
 *
 * @package     i-doit
 * @subpackage  API
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_api_model_cmdb_object extends isys_api_model_cmdb implements isys_api_model_interface
{
    /**
     * Data formatting used in format methods.
     *
     * @var  array
     */
    protected $m_mapping = [
        'isys_obj__id'                     => 'id',
        'isys_obj__title'                  => 'title',
        'isys_obj__sysid'                  => 'sysid',
        'isys_obj__isys_obj_type__id'      => 'objecttype',
        'isys_obj_type__title'             => [
            '_L',
            'type_title'
        ],
        'isys_obj_type__icon'              => 'type_icon',
        'isys_obj__status'                 => 'status',
        'isys_obj__isys_cmdb_status__id'   => 'cmdb_status',
        'isys_cmdb_status__title'          => [
            '_L',
            'cmdb_status_title'
        ],
        'isys_obj__created'                => 'created',
        'isys_obj__updated'                => 'updated',
        'isys_catg_image_list__image_link' => [
            'isys_api_model_cmdb_objects::get_image_url',
            'image'
        ]
    ];
    /**
     * Possible options and their parameters.
     *
     * @var  array
     */
    protected $m_options = [
        'read'       => [
            'id' => [
                'type'        => 'int',
                'description' => 'Object id',
                'reference'   => 'isys_obj__id',
                'optional'    => false
            ]
        ],
        'create'     => [
            'title'       => [
                'type'        => 'string',
                'description' => 'Object title',
                'reference'   => 'isys_obj__title',
                'optional'    => false
            ],
            'type'        => [
                'type'        => 'int|string',
                'description' => 'Object type as string constant or id',
                'reference'   => 'isys_obj_type__id',
                'optional'    => false
            ],
            'cmdb_status' => [
                'type'        => 'int|string',
                'description' => 'Cmdb status id or constant',
                'reference'   => 'isys_obj__isys_cmdb_status__id',
                'optional'    => true
            ]
        ],
        'update'     => [],
        'delete'     => [],
        'quickpurge' => []
    ];
    /**
     * Validation.
     *
     * @var  array
     */
    protected $m_validation = [];

    /**
     * Fetches information about an object.
     *
     * @param   array $p_params Parameters. Structure: array('id' => 1).
     *
     * @return  array  Returns an empty array when an error occures.
     */
    public function read($p_params)
    {
        $l_return = [];

        if (isset($p_params[C__CMDB__GET__OBJECT]))
        {
            $p_params['id'] = $p_params[C__CMDB__GET__OBJECT];
            unset($p_params[C__CMDB__GET__OBJECT]);
        } // if

        if (isset($p_params['id']) && $p_params['id'])
        {
            $this->m_log->info('Retrieving object with id ' . $p_params['id']);

            // Data retrieval.
            $l_data = $this->m_dao->get_object_by_id($p_params['id']);

            // Data formatting.
            if ($l_data->count() > 0)
            {
                return $this->format_by_mapping($this->m_mapping, $l_data->get_row());
            } // if
        }
        else
        {
            $this->m_log->error('Object ID missing.');
        } // if

        return $l_return;
    } // function

    /**
     * Creates an object.
     *
     * @param   array $p_params Parameters (depends on data method).
     *
     * @return  isys_api_model_cmdb  Returns itself.
     * @throws  isys_exception_api
     */
    public function create($p_params)
    {
        $l_return = [];

        if (!isset($p_params['title']))
        {
            throw new isys_exception_api('Object title missing');
        } // if

        if (isset($p_params['type']))
        {
            try
            {
                // Object-Type.
                $p_params['type'] = is_numeric($p_params['type']) ? $p_params['type'] : (defined($p_params['type']) ? constant($p_params['type']) : null);

                // CMDB-Status.
                $p_params['cmdb_status'] = is_numeric($p_params['cmdb_status']) ? $p_params['cmdb_status'] : (defined($p_params['cmdb_status']) ? constant(
                    $p_params['cmdb_status']
                ) : null);

                if ($p_params['type'] > 0 && $this->m_dao->get_objtype($p_params['type'])
                        ->num_rows() > 0
                )
                {
                    // insert the object.
                    $l_return['id'] = $this->m_dao->insert_new_obj(
                        $p_params['type'],
                        false,
                        $p_params['title'],
                        null,
                        C__RECORD_STATUS__NORMAL,
                        null,
                        null,
                        false,
                        null,
                        null,
                        null,
                        null,
                        $p_params['category'],
                        $p_params['purpose'],
                        $p_params['cmdb_status'],
                        $p_params['description']
                    );

                    if ($l_return['id'] > 0)
                    {
                        // Create logbook entry.
                        isys_event_manager::getInstance()
                            ->triggerCMDBEvent(
                                'C__LOGBOOK_EVENT__OBJECT_CREATED',
                                '-object initialized-',
                                $l_return['id'],
                                $this->m_dao->get_objTypeID($l_return['id'])
                            );

                        $l_return['message'] = 'Object was successfully created';
                        $l_return['success'] = true;
                    }
                    else
                    {
                        $l_return['message'] = 'Error while creating object';
                        $l_return['success'] = false;
                    } // if
                }
                else
                {
                    throw new isys_exception_api('Object type not found.');
                } // if
            }
            catch (isys_exception_cmdb $e)
            {
                throw new isys_exception_api($e->getMessage());
            } // try
        }
        else
        {
            throw new isys_exception_api('Object type missing');
        } // if

        return $l_return;
    } // function

    /**
     * Deletes an object.
     *
     * @param   array $p_params Parameters (depends on data method).
     *
     * @return  isys_api_model_cmdb  Returns itself.
     * @throws  isys_exception_api
     */
    public function delete($p_params)
    {
        $l_messageText = '';

        $l_return = [
            'message' => 'Error while deleting object(s)',
            'success' => false
        ];

        if (isset($p_params['ids']))
        {
            $p_params['id'] = $p_params['ids'];
        } // if

        if (!isset($p_params['id']))
        {
            throw new isys_exception_api('Object id missing');
        }
        else
        {
            if (is_numeric($p_params['id']))
            {
                $l_id = $p_params['id'];
                unset($p_params['id']);
                $p_params['id'] = [$l_id];
            } // if

            if (is_array($p_params['id']))
            {
                $l_status = C__RECORD_STATUS__DELETED;
                if (isset($p_params['status']))
                {
                    if (is_string($p_params['status']) && defined($p_params['status']))
                    {
                        $p_params['status'] = constant($p_params['status']);
                    }

                    if (($p_params['status'] == C__RECORD_STATUS__DELETED || $p_params['status'] == C__RECORD_STATUS__ARCHIVED || $p_params['status'] == C__RECORD_STATUS__PURGE))
                    {
                        $l_status = $p_params['status'];
                    } // if
                }

                foreach ($p_params['id'] as $l_id)
                {
                    switch ($l_status)
                    {
                        case C__RECORD_STATUS__PURGE:
                            $l_messageText  = 'purged';
                            $l_logbookEvent = 'C__LOGBOOK_EVENT__OBJECT_PURGED';
                            $l_result       = $this->m_dao->delete_object($l_id);
                            break;
                        case C__RECORD_STATUS__ARCHIVED:
                            $l_messageText  = 'archived';
                            $l_logbookEvent = 'C__LOGBOOK_EVENT__OBJECT_ARCHIVED';
                            $l_result       = $this->m_dao->set_object_status($l_id, $l_status);
                            break;
                        default:
                        case C__RECORD_STATUS__DELETED:
                            $l_messageText  = 'deleted';
                            $l_logbookEvent = 'C__LOGBOOK_EVENT__OBJECT_DELETED';
                            $l_result       = $this->m_dao->set_object_status($l_id, $l_status);
                            break;
                    }

                    if (!$l_result)
                    {
                        throw new isys_exception_api(sprintf('Error deleting object with id %s', $l_id));
                    } // if

                    isys_event_manager::getInstance()
                        ->triggerCMDBEvent(
                            $l_logbookEvent,
                            $this->m_dao->get_last_query(),
                            $l_id,
                            $this->m_dao->get_objTypeID($l_id)
                        );
                } // foreach

                $l_return['message'] = 'Object(s) successfully ' . $l_messageText;
                $l_return['success'] = true;
            } // if
        } // if

        return $l_return;
    } // function

    /**
     * Updates data.
     *
     * @param   array $p_params Parameters (depends on data method).
     *
     * @return  isys_api_model_cmdb  Returns itself.
     * @throws  isys_exception_api
     */
    public function update($p_params)
    {
        $l_return = [];

        if (!isset($p_params['id']))
        {
            throw new isys_exception_api('Object id missing');
        }
        else
        {
            $l_dao_global = new isys_cmdb_dao_category_g_global($this->m_dao->get_database_component());

            if ($l_dao_global->save_title($p_params['id'], $p_params['title']))
            {
                // Create logbook entry.
                // @todo type should be retrieved by object ID.
                isys_event_manager::getInstance()
                    ->triggerCMDBEvent(
                        'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                        '- Object title changed to ' . $p_params['title'] . ' -',
                        $p_params['id'],
                        $this->m_dao->get_objTypeID($p_params['id']),
                        'LC__CMDB__CATG__GLOBAL'
                    );

                $l_dao_global->object_changed($p_params['id']);
                $l_return['message'] = 'Object title was successfully updated';
                $l_return['success'] = true;

                return $l_return;
            } // if
        } // if

        $l_return['message'] = 'Error while updating object';
        $l_return['success'] = false;

        return $l_return;
    } // function

    /**
     * Deletes an object.
     *
     * @param   array $p_params Parameters (depends on data method).
     *
     * @return  isys_api_model_cmdb  Returns itself.
     * @throws  isys_exception_api
     */
    public function quickpurge($p_params)
    {
        if (!isys_settings::get('cmdb.quickpurge', false))
        {
            throw new isys_exception_api('Quickpurge is not enabled');
        } // if

        $l_return = [
            'message' => 'Error while quickpurging object(s)',
            'success' => false
        ];

        if (isset($p_params['ids']))
        {
            $p_params['id'] = $p_params['ids'];
        } // if

        if (!isset($p_params['id']))
        {
            throw new isys_exception_api('Object id missing');
        }
        else
        {
            if (is_numeric($p_params['id']))
            {
                $l_id = $p_params['id'];
                unset($p_params['id']);
                $p_params['id'] = [$l_id];
            } // if

            if (is_array($p_params['id']))
            {
                foreach ($p_params['id'] as $l_id)
                {
                    if (!$this->m_dao->rank_record($l_id, C__CMDB__RANK__DIRECTION_DELETE, 'isys_obj', null, true))
                    {
                        throw new isys_exception_api(sprintf('Error while purging object with id %s', $l_id));
                    } // if
                } // foreach

                $l_return['message'] = 'Object(s) successfully purged';
                $l_return['success'] = true;
            } // if
        } // if

        return $l_return;
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