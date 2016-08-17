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
 * Popup class for location browser.
 *
 * @package     i-doit
 * @subpackage  Popups
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_popup_browser_location extends isys_popup_browser
{
    use \idoit\Component\Provider\Singleton;

    /**
     * Return text instead of a href links in format_selection
     *
     * @var bool
     */
    public $m_format_as_text = false;
    /**
     * Exclude current object in format_selection
     *
     * @var bool
     */
    public $m_format_exclude_self = false;

    /**
     * Cut object name at 100 characters in format_selection
     *
     * @var int
     */
    public $m_format_object_name_cut = 100;

    /**
     * Cut the complete string in format_selection. 0 for disabling
     *
     * @var int
     */
    public $m_format_str_cut = 0;

    /**
     * Prefix for format_selection
     *
     * @var string
     */
    public $m_format_prefix = '';

    /**
     * @param bool|false $bool
     *
     * @inherit
     * @return $this
     */
    public function set_format_str_cut($length = 0)
    {
        $this->m_format_str_cut = $length;

        return $this;
    }

    /**
     * @param bool|false $bool
     *
     * @inherit
     * @return $this
     */
    public function set_format_as_text($bool = false)
    {
        $this->m_format_as_text = $bool;

        return $this;
    }

    /**
     * @param int $cut
     *
     * @inherit
     * @return $this
     */
    public function set_format_object_name_cut($cut = 100)
    {
        $this->m_format_object_name_cut = $cut;

        return $this;
    }

    /**
     * @param bool|false $bool
     *
     * @inherit
     * @return $this
     */
    public function set_format_exclude_self($bool = false)
    {
        $this->m_format_exclude_self = $bool;

        return $this;
    }

    /**
     * @param string $prefix
     *
     * @return $this
     */
    public function set_format_prefix($prefix)
    {
        $this->m_format_prefix = $prefix;

        return $this;
    }

    /**
     * Handles SMARTY request for location browser.
     *
     * @param   isys_component_template  & $p_tplclass
     * @param                            $p_params
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function handle_smarty_include(isys_component_template &$p_tplclass, $p_params)
    {
        global $g_dirs, $g_comp_database, $g_comp_template_language_manager;

        $l_strOut = '';

        if (empty($p_params['name']))
        {
            return $l_strOut;
        } // if

        // If no origin object is selected, select the root node.
        if (empty($p_params['p_intOriginObjID']))
        {
            $l_dao_loc                    = new isys_cmdb_dao_location($g_comp_database);
            $p_params['p_intOriginObjID'] = $l_dao_loc->get_root_location_as_integer();
        } // if

        $l_objPlugin = new isys_smarty_plugin_f_text();

        if (strstr($p_params["name"], '[') && strstr($p_params["name"], ']'))
        {
            $l_tmp    = explode('[', $p_params["name"]);
            $l_view   = $l_tmp[0] . '__VIEW[' . implode('[', array_slice($l_tmp, 1));
            $l_hidden = $l_tmp[0] . '__HIDDEN[' . implode('[', array_slice($l_tmp, 1));
            unset($l_tmp);
        }
        else
        {
            $l_view   = $p_params["name"] . '__VIEW';
            $l_hidden = $p_params["name"] . '__HIDDEN';
        }

        // Extract object id from either p_strValue or p_strSelectedID.
        if ($p_params['p_strValue'])
        {
            $l_object_id = (int) $p_params['p_strValue'];
        }
        else if ($p_params['p_strSelectedID'])
        {
            $l_object_id = (int) $p_params['p_strSelectedID'];
        }
        else
        {
            $l_object_id = 0;
        } // if

        $l_editmode = (isys_glob_is_edit_mode() || isset($p_params['edit'])) && !isset($p_params['plain']);

        // We got a preselection.
        if ($l_object_id > 0)
        {
            // We are in edit mode, don't display any tags inside the input.
            if ($l_editmode)
            {
                $this->set_format_as_text(true)
                    ->set_format_exclude_self(false)
                    ->set_format_object_name_cut(0)
                    ->set_format_str_cut(0);

                $p_params['p_strValue'] = $this->format_selection($l_object_id);
            }
            else
            {
                $p_params['p_strValue'] = $this->format_selection($l_object_id);
            } // if
        }

        // Prepare a few parameters.
        $p_params['mod']          = 'cmdb';
        $p_params['popup']        = 'browser_location';
        $p_params['currentObjID'] = $_GET["objID"];
        $p_params['resultField']  = $p_params["name"];
        $p_params['selID']        = $p_params["p_strValue"];
        $p_params['originObjID']  = $p_params["p_intOriginObjID"];
        $p_params['p_additional'] .= ' data-hidden-field="' . str_replace('"', '\'', $l_hidden) . '"';

        // Hidden field, in which the selected value is put.
        $l_strHiddenField = '<input name="' . $l_hidden . '" id="' . $l_hidden . '" type="hidden" value="' . $l_object_id . '" />';

        // Set parameters for the f_text plug-in.
        $p_params['p_strID'] = $l_view;

        // Check if we are in edit-mode before displaying the input fields.
        if ($l_editmode)
        {
            // Auto Suggesstion.
            $p_params["p_onClick"]          = "if (this.value == '" . $p_params["p_strValue"] . "') this.value = '';";
            $p_params["p_strSuggest"]       = "location";
            $p_params["p_strSuggestView"]   = $l_view;
            $p_params["p_strSuggestHidden"] = $l_hidden;

            if (isset($p_params[isys_popup_browser_object_ng::C__CALLBACK__ACCEPT]))
            {
                $p_params["p_strSuggestParameters"] = "parameters: {}, " . "selectCallback: function() {" . $p_params[isys_popup_browser_object_ng::C__CALLBACK__ACCEPT] . "}";
            } // if

            $l_strOut .= $l_objPlugin->navigation_edit($p_tplclass, $p_params) . '<a href="javascript:void(0);" title="' . _L(
                    "LC__UNIVERSAL__ATTACH"
                ) . '" class="ml5 attach" onClick="' . $this->process_overlay(
                    '',
                    1100,
                    360,
                    $p_params
                ) . ';" >' . '<img src="' . $g_dirs['images'] . 'icons/silk/zoom.png" class="vam" alt="' . _L('LC__UNIVERSAL__ATTACH') . '" />' . '</a>';

            $l_detach_callback = isset($p_params[isys_popup_browser_object_ng::C__CALLBACK__DETACH]) ? $p_params[isys_popup_browser_object_ng::C__CALLBACK__DETACH] : "";

            // OnClick Handler for detaching the object.
            $l_onclick_detach = "var e_view = document.getElementsByName('" . $p_params["name"] . "')[0], " . "e_hidden = document.getElementsByName('" . $l_hidden . "')[0];" . "if(e_view && e_hidden) {" . "e_view.value = '" . $g_comp_template_language_manager->get(
                    'LC__UNIVERSAL__CONNECTION_DETACHED'
                ) . "!'; " . "e_hidden.value = '0';}" . $l_detach_callback;

            $l_strOut .= '<a href="javascript:void(0);" title="' . _L(
                    "LC__UNIVERSAL__DETACH"
                ) . '" class="ml5 detach" onClick="' . $l_onclick_detach . ';">' . '<img src="' . $g_dirs["images"] . 'icons/silk/detach.png" class="vam" alt="' . _L(
                    'LC__UNIVERSAL__DETACH'
                ) . '" />' . '</a>' . $l_strHiddenField;
        }
        else
        {
            $l_strOut .= $l_objPlugin->navigation_view($p_tplclass, $p_params) . $l_strHiddenField;
        } // if

        return $l_strOut;
    } // function

    /**
     * Formats a location string according to the specified enclosure ID.
     *
     * @param   integer $p_obj_id
     *
     * @return  string
     * @author  Dennis Stücken <dstuecken@i-doit.de>
     */
    public function format_selection($p_obj_id, $p_unused = false)
    {
        $l_cut        = null;
        $l_out        = [];
        $l_quick_info = new isys_ajax_handler_quick_info();
        $l_dao        = new isys_cmdb_dao_category_g_location(isys_application::instance()->database);

        $l_separator = isys_tenantsettings::get('gui.separator.location', ' > ');

        if ($p_obj_id == C__OBJ__ROOT_LOCATION)
        {
            return $l_dao->get_obj_name_by_id_as_string($p_obj_id);
        } // if

        // Get location tree.
        try
        {
            $l_locationpath = $l_dao->get_location_path($p_obj_id);
        }
        catch (RuntimeException $e)
        {
            return $e->getMessage();
        } // try

        $l_locationpath = array_reverse($l_locationpath);
        $i              = 0;
        $l_length       = 0;

        // Parse location tree.
        foreach ($l_locationpath as $l_object_id)
        {
            if ($l_object_id > C__OBJ__ROOT_LOCATION && $l_object_id != $p_obj_id)
            {
                if (is_null($l_cut))
                {
                    $i++;
                } // if

                $l_object_title = $l_dao->get_cached_locations($l_object_id)['title'];

                if (!$this->m_format_as_text)
                {
                    $l_out[] = $l_quick_info->get_quick_info(
                        $l_object_id,
                        $l_object_title,
                        C__LINK__OBJECT,
                        $this->m_format_object_name_cut
                    );
                }
                else
                {
                    $l_out[] = $l_object_title;
                }

                $l_length += strlen($l_object_title);

                if ($l_length >= $this->m_format_str_cut && is_null($l_cut))
                {
                    $l_cut = $i;
                } // if
            } // if
        } // foreach

        if ($p_obj_id > C__OBJ__ROOT_LOCATION)
        {
            if (!$this->m_format_exclude_self)
            {
                if (!$this->m_format_as_text)
                {
                    $l_out[] = $l_quick_info->get_quick_info(
                        $p_obj_id,
                        $l_dao->get_obj_name_by_id_as_string($p_obj_id),
                        C__LINK__OBJECT,
                        $this->m_format_object_name_cut
                    );
                }
                else
                {
                    $l_out[] = $l_dao->get_obj_name_by_id_as_string($p_obj_id);
                }
            } // if
        }
        else
        {
            if (!$this->m_format_exclude_self)
            {
                $l_out[] = $l_dao->get_obj_name_by_id_as_string($p_obj_id);
            } // if
        } // if

        $l_tmp = $l_out;
        $l_out = implode($l_separator, $l_out);

        if ($this->m_format_str_cut)
        {
            if (strlen(strip_tags($l_out)) >= $this->m_format_str_cut)
            {
                if (!is_null($l_cut))
                {
                    if (count($l_tmp) >= $l_cut)
                    {
                        $l_out_stripped = rtrim(strip_tags(preg_replace("(<script[^>]*>([\\S\\s]*?)<\/script>)", '', $l_out)), $l_separator);

                        $l_out = '<acronym title="' . $l_out_stripped . '">..</acronym> ' . $l_separator;

                        for ($i = intval(($l_cut / 2));$i < count($l_tmp);$i++)
                        {
                            if (isset($l_tmp[$i]) && !empty($l_tmp[$i]))
                            {
                                $l_out .= $l_tmp[$i];

                                if (isset($l_tmp[$i + 1]))
                                {
                                    $l_out .= $l_separator;
                                }
                            } // if
                        } // for
                    } // if
                }
            } // if
        } // if

        if ($l_out && $this->m_format_prefix)
        {
            return $this->m_format_prefix . $l_out;
        }

        return $l_out;
    } // function

    /**
     * Handle the popup window and its content.
     *
     * @param   isys_module_request $p_modreq
     *
     * @return  isys_component_template
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function &handle_module_request(isys_module_request $p_modreq)
    {
        global $g_comp_database;

        // Create special location DAO.
        $l_dao_loc = new isys_cmdb_dao_location($g_comp_database);

        // Get our parameters.
        $l_params = base64_decode($_POST['params']);

        $l_params = isys_format_json::decode($l_params, true);

        // This is new, to configure if only "container" objects shall be displayed
        $l_container = (int) $l_params['containers_only'];

        // Set ajax url for tree.
        $l_str_ajaxurl = "index.php?call=tree_level&ajax=1&id={0}&containersOnly=" . $l_container . "&selectCallback=window.select";

        // Retrieve currently selected obj-ID.
        $l_objid = $l_params['currentObjID'];

        if (!empty($l_objid))
        {
            // The get_node_hierarchy returns a comma-separated list including the object itself (for example a server).
            $l_hierarchy_list = explode(',', $l_dao_loc->get_node_hierarchy($l_objid));
            array_shift($l_hierarchy_list);

            // Because we don't want the server object as hierarchy-path, we shifted it out.
            $l_hierarchy = implode(',', $l_hierarchy_list);
        }
        else
        {
            $l_hierarchy = '';
        } // if

        // Prepare tree.
        $l_tree = new isys_component_ajaxtree("g_browser", $l_str_ajaxurl, null, null, $l_hierarchy);

        $l_selection = _L('LC__UNIVERSAL__NOT_SELECTED');

        if (isset($l_params["selID"]))
        {
            $l_selection = $l_params["selID"];
        } // if

        // Assign everything.
        $p_modreq->get_template()
            ->assign('selFull', $l_selection)
            ->assign('callback_accept', $l_params['callback_accept'] . ';')
            ->assign("browser", $l_tree->process())
            ->assign('return_hidden', $l_params['p_strSuggestHidden'])
            ->assign('return_view', $l_params['p_strSuggestView'])
            ->assign('selNoSelection', _L('LC__UNIVERSAL__NOT_SELECTED'))
            ->display('popup/location_ajax.tpl');
        die();
    } // function

    /**
     * isys_popup_browser_location constructor.
     */
    public function __construct()
    {
        $this->m_format_str_cut         = isys_tenantsettings::get('maxlength.location.path', 0);
        $this->m_format_object_name_cut = isys_tenantsettings::get('maxlength.location.objects', 16);

        parent::__construct();
    } // function
} // class