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
 * CMDB UI: Port category for Network
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_network_port_overview extends isys_cmdb_ui_category_global
{
    /**
     * Show the detail-template for port as a subcategory of network.
     *
     * @param   isys_cmdb_dao_category_g_network_port_overview $p_cat
     *
     * @throws  isys_exception_general
     * @return  void
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_port_count = isys_cmdb_dao_category_g_network_port::instance($this->get_database_component())
            ->get_ports($_GET[C__CMDB__GET__OBJECT], null, C__RECORD_STATUS__NORMAL, null, [], null, true)
            ->num_rows();

        // Setting the edit-button inactive and invisible.
        isys_component_template_navbar::getInstance()
            ->set_active(false, C__NAVBAR_BUTTON__EDIT)
            ->set_visible(false, C__NAVBAR_BUTTON__EDIT);

        $this->get_template_component()
            ->assign('obj_id', $_GET[C__CMDB__GET__OBJECT])
            ->assign('port_count', $l_port_count);

        $this->deactivate_commentary();
    } // function

    /**
     * UI constructor.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function __construct(isys_component_template &$p_template)
    {
        parent::__construct($p_template);
        $this->set_template("catg__port_overview.tpl");
    } // function
} // class