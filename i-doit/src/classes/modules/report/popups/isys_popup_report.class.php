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
 * Popup for Report
 *
 * @package     i-doit
 * @subpackage  Popups
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_popup_report extends isys_component_popup
{

    /**
     * Instance of database component
     *
     * @var isys_component_database
     */
    protected $m_db;

    /**
     * Instance of logger.
     *
     * @var  isys_log
     */
    protected $m_log;

    /**
     * Instance of the template component
     *
     * @var isys_component_template
     */
    protected $m_tpl_popup;

    /**
     * Handles Smarty inclusion.
     *
     * @global  array                   $g_config
     *
     * @param   isys_component_template $p_tplclass (unused)
     * @param   mixed                   $p_params   (unused)
     *
     * @return  string
     */
    public function handle_smarty_include(isys_component_template &$p_tplclass, $p_params)
    {
        global $g_config;

        $l_url = $g_config['startpage'] . '?mod=report&' . C__CMDB__GET__POPUP . '=report';

        $this->set_config('width', 1000);
        $this->set_config('height', 800);
        $this->set_config('scrollbars', 'no');

        return $this->process($l_url, true);
    } // function

    /**
     * Handles module request.
     *
     * @param   isys_module_request $p_modreq
     *
     * @return  isys_component_template
     */
    public function &handle_module_request(isys_module_request $p_modreq)
    {
        // Prepare template for popup:
        $this->m_tpl_popup = isys_component_template::instance();

        $l_tpl_dir = isys_module_report::get_tpl_dir();
        try
        {
            switch ($_POST['func'])
            {
                case 'report_preview':
                    $this->m_tpl_popup->assign('file_body', $l_tpl_dir . '/popup/report_preview.tpl');
                    $this->report_preview();
                    break;
                case 'report_preview_sql':
                    $this->m_tpl_popup->assign('file_body', $l_tpl_dir . '/popup/report_preview.tpl');
                    $this->report_preview(false);
                    break;
                case 'show_duplicate':
                    $this->m_tpl_popup->assign('file_body', $l_tpl_dir . '/duplicate_report.tpl');
                    $this->show_duplicate();
                    break;
                case 'show_category':
                    $this->m_tpl_popup->assign('file_body', $l_tpl_dir . '/report_category.tpl');
                    $this->show_category();
                    break;
                default:
                    break;
            } // switch

            return $this->m_tpl_popup;
        }
        catch (Exception $e)
        {
            return $this->m_tpl_popup->assign('error', $e->getMessage());
        } // try
    } // function

    /**
     * Shows popup for report categories
     */
    protected function show_category()
    {
        global $g_comp_database_system;

        $l_has_right = isys_auth_report::instance()
            ->is_allowed_to(isys_auth::SUPERVISOR, 'REPORT_CATEGORY');

        if ($l_has_right)
        {
            $l_dao               = new isys_report_dao($g_comp_database_system);
            $l_report_categories = $l_dao->get_report_categories();
            $l_data              = ['-1' => _L('LC__REPORT__POPUP__REPORT_CATEGORY__ADD_NEW_CATEGORY')];

            if (count($l_report_categories) > 0)
            {
                foreach ($l_report_categories AS $l_category)
                {
                    $l_data[_L('Bestehende bearbeiten')][$l_category['isys_report_category__id']] = $l_category['isys_report_category__title'];
                } // foreach
            } // if

            $l_sort = (int) $l_dao->retrieve('SELECT count(*) AS count FROM isys_report_category')
                ->get_row_value('count');

            $this->m_tpl_popup->activate_editmode()
                ->assign('category_selection', $l_data)
                ->assign('latest_id', $l_sort);
        }
        else
        {
            $this->m_tpl_popup->assign('force_close', true);
            isys_notify::error(_L('LC__AUTH__REPORT_EXCEPTION__MISSING_RIGHT_FOR_REPORT_CATEGORIES'));
        } // if
    } // function

    /**
     * Fills the fields in the duplicate report template
     */
    protected function show_duplicate()
    {
        global $g_comp_database_system;

        if (!is_array($_POST['id']))
        {
            $this->m_tpl_popup->assign('force_close', true);
            isys_notify::error(_L('LC__REPORT__POPUP__REPORT_DUPLICATE__NO_REPORT_SELECTED'));

            return;
        } // if

        try
        {
            $l_has_right = isys_auth_report::instance()
                ->check_report_right(isys_auth::SUPERVISOR, $_POST["id"][0]);
        }
        catch (isys_exception_auth $e)
        {
            $l_has_right = false;
        } // try

        $l_dao    = new isys_report_dao($g_comp_database_system);
        $l_report = $l_dao->get_report($_POST['id'][0]);

        $l_allowed_report_categories = isys_auth_report::instance()
            ->get_allowed_report_categories();
        if ($l_allowed_report_categories === false)
        {
            $l_report_category_data                                      = $l_dao->get_report_categories('Global', false)
                ->get_row();
            $l_data[$l_report_category_data['isys_report_category__id']] = $l_report_category_data['isys_report_category__title'];
        }
        else
        {
            $l_report_categories = $l_dao->get_report_categories($l_allowed_report_categories);
            $l_data              = [];
            if (count($l_report_categories) > 0)
            {
                foreach ($l_report_categories AS $l_category)
                {
                    $l_data[$l_category['isys_report_category__id']] = $l_category['isys_report_category__title'];
                } // foreach
            } // if
        } // if

        $this->m_tpl_popup->assign('category_selection', $l_data);

        if (!empty($_POST['id'][0]) && $l_has_right)
        {
            // @todo  How about using "rules" here?
            $this->m_tpl_popup->activate_editmode()
                ->assign("chk_user_specific", $l_report["isys_report__user_specific"])
                ->assign("report_id", $l_report["isys_report__id"])
                ->assign("report_title", $l_report["isys_report__title"])
                ->assign("report_description", $l_report["isys_report__description"])
                ->assign("report_category", $l_report["isys_report__isys_report_category__id"]);
        }
        else
        {
            $this->m_tpl_popup->assign('force_close', true);
            if (!$l_has_right)
            {
                isys_notify::error(_L('LC__AUTH__REPORT_EXCEPTION__MISSING_RIGHT_FOR_DUPLICATING_REPORTS', [$l_report['isys_report__title']]));
            }
            else
            {
                isys_notify::error(_L('LC__REPORT__POPUP__REPORT_DUPLICATE__NO_REPORT_SELECTED'));
            } // if
        } // if
    } // function

    /**
     * This method builds the report and assigns the important data to the popup template
     *
     * @throws    Exception
     */
    protected function report_preview($p_query_builder = true)
    {
        global $g_comp_database;

        if ($p_query_builder)
        {
            if (!empty($_POST['report__HIDDEN_IDS']) && $_POST['report__HIDDEN_IDS'] != '[]')
            {
                $l_dao = new isys_cmdb_dao_category_property($g_comp_database);

                try
                {
                    $this->show_report($l_dao->create_property_query_for_report(25));
                }
                catch (Exception $e)
                {
                    $this->m_tpl_popup->assign('message_class', 'red error')
                        ->assign('message', '<span>' . _L('LC__REPORT__POPUP__REPORT_PREVIEW__ERROR_GENERAL', [$e->getMessage()]) . '</span>');
                } // try
            }
            else
            {
                $this->m_tpl_popup->assign('message', '<span>' . _L('LC__REPORT__POPUP__REPORT_PREVIEW__EMPTY_RESULT') . '</span>');
            } // if
        }
        elseif ($_POST['query'] != '')
        {
            try
            {
                $this->show_report(trim($_POST['query']));
            }
            catch (Exception $e)
            {
                $this->m_tpl_popup->assign('message', '<div class="mt5">' . $e->getMessage() . '</div>');
            }
        }
        else
        {
            $this->m_tpl_popup->assign('message', '<span>' . _L('LC__REPORT__POPUP__REPORT_PREVIEW__EMPTY_RESULT') . '</span>');
        } // if
    } // function

    /**
     * Wrapper method for displaying the report.
     *
     * @param  string $p_query
     * @param  null   $deprecated
     */
    private function show_report($p_query, $deprecated = null)
    {
        $l_mod_report = isys_module_report::get_instance();

        if (method_exists($l_mod_report, 'process_show_report'))
        {
            $l_result = $l_mod_report->process_show_report($p_query, null, true, true);
            if (count($l_result) > 0)
            {
                $l_return = isys_format_json::encode($l_result);
                $this->m_tpl_popup->assign('show_preview', true)
                    ->assign('l_json_data', $l_return);
            }
            else
            {
                $this->m_tpl_popup->assign('message_class', 'p10')
                    ->assign('message', '<span>' . _L('LC__REPORT__POPUP__REPORT_PREVIEW__EMPTY_RESULT') . '</span>')
                    ->assign('show_preview', false);
            } // if
        } // if
    } // function

    /**
     * Constructor
     */
    public function __construct()
    {
        global $g_comp_database;
        $this->m_db = $g_comp_database;

        parent::__construct();
    } // function
} // class