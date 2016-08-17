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
class isys_cmdb_ui_category_g_jdisc_ca extends isys_cmdb_ui_category_global
{
    /**
     * Process method for displaying the template.
     *
     * @global  array                             $index_includes
     *
     * @param   isys_cmdb_dao_category_g_jdisc_ca & $p_cat
     *
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        // Initializing some variables.
        $l_rules   = [];
        $l_catdata = $p_cat->get_general_data();
        $l_locales = isys_locale::get_instance();

        switch ($l_catdata['isys_jdisc_ca_type__const'])
        {
            case 'C__JDISC__CA_TYPE__DATE':
                $l_catdata['isys_catg_jdisc_ca_list__content'] = $l_locales->fmt_date($l_catdata['isys_catg_jdisc_ca_list__content']);
                break;
            case 'C__JDISC__CA_TYPE__CURRENCY':
                $l_catdata['isys_catg_jdisc_ca_list__content'] = $l_locales->fmt_numeric(((float) $l_catdata['isys_catg_jdisc_ca_list__content'] / 100));
                break;
            default:
                break;
        }
        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        // Apply rules.
        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    } // function

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
    } // function

    public function __construct(isys_component_template &$p_template)
    {
        global $g_dirs;
        parent::__construct($p_template);
        $this->set_template($g_dirs["class"] . DS . "modules" . DS . "jdisc" . DS . "templates" . DS . "content" . DS . "bottom" . DS . "content" . DS . "catg__jdisc_ca.tpl");
    }
} // class
?>