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
 * DAO: Global category pdu branch.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Stuecken - 09-2010
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_cats_pdu_branch extends isys_cmdb_dao_list
{
    /**
     * Return constant of category.
     *
     * @return  integer
     */
    public function get_category()
    {
        return C__CATS__PDU_BRANCH;
    } // function

    /**
     * Return constant of category type.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_SPECIFIC;
    } // function

    /**
     *
     * @param   string  $p_table
     * @param   integer $p_object_id
     * @param   integer $p_cRecStatus
     *
     * @return  isys_component_dao_result
     */
    public function get_result($p_table = null, $p_object_id, $p_cRecStatus = null)
    {
        return isys_cmdb_dao_category_s_pdu_branch::instance($this->m_db)
            ->get_data(null, $p_object_id, "", null, (empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus));
    } // function

    /**
     * Row modification.
     *
     * @param  array &$p_arrRow
     */
    public function modify_row(&$p_arrRow)
    {
        try
        {
            $l_nrg = $l_pwr = $l_branch_title = '';
            /**
             * @var $l_dao isys_cmdb_dao_category_s_pdu_branch
             */
            $l_dao = isys_cmdb_dao_category_s_pdu_branch::instance($this->m_db);
            if (isys_tenantsettings::get('snmp.pdu.queries', false))
            {
                $l_snmp = new isys_library_snmp(
                    $l_dao->get_snmp_host($p_arrRow["isys_cats_pdu_list__isys_obj__id"]),
                    isys_cmdb_dao_category_g_snmp::instance($this->m_db)
                        ->get_community($p_arrRow["isys_cats_pdu_list__isys_obj__id"])
                );

                $l_nrg          = $l_snmp->cleanup(
                    $l_snmp->{$l_dao->format(
                        $l_dao->get_snmp_path("lgpPduRbEntryEnergyAccum"),
                        $p_arrRow["isys_cats_pdu_list__pdu_id"],
                        $p_arrRow["isys_cats_pdu_branch_list__branch_id"],
                        0
                    )}
                );
                $l_pwr          = $l_snmp->cleanup(
                    $l_snmp->{$l_dao->format(
                        $l_dao->get_snmp_path("lgpPduRbEntryPwrTotal"),
                        $p_arrRow["isys_cats_pdu_list__pdu_id"],
                        $p_arrRow["isys_cats_pdu_branch_list__branch_id"],
                        0
                    )}
                );
                $l_branch_title = $l_snmp->{$l_dao->format(
                    $l_dao->get_snmp_path("branchTag"),
                    $p_arrRow["isys_cats_pdu_branch_list__title"],
                    $p_arrRow["isys_cats_pdu_list__pdu_id"],
                    $p_arrRow["isys_cats_pdu_branch_list__branch_id"],
                    0
                )};

                if ($l_branch_title !== false)
                {
                    $p_arrRow["isys_cats_pdu_branch_list__branch_id"] .= " (" . $l_snmp->cleanup($l_branch_title) . ")";
                } // if
            }

            if (!$l_nrg)
            {
                $l_nrg = "n/a";
            }
            else
            {
                $l_nrg = $l_dao->decimal_shift($l_nrg);
            } // if

            $p_arrRow["nrg"] = $l_nrg . " kWh";

            if (!$l_pwr)
            {
                $l_pwr = "n/a";
            } // if

            $p_arrRow["pwr"] = $l_pwr . " Watt";
        }
        catch (Exception $e)
        {
            // Do nothing.
        } // try
    } // function

    /**
     * @return  array
     */
    public function get_fields()
    {
        $l_fields = [
            "isys_cats_pdu_branch_list__branch_id"   => "Branch-ID",
            "isys_cats_pdu_branch_list__receptables" => "Receptables"

        ];

        if (isys_tenantsettings::get('snmp.pdu.queries', false))
        {
            $l_fields['pwr'] = 'LC__CMDB__CATS__PDU__CURRENT_POWER_OUT';
            $l_fields['nrg'] = 'LC__CMDB__CATS__PDU__ACCUMULATED_ENERGY';
        } // if

        return $l_fields;
    } // function
} // class