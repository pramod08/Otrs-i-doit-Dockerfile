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
 * AJAX Handler for Cabling import
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_ajax_handler_cabling_import extends isys_ajax_handler
{
    /**
     * Initialize method.
     */
    public function init()
    {
        global $g_comp_database;

        if (isset($_POST['func']))
        {
            $l_function = $_POST['func'];

            switch ($l_function)
            {
                case 'check_object':
                    $l_dao = isys_cmdb_dao::instance($g_comp_database);

                    $l_obj_id = $l_dao->get_obj_id_by_title(
                        $_POST['title'],
                        $l_dao->get_object_types_by_category(C__CATG__CABLING, 'g', false, false),
                        C__RECORD_STATUS__NORMAL
                    );

                    if ($l_obj_id > 0)
                    {
                        echo $l_obj_id;
                    }
                    else
                    {
                        echo false;
                    } // if
                    break;

                default:
                    break;
            } // switch

            die;
        } // if
    } // function
} // class