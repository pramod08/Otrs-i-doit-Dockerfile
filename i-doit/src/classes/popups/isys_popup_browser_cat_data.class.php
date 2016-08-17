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
 * Popup class for various category-data selections (multiselection only!).
 *
 * @package     i-doit
 * @subpackage  Popups
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_popup_browser_cat_data extends isys_component_popup
{
    /**
     * Constant for automatic data retrieval.
     * Example: "isys_cmdb_dao_category_g_ip::catdata_browser".
     */
    const C__DATARETRIEVAL = "dataretrieval";

    /**
     * Method for displaying the browser.
     *
     * @param   isys_module_request $p_modreq
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @return  void
     */
    public function &handle_module_request(isys_module_request $p_modreq)
    {
        global $g_comp_database;

        $l_params = isys_format_json::decode(base64_decode($_POST['params']), true);

        // Unpack module request.
        $l_tplpopup = $p_modreq->get_template();

        if ($l_params[C__CMDB__GET__OBJECT] > 0)
        {
            if (class_exists($l_params[self::C__DATARETRIEVAL][0]))
            {
                $l_dao = new $l_params[self::C__DATARETRIEVAL][0]($g_comp_database);

                if (method_exists($l_dao, $l_params[self::C__DATARETRIEVAL][1]))
                {
                    $l_data = call_user_func(
                        [
                            $l_dao,
                            $l_params[self::C__DATARETRIEVAL][1]
                        ],
                        $l_params[C__CMDB__GET__OBJECT]
                    );

                    $l_tplpopup->assign("browser_title", _L($l_params['title']))
                        ->assign("preselection", $l_params['preselection'])
                        ->assign("data", $l_data)
                        ->assign("obj_title", $l_dao->get_obj_name_by_id_as_string($l_params[C__CMDB__GET__OBJECT]));
                } // if
            } // if
        } // if

        $l_tplpopup->assign("hidden_field", $l_params["hidden"])
            ->assign("view_field", $l_params["view"])
            ->display('popup/cat_data.tpl');
        die();
    } // function

    /**
     * Handles SMARTY request for location browser.
     *
     * @global  array                   $g_dirs
     * @global  isys_component_database $g_comp_database
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function handle_smarty_include(isys_component_template &$p_tplclass, $p_params)
    {
        global $g_dirs, $g_comp_database;

        $l_objPlugin = new isys_smarty_plugin_f_text();

        $l_selection = [];
        $l_data      = [];

        $l_browser_params = [
            self::C__DATARETRIEVAL => explode('::', $p_params[self::C__DATARETRIEVAL]),
            C__CMDB__GET__OBJECT   => $p_params['p_strSelectedID'],
            'preselection'         => isys_format_json::decode(html_entity_decode($p_params["p_preSelection"]), true),
            'hidden'               => $p_params["name"] . '__HIDDEN',
            'view'                 => $p_params["name"] . '__VIEW',
            'title'                => $p_params["title"]
        ];

        if (class_exists($l_browser_params[self::C__DATARETRIEVAL][0]))
        {
            $l_dao = new $l_browser_params[self::C__DATARETRIEVAL][0]($g_comp_database);

            if (method_exists($l_dao, $l_browser_params[self::C__DATARETRIEVAL][1]))
            {
                $l_data = call_user_func(
                    [
                        $l_dao,
                        $l_browser_params[self::C__DATARETRIEVAL][1]
                    ],
                    $l_browser_params[C__CMDB__GET__OBJECT]
                );
            } // if
        } // if

        if (isset($l_browser_params['preselection']) && is_array($l_browser_params['preselection']))
        {
            foreach ($l_browser_params['preselection'] as $l_preselection)
            {
                if (array_key_exists($l_preselection, $l_data))
                {
                    $l_selection[] = strip_tags($l_data[$l_preselection]);
                } // if
            } // foreach
        } // if

        $p_params["p_strValue"] = implode(', ', $l_selection);

        $l_hidden_field = '<input id="' . $p_params["name"] . '__HIDDEN" name="' . $p_params['name'] . '__HIDDEN" type="hidden" value="' . $p_params['p_preSelection'] . '" />';

        // Set params for the f_text plugin.
        $p_params["name"]        = $p_params["name"] . "__VIEW";
        $p_params["p_bReadonly"] = 1;

        if (isys_glob_is_edit_mode())
        {
            return $l_objPlugin->navigation_edit($p_tplclass, $p_params) . '<span class="ml5 mouse-pointer" title="' . _L(
                'LC_POPUP_IMAGE__CHOOSE_IMAGE'
            ) . '" onClick="' . $this->process_overlay('', 800, 400, $l_browser_params) . ';">' . '<img src="' . $g_dirs['images'] . 'icons/silk/zoom.png" alt="' . _L(
                'LC_UNIVERSAL__MAGNIFIER'
            ) . '" />' . '</span>' . $l_hidden_field;
        }
        else
        {
            return '<img style="width:15px; height:15px;" src="' . $g_dirs['images'] . 'empty.gif" class="infoIcon vam mr5">' . $p_params["p_strValue"] . $l_hidden_field;
        } // if
    } // function
} // class