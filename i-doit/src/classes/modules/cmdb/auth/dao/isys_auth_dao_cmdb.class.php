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
 * Auth: dao class for module cmdb
 *
 * @package     i-doit
 * @subpackage  dao
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_auth_dao_cmdb extends isys_auth_module_dao
{
    /**
     * Determines which cleanup method should be called
     *
     * @param null $p_method
     *
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    protected function cleanup($p_method = null)
    {
        switch ($p_method)
        {
            case 'obj_id':
            case 'location':
                $this->cleanup_default($p_method, 'isys_obj', 'isys_obj__id');
                break;
            case 'obj_type':
            case 'obj_in_type':
                $this->cleanup_default($p_method, 'isys_obj_type', 'isys_obj_type__const');
                break;
            case 'category':
                $this->cleanup_category();
                break;
            default:

                $this->cleanup_default('obj_id', 'isys_obj', 'isys_obj__id')
                    ->cleanup_default('location', 'isys_obj', 'isys_obj__id')
                    ->cleanup_default('obj_type', 'isys_obj_type', 'isys_obj_type__const')
                    ->cleanup_default('obj_in_type', 'isys_obj_type', 'isys_obj_type__const')
                    ->cleanup_category();

                break;
        } // switch
        return $this;
    } // function

    /**
     * Method for cleaning the auth paths for categories.
     *
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function cleanup_category()
    {
        // Prepare delete query
        $l_delete_query = 'DELETE FROM isys_auth WHERE isys_auth__id IN ';
        $l_delete_arr   = [];

        // Get paths
        $l_auth_query = 'SELECT isys_auth__id, isys_auth__path FROM isys_auth
			WHERE isys_auth__isys_module__id = ' . $this->convert_sql_id($this->m_module_id) . '
			AND isys_auth__path LIKE "CATEGORY/%";';

        try
        {
            $l_res = $this->retrieve($l_auth_query);

            if ($l_res->num_rows() > 0)
            {
                while ($l_row = $l_res->get_row())
                {
                    $l_path_arr = explode('/', $l_row['isys_auth__path']);
                    if ($l_path_arr[1] == isys_auth::WILDCHAR)
                    {
                        continue;
                    } // if

                    $l_category_const = $l_path_arr[1];
                    $l_auth_id        = $l_row['isys_auth__id'];

                    $l_check_query = 'SELECT isysgui_catg__id AS id, "g" AS type FROM isysgui_catg WHERE isysgui_catg__const = ' . $this->convert_sql_text($l_category_const) . '
						UNION
						SELECT isysgui_cats__id AS id, "s" AS type FROM isysgui_cats WHERE isysgui_cats__const = ' . $this->convert_sql_text($l_category_const) . '
						UNION
						SELECT isysgui_catg_custom__id AS id, "g" AS type FROM isysgui_catg_custom WHERE isysgui_catg_custom__const = ' . $this->convert_sql_text(
                            $l_category_const
                        );

                    $l_res_check = $this->retrieve($l_check_query);

                    if ($l_res_check->num_rows() == 0)
                    {
                        $l_delete_arr[] = $l_auth_id;
                    } // if
                } // while

                if (count($l_delete_arr) > 0)
                {
                    $l_delete_query = $l_delete_query . '(' . implode(',', $l_delete_arr) . ')';
                    $this->update($l_delete_query);
                    $this->apply_update();
                } // if
            } // if
        }
        catch (isys_exception_general $e)
        {
            throw new isys_exception_general($e->getMessage());
        } // try

        return $this;
    } // function
} // class