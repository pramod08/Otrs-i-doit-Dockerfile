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
 * Installer
 * Step 1
 * System check
 *
 * @package     i-doit
 * @subpackage  General
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */

define('STEP1_RESULT_GOOD', 'OK');
define('STEP1_RESULT_BAD', 'ERROR');

$l_step1_complete = true;

function step1_do_check(&$p_template, $p_data, $p_var, $p_result)
{
    global $l_step1_complete;

    if ($p_result === true)
    {
        // CSS-Class
        $l_status = 'Good';
        $l_result = STEP1_RESULT_GOOD;
    }
    else if ($p_result == 'both')
    {
        // CSS-Class
        $l_status = 'Both';
        $l_result = STEP1_RESULT_GOOD;
    }
    else
    {
        // CSS-Class
        $l_status         = 'Bad';
        $l_result         = STEP1_RESULT_BAD;
        $l_step1_complete = false;
    } // if

    tpl_set(
        $p_template,
        [
            $p_var             => $p_data,
            $p_var . '_RESULT' => $l_result,
            $p_var . '_STATUS' => 'stepLineStatus' . $l_status
        ]
    );
} // function

/**
 * Converts a ini-value to bytes (128M or 1G, ...).
 *
 * @param   string $p_value
 *
 * @return  integer
 * @author  Leonard Fischer <lfischer@i-doit.org>
 */
function to_bytes($p_value)
{
    if (is_null($p_value) || !is_numeric(substr($p_value, 0, -1)))
    {
        return null;
    } // if

    $l_return = trim($p_value);
    $l_unit   = strtolower($p_value[strlen($p_value) - 1]);

    switch ($l_unit)
    {
        case 'g':
            $l_return *= 1024;
        case 'm':
            $l_return *= 1024;
        case 'k':
            $l_return *= 1024;
    } // switch

    return $l_return;
} // function

// Operating System.
step1_do_check($g_tpl_step, php_uname('s'), 'STEP1_OS_TYPE', true);
step1_do_check($g_tpl_step, php_uname('r') . ' ' . php_uname('v'), 'STEP1_OS_VERSION', true);

// Webserver.
step1_do_check($g_tpl_step, $_SERVER['SERVER_SOFTWARE'], 'STEP1_WEBSERVER_VERSION', true);

// Magic quotes and other PHP settings.
step1_do_check(
    $g_tpl_step,
    ini_get('max_input_vars') . ' Minimum: <strong>10000</strong>',
    'STEP1_MAX_INPUT_VARS',
    (ini_get('max_input_vars') == 0 || ini_get('max_input_vars') >= 10000)
);
step1_do_check(
    $g_tpl_step,
    ini_get('post_max_size') . ' Minimum: <strong>128M</strong>',
    'STEP1_POST_MAX_SIZE',
    (ini_get('post_max_size') == 0 || to_bytes(ini_get('post_max_size')) >= to_bytes('128M'))
);
step1_do_check(
    $g_tpl_step,
    phpversion() . ' Minimum: <strong>' . PHP_VERSION_MINIMUM . '</strong>',
    'STEP1_WEBSERVER_PHP',
    (version_compare(phpversion(), PHP_VERSION_MINIMUM) != -1)
);

// Check for PHP extensions.
$l_phpSession   = extension_loaded('session');
$l_phpMysql     = extension_loaded('mysqli');
$l_phpXML       = extension_loaded('xml');
$l_phpSimpleXML = extension_loaded('simplexml');
$l_phpZLIB      = extension_loaded('zlib');
$l_phpGD        = extension_loaded('gd');
$l_phpCURL      = extension_loaded('curl');
$l_phpPDO_MySQL = extension_loaded('pdo_mysql');
$l_phpMCRYPT    = extension_loaded('mcrypt');

if (function_exists('apache_get_modules'))
{
    $l_modRewrite = in_array('mod_rewrite', apache_get_modules());

    step1_do_check($g_tpl_step, (($l_modRewrite) ? 'Installed' : 'Not found'), 'STEP1_WEBSERVER_REWRITE', $l_modRewrite);
}
else
{
    step1_do_check(
        $g_tpl_step,
        '<span style="color:#926c07;">Please verify that the apache module "mod_rewrite" is installed and active.</span>',
        'STEP1_WEBSERVER_REWRITE',
        true
    );
} // if

step1_do_check($g_tpl_step, (($l_phpSession) ? 'Active' : 'Not found'), 'STEP1_WEBSERVER_PHP_SESSION', $l_phpSession);
step1_do_check($g_tpl_step, (($l_phpMysql) ? 'Active' : 'Not found'), 'STEP1_WEBSERVER_PHP_MYSQL', $l_phpMysql);
step1_do_check($g_tpl_step, (($l_phpXML) ? 'Active' : 'Not found'), 'STEP1_WEBSERVER_PHP_XML', $l_phpXML);
step1_do_check($g_tpl_step, (($l_phpSimpleXML) ? 'Active' : 'Not found - Needed for Exports and Imports'), 'STEP1_WEBSERVER_PHP_SIMPLEXML', $l_phpSimpleXML);
step1_do_check($g_tpl_step, (($l_phpZLIB) ? 'Active' : 'Not found'), 'STEP1_WEBSERVER_PHP_ZLIB', $l_phpZLIB);
step1_do_check($g_tpl_step, (($l_phpGD) ? 'Active' : 'Not found'), 'STEP1_WEBSERVER_PHP_GD', $l_phpGD);
step1_do_check(
    $g_tpl_step,
    (($l_phpCURL) ? 'Active - Needed for external web-services' : 'Not found - cURL is not mandatory and only needed for external web-services. Activate it if you\'re planning to use these services.'),
    'STEP1_WEBSERVER_PHP_CURL',
    (($l_phpCURL) ? true : 'both')
);
step1_do_check($g_tpl_step, (($l_phpPDO_MySQL) ? 'Active' : 'Not found. Needed for database abstraction'), 'STEP1_WEBSERVER_PHP_PDO_MYSQL', $l_phpPDO_MySQL);
step1_do_check(
    $g_tpl_step,
    (($l_phpMCRYPT) ? 'Active - Needed for encrypting/decrypting' : 'Not found - Mcrypt is not mandatory and only needed for encrypting/decrypting passwords. Activate it if you\'re planning to use these features.'),
    'STEP1_WEBSERVER_PHP_MCRYPT',
    (($l_phpMCRYPT) ? true : 'both')
);

// DBMS.
if ($l_phpMysql)
{
    step1_do_check($g_tpl_step, mysqli_get_client_info(), 'STEP1_DATABASE_VERSION', true);
} // if

$l_next_disabled = !$l_step1_complete;