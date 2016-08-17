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
 * CMDB Specific category PDU Branch
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stuecken <dsteucken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_s_pdu_overview extends isys_cmdb_ui_category_specific
{
    /**
     * @param  isys_cmdb_dao_category_s_pdu_overview $p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $index_includes;
        global $g_comp_template;

        $l_receptables = $l_branch_array = [];

        // Retrieve PDU.
        $l_pdu_dao = new isys_cmdb_dao_category_s_pdu($p_cat->get_database_component());
        $l_data    = $l_pdu_dao->get_data(null, $_GET[C__CMDB__GET__OBJECT])
            ->__to_array();
        $l_snmp    = null;

        if ($l_data)
        {
            $l_pdu = $l_data["isys_cats_pdu_list__pdu_id"];
        } // if
        else $l_pdu = '';

        $l_checkSNMP = isys_tenantsettings::get('snmp.pdu.queries', false);

        // Initialize SNMP and Branch DAOs.
        $l_branch_dao = new isys_cmdb_dao_category_s_pdu_branch($p_cat->get_database_component());

        try
        {
            if ($l_checkSNMP)
            {
                $l_snmp = new isys_library_snmp(
                    $l_branch_dao->get_snmp_host($_GET[C__CMDB__GET__OBJECT]),
                    isys_cmdb_dao_category_g_snmp::instance($this->m_db)
                        ->get_community($_GET[C__CMDB__GET__OBJECT])
                );
            }

            $l_branches = $l_branch_dao->get_data(null, $_GET[C__CMDB__GET__OBJECT]);

            while ($l_row = $l_branches->get_row())
            {
                $l_branch_id = $l_row["isys_cats_pdu_branch_list__branch_id"];

                if ($l_row["isys_cats_pdu_branch_list__receptables"] > 0)
                {
                    for ($i = 1;$i <= $l_row["isys_cats_pdu_branch_list__receptables"];$i++)
                    {
                        $l_receptables[$i] = [
                            "title" => $l_checkSNMP ? $l_snmp->cleanup(
                                $l_snmp->{$l_branch_dao->format($l_branch_dao->get_snmp_path("receptableName"), $l_pdu, $l_branch_id, $i)}
                            ) : '',
                            "pwr"   => $l_checkSNMP ? $l_snmp->cleanup(
                                $l_snmp->{$l_branch_dao->format($l_branch_dao->get_snmp_path("lgpPduRcpEntryPwrOut"), $l_pdu, $l_branch_id, $i)}
                            ) : '',
                            "nrg"   => $l_checkSNMP ? $l_branch_dao->decimal_shift(
                                $l_snmp->cleanup($l_snmp->{$l_branch_dao->format($l_branch_dao->get_snmp_path("lgpPduRcpEntryEnergyAccum"), $l_pdu, $l_branch_id, $i)})
                            ) : ''
                        ];
                    } // for
                } // if
                else $i = 1;

                $l_branch_array[] = [
                    "row"         => $l_row,
                    "title"       => $l_checkSNMP ? $l_snmp->cleanup(
                        $l_snmp->{$l_branch_dao->format($l_branch_dao->get_snmp_path("branchTag"), $l_pdu, $l_branch_id, 0)}
                    ) : '',
                    "nrg"         => $l_checkSNMP ? $l_branch_dao->decimal_shift(
                        $l_snmp->cleanup($l_snmp->{$l_branch_dao->format($l_branch_dao->get_snmp_path("lgpPduRbEntryEnergyAccum"), $l_pdu, $l_branch_id, $i)})
                    ) : '',
                    "pwr"         => $l_checkSNMP ? $l_snmp->cleanup(
                        $l_snmp->{$l_branch_dao->format($l_branch_dao->get_snmp_path("lgpPduRbEntryPwrTotal"), $l_pdu, $l_branch_id, $i)}
                    ) : '',
                    "receptables" => $l_receptables
                ];
            } // while

            $g_comp_template->assign("branches", $l_branch_array);
        }
        catch (Exception $e)
        {
            isys_notify::warning($e->getMessage());
        } // try

        $this->deactivate_commentary();

        $g_comp_template->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1");

        isys_component_template_navbar::getInstance()
            ->set_active(false, C__NAVBAR_BUTTON__NEW)
            ->set_active(false, C__NAVBAR_BUTTON__EDIT)
            ->set_active(false, C__NAVBAR_BUTTON__PRINT);

        $index_includes["contentbottomcontent"] = $this->get_template();
    } // function

    /**
     * UI constructor.
     *
     * @param  isys_component_template $p_template
     */
    public function __construct(isys_component_template &$p_template)
    {
        parent::__construct($p_template);
        $this->set_template("cats__pdu_overview.tpl");
    } // function
} // class
?>