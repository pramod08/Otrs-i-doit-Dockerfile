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
die('Wuff');

/**
 * i-doit
 *
 * DAO: Table list for the category Logbook
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_catg_logbook extends isys_cmdb_dao_list
{
    /**
     * Return constant of category.
     *
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category()
    {
        return C__CATG__LOGBOOK;
    } // function

    /**
     * Return constant of category type
     *
     * @return integer
     * @author Niclas Potthast <npotthast@i-doit.org> - 2006-09-27
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    } // function

    /**
     * @return isys_component_dao_result
     * @author  Niclas Potthast <npotthast@i-doit.org> - 2005-12-12
     * @version Dennis Stuecken 2008-07-24
     * @desc    retrieve data for category logbook list view
     */
    public function get_result($p_strTable = null, $p_nObjID, $p_unused = null)
    {
        $l_strSQL = "";

        $l_strSQL .= "SELECT isys_logbook__id, isys_logbook__title, isys_logbook__date, isys_logbook__changes, isys_logbook_level__title, isys_logbook_level__id, isys_catg_logb_list__id
            FROM isys_logbook
            INNER JOIN isys_logbook_level ON isys_logbook__isys_logbook_level__id = isys_logbook_level__id
            INNER JOIN isys_catg_logb_list ON isys_catg_logb_list__isys_obj__id = " . $this->convert_sql_id($p_nObjID) .

            " GROUP BY isys_catg_logb_list__id ORDER BY isys_logbook__date DESC;";

        return $this->retrieve($l_strSQL);
    } // function

    /**
     * @param array $p_arrRow
     *
     * @author Niclas Potthast <npotthast@i-doit.org> - 2007-10-15
     */
    public function modify_row(&$p_arrRow)
    {
        global $g_dirs;
        global $g_loc;

        if ($p_arrRow["isys_logbook__id"] != ISYS_NULL)
        {
            //set alert level
            $l_strAlertLevel = $p_arrRow["isys_logbook_level__id"];

            if ($l_strAlertLevel == C__LOGBOOK__ALERT_LEVEL__0)
            {
                $l_strAlertLevel = "blue";
            }
            else if ($l_strAlertLevel == C__LOGBOOK__ALERT_LEVEL__1)
            {
                $l_strAlertLevel = "green";
            }
            else if ($l_strAlertLevel == C__LOGBOOK__ALERT_LEVEL__2)
            {
                $l_strAlertLevel = "yellow";
            }
            else if ($l_strAlertLevel == C__LOGBOOK__ALERT_LEVEL__3)
            {
                $l_strAlertLevel = "red";
            }

            $l_strImage = '<img width="15px" height="15px" src="' . $g_dirs["images"] . 'icons/infobox/' . $l_strAlertLevel . '.png" title="' . $p_arrRow["isys_logbook_level__title"] . '" />&nbsp;&nbsp;';

            // Format date
            $p_arrRow["isys_logbook__date"] = $g_loc->fmt_datetime($p_arrRow["isys_logbook__date"]);

            // Get the alert level images
            $p_arrRow["isys_logbook_level__title"] = $l_strImage;
        }
    } // function

    /**
     * @return array
     * @global       $g_comp_template_language_manager
     *
     * @param string $p_table
     *
     * @version Niclas Potthast <npotthast@i-doit.org> - 2005-12-12
     */
    public function get_fields()
    {
        global $g_comp_template_language_manager;

        return [
            "isys_logbook__date"  => $g_comp_template_language_manager->{"LC__CMDB__LOGBOOK__DATE"},
            "isys_logbook__title" => $g_comp_template_language_manager->{"LC__CMDB__LOGBOOK__TITLE"},
        ];
    } // function
} // class