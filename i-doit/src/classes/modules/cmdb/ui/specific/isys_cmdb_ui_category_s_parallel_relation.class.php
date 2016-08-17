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
 * CMDB Specific category EPS
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stuecken <dsteucken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_s_parallel_relation extends isys_cmdb_ui_category_specific
{
    /**
     * Method for retrieving the assigned relations.
     *
     * @param   isys_cmdb_dao_category_s_parallel_relation $p_cat
     *
     * @return  string
     */
    public static function get_relation_pool(isys_cmdb_dao_category_s_parallel_relation $p_cat)
    {
        global $g_dirs;

        $l_str        = "";
        $l_removelink = "<a style=\"display:none;margin-top:-7px;\" class=\"fr\" href=\"javascript:;\" onclick=\"remove_from_pool(this, '%s');\">" . "<img src=\"" . $g_dirs["images"] . "icons/detach.gif\" class=\"vam\" alt=\"\" /> " . _L(
                "LC__UNIVERSAL__REMOVE"
            ) . "</a>";

        $l_pool = $p_cat->get_relation_pool($_GET[C__CMDB__GET__OBJECT]);

        while ($l_row = $l_pool->get_row())
        {
            $l_str .= "<li> " . sprintf($l_removelink, $l_row["isys_obj__id"]) . "<span>" . $l_row["isys_obj__title"] . "</span></li>";
        } // while

        return $l_str;
    } // function

    /**
     * @global  array                                      $index_includes
     *
     * @param   isys_cmdb_dao_category_s_parallel_relation $p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $index_includes;

        if (is_object(($l_result = $p_cat->get_result())))
        {
            $l_catdata = $l_result->__to_array();
        } // if

        // Assign rules.
        $l_rules["C__CMDB__CATS__RELPL__TITLE"]["p_strValue"]                                                         = $l_catdata["isys_cats_relpool_list__title"];
        $l_rules["C__CMDB__CATS__RELPL__THRESHOLD"]["p_strValue"]                                                     = $l_catdata["isys_cats_relpool_list__threshold"];
        $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $l_catdata["isys_cats_relpool_list__description"];

        // Prepare the new relation-browser.
        $l_params = [
            isys_popup_browser_object_relation::C__MULTISELECTION   => true,
            isys_popup_browser_object_relation::C__RELATION_ONLY    => true,
            isys_popup_browser_object_relation::C__RETURN_ELEMENT   => 'objectID',
            isys_popup_browser_object_relation::C__SECOND_LIST      => "isys_cmdb_dao_category_g_relation::object_browser_get_data_by_object_and_relation_type",
            isys_popup_browser_object_relation::C__CALLBACK__ACCEPT => "rpool_callback('" . $this->prepare_link($_GET) . "');"
        ];

        $l_instance         = new isys_popup_browser_object_relation();
        $l_relation_browser = $l_instance->get_js_handler($l_params);

        $this->get_template_component()
            ->assign("link_pool", self::get_relation_pool($p_cat))
            ->assign("ajax_link", $this->prepare_link($_GET))
            ->assign("relation_browser", $l_relation_browser)
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);

        $index_includes["contentbottomcontent"] = $this->get_template();
    } // function

    /**
     * Method for preparing the ajax link.
     *
     * @param   array $p_get
     *
     * @return  string
     */
    private function prepare_link($p_get)
    {
        $l_link[C__CMDB__GET__OBJECT]     = $p_get[C__CMDB__GET__OBJECT];
        $l_link[C__CMDB__GET__TREEMODE]   = C__CMDB__VIEW__TREE_RELATION;
        $l_link[C__CMDB__GET__VIEWMODE]   = C__CMDB__VIEW__CATEGORY_SPECIFIC;
        $l_link[C__CMDB__GET__CATS]       = C__CATS__PARALLEL_RELATION;
        $l_link[C__CMDB__GET__OBJECTTYPE] = $p_get[C__CMDB__GET__OBJECTTYPE];

        return "?" . http_build_query($l_link, null, "&");
    } // function

    /**
     * Constructor.
     *
     * @param  isys_component_template $p_template
     */
    public function __construct(isys_component_template &$p_template)
    {
        parent::__construct($p_template);
        $this->set_template("cats__parallel_relation.tpl");
    } // function
} // class
?>