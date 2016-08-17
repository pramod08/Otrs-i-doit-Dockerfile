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
 * i-doit core classes
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_core
{
    /**
     * HTTP headers (RFC 2616)
     */
    const
        HTTP_AcceptEnc = 'Accept-Encoding', HTTP_Accept = 'Accept', HTTP_Agent = 'User-Agent', HTTP_Allow = 'Allow', HTTP_Cache = 'Cache-Control', HTTP_Connect = 'Connection', HTTP_Content = 'Content-Type', HTTP_Disposition = 'Content-Disposition', HTTP_Encoding = 'Content-Encoding', HTTP_Expires = 'Expires', HTTP_Host = 'Host', HTTP_IfMod = 'If-Modified-Since', HTTP_IfNoneMatch = 'If-None-Match', HTTP_Keep = 'Keep-Alive', HTTP_LastMod = 'Last-Modified', HTTP_Length = 'Content-Length', HTTP_Location = 'Location', HTTP_Origin = 'Origin', HTTP_Partial = 'Accept-Ranges', HTTP_Powered = 'X-Powered-By', HTTP_RequestedWith = 'X-Requested-With', HTTP_Pragma = 'Pragma', HTTP_Referer = 'Referer', HTTP_Transfer = 'Content-Transfer-Encoding', HTTP_WebAuth = 'WWW-Authenticate', HTTP_Authorization = 'Authorization', HTTP_RPCAuthUser = 'X-RPC-Auth-Username', HTTP_RPCAuthPass = 'X-RPC-Auth-Password', HTTP_RPCAuthSession = 'X-RPC-Auth-Session';
    /**
     * HTTP Status codes
     */
    const
        HTTP_100 = 'Continue', HTTP_101 = 'Switching Protocols', HTTP_200 = 'OK', HTTP_201 = 'Created', HTTP_202 = 'Accepted', HTTP_203 = 'Non-Authorative Information', HTTP_204 = 'No Content', HTTP_205 = 'Reset Content', HTTP_206 = 'Partial Content', HTTP_300 = 'Multiple Choices', HTTP_301 = 'Moved Permanently', HTTP_302 = 'Found', HTTP_303 = 'See Other', HTTP_304 = 'Not Modified', HTTP_305 = 'Use Proxy', HTTP_307 = 'Temporary Redirect', HTTP_400 = 'Bad Request', HTTP_401 = 'Unauthorized', HTTP_402 = 'Payment Required', HTTP_403 = 'Forbidden', HTTP_404 = 'Not Found', HTTP_405 = 'Method Not Allowed', HTTP_406 = 'Not Acceptable', HTTP_407 = 'Proxy Authentication Required', HTTP_408 = 'Request Timeout', HTTP_409 = 'Conflict', HTTP_410 = 'Gone', HTTP_411 = 'Length Required', HTTP_412 = 'Precondition Failed', HTTP_413 = 'Request Entity Too Large', HTTP_414 = 'Request-URI Too Long', HTTP_415 = 'Unsupported Media Type', HTTP_416 = 'Requested Range Not Satisfiable', HTTP_417 = 'Expectation Failed', HTTP_500 = 'Internal Server Error', HTTP_501 = 'Not Implemented', HTTP_502 = 'Bad Gateway', HTTP_503 = 'Service Unavailable', HTTP_504 = 'Gateway Timeout', HTTP_505 = 'HTTP Version Not Supported';

    /**
     * HTTP header storage.
     *
     * @var  array
     */
    private static $m_headers = null;

    /**
     * Installed Apache modules
     *
     * @var array
     */
    private static $m_modules = null;

    /**
     * Return i-doit request URL
     *
     * @param bool $p_include_querystring
     *
     * @return mixed
     */
    static function request_url($p_include_querystring = false)
    {
        if ($p_include_querystring)
        {
            return $_SERVER['REQUEST_URI'];
        }

        return 'http' . ($_SERVER['HTTPS'] ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . isys_application::instance()->www_path;
    }

    /**
     * Send HTTP status header; Return text equivalent of status code.
     *
     * @param   integer $p_code
     *
     * @return  mixed
     * @throws  Exception
     */
    static function status($p_code)
    {
        if (!defined('self::HTTP_' . $p_code))
        {
            throw new Exception(sprintf('HTTP Status code %s not found', $p_code));
        } // if

        //Get response code.
        $l_response = constant('self::HTTP_' . $p_code);

        //Send HTTP header.
        if (PHP_SAPI != 'cli' && !headers_sent())
        {
            header($_SERVER['SERVER_PROTOCOL'] . ' ' . $p_code . ' ' . $l_response);
        } // if

        return $l_response;
    } // function

    /**
     * Sends a raw HTTP header
     *
     * @param string $p_header
     * @param string $p_content
     */
    public static function send_header($p_header, $p_content)
    {
        if (!headers_sent())
        {
            header($p_header . ': ' . $p_content);

            return true;
        }

        return false;
    }

    /**
     * Retrieve specific header
     *
     * @param string $p_header
     *
     * @return mixed
     */
    public static function header($p_header)
    {
        if (self::$m_headers === null) self::headers();

        return isset(self::$m_headers[$p_header]) ? self::$m_headers[$p_header] : false;
    }

    /**
     * Retrieve HTTP headers.
     *
     * @return  array
     */
    public static function headers()
    {
        if (PHP_SAPI != 'cli')
        {
            if (self::$m_headers !== null)
            {
                return self::$m_headers;
            } // if

            if (function_exists('apache_request_headers'))
            {
                self::$m_headers = apache_request_headers();
            } // if

            foreach ($_SERVER as $l_key => $l_value)
            {
                if (substr($l_key, 0, 5) == 'HTTP_')
                {
                    self::$m_headers[strtr(ucwords(strtolower(strtr(substr($l_key, 5), '_', ' '))), ' ', '-')] = $l_value;
                } // if
            } // foreach

            return self::$m_headers;
        } // if

        return [];
    } // function

    /**
     * Send HTTP header with expiration date (seconds from current time).
     *
     * @param  integer $p_secs
     */
    static function expire($p_secs = 0)
    {
        if (PHP_SAPI != 'cli' && !headers_sent())
        {
            header(self::HTTP_Powered . ': i-doit');

            if ($p_secs)
            {
                $l_time = time();
                $l_req  = self::headers();

                header_remove(self::HTTP_Pragma);
                header(self::HTTP_Expires . ': ' . gmdate('r', $l_time + $p_secs));
                header(self::HTTP_Cache . ': max-age=' . $p_secs);
                header(self::HTTP_LastMod . ': ' . gmdate('r'));

                if (isset($l_req[self::HTTP_IfMod]) && strtotime($l_req[self::HTTP_IfMod]) + $p_secs > $l_time)
                {
                    self::status(304);
                    die;
                } // if
            }
            else
            {
                header(self::HTTP_Cache . ': no-cache, no-store, must-revalidate');
            } // if
        } // if
    } // function

    /**
     * Static method for checking if the current request is a ajax request.
     *
     * @return  boolean
     */
    public static function is_ajax_request()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    } // function

    /**
     * Relocated E_WARNING handler to throw ErrorExceptions when a php warning occurred
     *
     * @param int    $p_errno
     * @param string $p_errstr
     * @param string $p_errfile
     * @param int    $p_errline
     * @param array  $p_errcontext
     *
     * @return bool
     * @throws ErrorException
     */
    public static function warning_handler($p_errno, $p_errstr, $p_errfile, $p_errline, array $p_errcontext)
    {
        // error was suppressed with the @-operator
        if (0 === error_reporting())
        {
            return false;
        }

        throw new ErrorException($p_errstr . ' (' . $p_errfile . ':' . $p_errline . ')', 0, $p_errno, $p_errfile, $p_errline);
    }

    /**
     * Initialize and globales isys_locales
     *
     * @param $p_user_id
     *
     * @return isys_locale
     */
    public static function init_locales($p_user_id)
    {
        global $g_loc, $g_comp_database;

        if ($p_user_id && $g_comp_database)
        {
            // Get global locale component.
            $g_loc = isys_locale::get(
                $g_comp_database,
                $p_user_id
            );
        }
        else
        {
            $g_loc = isys_locale::dummy();
        }

        return $g_loc;
    }

    /**
     * Post function after system has changed.
     *
     * Gets called after
     *  - a module has been installed
     *  - a module has been uninstalled
     *  - a module has been activated
     *  - a module has been deactivated
     *  - i-doit has been updated
     */
    public static function post_system_has_changed()
    {
        // Save timestamp of last system change.
        isys_settings::set('system.last-change', time());
    } // function

    /**
     * Checks wheather an apache module is installed
     *
     * @param   string $p_module
     *
     * @return  boolean
     * @throws  Exception
     */
    public static function is_webserver_module_installed($p_module)
    {
        if (function_exists('apache_get_modules'))
        {
            if (!self::$m_modules)
            {
                foreach (apache_get_modules() as $module)
                {
                    self::$m_modules[$module] = [
                        'active' => true
                    ];
                }
            }

            return isset(self::$m_modules[$p_module]);
        }
        else
        {
            throw new Exception('Could not verify existence of Webserver Module "' . $p_module . '"');
        } // if
    } // function

    /**
     * Checks wheather an apache module is configured. Currently only supports mod_rewrite
     *
     * @param   string $p_module
     *
     * @return  boolean
     * @throws  Exception
     */
    public static function is_webserver_module_configured($p_module = "mod_rewrite")
    {
        if (self::is_webserver_module_installed($p_module))
        {
            switch ($p_module)
            {
                case 'mod_rewrite':
                    if (isset(self::$m_modules[$p_module]['working']))
                    {
                        return self::$m_modules[$p_module]['working'];
                    }

                    /** Accept ssl certs | @see ID-3150 */
                    $context = stream_context_create(
                        [
                            "ssl" => [
                                "verify_peer"      => false,
                                "verify_peer_name" => false,
                            ],
                        ]
                    );

                    // Check on own mod_rewrite URL
                    $response = file_get_contents(self::request_url() . 'system/rewrite-check', null, $context);

                    self::$m_modules[$p_module]['working'] = (strstr($response, 'LC__CMDB__'));

                    return self::$m_modules[$p_module]['working'];

                    break;
            }
        }

        return true;
    } // function
} // class