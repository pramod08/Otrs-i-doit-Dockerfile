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
 * Template module
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */

define("TPL_PID__EXISTING", 1);
define("TPL_PID__NEW", 2);
define("TPL_PID__NEW_OBJET", 3);
define("TPL_PID__SETTINGS", 4);
define('TPL_PID__MASS_CHANGE', 5);

class isys_module_templates extends isys_module implements isys_module_interface, isys_module_authable
{
    const DISPLAY_IN_MAIN_MENU = true;

    // Define, if this module shall be displayed in the named menus.
    const DISPLAY_IN_SYSTEM_MENU = true;
    /**
     * Skip these categories for export.
     *
     * @var  array
     */
    protected static $m_cat_skips = [
        C__CMDB__CATEGORY__TYPE_GLOBAL   => [
            C__CATG__LOGBOOK,
            C__CATG__OVERVIEW,
            C__CMDB__SUBCAT__NETWORK_PORT_OVERVIEW,
            C__CATG__STORAGE,
            C__CATG__SANPOOL,
            C__CATG__ITS_LOGBOOK,
            C__CATG__CABLING,
            C__CATG__CLUSTER_VITALITY,
            C__CATG__CLUSTER_SHARED_VIRTUAL_SWITCH,
            C__CATG__CLUSTER_SHARED_STORAGE,
            C__CATG__RELATION,
            C__CATG__COMPUTING_RESOURCES,
            C__CATG__IT_SERVICE_RELATIONS,
            C__CATG__OBJECT_VITALITY,
            C__CATG__RACK_VIEW,
            C__CMDB__SUBCAT__LICENCE_OVERVIEW
        ],
        C__CMDB__CATEGORY__TYPE_SPECIFIC => [
            C__CATS__RELATION_DETAILS,
            C__CATS__CHASSIS_VIEW,
            C__CATS__PDU_OVERVIEW
        ]
    ];
    /**
     * @var  boolean
     */
    protected static $m_licenced = true;
    protected $m_db;
    /**
     *
     */
    private $m_rec_status = C__RECORD_STATUS__TEMPLATE;
    /**
     * The isys_module_request instance.
     *
     * @var  isys_module_request
     */
    private $m_userrequest;

    /**
     * Method for retrieving any module specific additional links.
     *
     * @static
     * @return  array
     */
    public static function get_additional_links()
    {
        global $g_dirs;

        $l_return = [];

        if (defined('C__MODULE__PRO'))
        {
            $l_return['MASS_CHANGES'] = [
                'LC__MASS_CHANGE',
                isys_helper_link::create_url(
                    [
                        C__GET__MODULE_ID => C__MODULE__TEMPLATES,
                        C__GET__MODULE    => C__MODULE__CMDB
                    ]
                ),
                C__MODULE__CMDB,
                // Module parent
                $g_dirs['images'] . 'icons/silk/table_row_insert.png',
                // Sub module icon
            ];
        } // if

        return $l_return;
    } // function

    /**
     * @param null $p_mod
     *
     * @return string
     */
    public static function get_module_title($p_mod = null)
    {
        switch ($p_mod)
        {
            case C__MODULE__CMDB:
                return 'LC__MASS_CHANGE';
                break;
            default:
                return 'LC__MODULE__TEMPLATES';
                break;
        } // switch
    } // function

    /**
     * Get related auth class for module
     *
     * @author Selcuk Kekec <skekec@i-doit.com>
     * @return isys_auth
     */
    public static function get_auth()
    {
        return isys_auth_templates::instance();
    } // function

    /**
     * This method builds the tree for the menu.
     *
     * @param   isys_component_tree $p_tree
     * @param   boolean             $p_system_module
     * @param   integer             $p_parent
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @since   0.9.9-7
     * @see     isys_module::build_tree()
     */
    public function build_tree(isys_component_tree $p_tree, $p_system_module = true, $p_parent = null)
    {
        global $g_comp_template_language_manager;

        $l_parent    = -1;
        $l_submodule = '';
        $p_tree->set_tree_sort(false);

        if ($p_system_module)
        {
            $l_parent    = $p_tree->find_id_by_title('Modules');
            $l_submodule = '&' . C__GET__MODULE_SUB_ID . '=' . C__MODULE__TEMPLATES;
        } // if

        if (null !== $p_parent && is_int($p_parent))
        {
            $l_root = $p_parent;
        }
        else
        {
            if ($_GET[C__GET__MODULE] == C__MODULE__CMDB)
            {
                if (defined('C__MODULE__PRO'))
                {
                    $l_root = $p_tree->add_node(
                        C__MODULE__TEMPLATES . '0',
                        $l_parent,
                        _L('LC__MASS_CHANGE')
                    );
                }
            }
            else
            {
                $l_root = $p_tree->add_node(
                    C__MODULE__TEMPLATES . '0',
                    $l_parent,
                    'Templates'
                );
            }
        } // if

        if (!$p_system_module)
        {
            if ($_GET[C__GET__MODULE] == C__MODULE__CMDB)
            {
                if (defined('C__MODULE__PRO'))
                {
                    $p_tree->add_node(
                        C__MODULE__TEMPLATES . TPL_PID__MASS_CHANGE,
                        $l_root,
                        $g_comp_template_language_manager->{'LC__MASS_CHANGE'},
                        '?' . C__GET__MODULE_ID . '=' . $_GET[C__GET__MODULE_ID] . '&' . C__GET__MODULE . '=' . C__MODULE__CMDB . '&' . C__GET__TREE_NODE . '=' . C__MODULE__TEMPLATES . TPL_PID__MASS_CHANGE . '&' . C__GET__SETTINGS_PAGE . '=' . TPL_PID__MASS_CHANGE,
                        '',
                        '',
                        (($_GET[C__GET__SETTINGS_PAGE] == '1') ? 1 : 0),
                        '',
                        '',
                        isys_auth_templates::instance()
                            ->is_allowed_to(isys_auth::EXECUTE, 'MASS_CHANGES/' . C__MODULE__TEMPLATES . TPL_PID__MASS_CHANGE)
                    );

                    $p_tree->add_node(
                        C__MODULE__TEMPLATES . TPL_PID__NEW,
                        $l_root,
                        $g_comp_template_language_manager->{'LC__MASS_CHANGE__CREATE_NEW_TEMPLATE'},
                        '?' . C__GET__MODULE_ID . '=' . $_GET[C__GET__MODULE_ID] . '&' . C__GET__MODULE . '=' . C__MODULE__CMDB . '&' . C__GET__TREE_NODE . '=' . C__MODULE__TEMPLATES . TPL_PID__NEW . '&' . C__GET__SETTINGS_PAGE . '=' . TPL_PID__NEW,
                        '',
                        '',
                        (($_GET[C__GET__SETTINGS_PAGE] == '1') ? 1 : 0),
                        '',
                        '',
                        isys_auth_templates::instance()
                            ->is_allowed_to(isys_auth::EXECUTE, 'MASS_CHANGES/' . C__MODULE__TEMPLATES . TPL_PID__NEW)
                    );

                    $p_tree->add_node(
                        C__MODULE__TEMPLATES . TPL_PID__EXISTING,
                        $l_root,
                        $g_comp_template_language_manager->{'LC__MASS_CHANGE__EXISTING_TEMPLATES'},
                        '?' . C__GET__MODULE_ID . '=' . $_GET[C__GET__MODULE_ID] . '&' . C__GET__MODULE . '=' . C__MODULE__CMDB . '&' . C__GET__TREE_NODE . '=' . C__MODULE__TEMPLATES . TPL_PID__EXISTING . '&' . C__GET__SETTINGS_PAGE . '=' . TPL_PID__EXISTING,
                        '',
                        '',
                        (($_GET[C__GET__SETTINGS_PAGE] == '1') ? 1 : 0),
                        '',
                        '',
                        isys_auth_templates::instance()
                            ->is_allowed_to(isys_auth::VIEW, 'MASS_CHANGES/' . C__MODULE__TEMPLATES . TPL_PID__EXISTING)
                    );
                }
            }
            else
            {
                $p_tree->add_node(
                    C__MODULE__TEMPLATES . TPL_PID__NEW_OBJET,
                    $l_root,
                    $g_comp_template_language_manager->{'LC__TEMPLATES__CREATE_OBJECTS'},
                    '?' . C__GET__MODULE_ID . '=' . $_GET[C__GET__MODULE_ID] . $l_submodule . '&' . C__GET__TREE_NODE . '=' . C__MODULE__TEMPLATES . TPL_PID__NEW_OBJET . '&' . C__GET__SETTINGS_PAGE . '=' . TPL_PID__NEW_OBJET,
                    '',
                    '',
                    (($_GET[C__GET__SETTINGS_PAGE] == '1') ? 1 : 0),
                    '',
                    '',
                    isys_auth_templates::instance()
                        ->is_allowed_to(isys_auth::EXECUTE, 'TEMPLATES/' . C__MODULE__TEMPLATES . TPL_PID__NEW_OBJET)
                );

                $p_tree->add_node(
                    C__MODULE__TEMPLATES . TPL_PID__NEW,
                    $l_root,
                    $g_comp_template_language_manager->{'LC__TEMPLATES__NEW_TEMPLATE'},
                    '?' . C__GET__MODULE_ID . '=' . $_GET[C__GET__MODULE_ID] . $l_submodule . '&' . C__GET__TREE_NODE . '=' . C__MODULE__TEMPLATES . TPL_PID__NEW . '&' . C__GET__SETTINGS_PAGE . '=' . TPL_PID__NEW,
                    '',
                    '',
                    (($_GET[C__GET__SETTINGS_PAGE] == '1') ? 1 : 0),
                    '',
                    '',
                    isys_auth_templates::instance()
                        ->is_allowed_to(isys_auth::EXECUTE, 'TEMPLATES/' . C__MODULE__TEMPLATES . TPL_PID__NEW)
                );

                $p_tree->add_node(
                    C__MODULE__TEMPLATES . TPL_PID__EXISTING,
                    $l_root,
                    $g_comp_template_language_manager->{'LC__TEMPLATES__EXISTING_TEMPLATES'},
                    '?' . C__GET__MODULE_ID . '=' . $_GET[C__GET__MODULE_ID] . $l_submodule . '&' . C__GET__TREE_NODE . '=' . C__MODULE__TEMPLATES . TPL_PID__EXISTING . '&' . C__GET__SETTINGS_PAGE . '=' . TPL_PID__EXISTING,
                    '',
                    '',
                    (($_GET[C__GET__SETTINGS_PAGE] == '1') ? 1 : 0),
                    '',
                    '',
                    isys_auth_templates::instance()
                        ->is_allowed_to(isys_auth::VIEW, 'TEMPLATES/' . C__MODULE__TEMPLATES . TPL_PID__EXISTING)
                );
            }
        } // if

        if ($p_system_module)
        {
            $p_tree->add_node(
                C__MODULE__TEMPLATES . '4',
                $l_root,
                $g_comp_template_language_manager->{'LC__CATS__AC_SETTINGS'},
                '?' . C__GET__MODULE_ID . '=' . $_GET[C__GET__MODULE_ID] . $l_submodule . '&' . C__GET__TREE_NODE . '=' . C__MODULE__TEMPLATES . '4' . '&' . C__GET__SETTINGS_PAGE . '=4'
            );
        } // if
    } // function

    /**
     * Retrieves a bookmark string for mydoit.
     *
     * @param    string $p_text
     * @param    string $p_link
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.org>
     * @return   boolean
     */
    public function mydoit_get(&$p_text, &$p_link)
    {
        $l_url_exploded        = explode('?', $_SERVER['HTTP_REFERER']);
        $l_url_parameters      = $l_url_exploded[1];
        $l_parameters_exploded = explode('&', $l_url_parameters);

        $l_params = array_pop(
            array_map(
                function ($p_arg)
                {
                    $l_return = [];
                    foreach ($p_arg AS $l_content)
                    {
                        list($l_key, $l_value) = explode('=', $l_content);
                        $l_return[$l_key] = $l_value;
                    } // foreach

                    return $l_return;
                },
                [$l_parameters_exploded]
            )
        );

        if (isset($l_params[C__GET__MODULE]))
        {
            // Mass changes
            $p_text[] = _L('LC__MASS_CHANGE');

            switch ($l_params[C__GET__SETTINGS_PAGE])
            {
                case TPL_PID__MASS_CHANGE:
                    // Do nothing.
                    break;

                case TPL_PID__NEW:
                    $p_text[] = _L('LC__MASS_CHANGE__CREATE_NEW_TEMPLATE');
                    break;

                case TPL_PID__EXISTING:
                default:
                    $p_text[] = _L('LC__MASS_CHANGE__EXISTING_TEMPLATES');
                    break;
            } // switch
        }
        else
        {
            // Templates.
            $p_text[] = _L('LC__MODULE__TEMPLATES');
            switch ($l_params[C__GET__SETTINGS_PAGE])
            {
                case TPL_PID__NEW:
                    $p_text[] = _L('LC__TEMPLATES__NEW_TEMPLATE');
                    break;

                case TPL_PID__NEW_OBJET:
                    $p_text[] = _L('LC__TEMPLATES__CREATE_OBJECTS');
                    break;

                case TPL_PID__EXISTING:
                default:
                    $p_text[] = _L('LC__TEMPLATES__EXISTING_TEMPLATES');
                    break;
            } // switch
        } // if

        $p_link = $l_url_parameters;

        return true;
    } // function

    /**
     * Starts module process
     */
    public function start()
    {
        global $index_includes;

        // Unpack request package.
        $l_gets     = $this->m_userrequest->get_gets();
        $l_posts    = $this->m_userrequest->get_posts();
        $l_template = $this->m_userrequest->get_template();

        if ($_GET[C__GET__MODULE_ID] != C__MODULE__SYSTEM)
        {
            $l_tree = $this->m_userrequest->get_menutree();

            // Process tree.
            $this->build_tree($l_tree, false);

            // Assign tree.
            $l_template->assign("menu_tree", $l_tree->process($_GET[C__GET__TREE_NODE]));
        } // if

        try
        {
            $this->process($l_gets, $l_posts);
        }
        catch (isys_exception_general $e)
        {
            throw $e;
        }
        catch (isys_exception_auth $e)
        {
            $l_template->assign("exception", $e->write_log());
            $index_includes['contentbottomcontent'] = "exception-auth.tpl";
        } // try
    } // function

    /**
     * Initializes the module
     *
     * @param   isys_module_request & $p_req
     *
     * @return  boolean
     */
    public function init(isys_module_request $p_req)
    {
        if (is_object($p_req))
        {
            $this->m_userrequest = &$p_req;

            return true;
        } // if

        return false;
    } // function

    /**
     * Method for retrieving the module-ID.
     *
     * @return  integer
     */
    public function get_module_id()
    {
        return C__MODULE__TEMPLATES;
    }

    /**
     * Method for replacing all placeholders.
     *
     * @param string  $p_xml
     * @param string  $p_title
     * @param string  $p_type
     * @param integer $p_id
     * @param string  $p_sysid
     * @param string  $p_created
     * @param string  $p_created_by
     * @param string  $p_updated
     * @param string  $p_updated_by
     */
    public function modify_xml_header($p_xml, $p_title, $p_type, $p_id = null, $p_sysid = null, $p_created = null, $p_created_by = null, $p_updated = null, $p_updated_by = null, $p_description = null, $p_cmdb_status = null, $p_status = C__RECORD_STATUS__NORMAL)
    {
        global $g_comp_database, $g_comp_template_language_manager;

        $l_modified = $p_xml;

        if (!empty($p_title) && !empty($p_type) && !empty($p_xml))
        {

            $l_dao = isys_cmdb_dao::instance($g_comp_database);
            $l_res = $l_dao->get_objtype($p_type);
            $l_row = $l_res->get_row();
            unset($l_res);
            $l_res  = $l_dao->get_object_group_by_id($l_row['isys_obj_type__isys_obj_type_group__id']);
            $l_row2 = $l_res->get_row();

            /* DEFAULTS */
            if (!empty($p_id))
            {
                $l_res3 = $l_dao->get_object_by_id($p_id);
                if ($l_res3->num_rows() > 0)
                {
                    $l_row3        = $l_res3->get_row();
                    $p_created     = $l_row3['isys_obj__created'];
                    $p_created_by  = $l_row3['isys_obj__created_by'];
                    $p_updated     = $l_row3['isys_obj__updated'];
                    $p_updated_by  = $l_row3['isys_obj__updated_by'];
                    $p_sysid       = $l_row3['isys_obj__sysid'];
                    $p_cmdb_status = $l_row3['isys_obj__isys_cmdb_status__id'];

                    if (empty($p_cmdb_status)) $p_cmdb_status = $l_row3["isys_obj__isys_cmdb_status__id"];

                    if (empty($p_status)) $p_status = $l_row3["isys_obj__status"];
                }
            }

            if (empty($p_id)) $p_id = 42424242;
            if (empty($p_created)) $p_created = date('Y-m-d H:i:s');
            if (empty($p_created_by)) $p_created_by = $_SESSION['username'];
            if (empty($p_updated)) $p_updated = date('Y-m-d H:i:s');
            if (empty($p_updated_by)) $p_updated_by = $_SESSION['username'];

            if (empty($p_sysid))
            {

                $l_sysid_prefix = (!empty($l_row['isys_obj_type__sysid_prefix'])) ? $l_row['isys_obj_type__sysid_prefix'] : C__CMDB__SYSID__PREFIX;
                $l_sysid_suffix = ($l_sysid_prefix == C__CMDB__SYSID__PREFIX) ? time() : ($l_dao->get_last_obj_id_from_type() + 1);

                $p_sysid = $l_sysid_prefix . $l_sysid_suffix;

                if (strlen($p_sysid) < 13)
                {
                    $l_zeros = '';
                    for ($i = 0;$i < (13 - strlen($p_sysid));$i++)
                    {
                        $l_zeros .= '0';
                    }
                    $l_sysid_suffix = $l_zeros . $l_sysid_suffix;
                    $p_sysid        = $l_sysid_prefix . $l_sysid_suffix;
                }

                // generate sysid till its unique
                if ($l_sysid_prefix == C__CMDB__SYSID__PREFIX)
                {
                    $l_counter = 1;
                    while ($l_dao->get_obj_id_by_sysid($p_sysid))
                    {
                        $p_sysid = $l_sysid_prefix . ($l_sysid_suffix + $l_counter);
                        $l_counter++;
                    }
                }

            }

            if (is_array($l_modified))
            {
                foreach ($l_modified AS $l_key => $l_value)
                {
                    if (!is_array($l_value))
                    {
                        $l_modified[$l_key] = str_replace('%TITLE%', $p_title, $l_value);
                        $l_modified[$l_key] = str_replace('%OBJTYPEID%', $p_type, $l_value);
                        $l_modified[$l_key] = str_replace('%ID%', $p_id, $l_value);
                        $l_modified[$l_key] = str_replace('%SYSID%', $p_sysid, $l_value);
                        $l_modified[$l_key] = str_replace('%CREATED%', $p_created, $l_value);
                        $l_modified[$l_key] = str_replace('%CREATEDBY%', $p_created_by, $l_value);
                        $l_modified[$l_key] = str_replace('%UPDATED%', $p_updated, $l_value);
                        $l_modified[$l_key] = str_replace('%DESCRIPTION%', $p_description, $l_value);
                        $l_modified[$l_key] = str_replace('%UPDATEDBY%', $p_updated_by, $l_value);
                        $l_modified[$l_key] = str_replace('%OBJTYPECONST%', $l_row['isys_obj_type__const'], $l_value);
                        $l_modified[$l_key] = str_replace('%OBJTYPETITLE%', $g_comp_template_language_manager->get($l_row['isys_obj_type__title']), $l_value);
                        $l_modified[$l_key] = str_replace('%OBJTYPELANG%', $l_row['isys_obj_type__title'], $l_value);
                        $l_modified[$l_key] = str_replace('%OBJTYPEGROUP%', $l_row2['isys_obj_type_group__const'], $l_value);
                        $l_modified[$l_key] = str_replace('%STATUS%', $p_status, $l_value);
                        $l_modified[$l_key] = str_replace('%CMDB_STATUS%', $p_cmdb_status, $l_value);
                    }
                }
            }
            else
            {
                $l_modified = str_replace('%TITLE%', $p_title, $l_modified);
                $l_modified = str_replace('%OBJTYPEID%', $p_type, $l_modified);
                $l_modified = str_replace('%ID%', $p_id, $l_modified);
                $l_modified = str_replace('%SYSID%', $p_sysid, $l_modified);
                $l_modified = str_replace('%CREATED%', $p_created, $l_modified);
                $l_modified = str_replace('%CREATEDBY%', $p_created_by, $l_modified);
                $l_modified = str_replace('%UPDATED%', $p_updated, $l_modified);
                $l_modified = str_replace('%DESCRIPTION%', $p_description, $l_modified);
                $l_modified = str_replace('%UPDATEDBY%', $p_updated_by, $l_modified);
                $l_modified = str_replace('%OBJTYPECONST%', $l_row['isys_obj_type__const'], $l_modified);
                $l_modified = str_replace('%OBJTYPETITLE%', $g_comp_template_language_manager->get($l_row['isys_obj_type__title']), $l_modified);
                $l_modified = str_replace('%OBJTYPELANG%', $l_row['isys_obj_type__title'], $l_modified);
                $l_modified = str_replace('%OBJTYPEGROUP%', $l_row2['isys_obj_type_group__const'], $l_modified);
                $l_modified = str_replace('%OBJTYPESYSIDPREFIX%', $l_row['isys_obj_type__sysid_prefix'], $l_modified);
                $l_modified = str_replace('%STATUS%', $p_status, $l_modified);
                $l_modified = str_replace('%CMDB_STATUS%', $p_cmdb_status, $l_modified);
            }
        }

        return $l_modified;
    } // function

    /**
     * Creates object by template.
     *
     * @param   array   $p_templates       Array of templates to use; format: array(1,2,3,4).
     * @param   integer $p_object_type     Object type id to create.
     * @param   string  $p_title           Object title.
     * @param   integer $p_obj_id          Use template to the given Object ID if exists.
     * @param   boolean $p_html_output     Output debug messages as html, if false, no output is made.
     * @param   integer $p_count           Amount of objects to create.
     * @param   string  $p_suffix_type
     * @param   string  $p_category
     * @param   string  $p_purpose
     * @param   string  $p_append_to_title Appends this string to the object title if count > 1; Placeholder "##COUNT##" stands for the current iterator.
     * @param   integer $p_start_at
     * @param   integer $p_zero_point_calc
     * @param   integer $p_zero_points
     *
     * @return  bool|mixed|null
     * @throws  isys_exception_cmdb
     * @throws  isys_exception_template
     */
    public function create_from_template($p_templates, $p_object_type, $p_title, $p_obj_id = null, $p_html_output = true, $p_count = 1, $p_suffix_type = '', $p_category = null, $p_purpose = null, $p_append_to_title = '##COUNT##', $p_start_at = 0, $p_zero_point_calc = 0, $p_zero_points = 0)
    {
        global $g_comp_template, $g_comp_session, $g_comp_template_language_manager;

        $l_mod_event_manager = isys_event_manager::getInstance();

        if (isys_tenantsettings::get('cmdb.registry.sanitize_input_data', 1))
        {
            $p_title = isys_helper::sanitize_text($p_title);
        } // if

        $g_comp_session->write_close();
        set_time_limit(60 * 60 * 24);

        // Start logging.
        $l_log = isys_factory_log::get_instance('template');
        $l_log->set_verbose_level(isys_log::C__NONE);

        $l_object_id      = null;
        $l_template_names = null;

        if ($p_html_output)
        {
            // Stop output buffering.
            ob_end_clean();
            ob_flush();

            echo "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"de\" lang=\"de\">";
            $g_comp_template->display("head.tpl");
            echo "<div class=\"p10\">";
        } // if

        $l_dao = new isys_cmdb_dao($this->m_db);

        // Check if the user chose a template.
        if (empty($p_templates))
        {
            throw new isys_exception_template($g_comp_template_language_manager->get('LC__TEMPLATES__NO_TEMPLATE_CHOSEN'));
        } // if

        // Check if he chose an object-type.
        if (empty($p_object_type))
        {
            throw new isys_exception_template($g_comp_template_language_manager->get('LC__TEMPLATES__NO_OBJECT_TYPE_CHOSEN'));
        } // if

        // Check if the user gave us a title.
        if (empty($p_title))
        {
            throw new isys_exception_template($g_comp_template_language_manager->get('LC__TEMPLATES__NO_TITLE_GIVEN'));
        } // if

        // Export module check.
        if (defined("C__MODULE__EXPORT") && class_exists("isys_module_export"))
        {
            // Export type check.
            $l_export_type = "isys_export_type_xml";
            if (class_exists($l_export_type))
            {

                // Import module check.
                if (class_exists("isys_import_handler_cmdb"))
                {
                    // Init.
                    $l_export     = new isys_export_cmdb_object($l_export_type, $this->m_db);
                    $l_import     = new isys_import_handler_cmdb($l_log, $this->m_db);
                    $l_import_dao = new isys_module_dao_import_log($this->m_db);
                    $l_overwrite  = false;

                    if ($p_obj_id > 0)
                    {
                        $l_import->set_mode($l_import::C__OVERWRITE);
                        $l_overwrite = true;
                    }
                    else
                    {
                        $l_import->set_mode(isys_import_handler_cmdb::C__APPEND);
                    } // if

                    // Skip the following categories.
                    foreach (self::$m_cat_skips as $l_skip_type => $l_skip_cat)
                    {
                        $l_export->add_skip($l_skip_cat, $l_skip_type);
                    }

                    if (!is_numeric($p_count) || $p_count < 1)
                    {
                        $p_count = 1;
                    } // if

                    $l_xml_template = $l_export->export($p_templates, $this->get_export_cats(), C__RECORD_STATUS__TEMPLATE, false, true)
                        ->parse()
                        ->get_export();

                    $l_start = ($p_start_at > 0) ? $p_start_at : 0;

                    isys_event_manager::getInstance()
                        ->set_import_id(
                            $l_import_dao->add_import_entry(
                                $l_import_dao->get_import_type_by_const('C__IMPORT_TYPE__TEMPLATE'),
                                $p_count . ' object(s) created by the template module'
                            )
                        );

                    // Fixes ID-2087
                    $p_title = htmlspecialchars($p_title);

                    for ($i = $l_start;$i < $p_count + $l_start;$i++)
                    {
                        $l_import->set_prepared_data([]);
                        // Title formatting.
                        $l_counter = "";
                        $l_title   = $p_title;

                        if ($p_suffix_type != '' && $p_count > 1)
                        {
                            if ($p_zero_point_calc == "1" && $p_zero_points > 0)
                            {
                                for ($n = strlen(strval($i));$n <= $p_zero_points;$n++)
                                {
                                    $l_counter .= "0";
                                } // for
                            } // if

                            $l_counter .= $i;

                            if ($p_suffix_type == '-1')
                            {
                                $l_title .= str_replace('##COUNT##', $l_counter, $p_append_to_title);
                            }
                            else
                            {
                                $l_title .= $l_counter;
                            }
                        }

                        if (!$l_overwrite)
                        {
                            $p_obj_id = $l_dao->get_last_id_from_table('isys_obj');
                            $p_obj_id++;
                        } // if

                        // If object type is null or does not exist, use object type SERVER instead.
                        if (!is_numeric($p_object_type) || $p_object_type < 0 || $l_dao->get_objtype($p_object_type)
                                ->num_rows() <= 0
                        )
                        {
                            $p_object_type = C__OBJTYPE__SERVER;
                        } // if

                        $this->println($g_comp_template_language_manager->get("LC__TEMPLATES__APPLYING") . "&hellip;", $p_html_output);
                        $l_modified_template = $this->modify_xml_header(
                            $l_xml_template,
                            $l_title,
                            $p_object_type,
                            $p_obj_id,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            C__RECORD_STATUS__NORMAL
                        );
                        $l_import->load_xml_data(isys_glob_utf8_encode($l_modified_template));

                        if (!$l_import->parse())
                        {
                            return false;
                        } // if

                        $l_import->prepare();

                        if (!$l_import->import())
                        {
                            return false;
                        } // if

                        $l_tmp = $l_import->get_object_ids();

                        while ($l_tmpID = array_shift($l_tmp))
                        {
                            $l_object_id = $l_tmpID;
                        } // while

                        $l_dao             = new isys_cmdb_dao_category_g_global($this->m_db);
                        $l_data            = $l_dao->get_object_by_id($p_templates[count($p_templates) - 1])
                            ->__to_array();
                        $l_data_new_object = $l_dao->get_object_by_id($l_object_id)
                            ->__to_array();

                        if (!isys_cmdb_dao_category_g_accounting::has_placeholders($l_title))
                        {
                            $l_dao->save_title($l_object_id, $l_title);
                        } // if

                        // Saving some properties to our new object.
                        $l_dao->save_description($l_object_id, $l_data["isys_obj__description"]);
                        $l_dao->set_object_cmdb_status($l_object_id, $l_data["isys_obj__isys_cmdb_status__id"]);

                        // Category:
                        if (is_numeric($p_category) && $p_category > 0)
                        {
                            $l_log->debug('Overwrite category...');
                            if ($l_dao->save_category($l_object_id, $p_category) === false)
                            {
                                $l_log->warning('Cannot overwrite category.');
                            }
                        } //if

                        // Purpose:
                        if (is_numeric($p_purpose) && $p_purpose > 0)
                        {
                            $l_log->debug('Overwrite purpose...');
                            if ($l_dao->save_purpose($l_object_id, $p_purpose) === false)
                            {
                                $l_log->warning('Cannot overwrite pupose.');
                            }
                        } //if

                        unset($l_data);

                        $l_title = $l_data_new_object['isys_obj__title'];

                        $this->println($g_comp_template_language_manager->get("LC__CATG__ODEP_OBJ") . " " . $i . " (" . $l_title . ") ..", $p_html_output);
                        $this->println($g_comp_template_language_manager->get("LC_POPUP_WIZARD_FILE__CASE2A", ["var1" => $l_title]), $p_html_output);

                        if ($p_html_output)
                        {

                            $l_object_link = "?" . C__GET__MODULE_ID . "=" . C__MODULE__CMDB . "&" . C__CMDB__GET__OBJECTTYPE . "=" . $p_object_type . "&" . C__CMDB__GET__VIEWMODE . "=1100" . "&" . C__CMDB__GET__TREEMODE . "=1006" . "&" . C__CMDB__GET__OBJECT . "=" . $l_object_id;

                            $this->println(
                                "<a class=\"\" target=\"_new\" href=\"" . $l_object_link . "\">" . $g_comp_template_language_manager->get(
                                    $l_dao->get_objtype_name_by_id_as_string($p_object_type)
                                ) . ": " . $l_title . "</a>"
                            );

                            $this->println("", $p_html_output);
                        } // if

                        if (is_object($l_mod_event_manager))
                        {
                            $l_template_names = [];

                            foreach ($p_templates AS $l_templ_obj_id)
                            {
                                $l_template_names[] = '"' . $l_dao->get_obj_name_by_id_as_string($l_templ_obj_id) . '"';
                            } // foreach

                            $l_template_names = isys_helper_textformat::this_this_and_that(array_filter(array_unique($l_template_names)));

                            $l_mod_event_manager->triggerCMDBEvent(
                                'C__LOGBOOK_ENTRY__TEMPLATE_APPLIED',
                                null,
                                $l_object_id,
                                $p_object_type,
                                $l_template_names,
                                null,
                                "Template successfully applied."
                            );
                        } // if
                    } // for

                    if ($p_html_output)
                    {
                        echo "<script type=\"text/javascript\">parent.tpl_loader_hide();</script></div></body></html>";
                    } // if

                    return $l_object_id;
                }
                else
                {
                    throw new isys_exception_template("Import environment not installed. Template creation aborted.");
                } // if
            }
            else
            {
                throw new isys_exception_template("Export type: XML is not installed. Template creation aborted.");
            } // if
        }
        else
        {
            throw new isys_exception_template("Required export module is not installed. Template creation aborted.");
        } // if

        return false;
    } //function

    /**
     * Applies mass change to one or more objects based on a template.
     *
     * @param array  $p_objects               Object identifiers (int)
     * @param int    $p_template              Template object identifier
     * @param string $p_empty_fields          Mode for empty fields
     * @param string $p_multivalue_categories Mode for multi-valued categories
     * @param bool   $p_html_output           Prints HTML if enabled.
     *
     * @return type
     */
    public function apply_mass_change($p_objects, $p_template, $p_empty_fields, $p_multivalue_categories, $p_html_output = true)
    {
        assert('is_array($p_objects)');
        assert('is_int($p_template) && $p_template > 0');
        assert('is_int($p_empty_fields)');
        assert('is_int($p_multivalue_categories)');
        assert('is_bool($p_html_output)');

        global $g_comp_template, $g_comp_template_language_manager;

        $l_mod_event_manager = isys_event_manager::getInstance();
        $l_object_arr        = [];

        $l_string = null;

        // Start logging.
        $l_log = isys_factory_log::get_instance('mass_change');
        $l_log->set_verbose_level(isys_log::C__NONE);

        // Initiate export:
        $l_export = new isys_export_cmdb_object(
            'isys_export_type_xml', $this->m_db
        );

        // Initiate import:
        $l_import     = new isys_import_handler_cmdb($l_log, $this->m_db);
        $l_import_dao = new isys_module_dao_import_log($this->m_db);

        isys_event_manager::getInstance()
            ->set_import_id(
                $l_import_dao->add_import_entry(
                    $l_import_dao->get_import_type_by_const('C__IMPORT_TYPE__MASS_CHANGES'),
                    count($p_objects) . ' object(s) modified by mass channges.'
                )
            );

        // Set mode:
        $l_import->set_mode(isys_import_handler_cmdb::C__MERGE);
        $l_import->set_empty_fields_mode($p_empty_fields);
        $l_import->set_multivalue_categories_mode($p_multivalue_categories);
        $l_import->set_logbook_event('C__LOGBOOK_ENTRY__MASS_CHANGE_APPLIED');

        $l_xml_template = $l_export->export(
            $p_template,
            $this->get_export_cats(),
            C__RECORD_STATUS__TEMPLATE,
            false,
            true
        )
            ->parse()
            ->get_export();

        if ($p_html_output)
        {
            // Stop output buffering:
            ob_end_clean();
            ob_flush();

            echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">';
            $g_comp_template->display('head.tpl');
            echo '<div class="p10">';
        } // if

        $l_dao = new isys_cmdb_dao($this->m_db);

        $l_template_object = $l_dao->get_object_by_id($p_template)
            ->__to_array();

        // @todo Save some properties that are not handled by modifying the XML header:
        //$l_dao->set_object_cmdb_status($l_object_id, $l_template_object["isys_obj__isys_cmdb_status__id"]);

        //unset($l_template_object);

        // Iterate through each target object:
        foreach ($p_objects as $l_object_id)
        {
            // Fetch object information:
            $l_object = $l_dao->get_object_by_id($l_object_id)
                ->__to_array();

            $l_object_arr[$l_object_id] = $l_object;

            // Keep some object information untouched:
            $l_string .= $g_comp_template_language_manager->get("LC__TEMPLATES__APPLYING") . "&hellip;<br>";

            $l_modified_template = $this->modify_xml_header(
                $l_xml_template,
                htmlspecialchars($l_object['isys_obj__title']),  // Problems with special characters
                $l_object['isys_obj_type__id'],
                $l_object['isys_obj__id'],
                $l_object['isys_obj__sysid'],
                $l_object['isys_obj__created'],
                $l_object['isys_obj__created_by'],
                $l_object['isys_obj__updated'],
                $l_object['isys_obj__updated_by'],
                $l_template_object['isys_obj__description'],
                $l_object['isys_obj__isys_cmdb_status__id']
            );

            $l_import->load_xml_data($l_modified_template);

            if (!$l_import->parse())
            {
                throw new isys_exception_general('Parsing failed.');
            } // if

            $l_import->prepare();

            $l_string .= $g_comp_template_language_manager->get(
                    "LC__MASS_CHANGE__APPLIED",
                    ["var1" => $l_object['isys_obj__title']]
                ) . "<br>";

            $l_object_link = "?" . C__GET__MODULE_ID . "=" . C__MODULE__CMDB . "&" . C__CMDB__GET__OBJECTTYPE . "=" . $l_object['isys_obj_type__id'] . "&" . C__CMDB__GET__VIEWMODE . "=1100" . "&" . C__CMDB__GET__TREEMODE . "=1006" . "&" . C__CMDB__GET__OBJECT . "=" . $l_object_id;

            $l_string .= '<a class="" target="_blank" href="' . $l_object_link . '">' . $g_comp_template_language_manager->get(
                    $l_object['isys_obj_type__title']
                ) . ': ' . $l_object['isys_obj__title'] . '</a><br>';

        } // foreach

        if (!$l_import->import())
        {
            throw new isys_exception_general('Importing failed.');
        }
        else
        {

            $this->println($l_string, true);
        } // if

        if ($p_html_output)
        {
            echo '<script type="text/javascript">parent.loader_hide();</script>';
            echo '</div>';
            echo '</body>';
            echo '</html>';
        } // if

        isys_component_signalcollection::get_instance()
            ->emit('mod.cmdb.massChangeApplied', $l_object_arr, $p_template, $l_import);
    } // function

    public function get_template_list()
    {
        $l_dao = new isys_templates_dao($this->m_db);
        switch ($this->get_m_rec_status())
        {
            case C__RECORD_STATUS__MASS_CHANGES_TEMPLATE:
                $l_templates = $l_dao->get_mass_change_templates();
                break;
            default:
                $l_templates = $l_dao->get_templates();
                break;
        }

        /**
         * Display template list
         */
        if ($l_templates->num_rows() > 0)
        {
            $l_arTableHeader = [
                "title"               => "Template",
                "created"             => _L("LC__TASK__DETAIL__WORKORDER__CREATION_DATE"),
                "affected_categories" => _L("LC__CMDB__AFFECTED_CATEGORIES"),
                "export"              => "Download"
            ];

            $l_objList = new isys_component_list();

            $l_objList->config(
                $l_arTableHeader,
                "?" . C__GET__MODULE_ID . "=" . C__MODULE__CMDB . "&" . C__CMDB__GET__OBJECTTYPE . "=[{isys_obj_type__id}]&" . C__CMDB__GET__VIEWMODE . "=1100&" . C__CMDB__GET__TREEMODE . "=1006&" . C__CMDB__GET__OBJECT . "=[{isys_obj__id}]",
                "[{isys_obj__id}]",
                true,
                true,
                [
                    "10",
                    "20",
                    "950",
                    "20"
                ]
            );

            $l_objList->set_row_modifier($this, "row_templates");

            return $l_objList->getTempTableHtml($l_templates, true);
        }
        else
        {
            return false;
        } // if
    } // function

    public function row_templates(&$p_ar_data)
    {
        $p_ar_data["affected_categories"] = "<div style='white-space:normal;'>" . implode(
                ", ",
                $this->get_affected_categories($p_ar_data["isys_obj__id"], $p_ar_data["isys_obj__isys_obj_type__id"])
            ) . "</div>";
        $p_ar_data["title"]               = "<strong>" . $p_ar_data["isys_obj__title"] . "</strong>, <br />" . _L($p_ar_data["isys_obj_type__title"]);
        $p_ar_data["created"]             = $p_ar_data["isys_obj__created"] . " (" . $p_ar_data["isys_obj__created_by"] . ")";
        $p_ar_data["export"]              = "<a href=\"?" . C__GET__MODULE_ID . "=" . $this->get_module_id(
            ) . "&export=" . $p_ar_data["isys_obj__id"] . "\"><img src=\"images/icons/silk/page_white_code.png\" /></a>";
    }

    public function get_affected_categories($p_object_id, $p_object_type = null, $p_as_array = false)
    {
        /* Categories */
        $l_template_cats = $l_return_as_array = [];
        $l_cat           = new isys_cmdb_dao_category_g_overview($this->m_db);

        if (is_null($p_object_type))
        {
            $p_object_type = $l_cat->get_objTypeID($p_object_id);
        }

        $l_catm[C__CMDB__CATEGORY__TYPE_GLOBAL] = $l_cat->get_categories_as_array(
            $p_object_type,
            $p_object_id,
            C__CMDB__CATEGORY__TYPE_GLOBAL,
            C__RECORD_STATUS__NORMAL,
            false
        );

        $l_catm[C__CMDB__CATEGORY__TYPE_SPECIFIC] = $l_cat->get_categories_as_array(
            $p_object_type,
            $p_object_id,
            C__CMDB__CATEGORY__TYPE_SPECIFIC,
            C__RECORD_STATUS__NORMAL,
            false
        );

        foreach ($l_catm AS $l_cattype => $l_cattype_data)
        {
            if (!is_array($l_cattype_data) || count($l_cattype_data) === 0)
            {
                continue;
            } // if

            foreach ($l_cattype_data as $l_category_id => $l_data)
            {

                if (!in_array($l_data['const'], self::$m_cat_skips[$l_cattype]))
                {
                    $l_category_dao = $l_data["dao"];

                    if (is_object($l_category_dao) && method_exists($l_category_dao, "get_count"))
                    {

                        $l_count = $l_category_dao->get_count($p_object_id);

                        if ($l_count > 0)
                        {
                            $l_return_as_array[$l_cattype][] = $l_category_id;
                            $l_template_cats[]               = "<u>" . $l_data["title"] . "</u> " . "(<acronym class=\"bold\" title=\"{$l_count} Row(s)\">" . $l_count . "</acronym>)";
                        }
                    }

                }

            }

        }

        return ($p_as_array) ? $l_return_as_array : $l_template_cats;
    }

    /**
     * Gets type
     *
     * @return int
     */
    public function get_m_rec_status()
    {
        return $this->m_type;
    }

    /**
     * Sets type.
     * Possible options:
     *    - C__RECORD_STATUS__TEMPLATE
     *    - C__RECORD_STATUS__MASS_CHANGES_TEMPLATE
     *
     * @param int $p_type
     */
    public function set_m_rec_status($p_type)
    {
        $this->m_type = $p_type;
    }

    /**
     * Method for displaying a string.
     *
     * @param  string  $p_message
     * @param  boolean $p_html_output
     */
    private function println($p_message, $p_html_output = true)
    {
        if ($p_html_output)
        {
            echo $p_message . '<br />';
        } // if
    } // function

    /**
     * Method for retrieving specific export categories.
     *
     * @return  array
     */
    private function get_export_cats()
    {

        $l_export      = new isys_export_cmdb_object();
        $l_tmp         = $l_export->fetch_exportable_categories();
        $l_transformed = [];

        foreach ($l_tmp AS $l_category_type => $l_categories)
        {
            foreach ($l_categories AS $l_categoryID => $l_crap)
            {
                $l_transformed[$l_category_type][] = $l_categoryID;
            } // foreach
        } // foreach

        return $l_transformed;
    } //function

    /**
     * Processes user requests.
     *
     * @param   array $p_get
     * @param   array $p_post
     *
     * @throws  isys_exception_general
     */
    private function process($p_get, $p_post)
    {
        global $index_includes, $g_comp_template, $g_comp_template_language_manager;

        $l_navbar = isys_component_template_navbar::getInstance();

        if (isset($p_get["export"]) && $p_get["export"] > 0)
        {
            header("Content-Type: text/xml; charset=utf-8");
            header("Content-Disposition: attachment; filename=\"template-" . $p_get["export"] . "-" . date("Y-m-d") . ".xml\"");

            $l_affected_categories = $this->get_affected_categories(
                $p_get["export"],
                isys_cmdb_dao::instance($this->m_db)
                    ->get_objTypeID($p_get["export"]),
                true
            );

            $l_export = new isys_export_cmdb_object();
            echo $l_export->export($p_get["export"], $l_affected_categories, C__RECORD_STATUS__NORMAL)
                ->parse()
                ->get_export();
            die;
        } // if

        if (isset($p_post["create_template"]) && $p_post["create_template"])
        {
            try
            {
                // Create object by templates.
                $this->create_from_template(
                    $p_post["templates"],
                    $p_post["object_type"],
                    $p_post["object_title"],
                    null,
                    true,
                    $p_post["C__TEMPLATE__SUFFIX_COUNT"],
                    $p_post["C__TEMPLATE__SUFFIX_SUFFIX_TYPE"],
                    $p_post["category"],
                    $p_post["purpose"],
                    $p_post["C__TEMPLATE__SUFFIX_SUFFIX_TYPE_OWN"],
                    $p_post["C__TEMPLATE__SUFFIX_COUNT_STARTING_AT"],
                    $p_post["C__TEMPLATE__SUFFIX_ZERO_POINT_CALC"],
                    $p_post["C__TEMPLATE__SUFFIX_ZERO_POINTS"]
                );

                die();
            }
            catch (Exception $e)
            {
                isys_glob_display_error($e->getMessage());
                die;
            } // try
        } // if

        // Mass change:
        if ($p_post["apply_mass_change"])
        {
            // Parse arguments and call method for mass changes:
            try
            {
                // Selected objects:
                if (!isset($p_post['selected_objects__HIDDEN']))
                {
                    throw new isys_exception_general('No objects selected.');
                } //if

                $l_objects     = json_decode($p_post['selected_objects__HIDDEN']);
                $l_object_list = [];
                if (is_array($l_objects))
                {
                    foreach ($l_objects as $l_object)
                    {
                        if (!is_numeric($l_object) || $l_object <= 0)
                        {
                            throw new isys_exception_general('Object list is invalid.');
                        } //if
                        $l_object_list[] = intval($l_object);
                    } //foreach
                    unset($l_objects);
                }

                // Selected template:
                if (!isset($p_post['templates'][0]) || !is_numeric($p_post['templates'][0]) || $p_post['templates'][0] <= 0)
                {
                    throw new isys_exception_general('No template selected.');
                } // if
                $l_template = intval($p_post['templates'][0]);

                // Handle empty fields:
                if (!isset($p_post['empty_fields']) || !is_numeric(
                        $p_post['empty_fields']
                    ) || ($p_post['empty_fields'] != isys_import_handler_cmdb::C__KEEP && $p_post['empty_fields'] != isys_import_handler_cmdb::C__CLEAR)
                )
                {
                    throw new isys_exception_general('Handling empty fields is unclear.');
                } //if
                $l_empty_fields = intval($p_post['empty_fields']);

                // Handle multi-valued categories:
                if (!isset($p_post['multivalue_categories']) || !is_numeric(
                        $p_post['multivalue_categories']
                    ) || ($p_post['multivalue_categories'] != isys_import_handler_cmdb::C__UNTOUCHED && $p_post['multivalue_categories'] != isys_import_handler_cmdb::C__APPEND && $p_post['multivalue_categories'] != isys_import_handler_cmdb::C__OVERWRITE && $p_post['multivalue_categories'] != isys_import_handler_cmdb::C__UPDATE && $p_post['multivalue_categories'] != isys_import_handler_cmdb::C__UPDATE_ADD)
                )
                {
                    throw new isys_exception_general('Handling multi-valued categories is unclear.');
                } //if
                $l_multivalue_categories = intval($p_post['multivalue_categories']);

                $this->apply_mass_change(
                    $l_object_list,
                    $l_template,
                    $l_empty_fields,
                    $l_multivalue_categories,
                    true
                );
            }
            catch (Exception $e)
            {
                echo($e->getMessage());
            } //try/catch

            die;
        } // if mass change

        $g_comp_template->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1");

        if (!isset($p_get[C__GET__SETTINGS_PAGE]))
        {
            if ($p_get[C__GET__MODULE] == C__MODULE__CMDB)
            {
                if (isys_auth_templates::instance()
                    ->is_allowed_to(isys_auth::EXECUTE, 'MASS_CHANGES/' . C__MODULE__TEMPLATES . TPL_PID__MASS_CHANGE)
                )
                {
                    $p_get[C__GET__SETTINGS_PAGE] = TPL_PID__MASS_CHANGE;
                }
                elseif (isys_auth_templates::instance()
                    ->is_allowed_to(isys_auth::VIEW, 'MASS_CHANGES/' . C__MODULE__TEMPLATES . TPL_PID__EXISTING)
                )
                {
                    $p_get[C__GET__SETTINGS_PAGE] = TPL_PID__EXISTING;
                }
                elseif (isys_auth_templates::instance()
                    ->is_allowed_to(isys_auth::EXECUTE, 'MASS_CHANGES/' . C__MODULE__TEMPLATES . TPL_PID__NEW)
                )
                {
                    $p_get[C__GET__SETTINGS_PAGE] = TPL_PID__NEW;
                }
            }
            else
            {
                if (isys_auth_templates::instance()
                    ->is_allowed_to(isys_auth::EXECUTE, 'TEMPLATES/' . C__MODULE__TEMPLATES . TPL_PID__NEW_OBJET)
                )
                {
                    $p_get[C__GET__SETTINGS_PAGE] = TPL_PID__NEW_OBJET;
                }
                elseif (isys_auth_templates::instance()
                    ->is_allowed_to(isys_auth::EXECUTE, 'TEMPLATES/' . C__MODULE__TEMPLATES . TPL_PID__NEW)
                )
                {
                    $p_get[C__GET__SETTINGS_PAGE] = TPL_PID__NEW;
                }
                elseif (isys_auth_templates::instance()
                    ->is_allowed_to(isys_auth::VIEW, 'TEMPLATES/' . C__MODULE__TEMPLATES . TPL_PID__EXISTING)
                )
                {
                    $p_get[C__GET__SETTINGS_PAGE] = TPL_PID__EXISTING;
                }
            }
        }

        switch ($p_get[C__GET__SETTINGS_PAGE])
        {
            case TPL_PID__NEW:
                if ($p_get[C__GET__MODULE] == C__MODULE__CMDB)
                {
                    isys_auth_templates::instance()
                        ->check(isys_auth::EXECUTE, 'MASS_CHANGES/' . C__MODULE__TEMPLATES . TPL_PID__NEW);
                    $l_rules['object_type']['p_strSelectedID'] = C__OBJTYPE__GENERIC_TEMPLATE;
                    $g_comp_template->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
                    $index_includes['contentbottomcontent'] = "modules/templates/new_mass_changes_template.tpl";
                }
                else
                {
                    isys_auth_templates::instance()
                        ->check(isys_auth::EXECUTE, 'TEMPLATES/' . C__MODULE__TEMPLATES . TPL_PID__NEW);
                    $index_includes['contentbottomcontent'] = "modules/templates/new_template.tpl";
                }
                break;

            case TPL_PID__NEW_OBJET:
                isys_auth_templates::instance()
                    ->check(isys_auth::EXECUTE, 'TEMPLATES/' . C__MODULE__TEMPLATES . TPL_PID__NEW_OBJET);
                $l_dao       = new isys_templates_dao($this->m_db);
                $l_templates = $l_dao->get_templates();

                $l_template_array = null;

                while ($l_row = $l_templates->get_row())
                {
                    $l_template_array[$g_comp_template_language_manager->{$l_row["isys_obj_type__title"]}][$l_row["isys_obj__id"]] = $l_row["isys_obj__title"];
                } // while

                $g_comp_template->assign("templates", $l_template_array);
                $index_includes['contentbottomcontent'] = "modules/templates/create_object.tpl";
                break;

            case TPL_PID__SETTINGS:
                if (class_exists("isys_module_system"))
                {
                    $l_settings = new isys_module_system();
                    $l_settings->handle_templates();
                }
                else
                {
                    throw new isys_exception_general("Module 'isys_module_system' does not exist.");
                } // if
                break;

            case TPL_PID__MASS_CHANGE:
                isys_auth_templates::instance()
                    ->check(isys_auth::EXECUTE, 'MASS_CHANGES/' . C__MODULE__TEMPLATES . TPL_PID__MASS_CHANGE);
                // Retrieve available object templates for mass changes:
                $l_dao            = new isys_templates_dao($this->m_db);
                $l_templates      = $l_dao->get_mass_change_templates();
                $l_template_array = [];

                while ($l_row = $l_templates->get_row())
                {
                    $l_template_array[$g_comp_template_language_manager->{$l_row["isys_obj_type__title"]}][$l_row["isys_obj__id"]] = $l_row["isys_obj__title"];
                } // while

                if (count($l_template_array) === 0)
                {
                    $l_rules['selected_objects']['p_bDisabled']  = true;
                    $l_rules['templates']['p_bDisabled']         = true;
                    $l_rules['apply_mass_change']['p_bDisabled'] = true;
                    $g_comp_template->assign('field_disabled', 'disabled="disabled"');
                }
                else
                {
                    $l_ar_data                        = serialize($l_template_array);
                    $l_rules['templates']['p_arData'] = $l_ar_data;
                }

                $g_comp_template->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
                // Template file:
                $index_includes['contentbottomcontent'] = "modules/templates/mass_change.tpl";

                // Options:
                $g_comp_template->assign('keep', isys_import_handler_cmdb::C__KEEP)
                    ->assign('clear', isys_import_handler_cmdb::C__CLEAR)
                    ->assign('untouched', isys_import_handler_cmdb::C__UNTOUCHED)
                    ->assign('add', isys_import_handler_cmdb::C__APPEND)
                    ->assign('delete_add', isys_import_handler_cmdb::C__OVERWRITE)
                    ->assign('update', isys_import_handler_cmdb::C__UPDATE)
                    ->assign('update_add', isys_import_handler_cmdb::C__UPDATE_ADD);
                break;

            default:

                $l_strJs = "onClick=\"load_list();\"";

                switch ($_GET[C__GET__MODULE])
                {
                    case C__MODULE__CMDB:
                        isys_auth_templates::instance()
                            ->check(isys_auth::VIEW, 'MASS_CHANGES/' . C__MODULE__TEMPLATES . '1');
                        $l_check_delete_rights = isys_auth_templates::instance()
                            ->is_allowed_to(isys_auth::DELETE, 'MASS_CHANGES/' . C__MODULE__TEMPLATES . '1');
                        $g_comp_template->assign('rec_status', C__RECORD_STATUS__MASS_CHANGES_TEMPLATE);
                        break;
                    default:
                        isys_auth_templates::instance()
                            ->check(isys_auth::VIEW, 'TEMPLATES/' . C__MODULE__TEMPLATES . '1');
                        $l_check_delete_rights = isys_auth_templates::instance()
                            ->is_allowed_to(isys_auth::DELETE, 'TEMPLATES/' . C__MODULE__TEMPLATES . '1');
                        $g_comp_template->assign('rec_status', C__RECORD_STATUS__TEMPLATE);
                        break;
                }

                $l_navbar->set_js_function($l_strJs, C__NAVBAR_BUTTON__PURGE)
                    ->set_active($l_check_delete_rights, C__NAVBAR_BUTTON__PURGE)
                    ->set_visible(true, C__NAVBAR_BUTTON__PURGE)
                    ->set_visible(false, C__NAVBAR_BUTTON__UP)
                    ->set_visible(false, C__NAVBAR_BUTTON__FORWARD)
                    ->set_visible(false, C__NAVBAR_BUTTON__BACK);

                $index_includes['contentbottomcontent'] = "modules/templates/main.tpl";
                break;
        } // switch
    } // function

    /**
     * Constructor
     */
    public function __construct()
    {
        global $g_comp_database;
        $this->m_db = $g_comp_database;
    } // function

} // class