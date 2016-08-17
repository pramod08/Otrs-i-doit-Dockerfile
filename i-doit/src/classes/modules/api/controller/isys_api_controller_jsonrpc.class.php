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
 * API controller for JSON RPC
 *
 * Specification: http://json-rpc.org/wiki/specification
 * JSON-RPC 2.0:  http://jsonrpc.org/spec.html
 *
 * @package    i-doit
 * @subpackage API
 * @author     Dennis Stücken <dstuecken@synetics.de>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_api_controller_jsonrpc extends isys_api_controller
{

    const ERR_Parse      = -32700;
    const ERR_Request    = -32600;
    const ERR_Method     = -32601;
    const ERR_Parameters = -32602;
    const ERR_Internal   = -32603;
    const ERR_Auth       = -32604;
    const ERR_System     = -32099;
    /**
     * Error codes
     *
     * @var array
     */
    protected $m_errors = [
        -32700 => 'Parse error',
        -32600 => 'Invalid request',
        -32601 => 'Method not found',
        -32602 => 'Invalid parameters',
        -32603 => 'Internal error',
        -32604 => 'Authentication error',
        -32099 => 'i-doit system error'
    ];
    /**
     * Request
     *
     * @var string
     */
    protected $m_request;
    /**
     * Single or batch request?
     *
     * @var bool
     */
    private $m_batch_request = false;

    /**
     * Sets some needed Cross Origin Resource Sharing headers
     *
     * Allows other sites to access the JSON-RPC webservice.
     *
     * @param bool  $auth  Enables or disables authentication
     * @param array $allow Defaults to '*' if empty, else sets the allowed sites from array
     */
    public function setCORSheaders($auth = true, $allow = [])
    {
        if (empty($allow))
        {
            isys_core::send_header('Access-Control-Allow-Origin', '*');
        }
        else
        {
            isys_core::send_header('Access-Control-Allow-Origin', join(', ', $allow));
        }

        if ($auth)
        {
            isys_core::send_header(
                'Access-Control-Allow-Headers',
                join(
                    ', ',
                    [
                        isys_core::HTTP_Origin,
                        isys_core::HTTP_RequestedWith,
                        isys_core::HTTP_Content,
                        isys_core::HTTP_Accept,
                        isys_core::HTTP_RPCAuthUser,
                        isys_core::HTTP_RPCAuthPass,
                        isys_core::HTTP_RPCAuthSession
                    ]
                )
            );
            isys_core::send_header('Access-Control-Expose-Headers', isys_core::HTTP_RPCAuthSession);
        }
        else
        {
            isys_core::send_header(
                'Access-Control-Allow-Headers',
                join(
                    ', ',
                    isys_core::HTTP_Origin,
                    isys_core::HTTP_RequestedWith,
                    isys_core::HTTP_Content,
                    isys_core::HTTP_Accept
                )
            );
        }
    } // function

    /**
     * Handles the request and returns itself.
     *
     * @return isys_api_controller_jsonrpc
     */
    public function handle()
    {
        global $g_comp_session, $g_comp_database;

        // Response ID.
        $l_id = 0;

        // Initialize response.
        $l_response = null;

        try
        {
            // Validate request.
            $this->validate_request();

            // Decode request.
            $this->m_request = isys_format_json::decode($this->m_request);

            // Set request header policies.
            $this->setCORSheaders();

            if (!is_array($this->m_request))
            {
                $this->m_request = [];
            } // if

            // Log request.
            $this->m_log->info('JSON-RPC controller started for request: ' . str_replace("\n", "", var_export($this->m_request, true)));

            // Define wheather this is a batch requets or not.
            if (isset($this->m_request[0]))
            {
                $this->m_batch_request = true;
            }
            else if (isset($this->m_request['jsonrpc']) || isset($this->m_request['version']))
            {
                // Single request.
                $this->m_request = [$this->m_request];
            } // if

            // Iterate through requests.
            foreach ($this->m_request as $l_request)
            {
                try
                {
                    // Validate JSON-RPC request.
                    if ($this->validate_jsonrpc($l_request))
                    {
                        $this->m_log->debug('Request validated. Calling Method: "' . $l_request['method'] . '"');

                        // Check if request method is existing.
                        if (!isset($l_request['method']))
                        {
                            // Invalid request!
                            throw new isys_exception_api('Invalid JSON-RPC request package: Parameter "method" not found', isys_api_controller_jsonrpc::ERR_Request);
                        } // if

                        if (!isset($l_request['params']))
                        {
                            // Invalid request.
                            throw new isys_exception_api('Invalid JSON-RPC request package: Parameter "params" not found.', isys_api_controller_jsonrpc::ERR_Request);
                        } // if

                        if (isset($l_request['params']['session']))
                        {
                            // Deprecated parameter.
                            throw new isys_exception_api('The session parameter is deprecated as of version 1.4. Use apikey and header authentication instead.');
                        } // if

                        if (!$g_comp_session->is_logged_in())
                        {
                            if (!isset($l_request['params']['apikey']))
                            {
                                throw new isys_exception_api(
                                    'apikey parameter missing. You need to authenticate yourself in order to use this api method.', isys_api_controller_jsonrpc::ERR_Auth
                                );
                            } // if

                            $this->m_log->info('API-Key: ' . $l_request['params']['apikey']);

                            if ($this->apikey_login($l_request['params']['apikey'], isys_core::header(isys_core::HTTP_RPCAuthSession)))
                            {
                                $this->m_log->info('Logged in as ' . $g_comp_session->get_current_username());

                                // Send back session id.
                                isys_core::send_header(isys_core::HTTP_RPCAuthSession, $g_comp_session->get_session_id());

                                // Remove session parameters.
                                unset($l_request['params']['apikey'], $l_request['params']['language']);
                            } // if
                        }
                        else
                        {
                            $this->m_log->info(
                                sprintf('Using previous authorizaton from %s (%s)', $g_comp_session->get_current_username(), $g_comp_session->get_mandator_name())
                            );
                        } // if

                        if (!isys_tenantsettings::get('api.status', 0))
                        {
                            // This can only be handled after the login!
                            throw new isys_exception_api('API is disabled.');
                        } // if

                        // Override Language:
                        if (isset($l_request['params']['language']))
                        {
                            $l_language = $l_request['params']['language'];
                            $this->set_language($l_language);
                        } // if

                        // Extract request method.
                        $l_call = explode('.', $l_request['method']);

                        // Fallback to _ exploding. This can be replaced by an ifsetor operation when we switched to php 5.3.
                        if (!$l_call)
                        {
                            $l_call = explode('_', $l_request['method']);
                        } // if

                        // If the explode went fine, go further and process the request.
                        if (count($l_call) < 2)
                        {
                            // Invalid request.
                            throw new isys_exception_api(
                                'Request Method should be in this format: namespace.method (Example: cmdb.object)', isys_api_controller_jsonrpc::ERR_Request
                            );
                        } // if

                        // Extract method data.
                        $l_model_class = 'isys_api_model_' . $l_call[0];
                        $l_data_method = $l_call[1];

                        // Load and initialize modules
                        isys_module_manager::instance()
                            ->module_loader();

                        // Get model object.
                        if (!class_exists($l_model_class))
                        {
                            // Object not found.
                            throw new isys_exception_api(
                                'API Namespace "' . $l_call[0] . '" (' . $l_model_class . ') does not exist.', isys_api_controller_jsonrpc::ERR_Method
                            );
                        } // if

                        // Initiate the model.
                        $this->m_model = new $l_model_class();

                        // API-Key authentification.
                        if ($this->m_model->needs_login())
                        {
                            if (!$g_comp_session->is_logged_in())
                            {
                                throw new isys_exception_api('Login required.', self::ERR_Auth);
                            } // if
                        }
                        else
                        {
                            $this->m_log->debug('This api request does not need a login.');
                        } // if

                        $this->m_log->debug('Found model: "' . $l_model_class . '. Validating request.."');

                        // Set database if not already done.
                        if (!$this->m_model->get_database() && is_object($g_comp_database))
                        {
                            $this->m_model->set_database($g_comp_database);
                        } // if

                        // Check for mandatory parameters.
                        $l_validation = $this->m_model->get_validation();
                        if (isset($l_validation[$l_data_method]) && is_array($l_validation[$l_data_method]))
                        {
                            foreach ($l_validation[$l_data_method] as $l_validate)
                            {
                                if ($l_validate && !isset($l_request['params'][$l_validate]))
                                {
                                    throw new isys_exception_api(
                                        'Mandatory parameter \'' . $l_validate . '\' not found in your request.', isys_api_controller_jsonrpc::ERR_Parameters
                                    );
                                } // if
                            } // foreach
                        } // if

                        // Check if the option was set as last method parameter.
                        if (isset($l_call[2]) && is_string($l_call[2]))
                        {
                            $l_request['params']['option'] = $l_call[2];
                        } // if

                        // Call request function.
                        $l_params = [
                            $l_data_method,
                            $l_request['params']
                        ];

                        if (($l_result = call_user_func_array(
                            [
                                $this->m_model,
                                'route'
                            ],
                            $l_params
                        ))
                        )
                        {
                            // All good.
                            $this->m_log->debug('Got response: "' . var_export($l_result->get_data(), true) . '".');
                            $l_tmpResponse = $this->rpc_response($l_request['id'], $l_result->get_data());

                            $l_response[] = $l_tmpResponse;
                            unset($l_tmpResponse);
                        }
                        else
                        {
                            // Method not found.
                            throw new isys_exception_api('API Method "' . $l_model_class . '::route() does not exist.', isys_api_controller_jsonrpc::ERR_Method);
                        } // if
                    } // if
                }
                catch (isys_exception_api_validation $e)
                {
                    $l_response[] = $this->error(
                        ($e->get_error_code() ? $e->get_error_code() : -32603),
                        [
                            'error'      => $e->getMessage(),
                            'validation' => $e->get_validation_errors()
                        ]
                    );
                    $this->m_log->error($e->getMessage());
                }
                catch (isys_exception_api $e)
                {
                    $l_response[] = $this->error(($e->get_error_code() ? $e->get_error_code() : -32603), ['error' => $e->getMessage()]);
                    $this->m_log->error($e->getMessage());
                }
                catch (Exception $e)
                {
                    $l_response[] = $this->error(isys_api_controller_jsonrpc::ERR_System, ['error' => $e->getMessage()]);
                    $this->m_log->error($e->getMessage());
                } // try

                // Internal error.
                if (!$l_response || count($l_response) === 0)
                {
                    throw new isys_exception_api('Response invalid. Request was: ' . var_export($this->m_request, true), isys_api_controller_jsonrpc::ERR_Internal);
                } // if
            } // foreach
        }
        catch (isys_exception_api $e)
        {
            $l_response            = $this->error(($e->get_error_code() ? $e->get_error_code() : isys_api_controller_jsonrpc::ERR_Internal), ['error' => $e->getMessage()]);
            $this->m_batch_request = false;
            $this->m_log->error($e->getMessage());
            $this->m_log->info('SERVER: ' . var_export($_SERVER, true));
        }
        catch (Exception $e)
        {
            $l_response = $this->error(-32099, ['error' => $e->getMessage()]);
            $this->m_log->error($e->getMessage());
        }

        /**
         * Is this a single or batch request?
         */
        if (isset($l_response[0]) && !$this->m_batch_request)
        {
            $l_response = $l_response[0];
            $l_id       = $l_response['id'];
        }

        /* Send response to view */
        $this->m_view->set_response($l_response);

        /* Print response */
        $this->m_log->info('Transmitting response: ' . $this->m_view->get_formatted_response());
        $this->m_view->output();

        if ($l_id > 0)
        {
            $this->m_log->info('Request with id ' . $l_id . ' transmitted.');
        }
        else if ($l_response)
        {
            $this->m_log->info('Batch-Request transmitted.');
        }
        unset($l_response);

        /* Write log file if logging is enabled */
        if (isys_settings::get('logging.system.api', false))
        {
            $this->m_log->flush_log();
        }

        return $this;
    }

    /**
     * Formats a JSON-RPC error.
     *
     * @param int   $p_code      Error code
     * @param mixed $p_errordata (optional) Error-related data. Defaults to null.
     *
     * @return array
     */
    public function error($p_code, $p_errordata = null)
    {
        // Determine identifier:
        $l_id = 0;
        if (is_array($this->m_request) && isset($this->m_request[0]))
        {
            $l_id = $this->m_request[0]['id'];
        } //if

        return $this->rpc_response(
            $l_id,
            null,
            [
                'code'    => $p_code,
                'message' => $this->m_errors[$p_code],
                'data'    => $p_errordata
            ]
        );
    } // function

    /**
     * Formats a JSON-RPC response.
     *
     * @param int   $p_id
     * @param mixed $p_result
     * @param mixed $p_error
     *
     * @return array
     */
    private function rpc_response($p_id, $p_result, $p_error = null)
    {
        return [
            'jsonrpc' => $this->m_version,
            'result'  => $p_result,
            'error'   => $p_error,
            'id'      => $p_id
        ];
    } // function

    /**
     * Validates a JSON-RPC request.
     *
     * @return boolean
     */
    private function validate_request()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_SERVER['CONTENT_TYPE']) || substr($_SERVER['CONTENT_TYPE'], 0, 16) != 'application/json')
        {
            /* Invalid JSON-RPC */
            throw new isys_exception_api(
                'This is not a JSON-RPC. The content-type should be ' . 'application/json, request method should be "post" ' . 'and the http body should be a valid json-rpc 2.0 package.',
                isys_api_controller_jsonrpc::ERR_Request
            );
        } // if

        /* Empty request body  */
        if (!$this->m_request)
        {
            throw new isys_exception_api('Invalid API Request. Post body is empty.', isys_api_controller_jsonrpc::ERR_Request);
        }

        /* Invalid JSON */
        if (count($this->m_request) === 0)
        {
            /* Parse error */
            throw new isys_exception_api('Invalid JSON request sent', isys_api_controller_jsonrpc::ERR_Parse);
        }

        return true;
    } // function

    /**
     * Validates json rpc data
     *
     * @param $p_request
     *
     * @return bool
     * @throws isys_exception_api
     */
    private function validate_jsonrpc($p_request)
    {
        /* Invalid JSON-Version */
        if ((!isset($p_request['jsonrpc']) || $p_request['jsonrpc'] != '2.0') && (!isset($p_request['version']) || $p_request['version'] != '2.0'))
        {
            /* Parse error */
            throw new isys_exception_api('Invalid JSON-RPC Version. Use version 2.0', isys_api_controller_jsonrpc::ERR_Internal);
        }

        return true;
    } // function

    /**
     * Constructor
     *
     * @param string $p_request Client request
     */
    public function __construct($p_request)
    {
        // Sets API version:
        $this->m_version = '2.0';

        // Sets raw request:
        $this->m_request = $p_request;

        // Sets API view:
        $this->m_view = new isys_api_view_json();

        /* Call api initialization */
        parent::init();
    }

} // class