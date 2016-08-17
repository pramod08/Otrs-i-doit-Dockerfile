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
 * Logbook archiving handler
 *
 * @package    i-doit
 * @subpackage General
 * @author     Dennis Blümer <dbluemer@i.doit.org>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 *
 */

/**
 * Class isys_handler_archivelog
 */
class isys_handler_archivelog extends isys_handler
{

    /**
     * Logbook DAO
     *
     * @var isys_component_dao_logbook
     */
    private $m_daoLogbook;

    /**
     * @return bool
     */
    public function init()
    {
        global $g_comp_session, $g_comp_database;

        if ($g_comp_session->is_logged_in())
        {

            verbose("Setting up system environment");

            /* Get daos, because now we are logged in */
            $this->m_daoLogbook = new isys_component_dao_logbook($g_comp_database);

            verbose("Logbook archiving-handler initialized (" . date("Y-m-d H:i:s") . ")");

            /* Check status and add to logbook */
            try
            {
                $this->processArchiving();
            }
            catch (Exception $e)
            {
                verbose("");
            }
        }

        return true;
    }

    /**
     * @return bool
     * @throws Exception
     */
    private function processArchiving()
    {
        global $g_db_system;

        $l_settings = $this->m_daoLogbook->getArchivingSettings();

        if ($l_settings["dest"] == 0)
        {
            $l_db = isys_application::instance()->database;
            verbose("Using local database");
        }
        else
        {
            try
            {
                verbose("Using remote database on " . $l_settings["host"]);
                $l_db = isys_component_database::get_database(
                    $g_db_system["type"],
                    $l_settings["host"],
                    $l_settings["port"],
                    $l_settings["user"],
                    $l_settings["pass"],
                    $l_settings["db"]
                );
                verbose("Connection to " . $l_settings["host"] . " established");
            }
            catch (Exception $e)
            {
                throw new Exception("Logbook archiving: Failed to connect to " . $l_settings["host"]);
            }
        }

        verbose("Archiving");
        loading();

        $l_daoArchive = new isys_component_dao_archive($l_db);
        $l_arDate     = getdate(time() - $l_settings["interval"] * isys_convert::DAY);
        $l_date       = $l_arDate["year"] . "-" . $l_arDate["mon"] . "-" . $l_arDate["mday"];

        loading();

        try
        {
            $archivedRecords = $l_daoArchive->archive(
                $this->m_daoLogbook,
                $l_date,
                $l_settings["interval"],
                $l_settings["dest"] == 0
            );

            verbose('Archived records: ' . ($archivedRecords ? $archivedRecords : 0) . ' (Memory peak: ' . (memory_get_peak_usage(true) / 1024 / 1024) . ' mb)');
        }
        catch (Exception $e)
        {
            die($e->getMessage());
        }
        verbose("Archiving successful");

        return true;
    }
}