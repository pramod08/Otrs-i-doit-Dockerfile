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
 * CMDB UI: Global category (category type is global).
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_access extends isys_cmdb_ui_category_global
{
    /**
     * Process method.
     *
     * @param  isys_cmdb_dao_category_g_access $p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_obj_id = $_GET[C__CMDB__GET__OBJECT];

        $l_catdata    = $p_cat->get_general_data();
        $l_numEntries = $p_cat->get_data(null, $l_obj_id, null, null, C__RECORD_STATUS__NORMAL)
            ->count();

        // Fill in form fields with help of category properties
        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        // Here come some more specific rules
        $l_rules["C__CATG__ACCESS_URL"]["p_strValue"] = ($_POST[C__GET__NAVMODE]) ? $l_catdata["isys_catg_access_list__url"] : isys_helper_link::handle_url_variables(
            $l_catdata["isys_catg_access_list__url"],
            $l_obj_id
        );

        $l_variables = isys_helper_link::get_url_variables($l_obj_id);

        foreach ($l_variables as $l_key => $l_value)
        {
            $l_variables[$l_key] = (empty($l_value) ? isys_tenantsettings::get('gui.empty_value', '-') : $l_value);
        } // foreach

        ksort($l_variables);

        if ((!$l_numEntries && $_POST['navMode'] == 1) || ($l_numEntries == 1 && $l_catdata["isys_catg_access_list__id"] && $_POST["navMode"] == 2))
        {
            $l_rules["C__CATG__ACCESS_PRIMARY"]["p_strSelectedID"] = 1;
            $l_rules["C__CATG__ACCESS_PRIMARY"]["p_bDisabled"]     = "1";
        } // if

        // Apply rules.
        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules)
            ->assign('accessPlaceholders', $l_variables);
    } // function
} // class