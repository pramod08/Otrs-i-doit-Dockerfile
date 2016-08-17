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
 * Error Tracker Module
 *
 * @package     modules
 * @subpackage  error_tracker
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     1.5
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.5
 */

// namespace
namespace error_trackers\rollbar;

// use
use error_trackers\Trackable;

class Tracker extends \Rollbar implements Trackable
{

    /**
     * @var string
     */
    private $auth_token = '928fead742ac418c8d81b5e068db2007';

    /**
     * @var string
     */
    private $environment = 'Production';

    /**
     * Report an exception
     *
     * @param \Exception $exc
     *
     * @return Trackable
     */
    public function exception(\Exception $exc)
    {
        if (\isys_settings::get('error-tracker.enabled', true))
        {
            parent::report_exception($exc);
        }

        return $this;
    }

    /**
     * This function must return false so that the default php error handler runs
     *
     * @param $errno
     * @param $errstr
     * @param $errfile
     * @param $errline
     *
     * @return bool
     */
    public static function report_php_error($errno, $errstr, $errfile, $errline)
    {
        if ($errno === E_NOTICE) return false;

        if (self::$instance != null)
        {
            self::$instance->report_php_error($errno, $errstr, $errfile, $errline);
        }

        return false;
    }

    /**
     * Initialize rollbar
     */
    public function initialize($config = [])
    {
        $set_exception_handler = true;
        $set_error_handler     = false;

        set_error_handler([$this, 'report_php_error']);

        /* Set environment*/
        if (!isset($config['environment']))
        {
            $this->environment = \isys_settings::get('error-tracker.environment', 'Production');
        }
        else
        {
            $this->environment = $config['environment'];
        }

        /* i-doit's default token on rollbar is '928fead742ac418c8d81b5e068db2007' */
        if (!isset($config['auth_token']))
        {
            $this->auth_token = \isys_settings::get('error-tracker.auth_token', '928fead742ac418c8d81b5e068db2007');
        }
        else
        {
            $this->auth_token = $config['auth_token'];
        }

        /* Fill persondata */
        $userdata             = \isys_application::instance()->session->get_userdata();
        $userdata['username'] = \isys_application::instance()->session->get_current_username();

        $additional_scrubs = [];
        if (\isys_settings::get('error-tracker.anonymize', 1))
        {
            $additional_scrubs = [
                'Host',
                'host',
                'user_ip',
                'server.host'
            ];
            $userdata          = null;
        }

        // Activate proxy if needed
        if (\isys_settings::get('proxy.host', false))
        {
            $config['proxy'] = [
                'address'  => \isys_settings::get('proxy.host') . ":" . \isys_settings::get('proxy.port'),
                'username' => \isys_settings::get('proxy.username'),
                'password' => \isys_settings::get('proxy.password')
            ];
        }

        /**
         * Initialize
         */
        parent::init(
            [
                // required
                'access_token'              => $this->auth_token,
                // optional - environment name. any string will do.
                'environment'               => $this->environment,
                // i-doit version
                'code_version'              => \isys_application::instance()->info->get('version'),
                // user info
                'person'                    => $userdata,
                // optional - path to directory your code is in. used for linking stack traces.
                'root'                      => \isys_application::instance()->app_path,
                // Sets whether errors suppressed with '@' should be reported or not
                'report_suppressed'         => 1,
                // Record full stacktraces for PHP errors.
                'capture_error_stacktraces' => true,
                // Array of field names to scrub out of _POST and _SESSION
                'scrub_fields'              => [
                        'Cookie',
                        'session_data',
                        'user_setting',
                        'username',
                        'user_mandator',
                        'cRecStatusListView',
                        'cmdb_status',
                        'session',
                        'headers',
                        'login_password',
                        'login_username',
                        'C__MODULE__LDAP__PASS',
                        'C__CATG__PASSWORD__PASSWORD'
                    ] + $additional_scrubs
            ] + $config,
            $set_exception_handler,
            $set_error_handler
        );
    }

    /**
     * Just report a message
     *
     * @param        $message
     * @param string $level
     * @param array  $data
     *
     * @return Trackable
     */
    public function message($message, $level = 'error', $data = [])
    {
        if (\isys_settings::get('error-tracker.enabled', true))
        {
            try
            {
                $anonymize = \isys_settings::get('error-tracker.anonymize', 1);
                $that = $this;

                register_shutdown_function(
                    function () use ($that, $message, $level, $data, $anonymize)
                    {
                        if ($anonymize)
                        {
                            foreach ($_POST as $key => $value)
                            {
                                $GLOBALS['_POST'][$key] = '';
                            }
                        }

                        $that->report_message(
                            $message,
                            $level,
                            [
                                'package'    => \isys_application::instance()->info->get('type'),
                                'os'         => php_uname(),
                                'phpversion' => phpversion()
                            ] + $data
                        );
                    }
                );
            }
            catch (\Exception $e)
            {
                \isys_application::instance()->logger->addWarning($e->getMessage());
            }
        }

        return $this;
    }

    /**
     * Flush messages and force sending them to the tracking instance
     *
     * @return Trackable
     */
    public function send()
    {
        if (\isys_settings::get('error-tracker.enabled', true))
        {
            parent::flush();
        }

        return $this;
    }

}