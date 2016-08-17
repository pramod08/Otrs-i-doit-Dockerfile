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
 * Maintenance report view.
 *
 * @package     modules
 * @subpackage  maintenance
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.2
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.5.1
 */
class isys_maintenance_reportview_maintenance_export extends isys_report_view
{
    /**
     * Method for ajax-requests.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function ajax_request()
    {
        ;
    } // function

    /**
     * Method for retrieving the language constant of the report-description.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @todo    Should we update the parent method to retrieve this automatically?
     */
    public static function description()
    {
        return 'LC__REPORT__VIEW__MAINTENANCE_EXPORT_DESCRIPTION';
    } // function

    /**
     * Determines, if a report view is brought in by an external source (module?).
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function external()
    {
        return true;
    } // function

    /**
     * Initialize method.
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function init()
    {
        return true;
    } // function

    /**
     * Method for retrieving the language constant of the report-name.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @todo    Should we update the parent method to retrieve this automatically?
     */
    public static function name()
    {
        return 'LC__REPORT__VIEW__MAINTENANCE_EXPORT';
    } // function

    /**
     * Start-method - Implement the logic for displaying your data here.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @return  void
     */
    public function start()
    {
        $l_rules = [
            'C__REPORT__VIEW__MAINTENANCE_EXPORT__TITLE'      => [
                'p_strPlaceholder' => _L('LC__MAINTENANCE__PDF__TITLE') . ' (' . date('d.m.Y', mktime(0, 0, 0, 1, 1, date('Y'))) . ' - ' . date(
                        'd.m.Y',
                        mktime(0, 0, 0, 1, 0, date('Y') + 1)
                    ) . ')',
                'p_strClass'       => 'input-small'
            ],
            'C__REPORT__VIEW__MAINTENANCE_EXPORT__TYPE'       => [
                'p_strTable' => 'isys_maintenance_type',
                'p_strClass' => 'input-small'
            ],
            'C__REPORT__VIEW__MAINTENANCE_EXPORT__FROM'       => [
                'p_strValue'     => date('Y-m-d', mktime(0, 0, 1, 1, 1, date('Y'))),
                'p_strPopupType' => 'calendar',
                'p_strClass'     => 'input-mini'
            ],
            'C__REPORT__VIEW__MAINTENANCE_EXPORT__TO'         => [
                'p_strValue'     => date('Y-m-d', mktime(0, 0, 1, 1, 0, (date('Y') + 1))),
                'p_strPopupType' => 'calendar',
                'p_strClass'     => 'input-mini'
            ],
            'C__REPORT__VIEW__MAINTENANCE_EXPORT__LOGO' => [
                'p_strClass' => 'input-small'
            ]
        ];

        if (isset($_GET['download_export']) && $_GET['download_export'] == 1)
        {
            try
            {
                $l_pdf = $this->process_pdf_creation($_GET['title'], $_GET['date_from'], $_GET['date_to'], $_GET['type'], $_GET['logo_obj_id']);

                $l_pdf->Output(_L('LC__REPORT__VIEW__MAINTENANCE_EXPORT__FILENAME') . '.pdf', 'D');
            }
            catch (Exception $e)
            {
                isys_notify::error($e->getMessage(), ['sticky' => true]);

                // Refill all formfields.
                $l_rules['C__REPORT__VIEW__MAINTENANCE_EXPORT__TITLE']['p_strValue']     = $_GET['title'];
                $l_rules['C__REPORT__VIEW__MAINTENANCE_EXPORT__TYPE']['p_strSelectedID'] = $_GET['type'];
                $l_rules['C__REPORT__VIEW__MAINTENANCE_EXPORT__FROM']['p_strValue']      = $_GET['date_from'];
                $l_rules['C__REPORT__VIEW__MAINTENANCE_EXPORT__TO']['p_strValue']        = $_GET['date_to'];
                $l_rules['C__REPORT__VIEW__MAINTENANCE_EXPORT__LOGO']['p_strSelectedID'] = $_GET['logo_obj_id'];
            } // try
        } // if

        // Assign the ajax URL to the template.
        isys_application::instance()->template
            ->activate_editmode()
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules)
            ->assign('ajax_url', isys_glob_add_to_query('ajax', 1))
            ->assign('url', isys_helper_link::create_url($_GET));
    } // function

    /**
     * Method for retrieving the template-name of this report.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @todo    Should we update the parent method to retrieve this automatically?
     */
    public function template()
    {
        return __DIR__ . DS . 'view_maintenance_export.tpl';
    } // function

    /**
     * Method for declaring the type of this report.
     *
     * @return  integer
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function type()
    {
        return self::c_php_view;
    } // function

    /**
     * Method for declaring the view-type.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function viewtype()
    {
        return 'LC__MODULE__MAINTENANCE';
    } // function

    /**
     * Method for processing the PDF creation. Will return the class instance for further processing.
     *
     * @param   string  $p_title
     * @param   string  $p_date_from
     * @param   string  $p_date_to
     * @param   integer $p_type
     * @param   integer $p_logo_file_id
     *
     * @return  isys_maintenance_reportview_fpdi
     * @throws  isys_exception_filesystem
     * @throws  isys_exception_general
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function process_pdf_creation($p_title, $p_date_from, $p_date_to, $p_type = null, $p_logo_file_id = 0)
    {
        global $g_loc, $g_comp_database, $g_dirs;

        $l_empty_value = isys_tenantsettings::get('gui.empty_value', '-');

        $l_logo_path    = '';
        $l_maintenances = $l_objects = [];

        /**
         * @var  isys_maintenance_dao             $l_dao
         * @var  isys_cmdb_dao_category_g_contact $l_contact_dao
         */
        $l_dao         = isys_maintenance_dao::instance($g_comp_database);
        $l_contact_dao = isys_cmdb_dao_category_g_contact::instance($g_comp_database);

        if ($p_logo_file_id > 0)
        {
            $l_filename = isys_cmdb_dao_category_s_file::instance($g_comp_database)
                ->get_file_by_obj_id($p_logo_file_id)
                ->get_row_value('isys_file_physical__filename');

            $l_logo_path = $g_dirs['fileman']['target_dir'] . $l_filename;
        } // if

        $l_maintenance_res = $l_dao->get_filtered_planning_list($p_date_from, $p_date_to, true, $p_type);

        if (count($l_maintenance_res) === 0)
        {
            throw new isys_exception_general(_L('LC__MAINTENANCE__NOTIFY__NO_MAINTENANCES_FOUND_IN_THIS_TIMEPERIOD'));
        } // if

        while ($l_maintenance_row = $l_maintenance_res->get_row())
        {
            if (!isset($l_maintenances[$l_maintenance_row['isys_maintenance__id']]))
            {
                $l_maintenance = [];

                foreach ($l_maintenance_row as $l_key => $l_value)
                {
                    if (strpos($l_key, 'isys_maintenance_') !== false)
                    {
                        $l_maintenance[$l_key] = $l_value;
                    } // if
                } // foreach

                $l_maintenance['objects'] = [$l_maintenance_row['isys_obj__id']];

                $l_maintenances[$l_maintenance_row['isys_maintenance__id']] = $l_maintenance;
            }
            else
            {
                // Create the "reference".
                $l_maintenances[$l_maintenance_row['isys_maintenance__id']]['objects'][] = $l_maintenance_row['isys_obj__id'];
            } // if

            if (!isset($l_objects[$l_maintenance_row['isys_obj__id']]))
            {
                $l_object = [
                    'contact_role' => $l_empty_value
                ];

                foreach ($l_maintenance_row as $l_key => $l_value)
                {
                    if (strpos($l_key, 'isys_obj_') !== false)
                    {
                        $l_object[$l_key] = $l_value;
                    } // if
                } // foreach

                $l_contact_by_role = $l_contact_dao->get_contact_objects_by_tag(
                    $l_maintenance_row['isys_obj__id'],
                    $l_maintenance_row['isys_maintenance__isys_contact_tag__id'],
                    ' ORDER BY isys_catg_contact_list__primary_contact DESC LIMIT 1'
                )
                    ->get_row();

                if (is_array($l_contact_by_role))
                {
                    $l_object['contact_role'] = $l_contact_by_role['isys_obj__title'] . ' (' . _L($l_contact_by_role['isys_contact_tag__title']) . ')';
                } // if

                $l_object['maintenances'] = [$l_maintenance_row['isys_maintenance__id']];

                $l_objects[$l_maintenance_row['isys_obj__id']] = $l_object;
            }
            else
            {
                // Create the "reference".
                $l_objects[$l_maintenance_row['isys_obj__id']]['maintenances'][] = $l_maintenance_row['isys_maintenance__id'];
            } // if
        } // while

        $l_title = $p_title ?: _L('LC__MAINTENANCE__PDF__TITLE') . ' (' .
            isys_locale::get_instance()->fmt_date($p_date_from) . ' - ' .
            isys_locale::get_instance()->fmt_date($p_date_to) . ')';

        return (new isys_maintenance_reportview_fpdi('P'))
            ->SetTitle($l_title)
            ->set_logo_filepath($l_logo_path)
            ->set_maintenance_data($l_maintenances)
            ->set_object_data($l_objects)
            ->first_page()
            ->fill_report();
    } // function
} // class