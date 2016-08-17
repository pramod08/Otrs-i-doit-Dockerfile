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
 * Syslog handler
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Blümer <dbluemer@i.doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_handler_syslog extends isys_handler
{
    /**
     * IP DAO
     *
     * @var  isys_cmdb_dao_category_g_ip
     */
    private $m_dao_ip;
    /**
     * Logbook DAO
     *
     * @var  isys_event_manager
     */
    private $m_eventManager;

    public function init()
    {
        global $g_comp_session, $g_comp_database;

        if ($g_comp_session->is_logged_in())
        {
            verbose("Setting up system environment");

            // Get daos, because now we are logged in.
            $this->m_eventManager = isys_event_manager::getInstance();
            $this->m_dao_ip       = new isys_cmdb_dao_category_g_ip($g_comp_database);

            verbose("Syslog-Handler initialized (" . date("Y-m-d H:i:s") . ")");

            // Check log and add to logbook.
            try
            {
                $this->parseLog();
            }
            catch (Exception $e)
            {
                return false;
            } // try
        } // if

        return true;
    } // function

    private function parseLog()
    {
        global $g_userconf, $g_strSplitSyslogLine;

        verbose(count($g_userconf["logfiles"]) . " Logfiles found.");

        for ($i = 0;$i < count($g_userconf["logfiles"]);$i++)
        {
            if (!file_exists($g_userconf["logfiles"][$i]))
            {
                continue;
            } // if

            if (!is_readable($g_userconf["logfiles"][$i]))
            {
                verbose("CRON: syslog connector - cannot read syslog from file: \"" . $g_userconf["logfiles"][$i] . "\"");
                throw new Exception("CRON: syslog connector - cannot read syslog from file: \"" . $g_userconf["logfiles"][$i] . "\"");
            } // if

            $l_ambigiousIPs   = [];
            $l_noHost         = [];
            $l_ambigiousHosts = [];
            $l_noIPs          = [];

            $l_syslog = fopen($g_userconf["logfiles"][$i], "r");

            verbose("Processing " . $g_userconf["logfiles"][$i]);

            while (!feof($l_syslog))
            {
                $l_line = fgets($l_syslog, 4096);

                $l_parts = [];

                if (preg_match($g_strSplitSyslogLine, $l_line, $l_parts) == 0)
                {
                    continue;
                } // if

                $l_ip   = trim($l_parts[4]);
                $l_host = trim($l_parts[3]);

                if (isys_glob_is_valid_ip($l_host))
                {
                    $l_ip = $l_host;
                    unset($l_host);
                }

                if (!empty($l_host))
                {
                    if (array_search($l_host, $l_ambigiousHosts) !== false || array_search($l_host, $l_noHost) !== false)
                    {
                        continue;
                    } // if
                }
                elseif (!empty($l_ip))
                {
                    if (array_search($l_ip, $l_ambigiousIPs) !== false || array_search($l_ip, $l_noIPs) !== false)
                    {
                        continue;
                    } // if
                }
                else
                {
                    continue;
                } // if

                if (preg_match("/[\d{1,3}\.]{3}\d{1,3}/", $l_ip) == 0)
                {
                    $l_objIDs = $this->m_dao_ip->getObjIDsByHostName($l_host);
                    $l_key    = 'id';
                }
                else
                {
                    $l_objIDs = $this->m_dao_ip->getObjIDsByIP($l_ip);
                    $l_key    = 'isys_obj__id';
                } // if

                try
                {
                    if ($l_key == 'id')
                    {
                        if (count($l_objIDs) > 1)
                        {
                            $l_ambigiousHosts[] = $l_host;
                            throw new Exception($l_host . " is ambigious");
                        } // if

                        if (count($l_objIDs) < 1)
                        {
                            $l_noHost[] = $l_host;
                            throw new Exception($l_host . " has no object");
                        } // if
                    }
                    else
                    {
                        if (count($l_objIDs) > 1)
                        {
                            $l_ambigiousIPs[] = $l_ip;
                            throw new Exception($l_ip . " is ambigious");
                        } // if

                        if (count($l_objIDs) < 1)
                        {
                            $l_noIPs[] = $l_ip;
                            throw new Exception($l_ip . " has no object");
                        } // if
                    }

                    preg_match("/([a-zA-Z]{3})[ ]+([\d]+)[ ]+([\d]+:[\d]+:[\d]+)/", $l_parts[1], $l_dateParts);

                    $l_month = $this->parseMonth($l_dateParts[1]);

                    $l_date = date("Y") . "-" . $l_month . "-" . $l_dateParts[2] . " " . $l_dateParts[3];

                    $this->m_eventManager->triggerEvent(
                        "Syslog: " . $l_parts[5] . " " . $g_userconf["priorities"][$i],
                        $l_parts[6],
                        $l_date,
                        $g_userconf["alertlevels"][$i],
                        C__LOGBOOK_SOURCE__EXTERNAL,
                        $l_objIDs[0][$l_key]
                    );
                }
                catch (Exception $e)
                {
                    verbose($e->getMessage());

                    $this->m_eventManager->triggerEvent(
                        "Syslog: " . $e->getMessage(),
                        "Error processing " . $g_userconf["logfiles"][$i],
                        null,
                        C__LOGBOOK__ALERT_LEVEL__2,
                        C__LOGBOOK_SOURCE__EXTERNAL,
                        null
                    );
                } // try
            } // while

            fclose($l_syslog);
            unlink($g_userconf["logfiles"][$i]);
        } // for
    } // function

    /**
     * Return the number of the month by a given abbrevation.
     *
     * @param   string $p_month
     *
     * @return  string
     */
    private function parseMonth($p_month)
    {
        switch ($p_month)
        {
            case "Jan":
                return "01";
            case "Feb":
                return "02";
            case "Mar":
                return "03";
            case "Apr":
                return "04";
            case "May":
                return "05";
            case "Jun":
                return "06";
            case "Jul":
                return "07";
            case "Aug":
                return "08";
            case "Sep":
                return "09";
            case "Oct":
                return "10";
            case "Nov":
                return "11";
            case "Dec":
                return "12";
        } // switch

        return "";
    } // function
} // class
?>