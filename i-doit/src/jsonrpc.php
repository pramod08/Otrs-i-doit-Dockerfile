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
 * JSON RPC
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     1.0
 * @copyright   Copyright 2010 - synetics GmbH
 * @license     http://www.i-doit.com/license
 */

try
{
    /* Include minimal runtime environment if this script is called directly */
    if (!isset($g_absdir))
    {
        // Set error reporting.
        $l_errorReporting = E_ALL & ~E_NOTICE;
        if (defined('E_DEPRECATED'))
        {
            $l_errorReporting &= ~E_DEPRECATED;
        } // if

        if (defined('E_STRICT'))
        {
            $l_errorReporting &= ~E_STRICT;
        } // if

        error_reporting($l_errorReporting);
        $g_absdir = dirname(dirname(__FILE__));

        // Include config.
        if (file_exists("config.inc.php") && include_once("config.inc.php"))
        {
            // Include global and caching environment.
            include_once("bootstrap.inc.php");
            include_once("caching.inc.php");
        }
    } // if

    if (!class_exists("isys_locale"))
    {
        require_once "locales.inc.php";
    } // if

    require_once __DIR__ . '/classes/modules/api/init.php';

    // Call request controller.
    if (class_exists('isys_api_controller_jsonrpc'))
    {
        // Read JSON HTTP body from input stream.
        $l_api = new isys_api_controller_jsonrpc(file_get_contents('php://input'));

        // Handle the API call.
        $l_api->handle();
    }
    else
    {
        throw new Exception('Error: i-doit is unavailable.');
    } // if
}
catch (Exception $e)
{
    echo json_encode(
        [
            'id'      => 0,
            'jsonrpc' => '2.0',
            'error'   => [
                'code'    => -1,
                'message' => $e->getMessage(),
                'data'    => null
            ]
        ]
    );
}
die;