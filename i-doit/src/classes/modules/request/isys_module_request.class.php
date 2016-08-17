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
 * Class for a module request.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_module_request
{
    const DISPLAY_IN_MAIN_MENU = false;

    // Define, if this module shall be displayed in the named menus.
    const DISPLAY_IN_SYSTEM_MENU = false;
    /**
     * @var boolean
     */
    protected static $m_licenced = true;
    /**
     * Singleton instance
     *
     * @var isys_module_request
     */
    private static $m_instance;
    /**
     * @var  isys_component_database
     */
    private $m_db;
    /**
     * @var  array
     */
    private $m_get;
    /**
     * The module request.
     *
     * @var  isys_component_tree
     */
    private $m_menutree;
    /**
     * @var  isys_module_manager
     */
    private $m_modman;
    /**
     * @var  isys_component_template_navbar
     */
    private $m_objNavbar;
    /**
     * @var  array
     */
    private $m_post;
    /**
     * @var  isys_component_template
     */
    private $m_template;

    /**
     * Returns a module request instance object.
     *
     * @param   isys_component_tree            $p_menutree
     * @param   isys_component_template        $p_template
     * @param   array                          $p_get
     * @param   array                          $p_post
     * @param   isys_component_template_navbar $p_objNavbar
     * @param   isys_component_database        $p_db
     * @param   isys_module_manager            $p_modman
     *
     * @return  isys_module_request
     */
    public static function build($p_menutree, $p_template, &$p_get, &$p_post, $p_objNavbar, $p_db, $p_modman)
    {
        if (!self::$m_instance)
        {
            self::$m_instance = new isys_module_request($p_menutree, $p_template, $p_get, $p_post, $p_objNavbar, $p_db, $p_modman);
        } // if

        return self::$m_instance;
    } // function

    /**
     * Return singleton instance
     *
     * @return isys_module_request
     */
    public static function get_instance()
    {
        if (!self::$m_instance)
        {
            self::$m_instance = self::build(
                isys_component_tree::factory(),
                isys_application::instance()->template,
                $_GET,
                $_POST,
                isys_component_template_navbar::getInstance(),
                isys_application::instance()->database,
                isys_module_manager::instance()
            );
        }

        return self::$m_instance;
    } // function

    /**
     * In order to change the module request at runtime, you can use this function. If you use this function, please write your functionname, line and file in this list:
     * AW: isys_module_cmdb.class.php, request_conformer
     *
     * @param  string $p_varname
     * @param  mixed  $p_newvalue
     *
     * @return $this
     */
    final public function _internal_set_private($p_varname, $p_newvalue)
    {
        if (isset($this->$p_varname))
        {
            $this->$p_varname = $p_newvalue;
        } // if

        return $this;
    } // function

    /**
     * This method builds the tree for the menu.
     *
     * @param   isys_component_tree $p_tree
     * @param   boolean             $p_system_module
     * @param   integer             $p_parent
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @since   0.9.9-7
     */
    public function build_tree(isys_component_tree $p_tree, $p_system_module = true, $p_parent = null)
    {
        ;
    } // function

    /**
     * Returns a reference to the menutree.
     *
     * @return isys_component_tree
     */
    public function &get_menutree()
    {
        return $this->m_menutree;
    } // function

    /**
     * Returns a reference to the GET variables.
     *
     * @return  array
     */
    public function &get_gets()
    {
        return $this->m_get;
    } // function

    /**
     * Returns one GET variables.
     *
     * @param   string $p_key
     * @param   string $p_default
     *
     * @return  mixed
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get($p_key, $p_default = '')
    {
        if (isset($this->m_get[$p_key]))
        {
            return $this->m_get[$p_key];
        } // if

        return $p_default;
    } // function

    /**
     * Returns a reference to the POST variables.
     *
     * @return  array
     */
    public function &get_posts()
    {
        return $this->m_post;
    } // function

    /**
     * Returns one POST variables.
     *
     * @param   string $p_key
     * @param   string $p_default
     *
     * @return  mixed
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_post($p_key, $p_default = '')
    {
        if (isset($this->m_post[$p_key]))
        {
            return $this->m_post[$p_key];
        } // if

        return $p_default;
    } // function

    /**
     * Returns a reference to the template component.
     *
     * @return  isys_component_template
     */
    public function &get_template()
    {
        return $this->m_template;
    } // function

    /**
     * Returns a reference to the navbar object.
     *
     * @return  isys_component_template_navbar
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function &get_navbar()
    {
        return $this->m_objNavbar;
    } // function

    /**
     * Returns a reference to the database component.
     *
     * @return  isys_component_database
     */
    public function &get_database()
    {
        return $this->m_db;
    } // function

    /**
     * Returns a reference to the module manager object.
     *
     * @return  isys_module_manager
     */
    public function &get_module_manager()
    {
        return $this->m_modman;
    } // function

    /**
     * Constructor, private but used by factory method "build".
     *
     * @param  isys_component_tree            $p_menutree
     * @param  isys_component_template        $p_template
     * @param  array                          $p_get
     * @param  array                          $p_post
     * @param  isys_component_template_navbar $p_objNavbar
     * @param  isys_component_database        $p_db
     * @param  isys_module_manager            $p_modman
     */
    private function __construct(&$p_menutree, &$p_template, &$p_get, &$p_post, &$p_objNavbar, &$p_db, &$p_modman)
    {
        $this->m_menutree  = $p_menutree;
        $this->m_template  = $p_template;
        $this->m_get       = $p_get;
        $this->m_post      = $p_post;
        $this->m_objNavbar = $p_objNavbar;
        $this->m_db        = $p_db;
        $this->m_modman    = $p_modman;
    } // function
} // class