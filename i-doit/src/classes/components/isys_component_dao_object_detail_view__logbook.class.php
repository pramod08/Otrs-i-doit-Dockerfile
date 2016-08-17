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
 * DAO for object detail template (logbook)
 *
 * @package    i-doit
 * @subpackage Components
 * @author     Niclas Potthast <npotthast@i-doit.de>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_component_dao_object_detail_view__logbook extends isys_component_dao_object_detail_view
{
    /**
     * @param integer $p_nUserID
     *
     * @return isys_component_dao_result
     */
    public function get_result($p_nMessageID)
    {
        $l_strSQL = "";

        $l_strSQL .= "SELECT " . "isys_logbook__id, " . "isys_logbook__title, " . "isys_logbook__date, " . "isys_logbook_level__title, " . "FROM isys_logbook " . "JOIN isys_logbook_level " . "ON isys_logbook__isys_logbook_level__id = " . "isys_logbook_level__id ";

        if ($p_nMessageID != 0)
        {
            $l_strSQL .= "WHERE isys_logbook__id = $p_nMessageID ";
        }

        $l_navPageCount = $this->retrieve($l_strSQL);
        $this->set_page_count($l_navPageCount->num_rows());

        if (strlen($this->strFilter) > 0)
        {
            if ($p_nMessageID != 0)
            {
                $l_strSQL .= "AND ";
            }
            $l_strSQL .= "WHERE " . "isys_logbook__title LIKE '%$this->strFilter%'" . " OR " . "isys_logbook__date LIKE '%$this->strFilter%'" . " OR " . "isys_logbook_level__title LIKE '%$this->strFilter%'";
        }

        if ($this->nStart >= 0 and $this->nLimit)
        {
            //limit query
            $l_strSQL .= " LIMIT $this->nStart,$this->nLimit ";
        }

        $l_strSQL .= ";";

        return $this->retrieve($l_strSQL);
    }
}

?>