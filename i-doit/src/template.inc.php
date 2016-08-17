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
 * Assign some template variables
 *
 * @package     i-doit
 * @subpackage  General
 * @version     1.5
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 *
 * @todo        move to initialization method in isys_component_template
 */
global $g_config, $g_dirs, $g_ajax_calls, $index_includes;

/* Assign almighty index_includes array */
isys_application::instance()->template->assign("index_includes", $index_includes);

// Analyze parameters for object lists.
if (isys_glob_get_param('sort') != false)
{
    isys_application::instance()->template->assign('sort', isys_glob_get_param('sort'));
} // if

if (isys_glob_get_param('dir') != false)
{
    isys_application::instance()->template->assign('dir', isys_glob_get_param('dir'));
} // if

// Exception handling.
if (!empty($g_error))
{
    if (!is_object($g_error))
    {
        $g_error = str_replace("\\n", "<br />", $g_error);
    } // if

    isys_application::instance()->template->assign("g_error", $g_error);
} // if
