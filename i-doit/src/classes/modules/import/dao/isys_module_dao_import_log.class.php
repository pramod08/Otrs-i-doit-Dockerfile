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
 * Import DAO
 *
 * @package    i-doit
 * @subpackage Modules
 * @author     Dennis Stücken <dstuecken@i-doit.org>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 *
 */
class isys_module_dao_import_log extends isys_module_dao
{
    /**
     * Import types
     *
     * @var array
     */
    protected $m_import_types = [];

    /**
     * get_data always retrieves the data of the main table of this module
     */
    public function get_data()
    {
        return $this->retrieve('SELECT * FROM isys_import LIMIT 1000;');
    }

    /**
     * Retrieve import type by constant
     *
     * @param string $p_const
     *
     * @return string
     */
    public function get_import_type_by_const($p_const)
    {
        if (!isset($this->m_import_types[$p_const]))
        {
            $l_sql = 'SELECT isys_import_type__id FROM isys_import_type WHERE isys_import_type__const = ' . $this->convert_sql_text($p_const);

            $l_res = $this->retrieve($l_sql);
            if ($l_res)
            {
                $this->m_import_types[$p_const] = $l_res->get_row_value('isys_import_type__id');
            }
        }

        return $this->m_import_types[$p_const] ?: null;
    } // function

    /**
     * Adds a new entry
     *
     * @param      $p_import_type
     * @param null $p_title
     * @param null $p_import_profile
     *
     * @return bool|int
     */
    public function add_import_entry($p_import_type, $p_title = null, $p_import_profile = null)
    {
        $l_insert = 'INSERT INTO isys_import (isys_import__title, isys_import__import_date, isys_import__isys_import_type__id, isys_import__isys_import_profile__id) ' . 'VALUES ' . '(' . $this->convert_sql_text(
                $p_title
            ) . ', NOW(), ' . $this->convert_sql_id($p_import_type) . ', ' . $this->convert_sql_id($p_import_profile) . ');';

        if ($this->update($l_insert) && $this->apply_update())
        {
            return $this->get_last_insert_id();
        } // if
        return false;
    } // function

}