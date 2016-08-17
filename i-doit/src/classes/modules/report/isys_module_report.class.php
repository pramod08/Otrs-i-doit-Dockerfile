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
 * i-doit Report Manager.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Bluemer <dbluemer@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_module_report extends isys_module implements isys_module_interface, isys_module_authable
{
    const DISPLAY_IN_MAIN_MENU = true;

    // Define, if this module shall be displayed in the named menus.
    const DISPLAY_IN_SYSTEM_MENU = false;
    /**
     * @var bool
     */
    protected static $m_licenced = true;
    /**
     * @var isys_module_report_pro
     */
    private static $m_instance = null;
    /**
     * @var isys_report_dao
     */
    protected $m_dao;
    /**
     * Template location
     *
     * @var null
     */
    protected $m_tpl = null;

    /**
     * Static method for retrieving the path, to the modules templates.
     *
     * @static
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public static function get_tpl_dir()
    {
        $l_dir = __DIR__ . DS . 'templates';

        if (!is_dir($l_dir))
        {
            return false;
        } // if

        return $l_dir . DS;
    } // function

    /**
     * Static get instance method.
     *
     * @return  mixed  Either isys_module_report_pro or isys_module_report_open.
     */
    public static function get_instance()
    {
        if (!is_object(self::$m_instance))
        {
            if (defined("C__ENABLE__LICENCE") && C__ENABLE__LICENCE)
            {
                self::$m_instance = new isys_module_report_pro();
            }
            else
            {
                self::$m_instance = new isys_module_report_open();
            } // if
        } // if

        return self::$m_instance;
    } // function

    /**
     * Get related auth class for module
     *
     * @author Selcuk Kekec <skekec@i-doit.com>
     * @return isys_auth
     */
    public static function get_auth()
    {
        return isys_auth_report::instance();
    } // function

    /**
     * @param   isys_module_request & $p_req
     *
     * @return  boolean
     */
    public function init(isys_module_request $p_req)
    {
        return true;
    } // function

    /**
     * Enhances the breadcrumb navigation.
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@synetics.de>
     */
    public function breadcrumb_get(&$p_gets)
    {

        if (defined("C__ENABLE__LICENCE") && C__ENABLE__LICENCE)
        {
            $l_report = new isys_module_report_pro();
        }
        else
        {
            $l_report = new isys_module_report_open();
        } // if

        if (method_exists($l_report, 'breadcrumb_get'))
        {
            return $l_report->breadcrumb_get($_GET);
        }
        else
        {
            return null;
        } // if
    } // function

    /**
     * This method builds the tree for the menu.
     *
     * @param   isys_component_tree & $p_tree
     * @param   boolean             $p_system_module
     * @param   integer             $p_parent
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @since   0.9.9-7
     * @see     isys_module::build_tree()
     */
    public function build_tree(isys_component_tree $p_tree, $p_system_module = true, $p_parent = null)
    {
        if (defined("C__ENABLE__LICENCE") && C__ENABLE__LICENCE)
        {
            $l_report = new isys_module_report_pro();
        }
        else
        {
            $l_report = new isys_module_report_open();
        } // if

        $l_report->build_tree($p_tree, $p_system_module, $p_parent);
    } // function

    /**
     * Method for retrieving a bookmark string (mydoit).
     *
     * @param   string $p_text
     * @param   string $p_link
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @version Van Quyen Hoang <qhoang@i-doit.org>
     * @since   0.9.9-9
     */
    public function mydoit_get(&$p_text, &$p_link)
    {
        $l_link_options = [
            C__GET__MODULE_ID        => C__MODULE__REPORT,
            C__GET__REPORT_PAGE      => $_GET[C__GET__REPORT_PAGE],
            C__GET__REPORT_REPORT_ID => $_GET[C__GET__REPORT_REPORT_ID],
            C__GET__TREE_NODE        => $_GET[C__GET__TREE_NODE],
            'report_category'        => $_GET['report_category']
        ];

        $p_text[] = 'Report Manager';
        switch ($_GET[C__GET__REPORT_PAGE])
        {
            case C__REPORT_PAGE__REPORT_BROWSER:
                $p_text[] = _L('LC__REPORT__MAINNAV__QUERY_BROWSER');
                break;
            case C__REPORT_PAGE__CUSTOM_REPORTS:
                if ($_GET['report_category'] > 0)
                {
                    $p_text[] = $this->m_dao->get_report_categories($_GET['report_category'], false)
                        ->get_row_value('isys_report_category__title');
                }
                else
                {
                    $p_text[] = _L('LC__REPORT__MAINNAV__CUSTOM_QUERIES');
                }
                break;
            case C__REPORT_PAGE__QUERY_BUILDER:
                $p_text[] = _L('LC__REPORT__MAINNAV__QUERY_BUILDER');
                break;
            case C__REPORT_PAGE__VIEWS:
                $p_text[] = 'Views';
                break;
            case C__REPORT_PAGE__STANDARD_REPORTS:
            default:
                $p_text[] = _L('LC__REPORT__MAINNAV__STANDARD_QUERIES');
                break;
        } // switch

        if (isset($_GET[C__GET__REPORT_REPORT_ID]))
        {
            $l_row    = $this->m_dao->get_report($_GET[C__GET__REPORT_REPORT_ID]);
            $p_text[] = $l_row['isys_report__title'];
        } // if

        // Define the favorite-link.
        $p_link = isys_glob_http_build_query($l_link_options);

        return true;
    } // function

    /**
     * Start-method.
     *
     * @return  isys_module_report_open
     */
    public function start()
    {
        if (defined("C__ENABLE__LICENCE") && C__ENABLE__LICENCE)
        {
            $l_report = new isys_module_report_pro();
        }
        else
        {
            $l_report = new isys_module_report_open();
        } // if

        global $g_comp_database_system;

        $this->m_dao = new isys_report_dao($g_comp_database_system);

        $l_report->start();

        return $l_report;
    } // function
} // class