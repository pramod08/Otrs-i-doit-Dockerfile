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
 * i-doit Report Manager View
 *
 * @package     i-doit
 * @subpackage  Reports
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   Copyright 2013 - synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       1.0
 */
class isys_qrcode_reportview_qr_codes extends isys_report_view
{
    /**
     * Method for ajax-requests.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function ajax_request()
    {
        global $g_comp_database, $g_comp_template, $g_dirs;

        // This will be used for the printable popup.
        if ($_GET['printview'] == '1')
        {
            $l_layouts = explode('---', file_get_contents(__DIR__ . DS . 'view_qr_code_layouts.html'));

            $l_layout = str_replace(
                [
                    '%qrcode_url%',
                    '%description%',
                    '%logo_url%'
                ],
                [
                    $g_dirs['images'] . 'icons/tree/qr_code.png',
                    _L('LC__REPORT__VIEW__QR_CODES__LAYOUT_DESCRIPTION'),
                    $g_dirs['images'] . 'favicon.png'
                ],
                $l_layouts[$_GET['layout']]
            );

            $g_comp_template->assign('ajax_url', isys_glob_url_remove('?' . $_SERVER['QUERY_STRING'], 'printview'))
                ->assign('qr_code_size', $_GET['size'])
                ->assign('qr_code_error_correction', $_GET['error_correction'])
                ->assign('columns', $_GET['cols'])
                ->assign('obj_ids', $_GET['objects'])
                ->assign('text_alignment', $_GET['text_alignment'])
                ->assign('layout', $l_layout);

            $g_comp_template->display(__DIR__ . DS . 'view_qr_codes_popup.tpl');
            die();
        } // if

        $l_return = ['success' => true];

        try
        {
            if (isset($_POST['obj_ids']))
            {
                $l_object_ids = isys_format_json::decode($_POST['obj_ids'], true);
            } // if
            else $l_object_ids = [];

            /**
             * @var $l_qrcode_module isys_module_qrcode
             */
            $l_qrcode_module = isys_factory::get_instance('isys_module_qrcode')
                ->init(isys_module_request::get_instance());

            if (is_array($l_object_ids))
            {
                foreach ($l_object_ids as $l_object_id)
                {
                    $l_qr_code_data = $l_qrcode_module->load_qr_code($l_object_id);

                    if (isys_tenantsettings::get('barcode.type', 'qr') == 'qr')
                    {
                        $l_url = 'src/tools/php/qr/qr_img.php?d=' . $l_qr_code_data['url'];
                    }
                    else
                    {
                        $l_url = 'src/tools/php/barcode.php?height=65&barcode=' . $l_qr_code_data['sysid'];
                    } // if

                    $l_return['data'][] = [
                        'logo'        => $l_qr_code_data['logo'],
                        'success'     => true,
                        'url'         => isys_application::instance()->www_path . $l_url,
                        'description' => $l_qr_code_data['description']
                    ];
                } // foreach
            } // if
        }
        catch (Exception $e)
        {
            $l_return['success'] = false;
            $l_return['message'] = $e->getMessage();
        } // try

        header('Content-Type: application/json');

        echo isys_format_json::encode($l_return);

        die;
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
        return 'LC__REPORT__VIEW__QR_CODES_DESCRIPTION';
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
        return 'LC__REPORT__VIEW__QR_CODES';
    } // function

    /**
     * Start-method - Implement the logic for displaying your data here.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @return  void
     */
    public function start()
    {
        global $g_dirs;

        $l_rules = [
            'C__QR_CODE_ERROR_CORRECTION'       => [
                'p_arData' => [
                    'l' => _L('LC__REPORT__VIEW__QR_CODES__CORRECTION__LOW'),
                    'm' => _L('LC__REPORT__VIEW__QR_CODES__CORRECTION__MEDIUM'),
                    'q' => _L('LC__REPORT__VIEW__QR_CODES__CORRECTION__QUALITY'),
                    'h' => _L('LC__REPORT__VIEW__QR_CODES__CORRECTION__HIGH')
                ]
            ],
            'C__QR_CODE_DEFAULT_TEXT_ALIGNMENT' => [
                'p_arData'        => [
                    'left'    => _L('LC__QR_CODE_DEFAULT_TEXT_ALIGNMENT_LEFT'),
                    'center'  => _L('LC__QR_CODE_DEFAULT_TEXT_ALIGNMENT_CENTER'),
                    'right'   => _L('LC__QR_CODE_DEFAULT_TEXT_ALIGNMENT_RIGHT'),
                    'justify' => _L('LC__QR_CODE_DEFAULT_TEXT_ALIGNMENT_JUSTIFY')
                ],
                'p_strSelectedID' => 'center'
            ]
        ];

        $l_layouts = file_get_contents(__DIR__ . DS . 'view_qr_code_layouts.html');
        $l_layouts = str_replace(
            [
                '%qrcode_url%',
                '%description%',
                '%logo_url%'
            ],
            [
                $g_dirs['images'] . 'icons/tree/qr_code.png',
                _L('LC__REPORT__VIEW__QR_CODES__LAYOUT_DESCRIPTION'),
                $g_dirs['images'] . 'favicon.png'
            ],
            $l_layouts
        );

        $l_configuration_url = isys_helper_link::create_url(
            [
                C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                C__GET__MODULE_SUB_ID => C__MODULE__QRCODE,
                C__GET__TREE_NODE     => C__MODULE__QRCODE . '0'
            ]
        );

        // Assign the ajax URL to the template.
        isys_application::instance()->template->activate_editmode()
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules)
            ->assign('configuration_url', $l_configuration_url)
            ->assign('ajax_url', isys_glob_add_to_query('ajax', 1))
            ->assign('layouts', explode('---', $l_layouts));
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
        return __DIR__ . DS . 'view_qr_codes.tpl';
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
        return 'LC__REPORT__VIEW__QR_CODES';
    } // function
} // class