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
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_popup_browser_object_ng extends isys_component_popup
{
    const C__OBJECT_BROWSER__TAB__LOCATION = "location";

    /* Constants for the tabs */
    const C__OBJECT_BROWSER__TAB__SEARCH = "search";
    const C__OBJECT_BROWSER__TAB__LOG    = "log";
    const C__OBJECT_BROWSER__TAB__REPORT = "report";
    const C__OBJECT_BROWSER__TAB         = "tabconfig";
    const C__CALL_CONTEXT__PREPARATION   = 1;
    const C__CALL_CONTEXT__REQUEST       = 2;

    const C__USE_AUTH = 'use_auth';

    /**
     * CMDB filter for objects.
     * Example: "C__CMDB_STATUS__ORDERED;C__CMDB_STATUS__DELIVERED".
     */
    const C__CMDB_FILTER = 'cmdb_filter';
    /**
     * Callback function for aborting/cancelling the object-browser.
     * Example: alert('Abort Callback').
     */
    const C__CALLBACK__ABORT = "callback_abort";

    /* Call contexts, used for category filtering. */
    /**
     * Callback for accepting.
     * Example: alert('Accept Callback').
     */
    const C__CALLBACK__ACCEPT = "callback_accept";
    /**
     * Callback function for detaching the object.
     * Example: alert('Detach Callback').
     */
    const C__CALLBACK__DETACH = "callback_detach";
    /**
     * If this filter is configured, you should only get this category specific filter inside the object browser. Every other filter function should be disabled.
     * Example: array(array("isys_cmdb_dao_category_g_virtual_host", "object_browser_filter"), $_GET[C__CMDB__GET__OBJECT]).
     */
    const C__CATEGORY_FILTER = "categoryFilter";
    /**
     * Constant for automatic data retrieval.
     * Example: array(array("isys_cmdb_dao_category_s_group", "get_browser_selection"), $_GET[C__CMDB__GET__OBJECT]).
     */
    const C__DATARETRIEVAL = "dataretrieval";
    /**
     * Disables the detach field.
     * Example: (boolean) true, false. Default: false.
     */
    const C__DISABLE_DETACH = "p_bDisableDetach";
    /**
     * Disables the hidden field.
     * Example: (boolean) true, false. Default: false.
     */
    const C__DISABLE_HIDDEN = "nohidden";
    /**
     * With this parameter you can tell the object browser to directly enter the edit mode. Used for the IP-list for example.
     * Example: (boolean) true, false.
     */
    const C__EDIT_MODE = 'edit_mode';
    /**
     * Do formsubmit after accept.
     * Example: (boolean) true, false.
     */
    const C__FORM_SUBMIT = "formsubmit";
    /**
     * Group filter, for easy usage in the template.
     * Example: "C__OBJTYPE_GROUP__INFRASTRUCTURE;C__OBJTYPE_GROUP__SOFTWARE".
     *
     * @deprecated  Please try to use "C__CAT_FILTER" instead.
     */
    const C__GROUP_FILTER = "groupFilter";
    /**
     * Category filter, for easy usage in the template.
     * Example: "C__CATS__ROUTER;C__CATG__MODEL;C__CATG__PORT
     * Note: If you like to add a sub cat you'll have to use the cat dir.
     */
    const C__CAT_FILTER = "catFilter";
    /**
     * Constant for defining the use of multiselection.
     * Example: (boolean) true, false.
     */
    const C__MULTISELECTION = "multiselection";
    /**
     * Constant for defining the field to be un-writable.
     * Example: (boolean) true, false.
     */
    const C__READ_ONLY = 'readOnly';
    /**
     * Relation filter (type).
     * Example: "C__RELATION_TYPE__SOFTWARE".
     */
    const C__RELATION_FILTER = "relationFilter";
    /**
     * Make only relations selectable.
     * Example: (boolean) true, false.
     */
    const C__RELATION_ONLY = "relationOnly";
    /**
     * Switches the report-filter on and off.
     * Example: (boolean) true, false.
     */
    const C__REPORT_FILTER = "reportFilter";
    /**
     * ID of the return element. Works only for multiselection!
     * Example: popupReceiver.
     */
    const C__RETURN_ELEMENT = "returnElement";
    /**
     * ID of return element for the second-selection.
     * Example: popupSecondReceiver.
     */
    const C__RETURN_SECOND_ELEMENT = 'returnElement2';
    /**
     * Method for retrieving the JSON for the second list.
     * Example: 'isys_cmdb_dao_xyz::object_browser_list' or array("isys_cmdb_dao_xyz", "object_browser_list").
     */
    const C__SECOND_LIST = "secondList";
    /**
     * Method for retrieving the correct format for displaying the input-contents (called in $this->format_selection())
     * Example: 'isys_cmdb_dao_xyz::format_selection' or array("isys_cmdb_dao_xyz", "format_selection").
     */
    const C__SECOND_LIST_FORMAT = "secondListFormat";
    /**
     * Constant, if the "second selection"-view shall be activated.
     * Example: (boolean) true, false.
     */
    const C__SECOND_SELECTION = "secondSelection";
    /**
     * Constant for object preselection.
     * Example: JSON array [1,2,3,4,5,6] or PHP array.
     */
    const C__SELECTION = "selection";
    /**
     * Constant for defining the browser-title, through language constants.
     * Example: 'LC__BROWSER__TITLE__CONNECTION'.
     */
    const C__TITLE = "title";
    /**
     * Type filter, for easy usage in the template.
     * Example: "C__OBJTYPE__SERVER;C__OBJTYPE__CLIENT;C__OBJTYPE__RELATION".
     *
     * @deprecated  Please try to use "C__CAT_FILTER" instead.
     */
    const C__TYPE_FILTER = "typeFilter";
    /**
     * @desc Minimum right to create an object
     * Example: isys_auth::EDIT
     */
    const C__CHECK_RIGHT = "checkRight";
    /**
     * The selection sorting can only be used with "multiselect". This is used in the nagios categories so far.
     * Example: (boolean) true, false.
     */
    const C__SORT_SELECTION = 'sortSelection';
    /**
     * The selection sorting can only be used with "multiselect". This is used in the nagios categories so far.
     * Example: (boolean) true, false.
     */
    const C__LOCATION_VIEW__CONTAINERS_ONLY = 'location_containers_only';
    /**
     * In some case we don´t want to exclude some objecttypes if we use the category filter
     * Example: "C__OBJTYPE__SUPERNET;C__OBJTYPE__MIGRATION_OBJECT
     */
    const C__TYPE_BLACK_LIST = "typeBlacklist";
    /**
     * This array will hold the object types for the "object-type" filter.
     *
     * @var  array
     */
    protected $m_object_types = [];
    /**
     * The params, written in the smarty template.
     *
     * @var  array
     */
    protected $m_params = [];
    /**
     * Tab configuration.
     *
     * @var  array
     */
    protected $m_tabconfig = [
        self::C__OBJECT_BROWSER__TAB__LOCATION => ["disabled" => false],
        self::C__OBJECT_BROWSER__TAB__SEARCH   => ["disabled" => false],
        self::C__OBJECT_BROWSER__TAB__LOG      => ["disabled" => false],
        self::C__OBJECT_BROWSER__TAB__REPORT   => ["disabled" => false]
    ];
    /**
     * The smarty-template variable.
     *
     * @var  isys_component_template
     */
    protected $m_template;
    /**
     * Display quickinfo in format_selection
     *
     * @var bool
     */
    private $m_format_quick_info = true;

    /**
     * Get object type ids which are matched by our multiple filter parameters.
     *
     * @param   array   $p_objectgroup       array('C__OBJ_GROUP__INFRASTRACTURE', ...)
     * @param   array   $p_objecttype        array('C__OBJTYPE__SERVER', 'C__OBJTYPE__ROUTER', ...)
     * @param   array   $p_category          array('C__CATG__GLOBAL', 'C__CATS__ROUTER')
     * @param   array   $p_objtype_blacklist array('C__OBJTYPE__SERVER', 'C__OBJTYPE__ROUTER', ...)
     * @param   boolean $p_as_constant_array
     *
     * @return  array
     * @author Selcuk Kekec <skekec@i-doit.org>
     * @author Leonard Fischer <lfischer@i-doit.com>
     */
    public static function get_objecttype_filter(array $p_objectgroup = [], array $p_objecttype = [], array $p_category = [], array $p_objtype_blacklist = [], $p_as_constant_array = false)
    {
        $l_arrObjectTypes = [];
        $l_dao            = new isys_cmdb_dao_nexgen(isys_application::instance()->database);
        $l_groupFilter    = (is_array($p_objectgroup) && count($p_objectgroup)) ? array_flip($p_objectgroup) : false;
        $l_typeFilter     = (is_array($p_objecttype) && count($p_objecttype)) ? array_flip($p_objecttype) : false;
        $l_catFilter      = (is_array($p_category) && count($p_category)) ? $p_category : false;
        $l_typeBlacklist  = (is_array($p_objtype_blacklist) && count($p_objtype_blacklist)) ? array_flip($p_objtype_blacklist) : false;

        if ($l_typeFilter)
        {
            foreach ($l_typeFilter AS $l_objtype_const => $l_key)
            {
                $l_arr = $l_dao->get_objecttypes_using_cats($l_objtype_const);

                if ($l_arr)
                {
                    $l_typeFilter = array_merge($l_typeFilter, (array) array_flip($l_arr));
                } // if
            } // foreach
        } // if

        // Get objecttype groups.
        $l_objgroups = $l_dao->objgroup_get();

        /* @var  $l_cmdb_dao  isys_cmdb_dao_object_type */
        $l_cmdb_dao = isys_cmdb_dao_object_type::instance(isys_application::instance()->database);

        while ($l_row = $l_objgroups->get_row())
        {
            // Check if we can skip this object group.
            if (($l_groupFilter && !isset($l_groupFilter[$l_row["isys_obj_type_group__const"]])) || $l_row["isys_obj_type_group__status"] != C__RECORD_STATUS__NORMAL)
            {
                continue;
            } // if

            // Get object types for current group.
            $l_objtypes = $l_dao->objtype_get_by_objgroup_id($l_row["isys_obj_type_group__id"]);
            while ($l_otrow = $l_objtypes->get_row())
            {
                // Check if we can skip this object type.
                if ($l_typeFilter && !isset($l_typeFilter[$l_otrow["isys_obj_type__const"]]))
                {
                    continue;
                } // if

                if ($l_catFilter && !$l_cmdb_dao->has_cat($l_otrow["isys_obj_type__id"], $l_catFilter))
                {
                    continue;
                } // if

                if ($l_typeBlacklist && isset($l_typeBlacklist[$l_otrow["isys_obj_type__const"]]))
                {
                    continue;
                } // if

                $l_arrObjectTypes[] = $p_as_constant_array ? $l_otrow['isys_obj_type__const'] : $l_otrow['isys_obj_type__id'];
            } // while
        } // while

        return $l_arrObjectTypes;
    }

    /**
     * @param bool|true $bool
     *
     * @inherit
     * @return $this
     */
    public function set_format_quick_info($bool = true)
    {
        $this->m_format_quick_info = $bool;

        return $this;
    } // function

    /**
     * Handle specific ajax requests.
     *
     * @param   isys_module_request $p_modreq
     *
     * @return  mixed
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @throws  isys_exception_objectbrowser
     */
    public function &handle_ajax_request(isys_module_request $p_modreq)
    {
        // If we get an array, it's most likely something like "array('class::method', array('params'))".
        if (is_array($_GET['request']))
        {
            // Write the callback-method in the callback variable.
            $l_callback = $_GET['request'][0];

            // Check if we got a second item in the array (We'll assume they are parameters).
            $l_callback_params = (isset($_GET['request'][1])) ? (array) $_GET['request'][1] : [];
        }
        else if (is_string($_GET['request']))
        {
            // We will take the string, if given, as "class::method".
            $l_callback = $_GET['request'];

            // And assign an empty array as parameters.
            $l_callback_params = [];
        }
        else
        {
            throw new isys_exception_objectbrowser(
                'Wrong parameter.',
                'A wrong parameter has been assigned to isys_popup_browser_object_ng::C__SECOND_LIST. The syntax should be: array("class::method", array("params") or "class::method".'
            );
        } // if

        // Yeah, this can happen sometimes...
        if (is_array($l_callback) && count($l_callback) == 1)
        {
            $l_callback = $l_callback[0];
        }

        // Get an array from our "class::method" string.
        $l_callback = explode('::', $l_callback);

        if (empty($l_callback_params['modreq']))
        {
            $l_callback_params['modreq'] = $p_modreq;
        } // if

        // Be sure to send JSON headers, so prototype can handle everything right.
        header('Content-type: application/json');

        // We won't blindly instancinate a class - We check first.
        if (class_exists($l_callback[0]))
        {
            $l_obj = new $l_callback[0](isys_application::instance()->database);

            if (method_exists($l_obj, $l_callback[1]))
            {
                // Call the method with the context-variable and module request as parameter.
                return call_user_func(
                    [
                        $l_obj,
                        $l_callback[1]
                    ],
                    self::C__CALL_CONTEXT__REQUEST,
                    $l_callback_params
                );
            } // if
        }

        return '[]';
    } // function

    /**
     * Retrieves the list of reports the user is allowed to see.
     *
     * @return  array
     * @author  Leonard Fischer <lficher@synetics.de>
     */
    public function handle_report_request()
    {
        $l_return = [];

        if (class_exists('isys_report_dao'))
        {
            $l_res = isys_report_dao::instance(isys_application::instance()->database_system)
                ->get_reports(
                    null,
                    isys_auth_report::instance()
                        ->get_allowed_reports()
                );

            while ($l_row = $l_res->get_row())
            {
                $l_return[$l_row['category_title']][$l_row['isys_report__id']] = $l_row['isys_report__title'];
            } // while
        } // if
        // We sort the reports by their name.
        $l_return = array_map(
            function ($l_item)
            {
                asort($l_item);

                return $l_item;
            },
            $l_return
        );

        return $l_return;
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
        global $g_dirs;

        // Init.
        $l_strOut       = '';
        $this->m_params = $p_params;
        $l_hiddenValue  = '';
        $l_object_id    = 0;

        if (!isset($this->m_params[self::C__USE_AUTH]))
        {
            $this->m_params[self::C__USE_AUTH] = !!isys_tenantsettings::get('auth.use-in-object-browser', false);
        } // if

        $l_editmode = isys_glob_is_edit_mode();

        // We use this, if we don't use the edit mode for a view and want to display the object browser directly.
        if (isset($p_params[self::C__EDIT_MODE]))
        {
            if ($p_params[self::C__EDIT_MODE] == "1") $l_editmode = true;
            elseif ($p_params[self::C__EDIT_MODE] == "0") $l_editmode = false;
        } // if

        // Check if theres a name given.
        if ($this->m_params["name"])
        {
            $l_objPlugin = new isys_smarty_plugin_f_text();

            if (strstr($this->m_params["name"], '[') && strstr($this->m_params["name"], ']'))
            {
                $l_tmp    = explode('[', $this->m_params["name"]);
                $l_view   = $l_tmp[0] . '__VIEW[' . implode('[', array_slice($l_tmp, 1));
                $l_hidden = $l_tmp[0] . '__HIDDEN[' . implode('[', array_slice($l_tmp, 1));
                unset($l_tmp);
            }
            else
            {
                $l_view   = $this->m_params["name"] . '__VIEW';
                $l_hidden = $this->m_params["name"] . '__HIDDEN';
            }

            // Extract object id from either p_strValue or p_strSelectedID.
            if ($this->m_params["p_strValue"])
            {
                $l_object_id = $this->m_params["p_strValue"];
            }
            else if ($this->m_params["p_strSelectedID"])
            {
                $l_object_id = $this->m_params["p_strSelectedID"];
            } // if

            // Get object name and store id in p_strSelectedID.
            if ($l_object_id)
            {
                $this->m_params["p_strSelectedID"] = $l_object_id;

                // When in multiselection mode, use a different logic.
                if ($this->m_params[self::C__MULTISELECTION])
                {
                    // Have we got a array with objects?
                    if (is_array($l_object_id))
                    {
                        // Just assign for later iteration.
                        $l_objects = $l_object_id;
                    }
                    else if (is_string($l_object_id) && isys_format_json::is_json_array($l_object_id))
                    {
                        // We check if we got a valid JSON.
                        $l_objects = isys_format_json::decode($l_object_id, true);
                    }
                    else
                    {
                        // The last option: A comma-separated list.
                        $l_objects = explode(',', $l_object_id);
                    } // if

                    // We need this to prevent JSON Arrays with quotes.
                    $l_objects = array_map('intval', $l_objects);

                    $l_object_array = [];

                    $i = 1;
                    // Iterate through each object-id.
                    foreach ($l_objects as $l_id)
                    {
                        if ($i++ == isys_tenantsettings::get('cmdb.limits.obj-browser.objects-in-viewmode', 8))
                        {
                            $l_object_array[] = '...';
                            break;
                        }

                        if ($l_id > 0)
                        {

                            $l_object_array[] = $this->set_format_quick_info(!$l_editmode)
                                ->format_selection($l_id);
                        } // if
                    } // foreach

                    $this->m_params["p_strValue"] = implode(', ', $l_object_array);

                    // Prepare value for hidden field.
                    $l_hiddenValue = isys_format_json::encode($l_objects);
                }
                else
                {
                    // Prepare value for hidden field.
                    $l_hiddenValue = $l_object_id;

                    // Prepare value for visible field.
                    $this->m_params["p_strValue"] = $this->set_format_quick_info(!$l_editmode)
                        ->format_selection($l_object_id);
                } // if
            }
            else
            {
                // Sometimes we got an empty array, which will cause PHP errors...
                $this->m_params["p_strValue"] = '';
            } // if

            // This seems to happen sometimes..??
            if (is_array($this->m_params["p_strValue"]))
            {
                $this->m_params["p_strValue"] = implode(', ', $this->m_params["p_strValue"]);
            } // if

            // Auto Suggesstion and read-only.
            if (!isset($this->m_params[self::C__MULTISELECTION]) || !$this->m_params[self::C__MULTISELECTION])
            {
                $this->m_params["p_onClick"]              = "if (!this.getValue().blank()) {this.writeAttribute('placeholder',this.readAttribute('data-last-value')).setValue('');}";
                $this->m_params["p_onBlur"]               = "if (this.getValue().blank()) {this.setValue(this.readAttribute('data-last-value'));}";
                $this->m_params["p_strSuggest"]           = "object";
                $this->m_params["p_strSuggestView"]       = $l_view;
                $this->m_params["p_strSuggestHidden"]     = $l_hidden;
                $this->m_params["p_strSuggestParameters"] = "parameters: { " . self::C__TYPE_FILTER . ": '" . $this->m_params[self::C__TYPE_FILTER] . "', " . self::C__GROUP_FILTER . ": '" . $this->m_params[self::C__GROUP_FILTER] . "', " . self::C__CAT_FILTER . ": '" . $this->m_params[self::C__CAT_FILTER] . "', " . self::C__TYPE_BLACK_LIST . ": '" . $this->m_params[self::C__TYPE_BLACK_LIST] . "', " . self::C__CMDB_FILTER . ": '" . $this->m_params[self::C__CMDB_FILTER] . "' " . "}, selectCallback: function(view, li) {view.writeAttribute('data-last-value', li.readAttribute('title'));" . $this->m_params[self::C__CALLBACK__ACCEPT] . "}";
            }
            else
            {
                $this->m_params["p_bReadonly"] = 1;
            } // if

            // Additional check, if the element should only be readable.
            if ($this->m_params[self::C__READ_ONLY] == true)
            {
                $this->m_params["p_bReadonly"] = 1;
            } // if

            if (!isset($this->m_params[self::C__CHECK_RIGHT]))
            {
                $this->m_params[self::C__CHECK_RIGHT] = 'isys_auth::EDIT';
            }

            if ($l_editmode)
            {
                $this->m_params['p_additional'] .= ' data-hidden-field="' . str_replace('"', '\'', $l_hidden) . '" data-last-value="' . $this->m_params["p_strValue"] . '"';
                $this->m_params["id"] = $l_view;
                $l_strOut .= $l_objPlugin->navigation_edit($p_tplclass, $this->m_params);

                if (!isset($this->m_params["p_bDisabled"]) && !$this->m_params["p_bDisabled"])
                {
                    if (!isset($this->m_params[self::C__DISABLE_HIDDEN]))
                    {
                        $l_strOut .= '<input name="' . $l_hidden . '" id="' . $l_hidden . '" type="hidden" value="' . $l_hiddenValue . '" >' .
                            '<a href="javascript:" title="' . _L("LC__UNIVERSAL__ATTACH") . '" class="' . $this->m_params["name"] . ' attach ml5 vam" onclick="' . $this->process_overlay(
                                "live_preselection=' + $('" . $l_hidden . "').value + '",
                                1100,
                                650,
                                $this->m_params) . '" >' .
                            '<img src="' . $g_dirs["images"] . 'icons/silk/zoom.png" alt=" " title="' . _L('LC__UNIVERSAL__ATTACH') . '" class="vam" />' . '</a>';
                    }
                    else
                    {
                        $l_strOut .= '<a href="javascript:" title="' . _L("LC__UNIVERSAL__ATTACH") . '" class="' . $this->m_params["name"] . ' attach ml5 vam" onclick="' . $this->get_js_handler($this->m_params) . '" >' .
                            '<img src="' . $g_dirs["images"] . 'icons/silk/zoom.png" alt=" " title="' . _L('LC__UNIVERSAL__ATTACH') . '" class="vam" />' .
                            '</a>';
                    } // if

                    if (!isset($this->m_params[self::C__DISABLE_DETACH]))
                    {
                        $l_detach_callback = isset($this->m_params[self::C__CALLBACK__DETACH]) ? $this->m_params[self::C__CALLBACK__DETACH] : "";

                        $l_onclick_detach = "var e_view = $('" . $l_view . "'), e_hidden = $('" . $l_hidden . "');" .
                            "if(e_view && e_hidden) {" .
                                "e_view.writeAttribute('data-last-value', '" . _L('LC__UNIVERSAL__CONNECTION_DETACHED') . "').setValue('" . _L('LC__UNIVERSAL__CONNECTION_DETACHED') . "'); " .
                                "e_hidden.setValue('');" .
                            "}" . $l_detach_callback;

                        $l_strOut .= '<a href="javascript:" title="' . _L("LC__UNIVERSAL__DETACH") . '" class="' . $this->m_params["name"] . ' detach ml5 vam" onclick="' . $l_onclick_detach . ';">' .
                            '<img src="' . $g_dirs["images"] . 'icons/silk/detach.png" alt=" " title="' . _L('LC__UNIVERSAL__DETACH') . '" class="vam" />' .
                            '</a>';
                    } // if

                    if (isset($this->m_params[self::C__SORT_SELECTION]) && $this->m_params[self::C__SORT_SELECTION] && isset($this->m_params[self::C__MULTISELECTION]) && $this->m_params[self::C__MULTISELECTION])
                    {
                        $l_onclick_sort = 'new BrowserSelectionSorter(\'' . $l_view . '\', {values:$F(\'' . $l_hidden . '\'),hidden:\'' . $l_hidden . '\'});';

                        $l_strOut .= '<a href="javascript:" title="' . _L("LC__REPORT__INFO__SORTING") . '" class="ml5 vam" onclick="' . $l_onclick_sort . ';">' .
                            '<img src="' . $g_dirs["images"] . 'icons/silk/arrow_switch.png" alt=" " title="' . _L('LC__CMDB__OBJECT_BROWSER__SORT_SELECTION') . '" class="vam" />' .
                            '</a>';
                    } // if
                } // if
            }
            else
            {
                $l_strOut = $l_objPlugin->navigation_view($p_tplclass, $this->m_params);
            } // if
        } // if

        return $l_strOut;
    } // function

    /**
     * Method for retrieving a pre-formatted text for the input-elements.
     *
     * @param   integer $p_objid
     *
     * @return  string
     * @author  Dennis Stücken <dstuecken@i-doit.de>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function format_selection($p_objid, $p_unused = false)
    {
        // Very important: We only want integers here!
        $p_objid = (int) $p_objid;

        // If the given object id is empty (0) we return an empty string.
        if ($p_objid == 0)
        {
            return '';
        } // if

        if ($this->m_params[self::C__USE_AUTH] && !isys_auth_cmdb::instance()->is_allowed_to(isys_auth::VIEW, 'OBJ_ID/' . $p_objid))
        {
            return '[' . _L('LC__UNIVERSAL__HIDDEN') . ']';
        } // if

        // Init.
        $l_object_title = '';
        $l_return       = '';

        if (isset($this->m_params[self::C__SECOND_LIST_FORMAT]) && !empty($this->m_params[self::C__SECOND_LIST_FORMAT]))
        {
            $l_callback = $this->m_params[self::C__SECOND_LIST_FORMAT];

            if (is_string($l_callback))
            {
                // We check if we got a valid JSON - But we have to go sure it is an JSON array.
                if (isys_format_json::is_json_array($l_callback))
                {
                    $l_callback = isys_format_json::decode($l_callback, true);
                }
                else
                {
                    $l_callback = explode('::', $this->m_params[self::C__SECOND_LIST_FORMAT]);
                } // if
            } // if

            if (class_exists($l_callback[0]))
            {
                $l_obj = new $l_callback[0](isys_application::instance()->database);

                if (method_exists($l_obj, $l_callback[1]))
                {
                    return call_user_func(
                        [
                            $l_obj,
                            $l_callback[1]
                        ],
                        $p_objid,
                        !$this->m_format_quick_info
                    );
                } // if
            } // if
        }
        else
        {
            if (empty($p_objid))
            {
                return _L('LC__CMDB__BROWSER_OBJECT__NONE_SELECTED');
            } // if

            if (strstr($p_objid, ','))
            {
                $l_obj_ids = explode(',', $p_objid);
            }
            else
            {
                $l_obj_ids = [$p_objid];
            } // if

            // We need a DAO for the object name.
            $l_dao_cmdb   = new isys_cmdb_dao(isys_application::instance()->database);
            $l_quick_info = new isys_ajax_handler_quick_info($_GET, $_POST);

            foreach ($l_obj_ids as $l_obj_id)
            {
                if ($l_obj_id > 0)
                {
                    $l_object_title .= _L(
                            $l_dao_cmdb->get_objtype_name_by_id_as_string($l_dao_cmdb->get_objTypeID($l_obj_id))
                        ) . ' >> ' . $l_dao_cmdb->get_obj_name_by_id_as_string($l_obj_id);

                    if ($this->m_format_quick_info)
                    {
                        $l_return .= $l_quick_info->get_quick_info(
                                $l_obj_id,
                                $l_object_title,
                                C__LINK__OBJECT
                            ) . ', ';
                    }
                    else
                    {
                        $l_return .= str_replace('"', '', $l_object_title) . ', ';
                    } // if
                } // if
            } // foreach

            return substr($l_return, 0, -2);
        } // if

        return '';
    } // function

    /**
     * This is the default entrypoint of the object browser.
     * Every time the browser gui is loaded, this method gets called.
     *
     * @param   isys_module_request $p_modreq
     *
     * @return  isys_component_template|void
     */
    public function &handle_module_request(isys_module_request $p_modreq)
    {
        // Initialization.
        $this->m_template = $p_modreq->get_template();

        // Parameter retrieval.
        $l_params_decoded = base64_decode($_POST["params"]);
        $l_params         = isys_format_json::decode($l_params_decoded, true);

        if (!isset($l_params[self::C__USE_AUTH]))
        {
            // ID-2895 - Only append the auth-condition, if this feature is enabled.
            $l_params[self::C__USE_AUTH] = !!isys_tenantsettings::get('auth.use-in-object-browser', false);
        } // if

        // Parameter validation.
        if (is_array($l_params))
        {
            $this->m_params = $l_params;

            if (strstr($this->m_params["name"], '[') && strstr($this->m_params["name"], ']'))
            {
                $l_tmp    = explode('[', $this->m_params["name"]);
                $l_view   = $l_tmp[0] . '__VIEW[' . implode('[', array_slice($l_tmp, 1));
                $l_hidden = $l_tmp[0] . '__HIDDEN[' . implode('[', array_slice($l_tmp, 1));
                unset($l_tmp);
            }
            else
            {
                $l_view   = $this->m_params["name"] . '__VIEW';
                $l_hidden = $this->m_params["name"] . '__HIDDEN';
            } // if

            if (isset($this->m_params[self::C__OBJECT_BROWSER__TAB]) && count($this->m_params[self::C__OBJECT_BROWSER__TAB]) > 0)
            {
                foreach ($this->m_params[self::C__OBJECT_BROWSER__TAB] AS $l_tab_type => $l_tab_status)
                {
                    $this->m_tabconfig[$l_tab_type]['disabled'] = !$l_tab_status;
                } // foreach
            } // if

            if (class_exists('isys_module_report'))
            {
                if (isset($this->m_params[self::C__REPORT_FILTER]) && !$this->m_params[self::C__REPORT_FILTER])
                {
                    $this->m_tabconfig[self::C__OBJECT_BROWSER__TAB__REPORT]['disabled'] = true;
                } // if

                // For some reasons we do not allow the report view "everywhere".
                if ($this->m_params[self::C__SECOND_LIST] || !empty($this->m_params[self::C__TYPE_FILTER]) || !empty($this->m_params[self::C__GROUP_FILTER]))
                {
                    $this->m_tabconfig[self::C__OBJECT_BROWSER__TAB__REPORT]['disabled'] = true;
                } // if
            }
            else
            {
                $this->m_tabconfig[self::C__OBJECT_BROWSER__TAB__REPORT]['disabled'] = true;
            } // if

            try
            {
                // Assign some smarty configuration variables.
                $this->m_template->assign(self::C__MULTISELECTION, @$l_params[self::C__MULTISELECTION])
                    ->assign(self::C__CALLBACK__ACCEPT, @$l_params[self::C__CALLBACK__ACCEPT])
                    ->assign(self::C__CALLBACK__ABORT, @$l_params[self::C__CALLBACK__ABORT])
                    ->assign(self::C__FORM_SUBMIT, @$l_params[self::C__FORM_SUBMIT])
                    ->assign(self::C__TYPE_FILTER, @$l_params[self::C__TYPE_FILTER])
                    ->assign(self::C__CMDB_FILTER, @$l_params[self::C__CMDB_FILTER])
                    ->assign(self::C__GROUP_FILTER, @$l_params[self::C__GROUP_FILTER])
                    ->assign(self::C__CAT_FILTER, @$l_params[self::C__CAT_FILTER])
                    ->assign(self::C__CHECK_RIGHT, @$l_params[self::C__CHECK_RIGHT]);

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
                        $l_filterObject = new $l_filter[0](isys_application::instance()->database);

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
                                $this->m_template->assign("arCategoryFilter", $l_data);
                                $this->m_template->assign(self::C__CATEGORY_FILTER, $l_params[self::C__CATEGORY_FILTER]);
                            } // if
                        } // if
                    } // if

                    // Disable some tabs.
                    $this->m_tabconfig[self::C__OBJECT_BROWSER__TAB__LOCATION]["disabled"] = true;
                    $this->m_tabconfig[self::C__OBJECT_BROWSER__TAB__SEARCH]["disabled"]   = true;
                } // if

                // Automatically set the return element.
                if ((!isset($l_params[self::C__RETURN_ELEMENT]) || empty($l_params[self::C__RETURN_ELEMENT])) && isset($l_params["name"]))
                {
                    $this->m_template->assign("return_element", $l_hidden);
                    $this->m_template->assign("return_view", $l_view);
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

                // This code will preselect the objects, we selected since the last request (Open browser, select and close. Open browser again).
                if (isset($_GET['live_preselection']))
                {
                    if (count(isys_format_json::decode($_GET['live_preselection'], true)) > 0)
                    {
                        $l_params[self::C__SELECTION] = [$_GET['live_preselection']];
                    }
                    else
                    {
                        $l_params[self::C__SELECTION] = [];
                    }
                } // if

                $this->handle_preselection($l_params[self::C__SELECTION], $l_params[self::C__DATARETRIEVAL]);
                $this->handle_location_tree($l_params[self::C__LOCATION_VIEW__CONTAINERS_ONLY] ?: false);

                // Preparations.
                $this->prepare_smarty_assignments($l_params);
            }
            catch (isys_exception_objectbrowser $e)
            {
                $this->m_template->assign("error", $e->getMessage());
                $this->m_template->assign("errorDetail", $e->getDetailMessage());
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

        // Javascript initialization.
        if (isset($l_params[self::C__SECOND_SELECTION]) && $l_params[self::C__SECOND_SELECTION])
        {
            $l_gets = $p_modreq->get_gets();

            // Disable the search for all second-selection browser.
            $this->m_tabconfig[self::C__OBJECT_BROWSER__TAB__SEARCH]["disabled"] = true;

            // Create the AJAX-string.
            $l_ajaxgets = [
                C__CMDB__GET__POPUP           => $l_gets[C__CMDB__GET__POPUP],
                C__GET__MODULE_ID             => C__MODULE__CMDB,
                C__CMDB__GET__CONNECTION_TYPE => $l_gets[C__CMDB__GET__CONNECTION_TYPE],
                C__CMDB__GET__CATG            => $l_gets[C__CMDB__GET__CATG],
                C__GET__AJAX_REQUEST          => 'handle_ajax_request',
                'request'                     => $l_params[self::C__SECOND_LIST],
            ];

            // Assign the Ajax URL for calling from the template.
            $this->m_template->assign('ajax_url', isys_glob_build_url(isys_glob_http_build_query($l_ajaxgets)));

            // Assign the cable-connection JS (change name to "dual-browser" or something...).
            $this->m_template->assign('js_init', 'popup/cable_connection_ng.js');

            // Enable second selection
            $this->m_template->assign(self::C__SECOND_SELECTION, true);
        }
        else
        {
            // Assign the object-browser JS.
            $this->m_template->assign('js_init', 'popup/object_ng.js');
        } // if

        if ($this->m_tabconfig[self::C__OBJECT_BROWSER__TAB__REPORT]['disabled'] === false)
        {
            $this->m_template->assign('reports', $this->handle_report_request());
        } // if

        $this->m_template
            ->assign("useAuth", (int) $this->m_params[self::C__USE_AUTH])
            ->assign("tabs", $this->m_tabconfig)
            ->display('popup/object_ng.tpl');

        // Die, because this is an ajax request.
        die();
    } // function

    /**
     * Method for finding out which object types are allowed for the given parameters.
     *
     * @param   array $p_params
     *
     * @return  array
     */
    public function get_object_types_by_filter(array $p_params = [])
    {
        // Initialization.
        $l_dao              = new isys_cmdb_dao_nexgen(isys_application::instance()->database);
        $l_typeFilter       = false;
        $l_arAllObjectTypes = [];

        // Check for group filtering.
        if (isset($p_params[self::C__GROUP_FILTER]) && !empty($p_params[self::C__GROUP_FILTER]))
        {
            $l_groupFilter = array_flip(explode(";", $p_params[self::C__GROUP_FILTER]));
        }
        else
        {
            $l_groupFilter = false;
        } // if

        // Check for type filtering.
        if (isset($p_params[self::C__TYPE_FILTER]) && !empty($p_params[self::C__TYPE_FILTER]))
        {
            $l_typeFilter = array_flip(explode(";", $p_params[self::C__TYPE_FILTER]));
        } // if

        if ($l_typeFilter)
        {
            foreach ($l_typeFilter as $l_objtype_const => $l_key)
            {
                $l_arr = $l_dao->get_objecttypes_using_cats($l_objtype_const);
                if ($l_arr)
                {
                    $l_typeFilter = array_merge($l_typeFilter, (array) array_flip($l_arr));
                } // if
            } // foreach
        } // if

        if (isset($p_params[self::C__CAT_FILTER]) && !empty($p_params[self::C__CAT_FILTER]))
        {
            $l_catFilter = explode(";", $p_params[self::C__CAT_FILTER]);
        }
        else
        {
            $l_catFilter = false;
        } // if

        if (isset($p_params[self::C__TYPE_BLACK_LIST]) && !empty($p_params[self::C__TYPE_BLACK_LIST]))
        {
            $l_typeBlacklist = array_flip(explode(";", $p_params[self::C__TYPE_BLACK_LIST]));
        }
        else
        {
            $l_typeBlacklist = false;
        } // if

        // Get objecttype groups.
        $l_objgroups = $l_dao->objgroup_get();

        /**
         * @var $l_cmdb_dao isys_cmdb_dao_object_type
         */
        $l_cmdb_dao = isys_cmdb_dao_object_type::instance(isys_application::instance()->database);

        while ($l_row = $l_objgroups->get_row())
        {
            if (($l_groupFilter && !isset($l_groupFilter[$l_row["isys_obj_type_group__const"]])))
            {
                continue;
            } // if

            // Get object types for current group.
            $l_objtypes = $l_dao->objtype_get_by_objgroup_id($l_row["isys_obj_type_group__id"]);
            while ($l_otrow = $l_objtypes->get_row())
            {
                // Check if we can skip this object type.
                if ($l_typeFilter && !isset($l_typeFilter[$l_otrow["isys_obj_type__const"]]))
                {
                    continue;
                } // if

                if ($l_catFilter && !$l_cmdb_dao->has_cat($l_otrow["isys_obj_type__id"], $l_catFilter))
                {
                    continue;
                } // if

                if ($l_typeBlacklist && isset($l_typeBlacklist[$l_otrow["isys_obj_type__const"]]))
                {
                    continue;
                } // if

                // Get count.
                $l_count = $l_otrow["objcount"];

                $l_arAllObjectTypes[_L($l_row["isys_obj_type_group__title"])][$l_otrow["isys_obj_type__id"]] = _L($l_otrow["isys_obj_type__title"]) . " (" . $l_count . ")";
            } // while
        } // while

        return $l_arAllObjectTypes;
    } // function

    /**
     * Method for adding a object type to the filter dropdown.
     *
     * @param   integer $p_object_type_id
     * @param   string  $p_object_type_name
     * @param   string  $p_object_type_group
     *
     * @return  isys_popup_browser_object_ng
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function add_object_type_filter($p_object_type_id, $p_object_type_name, $p_object_type_group = null)
    {
        if ($p_object_type_group === null)
        {
            $p_object_type_group = _L('LC__CMDB__OBJTYPE_GROUP__OTHER');
        } // if

        if (!isset($this->m_object_types[$p_object_type_group]))
        {
            $this->m_object_types[$p_object_type_group] = [];
        } // if

        $this->m_object_types[$p_object_type_group][$p_object_type_id] = $p_object_type_name;

        return $this;
    } // function

    /**
     * Checks, if a given object type is set in the "$this->m_object_types" array.
     *
     * @param   integer $p_object_type_id
     * @param   string  $p_object_type_group
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function has_object_type_filter($p_object_type_id, $p_object_type_group = null)
    {
        if (!count($this->m_object_types))
        {
            return false;
        } // if

        if ($p_object_type_group !== null)
        {
            return isset($this->m_object_types[$p_object_type_group][$p_object_type_id]);
        }
        else
        {
            foreach ($this->m_object_types as $l_object_types)
            {
                if (isset($l_object_types[$p_object_type_id]))
                {
                    return true;
                } // if
            } // foreach
        } // if

        return false;
    } // function

    /**
     * Destructor.
     */
    public function __destruct()
    {
        unset($this->m_template);
    } // function

    /**
     * Handles the preselection and assigns a selection to smarty. A preselection is assigned in this format:
     *  [ object id, object title, object type, sys-id ]
     * Example:
     *  [ 1 , 'My Server', 'Server', 'SYSID1234567890' ]
     *
     * @param   array $p_preselection
     * @param   array $p_dataretrieval
     *
     * @throws  Exception
     * @throws  isys_exception_objectbrowser
     * @author  Dennis Stücken <dstuecken@i-doit.de>
     * @author  Leonard Fischer <lfischer@synetics.de>
     */
    protected function handle_preselection($p_preselection, $p_dataretrieval = null)
    {
        $l_preselection = $l_otypeCache = [];

        // If there is a certain callback defined via UI or template, use it.
        if (isset($this->m_params[self::C__SECOND_LIST]) && !empty($this->m_params[self::C__SECOND_LIST]) && isset($this->m_params[self::C__SECOND_SELECTION]) && $this->m_params[self::C__SECOND_SELECTION])
        {
            $l_preselection = [];

            // If we get an array, it's most likely something like "array('class::method', array('params'))".
            if (is_array($this->m_params[self::C__SECOND_LIST]))
            {
                // Write the callback-method in the callback variable.
                $l_callback = $this->m_params[self::C__SECOND_LIST][0];

                // Check if we got a second item in the array (We'll assume they are parameters).
                $l_callback_params = (isset($this->m_params[self::C__SECOND_LIST][1])) ? (array) $this->m_params[self::C__SECOND_LIST][1] : [];
            }
            else if (is_string($this->m_params[self::C__SECOND_LIST]))
            {
                // When we only get a string, we can just write it inside the callback variable.
                $l_callback = $this->m_params[self::C__SECOND_LIST];

                // And assign an empty array as parameters.
                $l_callback_params = [];
            }
            else
            {
                throw new isys_exception_objectbrowser(
                    'Wrong parameter.',
                    'A wrong parameter has been assigned to isys_popup_browser_object_ng::C__SECOND_LIST. The syntax should be: array("class::method", array("params") or "class::method".'
                );
            } // if

            // Get an array with the class and method name separated.
            $l_callback = explode('::', $l_callback);

            if (is_array($p_preselection))
            {
                $p_preselection = $p_preselection[0];
            } // if

            // Assign the preselection to our parameter-array.
            if (empty($l_callback_params['preselection']))
            {
                $l_callback_params['preselection'] = $p_preselection;
            } // if

            // We won't blindly instancinate the class and call the method - we check.
            if (class_exists($l_callback[0]))
            {
                $l_obj = new $l_callback[0](isys_application::instance()->database);

                if (method_exists($l_obj, $l_callback[1]))
                {
                    // Call the callback-method with our parameters.
                    $l_preselection = call_user_func(
                        [
                            $l_obj,
                            $l_callback[1]
                        ],
                        self::C__CALL_CONTEXT__PREPARATION,
                        $l_callback_params
                    );
                } // if
            } // if

            // Assign the preselection-variables.
            $this->m_template->assign('preselection', isys_format_json::encode($l_preselection['first']))
                ->assign('second_preselection', isys_format_json::encode($l_preselection['second']))
                ->assign('category_preselection', (int) $l_preselection['category']);
        }
        else
        {
            // Dirty hotfix for JSON inside an array (why?!) detection.
            if (is_array($p_preselection) && count($p_preselection) == 1)
            {
                if (is_string($p_preselection[0]) && isys_format_json::is_json($p_preselection[0]))
                {
                    $p_preselection = isys_format_json::decode($p_preselection[0], true);
                }
                else if (is_string($p_preselection[0]) && strstr($p_preselection[0], ','))
                {
                    $p_preselection = explode(',', $p_preselection[0]);
                }
                else if (is_array($p_preselection[0]))
                {
                    $p_preselection = $p_preselection[0];
                } // if

            } // if

            // If preselection should be retrieved via dataretrieval.
            if (!is_null($p_dataretrieval))
            {
                // Feature - If we can't assign an array (because smarty can't handle arrays) we can use a JSON string instead.
                if (is_array($p_dataretrieval) && count($p_dataretrieval) > 1)
                {
                    // Try to retrieve the preselection via callback function.
                    list($l_callback, $l_parameter, $l_keys) = $p_dataretrieval;
                    list($l_class, $l_method) = $l_callback;
                }
                else if (is_string($p_dataretrieval) && isys_format_json::is_json_array($p_dataretrieval))
                {
                    list($l_callback, $l_parameter, $l_keys) = isys_format_json::decode($p_dataretrieval, true);

                    // We might already got an array or have string like "class::method".
                    list($l_class, $l_method) = is_array($l_callback) ? $l_callback : explode('::', $l_callback);
                }
                else
                {
                    throw new Exception("Dataretrieval is empty");
                }// if

                if (!isset($l_keys))
                {
                    $l_keys = [
                        "isys_obj__id",
                        "isys_obj__title",
                        "isys_obj_type__title",
                        "isys_obj__sysid"
                    ];
                }

                if (isset($l_class) && class_exists($l_class))
                {
                    $l_class = new $l_class(isys_application::instance()->database);

                    if (method_exists($l_class, $l_method) && ($l_selection = $l_class->$l_method($l_parameter)))
                    {

                        if (is_object($l_selection) && is_a($l_selection, "isys_component_dao_result"))
                        {
                            // .. and iterate through the object related resultset.
                            while ($l_row = $l_selection->get_row())
                            {

                                // Go further if there is an object id only.
                                if ($l_row[$l_keys[0]] > 0)
                                {
                                    // Try to get an object type title if it is numeric.
                                    if (is_numeric($l_row[$l_keys[2]]))
                                    {
                                        // Build up an object type cache for faster access.
                                        if (!array_key_exists($l_row[$l_keys[2]], $l_otypeCache) && method_exists($l_class, "get_objtype_name_by_id_as_string"))
                                        {
                                            $l_otypeCache[$l_row[$l_keys[2]]] = $l_class->get_objtype_name_by_id_as_string($l_row[$l_keys[2]]);
                                        } // if

                                        // We might have the object type title now!
                                        $l_row[$l_keys[2]] = $l_otypeCache[$l_row[$l_keys[2]]];
                                    } // if

                                    if ($this->m_params[self::C__USE_AUTH] && !isys_auth_cmdb::instance()->is_allowed_to(isys_auth::VIEW, 'OBJ_ID/' . $l_row[$l_keys[0]]))
                                    {
                                        $l_preselection[] = [
                                            $l_row[$l_keys[0]],
                                            '[' . _L('LC__UNIVERSAL__HIDDEN') . ']',
                                            '[' . _L('LC__UNIVERSAL__HIDDEN') . ']',
                                            $l_row[$l_keys[3]]
                                        ];
                                    }
                                    else
                                    {
                                        $l_preselection[] = [
                                            $l_row[$l_keys[0]],
                                            isys_glob_htmlentities($l_row[$l_keys[1]]),
                                            isys_glob_htmlentities(_L($l_row[$l_keys[2]])),
                                            $l_row[$l_keys[3]]
                                        ];
                                    } // if
                                } // if
                            } // while
                        }
                        else throw new isys_exception_objectbrowser(
                            "Dataretrieval failed.",
                            get_class($l_class) . "::" . $l_method . " does not return an object of type isys_component_dao_result.\n\n" . "Return value is: " . var_export(
                                $l_selection,
                                true
                            )
                        );

                    }
                    else throw new isys_exception_objectbrowser("Dataretrieval failed.", $l_method . " does not exist in " . get_class($l_class));

                }
                else
                {
                    throw new Exception($l_class . " does not exist.");
                } // if

                // Ok then try to retrieve a preselection via selected object-ids.
            }
            else
            {
                // Format preselection.
                if (is_array($p_preselection) && count($p_preselection) > 0)
                {
                    // Build the SQL condition for filtering the selected objects.
                    $l_condition    = "AND isys_obj__id IN (" . implode(",", array_map('intval', $p_preselection)) . ")";
                    $l_preselection = [];

                    // Use global dao for retrieval.
                    $l_dao     = new isys_cmdb_dao_category_g_global(isys_application::instance()->database);
                    $l_objects = $l_dao->get_data(null, null, $l_condition);

                    // Iterate through preselection.
                    while ($l_row = $l_objects->get_row())
                    {
                        if ($this->m_params[self::C__USE_AUTH] && !isys_auth_cmdb::instance()->is_allowed_to(isys_auth::VIEW, 'OBJ_ID/' . $l_row["isys_obj__id"]))
                        {
                            $l_preselection[] = [
                                $l_row["isys_obj__id"],
                                '[' . _L('LC__UNIVERSAL__HIDDEN') . ']',
                                '[' . _L('LC__UNIVERSAL__HIDDEN') . ']',
                                $l_row["isys_obj__sysid"]
                            ];
                        }
                        else
                        {
                            $l_preselection[] = [
                                $l_row["isys_obj__id"],
                                isys_glob_htmlentities($l_row["isys_obj__title"]),
                                isys_glob_htmlentities(_L($l_dao->get_objtype_name_by_id_as_string($l_row["isys_obj__isys_obj_type__id"]))),
                                $l_row["isys_obj__sysid"]
                            ];
                        } // if
                    } // while
                }
                else if (is_numeric($p_preselection))
                {
                    // Build the SQL condition for filtering the selected objects.
                    $l_preselection = [];

                    // Use global dao for retrieval.
                    $l_dao     = new isys_cmdb_dao_category_g_global(isys_application::instance()->database);
                    $l_objects = $l_dao->get_data(null, $p_preselection);

                    // Iterate through preselection.
                    while ($l_row = $l_objects->get_row())
                    {
                        // The the selection grow.
                        if ($this->m_params[self::C__USE_AUTH] && !isys_auth_cmdb::instance()->is_allowed_to(isys_auth::VIEW, 'OBJ_ID/' . $l_row["isys_obj__id"]))
                        {
                            $l_preselection[] = [
                                $l_row["isys_obj__id"],
                                '[' . _L('LC__UNIVERSAL__HIDDEN') . ']',
                                '[' . _L('LC__UNIVERSAL__HIDDEN') . ']',
                                $l_row["isys_obj__sysid"]
                            ];
                        }
                        else
                        {
                            $l_preselection[] = [
                                $l_row["isys_obj__id"],
                                isys_glob_htmlentities($l_row["isys_obj__title"]),
                                isys_glob_htmlentities(_L($l_dao->get_objtype_name_by_id_as_string($l_row["isys_obj__isys_obj_type__id"]))),
                                $l_row["isys_obj__sysid"]
                            ];
                        } // if
                    } // while
                } // if
            } // if

            // Populate preselection in smarty object.
            $this->m_template->assign("preselection", isys_glob_prepare_string(isys_format_json::encode($l_preselection)));
        } // if
    } // function

    /**
     * Prepares all smarty assignments used by the object browser.
     *
     * @param  array $p_params
     */
    protected function prepare_smarty_assignments($p_params = [])
    {
        // Initialization.
        $l_dao              = new isys_cmdb_dao_nexgen(isys_application::instance()->database);
        $l_typeFilter       = false;
        $l_arAllObjectTypes = [];
        $l_authCondition    = $this->m_params[self::C__USE_AUTH] ? isys_auth_cmdb_objects::instance()->get_allowed_objects_condition() : '';

        // Check for group filtering.
        if (isset($p_params[self::C__GROUP_FILTER]) && !empty($p_params[self::C__GROUP_FILTER]))
        {
            $l_groupFilter = array_flip(explode(";", $p_params[self::C__GROUP_FILTER]));
        }
        else
        {
            $l_groupFilter = false;
        } // if

        // Check for type filtering.
        if (isset($p_params[self::C__TYPE_FILTER]) && !empty($p_params[self::C__TYPE_FILTER]))
        {
            $l_typeFilter = array_flip(explode(";", $p_params[self::C__TYPE_FILTER]));
        } // if

        if ($l_typeFilter)
        {
            foreach ($l_typeFilter AS $l_objtype_const => $l_key)
            {
                $l_arr = $l_dao->get_objecttypes_using_cats($l_objtype_const);
                if ($l_arr) $l_typeFilter = array_merge($l_typeFilter, (array) array_flip($l_arr));
            } // foreach
        } // if

        if (isset($p_params[self::C__CAT_FILTER]) && !empty($p_params[self::C__CAT_FILTER]))
        {
            $l_catFilter = explode(";", $p_params[self::C__CAT_FILTER]);
        }
        else
        {
            $l_catFilter = false;
        } // if

        if (isset($p_params[self::C__TYPE_BLACK_LIST]) && !empty($p_params[self::C__TYPE_BLACK_LIST]))
        {
            $l_typeBlacklist = array_flip(explode(";", $p_params[self::C__TYPE_BLACK_LIST]));
        }
        else
        {
            $l_typeBlacklist = false;
        } // if

        // Get objecttype groups.
        $l_objgroups = $l_dao->objgroup_get();

        /**
         * @var $l_cmdb_dao isys_cmdb_dao_object_type
         */
        $l_cmdb_dao = isys_cmdb_dao_object_type::instance(isys_application::instance()->database);

        while ($l_row = $l_objgroups->get_row())
        {
            if (($l_groupFilter && !isset($l_groupFilter[$l_row["isys_obj_type_group__const"]])))
            {
                continue;
            } // if

            // ID-2895 Get allowed object types for current group.
            $l_objtypes = $l_dao->objtype_get_by_objgroup_id($l_row["isys_obj_type_group__id"], $this->m_params[self::C__USE_AUTH]);

            while ($l_otrow = $l_objtypes->get_row())
            {
                // Check if we can skip this object type.
                if ($l_typeFilter && !isset($l_typeFilter[$l_otrow["isys_obj_type__const"]]))
                {
                    continue;
                } // if

                if ($l_catFilter && !$l_cmdb_dao->has_cat($l_otrow["isys_obj_type__id"], $l_catFilter))
                {
                    continue;
                } // if

                if ($l_typeBlacklist && isset($l_typeBlacklist[$l_otrow["isys_obj_type__const"]]))
                {
                    continue;
                } // if

                // Get count.
                $l_count = $l_otrow["objcount"];

                // Show only object types with objects inside.
                if (($l_count > 0 || $l_typeFilter) && $l_otrow["isys_obj_type__show_in_tree"] == "1")
                {
                    // Add object type to array.
                    $this->m_object_types[_L($l_row["isys_obj_type_group__title"])][$l_otrow["isys_obj_type__id"]] = _L(
                            $l_otrow["isys_obj_type__title"]
                        ) . " (" . $l_count . ")";
                } // if

                $l_arAllObjectTypes[_L($l_row["isys_obj_type_group__title"])][$l_otrow["isys_obj_type__id"]] = _L($l_otrow["isys_obj_type__title"]) . " (" . $l_count . ")";
            } // while
        } // while

        // Only if there are no results in all objecttypes set the variable to suppress the exception
        if (count($this->m_object_types) == 0)
        {
            $this->m_object_types = $l_arAllObjectTypes;
        } // if

        // If only a couple of object types should be selected, skip other object filtering.
        if (!$l_groupFilter && !$l_typeFilter)
        {
            // Get object groups.
            $l_objgroups = $l_dao->get_objects_by_cats_id(C__CATS__GROUP, C__RECORD_STATUS__NORMAL, null, null, null, $l_authCondition);

            while ($l_row = $l_objgroups->get_row())
            {
                $l_arObjectGroups[$l_row["isys_obj__id"]] = $l_row["isys_obj__title"];
            } // while

            // Get person groups.
            $l_dao          = new isys_cmdb_dao_category_s_person_group_master(isys_application::instance()->database);
            $l_persongroups = $l_dao->get_data(null, null, $l_authCondition, null, C__RECORD_STATUS__NORMAL);

            while ($l_row = $l_persongroups->get_row())
            {
                $l_arPersonGroups[$l_row["isys_obj__id"]] = $l_row["isys_obj__title"];
            } // while
        } // if

        // Get relation types.
        if ($l_typeFilter["C__OBJTYPE__RELATION"] || (!$l_typeFilter && !$l_groupFilter))
        {
            // Check if a relation filter should be applied.
            if (isset($p_params[self::C__RELATION_FILTER]))
            {
                if (strstr($p_params[self::C__RELATION_FILTER], ";"))
                {
                    $l_relationFilter = explode(";", $p_params[self::C__RELATION_FILTER]);
                }
                else
                {
                    $l_relationFilter[] = $p_params[self::C__RELATION_FILTER];
                } // if
            }
            else
            {
                $l_relationFilter = null;
            } // if

            // Get Relations.
            $l_dao_relation = new isys_cmdb_dao_category_g_relation(isys_application::instance()->database);
            $this->m_template->assign("arRelationTypes", $l_dao_relation->get_relation_types_as_array($l_relationFilter));
        } // if

        // Disable location view if typefilter is active, because we can't really also filter the location tree.
        if ($l_typeFilter || $l_groupFilter)
        {
            $this->m_tabconfig[self::C__OBJECT_BROWSER__TAB__LOCATION]["disabled"] = true;

            if (!isset($this->m_object_types))
            {

                if (count($l_typeFilter) > 0)
                {
                    $l_object_types = [];
                    $l_res          = $l_dao->get_object_types(array_keys($l_typeFilter));

                    if (count($l_res) > 0)
                    {
                        while ($l_row = $l_res->get_row())
                        {
                            $l_object_types[] = _L($l_row['isys_obj_type__title']);
                        } // while
                    } // if

                    throw new isys_exception_objectbrowser(
                        _L('LC__CMDB__OBJECT_BROWSER__EXCEPTION__NO_OBJECT_TYPES_FOUND_BY_TYPEFILTER', implode(', ', $l_object_types)),
                        "<strong>parameters</strong> - " . var_export($p_params, true) . "\n\n" . "<strong>parsed catFilter</strong> - " . var_export(
                            $l_catFilter,
                            true
                        ) . "\n\n" . "<strong>parsed typeFilter</strong> - " . var_export(
                            $l_typeFilter,
                            true
                        ) . "\n\n" . "<strong>parsed groupFilter</strong> - " . var_export($l_groupFilter, true)
                    );
                } // if

                if (count($l_groupFilter) > 0)
                {
                    $l_groups = [];
                    $l_res    = $l_dao->get_object_group_by_id(array_keys($l_typeFilter));

                    if (count($l_res) > 0)
                    {
                        while ($l_row = $l_res->get_row())
                        {
                            $l_groups[] = _L($l_row['isys_obj_type_group__title']);
                        } // while
                    } // if

                    throw new isys_exception_objectbrowser(
                        _L('LC__CMDB__OBJECT_BROWSER__EXCEPTION__NO_OBJECT_TYPES_FOUND_BY_GROUPFILTER', implode(', ', $l_groups)),
                        "<strong>parameters</strong> - " . var_export($p_params, true) . "\n\n" . "<strong>parsed catFilter</strong> - " . var_export(
                            $l_catFilter,
                            true
                        ) . "\n\n" . "<strong>parsed typeFilter</strong> - " . var_export(
                            $l_typeFilter,
                            true
                        ) . "\n\n" . "<strong>parsed groupFilter</strong> - " . var_export($l_groupFilter, true)
                    );
                } // if

                throw new isys_exception_objectbrowser(
                    _L('LC__CMDB__OBJECT_BROWSER__EXCEPTION__NO_OBJECT_TYPES_FOUND'),
                    "<strong>parameters</strong> - " . var_export($p_params, true) . "\n\n" . "<strong>parsed catFilter</strong> - " . var_export(
                        $l_catFilter,
                        true
                    ) . "\n\n" . "<strong>parsed typeFilter</strong> - " . var_export($l_typeFilter, true) . "\n\n" . "<strong>parsed groupFilter</strong> - " . var_export(
                        $l_groupFilter,
                        true
                    )
                );
            } // if
        } // if

        // Emitting a signal to alternate the filters.
        isys_component_signalcollection::get_instance()
            ->emit('mod.cmdb.beforeObjectBrowserTypeAssignment', $this, $p_params, $this->m_object_types);

        // Assign object types if not null.
        if (isset($this->m_object_types))
        {
            // We probably need to filter the object-types, when we got an CMDB-filter.
            if (!empty($this->m_params[self::C__CMDB_FILTER]))
            {
                $l_status_array = [];
                $l_cmdb_status  = explode(';', $this->m_params[self::C__CMDB_FILTER]);

                foreach ($l_cmdb_status as $l_status)
                {
                    if (defined($l_status))
                    {
                        $l_status_array[] = constant($l_status);
                    } // if
                } // foreach

                // Iterate it as array, so we can remove certain items later on...
                foreach ($this->m_object_types as $l_type_key => $l_obj_types)
                {
                    foreach ($l_obj_types as $l_id => $l_title)
                    {
                        if (count($l_status_array) > 0)
                        {
                            $l_sql = 'SELECT COUNT(*) AS cnt FROM isys_obj
								WHERE isys_obj__isys_obj_type__id = ' . $l_dao->convert_sql_id($l_id) . '
								AND isys_obj__isys_cmdb_status__id IN (' . implode(',', $l_status_array) . ')
								LIMIT 1;';

                            $l_cnt = $l_dao->retrieve($l_sql)
                                ->get_row_value('cnt');

                            if ((int) $l_cnt == 0)
                            {
                                unset($this->m_object_types[$l_type_key][$l_id]);
                            }
                            else
                            {
                                // This is not the best solution, but it does the job!
                                $this->m_object_types[$l_type_key][$l_id] = preg_replace('~\(\d+\)~', '(' . $l_row['cnt'] . ')', $l_title);
                            } // if
                        } // if
                    } // foreach
                } // foreach
            } // if

            $this->m_template
                ->assign("arObjectTypes", $this->m_object_types)
                ->assign("arAllObjectTypes", $l_arAllObjectTypes);
        } // if

        // Assign person groups if not null.
        if (isset($l_arPersonGroups))
        {
            $this->m_template->assign("arPersonGroups", $l_arPersonGroups);
        } // if

        // Assign object groups if not null.
        if (isset($l_arObjectGroups))
        {
            $this->m_template->assign("arGroups", $l_arObjectGroups);
        } // if

        // Show debug information if wanted.
        if (isset($p_params["debug"]) && $p_params["debug"])
        {
            throw new isys_exception_objectbrowser(
                "Debug mode enabled. See details below:",
                "<strong>parameters:</strong> " . var_export($p_params, true) . "\n\n" .
                "<strong>found object types:</strong> " . var_export($this->m_object_types, true) . "\n\n" .
                "<strong>found object groups:</strong> " . var_export($l_arObjectGroups, true) . "\n\n" .
                "<strong>found person groups:</strong> " . var_export($l_arPersonGroups, true) . "\n\n" .
                "------------\n\n" .
                "<strong>parsed typeFilter:</strong> " . var_export($l_typeFilter, true) . "\n\n" .
                "<strong>parsed groupFilter:</strong> " . var_export($l_groupFilter, true)
            );
        }

        // Deallocate.
        unset($l_objgroups, $l_arObjectGroups, $l_persongroups, $l_arPersonGroups);
    } // function

    /**
     * Intializes the location tree and assigns it.
     *
     * @params  boolean  $p_only_containers
     */
    private function handle_location_tree($p_only_containers)
    {
        global $g_dirs;

        // Prepare tree.
        $l_tree = new isys_component_ajaxtree(
            'locationBrowser',
            'index.php?call=tree_level&ajax=1&id={0}&selectCallback=browserPreselection.addObject&containersOnly=' . (int) $p_only_containers,
            $g_dirs['images'] . 'icons/silk/house.png',
            _L('LC__CMDB__TREE__LOCATION'),
            ''
        );

        // Assign the template variables.
        $this->m_template
            ->assign('ajaxUrl', 'index.php?call=tree_level&ajax=1&get_obj_name=1&id=')
            ->assign('locationBrowser', $l_tree->process());
    } // function
} // class