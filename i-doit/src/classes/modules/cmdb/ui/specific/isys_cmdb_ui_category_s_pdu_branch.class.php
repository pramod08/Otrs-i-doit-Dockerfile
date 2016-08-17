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
 * CMDB Specific category PDU Branch.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stuecken <dsteucken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_s_pdu_branch extends isys_cmdb_ui_category_specific
{
    /**
     * @param isys_cmdb_dao_category_s_pdu_branch $p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_catdata = $p_cat->get_general_data();

        $l_branch_id = $l_catdata["isys_cats_pdu_branch_list__branch_id"];

        $l_receptables = [];
        $l_pdu_dao     = new isys_cmdb_dao_category_s_pdu($p_cat->get_database_component());
        $l_data        = $l_pdu_dao->get_data(null, $_GET[C__CMDB__GET__OBJECT])
            ->__to_array();

        if ($l_data)
        {
            $l_pdu = $l_data["isys_cats_pdu_list__pdu_id"];
        }

        /**
         * --------------------------------------------------------------------------------------
         * Get SNMP stats for receptables
         * --------------------------------------------------------------------------------------
         */
        try
        {
            if (isys_tenantsettings::get('snmp.pdu.queries', false))
            {
                $l_receptables = [];
                $l_snmp        = new isys_library_snmp($p_cat->get_snmp_host($_GET[C__CMDB__GET__OBJECT]), $p_cat->get_snmp_community($_GET[C__CMDB__GET__OBJECT]));

                if ($l_catdata["isys_cats_pdu_branch_list__receptables"] > 0)
                {
                    for ($i = 1;$i <= $l_catdata["isys_cats_pdu_branch_list__receptables"];$i++)
                    {
                        $l_receptables[$i] = [
                            "title"   => $l_snmp->cleanup($l_snmp->{$p_cat->format($p_cat->get_snmp_path("receptableName"), $l_pdu, $l_branch_id, $i)}),
                            "pwr_out" => $l_snmp->cleanup($l_snmp->{$p_cat->format($p_cat->get_snmp_path("lgpPduRcpEntryPwrOut"), $l_pdu, $l_branch_id, $i)}),
                            "acc_nrg" => $p_cat->decimal_shift(
                                $l_snmp->cleanup($l_snmp->{$p_cat->format($p_cat->get_snmp_path("lgpPduRcpEntryEnergyAccum"), $l_pdu, $l_branch_id, $i)})
                            )
                        ];
                    } // for
                } // if

                // Retrieve branch title via SNMP.
                if ($l_branch_id > 0)
                {
                    $l_branch_title = $l_snmp->{$p_cat->format($p_cat->get_snmp_path("branchTag"), $l_pdu, $l_branch_id, 0)};

                    if ($l_branch_title !== false)
                    {
                        $this->get_template_component()
                            ->assign("branch_title", "(" . $l_snmp->cleanup($l_branch_title) . ")");
                    } // if
                } // if

                $l_rules["C__CMDB__CATS__PDU__ACC_ENERGY_BRANCH"]["p_strValue"] = $p_cat->decimal_shift(
                    $l_snmp->cleanup($l_snmp->{$p_cat->format($p_cat->get_snmp_path("lgpPduRbEntryEnergyAccum"), $l_pdu, $l_branch_id, $i)})
                );
                $l_rules["C__CMDB__CATS__PDU__ACC_POWER_BRANCH"]["p_strValue"]  = $l_snmp->cleanup(
                    $l_snmp->{$p_cat->format($p_cat->get_snmp_path("lgpPduRbEntryPwrTotal"), $l_pdu, $l_branch_id, $i)}
                );
            }
        }
        catch (Exception $e)
        {
            isys_notify::warning($e->getMessage());
        } // try

        // Receptables default.
        if (!$l_catdata["isys_cats_pdu_branch_list__receptables"])
        {
            $l_catdata["isys_cats_pdu_branch_list__receptables"] = 6;
        } // if

        // PDU default.
        if (!$l_catdata["isys_cats_pdu_branch_list__pdu_id"] && !$_POST["C__CMDB__CATS__PDU__PDU_ID"])
        {
            $l_catdata["isys_cats_pdu_branch_list__pdu_id"] = $p_cat->count_objects_by_type(C__OBJTYPE__PDU);
        }

        // Branch default.
        if (!$l_catdata["isys_cats_pdu_branch_list__branch_id"] && !$_POST["C__CMDB__CATS__PDU__BRANCH_ID"])
        {
            $l_catdata["isys_cats_pdu_branch_list__branch_id"] = ($p_cat->get_data(null, $_GET[C__CMDB__GET__OBJECT])
                    ->num_rows() + 1);
        } // if

        // Assign rules.
        $l_rules["C__CMDB__CATS__PDU__BRANCH_ID"]["p_strValue"]                                                       = $l_catdata["isys_cats_pdu_branch_list__branch_id"];
        $l_rules["C__CMDB__CATS__PDU__RECEPTABLES"]["p_strValue"]                                                     = $l_catdata["isys_cats_pdu_branch_list__receptables"];
        $l_rules["C__CMDB__CATS__PDU__PDU_ID"]["p_strValue"]                                                          = $l_catdata["isys_cats_pdu_branch_list__pdu_id"];
        $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $l_catdata["isys_cats_pdu_branch_list__description"];

        if ($_POST["C__CMDB__CATS__PDU__BRANCH_ID"])
        {
            $l_rules["C__CMDB__CATS__PDU__BRANCH_ID"]["p_strValue"]                                                       = $_POST["C__CMDB__CATS__PDU__BRANCH_ID"];
            $l_rules["C__CMDB__CATS__PDU__RECEPTABLES"]["p_strValue"]                                                     = $_POST["C__CMDB__CATS__PDU__RECEPTABLES"];
            $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id(
            )]["p_strValue"]                                                                                              = $_POST["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type(
            ) . $p_cat->get_category_id()];
        } // if

        $this->get_template_component()
            ->assign("receptables", $l_receptables)
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    } // function

    /**
     * Process list.
     *
     * @param   isys_cmdb_dao_category $p_cat
     *
     * @return  boolean
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function process_list(isys_cmdb_dao_category $p_cat)
    {
        $this->list_view("isys_cats_pdu_branch", $_GET[C__CMDB__GET__OBJECT], new isys_cmdb_dao_list_cats_pdu_branch($p_cat->get_database_component()));

        return true;
    } // function
} // class