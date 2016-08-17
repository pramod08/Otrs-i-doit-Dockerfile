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
 * AJAX Handler for logbook exchange.
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_ajax_handler_logbook extends isys_ajax_handler
{
    /**
     * Init method.
     *
     * @throws  isys_exception_general
     */
    public function init()
    {
        global $g_comp_database;

        $_GET  = $this->m_get;
        $_POST = $this->m_post;

        if (count($_POST) == 0)
        {
            header("Content-Type: application/json");

            $l_data   = isys_cmdb_dao_category_g_logb::instance($g_comp_database)
                ->get_data_by_object($_GET[C__CMDB__GET__OBJECT]);
            $l_return = [];
            while ($l_row = $l_data->get_row())
            {
                $l_return[] = [
                    "isys_obj__title"                => $l_row["isys_obj__title"],
                    "isys_obj__created"              => $l_row["isys_obj__created"],
                    "isys_obj__updated"              => $l_row["isys_obj__updated"],
                    "isys_logbook__description"      => $l_row["isys_logbook__description"],
                    "isys_logbook__comment"          => $l_row["isys_logbook__comment"],
                    "isys_logbook_level__title"      => $l_row["isys_logbook_level__title"],
                    "isys_logbook__changes"          => unserialize($l_row["isys_logbook__changes"]),
                    "isys_logbook__date"             => $l_row["isys_logbook__date"],
                    "isys_logbook__user_name_static" => $l_row["isys_logbook__user_name_static"],
                    "isys_logbook__event_static"     => $l_row["isys_logbook__event_static"],
                    "isys_logbook__obj_name_static"  => $l_row["isys_logbook__obj_name_static"],
                    "isys_logbook__category_static"  => _L($l_row["isys_logbook__category_static"]),
                    "isys_logbook__obj_type_static"  => _L($l_row["isys_logbook__obj_type_static"]),
                    "isys_logbook_source__title"     => _L($l_row["isys_logbook_source__title"]),
                    "event"                          => isys_event_manager::getInstance()
                        ->translateEvent(
                            $l_row["isys_logbook__event_static"],
                            $l_row["isys_logbook__obj_name_static"],
                            $l_row["isys_logbook__category_static"],
                            $l_row["isys_logbook__obj_type_static"],
                            $l_row["isys_logbook__event_identifier_static"],
                            $l_row["isys_logbook__changecount"]
                        )
                ];
            } // while

            echo isys_format_json::encode($l_return);
        } // if

        $this->_die();
    } // function
} // class