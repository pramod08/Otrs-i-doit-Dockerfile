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
 * Tree visualization class.
 *
 * @package     modules
 * @subpackage  pro
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.5.0
 */
class isys_visualization_tree extends isys_visualization
{
    /**
     * This method will be called, if the current request is a AJAX request.
     *
     * @return  isys_visualization_graph
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function process()
    {
        $l_service_filter = null;

        if (isset($_GET['profile']) && $_GET['profile'] > 0)
        {
            $l_profile = $_GET['profile'];
        }
        else
        {
            // Load the user-defined "default" profile.
            $l_profile = isys_usersettings::get('cmdb-explorer.default-profile', null);
        } // if

        if ($l_profile > 0)
        {
            $l_profile_defaults = isys_factory::get_instance('isys_visualization_profile_model', $this->m_db)
                ->get_profile($l_profile)
                ->get_row_value('isys_visualization_profile__defaults');

            if ($l_profile_defaults && isys_format_json::is_json_array($l_profile_defaults))
            {
                $l_profile_defaults = isys_format_json::decode($l_profile_defaults);

                if (isset($l_profile_defaults['obj-type-filter']) && is_array($l_profile_defaults['obj-type-filter']))
                {
                    foreach ($l_profile_defaults['obj-type-filter'] as $l_object_type_id)
                    {
                        $this->m_object_types[$l_object_type_id]['filtered'] = true;
                    } // foreach
                } // if

                if (isset($l_profile_defaults['service-filter']))
                {
                    $l_service_filter = $l_profile_defaults['service-filter'];
                } // if
            } // if
        } // if

        // Add some rules to the smarty plugins...
        $l_rules = [
            'C_VISUALIZATION_OBJ_SELECTION'  => [
                'p_strClass'                                      => 'input input-small',
                'p_bDisableDetach'                                => true,
                'p_bInfoIconSpacer'                               => 0,
                'p_strValue'                                      => $_GET[C__CMDB__GET__OBJECT] ?: null,
                'p_strPlaceholder'                                => _L('LC__CATG__CMDB__ODEP_ERROR_SELECT_OBJECT'),
                isys_popup_browser_object_ng::C__CALLBACK__ACCEPT => "idoit.callbackManager.triggerCallback('visualization-init-explorer');",
                'nowiki'                                          => true
            ],
            'C_VISUALIZATION_PROFILE'        => [
                C__CMDB__VISUALIZATION_TYPE => C__CMDB__VISUALIZATION_TYPE__TREE,
                'p_strSelectedID'           => $l_profile,
                'nowiki'                    => true
            ],
            'C_VISUALIZATION_SERVICE_FILTER' => [
                'p_strSelectedID' => $l_service_filter
            ]
        ];

        // And do the template assignments.
        $this->m_tpl->assign('visualization_type', 'isys_visualization_tree_model')
            ->assign('object_types', $this->m_object_types)
            ->smarty_tom_add_rules('tom.content.top', $l_rules)
            ->include_template('contentbottomcontent', __DIR__ . '/assets/tree.tpl');

        return $this;
    } // function

    /**
     * This method will check, if the current request is a AJAX request and (if so) process the necessary logic.
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function process_ajax()
    {
        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json');

        $l_return = [
            'success' => true,
            'data'    => null,
            'message' => null
        ];

        try
        {
            switch ($_GET['func'])
            {
                // Will be used for loading the initial data.
                case 'load-tree-data':
                    $l_object  = (int) $_POST['object'];
                    $l_filter  = (int) $_POST['filter'];
                    $l_profile = (int) $_POST['profile'];

                    // We use the visitor for the "last inserted ID".
                    $l_return['data'] = [
                        'explorer_a'       => $this->m_model->recursion_run($l_object, $l_filter, $l_profile),
                        'explorer_a_count' => isys_visualization_tree_visitor::get_last_id(),
                        'explorer_b'       => $this->m_model->recursion_run($l_object, $l_filter, $l_profile, false),
                        'explorer_b_count' => isys_visualization_tree_visitor::get_last_id(),
                        'profile'          => isys_factory::get_instance('isys_visualization_profile_model', $this->m_db)
                            ->get_profile_config($l_profile)
                    ];
                    break;

                // Will be used for loading parts of the tree.
                case 'load-tree-level':
                    $l_object       = (int) $_POST['object'];
                    $l_by_master    = (bool) $_POST['by_master'];
                    $l_only_one_row = (bool) $_POST['only_one_row'];
                    $l_last_id      = (int) $_POST['last_id'];
                    $l_profile      = (int) $_POST['profile'];
                    $l_filter       = (int) $_POST['filter'];

                    $l_filter = $this->m_model->load_service_filter($l_filter);

                    if ($l_only_one_row)
                    {
                        $l_filter['level'] = 1;
                    } // if

                    $l_return['data'] = [
                        'nodes' => $this->m_model->recursion_run(
                            $l_object,
                            $l_filter,
                            $l_profile,
                            $l_by_master,
                            ($l_last_id + 10)
                        ),
                        'count' => isys_visualization_tree_visitor::get_last_id()
                    ];
                    break;
            } // switch
        }
        catch (isys_exception $e)
        {
            $l_return['success'] = false;
            $l_return['message'] = $e->getMessage();
        } // try

        echo isys_format_json::encode($l_return);
        die;
    } // function
} // class