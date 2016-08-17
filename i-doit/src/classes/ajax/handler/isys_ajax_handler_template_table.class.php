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
class isys_ajax_handler_template_table extends isys_ajax_handler
{
    /**
     * Method for initializing the AJAX request.
     */
    public function init()
    {
        global $g_comp_database;

        if (isset($_POST[C__GET__ID]) && is_array($_POST[C__GET__ID]))
        {
            $l_dao_cmdb = new isys_cmdb_dao($g_comp_database);

            foreach ($_POST[C__GET__ID] as $l_object_id)
            {
                $l_dao_cmdb->delete_object($l_object_id);
            } // foreach
        } // if

        $l_template = new isys_module_templates();
        $l_template->set_m_rec_status($_POST['type']);

        if (($l_tpl_list = $l_template->get_template_list()))
        {
            echo $l_tpl_list;
        }
        else
        {
            echo "<p class=\"p10\">" . _L('LC__MODULE__TEMPLATES__NO_TEMPLATES') . ".</p>";
        } // if

        $this->_die();
    } // function
} // class