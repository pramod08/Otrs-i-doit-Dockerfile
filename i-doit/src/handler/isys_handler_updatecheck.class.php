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
 * Update checker
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stuecken <dstuecken@i.doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 *
 */

/**
 * Class isys_handler_updatecheck
 */
class isys_handler_updatecheck extends isys_handler
{
    /**
     * Init method.
     *
     * @return  boolean
     */
    public function init()
    {
        try
        {
            $this->check();
        }
        catch (Exception $e)
        {
            verbose('');
        } // try

        return true;
    } // function

    /**
     *
     * @return  boolean
     */
    public function needs_login()
    {
        return false;
    } // function

    /**
     * Method for checking on a new version.
     */
    private function check()
    {
        global $g_absdir, $g_product_info;

        $l_new_update   = false;
        $l_responseTEXT = '';

        if (extension_loaded('curl'))
        {
            if (file_exists($g_absdir . '/updates/classes/isys_update.class.php'))
            {
                include_once($g_absdir . '/updates/classes/isys_update.class.php');

                $l_upd = new isys_update;

                verbose('Checking... Please wait..');

                try
                {

                    if (defined('C__IDOIT_UPDATES_PRO'))
                    {
                        $l_updateURL = C__IDOIT_UPDATES_PRO;
                    }
                    else
                    {
                        $l_updateURL = "http://www.i-doit.org/updates.xml";
                    }

                    $l_responseTEXT = $l_upd->fetch_file($l_updateURL);

                }
                catch (Exception $e)
                {
                    error($e->getMessage());
                } // try

                $l_version = $l_upd->get_new_versions($l_responseTEXT);
                $l_info    = $l_upd->get_isys_info();

                if (is_array($l_version) && count($l_version) > 0)
                {
                    foreach ($l_version as $l_v)
                    {
                        if ($l_info['revision'] < $l_v['revision'])
                        {
                            $l_new_update = $l_v;
                        } // if
                    } // foreach

                    if (!isset($l_new_update))
                    {
                        $l_update_msg = 'You have already got the latest version (' . $g_product_info['version'] . ').';
                    } // if
                }
                else
                {
                    $l_update_msg = 'Update check failed. Is the i-doit server not connected to the internet?';
                } // if
            } // if

            if (isset($l_update_msg))
            {
                verbose($l_update_msg);
            }
            else
            {
                if ($l_new_update)
                {
                    verbose('');
                    verbose('Theres a new i-doit version available: ' . $l_new_update['version']);
                    verbose('Your current version is: ' . $g_product_info['version']);
                    verbose('Go to the i-doit updater to download automatically or download yourself at: ');
                    verbose('http://www.i-doit.org');
                    verbose('');

                    file_put_contents($g_absdir . '/temp/new_version', serialize($l_new_update));
                } // if
                else
                {
                    verbose(sprintf('You already got the latest version (%s)', $g_product_info['version']));
                }
            } // if

            verbose(PHP_EOL, true, false);
        }
        else
        {

            error('You need to install the php-curl extension in order to run this script!');
        } // if
    } // function
} // class