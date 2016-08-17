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
 * CMDB Tree view for objects
 *
 * @package    i-doit
 * @subpackage CMDB_Views
 * @author     Andre Woesten <awoesten@i-doit.de>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_view_tree_object extends isys_cmdb_view_tree
{
    public function get_id()
    {
        return C__CMDB__VIEW__TREE_OBJECT;
    }

    /**
     *
     * @param  array $l_gets
     */
    public function get_mandatory_parameters(&$l_gets)
    {
        parent::get_mandatory_parameters($l_gets);
    }

    public function get_name()
    {
        return "Objektbaum";
    }

    /**
     *
     * @param  array $l_gets
     */
    public function get_optional_parameters(&$l_gets)
    {
        parent::get_optional_parameters($l_gets);

        $l_gets[C__CMDB__GET__OBJECT]      = true;
        $l_gets[C__CMDB__GET__OBJECTTYPE]  = true;
        $l_gets[C__CMDB__GET__OBJECTGROUP] = true;
    } // function

    /**
     * Builds the object tree.
     *
     * @throws   isys_exception_cmdb
     * @global   array                   $g_dirs
     * @global   isys_component_database $g_comp_database
     * @global   boolean                 $g_ajax_calls
     * @author   Dennis Stücken <dstuecken@i-doit.org>
     * @version  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function tree_build()
    {
        global $g_dirs, $g_comp_database, $g_ajax_calls, $g_config;

        // Prepare some variables.
        $l_gets      = $this->get_module_request()
            ->get_gets();
        $l_posts     = $this->get_module_request()
            ->get_posts();
        $l_tpl       = $this->get_module_request()
            ->get_template();
        $l_nodeid    = 0;
        $l_icon      = null;
        $l_jumpgets  = [];
        $l_bSelected = false;

        /**
         * @var $l_dao isys_cmdb_dao_object_type
         */
        $l_dao = isys_cmdb_dao_object_type::instance($g_comp_database);

        if ($l_gets[C__CMDB__GET__OBJECT] > 0)
        {
            $this->remove_ajax_parameters($l_gets);

            // Create root node.
            $l_gets[C__CMDB__GET__CATS]     = null;
            $l_gets[C__CMDB__GET__SUBCAT]   = null;
            $l_gets[C__CMDB__GET__CATG]     = C__CATG__OVERVIEW;
            $l_gets[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__CATEGORY;

            if ($g_ajax_calls)
            {
                $l_title_link = isys_glob_build_ajax_url(C__FUNC__AJAX__CONTENT_BY_OBJECT, $l_gets);
            }
            else
            {
                $l_title_link = isys_glob_build_url(isys_glob_http_build_query($l_gets));
            } // if

            if (!empty($l_posts["C__CATG__GLOBAL_TITLE"]))
            {
                $l_title = $l_posts["C__CATG__GLOBAL_TITLE"];
            }
            else
            {
                $l_title = $l_dao->obj_get_title_by_id_as_string($l_gets[C__CMDB__GET__OBJECT]);
            } // if

            $l_obj_type = ($l_gets[C__CMDB__GET__OBJECTTYPE]) ? $l_gets[C__CMDB__GET__OBJECTTYPE] : $l_dao->get_objTypeID($l_gets[C__CMDB__GET__OBJECT]);

            if (!is_null($l_obj_type))
            {
                $l_obj_row = $l_dao->get_type_by_id($l_obj_type);

                if (!is_null($l_obj_row))
                {
                    if (!empty($l_obj_row["isys_obj_type__icon"]))
                    {
                        if (strstr($l_obj_row["isys_obj_type__icon"], '/'))
                        {
                            $l_icon = $g_config['www_dir'] . $l_obj_row["isys_obj_type__icon"];
                        }
                        else
                        {
                            $l_icon = $g_dirs["images"] . "tree/" . $l_obj_row["isys_obj_type__icon"];
                        } // if
                    } // if
                } // if

                // Add root entry.
                $l_tree_root = $this->m_tree->add_node(
                    0,
                    C__CMDB__TREE_NODE__PARENT,
                    str_replace('\\', '&#92;', $l_title), // Fix for ID-602 (Backslashes in Object Title)
                    $l_title_link,
                    "",
                    $l_icon
                );

                /*********************************************************************
                 * SPECIFIC CATEGORIES
                 *********************************************************************/
                $l_specific_category = $l_dao->get_specific_category($l_obj_type);

                // Objects can only have one specific category assigned
                if ($l_specific_category->num_rows() == 1)
                {
                    $l_category = $l_specific_category->get_row();

                    /* Skip category when class does not exist */
                    if (class_exists($l_category["isysgui_cats__class_name"]))
                    {
                        $l_category_id      = $l_category["isysgui_cats__id"];
                        $l_category_title   = isys_glob_escape_string(isys_helper::sanitize_text(_L($l_category["isysgui_cats__title"])));
                        $l_category_tooltip = $l_category_title;
                        $l_category_const   = $l_category["isysgui_cats__const"];
                        $l_skip_category    = false;

                        // Needs to be checked differently because of the wildcard check
                        if ($l_category_id == C__CATS__BASIC_AUTH)
                        {
                            if (!isys_auth_auth::instance()
                                ->is_allowed_to(isys_auth::SUPERVISOR, 'MODULE/C__MODULE__AUTH')
                            )
                            {
                                $l_skip_category = true;
                            } // if
                        } // if

                        if (!isys_auth_cmdb::instance()
                            ->has_rights_in_obj_and_category(isys_auth::VIEW, $l_gets[C__CMDB__GET__OBJECT], $l_category_const)
                        )
                        {
                            $l_skip_category = true;
                        } // if

                        if (!$l_skip_category)
                        {
                            $l_jumpgets = $l_gets;

                            // Reset the Category selection parameters.
                            $this->reduce_catspec_parameters($l_jumpgets);

                            $l_jumpgets[C__CMDB__GET__CATS] = $l_category_id;

                            $l_jumpgets[C__CMDB__GET__VIEWMODE] = ($l_category["isysgui_cats__list_multi_value"]) ? C__CMDB__VIEW__LIST_CATEGORY : C__CMDB__VIEW__CATEGORY;

                            // Determine if the node is selected.
                            if ($l_category_id == $l_gets[C__CMDB__GET__CATS])
                            {
                                $this->m_select_node = C__CMDB__TREE_OBJECT__INC_SPECIFIC + $l_category_id;
                            } // if

                            if ($g_ajax_calls)
                            {
                                $l_link = "javascript:get_content_by_object(" . "'" . $l_gets[C__CMDB__GET__OBJECT] . "', " . "'" . $l_jumpgets[C__CMDB__GET__VIEWMODE] . "', " . "'" . $l_category_id . "'," . "'" . C__CMDB__GET__CATS . "');";
                            }
                            else
                            {
                                $l_link = isys_glob_build_url(isys_glob_http_build_query($l_jumpgets));
                            } // if

                            try
                            {
                                // Check if category has entries.
                                if (empty($_GET[C__CMDB__GET__OBJECT]))
                                {
                                    $l_category_title = "<span class=\"noentries\">" . $l_category_tooltip . "</span>";
                                }
                                elseif (!$l_dao->check_category(
                                    $_GET[C__CMDB__GET__OBJECT],
                                    $l_category["isysgui_cats__class_name"],
                                    $l_category["isysgui_cats__id"],
                                    $l_category["isysgui_cats__source_table"]
                                )
                                )
                                {
                                    $l_category_title = "<span class=\"noentries\">" . $l_category_tooltip . "</span>";
                                }
                                else
                                {
                                    $l_category_title = $l_category_tooltip;
                                } // if
                            }
                            catch (Exception $l_exception)
                            {
                                isys_notify::error($l_exception->getMessage(), ['sticky' => true]);
                                $l_category_title = '<del class="red">' . $l_category_tooltip . '</del>';
                            } // try

                            // Adds the tree node.
                            $l_tree_spec = $this->m_tree->add_node(
                                C__CMDB__TREE_OBJECT__INC_SPECIFIC + $l_category_id,
                                $l_tree_root,
                                $l_category_title,
                                $l_link,
                                '',
                                '',
                                0,
                                '',
                                $l_category_tooltip,
                                true,
                                $l_category_const
                            );

                            // Don't create sub entry for category net in objecttype supernet. Supernets should not have any ip addresses or dhcp ranges.
                            if (!($l_obj_type == C__OBJTYPE__SUPERNET && $l_category_id == C__CATS__NET))
                            {
                                try
                                {
                                    // Create the subcategory subtree.
                                    $this->tree_create_subcategory($l_category, C__CMDB__TREE_OBJECT__INC_SPECIFIC_EXT, $l_tree_spec, C__CMDB__GET__CATS, "isysgui_cats");
                                }
                                catch (Exception $l_exception)
                                {
                                    isys_notify::error($l_exception->getMessage(), ['sticky' => true]);
                                } // try
                            } // if
                        } // if
                    } // if
                } // if

                /*********************************************************************
                 * GLOBAL CATEGORIES
                 *********************************************************************/

                /* Then we need the global categories */
                unset($l_category);
                $l_global_categories = $l_dao->get_global_categories($l_obj_type);

                if ($l_global_categories->num_rows() > 0)
                {
                    while ($l_category = $l_global_categories->get_row())
                    {
                        // Skip category when class does not exist.
                        if (!class_exists($l_category["isysgui_catg__class_name"]))
                        {
                            continue;
                        } // if

                        if (!isys_auth_cmdb::instance()
                            ->has_rights_in_obj_and_category(isys_auth::VIEW, $l_gets[C__CMDB__GET__OBJECT], $l_category["isysgui_catg__const"])
                        )
                        {
                            continue;
                        } // if

                        if (in_array(
                            $l_category["isysgui_catg__id"],
                            [
                                C__CATG__LOGBOOK,
                                C__CATG__PLANNING,
                                C__CATG__RELATION,
                                C__CATG__VIRTUAL_TICKETS,
                                C__CATG__VIRTUAL_AUTH
                            ]
                        ))
                        {
                            continue;
                        } // if

                        // Skip VIVA category if module is available:
                        if (defined('C__CATG__VIRTUAL_VIVA') && $l_category["isysgui_catg__id"] == C__CATG__VIRTUAL_VIVA)
                        {
                            continue;
                        } // if

                        // Don't show a node for the overview page.
                        if ($l_category["isysgui_catg__property"] & C__RECORD_PROPERTY__NOT_SHOW_IN_LIST)
                        {
                            continue;
                        } // if

                        $l_category_id      = $l_category["isysgui_catg__id"];
                        $l_category_const   = $l_category["isysgui_catg__const"];
                        $l_category_tooltip = isys_glob_escape_string(isys_helper::sanitize_text(_L($l_category["isysgui_catg__title"])));

                        $l_jumpgets = $l_gets;
                        $this->reduce_catspec_parameters($l_jumpgets);

                        $l_jumpgets[C__CMDB__GET__CATG] = $l_category_id;

                        // Determine if the node has to be selected

                        $l_bSelected = 0;
                        if (isset($_GET[C__CMDB__GET__CATG]))
                        {
                            if ($l_category['isysgui_catg__id'] == $_GET[C__CMDB__GET__CATG])
                            {
                                $l_bSelected         = 1;
                                $this->m_select_node = C__CMDB__TREE_OBJECT__INC_GLOBAL + $l_category["isysgui_catg__id"];
                            }
                        }

                        $l_jumpgets[C__CMDB__GET__VIEWMODE] = ($l_category["isysgui_catg__list_multi_value"]) ? C__CMDB__VIEW__LIST_CATEGORY : C__CMDB__VIEW__CATEGORY;

                        $l_nodeid = C__CMDB__TREE_OBJECT__INC_GLOBAL + $l_category_id;

                        if ($g_ajax_calls)
                        {
                            $l_link = "javascript:get_content_by_object('" . $l_gets[C__CMDB__GET__OBJECT] . "', '" . $l_jumpgets[C__CMDB__GET__VIEWMODE] . "', '" . $l_category_id . "','" . C__CMDB__GET__CATG . "');";
                        }
                        else
                        {
                            $l_link = isys_glob_build_url(isys_glob_http_build_query($l_jumpgets));
                        } // if

                        // Check if category has entries.
                        try
                        {
                            if (empty($_GET[C__CMDB__GET__OBJECT]))
                            {
                                $l_category_title = "<span class=\"noentries\">" . $l_category_tooltip . "</span>";
                            }
                            elseif (!$l_dao->check_category(
                                $_GET[C__CMDB__GET__OBJECT],
                                $l_category["isysgui_catg__class_name"],
                                $l_category["isysgui_catg__id"],
                                $l_category["isysgui_catg__source_table"]
                            )
                            )
                            {
                                $l_category_title = "<span class=\"noentries\">" . $l_category_tooltip . "</span>";
                            }
                            else
                            {
                                $l_category_title = $l_category_tooltip;
                            } // if
                        }
                        catch (Exception $l_exception)
                        {
                            isys_notify::error($l_exception->getMessage(), ['sticky' => true]);
                            $l_category_title = '<del class="red">' . $l_category_tooltip . '</del>';
                        } // try

                        $l_tree_glob = $this->m_tree->add_node(
                            $l_nodeid,
                            $l_tree_root,
                            $l_category_title,
                            $l_link,
                            '',
                            '',
                            $l_bSelected,
                            '',
                            $l_category_tooltip,
                            true,
                            $l_category_const
                        );

                        try
                        {
                            $this->tree_create_subcategory($l_category, C__CMDB__TREE_OBJECT__INC_GLOBAL_EXT, $l_tree_glob);
                        }
                        catch (Exception $l_exception)
                        {
                            isys_notify::error($l_exception->getMessage(), ['sticky' => true]);
                        } // try
                    } // while
                } // if

                if (defined("C__MODULE__CUSTOM_FIELDS") && class_exists("isys_custom_fields_dao"))
                {
                    $l_nodeid += 20000;

                    $l_dao        = new isys_custom_fields_dao($g_comp_database);
                    $l_categories = $l_dao->get_assignments(null, $l_obj_type);

                    while ($l_row = $l_categories->get_row())
                    {
                        if (defined($l_row['isysgui_catg_custom__const']))
                        {
                            if (!isys_auth_cmdb::instance()
                                ->has_rights_in_obj_and_category(isys_auth::VIEW, $l_gets[C__CMDB__GET__OBJECT], $l_row['isysgui_catg_custom__const'])
                            )
                            {
                                continue;
                            } // if

                            $l_nodeid++;

                            if ($g_ajax_calls)
                            {

                                $l_link = "javascript:get_content_by_object(" . "'" . $l_gets[C__CMDB__GET__OBJECT] . "', " . "'" . $l_jumpgets[C__CMDB__GET__VIEWMODE] . "', " . "'" . C__CATG__CUSTOM_FIELDS . "'," . "'" . C__CMDB__GET__CATG . "'," . "'" . $l_row["isysgui_catg_custom__id"] . "');";

                            }
                            else
                            {
                                $l_jumpgets[C__CMDB__GET__CATG] = C__CATG__CUSTOM_FIELDS;

                                $l_link = isys_glob_build_url(isys_glob_http_build_query($l_jumpgets));
                            }

                            $l_count = isys_cmdb_dao_category_g_custom_fields::instance($g_comp_database)
                                ->set_catg_custom_id($l_row["isysgui_catg_custom__id"])
                                ->get_count($_GET[C__CMDB__GET__OBJECT]);

                            $l_category_title = isys_glob_escape_string(isys_helper::sanitize_text(_L($l_row["isysgui_catg_custom__title"])));

                            if (empty($_GET[C__CMDB__GET__OBJECT]))
                            {
                                $l_category_title = "<span class=\"noentries\">" . $l_category_title . "</span>";
                            }
                            else if (!$l_count)
                            {
                                $l_category_title = "<span class=\"noentries\">" . $l_category_title . "</span>";
                            } // if

                            // Adding the language manager, for custom translations: ID-1649.
                            $this->m_tree->add_node(
                                $l_nodeid,
                                $l_tree_root,
                                $l_category_title,
                                $l_link,
                                '',
                                '',
                                $l_bSelected,
                                '',
                                '',
                                true,
                                $l_row['isysgui_catg_custom__const']
                            );
                        } // if
                    } // while
                } // if

                $l_menu_sticky_links = [];

                // Prepare the sticky "CMDB-Explorer" link.
                if (defined('C__CMDB__VIEW__EXPLORER') && isys_auth_cmdb::instance()
                        ->is_allowed_to(isys_auth::VIEW, 'EXPLORER')
                )
                {
                    $l_menu_sticky_links['explorer'] = [
                        'title' => 'CMDB-Explorer',
                        'icon'  => $g_dirs['images'] . 'icons/silk/chart_organisation.png',
                        'link'  => isys_helper_link::create_url(
                            [
                                C__CMDB__GET__VIEWMODE      => C__CMDB__VIEW__EXPLORER,
                                C__CMDB__GET__OBJECT        => $l_gets[C__CMDB__GET__OBJECT],
                                C__CMDB__VISUALIZATION_TYPE => C__CMDB__VISUALIZATION_TYPE__TREE,
                                C__CMDB__VISUALIZATION_VIEW => C__CMDB__VISUALIZATION_VIEW__OBJECT,
                            ]
                        )
                    ];
                } // if

                // Preparing the sticky "Relation" link.
                if (isys_auth_cmdb::instance()
                    ->has_rights_in_obj_and_category(isys_auth::VIEW, $l_gets[C__CMDB__GET__OBJECT], 'C__CATG__RELATION')
                )
                {
                    $l_menu_sticky_links['relation'] = [
                        'title' => _L('LC__CMDB__CATG__RELATION'),
                        'icon'  => $g_dirs['images'] . 'icons/silk/arrow_out.png',
                        'link'  => "javascript:get_content_by_object('" . $l_gets[C__CMDB__GET__OBJECT] . "', '" . C__CMDB__VIEW__LIST_CATEGORY . "', '" . C__CATG__RELATION . "', '" . C__CMDB__GET__CATG . "');"
                    ];
                } // if

                // Prepare the sticky "Planning" link.
                if (defined('C__CATG__PLANNING') && defined('C__MODULE__PRO') && isys_auth_cmdb::instance()
                        ->has_rights_in_obj_and_category(isys_auth::VIEW, $l_gets[C__CMDB__GET__OBJECT], 'C__CATG__PLANNING')
                )
                {
                    $l_menu_sticky_links['planning'] = [
                        'title' => _L('LC__CMDB__CATG__PLANNING'),
                        'icon'  => $g_dirs['images'] . 'icons/silk/calendar.png',
                        'link'  => "javascript:get_content_by_object('" . $l_gets[C__CMDB__GET__OBJECT] . "', '" . C__CMDB__VIEW__LIST_CATEGORY . "', '" . C__CATG__PLANNING . "', '" . C__CMDB__GET__CATG . "');"
                    ];
                } // if

                // Preparing the sticky "Logbook" link.
                if (isys_auth_cmdb::instance()
                    ->has_rights_in_obj_and_category(isys_auth::VIEW, $l_gets[C__CMDB__GET__OBJECT], 'C__CATG__LOGBOOK')
                )
                {
                    $l_menu_sticky_links['logbook'] = [
                        'title' => _L('LC__CMDB__CATG__LOGBOOK'),
                        'icon'  => $g_dirs['images'] . 'icons/silk/book_open.png',
                        'link'  => "javascript:get_content_by_object('" . $l_gets[C__CMDB__GET__OBJECT] . "', '" . C__CMDB__VIEW__LIST_CATEGORY . "', '" . C__CATG__LOGBOOK . "', '" . C__CMDB__GET__CATG . "');"
                    ];
                } // if

                $l_tpl->assign('menuTreeLinksBack', $this->get_back_url(C__CMDB__VIEW__LIST_OBJECT, C__CMDB__VIEW__TREE_OBJECTTYPE))
                    ->assign('menuTreeStickyLinks', $l_menu_sticky_links);

                // Emit a new signal with the parameters: <isys_obj__id>, <isys_obj_type__id>
                try
                {
                    isys_component_signalcollection::get_instance()
                        ->emit("mod.cmdb.processMenuTreeLinks", $l_tpl, 'menuTreeStickyLinks', $l_gets[C__CMDB__GET__OBJECT], $l_obj_type);
                }
                catch (Exception $e)
                {
                    isys_notify::debug($e->getMessage(), ['sticky' => true]);
                } // try

                $l_dao_user = isys_component_dao_user::instance($g_comp_database);

                $l_settings = $l_dao_user->get_user_settings();

                if (!($l_settings['isys_user_ui__tree_visible'] & 2))
                {
                    $l_tpl->assign('treeHide', 1);
                }
                else
                {
                    $l_tpl->assign('treeHide', 0);
                } // if
            }
            else
            {
                // No object type given?
            } // if
        }
        else
        {
            throw new isys_exception_cmdb('Request problem: No object id found.');
        } // if

        // Sets the eye for hiding empty nodes
        $this->m_tree->set_tree_visibility(true);

        try
        {
            isys_component_signalcollection::get_instance()
                ->emit("mod.cmdb.extendObjectTree", $this->m_tree);
        }
        catch (Exception $e)
        {
            isys_notify::debug($e->getMessage(), ['sticky' => true]);
        } // try
    } // function

    /**
     *
     * @return  string
     */
    public function tree_process()
    {
        return $this->m_tree->process($this->m_select_node);
    } // function

    /**
     * Removes all category-specific GET-parameters
     *
     * @param  array &$p_arGet
     * @param  array $p_arExceptions
     */
    protected function reduce_catspec_parameters(&$p_arGet, $p_arExceptions = null)
    {
        $l_toDelete = [
            C__CMDB__GET__CATG,
            C__CMDB__GET__CATS,
            C__CMDB__GET__CATLEVEL,
            C__CMDB__GET__CATLEVEL_1,
            C__CMDB__GET__CATLEVEL_2,
            C__CMDB__GET__CATLEVEL_3,
            C__CMDB__GET__CATLEVEL_4,
            C__CMDB__GET__CATLEVEL_5,
            C__CMDB__GET__CAT_LIST_VIEW,
            C__CMDB__GET__CAT_MENU_SELECTION
        ];

        if ($p_arExceptions)
        {
            $l_toDelete = array_diff($l_toDelete, $p_arExceptions);
        }

        foreach ($l_toDelete as $l_delP)
        {
            unset($p_arGet[$l_delP]);
        }
    } // function

    /**
     * Public constructor.
     *
     * @param  isys_module_request $p_modreq
     */
    public function __construct(isys_module_request $p_modreq)
    {
        parent::__construct($p_modreq);
        $this->m_select_node = C__CMDB__TREE_NODE__PARENT;
    } // function
} // class