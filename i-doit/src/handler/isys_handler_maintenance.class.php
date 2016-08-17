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
if (!defined("C__MAINT__NOT_DAYS"))
{
    define("C__MAINT__NOT_DAYS", "+0");
} // if

/**
 * i-doit
 *
 * Maintenance handler.
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_handler_maintenance extends isys_handler
{
    /**
     * @var mixed|string
     */
    private $m_description = "";

    /**
     * Match date
     *
     * @return boolean
     */
    public function check()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function parse()
    {
        global $g_comp_database, $g_loc;

        // Get all specific contracts.
        verbose("Parsing contract objects..");
        $l_cats_dao  = new isys_cmdb_dao_category_s_contract($g_comp_database);
        $l_contracts = $l_cats_dao->get_data(null, null, "AND (isys_obj__status != " . C__RECORD_STATUS__TEMPLATE . ")");

        if ($l_contracts->num_rows() > 0)
        {
            verbose(" Found " . C__COLOR__WHITE . $l_contracts->num_rows() . C__COLOR__NO_COLOR, false);

            while ($l_row = $l_contracts->get_row())
            {

                if (!empty($l_row["isys_cats_contract_list__end_date"]))
                {
                    $l_end = strtotime($l_row["isys_cats_contract_list__end_date"]);

                    /**
                     * @todo nachfragen
                     */
                    if (date("Ymd", strtotime(C__MAINT__NOT_DAYS . " days")) == date("Ymd", $l_end))
                    {

                        $l_title       = $l_cats_dao->get_obj_name_by_id_as_string($l_row["isys_cats_contract_list__isys_obj__id"]);
                        $l_description = $this->get_description_s($l_title, $l_row);

                        verbose(
                            "Timeout detected: " . C__COLOR__YELLOW . $l_title . C__COLOR__NO_COLOR . " / " . C__COLOR__YELLOW . $g_loc->fmt_date(
                                $l_row["isys_cats_contract_list__end_date"]
                            ) . C__COLOR__NO_COLOR . " - Notificating..."
                        );

                        loading();

                        $l_title = sprintf($this->get_title(), $l_title);
                        if ($this->create_task($l_title, $l_description, $l_row["isys_cats_contract_list__isys_obj__id"]))
                        {
                            verbose(C__COLOR__LIGHT_GREEN . "done" . C__COLOR__NO_COLOR, false);
                        }
                        else verbose(C__COLOR__LIGHT_RED . "failed" . C__COLOR__NO_COLOR, false);
                    }
                }

            }
        }
        else
        {
            verbose(C__COLOR__LIGHT_RED . "Nothing found" . C__COLOR__NO_COLOR, false);
        }

        /* Get all global contracts */
        verbose("Parsing regular objects with sub-contracts..");
        $l_catg_dao  = new isys_cmdb_dao_category_g_contract_assignment($g_comp_database);
        $l_contracts = $l_catg_dao->get_data(null, null, "AND (isys_obj__status != " . C__RECORD_STATUS__TEMPLATE . ")");

        if ($l_contracts->num_rows() > 0)
        {
            verbose(" Found " . C__COLOR__WHITE . $l_contracts->num_rows() . C__COLOR__NO_COLOR, false);

            while ($l_row = $l_contracts->get_row())
            {

                if (!empty($l_row["isys_catg_contract_assignment_list__contract_end "]) || !empty($l_row["isys_cats_contract_list__end_date"]))
                {
                    $l_end = (!empty($l_row["isys_catg_contract_assignment_list__contract_end"])) ? $l_row["isys_catg_contract_assignment_list__contract_end"] : $l_row["isys_cats_contract_list__end_date"];
                    $l_end = strtotime($l_end);

                    if (date("Ymd", strtotime(C__MAINT__NOT_DAYS . " days")) == date("Ymd", $l_end))
                    {

                        $l_title = $l_catg_dao->get_obj_name_by_id_as_string(
                                $l_row["isys_catg_contract_assignment_list__isys_obj__id"]
                            ) . " (" . $l_row["isys_catg_contract_assignment_list__title"] . ")";

                        $l_description = $this->get_description_g($l_title, $l_row);

                        verbose(
                            "Timeout detected: " . C__COLOR__LIGHT_CYAN . $l_title . C__COLOR__NO_COLOR . " / " . C__COLOR__LIGHT_CYAN . $g_loc->fmt_date(
                                $l_end
                            ) . C__COLOR__NO_COLOR . " - Notificating..."
                        );
                        loading();

                        $l_title = sprintf($this->get_title(), $l_title);
                        if ($this->create_task($l_title, $l_description, $l_row["isys_catg_contract_assignment_list__isys_obj__id"]))
                        {
                            verbose(C__COLOR__LIGHT_GREEN . "done" . C__COLOR__NO_COLOR, false);
                        }
                        else verbose(C__COLOR__LIGHT_RED . "failed" . C__COLOR__NO_COLOR, false);

                    }
                }

            }
        }
        else
        {
            verbose(C__COLOR__LIGHT_RED . "Nothing found" . C__COLOR__NO_COLOR, false);
        }

        return true;
    }

    /**
     * @return string
     */
    protected function get_title()
    {
        return "Maintenance timeout: %s";
    }

    /**
     * @return bool
     */
    public function init()
    {
        global $g_comp_session;

        if ($g_comp_session->is_logged_in())
        {
            verbose("Maintenance-Handler initialized (" . date("Y-m-d H:i:s") . ")");
            verbose("Starting parser..");

            // Start parser
            return $this->parse();
        } // if

        return false;
    }

    /**
     * @param $p_title
     * @param $p_row
     *
     * @return string
     */
    private function get_description_s($p_title, $p_row)
    {
        global $g_loc;

        $l_start = $g_loc->fmt_date($p_row["isys_cats_contract_list__start_date"]);
        $l_end   = $g_loc->fmt_date($p_row["isys_cats_contract_list__end_date"]);

        $l_params = [
            C__CMDB__GET__OBJECT   => $p_row["isys_cats_contract_list__isys_obj__id"],
            C__CMDB__GET__CATS     => C__CATS__CONTRACT,
            C__CMDB__GET__TREEMODE => C__CMDB__VIEW__TREE_OBJECT,
            C__CMDB__GET__VIEWMODE => C__CMDB__VIEW__CATEGORY_GLOBAL,
            C__CMDB__GET__CATLEVEL => $p_row["isys_cats_contract_list__id"]

        ];

        $l_str_link = '<a href="' . isys_helper_link::create_url($l_params) . '" target="_new">' . $p_title . '</a>';

        return sprintf(
            $this->m_description,
            $l_str_link,
            $l_start,
            $l_end,
            $p_row["isys_cats_contract_list__support_url"],
            $p_row["isys_cats_contract_list__contract_no"],
            $p_row["isys_cats_contract_list__customer_no"]
        );
    }

    /**
     * @param $p_title
     * @param $p_row
     *
     * @return string
     */
    private function get_description_g($p_title, $p_row)
    {
        global $g_loc;

        $l_start = $g_loc->fmt_date($p_row["isys_catg_contract_assignment_list__contract_start"]);
        $l_end   = $g_loc->fmt_date($p_row["isys_catg_contract_assignment_list__contract_end"]);

        $l_params = [
            C__CMDB__GET__OBJECT   => $p_row["isys_catg_contract_assignment_list__isys_obj__id"],
            C__CMDB__GET__CATG     => C__CATG__CONTRACT_ASSIGNMENT,
            C__CMDB__GET__TREEMODE => C__CMDB__VIEW__TREE_OBJECT,
            C__CMDB__GET__VIEWMODE => C__CMDB__VIEW__CATEGORY_GLOBAL,
            C__CMDB__GET__CATLEVEL => $p_row["isys_catg_contract_assignment_list__id"]

        ];

        $l_str_link    = '<a href="' . isys_helper_link::create_url($l_params) . '" target="_new">' . $p_title . '</a>';
        $l_empty_value = isys_tenantsettings::get('gui.empty_value', '-');

        return sprintf(
            $this->m_description,
            $l_str_link,
            $l_start,
            $l_end,
            $l_empty_value,
            $l_empty_value,
            $l_empty_value
        );
    } // function

    /**
     * isys_handler_maintenance constructor.
     */
    public function __construct()
    {
        $this->m_month = date("m");
        $this->m_day   = date("d");
        $this->m_year  = date("Y");

        $this->m_description = C__WORKFLOW_MSG__MAINTENANCE;
    } // function
} // class