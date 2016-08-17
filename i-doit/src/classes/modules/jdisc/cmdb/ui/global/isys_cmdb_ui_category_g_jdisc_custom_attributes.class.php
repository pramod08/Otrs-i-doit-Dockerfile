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
 * UI: global category for jdisc custom attribute
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_jdisc_custom_attributes extends isys_cmdb_ui_category_global
{
    /**
     * Sets the template file (*.tpl).
     *
     * @param   string $p_template
     *
     * @return  isys_cmdb_ui_category
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function set_template($p_template)
    {
        global $g_dirs;
        $this->m_template_file = $g_dirs["class"] . DS . "modules" . DS . "jdisc" . DS . "templates" . DS . "content" . DS . "bottom" . DS . "content" . DS . $p_template;

        return $this;
    }

    public function __construct(isys_component_template &$p_template)
    {
        global $g_dirs;
        parent::__construct($p_template);
        $this->set_template(
            $g_dirs["class"] . DS . "modules" . DS . "jdisc" . DS . "templates" . DS . "content" . DS . "bottom" . DS . "content" . DS . "catg__jdisc_custom_attributes.tpl"
        );
    } // function

} // class
?>