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
 *  CCCCC    MM  MM   DDDDD   BBBBB
 * CC   CC  MM MM MM  DD  DD  BB  BB
 * CC       MM MM MM  DD  DD  BBBBB
 * CC   CC  MM MM MM  DD  DD  BB  BB
 *  CCCCC   MM    MM  DDDDD   BBBBB
 * ------------------------------
 *     M   O   D   U   L   E
 * ------------------------------
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Andre Woesten
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_module_cmdb extends isys_module implements isys_module_interface, isys_module_authable, isys_module_hookable
{
    const DISPLAY_IN_MAIN_MENU = false;

    // Define, if this module shall be displayed in the named menus.
    const DISPLAY_IN_SYSTEM_MENU = true;
    /**
     * @var bool
     */
    protected static $m_licenced = true;
    /**
     * For breadcrumb navigation: General CMDB-Access DAO Low-Level API
     *
     * @var isys_cmdb_dao
     */
    private $m_dao_cmdb;
    /**
     * Module request
     *
     * @var isys_module_request
     */
    private $m_modreq;
    /**
     * View manager
     *
     * @var isys_cmdb_view_manager
     */
    private $m_view_manager;

    /**
     * @static
     * @return array
     */
    public static function get_additional_links()
    {
        $l_links = [
            'RELATION' => [
                'LC__CMDB__CATG__RELATION',
                '?' . C__CMDB__GET__VIEWMODE . '=' . C__CMDB__VIEW__LIST_OBJECT . '&' . C__CMDB__GET__TREEMODE . '=' . C__CMDB__VIEW__TREE_RELATION,
                C__MODULE__CMDB,
                // Parent module
                isys_application::instance()->www_path . 'images/icons/silk/arrow_out.png',
                // Sub module icon
            ]
        ];

        if (class_exists('isys_cmdb_view_multiedit'))
        {
            $l_links['MULTIEDIT'] = [
                'LC__MULTIEDIT__MULTIEDIT',
                '?' . C__GET__MODULE_ID . '=' . C__MODULE__CMDB . '&' . C__CMDB__GET__VIEWMODE . '=' . C__CMDB__VIEW__MULTIEDIT,
                C__MODULE__CMDB,
                // Parent module
                isys_application::instance()->www_path . 'images/icons/silk/table_edit.png',
                // Sub module icon
            ];
        }

        return $l_links;
    } // function

    /**
     * Returns all corresponding signals as hookable events
     *
     * Wrapper for isys_module_cmdb_eventhandler::hooks
     *
     * @return isys_array
     */
    public static function hooks()
    {
        return isys_module_cmdb_eventhandler::hooks();
    }

    /**
     * This method is used to build the validation-cache which is used inside the isys_cmdb_dao_category::get_properties_ng() method.
     *
     * @return  isys_caching  The freshly filled isys_caching instance.
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function create_validation_cache()
    {
        global $g_comp_database;

        // At first we get ourself a fresh instance with no data.
        $l_cache = isys_caching::factory('validation_config')
            ->clear();

        $l_res = isys_cmdb_dao_validation::instance($g_comp_database)
            ->get_data();

        $l_json_cache = [];

        while ($l_validation_row = $l_res->get_row())
        {

            if ($l_validation_row['isys_validation_config__isysgui_catg__id'] !== null)
            {
                $l_cat_type = 'g';
            }
            else if ($l_validation_row['isys_validation_config__isysgui_cats__id'] !== null)
            {
                $l_cat_type = 's';
            }
            else
            {
                $l_cat_type = 'g_custom';
            }

            $l_json_cache[$l_cat_type][$l_validation_row['isys_validation_config__isysgui_cat' . $l_cat_type . '__id']] = isys_format_json::decode(
                $l_validation_row['isys_validation_config__json'],
                true
            );
        } // while

        return $l_cache->set('g', $l_json_cache['g'])
            ->set('s', $l_json_cache['s'])
            ->set('g_custom', $l_json_cache['g_custom'])
            ->save();
    }

    /**
     * Get related auth class for module
     *
     * @return isys_auth
     */
    public static function get_auth()
    {
        return isys_auth_cmdb::instance();
    } // function

    /**
     * @return isys_cmdb_view_manager|null
     */
    public function get_view_manager()
    {
        return $this->m_view_manager;
    } // function

    /**
     * Initializes the CMDB Module.
     *
     * @param   isys_module_request $p_req
     *
     * @return  boolean
     * @throws  isys_exception_cmdb
     */
    public function init(isys_module_request $p_req)
    {
        global $g_comp_database;
        $this->m_modreq = $p_req;

        $this->m_view_manager = new isys_cmdb_view_manager($p_req);
        $this->m_dao_cmdb     = new isys_cmdb_dao($g_comp_database);

        try
        {
            // Register category views.
            $this->m_view_manager->register(new isys_cmdb_view_category($this->m_modreq));

            // Register config views.
            $this->m_view_manager->register("isys_cmdb_view_config_objecttype", C__CMDB__VIEW__CONFIG_OBJECTTYPE);
            $this->m_view_manager->register("isys_cmdb_view_config_systemdata", C__CMDB__VIEW__CONFIG_SYSTEMDATA);

            // Register list views
            $this->m_view_manager->register("isys_cmdb_view_list_category", C__CMDB__VIEW__LIST_CATEGORY);
            $this->m_view_manager->register("isys_cmdb_view_list_object", C__CMDB__VIEW__LIST_OBJECT);
            $this->m_view_manager->register("isys_cmdb_view_list_objecttype", C__CMDB__VIEW__LIST_OBJECTTYPE);

            if (defined('C__CMDB__VIEW__EXPLORER'))
            {
                $this->m_view_manager->register("isys_cmdb_view_explorer", C__CMDB__VIEW__EXPLORER);
            } // if

            if (defined('C__CMDB__VIEW__MULTIEDIT'))
            {
                $this->m_view_manager->register("isys_cmdb_view_multiedit", C__CMDB__VIEW__MULTIEDIT);
            } // if

            // Register misc. views.
            $this->m_view_manager->register("isys_cmdb_view_misc_blank", C__CMDB__VIEW__MISC_BLANK);

            // Register tree views.
            $this->m_view_manager->register("isys_cmdb_view_tree_location", C__CMDB__VIEW__TREE_LOCATION);
            $this->m_view_manager->register("isys_cmdb_view_tree_object", C__CMDB__VIEW__TREE_OBJECT);
            $this->m_view_manager->register("isys_cmdb_view_tree_objecttype", C__CMDB__VIEW__TREE_OBJECTTYPE);
            $this->m_view_manager->register("isys_cmdb_view_tree_relation", C__CMDB__VIEW__TREE_RELATION);

            // Register workflow views
            $this->m_view_manager->register("isys_workflow_view_tree", C__WF__VIEW__TREE);
            $this->m_view_manager->register("isys_workflow_view_detail_generic", C__WF__VIEW__DETAIL__GENERIC);
            $this->m_view_manager->register("isys_workflow_view_detail_selector", C__WF__VIEW__DETAIL__SELECTOR);
            $this->m_view_manager->register("isys_workflow_view_detail_email_gui", C__WF__VIEW__DETAIL__EMAIL_GUI);
            $this->m_view_manager->register("isys_workflow_view_list_handler", C__WF__VIEW__LIST);
            $this->m_view_manager->register("isys_workflow_view_list_template", C__WF__VIEW__LIST_TEMPLATE);
            $this->m_view_manager->register("isys_workflow_view_detail_template", C__WF__VIEW__DETAIL__TEMPLATE);
            $this->m_view_manager->register("isys_workflow_view_detail_wf_type", C__WF__VIEW__DETAIL__WF_TYPE);
            $this->m_view_manager->register("isys_workflow_view_list_wf_type", C__WF__VIEW__LIST_WF_TYPE);
            $this->m_view_manager->register("isys_workflow_view_list_filter", C__WF__VIEW__LIST_FILTER);

            return true;
        }
        catch (isys_exception_cmdb $l_e)
        {
            throw new isys_exception_cmdb("Could not initialize CMDB View (" . $l_e->getMessage() . ")");
        } // try
    } // function

    /**
     * Method for removing the IP-address from objects, which are getting archived or deleted. This is necessary for several reasons. Believe me. Also read #4082 in our TRAC.
     *
     * @global                isys_component_database
     *
     * @param   isys_cmdb_dao $p_cmdb_dao
     * @param   integer       $p_direction
     * @param   array         $p_objects
     *
     * @return  void
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function detach_ip_address($p_cmdb_dao, $p_direction, array $p_objects)
    {
        global $g_comp_database;
        /**
         * @var  isys_cmdb_dao_category_g_ip $l_ip_dao
         */
        $l_ip_dao = isys_cmdb_dao_category_g_ip::instance($g_comp_database);

        switch ($p_direction)
        {
            case C__CMDB__RANK__DIRECTION_DELETE:
                $l_ip_dao->empty_ip_addresses_from_obj($p_objects);
                break;

            case C__CMDB__RANK__DIRECTION_RECYCLE:
                ; // Nothing to do here. Yet.
                break;
        } // switch
    }

    /**
     * Build breadcrumb navifation
     *
     * @param &$p_gets
     *
     * @return array|null
     */
    public function breadcrumb_get(&$p_gets)
    {
        global $g_product_info;

        $l_gets = $this->m_modreq->get_gets();
        $l_tpl  = $this->m_modreq->get_template();
        $l_cmdb = $this->m_dao_cmdb;
        $l_res  = [];

        // Stage ONE : Object type.

        /* Retrive object type by object id if `$_GET[C__CMDB__GET__OBJECT]`is not setted */
        if (!isset($l_gets[C__CMDB__GET__OBJECTTYPE]) && isset($l_gets[C__CMDB__GET__OBJECT]))
        {
            $l_objectRes                      = $this->m_dao_cmdb->get_object_by_id($l_gets[C__CMDB__GET__OBJECT])
                ->get_row();
            $l_gets[C__CMDB__GET__OBJECTTYPE] = $l_objectRes["isys_obj_type__id"];
        }

        if (isset($l_gets[C__CMDB__GET__OBJECTTYPE]))
        {
            $l_ot = $l_cmdb->get_object_types($l_gets[C__CMDB__GET__OBJECTTYPE]);
            if ($l_ot && $l_ot->num_rows() > 0)
            {
                $l_ot_data = $l_ot->get_row();
                if (isset($l_og_gets))
                {
                    // If object group was processed, take its GET-Parameters.
                    $l_ot_gets = $l_og_gets;
                }
                else
                {
                    // Other $p_gets ...
                    $l_ot_gets = $p_gets;
                } // if

                if (isset($l_ot_data['isys_obj_type__isys_obj_type_group__id']))
                {
                    //$l_ot_gets[C__CMDB__GET__OBJECTGROUP] = $l_ot_data['isys_obj_type__isys_obj_type_group__id'];
                } // if

                $l_ot_gets[C__CMDB__GET__OBJECTTYPE] = $l_gets[C__CMDB__GET__OBJECTTYPE];
                $l_ot_gets[C__CMDB__GET__VIEWMODE]   = C__CMDB__VIEW__LIST_OBJECT;

                if ($l_ot_data['isys_obj_type__id'] != C__OBJTYPE__RELATION && $l_ot_data['isys_obj_type__id'] != C__OBJTYPE__PARALLEL_RELATION)
                {
                    $l_ot_gets[C__CMDB__GET__TREEMODE] = C__CMDB__VIEW__TREE_OBJECTTYPE;
                }
                else
                {
                    $l_ot_gets[C__CMDB__GET__TREEMODE] = C__CMDB__VIEW__TREE_RELATION;
                } // if

                $l_res[] = [_L($l_ot_data["isys_obj_type__title"]) => $l_ot_gets];
            } // if

            // Stage TWO : Object.
            if (isset($l_gets[C__CMDB__GET__OBJECT]))
            {
                $l_o = $l_cmdb->get_object_by_id($l_gets[C__CMDB__GET__OBJECT]);

                if ($l_o && $l_o->num_rows() > 0)
                {
                    $l_o_data  = $l_o->get_row();
                    $l_o_title = $l_cmdb->get_obj_name_by_id_as_string($l_o_data["isys_obj__id"]);
                    if (isset($l_ot_gets))
                    {
                        $l_o_gets                         = $l_ot_gets;
                        $l_o_gets[C__CMDB__GET__CATG]     = C__CATG__GLOBAL;
                        $l_o_gets[C__CMDB__GET__OBJECT]   = $l_gets[C__CMDB__GET__OBJECT];
                        $l_o_gets[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__CATEGORY_GLOBAL;
                        $l_o_gets[C__CMDB__GET__TREEMODE] = C__CMDB__VIEW__TREE_OBJECT;
                        $l_res[]                          = [_L($l_o_title) => $l_o_gets];
                    }
                } // if

                // Stage THREE : Category.
                $l_cattype   = null;
                $l_catview   = null;
                $l_catget    = null;
                $l_catprefix = null;
                $l_cattree   = null;
                $l_catcget   = null;

                if (isset ($l_gets[C__CMDB__GET__CATG]))
                {
                    $l_cattype   = C__CMDB__CATEGORY__TYPE_GLOBAL;
                    $l_catview   = C__CMDB__VIEW__CATEGORY_GLOBAL;
                    $l_cattree   = C__CMDB__VIEW__TREE_OBJECT;
                    $l_catget    = C__CMDB__GET__CATG;
                    $l_catprefix = "catg";
                }
                else if (isset($l_gets[C__CMDB__GET__CATS]))
                {
                    $l_cattype   = C__CMDB__CATEGORY__TYPE_SPECIFIC;
                    $l_catview   = C__CMDB__VIEW__CATEGORY_SPECIFIC;
                    $l_cattree   = C__CMDB__VIEW__TREE_OBJECT;
                    $l_catget    = C__CMDB__GET__CATS;
                    $l_catprefix = "cats";
                } // if

                $l_catconst = isys_glob_which_isset($l_gets[C__CMDB__GET__CATG], $l_gets[C__CMDB__GET__CATS], $l_gets[C__CMDB__GET__CATD]);

                // Do not change this, necessary type comparison here!
                if ($l_cattype !== null)
                {
                    $l_catinfo = $l_cmdb->gui_get_info_by_category($l_cattype, $l_catconst);

                    if ($l_catinfo)
                    {
                        $l_catinfo   = $l_catinfo->get_row();
                        $l_cat_title = _L($l_catinfo["isysgui_" . $l_catprefix . "__title"]);

                        // @todo  Find out why $l_gets does not contain the key "C__CMDB__GET__CATG_CUSTOM".
                        if ($l_cattype == C__CMDB__CATEGORY__TYPE_GLOBAL && $l_catconst == C__CATG__CUSTOM_FIELDS && $_GET[C__CMDB__GET__CATG_CUSTOM] > 0)
                        {
                            global $g_comp_database;

                            $l_cat_title = isys_custom_fields_dao::instance($g_comp_database)
                                ->get_data($_GET[C__CMDB__GET__CATG_CUSTOM] ?: $_POST[C__CMDB__GET__CATG_CUSTOM])
                                ->get_row_value('isysgui_catg_custom__title');

                            // Just in case someone uses custom translations: ID-1649.
                            $l_cat_title = _L($l_cat_title);
                        } // if

                        $l_c_gets = [];

                        if (isset($l_o_gets))
                        {
                            $l_c_gets                         = $l_o_gets;
                            $l_c_gets[$l_catget]              = $l_catconst;
                            $l_c_gets[C__CMDB__GET__VIEWMODE] = $l_catview;
                            $l_c_gets[C__CMDB__GET__TREEMODE] = $l_cattree;
                        }

                        $l_c_gets['navPageStart'] = $_GET['navPageStart'];

                        if ($_GET['navPageStart'] > 0)
                        {
                            $l_cat_title .= ' (' . _L('LC__UNIVERSAL__PAGE') . ' ' . (ceil($_GET['navPageStart'] / isys_glob_get_pagelimit()) + 1) . ')';
                        }

                        if ($l_gets[C__CMDB__GET__CATLEVEL])
                        {
                            // This will prevent the "catgID=1" parameter, when we are in specific categories.
                            if (isset($l_gets[C__CMDB__GET__CATS]))
                            {
                                unset($l_c_gets[C__CMDB__GET__CATG]);
                            } // if

                            $l_dist = new isys_cmdb_dao_distributor(
                                $this->m_dao_cmdb->get_database_component(), $l_gets[C__CMDB__GET__OBJECT], $l_cattype, $l_gets[C__CMDB__GET__CATLEVEL], [$l_catconst => true]
                            );

                            $l_category_dao = $l_dist->get_category($l_catconst);
                            if (method_exists($l_category_dao, 'get_general_data'))
                            {
                                $l_general_data         = $l_category_dao->get_general_data();
                                $l_category_entry_title = $l_category_dao->get_entry_identifier($l_general_data);

                                if (empty($l_category_entry_title))
                                {
                                    if ($l_gets[C__CMDB__GET__CATLEVEL] > 0)
                                    {
                                        $l_category_entry_title = '#' . $l_gets[C__CMDB__GET__CATLEVEL];
                                    }
                                    else
                                    {
                                        $l_category_entry_title = _L('LC__UNIVERSAL__NEW_ENTRY');
                                    }
                                }
                            }

                            $l_c_gets[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__LIST_CATEGORY;

                            if ($l_cattype == C__CMDB__CATEGORY__TYPE_GLOBAL && $l_catconst == C__CATG__CUSTOM_FIELDS && $_GET[C__CMDB__GET__CATG_CUSTOM] > 0)
                            {
                                $l_c_gets[C__CMDB__GET__CATG_CUSTOM] = $_GET[C__CMDB__GET__CATG_CUSTOM];
                            } // if
                        }

                        $l_res[] = [
                            $l_cat_title => $l_c_gets
                        ];

                        if (isset($l_category_entry_title))
                        {
                            $l_res[] = [
                                $l_category_entry_title => []
                            ];
                        }
                    } // if
                } // if

            } // if
        }
        else if ($l_gets[C__CMDB__GET__VIEWMODE])
        {
            switch ($l_gets[C__CMDB__GET__VIEWMODE])
            {
                case C__CMDB__VIEW__MULTIEDIT:
                    $l_res[] = [_L('LC__MULTIEDIT__MULTIEDIT') => null];
                    break;

                case C__CMDB__VIEW__EXPLORER:
                    $l_res[] = ['CMDB-Explorer' => null];
                    break;

                case C__WF__VIEW__LIST:
                case C__WF__VIEW__LIST_FILTER:
                case C__WF__VIEW__LIST_TEMPLATE:
                case C__WF__VIEW__LIST_WF_TYPE:
                case C__WF__VIEW__DETAIL__EMAIL_GUI:
                case C__WF__VIEW__DETAIL__GENERIC:
                case C__WF__VIEW__DETAIL__SELECTOR:
                case C__WF__VIEW__DETAIL__TEMPLATE:
                case C__WF__VIEW__DETAIL__WF_TYPE:
                    $l_res[] = ['Workflows' => null];
                    break;

                default:
                    $l_res[] = ['i-doit ' . $g_product_info['version'] . ' ' . $g_product_info['step'] . ' ' . $g_product_info['type'] => null];
                    break;
            } // switch
        }
        else
        {
            // We're in dashboard mode if theres no object type or viewmode set.
            $l_res[] = ['i-doit ' . $g_product_info['version'] . ' ' . $g_product_info['step'] . ' ' . $g_product_info['type'] => null];
        } // if

        return $l_res;
    }

    /**
     * This method builds the tree for the menu.
     *
     * @param   isys_component_tree $p_tree
     * @param   boolean             $p_system_module
     * @param   integer             $p_parent
     *
     * @author  Dennis Stücken <dstuecken@synetics.de>
     * @since   0.9.9-7
     * @see     isys_module::build_tree()
     */
    public function build_tree(isys_component_tree $p_tree, $p_system_module = true, $p_parent = null)
    {
        global $g_comp_template_language_manager, $g_dirs;

        if (null !== $p_parent && is_int($p_parent))
        {
            $l_mod_gets = $_GET;

            if ($p_system_module)
            {
                $l_mod_gets[C__GET__MODULE_SUB_ID] = C__MODULE__CMDB;
            } // if

            unset($l_mod_gets[C__CMDB__GET__OBJECTTYPE], $l_mod_gets['what']);

            if (defined('C__MODULE__PRO'))
            {
                $l_mod_gets[C__GET__SETTINGS_PAGE] = 'validation';
                $l_mod_gets[C__GET__TREE_NODE]     = C__MODULE__CMDB . '01338';
                $p_tree->add_node(
                    C__MODULE__CMDB . '01338',
                    $p_parent,
                    $g_comp_template_language_manager->{"LC__SETTINGS__CMDB__VALIDATION"},
                    isys_glob_build_url(isys_glob_http_build_query($l_mod_gets)),
                    null,
                    $g_dirs["images"] . "/icons/silk/page_white_edit.png"
                );
            }

        } // if
    }

    /**
     * Initialize module slots
     */
    public function initslots()
    {
        global $g_comp_database;
        $l_comp_settings = new isys_component_dao_setting($g_comp_database);

        if ($l_comp_settings->get(null, null, 'C__MANDATORY_SETTING__IP_HANDLING'))
        {
            isys_component_signalcollection::get_instance()
                ->connect(
                    "mod.cmdb.afterObjectRank",
                    [
                        'isys_module_cmdb',
                        'detach_ip_address'
                    ]
                );
        }

        /* Unique-Handling for ips */
        isys_component_signalcollection::get_instance()
            ->connect(
                "mod.cmdb.afterObjectRank",
                [
                    'isys_cmdb_dao_category_g_ip',
                    'unique_handling'
                ]
            );
    }

    /**
     * Evalutes text and link for my-doit bookmark entry statically.
     * Returns true on success, false on failure.
     *
     * @param string $l_text
     * @param string $l_link
     *
     * @return boolean
     */
    public function mydoit_get(&$l_text, &$l_link)
    {
        $l_gets  = $this->m_modreq->get_gets();
        $l_posts = $this->m_modreq->get_posts();
        $l_cmdb  = $this->m_dao_cmdb;

        $l_textArray = [];

        $l_basegets                    = [];
        $l_basegets[C__GET__MODULE_ID] = C__MODULE__CMDB;

        // Stage ZERO : Object group.
        if (isset($l_gets[C__CMDB__GET__OBJECTGROUP]))
        {
            $l_og = $l_cmdb->objgroup_get_by_id($l_gets[C__CMDB__GET__OBJECTGROUP]);
            if ($l_og && $l_og->num_rows() > 0)
            {
                $l_basegets[C__CMDB__GET__OBJECTGROUP] = $l_gets[C__CMDB__GET__OBJECTGROUP];
            } // if
        } // if

        // Stage ONE : Object type.
        if (isset($l_gets[C__CMDB__GET__OBJECTTYPE]))
        {
            $l_ot = $l_cmdb->get_object_types($l_gets[C__CMDB__GET__OBJECTTYPE]);
            if ($l_ot && $l_ot->num_rows() > 0)
            {
                $l_ot_data                            = $l_ot->get_row();
                $l_basegets[C__CMDB__GET__OBJECTTYPE] = $l_gets[C__CMDB__GET__OBJECTTYPE];
                $l_basegets[C__CMDB__GET__VIEWMODE]   = C__CMDB__VIEW__LIST_OBJECT;
                $l_basegets[C__CMDB__GET__TREEMODE]   = C__CMDB__VIEW__TREE_OBJECTTYPE;

                $l_textArray[] = _L($l_ot_data["isys_obj_type__title"]);
            } // if

            /* Stage TWO : Object */
            if (isset($l_gets[C__CMDB__GET__OBJECT]))
            {
                $l_o = $l_cmdb->get_object_by_id($l_gets[C__CMDB__GET__OBJECT]);
                if ($l_o && $l_o->num_rows() > 0)
                {
                    $l_o_data                           = $l_o->get_row();
                    $l_o_title                          = $l_cmdb->get_obj_name_by_id_as_string($l_o_data["isys_obj__id"]);
                    $l_basegets[C__CMDB__GET__CATG]     = C__CATG__GLOBAL;
                    $l_basegets[C__CMDB__GET__OBJECT]   = $l_gets[C__CMDB__GET__OBJECT];
                    $l_basegets[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__CATEGORY_GLOBAL;
                    $l_basegets[C__CMDB__GET__TREEMODE] = C__CMDB__VIEW__TREE_OBJECT;

                    $l_textArray[] = $l_o_title;
                } // if
            } // if
        } // if

        if (isset($l_gets[C__CMDB__GET__VIEWMODE]) && $l_gets[C__CMDB__GET__VIEWMODE] == C__CMDB__VIEW__EXPLORER)
        {
            $l_textArray = [
                _L('LC__MODULE__CMDB__VISUALIZATION')
            ];

            // We use the "POST" array, because this might hold more reliable data.
            $l_basegets = [
                C__CMDB__GET__VIEWMODE      => C__CMDB__VIEW__EXPLORER,
                C__CMDB__GET__OBJECT        => (int) $l_posts['C_VISUALIZATION_OBJ_SELECTION__HIDDEN'],
                C__CMDB__VISUALIZATION_VIEW => $l_gets[C__CMDB__VISUALIZATION_VIEW],
                C__CMDB__VISUALIZATION_TYPE => $l_gets[C__CMDB__VISUALIZATION_TYPE],
                'profile'                   => (int) $l_posts['C_VISUALIZATION_PROFILE'],
                'service'                   => (int) $l_posts['C_VISUALIZATION_SERVICE_FILTER']
            ];

            if ($l_basegets[C__CMDB__GET__OBJECT] > 0)
            {
                $l_textArray[] = $l_cmdb->get_obj_name_by_id_as_string($l_basegets[C__CMDB__GET__OBJECT]);
            } // if
        } // if

        $l_text = $l_textArray;
        $l_link = isys_glob_http_build_query($l_basegets);

        return true;
    } // function

    /**
     * Starts the CMDB Module
     *
     * @return bool
     */
    public function start()
    {
        global $index_includes;
        global $g_comp_database;
        global $g_ajax;

        if ($_GET[C__GET__MODULE_SUB_ID])
        {
            return $this->system_settings();
        }

        $l_viewdata = "";
        $l_gets     = $this->m_modreq->get_gets();
        $l_posts    = $this->m_modreq->get_posts();
        $l_tpl      = $this->m_modreq->get_template();

        /* Define Drag and Drop constant */
        define("C__OBJECT_DRAGNDROP", !!isys_tenantsettings::get('cmdb.registry.object_dragndrop', 1));

        /**
         * -------------------------------------------------------------------
         *  P A R A M E T E R   -   H A N D L I N G
         * -------------------------------------------------------------------
         */

        try
        {
            /**
             * Handle popup request
             * Popups are CMDB-specific.
             */
            if (isset($l_gets[C__CMDB__GET__POPUP])) return $this->handle_popups($l_gets[C__CMDB__GET__POPUP]);

            if (isset($l_gets[C__GET__FILE_MANAGER])) $this->handle_file_manager($l_gets[C__GET__FILE_MANAGER]);

        }
        catch (isys_exception_cmdb $e)
        {
            isys_application::instance()->container['notify']->error($e->getMessage());
        }

        /* ---------------------------------------------------------------- */
        /* BEGIN ##1## */
        {
            // Set default viewmode.
            if (!isset($l_gets[C__CMDB__GET__VIEWMODE]))
            {
                if (isset($l_gets[C__CMDB__GET__OBJECT]))
                {
                    $l_gets[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__CATEGORY;
                }
                else
                {
                    $l_gets[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__MISC_BLANK;
                } // if
            } // if

            /* Retrieve object type id if needed! */
            if (!isset($l_gets[C__CMDB__GET__OBJECTTYPE]) && isset($l_gets[C__CMDB__GET__OBJECT]))
            {
                $l_objectRes                      = $this->m_dao_cmdb->get_object_by_id($l_gets[C__CMDB__GET__OBJECT])
                    ->get_row();
                $l_gets[C__CMDB__GET__OBJECTTYPE] = $l_objectRes['isys_obj_type__id'];
            }

            // Set default tree viewmode ##1##
            if (!isset($l_gets[C__CMDB__GET__TREEMODE]) || !$l_gets[C__CMDB__GET__TREEMODE])
            {
                if (isset($l_gets[C__CMDB__GET__OBJECT]))
                {
                    $l_gets[C__CMDB__GET__TREEMODE] = C__CMDB__VIEW__TREE_OBJECT;
                }
                else
                {
                    $l_res = isys_component_dao_user::instance(isys_application::instance()->database)
                        ->get_user_settings();

                    if ($l_res && !isset($l_gets['treePath']))
                    {
                        $l_gets[C__CMDB__GET__TREEMODE] = $l_res['isys_user_locale__default_tree_view'];
                        $l_gets[C__CMDB__GET__TREETYPE] = $l_res['isys_user_locale__default_tree_type'];

                        // This is a bugfix, which prevents throwing an exception!
                        if ((!isset($l_gets[C__CMDB__GET__OBJECT]) && $l_gets[C__CMDB__GET__TREEMODE] == C__CMDB__VIEW__TREE_OBJECT) || empty($l_gets[C__CMDB__GET__TREEMODE]))
                        {
                            $l_gets[C__CMDB__GET__TREEMODE] = C__CMDB__VIEW__TREE_OBJECTTYPE;
                        }
                    }
                    else if (!isset($l_gets[C__CMDB__GET__OBJECT]))
                    {
                        $l_gets[C__CMDB__GET__TREEMODE] = C__CMDB__VIEW__TREE_OBJECTTYPE;
                    }
                }
            }

            /**
             * @var $g_menu isys_component_menu
             */
            global $g_menu;
            if (!isset($l_gets[C__CMDB__GET__OBJECTGROUP]) && !isset($l_gets[C__CMDB__GET__OBJECT]) && !isset($l_gets[C__CMDB__GET__OBJECTTYPE]))
            {
                // @todo  Should we make this configurable? Feature-Request pending...
                if (defined('C__MAINMENU__INFRASTRUCTURE'))
                {
                    $l_gets[C__CMDB__GET__OBJECTGROUP] = C__OBJTYPE_GROUP__INFRASTRUCTURE;
                    isys_component_menu::set_active_menu(C__OBJTYPE_GROUP__INFRASTRUCTURE);
                }
                elseif (isset($l_gets[C__CMDB__GET__VIEWMODE]) && $l_gets[C__CMDB__GET__VIEWMODE] == C__CMDB__VIEW__MISC_BLANK)
                {
                    if (is_object($g_menu))
                    {
                        $l_active  = $g_menu->get_active_menuobj();
                        $l_default = isys_component_menu::get_default_mainmenu();

                        if (defined($l_default))
                        {
                            $l_gets[C__CMDB__GET__OBJECTGROUP] = constant($l_default);
                        } // if

                        if (isset($l_active) && !empty($l_active))
                        {
                            $l_gets[C__CMDB__GET__OBJECTGROUP] = substr($l_active, 0, -1);
                        } // if

                        isys_component_menu::set_active_menu($l_gets[C__CMDB__GET__OBJECTGROUP]);

                        if (($l_mainmenu = isys_component_menu::get_default_menu_as_constant()))
                        {
                            $l_gets[C__CMDB__GET__OBJECTGROUP] = substr($l_mainmenu, 0, -1);
                            $l_tpl->assign('activeMainMenuItem', $l_mainmenu);
                        } // if
                    } // if
                } // if
            }
            else
            {
                if (is_object($g_menu))
                {
                    $l_active  = $g_menu->get_active_menuobj();
                    $l_default = isys_component_menu::get_default_mainmenu();

                    if (defined($l_default))
                    {
                        $l_gets[C__CMDB__GET__OBJECTGROUP] = constant($l_default);
                    } // if
                    if (isset($l_active) && !empty($l_active))
                    {
                        $l_gets[C__CMDB__GET__OBJECTGROUP] = substr($l_active, 0, -1);
                    } // if
                    isys_component_menu::set_active_menu($l_gets[C__CMDB__GET__OBJECTGROUP]);
                    if (($l_mainmenu = isys_component_menu::get_default_menu_as_constant()))
                    {
                        $l_tpl->assign('activeMainMenuItem', $l_mainmenu);
                    }
                    unset($l_mainmenu);
                }
            }
            // This block is responsible to set absolutely necessary parameters to the GET-Parameterlist.
            $this->m_modreq->_internal_set_private("m_get", $l_gets);
        }
        /* END ##1## */
        /* ---------------------------------------------------------------- */
        /* BEGIN ##2## */
        {
            /* Exception 1: If showing the global category and no other
               category ID is set, set it to category "global"! */
            if ($l_gets[C__CMDB__GET__VIEWMODE] == C__CMDB__VIEW__CATEGORY && !isset($l_gets[C__CMDB__GET__CATG]) && !isset($l_gets[C__CMDB__GET__CATS]))
            {

                // See what the object type settings state
                $l_typeres   = $this->m_dao_cmdb->get_object_types($l_gets[C__CMDB__GET__OBJECTTYPE]);
                $l_arrRecord = $l_typeres->get_row();
                $l_bOverview = $l_arrRecord["isys_obj_type__overview"];

                if ($l_bOverview)
                {
                    $l_gets[C__CMDB__GET__CATG] = C__CATG__OVERVIEW;
                }
                else
                {
                    $l_gets[C__CMDB__GET__CATG] = C__CATG__GLOBAL;
                }
            }

            /* Exception 2: If showing the type list and objTypeID is not
                set, use misc view for welcome */
            if ($l_gets[C__CMDB__GET__VIEWMODE] == C__CMDB__VIEW__LIST_OBJECT && !isset($l_gets[C__CMDB__GET__OBJECTTYPE]))
            {
                $l_gets[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__MISC_BLANK;
            }

            /* This block is responsible to set absolutely necessary parameters
               to the GET-Parameterlist */
            $this->m_modreq->_internal_set_private("m_get", $l_gets);
        }
        /* END ##2## */
        /* ---------------------------------------------------------------- */

        /* Require viewmode and treemode are mandatory parameters, they must
           be set ALL times. If they are not set, they will be set manually
           in ##1## */
        $l_parameters_mandatory = [
            C__CMDB__GET__VIEWMODE => true,
            C__CMDB__GET__TREEMODE => true
        ];

        /* Parameters which won't get deleted by the conformer */
        $l_parameters_optional = [
            C__GET__FILE_MANAGER             => true,
            C__CMDB__GET__POPUP              => true,
            C__CMDB__GET__OBJECTGROUP        => true,
            C__GET__MODULE_ID                => true,
            C__GET__MAIN_MENU__NAVIGATION_ID => true,
            C__CMDB__GET__EDITMODE           => true,
            C__GET__AJAX_CALL                => true,
            C__GET__AJAX_REQUEST             => true
        ];

        /*
         * -------------------------------------------------------------------
         *  R E Q U E S T - P R O C E S S O R
         * -------------------------------------------------------------------
         */

        /*
         * -------------------------------------------------------------------
         *  V I E W - H A N D L I N G
         * -------------------------------------------------------------------
         */

        if (empty($l_gets[C__CMDB__GET__VIEWMODE]))
        {
            $l_gets[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__CATEGORY;
        } // if

        // Do some GUI / Interaction handling.
        switch ($l_posts[C__GET__NAVMODE])
        {
            case C__NAVMODE__NEW:
            case C__NAVMODE__EDIT:
                // If navmode = edit, set catlevel to first selected list element.
                if (isset($l_posts["id"][0]) && $l_posts[C__GET__NAVMODE] == C__NAVMODE__EDIT)
                {
                    if (is_array($l_posts[C__GET__ID]) && is_numeric($l_posts[C__GET__ID][0]))
                    {
                        $_GET[C__CMDB__GET__CATLEVEL] = $l_gets[C__CMDB__GET__CATLEVEL] = $l_posts[C__GET__ID][0];
                    }
                }

                // When clicking new or edit, we should always land inside the detail category view.
                switch ($l_gets[C__CMDB__GET__VIEWMODE])
                {
                    case C__CMDB__VIEW__LIST_CATEGORY:
                        $l_gets[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__CATEGORY;
                        break;
                } // switch

                $this->m_modreq->_internal_set_private("m_get", $l_gets);
                break;
        } // switch

        // Skip view handling if its not needed.
        if (!$g_ajax || $g_ajax && !strstr($_GET[C__GET__AJAX_CALL], "tree"))
        {
            try
            {
                $l_content_top    = null;
                $l_content_bottom = null;

                /**
                 * @var isys_cmdb_view
                 */
                $l_view = $this->m_view_manager->get_view($l_gets[C__CMDB__GET__VIEWMODE]);

                if (is_object($l_view))
                {
                    // Ask view object for its navigation parameters.
                    $l_view->get_mandatory_parameters($l_parameters_mandatory);
                    $l_view->get_optional_parameters($l_parameters_optional);

                    $GLOBALS["g_cmdb_view"] = &$l_view;

                    if ($l_posts[C__GET__NAVMODE] == C__NAVMODE__SAVE && $_GET[C__GET__AJAX] == '1')
                    {
                        // Saving is regularly done via ajax so we don't need to process any views here.
                        if (method_exists($l_view, 'process_save'))
                        {
                            $l_viewdata = $l_view->process_save();
                        } // if
                    }
                    else
                    {
                        $l_viewdata = $l_view->process();

                        // Does the view require a module request?
                        // @todo No view should require a reload of the cmdb module ! A reload is always a performance loss!
                        if ($l_view->requires_module_reload())
                        {
                            $l_view->trigger_module_reload();

                            // ... and restart the module.
                            return $this->start();
                        } // if
                    } // if

                    // Get filenames of templates associated with the view.
                    if (method_exists($l_view, 'get_template_top'))
                    {
                        $l_content_top = $l_view->get_template_top();
                    } // if

                    if (method_exists($l_view, 'get_template_bottom'))
                    {
                        $l_content_bottom = $l_view->get_template_bottom();
                    } // if

                    // Get name of template placeholder, where we write the data returned by process() in and assign it.
                    if (!empty($l_viewdata) && method_exists($l_view, 'get_template_destination'))
                    {
                        $l_tpl->assign($l_view->get_template_destination(), $l_viewdata);
                    } // if
                } // if
            }
            catch (isys_exception_auth $e)
            {
                $l_tpl->assign('exception', $e->write_log());
                $l_content_bottom = 'exception-auth.tpl';
            }
            catch (isys_exception $e)
            {
                isys_application::instance()->container['notify']->error($e->getMessage());

                return $e->getMessage();
            }
            catch (Exception $e)
            {
                // Trigger system.exceptionTriggered, because this is not an isys_exception.
                isys_component_signalcollection::get_instance()
                    ->emit('system.exceptionTriggered', $e);

                isys_application::instance()->container['notify']->error($e->getMessage());

                return $e->getMessage();
            } // try

            // Set content templates.
            if (isset($l_content_top) && $l_content_top != null)
            {
                $index_includes["contenttop"] = $l_content_top;
            } // if

            if ($l_content_bottom != null)
            {
                $index_includes["contentbottomcontent"] = $l_content_bottom;
            } // if

            // Emit viewProcessed.
            isys_component_signalcollection::get_instance()
                ->emit("mod.cmdb.viewProcessed", isset($l_view) ? $l_view : null, $l_viewdata);
        } // if

        /*
         * -------------------------------------------------------------------
         *  T R E E - H A N D L I N G
         * -------------------------------------------------------------------
         */

        // Skip tree processing if it's not needed.
        if (!$g_ajax || $g_ajax && strstr($_GET[C__GET__AJAX_CALL], "tree"))
        {
            // Attach tree buttons
            $index_includes['lefttreetop'] = __DIR__ . '/templates/tree/buttons.tpl';

            $l_has_location_view_right = isys_auth_cmdb::instance()
                ->is_allowed_to(isys_auth::VIEW, 'LOCATION_VIEW');
            $l_tpl->assign("bShowMenuTreeButtons", true);

            if ($l_gets[C__CMDB__GET__OBJECTTYPE] == C__OBJTYPE__PARALLEL_RELATION || $l_gets[C__CMDB__GET__OBJECTTYPE] == C__OBJTYPE__RELATION && !$l_gets[C__CMDB__GET__OBJECT])
            {
                $l_gets[C__CMDB__GET__TREEMODE] = C__CMDB__VIEW__TREE_RELATION;
            } // if

            $l_tbloc_gets                            = $l_gets;
            $l_tbobj_gets[C__CMDB__GET__OBJECT]      = $l_gets[C__CMDB__GET__OBJECT];
            $l_tbobj_gets[C__CMDB__GET__OBJECTTYPE]  = $l_gets[C__CMDB__GET__OBJECTTYPE];
            $l_tbobj_gets[C__CMDB__GET__OBJECTGROUP] = $l_gets[C__CMDB__GET__OBJECTGROUP];
            $l_tbobj_gets[C__CMDB__GET__TREETYPE]    = $l_gets[C__CMDB__GET__TREETYPE];

            if ($l_gets[C__CMDB__GET__VIEWMODE] == C__CMDB__VIEW__CATEGORY_GLOBAL || is_numeric($l_gets[C__CMDB__GET__OBJECT]))
            {
                $l_tbobj_gets[C__CMDB__GET__TREEMODE] = C__CMDB__VIEW__TREE_OBJECT;

                if (!isset($l_gets[C__CMDB__GET__TREEMODE]))
                {
                    $l_gets[C__CMDB__GET__TREEMODE] = C__CMDB__VIEW__TREE_OBJECT;
                } // if
            }
            else
            {
                $l_tbobj_gets[C__CMDB__GET__TREEMODE] = C__CMDB__VIEW__TREE_OBJECTTYPE;
            } // if

            $l_strMenuTreeButtonClass_location = "";
            $l_strMenuTreeButtonClass_object   = "";

            if ($l_gets[C__CMDB__GET__TREEMODE] == C__CMDB__VIEW__TREE_LOCATION)
            {
                $l_strMenuTreeButtonClass_location = "active";
            }
            else
            {
                $l_strMenuTreeButtonClass_object = "active";
            } // if

            $l_tbloc_gets[C__CMDB__GET__TREEMODE] = C__CMDB__VIEW__TREE_LOCATION;

            $l_arMenuTree = [
                "objectLink"    => isys_glob_build_ajax_url(C__FUNC__AJAX__TREE_LOCATION, $l_tbobj_gets),
                "locationLink"  => isys_glob_build_ajax_url(C__FUNC__AJAX__TREE_LOCATION, $l_tbloc_gets),
                "objectClass"   => $l_strMenuTreeButtonClass_object,
                "locationClass" => $l_strMenuTreeButtonClass_location,
            ];

            $l_tpl->assign('has_location_view_right', $l_has_location_view_right)
                ->assign("menuTree", $l_arMenuTree)
                ->assign("treeMode", $l_gets[C__CMDB__GET__TREEMODE])
                ->assign("treeType", $l_gets[C__CMDB__GET__TREETYPE]);

            if (isset($l_gets[C__GET__NAVMODE]) && $l_gets[C__GET__NAVMODE])
            {
                $l_posts[C__GET__NAVMODE] = $l_gets[C__GET__NAVMODE];
                $this->m_modreq->_internal_set_private("m_post", $l_posts);
            } // if

            // Get tree view.
            $l_tree = $this->m_view_manager->get_view($l_gets[C__CMDB__GET__TREEMODE]);

            if ($l_tree)
            {
                try
                {
                    $l_treedata = $l_tree->process();

                    // Tree data.
                    if (!empty($l_treedata))
                    {
                        // For the ajax based object tree, the tree data is a script initializing the tree, not the actual content.
                        if ($l_tree->get_id() == C__CMDB__VIEW__TREE_LOCATION)
                        {
                            // Build and output ajax tree.
                            $l_tpl->assign("menu_tree_script", $l_treedata);
                        }
                        else
                        {
                            // Build and output tree.
                            $l_tpl->assign("menu_tree", $l_treedata);
                        } // if
                    } // if

                }
                catch (isys_exception $l_e)
                {
                    isys_application::instance()->container['notify']->error($l_e->getMessage());

                    return $l_e->getMessage();
                } // try
            } // if
        } // if

        /**
         * Load dashboard if viewmode = blank
         */
        if ($l_gets[C__CMDB__GET__VIEWMODE] === C__CMDB__VIEW__MISC_BLANK)
        {
            // Load Dashboard
            isys_module_dashboard::instance()
                ->init($this->m_modreq)
                ->load_user_dashboard()
                ->process_view();
        }

        /**
         * -------------------------------------------------------------------
         *  REQUEST CONFORMER
         * -------------------------------------------------------------------
         */
        try
        {
            if ($l_parameters_mandatory && $l_parameters_optional)
            {
                // Reset GET-Parameters to mandatory and optional parameters.
                $this->request_conformer($l_parameters_mandatory, $l_parameters_optional);
            } // if
        }
        catch (isys_exception_cmdb $l_e)
        {
            echo $l_e->getMessage();
        } // try

        /* Replace the GET and POST superglobals by the module request ones */
        isys_glob_merge_globals_by_modreq($this->m_modreq);

        return true;
    } // function

    /**
     * This navigation-point needs to be separated, see #3990.
     *
     * @param   isys_component_tree $p_tree
     * @param   boolean             $p_system_module
     * @param   integer             $p_parent
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function build_tree_listedit(isys_component_tree $p_tree, $p_system_module = true, $p_parent = null)
    {
        global $g_comp_template_language_manager, $g_dirs;

        if (null !== $p_parent && is_int($p_parent))
        {
            $l_mod_gets = $_GET;

            if ($p_system_module)
            {
                $l_mod_gets[C__GET__MODULE_SUB_ID] = C__MODULE__CMDB;
            } // if

            unset($l_mod_gets[C__CMDB__GET__OBJECTTYPE], $l_mod_gets['what']);

            $l_mod_gets[C__GET__SETTINGS_PAGE] = 'list';
            $l_mod_gets[C__GET__TREE_NODE]     = C__MODULE__CMDB . '01337';
            $p_tree->add_node(
                C__MODULE__CMDB . '01337',
                $p_parent,
                $g_comp_template_language_manager->{"LC__SETTINGS__CMDB__LISTS"},
                isys_glob_build_url(isys_glob_http_build_query($l_mod_gets)),
                null,
                $g_dirs["images"] . "/icons/silk/cog.png"
            );
        } // if
    } // function

    /**
     * Method to create the object list in the user configuration
     *
     * @return bool|string
     * @throws isys_exception_auth
     */
    public function get_object_type_list()
    {
        global $g_comp_database;

        $l_dao = new isys_cmdb_dao($g_comp_database);

        $l_allowed_obj_types = isys_auth_cmdb_object_types::instance()
            ->get_allowed_objecttypes();

        if ($l_allowed_obj_types !== false)
        {
            $l_data = $l_dao->get_object_types($l_allowed_obj_types);
        }
        else
        {
            throw new isys_exception_auth(_L('LC__AUTH__EXCEPTION__MISSING_RIGHTS_ON_OBJECT_TYPES'));
        }

        if ($l_data->num_rows() > 0)
        {
            $l_objList = new isys_component_list(null, $l_data);

            $l_objList->config(
                [
                    "isys_obj_type__id"    => _L("LC__CMDB__OBJTYPE") . '-ID',
                    "isys_obj_type__title" => _L("LC__CMDB__OBJTYPE") . '-' . _L('LC__CMDB__LOGBOOK__TITLE'),
                ],
                '?' . C__GET__MODULE_ID . "=" . C__MODULE__SYSTEM . '&' . C__GET__MODULE_SUB_ID . '=' . C__MODULE__CMDB . '&' . C__GET__TREE_NODE . '=' . $_GET[C__GET__TREE_NODE] . '&' . C__GET__SETTINGS_PAGE . '=list&' . C__CMDB__GET__EDITMODE . '=' . C__EDITMODE__ON . "&" . C__CMDB__GET__OBJECTTYPE . "=[{isys_obj_type__id}]",
                "[{isys_obj_type__id}]",
                true
            );

            $l_objList->createTempTable();

            return $l_objList->getTempTableHtml();
        }
        else
        {
            return false;
        } // if
    } // function

    /**
     * System settings menu
     *
     * @return null
     * @throws Exception
     * @throws isys_exception_auth
     * @throws isys_exception_general
     */
    public function system_settings()
    {
        global $index_includes, $g_comp_template, $g_comp_database, $g_dirs;

        switch ($_GET[C__GET__SETTINGS_PAGE])
        {
            case 'list':
                isys_auth_cmdb::instance()->list_config(isys_auth::EXECUTE);

                isys_component_template_navbar::getInstance()
                    ->set_active(false, C__NAVBAR_BUTTON__NEW)
                    ->set_active(false, C__NAVBAR_BUTTON__PURGE);

                if (isset($_GET[C__CMDB__GET__OBJECTTYPE]) && $_GET[C__CMDB__GET__OBJECTTYPE] > 0 || (isset($_POST[C__GET__ID]) && $_POST[C__GET__ID][0] > 0) || (isset($_POST['list_objtype_id']) && $_POST['list_objtype_id'] > 0))
                {
                    if ($_POST[C__GET__ID][0] > 0)
                    {
                        $_GET[C__CMDB__GET__OBJECTTYPE] = $_POST[C__GET__ID][0];
                    }
                    elseif ($_POST['list_objtype_id'] > 0)
                    {
                        $_GET[C__CMDB__GET__OBJECTTYPE] = $_POST['list_objtype_id'];
                    }

                    $l_dao                 = new isys_cmdb_dao_category_property($g_comp_database);
                    $l_g_distributor       = new isys_cmdb_dao_distributor($g_comp_database, 1, C__CMDB__CATEGORY__TYPE_GLOBAL);
                    $l_s_distributor       = new isys_cmdb_dao_distributor($g_comp_database, 1, C__CMDB__CATEGORY__TYPE_SPECIFIC);
                    $l_custom_dao          = new isys_cmdb_dao_category_g_custom_fields($g_comp_database);
                    $l_selected_properties = [];

                    /**
                     * @var $l_dao_objtype isys_cmdb_dao_object_type
                     */
                    $l_dao_objtype = isys_cmdb_dao_object_type::instance($g_comp_database);
                    $l_object_type = $l_dao_objtype->get_object_types($_GET[C__CMDB__GET__OBJECTTYPE])
                        ->get_row();

                    $l_is_allowed = isys_auth_cmdb_object_types::instance()
                        ->is_allowed_in_objecttype($_GET[C__CMDB__GET__OBJECTTYPE]);

                    if (!$l_is_allowed)
                    {
                        throw new isys_exception_auth(
                            _L(
                                'LC__AUTH__EXCEPTION__MISSING_RIGHTS_TO_EDIT_OBJECT_LIST',
                                _L($l_dao_objtype->get_objtype_name_by_id_as_string($_GET[C__CMDB__GET__OBJECTTYPE]))
                            )
                        );
                    } // if

                    isys_component_template_navbar::getInstance()
                        ->set_active(true, C__NAVBAR_BUTTON__SAVE)
                        ->set_active(false, C__NAVBAR_BUTTON__CANCEL);

                    if (isset($_POST['list__HIDDEN']) && $_POST[C__GET__NAVMODE] == C__NAVMODE__SAVE)
                    {
                        $l_condition         = '';
                        $l_query             = $l_dao->create_property_query_for_lists($_POST['list__HIDDEN_IDS'], $_GET[C__CMDB__GET__OBJECTTYPE]);
                        $l_props             = isys_format_json::decode($_POST['list__HIDDEN'], true);
                        $l_default_sorting   = (empty($_POST['default_sorting'])) ? false : $_POST['default_sorting'];
                        $l_sorting_direction = $_POST['sorting_direction'];
                        $l_keyName           = 0;
                        $l_row_clickable     = false;
                        $l_list_config       = [];

                        $l_tresor = [];
                        if (is_array($l_props) && count($l_props) > 0)
                        {
                            foreach ($l_props as $l_category_info)
                            {
                                foreach ($l_category_info as $l_cat_type => $l_category)
                                {
                                    foreach ($l_category as $l_cat_const => $l_selected_property)
                                    {
                                        if ($l_cat_type == 'g')
                                        {
                                            $l_category       = $l_g_distributor->get_category(constant($l_cat_const));
                                            $l_category_title = $l_category->get_catg_name_by_id_as_string(constant($l_cat_const));
                                        }
                                        else if ($l_cat_type == 's')
                                        {
                                            $l_category       = $l_s_distributor->get_category(constant($l_cat_const));
                                            $l_category_title = $l_category->get_cats_name_by_id_as_string(constant($l_cat_const));
                                        }
                                        else if ($l_cat_type == 'g_custom')
                                        {
                                            $l_category = $l_custom_dao;
                                            $l_category->set_catg_custom_id(constant($l_cat_const));
                                            $l_category_title = $l_category->get_cat_custom_name_by_id_as_string(constant($l_cat_const));
                                        } // if

                                        if (is_object($l_category) && method_exists($l_category, 'get_properties'))
                                        {
                                            $l_custom_id = '';

                                            if (strpos($l_selected_property[0], '_') !== 0)
                                            {
                                                $l_properties    = $l_category->get_properties();
                                                $l_method_info   = get_class($l_category) . '::get_properties';
                                                $l_property_type = C__PROPERTY_TYPE__STATIC;

                                                if ($l_cat_type == 'g_custom')
                                                {
                                                    $l_custom_id = $l_cat_const;
                                                } // if

                                                if (isset($l_properties[$l_selected_property[0]][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS]))
                                                {
                                                    $l_field = $l_properties[$l_selected_property[0]][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS];
                                                }
                                                else if (!isset($l_properties[$l_selected_property[0]][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES]))
                                                {
                                                    $l_field = $l_properties[$l_selected_property[0]][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
                                                }
                                                else
                                                {
                                                    $l_field = $l_properties[$l_selected_property[0]][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '__title';
                                                } // if

                                                if (get_class($l_category) === 'isys_cmdb_dao_category_g_custom_fields')
                                                {
                                                    $l_field = $l_properties[$l_selected_property[0]][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE] . '###' . constant(
                                                            $l_cat_const
                                                        );
                                                } // if

                                                $l_callback = false;
                                            }
                                            else
                                            {
                                                $l_properties    = $l_category->get_dynamic_properties();
                                                $l_method_info   = get_class($l_category) . '::get_dynamic_properties';
                                                $l_property_type = C__PROPERTY_TYPE__DYNAMIC;
                                                $l_field         = false;
                                                $l_callback      = [
                                                    get_class($l_properties[$l_selected_property[0]][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0]),
                                                    $l_properties[$l_selected_property[0]][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1]
                                                ];
                                            } // if

                                            /* Generate unique fieldname */
                                            ++$l_keyName;

                                            /* Store translated title and keynames */
                                            $l_tresor[_L($l_properties[$l_selected_property[0]][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE])][] = $l_keyName;

                                            $l_list_config[$l_keyName] = [
                                                $l_property_type,
                                                $l_selected_property[0],
                                                $l_field,
                                                $l_properties[$l_selected_property[0]][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE],
                                                $l_method_info,
                                                $l_callback,
                                                $l_category_title,
                                                $l_custom_id
                                            ];
                                        } // if
                                    } // foreach
                                } // foreach
                            } // foreach

                            // Walk through the tresor.
                            foreach ($l_tresor as $l_tres)
                            {
                                // Double title detected.
                                if (count($l_tres) > 1)
                                {
                                    // Add category title to the property title.
                                    if (!empty($l_list_config))
                                    {
                                        foreach ($l_tres AS $l_prop)
                                        {
                                            $l_list_config[$l_prop][3] = _L($l_list_config[$l_prop][3]) . " (" . _L($l_list_config[$l_prop][6]) . ")";
                                        } //foreach
                                    } // if
                                } // if
                            } // foreach

                            if (isset($_POST['row_clickable']) && $_POST['row_clickable'] == 'on')
                            {
                                $l_row_clickable = true;
                            } // if

                            // Check for given users (when overwriting configurations).
                            if ($_POST['as_default'] == '1')
                            {
                                $l_obj_type_const = $_POST['object_type'];

                                if (defined($l_obj_type_const))
                                {
                                    if (!empty($l_list_config))
                                    {
                                        isys_tenantsettings::set('cmdb.default-object-list.config.' . $l_obj_type_const, isys_format_json::encode($l_list_config));
                                    }
                                    isys_tenantsettings::set('cmdb.default-object-list.sql.' . $l_obj_type_const, $l_query . $l_condition);

                                    isys_notify::success(_L('LC__INFOBOX__DATA_WAS_SAVED'));
                                    die;
                                }
                                else
                                {
                                    isys_notify::error('The given constant "' . $l_obj_type_const . '" does not exist!', ['sticky' => true]);
                                    die;
                                } // if
                            } // if

                            $l_users = [null];

                            if ($_POST['for_users'] == '1' && isys_format_json::is_json_array($_POST['users']))
                            {
                                $l_users = isys_format_json::decode($_POST['users']);
                            } // if

                            foreach ($l_users as $l_user)
                            {
                                $l_dao_objtype->save_list_config(
                                    $_GET[C__CMDB__GET__OBJECTTYPE],
                                    isys_format_json::encode($l_list_config),
                                    $l_query . $l_condition,
                                    $l_row_clickable,
                                    $l_default_sorting,
                                    $l_sorting_direction,
                                    $l_user
                                );
                            } // foreach

                            $l_selected_properties = $_POST['list__HIDDEN'];

                            try
                            {
                                isys_notify::success(_L('LC__CMDB__OBJECT_LIST__SUCCESS'));
                            }
                            catch (isys_exception_database $e)
                            {
                                isys_notify::error($e->getMessage(), ['sticky' => true]);
                            } // try
                        }
                        else
                        {
                            isys_notify::error(_L('LC__REPORT__NO_ATTRIBUTES_ADDED'), ['sticky' => true]);
                        } // if
                    }
                    else
                    {
                        $l_class = $l_object_type['isys_obj_type__class_name'];

                        if (class_exists($l_class) && is_subclass_of($l_class, 'isys_cmdb_dao_list_objects'))
                        {
                            $l_list_dao = new $l_class($g_comp_database);
                        }
                        else
                        {
                            $l_list_dao = new isys_cmdb_dao_list_objects($g_comp_database);
                        } // if

                        // We set the object type, so that we know what we have to look for in the isys_obj_type_list table.
                        $l_list_dao->set_object_type($l_object_type);

                        // Is the row clickable?
                        $l_row_clickable     = $l_list_dao->activate_row_click();
                        $l_default_sorting   = $l_list_dao->get_default_sorting();
                        $l_sorting_direction = $l_list_dao->get_sorting_direction();

                        if ($_POST['sort'] == 'default_values')
                        {
                            $l_selection = $l_list_dao->get_list_config(true);
                        }
                        else
                        {
                            $l_selection = $l_list_dao->get_list_config();
                        } // if

                        if (is_array($l_selection) && count($l_selection) > 0)
                        {
                            foreach ($l_selection as $l_prop)
                            {
                                list(, $l_key, $l_field, , $l_property_callback,) = $l_prop;

                                $l_categoty_dao = explode('::', $l_property_callback);
                                $l_category_dao = $l_categoty_dao[0];

                                if (class_exists($l_category_dao))
                                {
                                    $l_category_dao = new $l_category_dao($g_comp_database);
                                    $l_category_id  = $l_category_dao->get_category_id();

                                    if (strpos($l_property_callback, 'isys_cmdb_dao_category_g_custom') === 0)
                                    {
                                        $l_cat_type = 'g_custom';
                                        list(, $l_category_id) = explode('###', $l_field);
                                    }
                                    elseif (strpos($l_property_callback, 'isys_cmdb_dao_category_g') === 0)
                                    {
                                        $l_cat_type = 'g';
                                    }
                                    else
                                    {
                                        $l_cat_type = 's';

                                        // Fixing a problem where it was not possible to re-edit the person properties in list configuration
                                        if ($l_category_id == C__CATS__PERSON)
                                        {
                                            $l_category_id = C__CATS__PERSON_MASTER;
                                        } // if

                                        // Fix a problem for person group. See ID-1217
                                        if ($l_category_id == C__CATS__PERSON_GROUP_MASTER)
                                        {
                                            $l_category_id = C__CATS__PERSON_GROUP;
                                        } // if

                                        // Fix a problem for organization. See ID-2780
                                        if ($l_category_id == C__CATS__ORGANIZATION_MASTER_DATA)
                                        {
                                            $l_category_id = C__CATS__ORGANIZATION;
                                        } // if
                                    } // if

                                    if ($l_category_id > 0)
                                    {
                                        $l_selected_properties[] = [
                                            $l_cat_type => [
                                                $l_category_id => [
                                                    $l_key
                                                ]
                                            ]
                                        ];
                                    } // if
                                } // if
                            } // foreach
                        } // if

                        $l_selected_properties = isys_format_json::encode($l_selected_properties);
                    } // if

                    // ID-2755 This should fix the bug.
                    $g_comp_template->activate_editmode();

                    $g_comp_template->assign('img_dir', $g_dirs['images'])
                        ->assign('selected_properties', $l_selected_properties)
                        ->assign('provides', C__PROPERTY__PROVIDES__LIST)
                        ->assign('objecttype', $l_object_type)
                        ->assign('list_obj_type_id', $_GET[C__CMDB__GET__OBJECTTYPE])
                        ->assign('row_clickable', $l_row_clickable)
                        ->assign('default_sorting', $l_default_sorting)
                        ->assign(
                            'sorting_data',
                            serialize(
                                [
                                    'asc'  => _L('LC__CMDB__SORTING__ASC'),
                                    'desc' => _L('LC__CMDB__SORTING__DESC')
                                ]
                            )
                        )
                        ->assign('defined_sorting', $l_sorting_direction)
                        ->assign('ajax_url', isys_helper_link::create_url($_GET))
                        ->assign(
                            'has_right_to_overwrite',
                            isys_auth_cmdb::instance()
                                ->is_allowed_to(isys_auth::EXECUTE, 'OVERWRITE_USER_LIST_CONFIG')
                        )
                        ->assign(
                            'has_right_to_define_standard',
                            isys_auth_cmdb::instance()
                                ->is_allowed_to(isys_auth::EXECUTE, 'DEFINE_STANDARD_LIST_CONFIG')
                        );
                }
                else
                {
                    isys_component_template_navbar::getInstance()
                        ->set_active(true, C__NAVBAR_BUTTON__EDIT);

                    $g_comp_template->assign('g_list', $this->get_object_type_list());
                } // if

                if (isset($l_object_type) && is_array($l_object_type) && !empty($l_object_type))
                {
                    $g_comp_template->assign('content_title', _L('LC__CMDB__TREE__SYSTEM__OBJECT_LIST') . ' &raquo ' . _L($l_object_type['isys_obj_type__title']));
                }
                else
                {
                    $g_comp_template->assign('content_title', _L('LC__CMDB__TREE__SYSTEM__OBJECT_LIST'));
                } // if

                $index_includes['contentbottomcontent'] = "modules/cmdb/list_config.tpl";
                break;

            case 'validation':
                if (defined('C__MODULE__PRO'))
                {
                    isys_auth_system_tools::instance()
                        ->validation(isys_auth::EXECUTE);

                    $l_lc_global   = _L('LC__UNIVERSAL__GLOBAL');
                    $l_lc_custom   = _L('LC__CMDB__CATG__CUSTOM_CATEGORY');
                    $l_lc_specific = _L('LC__UNIVERSAL__SPECIFIC');

                    $l_category_dialog = [
                        $l_lc_global   => [],
                        $l_lc_specific => [],
                        $l_lc_custom   => []
                    ];

                    // @todo  This array SHOULD only be used for some very tricky categories.
                    $l_category_blacklist = [
                        C__CMDB__CATEGORY__TYPE_GLOBAL   => [
                            C__CATG__IMAGES,
                            C__CATG__LOGBOOK
                        ],
                        C__CMDB__CATEGORY__TYPE_SPECIFIC => []
                    ];

                    /** @var  $l_prop_dao  isys_cmdb_dao_category_property */
                    $l_prop_dao = isys_cmdb_dao_category_property::instance($g_comp_database);

                    $l_categories = $l_prop_dao->get_all_categories(
                        [
                            isys_cmdb_dao_category_property::TYPE_EDIT,
                            isys_cmdb_dao_category_property::TYPE_FOLDER
                        ]
                    );

                    foreach ($l_categories[C__CMDB__CATEGORY__TYPE_GLOBAL] as $l_id => $l_catg)
                    {
                        if (in_array($l_id, $l_category_blacklist[C__CMDB__CATEGORY__TYPE_GLOBAL]))
                        {
                            continue;
                        } // if

                        $l_category_dialog[$l_lc_global]['g' . $l_id] = _L($l_catg['title']);
                    } // foreach

                    foreach ($l_categories[C__CMDB__CATEGORY__TYPE_SPECIFIC] as $l_id => $l_cats)
                    {
                        if (in_array($l_id, $l_category_blacklist[C__CMDB__CATEGORY__TYPE_SPECIFIC]))
                        {
                            continue;
                        } // if

                        $l_appendix  = '';
                        $l_obj_types = [];

                        $l_obj_type_rows = $l_prop_dao->get_object_types_by_category($l_id, 's', false, true);

                        foreach ($l_obj_type_rows as $l_obj_type)
                        {
                            $l_obj_types[] = _L($l_obj_type['isys_obj_type__title']);
                        } // foreach

                        if (count($l_obj_types) > 0)
                        {
                            $l_appendix = ' (' . implode(', ', $l_obj_types) . ')';
                        } // if

                        $l_category_dialog[$l_lc_specific]['s' . $l_id] = _L($l_cats['title']) . $l_appendix;
                    } // foreach

                    foreach ($l_categories[C__CMDB__CATEGORY__TYPE_CUSTOM] as $l_id => $l_catc)
                    {
                        $l_category_dialog[$l_lc_custom]['c' . $l_id] = _L($l_catc['title']);
                    } // foreach

                    // Sort the categories alphabetically.
                    asort($l_category_dialog[$l_lc_global]);
                    asort($l_category_dialog[$l_lc_specific]);
                    asort($l_category_dialog[$l_lc_custom]);

                    // Set some rules for the form elements.
                    $l_rules = [
                        'cmdb-validation-category-selector' => [
                            'p_arData'          => serialize($l_category_dialog),
                            'p_bInfoIconSpacer' => 0,
                            'p_bDbFieldNN'      => true,
                            'p_bSort'           => false,
                            'p_strSelectedID'   => 0
                        ],
                        'cmdb-validation-rules-template'    => [
                            'p_arData'          => [
                                'FILTER_VALIDATE_INT'    => _L('LC__SETTINGS__CMDB__VALIDATION__TYPE_INT'),
                                'FILTER_VALIDATE_FLOAT'  => _L('LC__SETTINGS__CMDB__VALIDATION__TYPE_FLOAT'),
                                'FILTER_VALIDATE_REGEXP' => _L('LC__SETTINGS__CMDB__VALIDATION__TYPE_REGEX'),
                                'FILTER_VALIDATE_EMAIL'  => _L('LC__SETTINGS__CMDB__VALIDATION__TYPE_EMAIL'),
                                'FILTER_VALIDATE_URL'    => _L('LC__SETTINGS__CMDB__VALIDATION__TYPE_URL'),
                                'VALIDATE_BY_TEXTFIELD'  => _L('LC__SETTINGS__CMDB__VALIDATION__BY_TEXTFIELD')
                            ],
                            'p_bInfoIconSpacer' => 0,
                            'p_strClass'        => 'input input-small rule-selector ml5 mt5',
                            'p_bSort'           => false
                        ]
                    ];

                    // Load the defined configurations.
                    $l_configured_categories = [];
                    $l_validation_rows       = isys_cmdb_dao_validation::instance($g_comp_database)
                        ->get_data();

                    if (count($l_validation_rows))
                    {
                        while ($l_row = $l_validation_rows->get_row())
                        {
                            $l_catg_id = $l_row['isys_validation_config__isysgui_catg__id'];
                            $l_cats_id = $l_row['isys_validation_config__isysgui_cats__id'];
                            $l_catc_id = $l_row['isys_validation_config__isysgui_catg_custom__id'];

                            // We just need the configured category IDs.
                            if ($l_catg_id > 0 && isset($l_category_dialog[$l_lc_global]['g' . $l_catg_id]))
                            {
                                $l_configured_categories[0 . _L($l_prop_dao->get_catg_name_by_id_as_string($l_catg_id))] = 'g' . $l_catg_id;
                            } // if

                            if ($l_cats_id > 0 && isset($l_category_dialog[$l_lc_specific]['s' . $l_cats_id]))
                            {
                                $l_configured_categories[1 . _L($l_prop_dao->get_cats_name_by_id_as_string($l_cats_id))] = 's' . $l_cats_id;
                            } // if

                            if ($l_catc_id > 0 && isset($l_category_dialog[$l_lc_custom]['c' . $l_catc_id]))
                            {
                                $l_configured_categories[2 . $l_prop_dao->get_cat_custom_name_by_id_as_string($l_catc_id)] = 'c' . $l_catc_id;
                            } // if
                        }  // while
                    } // if

                    ksort($l_configured_categories);

                    isys_component_template_navbar::getInstance()
                        ->set_active(true, C__NAVBAR_BUTTON__SAVE);

                    $g_comp_template->activate_editmode()
                        ->assign('configured_categories', array_values($l_configured_categories))
                        ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);

                    $index_includes['contentbottomcontent'] = $g_dirs['class'] . 'modules/pro/templates/modules/cmdb/validation_config.tpl';
                } // if
        } // switch

        return null;
    } // function

    /**
     * @param $p_popuptype
     *
     * @return bool
     */
    private function handle_popups($p_popuptype)
    {
        global $g_output_done;
        global $g_dirs;
        global $g_config;

        $l_popupcls = "isys_popup_" . $p_popuptype;

        if (class_exists($l_popupcls))
        {
            /**
             * @var isys_component_popup
             */
            $l_popup = new $l_popupcls;
            $l_gets  = $this->m_modreq->get_gets();

            if (isset($l_gets[C__GET__AJAX_REQUEST]) && method_exists($l_popup, "handle_ajax_request"))
            {
                try
                {
                    // Return the contents while dying.
                    die($l_popup->handle_ajax_request($this->m_modreq));
                }
                catch (Exception $e)
                {
                    die($e->getMessage());
                }
            }

            $l_popuptpl = $l_popup->handle_module_request($this->m_modreq)
                ->assign("dir_tools", $g_config["www_dir"] . "src/tools/")
                ->assign("dir_browser", $g_config["www_dir"] . "src/tools/browser/")
                ->assign("dir_images", $g_dirs["images"])
                ->assign("dir_theme_images", $g_dirs["theme_images"]);

            if ($l_popuptpl != null)
            {
                $l_popuptpl->display("popup/main.tpl");
                $g_output_done = true;

                return true;
            } // if
        } // if

        return false;
    } // function

    /**
     * @param $p_reqtype
     *
     * @throws Exception
     * @throws isys_exception_cmdb
     */
    private function handle_file_manager($p_reqtype)
    {
        global $g_dirs;

        try
        {
            $l_gets         = $this->m_modreq->get_gets();
            $l_file_manager = new isys_component_filemanager();

            switch ($p_reqtype)
            {
                case "image":
                    $l_image_url = $l_gets["file"];
                    $l_file_manager->set_upload_path($g_dirs["fileman"]["image_dir"]);

                    /**
                     * send directly outputs the file to the client
                     */
                    if (!$l_file_manager->send($l_image_url, null, C_FILES__MODE_VIEW))
                    {
                        header("HTTP/1.0 404 Not Found");
                        die;
                    }
                    break;
                case "get":
                    if (isset($l_gets[C__GET__FILE__ID]))
                    {
                        $l_physical_id = $l_gets[C__GET__FILE__ID];
                        $l_filename    = null;
                        $l_files       = null;
                        $l_download    = false;

                        if (!empty($l_gets[C__GET__FILE_NAME]))
                        {
                            $l_filename = $l_gets[C__GET__FILE_NAME];
                        }
                        else
                        {
                            $l_dao_file = new isys_cmdb_dao_category_s_file($this->m_dao_cmdb->get_database_component());
                            $l_files    = $l_dao_file->get_filemanager_dao_by_physical_file_id($l_physical_id);
                        }

                        /**
                         * send directly outputs the file to the client
                         */
                        if (!$l_file_manager->send($l_filename, $l_files, C_FILES__MODE_DOWNLOAD))
                        {
                            header("HTTP/1.0 404 Not Found");
                            die;
                        }
                    }
                    break;
            }
        }
        catch (isys_exception_cmdb $e)
        {
            throw $e;
        }
    } // function

    /**
     * Conforms the request to mandatory and optional GET-Parameters
     * to care for a "clean" navigation. Yes, we have too many. :-)
     *
     * @param array $p_parameters_mandatory
     * @param array $p_parameters_optional
     *
     * @return integer
     */
    private function request_conformer($p_parameters_mandatory, $p_parameters_optional)
    {
        $l_parameters_result = [];
        $l_result_count      = 0;
        $l_gets              = $this->m_modreq->get_gets();

        /* Process mandatory parameters */
        if (count($p_parameters_mandatory))
        {
            /* Filter out necessary parameters */
            while (list($l_pkey,) = each($p_parameters_mandatory))
            {
                if (isset($l_gets[$l_pkey]))
                {
                    $l_parameters_result[$l_pkey] = $l_gets[$l_pkey];
                    $l_result_count++;
                }
                else
                {
                    throw new isys_exception_cmdb(
                        "Request or navigation error.\n" . "Current GET-parameters are: \n" . var_export($l_gets, true) . "\n" . "Mandatory parameters are:\n" . var_export(
                            $p_parameters_mandatory,
                            true
                        )
                    );
                }
            }
        }

        /* Process optional parameters */
        if (count($p_parameters_optional))
        {
            while (list($l_pkey,) = each($p_parameters_optional))
            {
                if (isset($l_gets[$l_pkey]))
                {
                    $l_parameters_result[$l_pkey] = $l_gets[$l_pkey];
                    $l_result_count++;
                }
            }

            /* Since these parameters are optional, there is no Exception */
        }

        /* Conform GET-Parameters result parameters */
        $this->m_modreq->_internal_set_private("m_get", $l_parameters_result);

        /* And return count of new parameters (this is the same as
           count($this->m_modreq->get_gets()) */

        return $l_result_count;
    } // function

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->m_view_manager = null;
    } // function
} // class