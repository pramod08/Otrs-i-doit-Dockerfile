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
 * CMDB List view
 *
 * @package     i-doit
 * @subpackage  CMDB_Views
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
abstract class isys_cmdb_view_list extends isys_cmdb_view
{
    /**
     * List component
     *
     * @var  isys_component_list
     */
    protected $m_comp_list;

    /**
     * Abstract "list_init" method.
     *
     * @return  mixed
     */
    abstract public function list_init();

    /**
     * Abstract "list_process" method.
     *
     * @return  mixed
     */
    abstract public function list_process();

    /**
     * Returns the mandatory parameters.
     *
     * @param  array &$l_gets
     */
    public function get_mandatory_parameters(&$l_gets)
    {
        ;
    } // function

    /**
     * Returns the optional parameters.
     *
     * @param  array &$l_gets
     */
    public function get_optional_parameters(&$l_gets)
    {
        ;
    } // function

    /**
     * Returns template destination.
     *
     * @return  string
     */
    public function get_template_destination()
    {
        return "table_rows";
    } // function

    /**
     * This method returns the "top" template filepath.
     *
     * @return  string
     */
    public function get_template_top()
    {
        return null;
    } // function

    /**
     * Empty method.
     *
     * @param $p_navmode
     */
    public function handle_navmode($p_navmode)
    {
        ;
    } // function

    /**
     * Process method.
     *
     * @global  array $index_includes
     * @return  mixed
     */
    public function process()
    {
        // Prepare operation data.
        $l_posts      = $this->get_module_request()
            ->get_posts();
        $l_actionproc = $this->get_action_processor();

        if ($this->list_init())
        {
            // Handle selected navigation mode and fill action processor.
            $this->handle_navmode($l_posts[C__GET__NAVMODE]);

            if ($this->requires_module_reload())
            {
                return null;
            } // if

            // Process actions (if there are any).
            $l_actionproc->process();

            return $this->list_process();
        } // if
    } // function

    /**
     * Constructor method.
     *
     * @param  isys_module_request $p_modreq
     */
    public function __construct(isys_module_request $p_modreq)
    {
        parent::__construct($p_modreq);

        // Create "clean" list component.
        $this->m_comp_list = new isys_component_list();
    } // function
} // class
?>