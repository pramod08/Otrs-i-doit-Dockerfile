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
 * DAO: assigned nagios services
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_Lists
 * @author      Selcuk Kekec <skekec@i-doit.org>
 * @copyright   synetics GmbH
 * @varsion     1.1
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_catg_nagios_service_dep extends isys_cmdb_dao_list
{
    /**
     * Returns the category ID.
     *
     * @return  integer
     */
    public function get_category()
    {
        return C__CATG__NAGIOS_SERVICE_DEP;
    } // function

    /**
     * Returns the category type.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    } // function

    /**
     *
     *
     * @param   string  $p_str
     * @param   integer $p_objID
     * @param   integer $p_cRecStatus
     *
     * @return  isys_component_dao_result
     * @author  Selcuk Kekec <skekec@i-doit.org>
     */
    public function get_result($p_str = null, $p_objID, $p_cRecStatus = null)
    {
        $l_cRecStatus = empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus;

        return isys_cmdb_dao_category_g_nagios_service_dep::instance($this->get_database_component())
            ->get_collected_data($p_objID, $l_cRecStatus);
    } // function

    /**
     * Modify row method will be called by each iteration.
     *
     * @param   array $p_row
     *
     * @author  Selcuk Kekec <skekec@i-doit.org>
     */
    public function modify_row(&$p_row)
    {
        $l_dao = isys_cmdb_dao_connection::instance(isys_application::instance()->database);

        $l_service = $l_dao->get_object_by_id($p_row['isys_catg_nagios_service_dep_list__isys_obj__id'], true)->get_row();
        $p_row['service'] = '<a href="?' . C__CMDB__GET__OBJECT . '=' . $l_service['isys_obj__id'] . '">' . $l_service['isys_obj__title'] . '</a>';

        if ($p_row['host'])
        {
            $l_host        = $l_dao->get_object_by_id($p_row['host'], true)->get_row();
            $p_row['host'] = '<a href="?' . C__CMDB__GET__OBJECT . '=' . $l_host['isys_obj__id'] . '">' . $l_host['isys_obj__title'] . '</a>';
        } // if

        if ($p_row['servicedep'])
        {
            $l_service_dep       = $l_dao->get_object_by_id($p_row['servicedep'], true)->get_row();
            $p_row['servicedep'] = '<a href="?' . C__CMDB__GET__OBJECT . '=' . $l_service_dep['isys_obj__id'] . '">' . $l_service_dep['isys_obj__title'] . '</a>';
        } // if

        if ($p_row['hostdep'])
        {
            $l_host_dep       = $l_dao->get_object_by_id($p_row['hostdep'], true)->get_row();
            $p_row['hostdep'] = '<a href="?' . C__CMDB__GET__OBJECT . '=' . $l_host_dep['isys_obj__id'] . '">' . $l_host_dep['isys_obj__title'] . '</a>';
        } // if

        if ($p_row['isys_catg_nagios_service_dep_list__isys_obj__id'] != $_GET[C__CMDB__GET__OBJECT])
        {
            $p_row[isys_component_list::CL__DISABLE_ROW] = true;
        } // if
    } // function

    /**
     * Build header for the list.
     *
     * @return  array
     * @author  Selcuk Kekec <skekec@i-doit.org>
     */
    public function get_fields()
    {
        return [
            'service'                            => 'LC__CATG__NAGIOS_SERVICE_DEP_SERVICE',
            'hostdep'                            => 'LC__CATG__NAGIOS_SERVICE_DEP__HOST',
            'servicedep'                         => 'LC__CATG__NAGIOS_SERVICE_DEP__SERVICE_DEPENDENCY',
            'host'                               => 'LC__CATG__NAGIOS_SERVICE_DEP__HOST_DEPENDENCY',
            isys_component_list::CL__DISABLE_ROW => false,
        ];
    } // function
} // class