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

if (isys_component_session::instance()
    ->is_logged_in()
)
{
    /**
     * Enabling a cache lifetime of one month (but only for the full cache, which maybe for some modules is only generated after logging in)
     *
     * Cache will reload after installing a module or updating i-doit after deleting the temp/ contents
     */
    isys_core::expire(isys_convert::MONTH);
}

$l_path = isys_component_constant_manager::instance()
    ->get_fullpath();

if (file_exists($l_path . 'mod-style.css'))
{
    echo file_get_contents($l_path . 'mod-style.css');
    die;
}

$l_attachCSS = isys_component_signalcollection::get_instance()
    ->emit('mod.css.attachStylesheet');

if (is_array($l_attachCSS))
{
    foreach ($l_attachCSS as $l_css)
    {
        if (file_exists($l_css))
        {
            $l_out .= file_get_contents($l_css) . "\n";
        }
    }
}

echo $l_out;

if (is_dir($l_path) && isys_settings::get('css.caching.cache-to-temp', true))
{
    file_put_contents($l_path . 'mod-style.css', $l_out);
}

die;
