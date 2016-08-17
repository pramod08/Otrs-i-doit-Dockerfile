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
 * Objecttype DAO
 *
 * @package    i-doit
 * @subpackage CMDB_Low-Level_API
 * @author     Dennis Stuecken <dstuecken@synetics.de>
 * @version    1.4
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_cmdb_dao_objecttype extends isys_cmdb_dao
{

    public function save_list_config($p_object_type_id, $p_list_config, $p_list_query, $p_list_row_click = true, $p_default_sorting = null, $p_sorting_direction = 'asc')
    {
        global $g_comp_session;

        $l_sql = 'SELECT * FROM isys_obj_type_list
			WHERE isys_obj_type_list__isys_obj__id = ' . $this->convert_sql_id($g_comp_session->get_user_id()) . '
			AND isys_obj_type_list__isys_obj_type__id = ' . $this->convert_sql_id($p_object_type_id) . ';';
        $l_res = $this->retrieve($l_sql);

        if ($l_res->num_rows() > 0)
        {
            $l_row = $l_res->get_row();
            $l_sql = 'UPDATE isys_obj_type_list SET ' . 'isys_obj_type_list__config = ' . $this->convert_sql_text(
                    $p_list_config
                ) . ', ' . 'isys_obj_type_list__query = ' . $this->convert_sql_text($p_list_query) . ', ' . 'isys_obj_type_list__row_clickable = ' . $this->convert_sql_int(
                    ($p_list_row_click ? 1 : 0)
                ) . ', ' . 'isys_obj_type_list__isys_property_2_cat__id = ' . $this->convert_sql_id(
                    $p_default_sorting
                ) . ', ' . 'isys_obj_type_list__sorting_direction = ' . $this->convert_sql_text(
                    $p_sorting_direction
                ) . ' ' . 'WHERE isys_obj_type_list__id = ' . $this->convert_sql_id($l_row['isys_obj_type_list__id']) . ';';
        }
        else
        {
            $l_sql = 'INSERT INTO isys_obj_type_list (' . 'isys_obj_type_list__isys_obj__id, ' . 'isys_obj_type_list__isys_obj_type__id, ' . 'isys_obj_type_list__query, ' . 'isys_obj_type_list__config, ' . 'isys_obj_type_list__row_clickable, ' . 'isys_obj_type_list__isys_property_2_cat__id, ' . 'isys_obj_type_list__sorting_direction ' . ') VALUES (' . $this->convert_sql_id(
                    $g_comp_session->get_user_id()
                ) . ', ' . $this->convert_sql_id($p_object_type_id) . ', ' . $this->convert_sql_text($p_list_query) . ', ' . $this->convert_sql_text(
                    $p_list_config
                ) . ', ' . $this->convert_sql_int(($p_list_row_click ? 1 : 0)) . ', ' . $this->convert_sql_id($p_default_sorting) . ', ' . $this->convert_sql_text(
                    $p_sorting_direction
                ) . ');';
        } // if

        return $this->update($l_sql) && $this->apply_update();
    } // function

    /**
     * Check whether the Object-type has one of the categories inside of $p_constants.
     *
     * @param   integer $p_obj_type  Object-TypeID
     * @param   array   $p_constants Category-Constants
     *
     * @return  boolean
     * @author  Selcuk Kekec <skekec@i-doit.com>
     */
    public function has_cat($p_obj_type, $p_constants = [])
    {
        if (count($p_constants) == 0)
        {
            return false;
        } // if

        // Add quotation marks around the constants for the IN condition.
        $l_conditioner = [];
        $l_is_array    = false;
        if (is_array($p_constants))
        {
            $l_is_array = true;
            foreach ($p_constants as $l_cats_constant)
            {
                $l_conditioner[] = $this->convert_sql_text($l_cats_constant);
            } // foreach
            $l_where_condition_category = "AND igc.isysgui_catg__const IN(" . implode(",", $l_conditioner) . ");";
        }
        else
        {
            $l_where_condition_category = "AND igc.isysgui_catg__const = " . $this->convert_sql_text($p_constants) . ";";
        } // if

        // CATG-Query.
        $l_sql = "SELECT *
			FROM isys_obj_type_2_isysgui_catg
			INNER JOIN isysgui_catg igc ON isys_obj_type_2_isysgui_catg__isysgui_catg__id = igc.isysgui_catg__id
			WHERE isys_obj_type_2_isysgui_catg__isys_obj_type__id = " . $this->convert_sql_id($p_obj_type) . " " . $l_where_condition_category;

        if (count($this->retrieve($l_sql)) > 0)
        {
            return true;
        } // if

        $l_res = $this->get_specific_category($p_obj_type, C__RECORD_STATUS__NORMAL, null, true);

        if (count($l_res) > 0)
        {
            while ($l_row = $l_res->get_row())
            {
                if ($l_is_array)
                {
                    if (in_array($l_row["isysgui_cats__const"], $p_constants))
                    {
                        return true;
                    } // if
                }
                else
                {
                    if ($l_row["isysgui_cats__const"] == $p_constants)
                    {
                        return true;
                    } // if
                } // if
            } // while
        } // if

        return false;
    } // function

    /**
     * Get all global categories for the given object type id (as a result set).
     *
     * @param   integer $p_obj_type
     * @param   integer $p_nRecStatus
     *
     * @return  isys_component_dao_result
     * @author  dennis stuecken <dstuecken@i-doit.org>
     */
    public function get_global_categories($p_obj_type, $p_nRecStatus = C__RECORD_STATUS__NORMAL)
    {
        $l_sql = "SELECT * FROM isysgui_catg
				INNER JOIN isys_obj_type_2_isysgui_catg ON isysgui_catg__id = isys_obj_type_2_isysgui_catg__isysgui_catg__id
				WHERE isysgui_catg__status = " . $this->convert_sql_int($p_nRecStatus) . " ";

        if (!is_null($p_obj_type))
        {
            $l_sql .= " AND isys_obj_type_2_isysgui_catg__isys_obj_type__id = " . $this->convert_sql_id($p_obj_type) . "";
        } // if

        $l_sql .= " ORDER BY isysgui_catg__title ASC;";

        return $this->retrieve($l_sql);
    } // function

    /**
     * Get the specific category for object type (as a result set)
     *
     * @param int $p_obj_type
     *
     * @author dennis stuecken <dstuecken@i-doit.org> 2007-07-23
     * @return isys_component_dao_result
     */
    public function get_specific_category($p_obj_type = null, $p_nRecStatus = C__RECORD_STATUS__NORMAL, $p_category_id = null, $p_cats_childs = null)
    {
        if (empty($p_nRecStatus))
        {
            $p_nRecStatus = C__RECORD_STATUS__NORMAL;
        } // if

        $l_sql = "SELECT * FROM isysgui_cats
			JOIN isys_tree_group ON isys_tree_group__id = 1
			INNER JOIN isys_obj_type ON isys_obj_type__isysgui_cats__id = isysgui_cats__id
			WHERE isysgui_cats__status = " . $this->convert_sql_int($p_nRecStatus);

        if (!is_null($p_category_id))
        {
            $l_sql .= " AND isysgui_cats__id = " . $this->convert_sql_id($p_category_id);
        } // if

        if (!is_null($p_obj_type))
        {
            $l_sql .= " AND isys_obj_type__id = " . $this->convert_sql_id($p_obj_type);
        } // if

        if ($p_cats_childs)
        {
            $l_row       = $this->retrieve($l_sql . ';')
                ->get_row();
            $l_spec_cats = [$l_row['isysgui_cats__id']];
            $l_childRes  = $this->retrieve(
                "SELECT * FROM isysgui_cats_2_subcategory
				INNER JOIN isysgui_cats ON isysgui_cats__id = isysgui_cats_2_subcategory__isysgui_cats__id__child
				WHERE isysgui_cats_2_subcategory__isysgui_cats__id__parent = " . $this->convert_sql_id($l_row['isysgui_cats__id'])
            );

            if ($l_childRes->num_rows() > 0)
            {
                while ($l_row = $l_childRes->get_row())
                {
                    $l_spec_cats[] = $l_row["isysgui_cats__id"];
                } // while

                if (count($l_spec_cats) > 0)
                {
                    return $this->retrieve("SELECT * FROM isysgui_cats WHERE isysgui_cats__id IN(" . implode(",", $l_spec_cats) . ")");
                }
                else
                {
                    return false;
                } // if
            } // if
        } // if

        return $this->retrieve($l_sql);
    } // function

}