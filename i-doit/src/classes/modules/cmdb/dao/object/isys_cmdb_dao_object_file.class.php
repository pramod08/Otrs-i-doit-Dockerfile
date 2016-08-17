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
 * DAO: Object
 *
 * @author     Selcuk Kekec <skekec@i-doit.org>
 * @package    i-doit
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_cmdb_dao_object_file extends isys_cmdb_dao_object
{
    /**
     * Pre-Rank Method for removing uploaded files from filesystem whenever a file object is purged.
     *
     * @param   integer $p_objID
     * @param   integer $p_direction
     *
     * @author  Selcuk Kekec <skekec@i-doit.org>
     */
    public function pre_rank($p_objID, $p_direction)
    {
        if ($p_direction == C__CMDB__RANK__DIRECTION_DELETE)
        {
            $l_object_data = $this->get_object_by_id($p_objID)
                ->get_row();

            if (is_array($l_object_data) && ($l_object_data['isys_obj__status'] + 1) == C__RECORD_STATUS__PURGE)
            {
                $l_file_dao = new isys_cmdb_dao_category_s_file_version($this->get_database_component());
                $l_file_res = $l_file_dao->get_data(null, $p_objID);

                if (count($l_file_res) > 0)
                {
                    while ($l_row = $l_file_res->get_row())
                    {
                        $l_file_dao->delete_file($l_row['isys_file_physical__filename']);
                    } // while
                } // if
            } // if
        } // if
    } // function
} // class