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
 * @package    i-doit
 * @subpackage General
 * @author     GR <gr@synetics.de>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_ajax_handler_search_table extends isys_ajax_handler
{

    public function init()
    {
        global $g_comp_template;
        global $g_comp_template_language_manager;

        $l_search = new isys_module_search();

        if (($l_search_list = $l_search->get_search_list()))
        {
            if (isset($_POST['C__SEARCH_TEXT']))
            {
                if (isset($_POST['current_page']))
                {
                    $g_comp_template->display("search/response_container.tpl");
                }
                else
                {
                    $g_comp_template->display("search/result_list.tpl");
                }
            }
            else
            {
                $g_comp_template->display("search/response_container.tpl");
            }
        }
        else
        {
            $l_arSearchCounter = ["var" => 0];
            echo $g_comp_template_language_manager->get('LC_SEARCH__RESULTS_FOUND', $l_arSearchCounter);
        }
        $this->_die();

        return true;
    }

    public static function needs_hypergate()
    {
        return true;
    }
}

?>