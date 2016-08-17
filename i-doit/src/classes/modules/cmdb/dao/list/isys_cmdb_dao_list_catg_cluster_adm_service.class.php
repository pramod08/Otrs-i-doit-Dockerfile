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
 * DAO: Category list for cluster administration service.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_catg_cluster_adm_service extends isys_cmdb_dao_list
{
    /**
     * Return constant of category.
     *
     * @return  integer
     */
    public function get_category()
    {
        return C__CATG__CLUSTER_ADM_SERVICE;
    } // function

    /**
     * Return constant of category type.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    } // function

    /**
     * Method for retrieving the category-data.
     *
     * @param   mixed   $p_unused
     * @param   integer $p_objID
     * @param   integer $p_cRecStatus
     *
     * @return  isys_component_dao_result
     */
    public function get_result($p_unused, $p_objID, $p_cRecStatus = null)
    {
        $l_cRecStatus = empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus;

        $l_sql = "SELECT *
			FROM isys_catg_cluster_adm_service_list
			INNER JOIN isys_connection ON isys_catg_cluster_adm_service_list__isys_connection__id = isys_connection__id
			INNER JOIN isys_obj ON isys_connection__isys_obj__id = isys_obj__id
			INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id
			WHERE TRUE ";

        if (!empty($p_objID))
        {
            $l_sql .= "AND isys_catg_cluster_adm_service_list__isys_obj__id = " . $this->convert_sql_id($p_objID);
        } // if

        if (!empty($l_cRecStatus))
        {
            $l_sql .= " AND isys_catg_cluster_adm_service_list__status = " . $this->convert_sql_id($l_cRecStatus);
        } // if

        return $this->retrieve($l_sql);
    } // function

    /**
     * Gets flag for the rec status dialog.
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function rec_status_list_active()
    {
        return false;
    } // function

    /**
     * Method for retrieving the table fields.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            'isys_obj__title'      => 'LC__CMDB__CATG__CLUSTER_ADM_SERVICE_LIST__ADMINISTRATION_SERVICE',
            'isys_obj_type__title' => 'LC__CMDB__OBJTYPE',
        ];
    } // function

    /**
     * Probably unused method.
     *
     * @return  string
     */
    public function make_row_link()
    {
        // Return link pattern to administrative instance.
        return isys_helper_link::create_url(
            [
                C__CMDB__GET__OBJECT => '[{isys_obj__id}]',
                C__CMDB__GET__CATG   => C__CATG__GLOBAL,
            ]
        );
    } // function
} // class