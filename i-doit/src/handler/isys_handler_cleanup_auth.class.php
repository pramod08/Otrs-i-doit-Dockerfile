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
 * Cleanup controller for auth paths
 *
 * @package    i-doit
 * @subpackage General
 * @author     Van Quyen Hoang <qhoang@i.doit.org>
 * @version    1.1
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 *
 */
class isys_handler_cleanup_auth extends isys_handler
{

    public function init()
    {
        global $g_comp_session;

        if ($g_comp_session->is_logged_in())
        {

            verbose("Auth paths cleanup initialized (" . date("Y-m-d H:i:s") . ")");

            /* Cleanup all auth paths */
            try
            {
                isys_auth_module_dao::cleanup_all();
                verbose("Cleanup done.");
            }
            catch (Exception $e)
            {
                verbose("There was an error while cleaning up auth paths.");
            }
        }

        return true;
    }
}

?>