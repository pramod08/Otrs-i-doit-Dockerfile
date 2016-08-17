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
 * API controller
 *
 * @package    i-doit
 * @subpackage API
 * @author     Benjamin Heisig <bheisig@synetics.de>, Dennis Stücken <dstuecken@synetics.de>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */

/**
 * API controller base class
 */
abstract class isys_api_controller extends isys_api
{

    /**
     * Used model
     *
     * @var isys_api_model
     */
    protected $m_model;
    /**
     * API version
     *
     * @var string
     */
    protected $m_version = 'n/a';
    /**
     * Used view
     *
     * @var isys_api_view
     */
    protected $m_view;

    /**
     * Gets API version.
     *
     * @return string
     */
    public function get_version()
    {
        return $this->m_version;
    } // function

    /**
     * Gets model.
     *
     * @return isys_api_model
     */
    public function get_model()
    {
        return $this->m_model;
    } // function

    /**
     * Gets view.
     *
     * @return isys_api_view
     */
    public function get_view()
    {
        return $this->m_view;
    } // function

    /**
     * Does a session login based on the json-rpc login method.
     *
     * Login data structure: $p_login_data = array(
     *    'username' => 'user',
     *  'password' => 'pass', //md5
     *  'mandator' => 1
     * );
     *
     * @deprecated
     *
     * @param array $p_login_data
     *
     * @return bool
     */
    protected function login($p_login_data)
    {
        global $g_comp_session, $g_comp_database;

        if (!$g_comp_session->weblogin($p_login_data['username'], $p_login_data['password'], $p_login_data['mandator'], true))
        {
            $this->m_log->error('Login failed.');

            return false;
        } // if
        //
        // Set mandator database:
        $this->m_model->set_database($g_comp_database);

        return true;
    } // function

    /**
     * Does a session login based on the json-rpc login method and a defined API-Key.
     *  Also tries to authenticate over the following authentication methods:
     *      - Header based (X-RPC-Auth.Username, X-RPC-Auth.Password)
     *      - HTTP Basic Auth
     *  Tries to re-adopt an already available session by passing a valid session ID in $p_sessionID
     *
     * @param string $p_apikey
     * @param        optional string $p_sessionID
     *
     * @throws \Exception
     * @throws \isys_exception_api
     * @return bool
     */
    protected function apikey_login($p_apikey, $p_sessionID = null)
    {
        global $g_comp_session;

        if (!empty($p_apikey))
        {
            /**
             * Check wheather the requestor tries to login as a specific user
             */
            if (isys_core::header(isys_core::HTTP_RPCAuthUser))
            {
                $l_userdata = [
                    'username' => isys_core::header(isys_core::HTTP_RPCAuthUser),
                    'password' => isys_core::header(isys_core::HTTP_RPCAuthPass)
                ];
                $this->m_log->info('Logging in with RPC Session header (User: ' . $l_userdata['username'] . ')');
            }
            else if (isys_core::header(isys_core::HTTP_Authorization) && isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']))
            {
                $this->m_log->info('Logging in with HTTP Basic Authentication (User: ' . $_SERVER['PHP_AUTH_USER'] . ')');
                $l_userdata = [
                    'username' => $_SERVER['PHP_AUTH_USER'],
                    'password' => $_SERVER['PHP_AUTH_PW']
                ];
            }
            else
            {
                $l_userdata = null;
            } // if

            if ($g_comp_session->apikey_login($p_apikey, $l_userdata, $p_sessionID))
            {
                return true;
            } // if

            if ($l_userdata)
            {
                // Log failed APi login:
                isys_application::instance()->logger->addWarning(
                    'APi Authentication failed for user ' . $l_userdata['username'],
                    [
                        'api-key' => $p_apikey,
                        'user'    => $l_userdata['username']
                    ]
                );

                throw new isys_exception_api('Either your username or password is invalid.', isys_api_controller_jsonrpc::ERR_Auth);
            }
            else
            {
                // Log failed APi login:
                isys_application::instance()->logger->addWarning(
                    'APi Authentication failed for key ' . $p_apikey,
                    ['api-key' => $p_apikey]
                );

                throw new isys_exception_api('Your API-Key \'' . $p_apikey . '\' is invalid.', isys_api_controller_jsonrpc::ERR_Auth);
            }

        } // if

        return false;
    } // function

    /**
     * Sets language during session.
     *
     * @param string $p_language Language, e. g. 'en'
     */
    protected function set_language($p_language)
    {
        global $g_comp_template_language_manager, $g_comp_session;;
        if (is_string($p_language) && !empty($p_language))
        {
            $g_comp_template_language_manager = new isys_component_template_language_manager($p_language);
            $g_comp_session->set_language($p_language);
        } // if
    } // function

} // class