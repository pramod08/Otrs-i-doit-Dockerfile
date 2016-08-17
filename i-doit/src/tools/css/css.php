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
 * Call stylesheet data through cache/smarty.
 *
 * @package     i-doit
 * @subpackage  General
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
header("Content-Type: text/css");

/**
 * Enabling a cache lifetime of one month
 *
 * Cache will reload after installing a module or updating i-doit because
 * of a new token parameter with the value of the last system update timestamp
 */
isys_core::expire(isys_convert::MONTH);

$app = isys_application::instance();

global $g_dirs;

if (file_exists($app->app_path . '/temp/style.css'))
{
    echo file_get_contents($app->app_path . '/temp/style.css');
    die;
}

// Read every file from this directory.
$l_dir = $g_dirs["css_abs"];

// Set CSS variables to use.
$app->template->assign("dir_images", $g_dirs["images"])
    ->assign("dir_theme_images", $g_dirs["theme_images"])
    ->assign("gBrowser", _get_browser());

isys_component_signalcollection::get_instance()
    ->emit('mod.css.beforeProcess');

try
{
    $app->template->loadFilter('output', 'TrimWhiteSpaceEnhanced');
}
catch (SmartyException $e)
{
    //log($e->getMessage())
}

try
{
    if (is_dir($l_dir))
    {
        if (($l_dir_handle = opendir($l_dir)))
        {
            while ($l_filename = readdir($l_dir_handle))
            {
                if ($l_filename == 'print.css')
                {
                    continue;
                }

                $l_filename_full = $l_dir . "/" . $l_filename;
                if (is_file($l_filename_full) && preg_match("/\.css$/i", $l_filename))
                {
                    $l_out .= $app->template->fetch($l_filename_full) . "\n";
                } // if
            } // while

            closedir($l_dir_handle);
        } // if
    }
    else
    {
        throw new isys_exception_filesystem('"' . $l_dir . '" is not a directory!', 'The given directory "' . $l_dir . '" is no directory or does not exist.');
    } // if
}
catch (isys_exception $l_e)
{
    die("Error while creating CSS: " . $l_e->getMessage());
} // try

isys_component_signalcollection::get_instance()
    ->emit('mod.css.processed', $l_out);

echo $l_out;

if (isys_settings::get('css.caching.cache-to-temp', true))
{
    file_put_contents($app->app_path . '/temp/style.css', $l_out);
}
die;