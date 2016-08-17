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
class isys_ajax_handler_fetch_mandators extends isys_ajax_handler
{
    /**
     * Initialization.
     *
     * @global  array                   $g_config
     * @global  isys_component_session  $g_comp_session
     * @global  isys_component_database $g_comp_database
     */
    public function init()
    {
        global $g_config, $g_comp_session, $g_comp_database;

        if ($g_comp_session->is_logged_in())
        {
            if (isset($_POST["mandator_id"]) && $_POST["mandator_id"] > 0)
            {
                try
                {
                    $g_comp_session->change_mandator($_POST["mandator_id"]);
                    $this->_die();
                }
                catch (Exception $e)
                {
                    // Nothing to do here.
                } // try
            } // if

            $l_person_dao = new isys_cmdb_dao_category_s_person_master($g_comp_database);
            $l_person     = $l_person_dao->get_person_by_username($g_comp_session->get_current_username())
                ->__to_array();
            $l_md5_pass   = $l_person["isys_cats_person_list__user_pass"];

            $l_mandantors = $g_comp_session->fetch_mandators(
                $g_comp_session->get_current_username(),
                $l_md5_pass,
                true
            );

            if (count($l_mandantors) > 1)
            {
                /**
                 * @note we cannot always use the referer here, because the module we are in may not exist or has got another id for the new mandator
                 *       so this feature is user configurable now.
                 */
                $l_url = $g_config["www_dir"];
                if (isys_settings::get('gui.mandator-switch.keep-url', false))
                {
                    if (isset($_SERVER['HTTP_REFERER']) && strstr($_SERVER['HTTP_REFERER'], $g_config["www_dir"]))
                    {
                        $l_url = $_SERVER['HTTP_REFERER'];
                    }
                }

                echo '<select name="mandator_id" id="mandator_id" onchange="new Ajax.Call(\'?call=fetch_mandators\',{parameters:{mandator_id:this.options[this.selectedIndex].value},onComplete:function(){document.location = \'' . $l_url . '\';}});">';
                $l_current_mandator_id = $g_comp_session->get_current_mandator_as_id();
                foreach ($l_mandantors as $l_mandator_id => $l_mandator_data)
                {
                    $l_options = '';

                    if ($l_current_mandator_id == $l_mandator_id)
                    {
                        $l_options = ' selected="selected" style="font-weight:bold;"';
                    } // if

                    echo '<option value="' . $l_mandator_id . '"' . $l_options . '>' . isys_glob_utf8_decode($l_mandator_data['title']) . '</option>';
                } // foreach

                echo "</select>";
            }
            else
            {
                $l_mandator_info = array_pop($l_mandantors);
                echo "<strong>" . $l_mandator_info['title'] . "</strong>";
            } // if
        } // if

        $this->_die();
    } // function
} // class