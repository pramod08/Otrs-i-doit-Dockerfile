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
 * CMDB UI: Global category (category type is global).
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Blümer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_cluster_members extends isys_cmdb_ui_category_global
{
    /**
     * Process method. Usually unreachable.
     *
     * @param   isys_cmdb_dao_category_g_cluster_members $p_cat
     *
     * @return  null
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        return $this->process_list($p_cat);
    } // function

    /**
     * Show the list-template for subcategories of maintenance.
     *
     * @param   isys_cmdb_dao_category_g_cluster_members $p_cat
     *
     * @return  null
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function process_list(isys_cmdb_dao_category_g_cluster_members $p_cat)
    {
        $this->object_browser_as_new(
            [
                isys_popup_browser_object_ng::C__MULTISELECTION => true,
                isys_popup_browser_object_ng::C__FORM_SUBMIT    => true,
                isys_popup_browser_object_ng::C__RETURN_ELEMENT => C__POST__POPUP_RECEIVER,
                isys_popup_browser_object_ng::C__DATARETRIEVAL  => [
                    [
                        get_class($p_cat),
                        "get_assigned_members"
                    ],
                    $_GET[C__CMDB__GET__OBJECT]
                ]
            ],
            "LC__CATG__OBJECT__ADD",
            "LC__CMDB__CATG__SELECT_CLUSTER_MEMBERS"
        );

        parent::process_list($p_cat);
    } // function
} // class