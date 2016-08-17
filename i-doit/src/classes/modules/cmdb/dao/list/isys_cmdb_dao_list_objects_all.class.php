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
 * DAO: Object lists
 *
 * ** CURRENTLY; THIS IS FOR ALL OBJECT TYPES, ALTHOUGH THIS IS A GENERIC IMPLEMENTATION **
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @author      Dennis Stuecken <dstuecken@i-doit.org>
 * @author      Dennis Bluemer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 *
 */
class isys_cmdb_dao_list_objects_all extends isys_cmdb_dao_list
{
    /**
     * @param  array $p_row
     */
    public function format_row(&$p_row)
    {
        global $g_loc;

        $l_empty_value = isys_tenantsettings::get('gui.empty_value', '-');

        if ($p_row['updated'] == '0000-00-00 00:00:00')
        {
            $p_row['updated'] = $l_empty_value;
        } // if

        if ($p_row['created'] == '0000-00-00 00:00:00')
        {
            $p_row['created'] = $l_empty_value;
        } // if

        if (!empty($p_row['updated_by']))
        {
            $p_row['updated'] = '<span title="' . $g_loc->fmt_datetime($p_row['updated'], true, false) . '">' . $g_loc->fmt_date(
                    $p_row['updated']
                ) . ' (' . $p_row['updated_by'] . ')</span>';
        }
        else
        {
            $p_row['updated'] = '<span title="' . $g_loc->fmt_datetime($p_row['updated'], true, false) . '">' . $g_loc->fmt_date($p_row['updated']) . '</span>';
        } // if

        if (!empty($p_row['created_by']))
        {
            $p_row['created'] = '<span title="' . $g_loc->fmt_datetime($p_row['created'], true, false) . '">' . $g_loc->fmt_date(
                    $p_row['created']
                ) . ' (' . $p_row['created_by'] . ')</span>';
        }
        else
        {
            $p_row['created'] = '<span title="' . $g_loc->fmt_datetime($p_row['created'], true, false) . '">' . $g_loc->fmt_date($p_row['created']) . '</span>';
        } // if
    } // function

    /**
     * @param   integer $p_objtypeid
     *
     * @return  isys_component_dao_result
     */
    public function get_result($p_objtypeid = null, $p_unused, $p_cRecStatus = null)
    {
        /**
         * The route we go when we evaluate object lists based on an object type:
         *
         * Primary object table:      t_prim -> isys_obj
         * Category data list:        isys_catg_global_list
         */
        if (defined('C__MODULE__NAGIOS') && isys_module_manager::instance()
                ->get_by_id(C__MODULE__NAGIOS)
        )
        {
            $l_nagios_select = 't_nagios.isys_catg_nagios_list__name1 AS nagios_name, ';
            $l_nagios_join   = 'LEFT JOIN isys_catg_nagios_list AS t_nagios ON t_nagios.isys_catg_nagios_list__isys_obj__id = isys_obj__id ';
        }
        else
        {
            $l_nagios_select = '';
            $l_nagios_join   = '';
        } // if

        $l_cObjStatus = $this->get_rec_status();

        if (empty($l_cObjStatus))
        {
            $l_cObjStatus = C__RECORD_STATUS__NORMAL;
        }

        $l_strSQL = 'SELECT *,
 		    t_prim.isys_obj__id AS object_id,
 		    t_prim.isys_obj__title AS object_title,
 		    t_prim.isys_obj__sysid AS object_sysid,
 		    t_prim.isys_obj__created AS created,
 		    t_prim.isys_obj__created_by AS created_by,
 		    t_prim.isys_obj__updated AS updated,
 		    t_prim.isys_obj__updated_by AS updated_by,
 		    isys_catg_global_category__title AS object_category,
 		    isys_purpose__title AS purpose,
 		    isys_cmdb_status__title as cmdb_status,
 		    ' . $l_nagios_select . '
 		    "" AS object_location

 		    FROM isys_obj AS t_prim

 		    LEFT JOIN isys_cmdb_status ON isys_obj__isys_cmdb_status__id = isys_cmdb_status__id
 		    LEFT JOIN isys_catg_global_list ON isys_catg_global_list__isys_obj__id = isys_obj__id
 		    LEFT JOIN isys_catg_global_category ON isys_catg_global_list__isys_catg_global_category__id = isys_catg_global_category__id
 		    LEFT JOIN isys_purpose ON isys_purpose__id = isys_catg_global_list__isys_purpose__id
 		    ' . $l_nagios_join . '
 		    WHERE TRUE ';

        if ($p_objtypeid)
        {
            $l_strSQL .= ' AND isys_obj__isys_obj_type__id = ' . $this->convert_sql_id($p_objtypeid);
        } // if

        $l_strSQL .= ' AND t_prim.isys_obj__status = ' . $this->convert_sql_int($l_cObjStatus) . ' ' . $this->prepare_status_filter() . ';';

        return $this->retrieve($l_strSQL);
    } // function

    /**
     * @param  array $p_row
     */
    public function modify_row(&$p_row)
    {
        global $g_comp_template_language_manager;
        $l_tmp = null;

        if ($_GET[C__CMDB__GET__OBJECTTYPE] != C__OBJTYPE__APPLICATION && $_GET[C__CMDB__GET__OBJECTTYPE] != C__OBJTYPE__OPERATING_SYSTEM && $_GET[C__CMDB__GET__OBJECTTYPE] != C__OBJTYPE__LICENCE && $_GET[C__CMDB__GET__OBJECTTYPE] != C__OBJTYPE__SERVICE && $_GET[C__CMDB__GET__OBJECTTYPE] != C__OBJTYPE__FILE && $_GET[C__CMDB__GET__OBJECTTYPE] != C__OBJTYPE__EMERGENCY_PLAN)
        {
            $l_loc_popup              = new isys_popup_browser_location();
            $p_row['object_location'] = $l_loc_popup->format_selection($p_row['object_id']);
        }

        $l_quick_info = new isys_ajax_handler_quick_info();

        $p_row['object_title'] = $l_quick_info->get_quick_info(
            $p_row['object_id'],
            $p_row['object_title'],
            "javascript:void('" . $p_row["object_title"] . "');",
            isys_tenantsettings::get('maxlength.object.lists', 55)
        );

        $p_row['object_location'] = substr($p_row['object_location'], 0, (strlen($p_row['object_location']) - 3));

        if ($p_row['nagios_name'] != null)
        {
            $p_row['object_status'] = '<div id="nagios_' . $p_row['object_id'] . '"></div><script type="text/javascript">aj_submit(\'?' . C__GET__MODULE_ID . '=' . C__MODULE__NAGIOS . '&request=nagios_host_state&objID=' . $p_row['object_id'] . '\', \'get\', \'nagios_' . $p_row['object_id'] . '\');</script>';
        }

        $p_row['cmdb_status'] = '<div class="fl gradient" style="background-color:#' . $p_row['isys_cmdb_status__color'] . ';height:18px;width:4px;"></div>&nbsp;' . $g_comp_template_language_manager->get(
                $p_row['cmdb_status']
            );

        $p_row['object_category'] = isys_glob_str_stop($p_row['object_category'], 30);
    } // function

    /**
     * @return  array
     * @global  array $g_lists
     */
    public function get_fields()
    {
        global $g_lists;

        $l_fields = $g_lists[get_class($this)];

        switch ($_GET[C__CMDB__GET__OBJECTTYPE])
        {
            case C__OBJTYPE__APPLICATION:
            case C__OBJTYPE__SERVICE:
            case C__OBJTYPE__OPERATING_SYSTEM:
            case C__OBJTYPE__LICENCE:
            case C__OBJTYPE__EMERGENCY_PLAN:
            case C__OBJTYPE__FILE:
            case C__OBJTYPE__LAYER3_NET:
                unset($l_fields['object_location']);
                break;
        }

        if (defined('C__MODULE__NAGIOS') && isys_module_manager::instance()
                ->get_by_id(C__MODULE__NAGIOS)
        )
        {
            $l_fields['object_status'] = 'Nagios-Status';
        } // if

        return $l_fields;
    } // function
} // class