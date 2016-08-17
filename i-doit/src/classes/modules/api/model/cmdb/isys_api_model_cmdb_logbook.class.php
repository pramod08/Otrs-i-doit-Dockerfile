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
class isys_api_model_cmdb_logbook extends isys_api_model_cmdb implements isys_api_model_interface
{

    /**
     * Data formatting used in format methods
     *
     * @var array
     */
    protected $m_mapping = [
        'isys_logbook__id'                     => 'logbook_id',
        'isys_catg_logb_list__id'              => 'logbook_catg_id',
        'isys_logbook__comment'                => 'comment',
        'isys_logbook__description'            => [
            'stripslashes',
            'description'
        ],
        'isys_logbook__changes'                => [
            'isys_api_model_cmdb_logbook::read_changes',
            'changes'
        ],
        'isys_logbook__date'                   => 'date',
        'isys_logbook__user_name_static'       => 'username',
        'isys_logbook__event_static'           => 'event',
        'isys_obj__id'                         => 'object_id',
        'isys_obj__title'                      => 'object_title',
        'isys_logbook__obj_name_static'        => 'object_title_static',
        'isys_logbook_source__title'           => [
            '_L',
            'source'
        ],
        'isys_logbook_source__const'           => 'source_constant',
        'isys_logbook__isys_logbook_level__id' => 'level_id'
    ];
    /**
     * Possible options and their parameters
     *
     * @var array
     */
    protected $m_options = [
        'read'   => [
            'id'              => [
                'type'        => 'int',
                'description' => 'ID of logbook entry',
                'reference'   => 'isys_logbook__id',
                'optional'    => true
            ],
            'object_id'       => [
                'type'        => 'int',
                'description' => 'ID of object',
                'reference'   => 'isys_obj__id',
                'optional'    => true
            ],
            'catg_logbook_id' => [
                'type'        => 'int',
                'description' => 'ID of logbook category',
                'reference'   => 'isys_catg_logb_list__id',
                'optional'    => true
            ]
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
     * @static
     *
     * @param $p_changes
     */
    public static function read_changes($p_changes)
    {
        if ($p_changes)
        {
            return (stripslashes_deep(unserialize($p_changes)));
        }
        else return null;
    }

    /**
     * Documentation missing
     *
     * @param array $p_params
     *
     * @return array
     */
    public function read($p_params)
    {
        /* Init */
        $l_return      = [];
        $l_data        = null;
        $l_condition   = '';
        $l_logbook_dao = new isys_cmdb_dao_category_g_logb($this->m_db);

        /* Get raw parameter */
        $l_raw = filter_var(@$p_params['raw'], FILTER_VALIDATE_BOOLEAN);

        /**
         * Display logbook changes since specified strtotime value
         */
        if (isset($p_params['since']))
        {
            $l_condition .= ' AND (isys_logbook__date >= \'' . date('Y-m-d H:i:s', strtotime($p_params['since'])) . '\')';
        }

        if (isset($p_params['limit']))
        {
            $l_limit = $p_params['limit'];
        }
        else
        {
            $l_limit = 1000;
        }

        if (isset($p_params['status']))
        {
            if (is_string($p_params['status']) && defined($p_params['status']))
            {
                $p_params['status'] = constant($p_params['status']);
            }

            $l_condition .= ' AND (isys_obj__status = \'' . (int) $p_params['status'] . '\')';
        }
        else
        {
            $l_condition .= ' AND (isys_obj__status = \'' . C__RECORD_STATUS__NORMAL . '\')';
        }

        /* Extract parameters */
        if (isset($p_params['object_id']))
        {
            $l_data = $l_logbook_dao->get_data(null, $p_params['object_id'], $l_condition, null, null, $l_limit);
        }
        else if (isset($p_params[C__CMDB__GET__OBJECT]))
        {
            $l_data = $l_logbook_dao->get_data(null, $p_params[C__CMDB__GET__OBJECT], $l_condition, null, null, $l_limit);
        }
        else if (isset($p_params['id']))
        {
            $l_data = $l_logbook_dao->get_data(null, null, ' AND isys_logbook__id = \'' . (int) $p_params['id'] . '\'' . $l_condition, null, $l_limit);
        }
        else if (isset($p_params['logbook_catg_id']))
        {
            $l_data = $l_logbook_dao->get_data($p_params['logbook_catg_id'], null, $l_condition, null, null, $l_limit);
        }
        else if (!empty($l_condition))
        {
            $l_data = $l_logbook_dao->get_data(null, null, $l_condition, null, null, $l_limit);
        }

        if (is_object($l_data))
        {
            // Data formatting:
            while ($l_row = $l_data->get_row())
            {
                $l_return[] = $l_raw ? $l_row : $this->format_by_mapping($this->m_mapping, $l_row);
            } // while
        }
        else
        {

            throw new isys_exception_api('Parameter error: either object_id, logbook id or since parameter missing.');

        }

        return $l_return;
    } // function

    /**
     * Creates new data.
     *
     * @param string $p_method Data method
     * @param array  $p_params Parameters:
     *                         'object_id' (int) Object identifier -or-
     *                         'object_ids' (JSON string|array) List of object identifiers;
     *                         'alert_level' (int; optional) Alert level defaults to C__LOGBOOK__ALERT_LEVEL__0;
     *                         'source' (string; optional) Source defaults to 'C__LOGBOOK_SOURCE__EXTERNAL';
     *                         'message' (string) Message defaults to null;
     *                         'description' (string; optional) Description defaults to null;
     *                         'comment' (string; optional) Message defaults to an empty string
     *
     * @return isys_api_model_cmdb Returns itself.
     * @throws isys_exception_api
     */
    public function create($p_params)
    {
        $l_object_ids = [];

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

        /* Grab some default values */
        $l_alert_level = isset($p_params['alert_level']) ? $p_params['alert_level'] : C__LOGBOOK__ALERT_LEVEL__0;

        /* Source */
        $l_source = isset($p_params['source']) ? constant($p_params['source']) : C__LOGBOOK_SOURCE__EXTERNAL;

        if (!isset($p_params['message']))
        {
            throw new isys_exception_api('Message is empty.');
        } //if
        $l_message = $p_params['message'];

        $l_description = null;
        if (isset($p_params['description']))
        {
            $l_description = $p_params['description'];
        } //if

        $l_comment = '';
        if (isset($p_params['comment']))
        {
            $l_comment = $p_params['comment'];
        } //if

        $l_ticket = null;
        // Comment contains ticket number:
        if (!empty($l_comment))
        {
            $l_ticket = ' [' . $l_comment . ']';
        } //if

        /* Init logbook dao */
        $l_logbook_dao = new isys_component_dao_logbook($this->m_db);

        /* Init CMDB Dao */
        $l_cmdb_dao = new isys_cmdb_dao($this->m_db);

        foreach ($l_object_ids as $l_object_id)
        {
            $l_object = $l_cmdb_dao->get_object_by_id($l_object_id)
                ->__to_array();

            if (count($l_object) === 0)
            {
                throw new isys_exception_api('Object does not exist.');
            } //if

            // Add object title and type to message:
            $l_logbook_message = sprintf(
                _L('LC__CMDB__LOGBOOK__TTS'),
                $l_object['isys_obj__title'],
                _L($l_object['isys_obj_type__title']),
                $l_message,
                $l_description,
                $l_ticket
            );

            /* Create entry */
            $l_status = $l_logbook_dao->set_entry(
                $l_logbook_message,
                $l_description,
                null,
                $l_alert_level,
                $l_object_id,
                $l_cmdb_dao->get_obj_name_by_id_as_string($l_object_id),
                $l_cmdb_dao->get_objtype_name_by_id_as_string($l_object_id),
                null,
                $l_source,
                '',
                $l_comment
            );

            if ($l_status === false)
            {
                return $this->api_success(
                    false,
                    'Unknown database error while creating logbook entry.'
                );
            } //if
        } //foreach

        return $this->api_success(
            true,
            'Logbook entry/entries successfully created.',
            $l_logbook_dao->get_logbook_id()
        );
    } // function

    /**
     * Deletes data.
     *
     * @param string $p_method Data method
     * @param array  $p_params Parameters (depends on data method)
     *
     * @return isys_api_model_cmdb Returns itself.
     */
    public function delete($p_params)
    {
        throw new isys_exception_api('Deleting logbook entries is prohibited');
    } // function

    /**
     * Updates data.
     *
     * @param string $p_method Data method
     * @param array  $p_params Parameters (depends on data method)
     *
     * @return isys_api_model_cmdb Returns itself.
     */
    public function update($p_params)
    {
        throw new isys_exception_api('Updating logbook entries is prohibited');
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
