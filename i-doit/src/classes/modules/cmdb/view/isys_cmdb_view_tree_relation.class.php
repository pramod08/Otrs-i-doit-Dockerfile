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
 * CMDB Tree view
 *
 * @package    i-doit
 * @subpackage CMDB_Views
 * @author     Dennis Stücken <dstuecken@i-doit.org>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_view_tree_relation extends isys_cmdb_view_tree
{
    /**
     * Returns the view mode ID.
     *
     * @return  integer
     */
    public function get_id()
    {
        return C__CMDB__VIEW__TREE_RELATION;
    } // function

    /**
     * @param  array &$l_gets
     */
    public function get_mandatory_parameters(&$l_gets)
    {
        parent::get_mandatory_parameters($l_gets);
    } // function

    /**
     * Returns the tree name.
     *
     * @return  string
     */
    public function get_name()
    {
        return "Relation tree";
    } // function

    /**
     * @param  array &$l_gets
     */
    public function get_optional_parameters(&$l_gets)
    {
        parent::get_optional_parameters($l_gets);
    } // function

    /**
     * Method for building the tree.
     */
    public function tree_build()
    {
        global $g_comp_database;

        $l_dao_relation = new isys_cmdb_dao_category_g_relation($g_comp_database);

        $this->m_tree->add_node(1, -1, _L("LC__CMDB__CATG__RELATION"));

        $l_reltypes = $l_dao_relation->get_relation_type();
        if (isys_auth_cmdb::instance()
            ->is_allowed_to(isys_auth::VIEW, 'OBJ_IN_TYPE/C__OBJTYPE__RELATION')
        )
        {
            $this->m_tree->add_node(
                2,
                1,
                _L("LC__CMDB__RECORD_STATUS__ALL"),
                "?viewMode=" . C__CMDB__VIEW__LIST_OBJECT . "&" . C__CMDB__GET__TREEMODE . "=" . C__CMDB__VIEW__TREE_RELATION . "&" . C__CMDB__GET__OBJECTTYPE . "=" . C__OBJTYPE__RELATION,
                '',
                '',
                (!isset($_GET["type"]) && isset($_GET[C__CMDB__GET__OBJECTTYPE])) ? 1 : 0
            );

            while ($l_row = $l_reltypes->get_row())
            {

                if ($l_row["isys_relation_type__master"])
                {
                    $l_add = " (" . _L($l_row["isys_relation_type__master"]) . ")";
                }
                else
                {
                    $l_add = "";
                } // if

                $this->m_tree->add_node(
                    $l_row["isys_relation_type__id"] + 5,
                    2,
                    _L($l_row["isys_relation_type__title"]) . $l_add,
                    "?viewMode=" . C__CMDB__VIEW__LIST_OBJECT . "&" . C__CMDB__GET__TREEMODE . "=" . C__CMDB__VIEW__TREE_RELATION . "&" . C__CMDB__GET__OBJECTTYPE . "=" . C__OBJTYPE__RELATION . "&type=" . $l_row["isys_relation_type__id"],
                    '',
                    '',
                    ($_GET['type'] == $l_row["isys_relation_type__id"]) ? 1 : 0
                );
            } // while

            /*
             * removed, due to performance issues
            $this->m_tree->add_node(
                3,
                1,
                _L("LC__CMDB__RELATION_IMPLICIT"),
                "?viewMode=" . C__CMDB__VIEW__LIST_OBJECT . "&" . C__CMDB__GET__TREEMODE . "=" . C__CMDB__VIEW__TREE_RELATION . "&" . C__CMDB__GET__OBJECTTYPE . "=" . C__OBJTYPE__RELATION . "&view=implicit",
                '',
                '',
                ($_GET['view'] == 'implicit') ? 1 : 0);

            $this->m_tree->add_node(
                4,
                1,
                _L("LC__CMDB__RELATION_EXPLICIT"),
                "?viewMode=" . C__CMDB__VIEW__LIST_OBJECT . "&" . C__CMDB__GET__TREEMODE . "=" . C__CMDB__VIEW__TREE_RELATION . "&" . C__CMDB__GET__OBJECTTYPE . "=" . C__OBJTYPE__RELATION . "&view=explicit",
                '',
                '',
                ($_GET['view'] == 'explicit') ? 1 : 0);
            */
        } // if

        if (isys_auth_cmdb::instance()
            ->is_allowed_to(isys_auth::VIEW, 'OBJ_IN_TYPE/C__OBJTYPE__PARALLEL_RELATION')
        )
        {
            $this->m_tree->add_node(
                5,
                1,
                _L("LC__RELATION__PARALLEL_RELATIONS"),
                "?viewMode=" . C__CMDB__VIEW__LIST_OBJECT . "&" . C__CMDB__GET__TREEMODE . "=" . C__CMDB__VIEW__TREE_RELATION . "&" . C__CMDB__GET__OBJECTTYPE . "=" . C__OBJTYPE__PARALLEL_RELATION,
                '',
                '',
                ($_GET[C__CMDB__GET__OBJECTTYPE] == C__OBJTYPE__PARALLEL_RELATION) ? 1 : 0,
                '',
                ''
            );
        } // if

        $this->m_tree->set_tree_sort(false);

        global $g_comp_template;

        $g_comp_template->assign('bShowMenuTreeButtons', false);

        isys_component_signalcollection::get_instance()
            ->emit("mod.cmdb.extendRelationTree", $this->m_tree);
    } // function

    /**
     * @return  string
     */
    public function tree_process()
    {
        return $this->m_tree->process();
    } // function

    /**
     * Constructor method.
     *
     * @param  isys_module_request $p_modreq
     */
    public function __construct(isys_module_request $p_modreq)
    {
        parent::__construct($p_modreq);
    } // function
} // class