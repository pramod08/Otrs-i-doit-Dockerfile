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
define("C__GET__NAGIOS_PAGE", "npID");
define("C__GET__NAGIOS_TPID", "tpID");
define("C__GET__NAGIOS_CID", "cID");
define("C__GET__NAGIOS_EID", "eID");
define("C__GET__NAGIOS_NDOID", "nID");
define("C__GET__NAGIOS_HOST_ID", "hID");

define("C__NAGIOS_PAGE__CONFIG", 2);
define("C__NAGIOS_PAGE__TIMEPERIODS", 3);
define("C__NAGIOS_PAGE__COMMANDS", 4);
define("C__NAGIOS_PAGE__HOST_ESCALATIONS", 5);
define("C__NAGIOS_PAGE__SERVICE_ESCALATIONS", 6);
define("C__NAGIOS_PAGE__EXPORT", 7);

/**
 * i-doit
 *
 * Nagios Settings.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Bluemer <dbluemer@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_module_nagios extends isys_module implements isys_module_authable
{
    /**
     * Define, if this module shall be displayed in the extras menu.
     *
     * @var  boolean
     */
    const DISPLAY_IN_MAIN_MENU = true;
    /**
     * Defines, if this module shall be displayed in the system menu
     *
     * @var  boolean
     */
    const DISPLAY_IN_SYSTEM_MENU = true;
    /**
     * Static configuration array.
     *
     * @var  array
     */
    protected static $m_config = [];
    /**
     * @var bool
     */
    protected static $m_licenced = true;

    /**
     * Attach live status of object to contenttop header
     *
     * @param  $p_catdata
     *
     * @global $g_comp_database ;
     * @global $index_includes
     */
    public static function process_content_top($p_catdata)
    {
        global $g_comp_database;

        if (defined('C__MONITORING__TYPE_NDO'))
        {
            if ((count(
                    isys_monitoring_dao_hosts::instance($g_comp_database)
                        ->get_data(null, C__MONITORING__TYPE_NDO)
                ) > 0)
            )
            {
                global $index_includes;
                $index_includes['contenttopobjectdetail'][] = __DIR__ . '/templates/contenttop/main_objectdetail_ndo_status.tpl';
            } // if
        } // if
    } // function

    /**
     * Get related auth class for module
     *
     * @author Selcuk Kekec <skekec@i-doit.com>
     * @return isys_auth
     */
    public static function get_auth()
    {
        return isys_auth_nagios::instance();
    } // function

    /**
     * @param   integer $p_obj_id
     *
     * @return  string
     */
    protected static function get_correct_contact_name($p_obj_id)
    {
        global $g_comp_database;

        try
        {
            if (self::$m_config['PERSON_NAME_OPTION'] == C__NAGIOS__PERSON_OPTION__USERNAME)
            {
                $l_row = isys_cmdb_dao_category_s_person_login::instance($g_comp_database)
                    ->get_data(null, $p_obj_id)
                    ->get_row();

                return $l_row['isys_cats_person_list__title'];
            }
            else
            {
                return isys_cmdb_dao::instance($g_comp_database)
                    ->get_obj_name_by_id_as_string($p_obj_id);
            } // if
        }
        catch (Exception $e)
        {
            return 'ERROR: ' . $e->getMessage();
        } // try
    } // function

    /**
     * Method for initialization.
     *
     * @param   isys_module_request $p_req
     *
     * @return  isys_module_nagios
     */
    public function init(isys_module_request $p_req)
    {
        return $this;
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
        global $g_dirs;

        if ($p_system_module)
        {
            $l_monitoring_parent = $p_tree->find_id_by_title(_L('LC__MONITORING'));

            if ($l_monitoring_parent > 0)
            {
                $p_parent = $l_monitoring_parent;
            } // if

            $p_tree->add_node(
                C__MODULE__NAGIOS . '8',
                $p_parent,
                'Nagios Export',
                '?' . C__GET__MODULE_ID . '=' . $_GET[C__GET__MODULE_ID] . '&' . C__GET__MODULE_SUB_ID . '=' . C__MODULE__NAGIOS . '&' . C__GET__TREE_NODE . '=' . C__MODULE__NAGIOS . '8' . '&' . C__GET__NAGIOS_PAGE . '=' . C__NAGIOS_PAGE__EXPORT . '&' . C__GET__MAIN_MENU__NAVIGATION_ID . '=' . $_GET[C__GET__MAIN_MENU__NAVIGATION_ID],
                '',
                $g_dirs['images'] . 'icons/nagios.png',
                0,
                '',
                '',
                isys_auth_nagios::instance()
                    ->is_allowed_to(isys_auth::EXECUTE, 'NAGIOS_EXPORT')
            );
        }
        else
        {
            $p_parent = $p_tree->add_node(
                C__MODULE__NAGIOS . '0',
                -1,
                'Nagios',
                '',
                '',
                '',
                0,
                '',
                '',
                isys_auth_nagios::instance()
                    ->has_any_rights_in_module()
            );

            $p_tree->add_node(
                C__MODULE__NAGIOS . '3',
                $p_parent,
                'Config',
                '?' . C__GET__MODULE_ID . '=' . $_GET[C__GET__MODULE_ID] . '&' . C__GET__TREE_NODE . '=' . C__MODULE__NAGIOS . '3' . '&' . C__GET__NAGIOS_PAGE . '=' . C__NAGIOS_PAGE__CONFIG . '&' . C__GET__MAIN_MENU__NAVIGATION_ID . '=' . $_GET[C__GET__MAIN_MENU__NAVIGATION_ID],
                '',
                '',
                0,
                '',
                '',
                isys_auth_nagios::instance()
                    ->is_allowed_to(isys_auth::SUPERVISOR, 'NAGIOS_CONFIG')
            );

            $p_tree->add_node(
                C__MODULE__NAGIOS . '4',
                $p_parent,
                'Timeperiods',
                '?' . C__GET__MODULE_ID . '=' . $_GET[C__GET__MODULE_ID] . $l_submodule . '&' . C__GET__TREE_NODE . '=' . C__MODULE__NAGIOS . '4' . '&' . C__GET__NAGIOS_PAGE . '=' . C__NAGIOS_PAGE__TIMEPERIODS . '&' . C__GET__MAIN_MENU__NAVIGATION_ID . '=' . $_GET[C__GET__MAIN_MENU__NAVIGATION_ID],
                '',
                '',
                0,
                '',
                '',
                isys_auth_nagios::instance()
                    ->is_allowed_to(isys_auth::SUPERVISOR, 'NAGIOS_TIMEPERIODS')
            );

            $p_tree->add_node(
                C__MODULE__NAGIOS . '5',
                $p_parent,
                'Commands',
                '?' . C__GET__MODULE_ID . '=' . $_GET[C__GET__MODULE_ID] . $l_submodule . '&' . C__GET__TREE_NODE . '=' . C__MODULE__NAGIOS . '5' . '&' . C__GET__NAGIOS_PAGE . '=' . C__NAGIOS_PAGE__COMMANDS . '&' . C__GET__MAIN_MENU__NAVIGATION_ID . '=' . $_GET[C__GET__MAIN_MENU__NAVIGATION_ID],
                '',
                '',
                0,
                '',
                '',
                isys_auth_nagios::instance()
                    ->is_allowed_to(isys_auth::SUPERVISOR, 'NAGIOS_COMMANDS')
            );

            $p_tree->add_node(
                C__MODULE__NAGIOS . '6',
                $p_parent,
                'Host escalations',
                '?' . C__GET__MODULE_ID . '=' . $_GET[C__GET__MODULE_ID] . $l_submodule . '&' . C__GET__TREE_NODE . '=' . C__MODULE__NAGIOS . '6' . '&' . C__GET__NAGIOS_PAGE . '=' . C__NAGIOS_PAGE__HOST_ESCALATIONS . '&' . C__GET__MAIN_MENU__NAVIGATION_ID . '=' . $_GET[C__GET__MAIN_MENU__NAVIGATION_ID],
                '',
                '',
                0,
                '',
                '',
                isys_auth_nagios::instance()
                    ->is_allowed_to(isys_auth::SUPERVISOR, 'NAGIOS_HOST_ESCALATIONS')
            );

            $p_tree->add_node(
                C__MODULE__NAGIOS . '7',
                $p_parent,
                'Service escalations',
                '?' . C__GET__MODULE_ID . '=' . $_GET[C__GET__MODULE_ID] . $l_submodule . '&' . C__GET__TREE_NODE . '=' . C__MODULE__NAGIOS . '7' . '&' . C__GET__NAGIOS_PAGE . '=' . C__NAGIOS_PAGE__SERVICE_ESCALATIONS . '&' . C__GET__MAIN_MENU__NAVIGATION_ID . '=' . $_GET[C__GET__MAIN_MENU__NAVIGATION_ID],
                '',
                '',
                0,
                '',
                '',
                isys_auth_nagios::instance()
                    ->is_allowed_to(isys_auth::SUPERVISOR, 'NAGIOS_SERVICE_ESCALATIONS')
            );

            $p_tree->add_node(
                C__MODULE__NAGIOS . '9',
                $p_parent,
                _L('LC__OBJTYPE__NAGIOS_HOST_TPL'),
                "javascript:tree_obj_type_click('" . C__OBJTYPE__NAGIOS_HOST_TPL . "');",
                '',
                '',
                0,
                '',
                '',
                isys_auth_cmdb::instance()
                    ->is_allowed_to(isys_auth::SUPERVISOR, 'OBJ_IN_TYPE/C__OBJTYPE__NAGIOS_HOST_TPL')
            );

            $p_tree->add_node(
                C__MODULE__NAGIOS . '10',
                $p_parent,
                _L('LC__OBJTYPE__NAGIOS_SERVICE_TPL'),
                "javascript:tree_obj_type_click('" . C__OBJTYPE__NAGIOS_SERVICE_TPL . "');",
                '',
                '',
                0,
                '',
                '',
                isys_auth_cmdb::instance()
                    ->is_allowed_to(isys_auth::SUPERVISOR, 'OBJ_IN_TYPE/C__OBJTYPE__NAGIOS_SERVICE_TPL')
            );

            $p_tree->add_node(
                C__MODULE__NAGIOS . '11',
                $p_parent,
                _L('LC__OBJTYPE__NAGIOS_SERVICE'),
                "javascript:tree_obj_type_click('" . C__OBJTYPE__NAGIOS_SERVICE . "');",
                '',
                '',
                0,
                '',
                '',
                isys_auth_cmdb::instance()
                    ->is_allowed_to(isys_auth::SUPERVISOR, 'OBJ_IN_TYPE/C__OBJTYPE__NAGIOS_SERVICE')
            );
        } // if
    } // function

    /**
     * Start module Nagios.
     *
     * @author Dennis Bluemer <dbluemer@synetics.de>
     * @return void
     */
    public function start()
    {
        if (isys_glob_get_param("ajax") && !isys_glob_get_param("call"))
        {
            $this->processAjaxRequest();
            die;
        } // if

        global $g_comp_template;

        $l_gets  = isys_module_request::get_instance()
            ->get_gets();
        $l_posts = isys_module_request::get_instance()
            ->get_posts();

        // Build the menutree.
        if ($_GET[C__GET__MODULE_ID] != C__MODULE__SYSTEM)
        {
            $l_tree = isys_module_request::get_instance()
                ->get_menutree();

            $this->build_tree($l_tree, false);
            $g_comp_template->assign("menu_tree", $l_tree->process($_GET[C__GET__TREE_NODE]));
        } // if

        switch ($l_posts[C__GET__NAVMODE])
        {
            case C__NAVMODE__NEW:
            case C__NAVMODE__EDIT:
                isys_component_template_navbar::getInstance()
                    ->set_active(true, C__NAVBAR_BUTTON__SAVE)
                    ->set_active(true, C__NAVBAR_BUTTON__CANCEL);
                break;
        } //switch

        switch ($l_gets[C__GET__NAGIOS_PAGE])
        {
            case C__NAGIOS_PAGE__CONFIG:
                isys_auth_nagios::instance()
                    ->check(isys_auth::VIEW, 'NAGIOS_CONFIG');
                switch ($l_posts[C__GET__NAVMODE])
                {
                    case C__NAVMODE__SAVE:
                        $this->saveConfig();
                        break;

                    default:
                        $this->processConfigPage();
                }
                break;

            case C__NAGIOS_PAGE__TIMEPERIODS:
                isys_auth_nagios::instance()
                    ->check(isys_auth::VIEW, 'NAGIOS_TIMEPERIODS');
                switch ($l_posts[C__GET__NAVMODE])
                {
                    case C__NAVMODE__NEW:
                        $this->createTimeperiod();
                        break;

                    case C__NAVMODE__SAVE:
                        $this->saveTimeperiod();
                        break;

                    case C__NAVMODE__DELETE:
                        $this->deleteTimeperiod();
                        break;

                    default:
                        if (isset($l_gets[C__GET__NAGIOS_TPID]))
                        {
                            $this->processTimeperiodsPage($l_gets[C__GET__NAGIOS_TPID]);
                        }
                        else if (is_array($_POST["id"]))
                        {
                            $this->processTimeperiodsPage($_POST["id"][0]);
                        }
                        else
                        {
                            $this->processTimeperiodsListing();
                        } // if
                } // switch
                break;

            case C__NAGIOS_PAGE__COMMANDS:
                isys_auth_nagios::instance()
                    ->check(isys_auth::VIEW, 'NAGIOS_COMMANDS');
                switch ($l_posts[C__GET__NAVMODE])
                {
                    case C__NAVMODE__NEW:
                        $this->createCommand();
                        break;

                    case C__NAVMODE__SAVE:
                        $this->saveCommand();
                        break;

                    case C__NAVMODE__DELETE:
                        $this->deleteCommand();
                        break;

                    default:
                        if (isset($l_gets[C__GET__NAGIOS_CID]))
                        {
                            $this->processCommandsPage($l_gets[C__GET__NAGIOS_CID]);
                        }
                        else if (is_array($_POST["id"]))
                        {
                            $this->processCommandsPage($_POST["id"][0]);
                        }
                        else
                        {
                            $this->processCommandsListing();
                        } // if
                } // switch
                break;

            case C__NAGIOS_PAGE__HOST_ESCALATIONS:
                isys_auth_nagios::instance()
                    ->check(isys_auth::VIEW, 'NAGIOS_HOST_ESCALATIONS');
                switch ($l_posts[C__GET__NAVMODE])
                {
                    case C__NAVMODE__NEW:
                        $this->createHostEscalation();
                        break;

                    case C__NAVMODE__SAVE:
                        $this->saveHostEscalation();
                        break;

                    case C__NAVMODE__DELETE:
                        $this->deleteHostEscalation();
                        break;

                    default:
                        if (isset($l_gets[C__GET__NAGIOS_EID]))
                        {
                            $this->processHostEscalationsPage($l_gets[C__GET__NAGIOS_EID]);
                        }
                        else if (is_array($_POST["id"]))
                        {
                            $this->processHostEscalationsPage($_POST["id"][0]);
                        }
                        else
                        {
                            $this->processHostEscalationsListing();
                        }
                }
                break;

            case C__NAGIOS_PAGE__SERVICE_ESCALATIONS:
                isys_auth_nagios::instance()
                    ->check(isys_auth::VIEW, 'NAGIOS_SERVICE_ESCALATIONS');
                switch ($l_posts[C__GET__NAVMODE])
                {
                    case C__NAVMODE__NEW:
                        $this->createServiceEscalation();
                        break;

                    case C__NAVMODE__SAVE:
                        $this->saveServiceEscalation();
                        break;

                    case C__NAVMODE__DELETE:
                        $this->deleteServiceEscalation();
                        break;

                    default:
                        if (isset($l_gets[C__GET__NAGIOS_EID]))
                        {
                            $this->processServiceEscalationsPage($l_gets[C__GET__NAGIOS_EID]);
                        }
                        else if (is_array($_POST["id"]))
                        {
                            $this->processServiceEscalationsPage($_POST["id"][0]);
                        }
                        else
                        {
                            $this->processServiceEscalationsListing();
                        } // if
                } // switch
                break;

            case C__NAGIOS_PAGE__EXPORT:
                isys_auth_nagios::instance()
                    ->check(isys_auth::EXECUTE, 'NAGIOS_EXPORT');
                $this->processExportPage();
                break;

            default:
                // Do nothing to display the dashboard.
        } // switch
    } // function

    /**
     * Will be called when the Nagios module is activated via the module manager.
     * Activates the global Nagios category (sets its status to C__RECORD_STATUS__NORMAL).
     *
     * @return  boolean
     */
    public function activate()
    {
        global $g_comp_database;

        return isys_cmdb_dao::instance($g_comp_database)
            ->set_catg_status(C__CATG__NAGIOS, C__RECORD_STATUS__NORMAL);
    } // function

    /**
     * Will be called when the Nagios module is deactivated via the module manager.
     * Deactivates the global Nagios category (sets its status to C__RECORD_STATUS__DELETED).
     *
     * @return  boolean
     */
    public function deactivate()
    {
        global $g_comp_database;

        return isys_cmdb_dao::instance($g_comp_database)
            ->set_catg_status(C__CATG__NAGIOS, C__RECORD_STATUS__DELETED);
    } // function

    /**
     * Will be called when the Nagios module is uninstalled via the module manager.
     * Deletes the global Nagios category (permanently).
     *
     * @return  boolean
     */
    public function uninstall()
    {
        global $g_comp_database;

        return isys_cmdb_dao::instance($g_comp_database)
            ->delete_catg(C__CATG__NAGIOS);
    } // function

    /**
     * @throws  isys_exception_dao
     */
    public function saveHostEscalation()
    {
        global $g_comp_database, $g_comp_template, $index_includes;

        $l_rules = [];

        $l_daoNagios = new isys_component_dao_nagios($g_comp_database);

        try
        {
            if (!isset($_POST["id"]) || empty($_POST["id"]))
            {
                $l_id = $l_daoNagios->createHostEscalation();
            }
            else
            {
                $l_id = $_POST["id"];
            } // if

            $l_daoNagios->saveHostEscalation($l_id);
            $l_contactID = $l_daoNagios->getHostEscalationContactID($l_id);
            $l_daoNagios->saveContacts($l_contactID, $_POST["C__MODULE__NAGIOS__CONTACTS__HIDDEN"]);

            if (!$l_daoNagios->apply_update())
            {
                throw new isys_exception_dao(
                    $l_daoNagios->get_database_component()
                        ->get_last_error_as_string()
                );
            } // if
        }
        catch (isys_exception_dao $e)
        {
            $_POST["navMode"] = C__NAVMODE__EDIT;
        } // try

        $l_opt = $l_daoNagios->getHostEscalationOptions();

        $l_assOpt = explode(",", $_POST["C__MODULE__NAGIOS__ESCALATION_OPTIONS__selected_values"]);

        foreach ($l_opt as $l_key => $l_val)
        {
            $l_optArr[] = [
                "id"  => $l_key,
                "val" => $l_val,
                "sel" => (int) in_array($l_key, $l_assOpt),
                "url" => ""
            ];
        } // foreach

        $l_rules["C__MODULE__NAGIOS__CONTACTS"]["p_strSelectedID"]               = $_POST["C__MODULE__NAGIOS__CONTACTS__HIDDEN"];
        $l_rules["C__MODULE__NAGIOS__ESCALATION_OPTIONS"]["p_arData"]            = serialize($l_optArr);
        $l_rules["C__MODULE__NAGIOS__ESCALATION_PERIOD"]["p_arData"]             = serialize($l_daoNagios->getTimeperiodsAssoc());
        $l_rules["C__MODULE__NAGIOS__ESCALATION_PERIOD"]["p_strSelectedID"]      = $_POST["C__MODULE__NAGIOS__ESCALATION_PERIOD"];
        $l_rules["C__MODULE__NAGIOS__ESCALATION_PERIOD_PLUS"]["p_strSelectedID"] = $_POST["C__MODULE__NAGIOS__ESCALATION_PERIOD_PLUS"];
        $l_rules["C__MODULE__NAGIOS__TITLE"]["p_strValue"]                       = $_POST["C__MODULE__NAGIOS__TITLE"];
        $l_rules["C__MODULE__NAGIOS__FIRST_NOTIFICATION"]["p_strValue"]          = $_POST["C__MODULE__NAGIOS__FIRST_NOTIFICATION"];
        $l_rules["C__MODULE__NAGIOS__LAST_NOTIFICATION"]["p_strValue"]           = $_POST["C__MODULE__NAGIOS__LAST_NOTIFICATION"];
        $l_rules["C__MODULE__NAGIOS__NOTIFICATION_INTERVAL"]["p_strValue"]       = $_POST["C__MODULE__NAGIOS__NOTIFICATION_INTERVAL"];

        isys_component_template_navbar::getInstance()
            ->set_active(true, C__NAVBAR_BUTTON__EDIT)
            ->set_active(true, C__NAVBAR_BUTTON__DELETE);

        $g_comp_template->assign("eID", $l_id)
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);

        $index_includes['contentbottomcontent'] = "modules/nagios/escalation.tpl";
    } // function

    /**
     *
     * @throws  isys_exception_dao
     */
    public function saveServiceEscalation()
    {
        global $g_comp_database, $g_comp_template, $index_includes;

        $l_rules = [];

        $l_daoNagios = new isys_component_dao_nagios($g_comp_database);

        try
        {
            if (!isset($_POST["id"]) || empty($_POST["id"]))
            {
                $l_id = $l_daoNagios->createServiceEscalation();
            }
            else
            {
                $l_id = $_POST["id"];
            } // if

            $l_daoNagios->saveServiceEscalation($l_id);
            $l_contactID = $l_daoNagios->getServiceEscalationContactID($l_id);
            $l_daoNagios->saveContacts($l_contactID, $_POST["C__MODULE__NAGIOS__CONTACTS__HIDDEN"]);

            if (!$l_daoNagios->apply_update())
            {
                throw new isys_exception_dao(
                    $l_daoNagios->get_database_component()
                        ->get_last_error_as_string()
                );
            } // if
        }
        catch (isys_exception_dao $e)
        {
            $_POST["navMode"] = C__NAVMODE__EDIT;
        }

        $l_opt = $l_daoNagios->getServiceEscalationOptions();

        $l_assOpt = explode(",", $_POST["C__MODULE__NAGIOS__ESCALATION_OPTIONS__selected_values"]);

        foreach ($l_opt as $l_key => $l_val)
        {
            $l_optArr[] = [
                "id"  => $l_key,
                "val" => $l_val,
                "sel" => (int) in_array($l_key, $l_assOpt),
                "url" => ""
            ];
        } // foreach

        $l_rules["C__MODULE__NAGIOS__CONTACTS"]["p_strSelectedID"]               = $_POST["C__MODULE__NAGIOS__CONTACTS__HIDDEN"];
        $l_rules["C__MODULE__NAGIOS__ESCALATION_OPTIONS"]["p_arData"]            = serialize($l_optArr);
        $l_rules["C__MODULE__NAGIOS__ESCALATION_PERIOD"]["p_arData"]             = serialize($l_daoNagios->getTimeperiodsAssoc());
        $l_rules["C__MODULE__NAGIOS__ESCALATION_PERIOD"]["p_strSelectedID"]      = $_POST["C__MODULE__NAGIOS__ESCALATION_PERIOD"];
        $l_rules["C__MODULE__NAGIOS__ESCALATION_PERIOD_PLUS"]["p_strSelectedID"] = $_POST["C__MODULE__NAGIOS__ESCALATION_PERIOD_PLUS"];
        $l_rules["C__MODULE__NAGIOS__TITLE"]["p_strValue"]                       = $_POST["C__MODULE__NAGIOS__TITLE"];
        $l_rules["C__MODULE__NAGIOS__FIRST_NOTIFICATION"]["p_strValue"]          = $_POST["C__MODULE__NAGIOS__FIRST_NOTIFICATION"];
        $l_rules["C__MODULE__NAGIOS__LAST_NOTIFICATION"]["p_strValue"]           = $_POST["C__MODULE__NAGIOS__LAST_NOTIFICATION"];
        $l_rules["C__MODULE__NAGIOS__NOTIFICATION_INTERVAL"]["p_strValue"]       = $_POST["C__MODULE__NAGIOS__NOTIFICATION_INTERVAL"];

        isys_component_template_navbar::getInstance()
            ->set_active(true, C__NAVBAR_BUTTON__EDIT)
            ->set_active(true, C__NAVBAR_BUTTON__DELETE);

        $g_comp_template->assign("eID", $l_id)
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);

        $index_includes['contentbottomcontent'] = "modules/nagios/escalation.tpl";
    } // function

    /**
     * Saves a command with its parameters provided by $_POST
     *
     * @return void
     *
     */
    public function saveCommand()
    {
        global $g_comp_database;
        global $g_comp_template;
        global $index_includes;

        isys_component_template_navbar::getInstance()
            ->set_active(true, C__NAVBAR_BUTTON__EDIT)
            ->set_active(true, C__NAVBAR_BUTTON__DELETE);

        $l_rules = [];

        $l_daoNagios = new isys_component_dao_nagios($g_comp_database);

        try
        {
            if (!isset($_POST["id"]) || $_POST["id"] == "")
            {
                $l_id = $l_daoNagios->createCommand();
            }
            else
            {
                $l_id = $_POST["id"];
            } // if

            $l_daoNagios->validateCommandPost($_POST);
            $l_daoNagios->saveCommand($l_id);
        }
        catch (UnexpectedValueException $e)
        {
            switch ($e->getCode())
            {
                case 0:
                    $l_rules["C__MODULE__NAGIOS__COMMAND_NAME"]["p_strInfoIconError"] = $e->getMessage();
                    break;

                case 1:
                    $l_rules["C__MODULE__NAGIOS__COMMAND_LINE"]["p_strInfoIconError"] = $e->getMessage();
                    break;

                case 2:
                    $l_rules["C__MODULE__NAGIOS__COMMAND_NAME"]["p_strInfoIconError"] = $e->getMessage();
                    $l_rules["C__MODULE__NAGIOS__COMMAND_LINE"]["p_strInfoIconError"] = $e->getMessage();
                    break;
            } // switch

            $_POST["navMode"] = C__NAVMODE__EDIT;
        } // try

        $l_rules["C__MODULE__NAGIOS__COMMAND_NAME"]["p_strValue"]        = $_POST["C__MODULE__NAGIOS__COMMAND_NAME"];
        $l_rules["C__MODULE__NAGIOS__COMMAND_LINE"]["p_strValue"]        = $_POST["C__MODULE__NAGIOS__COMMAND_LINE"];
        $l_rules["C__MODULE__NAGIOS__COMMAND_DESCRIPTION"]["p_strValue"] = $_POST["C__MODULE__NAGIOS__COMMAND_DESCRIPTION"];

        $g_comp_template->assign("cID", $l_id)
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);

        $index_includes['contentbottomcontent'] = "modules/nagios/commands.tpl";
    }

    /**
     * Gathers all Nagios-relevant information from i-doit and writes a config into the local filesystem. Objects, which are not
     * well-defined and all depending objects will be skipped during this process to avoid invalid confgurations.
     *
     * @param   array   $p_monitoring_export_config
     * @param   boolean $p_validate
     *
     * @return  isys_nagios_export
     * @throws  isys_exception_filesystem
     */
    public function exportNagiosConfig($p_monitoring_export_config, $p_validate = false)
    {
        $l_config = [
            'export_dir'    => $p_monitoring_export_config['isys_monitoring_export_config__path'],
            'export_config' => $p_monitoring_export_config,
            'export_subdir' => 'objects',
            'debug'         => true,
            'validation'    => $p_validate
        ];

        return isys_nagios_export::instance()
            ->init_export($l_config)
            ->start_export();
    }

    private function processAjaxRequest()
    {
        global $g_comp_session;
        $g_comp_session->write_close();

        switch (isys_glob_get_param("request"))
        {
            case "nagios_host_state_table":
                global $g_comp_database;

                if (!isys_glob_get_param("objID"))
                {
                    die ("No ID given");
                }
                else
                {
                    $l_id = isys_glob_get_param("objID");
                } // if

                $l_daoCMDBNagios = isys_cmdb_dao_category_g_nagios::instance($g_comp_database);
                $l_daoNagios     = new isys_component_dao_nagios($g_comp_database);

                $l_catData = $l_daoCMDBNagios->getCatDataById($l_id);

                if (!$l_daoNagios->is_ndo_instance_active($l_catData["isys_catg_monitoring_list__isys_monitoring_hosts__id"]))
                {
                    die;
                } // if

                $l_ndo = isys_monitoring_ndo::factory($l_catData["isys_catg_monitoring_list__isys_monitoring_hosts__id"]);

                try
                {
                    $l_daoNagios->set_ndo($l_ndo->get_db_connection(), $l_ndo->get_db_prefix());

                    $l_host = $l_daoNagios->getHostStatusTable($l_catData);
                }
                catch (Exception $e)
                {
                    die($e->getMessage());
                }

                echo $l_host;

                break;

            case "nagios_host_state":
                global $g_comp_database;

                // Enable cache lifetime of 2 minutes
                // isys_core::expire(120);

                if (!isys_glob_get_param("objID"))
                {
                    die ("No ID given");
                }
                else
                {
                    $l_id = isys_glob_get_param("objID");
                } // if

                $l_daoCMDBNagios = isys_cmdb_dao_category_g_nagios::instance($g_comp_database);
                $l_daoNagios     = new isys_component_dao_nagios($g_comp_database);

                $l_catData = $l_daoCMDBNagios->getCatDataById($l_id);

                if (!$l_daoNagios->is_ndo_instance_active($l_catData["isys_catg_monitoring_list__isys_monitoring_hosts__id"]))
                {
                    die;
                } // if

                try
                {
                    $l_ndo = isys_monitoring_ndo::factory($l_catData["isys_catg_monitoring_list__isys_monitoring_hosts__id"]);

                    $l_daoNagios->set_ndo($l_ndo->get_db_connection(), $l_ndo->get_db_prefix());

                    $l_host = $l_daoNagios->getHostStatus($l_catData);
                }
                catch (Exception $e)
                {
                    $l_host = $e->getMessage();
                } // try

                echo $l_host;

                break;

            case "nagios_service_state_table":
                global $g_comp_database;

                if (!isys_glob_get_param("hostObjID") || !isys_glob_get_param("service_description"))
                {
                    die ("Insufficient parameters given");
                }
                else
                {
                    $l_hostID = isys_glob_get_param("hostObjID");
                    $l_sDesc  = isys_glob_get_param("service_description");
                }

                $l_daoCMDBNagios = isys_cmdb_dao_category_g_nagios::instance($g_comp_database);
                $l_daoNagios     = new isys_component_dao_nagios($g_comp_database);

                $l_catData = $l_daoCMDBNagios->getCatDataById($l_hostID);

                if (!$l_daoNagios->is_ndo_instance_active($l_catData["isys_catg_monitoring_list__isys_monitoring_hosts__id"]))
                {
                    die;
                } // if

                $l_ndo = isys_monitoring_ndo::factory($l_catData["isys_catg_monitoring_list__isys_monitoring_hosts__id"]);

                $l_daoNagios->set_ndo($l_ndo->get_db_connection(), $l_ndo->get_db_prefix());

                echo $l_daoNagios->getServiceStatusTable($l_catData, $l_sDesc);
                break;

            case "nagios_service_state":
                global $g_comp_database;

                if (!isys_glob_get_param("hostObjID") || !isys_glob_get_param("service_description"))
                {
                    die ("Insufficient parameters given");
                }
                else
                {
                    $l_hostID = isys_glob_get_param("hostObjID");
                    $l_sDesc  = isys_glob_get_param("service_description");
                }

                $l_daoCMDBNagios = isys_cmdb_dao_category_g_nagios::instance($g_comp_database);
                $l_daoNagios     = new isys_component_dao_nagios($g_comp_database);

                $l_catData = $l_daoCMDBNagios->getCatDataById($l_hostID);

                if (!$l_daoNagios->is_ndo_instance_active($l_catData["isys_catg_monitoring_list__isys_monitoring_hosts__id"]))
                {
                    die;
                }

                $l_ndo = isys_monitoring_ndo::factory($l_catData["isys_catg_monitoring_list__isys_monitoring_hosts__id"]);

                $l_daoNagios->set_ndo($l_ndo->get_db_connection(), $l_ndo->get_db_prefix());

                echo $l_daoNagios->getServiceStatus($l_catData, $l_sDesc);
                break;
            case "export":
                if ($_GET["hid"])
                {
                    global $g_comp_database, $g_comp_template;

                    $l_row = isys_monitoring_dao_hosts::instance($g_comp_database)
                        ->get_export_data($_GET["hid"])
                        ->get_row();

                    if (empty($l_row['isys_monitoring_export_config__path']))
                    {
                        $l_row['isys_monitoring_export_config__path'] = "nagiosexport";
                    } // if

                    $l_export = $this->exportNagiosConfig($l_row, (bool) $_GET['validate']);

                    $l_log_file = isys_helper_link::get_base() . str_replace(
                            DS,
                            '/',
                            strstr(
                                $l_export->get_log()
                                    ->get_log_file(),
                                'temp'
                            )
                        );

                    $g_comp_template->assign("nagios_dir", $l_row['isys_monitoring_export_config__path'])
                        ->assign("exportdest", "Nagios config written to " . $l_row['isys_monitoring_export_config__path'])
                        ->assign("logfile", $l_log_file)
                        ->display("modules/nagios/export_done.tpl");
                }
                else
                {
                    echo isys_glob_utf8_encode("Error exporting: No ID for Nagioshost given");
                } // if
                break;
        } // switch
    }

    /**
     * Method for processing the configuration page.
     *
     * @global  array                   $index_includes
     * @global  isys_component_template $g_comp_template
     * @global  isys_component_database $g_comp_database
     */
    private function processConfigPage()
    {
        global $index_includes, $g_comp_template, $g_comp_database;

        $l_rules      = [];
        $l_config_res = isys_component_dao_nagios::instance($g_comp_database)
            ->getConfig();

        $l_edit_right = isys_auth_nagios::instance()
            ->is_allowed_to(isys_auth::EDIT, 'NAGIOS_CONFIG');

        if (count($l_config_res) > 0)
        {
            while ($l_row = $l_config_res->get_row())
            {
                // Workaround... Because dialog field :(
                if ($l_row["key"] == 'PERSON_NAME_OPTION')
                {
                    $l_rules["C__MODULE__NAGIOS__" . $l_row["key"]]["p_strSelectedID"] = $l_row["value"];
                } // if

                $l_rules["C__MODULE__NAGIOS__" . $l_row["key"]]["p_strValue"] = $l_row["value"];
            } // while
        } // if

        $l_rules['C__MODULE__NAGIOS__PERSON_NAME_OPTION']['p_arData'] = serialize(
            [
                C__NAGIOS__PERSON_OPTION__OBJECT_TITLE => _L('LC__UNIVERSAL__OBJECT_TITLE'),
                C__NAGIOS__PERSON_OPTION__USERNAME     => _L('LC__LOGIN__USERNAME')
            ]
        );

        $l_resource_files = unserialize($l_rules['C__MODULE__NAGIOS__resource_file']['p_strValue']);
        $l_broker_module  = unserialize($l_rules['C__MODULE__NAGIOS__broker_module']['p_strValue']);
        $l_cfg_file       = unserialize($l_rules['C__MODULE__NAGIOS__cfg_file']['p_strValue']);
        $l_cfg_dir        = unserialize($l_rules['C__MODULE__NAGIOS__cfg_dir']['p_strValue']);

        if ($l_resource_files === false)
        {
            $l_resource_files = [];
        } // if

        if ($l_broker_module === false)
        {
            $l_broker_module = [];
        } // if

        if ($l_cfg_file === false)
        {
            $l_cfg_file = [];
        } // if

        if ($l_cfg_dir === false)
        {
            $l_cfg_dir = [];
        } // if

        if ($_POST[C__GET__NAVMODE] == C__NAVMODE__EDIT)
        {
            isys_component_template_navbar::getInstance()
                ->set_active(true, C__NAVBAR_BUTTON__SAVE)
                ->set_active(true, C__NAVBAR_BUTTON__CANCEL);
        }
        else
        {
            isys_component_template_navbar::getInstance()
                ->set_active($l_edit_right, C__NAVBAR_BUTTON__EDIT);
        } // if

        $g_comp_template->assign('resource_files', $l_resource_files)
            ->assign('broker_modules', $l_broker_module)
            ->assign('cfg_files', $l_cfg_file)
            ->assign('cfg_dirs', $l_cfg_dir)
            ->assign('object_dir', 'objects' . DS)
            ->assign('content_title', 'Nagios Config')
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);

        $index_includes['contentbottomcontent'] = 'modules/nagios/config.tpl';
    }

    /**
     * Method for saving the Nagios configuration.
     *
     * @global  isys_component_database $g_comp_database
     */
    private function saveConfig()
    {
        global $g_comp_database;

        isys_component_dao_nagios::instance($g_comp_database)
            ->saveConfig($_POST);

        $this->processConfigPage();
    }

    private function processExportPage()
    {
        global $index_includes, $g_comp_template, $g_comp_database;

        $l_rules                                                = [];
        $l_rules['C__MODULE__NAGIOS__NAGIOSHOST']['p_arData']   = serialize(
            isys_component_dao_nagios::instance($g_comp_database)
                ->getNagiosHostsAssoc()
        );
        $l_rules['C__MODULE__NAGIOS__NAGIOSHOST']['p_strClass'] = 'input input-small';
        $l_rules['C__EXPORT_WITH_VALIDATION']['p_arData']       = serialize(get_smarty_arr_YES_NO());
        $l_rules['C__EXPORT_WITH_VALIDATION']['p_strClass']     = 'input input-mini';

        $g_comp_template->assign('ajax_url', '?' . C__GET__MODULE_ID . '=' . C__MODULE__NAGIOS . '&request=export&ajax=1')
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);

        $index_includes['contentbottomcontent'] = "modules/nagios/export.tpl";
    }

    private function processTimeperiodsListing()
    {
        global $index_includes;
        global $g_comp_template;
        global $g_comp_database;

        $l_edit_right   = isys_auth_nagios::instance()
            ->is_allowed_to(isys_auth::EDIT, 'NAGIOS_TIMEPERIODS');
        $l_delete_right = isys_auth_nagios::instance()
            ->is_allowed_to(isys_auth::DELETE, 'NAGIOS_TIMEPERIODS');

        $l_daoNagios = new isys_component_dao_nagios($g_comp_database);

        $l_list = new isys_component_list();

        $l_list_headers = [
            "id"   => "ID",
            "name" => "Name"
        ];

        $l_timeperiods_result = $l_daoNagios->getTimeperiods();
        $l_data_count         = $l_timeperiods_result->num_rows();

        $l_list->set_data(null, $l_timeperiods_result);
        $l_list->config(
            $l_list_headers,
            isys_glob_build_url(http_build_query($_GET) . "&" . C__GET__NAGIOS_TPID . "=[{id}]"),
            "[{id}]"
        );

        if ($l_list->createTempTable())
        {
            $g_comp_template->assign(
                "objectTableList",
                $l_list->getTempTableHtml()
            );
        }

        isys_component_template_navbar::getInstance()
            ->set_active($l_edit_right, C__NAVBAR_BUTTON__NEW)
            ->set_active(($l_edit_right && count($l_data_count) > 0), C__NAVBAR_BUTTON__EDIT)
            ->set_active(($l_delete_right && count($l_data_count) > 0), C__NAVBAR_BUTTON__DELETE)
            ->set_visible(true, C__NAVBAR_BUTTON__NEW)
            ->set_visible(true, C__NAVBAR_BUTTON__EDIT)
            ->set_visible(true, C__NAVBAR_BUTTON__DELETE);

        $g_comp_template->assign(
            "content_title",
            "Timeperiods"
        );
        $g_comp_template->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1");
        $index_includes['contentbottomcontent'] = "content/bottom/content/object_table_list.tpl";
    }

    private function processTimeperiodsPage($p_id)
    {
        $l_navbar = isys_component_template_navbar::getInstance();
        global $index_includes;
        global $g_comp_database;
        global $g_comp_template;

        $l_rules = [];

        $l_edit_right   = isys_auth_nagios::instance()
            ->is_allowed_to(isys_auth::EDIT, 'NAGIOS_TIMEPERIODS');
        $l_delete_right = isys_auth_nagios::instance()
            ->is_allowed_to(isys_auth::DELETE, 'NAGIOS_TIMEPERIODS');

        if ($_POST[C__GET__NAVMODE] == C__NAVMODE__EDIT)
        {
            $l_navbar->set_selected(true, C__NAVBAR_BUTTON__EDIT)
                ->set_active(true, C__NAVBAR_BUTTON__SAVE)
                ->set_active(true, C__NAVBAR_BUTTON__CANCEL);
        }
        else
        {
            $l_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__EDIT)
                ->set_active($l_delete_right, C__NAVBAR_BUTTON__DELETE)
                ->set_visible(true, C__NAVBAR_BUTTON__EDIT)
                ->set_visible(true, C__NAVBAR_BUTTON__DELETE);
        }

        $l_daoNagios = new isys_component_dao_nagios($g_comp_database);

        $l_tp = $l_daoNagios->getTimeperiod($p_id);

        $l_exclude = $l_daoNagios->getTimeperiodsAssoc();

        $l_assExclude = explode(",", $l_tp["exclude"]);
        foreach ($l_exclude as $l_key => $l_val)
        {
            if (array_search($l_key, $l_assExclude) === false)
            {
                $l_exArr[] = [
                    "id"  => $l_key,
                    "val" => $l_val,
                    "sel" => 0,
                    "url" => ""
                ];
            }
            else
            {
                $l_exArr[] = [
                    "id"  => $l_key,
                    "val" => $l_val,
                    "sel" => 1,
                    "url" => ""
                ];
            } // if
        } // foreach

        $l_rules["C__MODULE__NAGIOS__TIMEPERIOD_EXCLUDE"]["p_arData"]                 = serialize($l_exArr);
        $l_rules["C__MODULE__NAGIOS__DEFAULT_CHECK_PERIOD"]["p_arData"]               = serialize(get_smarty_arr_YES_NO());
        $l_rules["C__MODULE__NAGIOS__DEFAULT_CHECK_PERIOD"]["p_strSelectedID"]        = $l_tp["def_check"];
        $l_rules["C__MODULE__NAGIOS__DEFAULT_NOTIFICATION_PERIOD"]["p_arData"]        = $l_rules["C__MODULE__NAGIOS__DEFAULT_CHECK_PERIOD"]["p_arData"];
        $l_rules["C__MODULE__NAGIOS__DEFAULT_NOTIFICATION_PERIOD"]["p_strSelectedID"] = $l_tp["def_not"];
        $l_rules["C__MODULE__NAGIOS__TIMEPERIOD_NAME"]["p_strValue"]                  = $l_tp["name"];
        $l_rules["C__MODULE__NAGIOS__TIMEPERIOD_ALIAS"]["p_strValue"]                 = $l_tp["alias"];
        $l_rules["C__MODULE__NAGIOS__TIMEPERIOD_DEFINITION"]["p_strValue"]            = $l_tp["definition"];

        $g_comp_template->assign("tpID", $p_id)
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);

        $index_includes['contentbottomcontent'] = "modules/nagios/timeperiods.tpl";
    } // function

    private function createTimeperiod()
    {
        global $g_comp_template;
        global $index_includes;

        $l_rules = [];

        isys_component_template_navbar::getInstance()
            ->set_selected(true, C__NAVBAR_BUTTON__NEW);

        $_POST[C__GET__NAVMODE] = C__NAVMODE__EDIT;

        $g_comp_template->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
        $index_includes['contentbottomcontent'] = "modules/nagios/timeperiods.tpl";
    } // function

    private function saveTimeperiod()
    {
        global $g_comp_database;
        global $g_comp_template;
        global $index_includes;

        isys_component_template_navbar::getInstance()
            ->set_active(true, C__NAVBAR_BUTTON__EDIT)
            ->set_active(true, C__NAVBAR_BUTTON__DELETE);

        $l_rules = [];

        $l_daoNagios = new isys_component_dao_nagios($g_comp_database);

        try
        {
            if (empty($_POST["id"]))
            {
                $l_id = $l_daoNagios->createTimeperiod();
            }
            else
            {
                $l_id = $_POST["id"];
            } // if

            $l_daoNagios->validateTimeperiodPost($_POST);
            $l_daoNagios->saveTimeperiod($l_id);
        }
        catch (UnexpectedValueException $e)
        {
            switch ($e->getCode())
            {
                case 0:
                    $l_rules["C__MODULE__NAGIOS__TIMEPERIOD_NAME"]["p_strInfoIconError"] = $e->getMessage();
                    break;

                case 1:
                    $l_rules["C__MODULE__NAGIOS__TIMEPERIOD_ALIAS"]["p_strInfoIconError"] = $e->getMessage();
                    break;

                case 2:
                    $l_rules["C__MODULE__NAGIOS__TIMEPERIOD_NAME"]["p_strInfoIconError"]  = $e->getMessage();
                    $l_rules["C__MODULE__NAGIOS__TIMEPERIOD_ALIAS"]["p_strInfoIconError"] = $e->getMessage();
                    break;
            } // switch

            $_POST["navMode"] = C__NAVMODE__EDIT;
        } // try

        $l_exclude = $l_daoNagios->getTimeperiodsAssoc();

        $l_assExclude = explode(",", $_POST["C__MODULE__NAGIOS__TIMEPERIOD_EXCLUDE__selected_values"]);
        foreach ($l_exclude as $l_key => $l_val)
        {
            $l_exArr[] = [
                "id"  => $l_key,
                "val" => $l_val,
                "sel" => (int) in_array($l_key, $l_assExclude),
                "url" => ""
            ];
        }

        $l_rules["C__MODULE__NAGIOS__TIMEPERIOD_EXCLUDE"]["p_arData"]                 = serialize($l_exArr);
        $l_rules["C__MODULE__NAGIOS__DEFAULT_CHECK_PERIOD"]["p_arData"]               = serialize(get_smarty_arr_YES_NO());
        $l_rules["C__MODULE__NAGIOS__DEFAULT_CHECK_PERIOD"]["p_strSelectedID"]        = $_POST["C__MODULE__NAGIOS__DEFAULT_CHECK_PERIOD"];
        $l_rules["C__MODULE__NAGIOS__DEFAULT_NOTIFICATION_PERIOD"]["p_arData"]        = $l_rules["C__MODULE__NAGIOS__DEFAULT_CHECK_PERIOD"]["p_arData"];
        $l_rules["C__MODULE__NAGIOS__DEFAULT_NOTIFICATION_PERIOD"]["p_strSelectedID"] = $_POST["C__MODULE__NAGIOS__DEFAULT_NOTIFICATION_PERIOD"];
        $l_rules["C__MODULE__NAGIOS__TIMEPERIOD_NAME"]["p_strValue"]                  = $_POST["C__MODULE__NAGIOS__TIMEPERIOD_NAME"];
        $l_rules["C__MODULE__NAGIOS__TIMEPERIOD_ALIAS"]["p_strValue"]                 = $_POST["C__MODULE__NAGIOS__TIMEPERIOD_ALIAS"];
        $l_rules["C__MODULE__NAGIOS__TIMEPERIOD_DEFINITION"]["p_strValue"]            = $_POST["C__MODULE__NAGIOS__TIMEPERIOD_DEFINITION"];

        $g_comp_template->assign(
            "tpID",
            $l_id
        );
        $g_comp_template->smarty_tom_add_rules(
            "tom.content.bottom.content",
            $l_rules
        );
        $index_includes['contentbottomcontent'] = "modules/nagios/timeperiods.tpl";
    }

    private function deleteCommand()
    {
        global $g_comp_database;

        $l_daoNagios = new isys_component_dao_nagios($g_comp_database);
        try
        {
            if (isset($_POST["id"]))
            {
                if (is_array($_POST["id"]))
                {
                    foreach ($_POST["id"] as $l_key => $l_value)
                    {
                        $l_daoNagios->deleteCommand($l_value);
                    }
                }
                else if ($_POST["id"] != "")
                {
                    $l_daoNagios->deleteCommand($_POST["id"]);
                }
            }
            else
            {
                throw new Exception("No id given");
            }
        }
        catch (Exception $e)
        {
            $l_tError = $e->getMessage();
        }
        $this->processCommandsListing();
    } // function

    private function deleteTimeperiod()
    {
        global $g_comp_database;

        $l_daoNagios = new isys_component_dao_nagios($g_comp_database);
        try
        {
            if (isset($_POST["id"]))
            {
                if (is_array($_POST["id"]))
                {
                    foreach ($_POST["id"] as $l_key => $l_value)
                    {
                        $l_daoNagios->deleteTimeperiod($l_value);
                    }
                }
                else if ($_POST["id"] != "")
                {
                    $l_daoNagios->deleteTimeperiod($_POST["id"]);
                }
            }
            else
            {
                throw new Exception("No id given");
            }
        }
        catch (Exception $e)
        {
            $l_tError = $e->getMessage();
        }
        $this->processTimeperiodsListing();
    } // function

    private function processHostEscalationsListing()
    {
        global $index_includes;
        global $g_comp_template;
        global $g_comp_database;
        $l_navbar = isys_component_template_navbar::getInstance();

        $l_edit_right   = isys_auth_nagios::instance()
            ->is_allowed_to(isys_auth::EDIT, 'NAGIOS_HOST_ESCALATIONS');
        $l_delete_right = isys_auth_nagios::instance()
            ->is_allowed_to(isys_auth::DELETE, 'NAGIOS_HOST_ESCALATIONS');

        $l_list         = new isys_component_list();
        $l_list_headers = [
            "id"    => "ID",
            "title" => "Name"
        ];
        $l_list_data    = [];

        $l_daoNagios = new isys_component_dao_nagios($g_comp_database);
        $l_list_data = $l_daoNagios->getHostEscalations();

        $l_list->set_data($l_list_data);
        $l_list->config(
            $l_list_headers,
            isys_glob_build_url(http_build_query($_GET) . "&" . C__GET__NAGIOS_EID . "=[{id}]"),
            "[{id}]"
        );

        $l_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__NEW)
            ->set_active(($l_edit_right && count($l_list_data) > 0), C__NAVBAR_BUTTON__EDIT)
            ->set_active(($l_delete_right && count($l_list_data) > 0), C__NAVBAR_BUTTON__DELETE)
            ->set_visible(true, C__NAVBAR_BUTTON__NEW)
            ->set_visible(true, C__NAVBAR_BUTTON__EDIT)
            ->set_visible(true, C__NAVBAR_BUTTON__DELETE);

        if ($l_list->createTempTable())
        {
            $g_comp_template->assign(
                "objectTableList",
                $l_list->getTempTableHtml()
            );
        }

        $g_comp_template->assign(
            "content_title",
            "Host escalations"
        );
        $g_comp_template->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1");
        $index_includes['contentbottomcontent'] = "content/bottom/content/object_table_list.tpl";
    } // function

    /**
     *
     */
    private function createHostEscalation()
    {
        global $g_comp_database, $g_comp_template, $index_includes;

        $l_rules = [];

        isys_component_template_navbar::getInstance()
            ->set_selected(true, C__NAVBAR_BUTTON__NEW);

        $l_daoNagios = new isys_component_dao_nagios($g_comp_database);

        $_POST[C__GET__NAVMODE] = C__NAVMODE__EDIT;

        $l_opt = $l_daoNagios->getHostEscalationOptions();

        foreach ($l_opt as $l_key => $l_val)
        {
            $l_optArr[] = [
                "id"  => $l_key,
                "val" => $l_val,
                "sel" => 0,
                "url" => ""
            ];
        } // foreach

        $l_rules["C__MODULE__NAGIOS__ESCALATION_PERIOD"]["p_arData"]  = serialize($l_daoNagios->getTimeperiodsAssoc());
        $l_rules["C__MODULE__NAGIOS__ESCALATION_OPTIONS"]["p_arData"] = serialize($l_optArr);

        $g_comp_template->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);

        $index_includes['contentbottomcontent'] = "modules/nagios/escalation.tpl";
    } // function

    private function createServiceEscalation()
    {
        global $g_comp_database;
        global $g_comp_template;
        global $index_includes;

        $l_rules = [];

        isys_component_template_navbar::getInstance()
            ->set_selected(true, C__NAVBAR_BUTTON__NEW);

        $l_daoNagios = new isys_component_dao_nagios($g_comp_database);

        $_POST[C__GET__NAVMODE] = C__NAVMODE__EDIT;

        // rules
        $l_opt = $l_daoNagios->getServiceEscalationOptions();

        foreach ($l_opt as $l_key => $l_val)
        {
            $l_optArr[] = [
                "id"  => $l_key,
                "val" => $l_val,
                "sel" => 0,
                "url" => ""
            ];
        }

        $l_rules["C__MODULE__NAGIOS__ESCALATION_PERIOD"]["p_arData"]  = serialize($l_daoNagios->getTimeperiodsAssoc());
        $l_rules["C__MODULE__NAGIOS__ESCALATION_OPTIONS"]["p_arData"] = serialize($l_optArr);

        $g_comp_template->smarty_tom_add_rules(
            "tom.content.bottom.content",
            $l_rules
        );
        $index_includes['contentbottomcontent'] = "modules/nagios/escalation.tpl";
    } // function

    /**
     * Method for processing the host escalation-page.
     *
     * @param  integer $p_id
     */
    private function processHostEscalationsPage($p_id)
    {
        global $index_includes, $g_comp_database, $g_comp_template;

        // Prepare the navigation-bar.
        $l_navbar = isys_component_template_navbar::getInstance();

        // Prepare the rules-array.
        $l_rules = [];

        $l_edit_right   = isys_auth_nagios::instance()
            ->is_allowed_to(isys_auth::EDIT, 'NAGIOS_HOST_ESCALATIONS');
        $l_delete_right = isys_auth_nagios::instance()
            ->is_allowed_to(isys_auth::DELETE, 'NAGIOS_HOST_ESCALATIONS');

        if ($_POST["navMode"] == C__NAVMODE__EDIT)
        {
            $l_navbar->set_selected(true, C__NAVBAR_BUTTON__EDIT)
                ->set_active(true, C__NAVBAR_BUTTON__SAVE)
                ->set_active(true, C__NAVBAR_BUTTON__CANCEL);
        }
        else
        {
            $l_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__EDIT)
                ->set_active($l_delete_right, C__NAVBAR_BUTTON__DELETE);
        }

        $l_daoNagios = new isys_component_dao_nagios($g_comp_database);

        $l_he = $l_daoNagios->getHostEscalation($p_id);

        $l_opt = $l_daoNagios->getHostEscalationOptions();

        $l_assOpt = explode(
            ",",
            $l_he["isys_nagios_host_escalations__escalation_options"]
        );

        foreach ($l_opt as $l_key => $l_val)
        {
            if (array_search(
                    $l_key,
                    $l_assOpt
                ) === false
            )
            {
                $l_optArr[] = [
                    "id"  => $l_key,
                    "val" => $l_val,
                    "sel" => 0,
                    "url" => ""
                ];
            }
            else
            {
                $l_optArr[] = [
                    "id"  => $l_key,
                    "val" => $l_val,
                    "sel" => 1,
                    "url" => ""
                ];
            } // if
        } // foreach

        $l_contactID = $l_daoNagios->getHostEscalationContactID($p_id);

        $l_daoContact = new isys_contact_dao_reference($g_comp_database);
        $l_daoContact->load($l_contactID);
        $l_data_items = $l_daoContact->get_data_item_array();

        if (is_array($l_data_items))
        {
            $l_persons = array_keys($l_data_items);
        } // if

        if (count($l_persons) > 0)
        {
            $l_rules["C__MODULE__NAGIOS__CONTACTS"]["p_strSelectedID"] = implode(
                ',',
                $l_persons
            );
        } // if

        $l_rules["C__MODULE__NAGIOS__ESCALATION_OPTIONS"]["p_arData"]            = serialize($l_optArr);
        $l_rules["C__MODULE__NAGIOS__ESCALATION_PERIOD"]["p_arData"]             = serialize($l_daoNagios->getTimeperiodsAssoc());
        $l_rules["C__MODULE__NAGIOS__ESCALATION_PERIOD"]["p_strSelectedID"]      = $l_he["isys_nagios_host_escalations__escalation_period"];
        $l_rules["C__MODULE__NAGIOS__ESCALATION_PERIOD_PLUS"]["p_strSelectedID"] = $l_he["isys_nagios_host_escalations__escalation_period_plus"];
        $l_rules["C__MODULE__NAGIOS__TITLE"]["p_strValue"]                       = $l_he["isys_nagios_host_escalations__title"];
        $l_rules["C__MODULE__NAGIOS__FIRST_NOTIFICATION"]["p_strValue"]          = $l_he["isys_nagios_host_escalations__first_notification"];
        $l_rules["C__MODULE__NAGIOS__LAST_NOTIFICATION"]["p_strValue"]           = $l_he["isys_nagios_host_escalations__last_notification"];
        $l_rules["C__MODULE__NAGIOS__NOTIFICATION_INTERVAL"]["p_strValue"]       = $l_he["isys_nagios_host_escalations__notification_interval"];

        $g_comp_template->assign(
            "eID",
            $p_id
        );
        $g_comp_template->smarty_tom_add_rules(
            "tom.content.bottom.content",
            $l_rules
        );
        $index_includes['contentbottomcontent'] = "modules/nagios/escalation.tpl";
    }

    /**
     * Method for processing the service-escalations page.
     *
     * @param   integer $p_id
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    private function processServiceEscalationsPage($p_id)
    {
        global $index_includes, $g_comp_database, $g_comp_template;

        $l_edit_right   = isys_auth_nagios::instance()
            ->is_allowed_to(isys_auth::EDIT, 'NAGIOS_SERVICE_ESCALATIONS');
        $l_delete_right = isys_auth_nagios::instance()
            ->is_allowed_to(isys_auth::DELETE, 'NAGIOS_SERVICE_ESCALATIONS');

        // Navbar instance
        $l_navbar = isys_component_template_navbar::getInstance();

        // Prepare the rules-array.
        $l_rules = [];

        if ($_POST["navMode"] == C__NAVMODE__EDIT)
        {
            $l_navbar->set_selected(true, C__NAVBAR_BUTTON__EDIT)
                ->set_active(true, C__NAVBAR_BUTTON__SAVE)
                ->set_active(true, C__NAVBAR_BUTTON__CANCEL);
        }
        else
        {
            $l_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__EDIT)
                ->set_active($l_delete_right, C__NAVBAR_BUTTON__DELETE)
                ->set_visible(true, C__NAVBAR_BUTTON__EDIT)
                ->set_visible(true, C__NAVBAR_BUTTON__DELETE);
        } // if

        $l_daoNagios = new isys_component_dao_nagios($g_comp_database);

        $l_he = $l_daoNagios->getServiceEscalation($p_id);

        $l_opt = $l_daoNagios->getServiceEscalationOptions();

        $l_assOpt = explode(",", $l_he["isys_nagios_service_escalations__escalation_options"]);

        foreach ($l_opt as $l_key => $l_val)
        {
            $l_optArr[] = [
                "id"  => $l_key,
                "val" => $l_val,
                "sel" => (int) in_array($l_key, $l_assOpt),
                "url" => ""
            ];
        } // foreach

        $l_contactID = $l_daoNagios->getServiceEscalationContactID($p_id);

        $l_daoContact = new isys_contact_dao_reference($g_comp_database);
        $l_daoContact->load($l_contactID);
        $l_data_items = $l_daoContact->get_data_item_array();

        if (is_array($l_data_items))
        {
            $l_persons = array_keys($l_data_items);

            if (count($l_persons) > 0)
            {
                $l_rules["C__MODULE__NAGIOS__CONTACTS"]["p_strSelectedID"] = implode(',', $l_persons);
            } // if
        } // if

        $l_rules["C__MODULE__NAGIOS__ESCALATION_OPTIONS"]["p_arData"]            = serialize($l_optArr);
        $l_rules["C__MODULE__NAGIOS__ESCALATION_PERIOD"]["p_arData"]             = serialize($l_daoNagios->getTimeperiodsAssoc());
        $l_rules["C__MODULE__NAGIOS__ESCALATION_PERIOD_PLUS"]["p_strSelectedID"] = $l_he["isys_nagios_service_escalations__escalation_period_plus"];
        $l_rules["C__MODULE__NAGIOS__ESCALATION_PERIOD"]["p_strSelectedID"]      = $l_he["isys_nagios_service_escalations__escalation_period"];
        $l_rules["C__MODULE__NAGIOS__TITLE"]["p_strValue"]                       = $l_he["isys_nagios_service_escalations__title"];
        $l_rules["C__MODULE__NAGIOS__FIRST_NOTIFICATION"]["p_strValue"]          = $l_he["isys_nagios_service_escalations__first_notification"];
        $l_rules["C__MODULE__NAGIOS__LAST_NOTIFICATION"]["p_strValue"]           = $l_he["isys_nagios_service_escalations__last_notification"];
        $l_rules["C__MODULE__NAGIOS__NOTIFICATION_INTERVAL"]["p_strValue"]       = $l_he["isys_nagios_service_escalations__notification_interval"];

        $g_comp_template->assign("eID", $p_id)
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);

        $index_includes['contentbottomcontent'] = "modules/nagios/escalation.tpl";
    }

    /**
     * Method for deleting host-escalations.
     *
     * @throws Exception
     */
    private function deleteHostEscalation()
    {
        global $g_comp_database;

        $l_daoNagios = new isys_component_dao_nagios($g_comp_database);

        try
        {
            if (isset($_POST["id"]))
            {
                if (is_array($_POST["id"]))
                {
                    foreach ($_POST["id"] as $l_key => $l_value)
                    {
                        $l_daoNagios->deleteHostEscalation($l_value);
                    } // foreach
                }
                else if ($_POST["id"] != "")
                {
                    $l_daoNagios->deleteHostEscalation($_POST["id"]);
                } // if
            }
            else
            {
                throw new Exception("No id given");
            } // if
        }
        catch (Exception $e)
        {
            $l_tError = $e->getMessage();
        } // try

        $this->processHostEscalationsListing();
    }

    /**
     * Method for deleting service-escalations.
     *
     * @throws Exception
     */
    private function deleteServiceEscalation()
    {
        global $g_comp_database;

        $l_daoNagios = new isys_component_dao_nagios($g_comp_database);
        try
        {
            if (isset($_POST["id"]))
            {
                if (is_array($_POST["id"]))
                {
                    foreach ($_POST["id"] as $l_key => $l_value)
                    {
                        $l_daoNagios->deleteServiceEscalation($l_value);
                    } // foreach
                }
                else if ($_POST["id"] != "")
                {
                    $l_daoNagios->deleteServiceEscalation($_POST["id"]);
                } // if
            } // if
            else
            {
                throw new Exception("No id given");
            } // if
        }
        catch (Exception $e)
        {
            $l_tError = $e->getMessage();
        } // try

        $this->processServiceEscalationsListing();
    }

    private function processServiceEscalationsListing()
    {
        global $index_includes;
        global $g_comp_template;
        global $g_comp_database;

        $l_edit_right   = isys_auth_nagios::instance()
            ->is_allowed_to(isys_auth::EDIT, 'NAGIOS_SERVICE_ESCALATIONS');
        $l_delete_right = isys_auth_nagios::instance()
            ->is_allowed_to(isys_auth::DELETE, 'NAGIOS_SERVICE_ESCALATIONS');

        $l_list         = new isys_component_list();
        $l_list_headers = [
            "id"    => "ID",
            "title" => "Name"
        ];

        $l_daoNagios = new isys_component_dao_nagios($g_comp_database);
        $l_list_data = $l_daoNagios->getServiceEscalations();

        $l_list->set_data($l_list_data)
            ->config($l_list_headers, isys_glob_build_url(http_build_query($_GET) . "&" . C__GET__NAGIOS_EID . "=[{id}]"), "[{id}]");

        isys_component_template_navbar::getInstance()
            ->set_active($l_edit_right, C__NAVBAR_BUTTON__NEW)
            ->set_active(($l_edit_right && count($l_list_data) > 0), C__NAVBAR_BUTTON__EDIT)
            ->set_active(($l_delete_right && count($l_list_data) > 0), C__NAVBAR_BUTTON__DELETE)
            ->set_visible(true, C__NAVBAR_BUTTON__NEW)
            ->set_visible(true, C__NAVBAR_BUTTON__EDIT)
            ->set_visible(true, C__NAVBAR_BUTTON__DELETE);

        if ($l_list->createTempTable())
        {
            $g_comp_template->assign("objectTableList", $l_list->getTempTableHtml());
        } // if

        $g_comp_template->assign("content_title", "Service escalations")
            ->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1");

        $index_includes['contentbottomcontent'] = "content/bottom/content/object_table_list.tpl";
    }

    private function processCommandsListing()
    {
        global $index_includes;
        global $g_comp_template;
        global $g_comp_database;

        $l_edit_right   = isys_auth_nagios::instance()
            ->is_allowed_to(isys_auth::EDIT, 'NAGIOS_COMMANDS');
        $l_delete_right = isys_auth_nagios::instance()
            ->is_allowed_to(isys_auth::DELETE, 'NAGIOS_COMMANDS');

        $l_list         = new isys_component_list();
        $l_list_headers = [
            "id"   => "ID",
            "name" => "Name"
        ];
        $l_daoNagios    = new isys_component_dao_nagios($g_comp_database);

        $l_commands_result = $l_daoNagios->getCommands();
        $l_commands_count  = $l_commands_result->num_rows();
        $l_list->set_data(null, $l_commands_result);
        $l_list->config(
            $l_list_headers,
            isys_glob_build_url(http_build_query($_GET) . "&" . C__GET__NAGIOS_CID . "=[{id}]"),
            "[{id}]"
        );

        isys_component_template_navbar::getInstance()
            ->set_active($l_edit_right, C__NAVBAR_BUTTON__NEW)
            ->set_active(($l_edit_right && count($l_commands_count) > 0), C__NAVBAR_BUTTON__EDIT)
            ->set_active(($l_delete_right && count($l_commands_count) > 0), C__NAVBAR_BUTTON__DELETE)
            ->set_visible(true, C__NAVBAR_BUTTON__NEW)
            ->set_visible(true, C__NAVBAR_BUTTON__EDIT)
            ->set_visible(true, C__NAVBAR_BUTTON__DELETE);

        if ($l_list->createTempTable())
        {
            $g_comp_template->assign(
                "objectTableList",
                $l_list->getTempTableHtml()
            );
        }

        $g_comp_template->assign('content_title', 'Commands')
            ->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1");
        $index_includes['contentbottomcontent'] = "content/bottom/content/object_table_list.tpl";
    } // function

    private function processCommandsPage($p_id)
    {
        $l_navbar = isys_component_template_navbar::getInstance();
        global $index_includes;
        global $g_comp_database;
        global $g_comp_template;

        $l_rules = [];

        $l_edit_right   = isys_auth_nagios::instance()
            ->is_allowed_to(isys_auth::EDIT, 'NAGIOS_COMMANDS');
        $l_delete_right = isys_auth_nagios::instance()
            ->is_allowed_to(isys_auth::DELETE, 'NAGIOS_COMMANDS');

        if ($_POST[C__GET__NAVMODE] == C__NAVMODE__EDIT)
        {
            $l_navbar->set_selected(true, C__NAVBAR_BUTTON__EDIT)
                ->set_active(true, C__NAVBAR_BUTTON__SAVE)
                ->set_active(true, C__NAVBAR_BUTTON__CANCEL);
        }
        else
        {
            $l_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__EDIT)
                ->set_active($l_delete_right, C__NAVBAR_BUTTON__DELETE)
                ->set_visible(true, C__NAVBAR_BUTTON__EDIT)
                ->set_visible(true, C__NAVBAR_BUTTON__DELETE);
        }

        $l_daoNagios = new isys_component_dao_nagios($g_comp_database);

        $l_c                                                             = $l_daoNagios->getCommand($p_id);
        $l_rules["C__MODULE__NAGIOS__COMMAND_NAME"]["p_strValue"]        = $l_c["name"];
        $l_rules["C__MODULE__NAGIOS__COMMAND_LINE"]["p_strValue"]        = $l_c["line"];
        $l_rules["C__MODULE__NAGIOS__COMMAND_DESCRIPTION"]["p_strValue"] = $l_c["description"];

        $g_comp_template->assign("cID", $p_id)
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);

        $index_includes['contentbottomcontent'] = "modules/nagios/commands.tpl";
    } // function

    private function createCommand()
    {
        global $index_includes;

        isys_component_template_navbar::getInstance()
            ->set_selected(true, C__NAVBAR_BUTTON__NEW);

        $_POST[C__GET__NAVMODE] = C__NAVMODE__EDIT;

        $index_includes['contentbottomcontent'] = "modules/nagios/commands.tpl";
    } // function

} // class