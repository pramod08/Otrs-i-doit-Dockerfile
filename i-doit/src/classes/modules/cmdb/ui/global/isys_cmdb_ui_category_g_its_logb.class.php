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
 * CMDB Logbook
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Blümer <dbluemer@synetics.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_its_logb extends isys_cmdb_ui_category_global
{
    /**
     * Show the detail-template for the logbook.
     *
     * @global  array                             $index_includes
     * @global  isys_locale                       $g_comp_database
     *
     * @param   isys_cmdb_dao_category_g_its_logb $p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $index_includes, $g_loc;

        $l_mod_event_manager = isys_event_manager::getInstance();
        $l_ui_logbook        = new isys_cmdb_ui_category_g_logb($this->m_template);

        $l_rules = [];

        if ($_POST[C__GET__NAVMODE] == C__NAVMODE__NEW)
        {
            $l_alert_level = [
                C__LOGBOOK__ALERT_LEVEL__0 => _L("LC__CMDB__LOGBOOK__ALERT_LEVEL_0"),
                C__LOGBOOK__ALERT_LEVEL__1 => _L("LC__CMDB__LOGBOOK__ALERT_LEVEL_1"),
                C__LOGBOOK__ALERT_LEVEL__2 => _L("LC__CMDB__LOGBOOK__ALERT_LEVEL_2"),
                C__LOGBOOK__ALERT_LEVEL__3 => _L("LC__CMDB__LOGBOOK__ALERT_LEVEL_3")
            ];

            $l_rules["C__CATG__LOGBOOK__ALERTLEVEL"]["p_arData"] = serialize($l_alert_level);

            $this->get_template_component()
                ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
            $index_includes["contentbottomcontent"] = "content/bottom/content/module__logbook__list.tpl";

            return;
        } // if

        $l_catdata = $p_cat->get_general_data();

        if (is_null($l_catdata) || !$l_catdata)
        {
            $l_catdata = $p_cat->get_result()
                ->__to_array();
        } // if

        $l_listdao = new isys_component_dao_logbook($this->get_database_component());
        $l_daores  = $l_listdao->get_result_by_logbook_id($l_catdata["isys_catg_logb_list__isys_logbook__id"]);
        $l_catdata = $l_daores->get_row();

        $l_lbTitle = $l_mod_event_manager->translateEvent(
            $l_catdata["isys_logbook__event_static"],
            $l_catdata["isys_logbook__obj_name_static"],
            $l_catdata["isys_logbook__category_static"],
            $l_catdata["isys_logbook__obj_type_static"],
            $l_catdata["isys_logbook__entry_identifier_static"],
            $l_catdata["isys_logbook__changecount"]
        );

        // Make rules.
        $l_rules["C__CMDB__LOGBOOK__TITLE"]["p_strValue"] = $l_lbTitle;

        // Unescape the logbook sql statement.
        $l_desc = isys_glob_unescape($l_catdata["isys_logbook__description"]);

        $l_desc = $l_listdao->match_description($l_desc);

        $l_rules["C__CMDB__LOGBOOK__DESCRIPTION"]["p_strValue"] = $l_desc;
        $l_rules["C__CMDB__LOGBOOK__COMMENT"]["p_strValue"]     = $l_catdata["isys_logbook__comment"];
        $l_rules["C__CMDB__LOGBOOK__DATE"]["p_strValue"]        = $g_loc->fmt_datetime($l_catdata["isys_logbook__date"]);
        $l_rules["C__CMDB__LOGBOOK__LEVEL"]["p_strValue"]       = _L($l_catdata["isys_logbook_level__title"]);

        //is there a name?
        $l_dao_user = new isys_cmdb_dao_category_s_person_master($this->get_database_component());
        $l_userdata = $l_dao_user->get_person_by_id($l_catdata["isys_logbook__isys_obj__id"]);

        if ($l_userdata->num_rows() > 0)
        {
            $l_userdata = $l_userdata->get_row();

            $l_strUsertitle = "<a href=\"" . isys_helper_link::create_url(
                    [C__CMDB__GET__OBJECT => $l_userdata["isys_cats_person_list__isys_obj__id"]]
                ) . "\">" . $l_userdata["isys_cats_person_list__title"] . "</a>" . " (" . $l_userdata["isys_cats_person_list__first_name"] . $l_userdata["isys_cats_person_list__last_name"];

            if ($l_userdata["isys_cats_person_list__mail_address"])
            {
                $l_strUsertitle .= '; <a href="' . isys_helper_link::create_mailto(
                        $l_userdata["isys_cats_person_list__mail_address"]
                    ) . '" target="_blank">' . $l_userdata["isys_cats_person_list__mail_address"] . '</a>';
            } // if

            $l_strUsertitle .= ")";
        }
        else
        {
            $l_strUsertitle = $l_catdata["isys_logbook__user_name_static"];
        } // if

        $l_rules["C__CMDB__LOGBOOK__USER"]["p_strValue"] = $l_strUsertitle;

        // Assign and retrieve changes.
        $l_changes_ar = $l_ui_logbook->get_changes_as_array($l_catdata["isys_logbook__changes"]);

        $l_rules["C__CMDB__LOGBOOK__CHANGED_FIELDS"]["p_strValue"] = count($l_changes_ar);

        if (($l_changes = $l_ui_logbook->get_changes_as_html_table($l_changes_ar)))
        {
            $this->get_template_component()
                ->assign("changes", $l_changes);
        }

        // Apply rules
        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);

        // Switch navbar buttons.
        isys_component_template_navbar::getInstance()
            ->set_active(false, C__NAVBAR_BUTTON__EDIT);

        $index_includes["contentbottomcontent"] = "content/bottom/content/catg__logbook.tpl";
    } // function

    /**
     * Genrate html list for accumulated logbook entries from all components belonging to an IT Service.
     *
     * @param   isys_cmdb_dao_category_g_its_logb $p_cat
     *
     * @return  null
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function process_list(isys_cmdb_dao_category_g_its_logb $p_cat)
    {
        global $index_includes;

        $l_listdao = isys_factory::get_instance('isys_component_dao_logbook', $this->get_database_component());

        $l_filter              = $_POST;
        $l_filter['object_id'] = [];
        $l_arTotal             = [];

        $l_dao = isys_cmdb_dao_category_g_its_logb::instance($this->get_database_component());
        $l_res = $l_dao->get_data(null, $_GET[C__CMDB__GET__OBJECT]);

        if (count($l_res))
        {
            while ($l_resRow = $l_res->get_row())
            {
                $l_resRow["isys_logbook__obj_type_static"] = _L($l_resRow["isys_logbook__obj_type_static"]);
                $l_arTotal[]                               = $l_resRow;

                $l_filter['object_id'][] = $l_resRow['isys_catg_logb_list__isys_obj__id'];
            } // while

            $l_arTableHeader = [
                "+"                              => "",
                "isys_logbook__title"            => _L("LC__CMDB__LOGBOOK__TITLE"),
                "isys_logbook__obj_name_static"  => _L("LC__CATG__ODEP_OBJ"),
                "isys_logbook__obj_type_static"  => _L("LC__CMDB__OBJTYPE"),
                "isys_logbook__user_name_static" => "User",
                "isys_logbook__date"             => _L("LC__CMDB__LOGBOOK__DATE"),
                "isys_logbook_level__title"      => _L("LC__CMDB__LOGBOOK__LEVEL")
            ];

            $l_objList = new isys_component_list_logbook($l_arTotal, null, $l_listdao);

            $l_strRowLink = "document.location.href='?moduleID=" . C__MODULE__LOGBOOK . "&id=[{isys_logbook__id}]';";

            $l_objList->config($l_arTableHeader, $l_strRowLink);

            //$l_objList->createTempTable();

            // If the grouping filter is set get the table that groups the result.
            $l_group = isys_glob_get_param("filter_group");

            if ($l_group != false && $l_group != "-1")
            {
                $l_strTempHtml = $l_objList->getGroupedTableHtml($l_group);
            }
            else
            {
                $l_strTempHtml = $l_objList->getTempTableHtml($l_filter);
            } // if
        }
        else
        {
            global $g_dirs;

            $l_strTempHtml = '<p class="m5 blue"><img src="' . $g_dirs['images'] . 'icons/silk/information.png" class="vam mr5" /><span class="vam">' . _L('LC__CMDB__FILTER__NOTHING_FOUND_STD') . '</span></p>';
        } // if

        $this->get_template_component()
            ->activate_editmode()
            ->assign("LogbookList", $l_strTempHtml)
            ->assign("groups", "1")
            ->assign("bNavbarFilter", "1")
            ->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1")
            ->smarty_tom_add_rule("tom.content.top.filter.p_strValue=" . isys_glob_get_param("filter"));

        $this->setupFilter($l_listdao);
        $index_includes['contentbottomcontent'] = "content/bottom/content/module__logbook__list.tpl";
        $index_includes["navbar"]               = "content/navbar/logbook.tpl";

        isys_component_template_navbar::getInstance()
            ->set_active(false, C__NAVBAR_BUTTON__NEW);

        return null;
    } // function

    /**
     * Set up the filter for the logbook
     *
     * @param  $p_daoLogbook
     */
    private function setupFilter($p_daoLogbook)
    {
        global $g_comp_template;

        $l_rules         = [];
        $l_alertFilter   = $p_daoLogbook->getAlertlevels();
        $l_sourceFilter  = $p_daoLogbook->getSources();
        $l_filter_groups = [
            "isys_logbook__obj_name_static"  => _L("LC__CATG__ODEP_OBJ"),
            "isys_logbook__obj_type_static"  => _L("LC__CMDB__OBJTYPE"),
            "isys_logbook__user_name_static" => "User"
        ];

        $l_typeFilter = [
            "0" => _L("LC__CMDB__CATG__SYSTEM"),
            "1" => _L("LC__NAVIGATION__MENUTREE__BUTTON_OBJECT_VIEW")
        ];

        $l_rules["filter_source"]["p_arData"] = serialize($l_sourceFilter);

        if (isset($_POST["filter_source"]))
        {
            $l_rules["filter_source"]["p_strSelectedID"] = $_POST["filter_source"];
        }
        else
        {
            $l_rules["filter_source"]["p_strSelectedID"] = "-1";
        } // if

        $l_rules["filter_alert"]["p_arData"] = serialize($l_alertFilter);

        if (isset($_POST["filter_alert"]))
        {
            $l_rules["filter_alert"]["p_strSelectedID"] = $_POST["filter_alert"];
        }
        else
        {
            $l_rules["filter_alert"]["p_strSelectedID"] = "-1";
        } // if

        $l_rules["filter_type"]["p_arData"] = serialize($l_typeFilter);

        if (isset($_POST["filter_type"]))
        {
            $l_rules["filter_type"]["p_strSelectedID"] = $_POST["filter_type"];
        }
        else
        {
            $l_rules["filter_type"]["p_strSelectedID"] = "-1";
        } // if

        if (isset($_POST["filter_from__HIDDEN"]))
        {
            $l_rules["filter_from"]["p_strValue"] = $_POST["filter_from__HIDDEN"];
        } // if

        if (isset($_POST["filter_to__HIDDEN"]))
        {
            $l_rules["filter_to"]["p_strValue"] = $_POST["filter_to__HIDDEN"];
        } // if

        $l_rules["filter_group"]["p_arData"] = serialize($l_filter_groups);

        if (isset($_POST["filter_group"]))
        {
            $l_rules["filter_group"]["p_strSelectedID"] = $_POST["filter_group"];
        }
        else
        {
            $l_rules["filter_group"]["p_strSelectedID"] = "-1";
        } // if

        if (isset($_POST["filter_user__HIDDEN"]))
        {
            $l_rules["filter_user"]["p_strSelectedID"] = $_POST["filter_user__HIDDEN"];
        } // if

        $g_comp_template->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    } // function

    /**
     * Constructor.
     *
     * @param  isys_component_template $p_template
     */
    public function __construct(isys_component_template &$p_template)
    {
        parent::__construct($p_template);
        $this->set_template("module__logbook__list.tpl");
    } // function
} // class