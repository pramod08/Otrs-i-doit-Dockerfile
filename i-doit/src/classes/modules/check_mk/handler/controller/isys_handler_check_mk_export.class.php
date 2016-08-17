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
 * Export controller
 *
 * @package     Modules
 * @subpackage  Check_MK
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.4.0
 */
class isys_handler_check_mk_export extends isys_handler
{
    /**
     * Initialization method.
     *
     * @return  boolean
     */
    public function init()
    {
        global $g_comp_session, $argv;

        $l_export_language  = 0;
        $l_export_structure = \idoit\Module\Check_mk\Export::STRUCTURE_NONE;

        if (array_search('-l', $argv) !== false)
        {
            $l_export_language = (int) $argv[array_search('-l', $argv) + 1];
        }
        else
        {
            verbose("Use the '-l' parameter to export the configuration in a certain language:");
            verbose(" 0: All available");

            foreach (isys_glob_get_language_constants() as $l_id => $l_lang)
            {
                verbose(" " . $l_id . ": " . $l_lang);
            } // foreach

            verbose("");
        } // if

        if (array_search('-x', $argv) !== false)
        {
            $l_export_structure = (int) $argv[array_search('-x', $argv) + 1];
        }
        else
        {
            verbose("Add the '-x' parameter to export the configuration files in a certain directory pattern:");

            foreach (\idoit\Module\Check_mk\Export::getStructureOptions() as $l_id => $l_option)
            {
                verbose(" " . $l_id . ": " . $l_option);
            } // foreach

            verbose("");
        } // if

        if ($g_comp_session->is_logged_in())
        {
            verbose("Setting up system environment");

            $l_export = new \idoit\Module\Check_mk\Export(
                [
                    'export_structure' => (int) $l_export_structure,
                    'language'         => (int) $l_export_language
                ]
            );

            // Begin the export.
            $l_export->export();

            $l_logs = $l_export->getLogRecords();

            if (is_array($l_logs) && count($l_logs) > 0)
            {
                foreach ($l_logs as $l_log)
                {
                    switch ($l_log['level'])
                    {
                        case \Monolog\Logger::DEBUG:
                            $l_prefix = 'Debug: ';
                            break;

                        default:
                        case \Monolog\Logger::INFO:
                        case \Monolog\Logger::NOTICE:
                            $l_prefix = 'OK: ';
                            break;

                        case \Monolog\Logger::WARNING:
                        case \Monolog\Logger::ERROR:
                        case \Monolog\Logger::CRITICAL:
                        case \Monolog\Logger::ALERT:
                        case \Monolog\Logger::EMERGENCY:
                            $l_prefix = '!! ';
                            break;
                    } // switch

                    verbose($l_prefix . $l_log['message']);
                } // foreach
            } // if

            verbose('Done!');

            return true;
        } // if

        return false;
    } // function
} // class