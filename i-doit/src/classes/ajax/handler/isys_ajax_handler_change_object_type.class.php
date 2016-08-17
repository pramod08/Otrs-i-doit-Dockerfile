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
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_ajax_handler_change_object_type extends isys_ajax_handler
{
    /**
     * Ajax initializer.
     *
     * @throws  Exception
     */
    public function init()
    {
        global $g_dirs;

        if (isset($_POST[C__CMDB__GET__OBJECT]) && $_POST[C__CMDB__GET__OBJECT])
        {
            $l_dao = new isys_cmdb_dao($this->m_database_component);

            $l_otype = $l_dao->get_object_types($_POST[C__CMDB__GET__OBJECTTYPE]);

            if (count($l_otype))
            {
                $l_sql = "UPDATE isys_obj
					SET isys_obj__isys_obj_type__id = " . $l_dao->convert_sql_id($_POST[C__CMDB__GET__OBJECTTYPE]) . "
					WHERE isys_obj__id = " . $l_dao->convert_sql_id($_POST[C__CMDB__GET__OBJECT]) . ";";

                if ($l_dao->update($l_sql) && $l_dao->apply_update())
                {
                    $l_dao->object_changed($_POST[C__CMDB__GET__OBJECT]);

                    echo '<img src="' . $g_dirs["images"] . '/icons/infobox/blue.png" height="16"> <span>' . sprintf(
                            _L('LC__CMDB__OBJECT_MOVED'),
                            $l_dao->get_obj_name_by_id_as_string($_POST[C__CMDB__GET__OBJECT]),
                            _L($l_otype->get_row_value('isys_obj_type__title'))
                        ) . '</span>';
                } // if
            }
            else
            {
                throw new Exception("Error while changing object-type. " . "Object-type id: " . $_POST["object_type"]);
            } // if
        } // if

        $this->_die();
    } // function
} // class