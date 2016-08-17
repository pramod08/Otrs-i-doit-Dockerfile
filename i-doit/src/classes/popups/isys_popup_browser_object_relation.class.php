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
 * Object browser.
 *
 * @package     i-doit
 * @subpackage  Popups
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_popup_browser_object_relation extends isys_popup_browser_object_ng
{
    /**
     * This is mainly the same method as in the parent class, but we do a few quite specific things so we have to duplicate this whole method.
     *
     * @global  isys_component_database $g_comp_database
     *
     * @param   isys_module_request     $p_modreq
     */
    public function &handle_module_request(isys_module_request $p_modreq)
    {
        global $g_comp_database;

        // Initialization.
        $this->m_template = $p_modreq->get_template();

        // Parameter retrieval.
        $l_params_decoded = base64_decode($_POST["params"]);
        $l_params         = isys_format_json::decode($l_params_decoded, true);

        // Parameter validation.
        if (is_array($l_params))
        {
            $this->m_params = $l_params;

            try
            {
                // Assign some smarty configuration variables.
                $this->m_template->assign(self::C__MULTISELECTION, $l_params[self::C__MULTISELECTION])
                    ->assign(self::C__CALLBACK__ACCEPT, $l_params[self::C__CALLBACK__ACCEPT])
                    ->assign(self::C__CALLBACK__ABORT, $l_params[self::C__CALLBACK__ABORT])
                    ->assign(self::C__FORM_SUBMIT, $l_params[self::C__FORM_SUBMIT])
                    ->assign(self::C__TYPE_FILTER, $l_params[self::C__TYPE_FILTER])
                    ->assign(self::C__GROUP_FILTER, $l_params[self::C__GROUP_FILTER]);

                // Look, if we set an own title for this browser instance.
                if (!isset($l_params[self::C__TITLE]))
                {
                    $this->m_template->assign('browser_title', _L('LC__POPUP__BROWSER__OBJECT_BROWSER'));
                }
                else
                {
                    $this->m_template->assign('browser_title', _L($l_params[self::C__TITLE]));
                } // if

                // Check for specific filtering.
                if (isset($l_params[self::C__CATEGORY_FILTER]))
                {
                    $l_filter = explode("::", $l_params[self::C__CATEGORY_FILTER]);

                    if (class_exists($l_filter[0]))
                    {
                        $l_filterObject = new $l_filter[0]($g_comp_database);

                        if (method_exists($l_filterObject, $l_filter[1]))
                        {
                            $l_data = call_user_func(
                                [
                                    $l_filterObject,
                                    $l_filter[1]
                                ],
                                self::C__CALL_CONTEXT__PREPARATION
                            );

                            if ($l_data)
                            {
                                $this->m_template->assign("arCategoryFilter", $l_data)
                                    ->assign(self::C__CATEGORY_FILTER, $l_params[self::C__CATEGORY_FILTER]);
                            } // if
                        } // if
                    } // if
                } // if

                // Automatically set the return element.
                if ((!isset($l_params[self::C__RETURN_ELEMENT]) || empty($l_params[self::C__RETURN_ELEMENT])) && isset($l_params["name"]))
                {
                    if (strstr($l_params["name"], '[') && strstr($l_params["name"], ']'))
                    {
                        $l_tmp    = explode('[', $l_params["name"]);
                        $l_view   = $l_tmp[0] . '__VIEW[' . implode('[', array_slice($l_tmp, 1));
                        $l_hidden = $l_tmp[0] . '__HIDDEN[' . implode('[', array_slice($l_tmp, 1));
                        unset($l_tmp);
                    }
                    else
                    {
                        $l_view   = $l_params["name"] . '__VIEW';
                        $l_hidden = $l_params["name"] . '__HIDDEN';
                    }

                    $this->m_template->assign("return_element", $l_hidden)
                        ->assign("return_view", $l_view);
                }
                else
                {
                    $this->m_template->assign("return_element", $l_params[self::C__RETURN_ELEMENT]);
                } // if

                // Assign json encoded params.
                $this->m_template->assign("params", $l_params_decoded);

                // Call handlers.
                if (!$l_params[self::C__SELECTION] && $l_params["p_strSelectedID"])
                {
                    // @todo Why do we pack this inside an array !?
                    $l_params[self::C__SELECTION] = [$l_params["p_strSelectedID"]];
                } // if

                $this->handle_preselection($l_params[self::C__SELECTION], $l_params[self::C__DATARETRIEVAL]);

                // Preparations.
                $this->prepare_smarty_assignments($l_params);
            }
            catch (isys_exception_objectbrowser $e)
            {
                $this->m_template->assign("error", $e->getMessage())
                    ->assign("errorDetail", $e->getDetailMessage());
            }
            catch (Exception $e)
            {
                $this->m_template->assign("error", $e->getMessage());
            } // try
        }
        else
        {
            $this->m_template->assign("error", "Parameter error.");
        } // if

        // Javascript initialization
        $l_gets = $p_modreq->get_gets();

        // Disable the search for all second-selection browser.
        $this->m_tabconfig["search"]["disabled"]   = true;
        $this->m_tabconfig["location"]["disabled"] = true;
        $this->m_tabconfig["report"]["disabled"]   = true;

        // Create the AJAX-string.
        $l_ajaxgets = [
            C__CMDB__GET__POPUP           => $l_gets[C__CMDB__GET__POPUP],
            C__GET__MODULE_ID             => C__MODULE__CMDB,
            C__CMDB__GET__CONNECTION_TYPE => $l_gets[C__CMDB__GET__CONNECTION_TYPE],
            C__CMDB__GET__CATG            => $l_gets[C__CMDB__GET__CATG],
            C__GET__AJAX_REQUEST          => 'handle_ajax_request',
            'request'                     => $l_params[self::C__SECOND_LIST],
        ];

        $this->m_template// Assign the Ajax URL for calling from the template.
        ->assign(
            'ajax_url',
            isys_glob_build_url(isys_glob_http_build_query($l_ajaxgets))
        )// Assign the cable-connection JS (change name to "dual-browser" or something...).
        ->assign('js_init', 'popup/object_relation.js')// Enable second selection.
        ->assign(self::C__SECOND_SELECTION, true)// Assign tab configuration.
        ->assign("tabs", $this->m_tabconfig)// We need the information if the user shall only be able to select relations.
        ->assign("relation_only", ($l_params[self::C__RELATION_ONLY] ? 1 : 0))// Show popup content and die.
        ->display('popup/object_ng.tpl');
        die();
    } // function

    /**
     * Handles the smarty including and displays selected objects and a link to open the popup.
     *
     * @param   isys_component_template $p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     */
    public function handle_smarty_include(isys_component_template &$p_tplclass, $p_params)
    {
        // We don't need to copy the whole method, so we just do what we need and call the parent.
        $p_params[self::C__SECOND_LIST] = [
            ['isys_cmdb_dao_category_g_relation::object_browser_get_data_by_object_and_relation_type'],
            $p_params[self::C__RELATION_FILTER]
        ];

        return parent::handle_smarty_include($p_tplclass, $p_params);
    } // function
} // class