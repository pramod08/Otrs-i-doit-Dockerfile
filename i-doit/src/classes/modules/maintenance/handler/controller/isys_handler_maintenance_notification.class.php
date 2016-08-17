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
 * Controller for writing notification e-mails, which have not already been sent.
 *
 * @package     Modules
 * @subpackage  Maintenance
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.6.0
 */
class isys_handler_maintenance_notification extends isys_handler
{
    /**
     * Maintenance DAO.
     *
     * @var  isys_maintenance_dao
     */
    private $m_dao;
    /**
     * This variable will hold the database component.
     *
     * @var  isys_component_database
     */
    private $m_db;
    /**
     * List of maintenances, that will be notified.
     *
     * @var  array
     */
    private $m_maintenances = [];

    /**
     * Initialization method.
     *
     * @global  isys_component_session  $g_comp_session
     * @global  isys_component_database $g_comp_database
     * @return  boolean
     */
    public function init()
    {
        global $g_comp_session, $g_comp_database;

        if ($g_comp_session->is_logged_in())
        {
            verbose("Setting up system environment");

            $this->m_db  = &$g_comp_database;
            $this->m_dao = new isys_maintenance_dao($this->m_db);

            try
            {
                $this->collect_todays_maintenances()
                    ->notify_maintenance_contacts();
            }
            catch (Exception $e)
            {
                verbose('An error occured: ' . $e->getMessage());

                return false;
            } // try

            verbose("All finished!");
        } // if

        return false;
    } // function

    /**
     * Method for collecting all of "todays" maintenances. Only maintenances which have not already been notified will be selected.
     *
     * @return $this
     */
    private function collect_todays_maintenances()
    {
        verbose('Looking for maintenances, that start today (' . date('d.m.Y') . ')...');
        $l_res = $this->m_dao->get_data(null, 'AND isys_maintenance__mail_dispatched IS NULL AND isys_maintenance__date_from = CURDATE()', true);
        verbose('Found a total of ' . count($l_res) . '!');

        if (count($l_res))
        {
            while ($l_maintenance_id = $l_res->get_row_value('isys_maintenance__id'))
            {
                $this->m_maintenances[] = $l_maintenance_id;
            } // while
        } // if

        return $this;
    } // function

    /**
     * Method for notifying all contacts of our collected maintenances.
     *
     * @return $this
     */
    private function notify_maintenance_contacts()
    {
        foreach ($this->m_maintenances as $l_maintenance)
        {
            try
            {
                $this->m_dao->send_maintenance_planning_mail($l_maintenance);
                verbose('Contacts of maintenance #' . $l_maintenance . ' have been notified!');
            }
            catch (Exception $e)
            {
                verbose('Maintenance #' . $l_maintenance . ' could not be notifiend, an error occured: ' . $e->getMessage());
            } // try
        } // foreach

        return $this;
    } // function
} // class