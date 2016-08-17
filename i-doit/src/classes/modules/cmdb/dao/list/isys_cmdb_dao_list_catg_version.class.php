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
 * DAO: global category list for versions.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_catg_version extends isys_cmdb_dao_list
{
    /**
     * Gets fields to display in the list view.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            'isys_catg_version_list__title'       => 'LC__CATG__VERSION_TITLE',
            'isys_catg_version_list__servicepack' => 'LC__CATG__VERSION_SERVICEPACK',
            'isys_catg_version_list__kernel'      => 'LC__CATG__VERSION_KERNEL',
            'isys_catg_version_list__hotfix'      => 'LC__CATG__VERSION_PATCHLEVEL'
        ];
    } // function
} // class