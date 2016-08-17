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
 * Smarty plugin for object images. Returns a string with the source of the image.
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */

define("C__IMAGE_SIZE__THUMB", "28px");
define("C__IMAGE_SIZE__NORMAL", "100px");

class isys_smarty_plugin_object_image extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * @param      $p_filename
     * @param null $p_objectType
     *
     * @return string
     */
    public static function get_user_defined_image_url_by_file($p_filename, $p_objectType = null)
    {
        global $g_dirs;

        $l_dlgets[C__GET__MODULE_ID]    = C__MODULE__CMDB;
        $l_dlgets[C__GET__FILE_MANAGER] = "image";

        if (!is_null($p_filename) && file_exists($g_dirs["fileman"]["image_dir"] . "/" . $p_filename))
        {
            if (!empty($p_filename))
            {
                $l_dlgets["file"] = $p_filename;

                return '?' . isys_glob_http_build_query($l_dlgets);
            } // if
        } // if

        return "images/objecttypes/" . self::getImageNameByObjTypeID($p_objectType);
    } // function

    /**
     * @param   integer $p_objID
     *
     * @return  string
     * @author  Dennis Stuecken <dstuecken@synetics.de>
     */
    public static function get_user_defined_image_url_by_id($p_objID, $p_objTypeID = null)
    {
        global $g_comp_database;

        $l_image_dao = new isys_cmdb_dao_category_g_image($g_comp_database);

        return self::get_user_defined_image_url_by_file($l_image_dao->get_image_name_by_object_id($p_objID), $p_objTypeID);
    } // function

    /**
     * Return image name in objecttype image directory.
     *
     * @global  isys_component_database $g_comp_database
     *
     * @param   integer                 $p_objTypeID
     *
     * @return  string (empty if nothing is found)
     * @author  Niclas Potthast <npotthat@i-doit.org>
     */
    private static function getImageNameByObjTypeID($p_objTypeID)
    {
        global $g_comp_database;

        if ($p_objTypeID > 0)
        {
            $l_strName = isys_cmdb_dao::instance($g_comp_database)
                ->get_objtype_img_by_id_as_string($p_objTypeID);

            if (!empty($l_strName))
            {
                return $l_strName;
            } // if
        }

        return C__OBJTYPE_IMAGE__DEFAULT;
    }

    /**
     * Defines wheather the sm2 meta map is enabled or not.
     *
     * @return  boolean
     */
    public function enable_meta_map()
    {
        return false;
    } // function

    /**
     * Return the image to an objecttype (or object - todo).
     *
     * @param    isys_component_template &$p_tplclass
     * @param    array                   $p_params
     *
     * @return   string
     * @author   Niclas Potthast <npotthast@i-doit.org>
     */
    public function navigation_view(isys_component_template &$p_tplclass, $p_params = null)
    {
        global $g_config;

        if ($p_params === null)
        {
            $p_params = $this->m_parameter;
        } // if

        $this->m_strPluginClass = "object_image";
        $this->m_strPluginName  = $p_params["name"];

        if (empty($p_params["objType"]))
        {
            $l_nObjTypeID = $_GET["objTypeID"];
        }
        else
        {
            $l_nObjTypeID = $p_params["objType"];
            $l_nObjID     = null;
        } // if

        if (empty($p_params["objID"]))
        {
            $l_nObjID = $_GET["objID"];
        }
        else
        {
            $l_nObjID = $p_params["objID"];
        } // if

        // Get user image.
        $l_strSrc = $g_config['www_dir'] . $this->get_user_defined_image_url_by_id($l_nObjID, $l_nObjTypeID);

        if ($p_params["p_bThumb"] == "1" || $_SESSION["viewMode"]["contentTop"] == "off")
        {
            $l_width  = C__IMAGE_SIZE__THUMB;
            $l_height = C__IMAGE_SIZE__THUMB;
        }
        else
        {
            $l_width  = C__IMAGE_SIZE__NORMAL;
            $l_height = C__IMAGE_SIZE__NORMAL;
        } // if

        if ($p_params["width"])
        {
            $l_width = $p_params["width"];
        } // if

        if ($p_params["height"])
        {
            $l_height = $p_params["height"];
        } // if

        $l_class = $l_style = $l_align = "";

        if (!empty($p_params["class"]))
        {
            $l_class = ' class="' . $p_params["class"] . '"';
        } // if

        if (!empty($p_params["style"]))
        {
            $l_style = ' style="' . $p_params["style"] . '"';
        } // if

        if (!empty($p_params["align"]))
        {
            $l_align = ' align="' . $p_params["align"] . '"';
        } // if

        return '<img id="object_image_header" ' . $l_class . $l_style . $l_align . ' width="' . $l_width . '" height="' . $l_height . '" src="' . $l_strSrc . '" alt="' . $p_params["alt"] . '" />';
    } // function

    /**
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     */
    public function navigation_edit(isys_component_template &$p_tplclass, array $p_params = null)
    {
        return $this->navigation_view($p_tplclass, $p_params);
    } // function
} // class