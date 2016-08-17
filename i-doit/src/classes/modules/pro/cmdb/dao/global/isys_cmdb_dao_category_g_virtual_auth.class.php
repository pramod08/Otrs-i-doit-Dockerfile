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
 * DAO: class for global category "auth".
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       1.1
 */
class isys_cmdb_dao_category_g_virtual_auth extends isys_cmdb_dao_category_g_virtual
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'virtual_auth';

    /**
     * Method for receiving all persons / persongroups including their paths, which imply the given object.
     *
     * @param   integer $p_obj_id
     *
     * @return  array
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function get_object_paths($p_obj_id)
    {
        $l_path = [];

        $l_auth_dao       = isys_auth_dao::instance($this->m_db);
        $l_location_dao   = isys_cmdb_dao_location::instance($this->m_db);
        $l_location_cache = [];

        $l_obj = $this->get_object_by_id($p_obj_id, true)
            ->get_row();

        // The condition is necessary, to retrieve all paths which match with this object ID.
        $l_condition = [
            'OBJ_ID/' . isys_auth::WILDCHAR,
            'OBJ_ID/' . $p_obj_id,
            'OBJ_IN_TYPE/' . isys_auth::WILDCHAR,
            'OBJ_IN_TYPE/' . $l_obj['isys_obj_type__const']
        ];

        // Retrieve all paths, which match the defined condition.
        $l_res = $l_auth_dao->get_paths(null, C__MODULE__CMDB, 'AND isys_auth__path IN ("' . implode('", "', $l_condition) . '")');

        if (count($l_res) > 0)
        {
            while ($l_row = $l_res->get_row())
            {
                $l_path[$l_row['isys_auth__isys_obj__id']][] = $l_row;
            } // while
        } // if

        // Retrieve all location paths.
        $l_res = $l_auth_dao->get_paths(null, C__MODULE__CMDB, 'AND isys_auth__path LIKE "LOCATION/%"');

        if (count($l_res) > 0)
        {
            while ($l_row = $l_res->get_row())
            {
                $l_param = (int) end(explode('/', $l_row['isys_auth__path']));

                if ($l_param == isys_auth::WILDCHAR || $l_param == $p_obj_id)
                {
                    $l_path[$l_row['isys_auth__isys_obj__id']][] = $l_row;
                    continue;
                } // if

                // Before we call location-paths, we already called before, we look inside our cache.
                if (array_key_exists($p_obj_id, $l_location_cache))
                {
                    if ($l_location_cache[$l_param] === true)
                    {
                        $l_path[$l_row['isys_auth__isys_obj__id']][] = $l_row;
                    } // if

                    continue;
                } // if

                $l_child_locations = $l_location_dao->get_child_locations_recursive($l_param);

                // This is used to find location paths, which inherit the given object ID.
                if (array_key_exists($p_obj_id, $l_child_locations))
                {
                    $l_location_cache[$l_param]                  = true;
                    $l_path[$l_row['isys_auth__isys_obj__id']][] = $l_row;
                }
                else
                {
                    $l_location_cache[$l_param] = false;
                } // if
            } // while
        } // if

        return $l_path;
    } // function
} // class
?>