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
 * UI: global category for network listener
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_net_listener extends isys_cmdb_ui_category_global
{

    /**
     * Processes view/edit mode.
     *
     * @param isys_cmdb_dao_category $p_cat Category's DAO
     */
    public function process(isys_cmdb_dao_category $p_category_dao)
    {
        parent::process($p_category_dao);

        $this->m_template->assign(
            'connectors',
            $p_category_dao->get_connections_by_listener_object($_GET[C__CMDB__GET__OBJECT])
                ->__as_array()
        )
            ->include_template('contentbottomcontentadditionafter', 'content/bottom/content/catg__net_listener_connection_list.tpl');

    }
} // class