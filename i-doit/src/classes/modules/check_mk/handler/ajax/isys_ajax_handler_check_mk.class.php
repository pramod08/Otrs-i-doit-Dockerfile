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
 * @package     Modules
 * @subpackage  Check_MK
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.4.0
 */
class isys_ajax_handler_check_mk extends isys_ajax_handler
{
    /**
     * Init method, which gets called from the framework.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function init()
    {
        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json');

        $l_return = [
            'success' => true,
            'message' => null,
            'data'    => null
        ];

        // We initialize the check_mk helper, just in case.
        isys_check_mk_helper::init();

        try
        {
            switch ($_GET['func'])
            {
                case 'load_new_dynamic_tag_row':
                    $l_return['data'] = $this->load_new_dynamic_tag_row($_POST['count']);
                    break;

                case 'load_dynamic_parameter':
                    $l_return['data'] = $this->load_dynamic_parameter($_POST['count'], $_POST['condition']);
                    break;

                case 'load_generic_conditions':
                    $l_return['data'] = $this->load_generic_conditions($_POST['obj_type']);
                    break;

                case 'export':
                    $l_return['data'] = $this->export((int) $_POST['export_structure'], (int) $_POST['export_language']);
                    break;

                case 'shellscript':
                    $l_return['data'] = $this->execute_shellscript();
                    break;
            } // switch
        }
        catch (Exception $e)
        {
            $l_return['success'] = false;
            $l_return['message'] = $e->getMessage();
        } // try

        echo isys_format_json::encode($l_return);

        $this->_die();
    } // function

    /**
     * This method defines, if the hypergate needs to be included for this request.
     *
     * @static
     * @return  boolean
     */
    public static function needs_hypergate()
    {
        return true;
    } // function

    /**
     * The export method.
     *
     * @param   integer $p_export_structure
     * @param   integer $p_export_language
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function export($p_export_structure = null, $p_export_language = null)
    {
        // Build a new Check_MK export instance.
        $l_export = new \idoit\Module\Check_mk\Export(
            [
                'export_structure' => (int) $p_export_structure,
                'language'         => (int) $p_export_language
            ]
        );

        // Begin the export.
        $l_export->export();

        /*
        $l_export = isys_check_mk_export::instance()
            ->init_export(array(
                'export_structure' => (int) $p_export_structure,
                'language' => (int) $p_export_language
            ))
            ->start_export();
        */

        return [
            'log_icons' => \idoit\Component\Logger::getLevelIcons(),
            'log'       => $l_export->getLogRecords(),
            'files'     => $l_export->getExportedFiles()
        ];
    } // function

    /**
     * Method for calling the check_mk shellscript to transfer the exported files to the check_mk server.
     *
     * @return  string
     * @throws  isys_exception_general
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function execute_shellscript()
    {
        if (function_exists('shell_exec'))
        {
            return shell_exec('./checkmk_transfer.sh --force');
        } // if

        throw new isys_exception_general('Your system does not allow the usage of "shell_exec", please enable this function in your configuration.');
    } // function

    /**
     * Method for loading the "multiselect" field data.
     *
     * @param   integer $p_count
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function load_new_dynamic_tag_row($p_count)
    {
        global $g_comp_template;

        $l_dialog_list = new isys_smarty_plugin_f_dialog_list();
        $l_tag_params  = [
            'id'                => 'dynamic-tag-taglist-' . $p_count,
            'name'              => 'dynamic-tag-taglist-' . $p_count,
            'p_strClass'        => 'normal',
            'p_arData'          => serialize(
                isys_cmdb_dao_category_g_cmk_tag::instance($this->m_database_component)
                    ->get_tags_for_dialog_list()
            ),
            'p_bInfoIconSpacer' => 0
        ];

        return [
            'count'     => $p_count,
            'parameter' => isys_check_mk_dao::get_dynamic_tag_parameters(C__MODULE__CMK__DYNAMIC_TAG__OBJECT_TYPE, $p_count),
            'tags'      => $l_dialog_list->navigation_edit($g_comp_template->activate_editmode(), $l_tag_params)
        ];
    } // function

    /**
     * Method for (re-) loading the dynamic parameter set.
     *
     * @param   integer $p_count
     * @param   integer $p_condition
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function load_dynamic_parameter($p_count, $p_condition)
    {
        return [
            'parameter' => isys_check_mk_dao::get_dynamic_tag_parameters($p_condition, $p_count)
        ];
    } // function

    /**
     * Method for (re-) loading the generic conditions for an object-type.
     *
     * @param   integer $p_obj_type_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function load_generic_conditions($p_obj_type_id)
    {
        $l_tmp = $l_return = [];
        /**
         * @var $l_dao isys_cmdb_dao_object_type
         */
        $l_dao        = isys_cmdb_dao_object_type::factory($this->get_database_component());
        $l_conditions = isys_check_mk_dao_generic_tag::get_conditions();

        foreach ($l_conditions as $l_id => $l_condition)
        {
            if (!in_array($l_id, $l_tmp))
            {
                if (!$l_dao->has_cat($p_obj_type_id, $l_condition['category_const']))
                {
                    continue;
                } // if

                $l_tmp[] = $l_id;
            } // if

            $l_return[$l_id] = _L($l_condition['title']);
        } // foreach

        return $l_return;
    } // function
} // class