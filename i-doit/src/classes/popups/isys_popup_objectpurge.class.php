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
 * @package    i-doit
 * @subpackage Popups
 * @author     Dennis Stücken <dstuecken@synetics.de> 2010-08
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_popup_objectpurge extends isys_component_popup
{

    public function handle_popup_url($p_params)
    {
        return "&editMode=" . C__EDITMODE__ON . "&objects=" . base64_encode($p_params["p_strObjects"]) . "&headline=" . base64_encode(
            $p_params["p_strHeadline"]
        ) . "&message=" . base64_encode($p_params["p_strMessage"]);

    }

    public function handle_smarty_include(isys_component_template &$p_tplclass, $p_params)
    {
        global $g_config;

        $l_url = $g_config["startpage"] . "?mod=cmdb&" . "popup=objectpurge" . $this->handle_popup_url($p_params);

        $this->set_config("width", 480);
        $this->set_config("height", 500);
        $this->set_config("scrollbars", "no");

        return $this->process($l_url, true);
    }

    /**
     * @global                    $g_comp_database
     *
     * @param isys_module_request $p_modreq
     *
     * @return isys_component_template&
     * @desc ...
     */
    public function &handle_module_request(isys_module_request $p_modreq)
    {
        global $g_comp_database;

        $l_cmdb_dao = new isys_cmdb_dao($g_comp_database);

        /* Prepare new template for popup */
        $l_tplpopup = isys_component_template::instance();
        $l_tplpopup->assign("file_body", "popup/objectpurge.tpl");

        /* Assign message */
        $l_tplpopup->assign("message", base64_decode($_GET["message"]));

        /* Assign headline */
        $l_tplpopup->assign("headline", base64_decode($_GET["headline"]));

        /* Retrieve objects */
        $l_objects = explode(",", base64_decode($_GET["objects"]));
        $l_arr_objects = [];
        if (is_array($l_objects))
        {
            foreach ($l_objects as $l_object)
            {
                if ($l_object > 0)
                {
                    $l_arr_objects[$l_object] = $l_cmdb_dao->get_obj_name_by_id_as_string($l_object);
                } // if
            } // foreach
        } // if
        $l_tplpopup->assign("objects", $l_arr_objects);

        return $l_tplpopup;
    }

    public function __construct()
    {
        parent::__construct();
    }
}

?>