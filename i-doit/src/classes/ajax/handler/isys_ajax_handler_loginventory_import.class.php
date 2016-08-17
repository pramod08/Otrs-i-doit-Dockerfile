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
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       1.0
 */
class isys_ajax_handler_loginventory_import extends isys_ajax_handler
{
    /**
     * Init method, which gets called from the framework.
     *
     * @global  isys_component_database $g_comp_database
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function init()
    {
        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json');

        $l_return = [];

        switch ($_GET['func'])
        {
            case 'object_list':
                $l_return = $this->get_loginventory_object_list($_POST['id'], $_POST['table_order']);
                break;
        } // switch

        echo isys_format_json::encode($l_return);
        $this->_die();
    } // function

    private function get_loginventory_object_list($p_db_id, $p_table_order = 'ASC')
    {
        global $g_comp_database;

        $l_log    = isys_factory_log::get_instance('import_loginventory');
        $l_mod_li = new isys_loginventory_dao($g_comp_database, $l_log);
        try
        {
            $l_pdo         = $l_mod_li->get_connection($p_db_id);
            $l_mod_li_data = new isys_loginventory_dao_data($g_comp_database, $l_pdo);
            $l_dao         = isys_cmdb_dao::instance($g_comp_database);

            $l_li_objects = $l_mod_li_data->get_loginventory_objects(null, $p_table_order);

            $l_li_Obj = [];
            foreach ($l_li_objects AS $l_row)
            {
                $l_row["imported"] = $l_dao->retrieve("SELECT isys_obj__imported FROM isys_obj WHERE isys_obj__title = '" . $l_row["LI_PCNAME"] . "'")
                    ->get_row_value('isys_obj__imported');
                $l_li_Obj[]        = $l_row;
            }

            return $l_li_Obj;
        }
        catch (Exception $e)
        {
            return false;
        }
    }

} // class
?>