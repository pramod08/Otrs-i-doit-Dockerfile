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
 * @package     i-doit
 * @subpackage  Popups
 * @author      Dennis Stuecken <dstuecken@i-doit.de>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_popup_relation_type extends isys_component_popup
{
    /**
     * Method for preparing the UI field.
     *
     * @param   isys_component_template $p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     */
    public function handle_smarty_include(isys_component_template &$p_tplclass, $p_params)
    {
        // Redirect request to responsible dialog plus plugin.
        $l_dialog_obj = new isys_smarty_plugin_f_dialog();

        if (!isset($p_params["p_strSelectedID"]))
        {
            $p_params["p_strSelectedID"] = "-1";
        } // if

        $l_params     = $p_params;
        $l_popup_link = isys_helper_link::create_url(
            [
                C__GET__MODULE_ID      => C__MODULE__CMDB,
                C__CMDB__GET__POPUP    => 'relation_type',
                C__CMDB__GET__EDITMODE => C__EDITMODE__ON,
                'boxname'              => $p_params["name"],
                'selID'                => $p_params["p_strSelectedID"],
            ]
        );

        $l_params["p_bPlus"] = 1;

        // ID-2844 We need the popup slightly "too high" for the case of error messages.
        $l_params["p_strLink"] = $this->process_overlay($l_popup_link, 530, 230, $l_params);

        // Removed: isys_rs_system
        if (isys_glob_is_edit_mode() && (!isset($p_params["p_editMode"]) || $p_params["p_editMode"]))
        {
            $l_params["p_arData"] = $l_dialog_obj->get_array_data(
                "isys_relation_type",
                C__RECORD_STATUS__NORMAL,
                null,
                "isys_relation_type__type = '2'"
            );

            $l_out = $l_dialog_obj->navigation_edit($p_tplclass, $l_params);
        }
        else
        {
            $l_params["p_arData"] = $l_dialog_obj->get_array_data("isys_relation_type", C__RECORD_STATUS__NORMAL);
            $l_out                = $l_dialog_obj->navigation_view($p_tplclass, $l_params);
        } // if

        return $l_out;
    } // function

    /**
     * Method for displaying the popup.
     *
     * @param   isys_module_request $p_modreq
     *
     * @return  isys_component_template
     */
    public function &handle_module_request(isys_module_request $p_modreq)
    {
        $l_params = isys_format_json::decode(base64_decode($_POST['params']), true);

        if ($l_tplpopup = $p_modreq->get_template())
        {
            $l_tplpopup->assign('parent_field', $l_params['name'])
                ->display("popup/relation_type.tpl");
            die;
        } // if

        return null;
    } // function

    /**
     * Method for saving new relation types.
     *
     * @param   string  $p_title
     * @param   string  $p_master
     * @param   string  $p_slave
     * @param   string  $p_default
     * @param   integer $p_type
     *
     * @return  integer
     * @throws  Exception
     */
    public function create($p_title, $p_master, $p_slave, $p_default = "1", $p_type = 2)
    {
        global $g_comp_database;

        $l_dao = new isys_component_dao($g_comp_database);

        if (!$p_title)
        {
            throw new Exception(_L('LC__MODULE__SYSTEM__RELATION_TYPES__ERROR__TITLE_IS_EMPTY'));
        } // if

        if (!$p_master)
        {
            throw new Exception(_L('LC__MODULE__SYSTEM__RELATION_TYPES__ERROR__MASTER_DESCRIPTION_IS_EMPTY'));
        } // if

        if (!$p_slave)
        {
            throw new Exception(_L('LC__MODULE__SYSTEM__RELATION_TYPES__ERROR__SLAVE_DESCRIPTION_IS_EMPTY'));
        } // if

        if (empty($p_default))
        {
            $p_default = "1";
        } // if

        $l_num_rows = $l_dao->retrieve(
            "SELECT isys_relation_type__id FROM isys_relation_type WHERE isys_relation_type__title = " . $l_dao->convert_sql_text($p_title) . " LIMIT 1;"
        );

        if (!count($l_num_rows))
        {
            $l_sql = "INSERT INTO isys_relation_type SET
				isys_relation_type__title = " . $l_dao->convert_sql_text($p_title) . ",
				isys_relation_type__master = " . $l_dao->convert_sql_text($p_master) . ",
				isys_relation_type__slave = " . $l_dao->convert_sql_text($p_slave) . ",
				isys_relation_type__default = " . $l_dao->convert_sql_text($p_default) . ",
				isys_relation_type__type = " . $l_dao->convert_sql_text($p_type) . ";";

            if ($l_dao->update($l_sql) && $l_dao->apply_update())
            {
                return $l_dao->get_last_insert_id();
            } // if
        }
        else
        {
            throw new Exception("Relation type already exists.");
        } // if
    } // function
} // class