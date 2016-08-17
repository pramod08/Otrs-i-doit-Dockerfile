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
 * DAO: goup list
 *
 * @package     i-doit
 * @subpackage  Contact
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_contact_dao_group extends isys_contact_dao
{
    /**
     * Get PersonData by groupID.
     *
     * @param   integer $p_intGroupID
     *
     * @return  isys_component_dao_result
     */
    public function get_persons_by_id($p_intGroupID)
    {
        $l_strSQL = 'SELECT * FROM isys_obj
			INNER JOIN isys_person_2_group ON isys_person_2_group__isys_obj__id__person = isys_obj__id
			WHERE isys_person_2_group__isys_obj__id__group = ' . $this->convert_sql_id($p_intGroupID) . ';';

        return $this->retrieve($l_strSQL);
    } // function
} // class