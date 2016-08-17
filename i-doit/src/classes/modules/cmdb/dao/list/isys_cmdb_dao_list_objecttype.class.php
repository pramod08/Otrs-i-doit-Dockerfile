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
 * DAO: ObjectType lists.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_objecttype extends isys_component_dao_object_table_list
{
    /**
     * Retrieve all obj_types.
     *
     * @return  isys_component_dao_result
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_result($p_strTableName = null, $p_object_id = null, $p_cRecStatus = null)
    {
        $l_sql = "SELECT
			isys_obj_type__id,
			isys_obj_type__title,
			isys_obj_type_group__title,
			isys_obj_type__color AS color,
			isys_obj_type__overview AS overview,
			isys_obj_type__container AS container,
			isys_obj_type__isysgui_cats__id AS cats,
			isysgui_cats__title AS cats_title,
			COUNT(isys_obj__id) AS object_count,
			isys_obj_type__show_in_tree AS show_in_tree
			FROM isys_obj_type
			LEFT JOIN isys_obj ON isys_obj__isys_obj_type__id = isys_obj_type__id
			LEFT JOIN isys_obj_type_group ON isys_obj_type__isys_obj_type_group__id = isys_obj_type_group__id
			LEFT JOIN isysgui_cats ON isys_obj_type__isysgui_cats__id = isysgui_cats__id
			WHERE isys_obj_type__const != 'C__OBJTYPE__LOCATION_GENERIC' ";

        $l_allowed_objecttypes = isys_auth_cmdb_object_types::instance()
            ->get_allowed_objecttype_configs();

        if (is_array($l_allowed_objecttypes) && count($l_allowed_objecttypes) > 0)
        {
            $l_sql .= ' AND isys_obj_type__id IN (' . implode(',', $l_allowed_objecttypes) . ') ';
        }
        elseif ($l_allowed_objecttypes === false)
        {
            $l_sql .= ' AND isys_obj_type__id = FALSE ';
        }

        if ($_GET[C__CMDB__GET__OBJECTGROUP])
        {
            $l_sql .= " AND (isys_obj_type_group__id = " . $this->convert_sql_id($_GET[C__CMDB__GET__OBJECTGROUP]) . ")";
        } // if

        $l_sql .= "GROUP BY isys_obj_type__id;";

        return $this->retrieve($l_sql);
    } // function

    /**
     * Method for modifying the single row-data.
     *
     * @param  array &$p_arrRow
     */
    public function modify_row(&$p_arrRow)
    {
        $p_arrRow['show_in_tree']         = (!$p_arrRow['show_in_tree']) ? '<span class="red">' . _L('LC__UNIVERSAL__NO') . '</span>' : '<span class="green">' . _L(
                'LC__UNIVERSAL__YES'
            ) . '</span>';
        $p_arrRow['overview']             = (!$p_arrRow['overview']) ? _L('LC__UNIVERSAL__NO') : _L('LC__UNIVERSAL__YES');
        $p_arrRow['container']            = (!$p_arrRow["container"]) ? _L('LC__UNIVERSAL__NO') : _L('LC__UNIVERSAL__YES');
        $p_arrRow['cats']                 = (!$p_arrRow['cats']) ? isys_tenantsettings::get('gui.empty_value', '-') : _L($p_arrRow['cats_title']);
        $p_arrRow["object_count"]         = "<span class=\"grey\">" . $p_arrRow["object_count"] . "</span>";
        $p_arrRow["isys_obj_type__title"] = '<span class="cmdb-marker" style="background:#' . $p_arrRow["color"] . ';"></span> <span class="vam">' . _L(
                $p_arrRow["isys_obj_type__title"]
            ) . '</span>';
    } // function

    /**
     * Method for returning the fields to display in the list.
     *
     * @return  array
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_fields()
    {
        return [
            'isys_obj_type__id'          => 'LC__UNIVERSAL__ID',
            'isys_obj_type__title'       => 'LC__UNIVERSAL__TITLE',
            'isys_obj_type_group__title' => 'LC__CMDB__OBJTYPE__GROUP',
            'cats'                       => 'LC__REPORT__FORM__SELECT_PROPERTY_S',
            'overview'                   => 'LC__CMDB__CATG__OVERVIEW',
            'container'                  => 'LC__CMDB__OBJTYPE__LOCATION',
            'object_count'               => _L('LC_UNIVERSAL__OBJECT') . ' ' . _L('LC__POPUP__DUPLICATE__NUMBER'),
            'show_in_tree'               => 'LC__CMDB__OBJTYPE__SHOW_IN_TREE'
        ];
    } // function
} // class