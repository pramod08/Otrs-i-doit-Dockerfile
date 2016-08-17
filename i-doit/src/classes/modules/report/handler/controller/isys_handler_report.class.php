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
 * Handler for exporting reports for the specified file type
 *
 * @package     i-doit
 * @subpackage  Handler
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */

class isys_handler_report extends isys_handler
{
    /**
     * @var string directory where to put the export
     */
    private $m_directory = null;
    /**
     * @var string export type
     */
    private $m_export_type = 'csv';
    /**
     * @var string file name of the export
     */
    private $m_file_name = null;
    /**
     * @var isys_module_report_pro
     */
    private $m_report_dao = null;
    /**
     * @var int report id
     */
    private $m_report_id = null;

    /**
     * Output of the usage of this controller
     *
     * @param bool $p_error
     */
    public function usage($p_error = true)
    {
        $l_error = ($p_error) ? "Wrong usage!\n\n" : "How to use this controller:\n\n";

        $l_error .= "\nExample: \n" . "./controller -u USERNAME -p PASSWORD -i TENANT_ID -m report -r REPORT_ID -d ABSOLUTE/PATH/FOR/EXPORT -f FILE_NAME -t csv\n\n";

        $l_error .= "Parameters: \n" . "-r:   ID of the report.\n" . "-d:   Path to export the report into.\n" . "-f:   Optional parameter for the file name. Default is the title of the report.\n" . "-t:   Optional parameter for the file type. Possible options are: csv, txt, pdf, xml. Default: csv\n" . "Example: /var/www/controller -u admin -p admin -i 1 -m report -r 1 -d /var/www/exports/ -f export_file -t csv";

        error($l_error);
    }

    public function init()
    {
        verbose("Setting up system environment");

        try
        {
            if ($this->process())
            {
                $this->create_export();
            }
            else
            {
                verbose('ERROR: Your i-doit is not licensed.');
            } // if
        }
        catch (Exception $e)
        {
            verbose($e->getMessage());
        } // try

        return true;
    } // function

    /**
     * Setting up the environment for this controller
     */
    private function process()
    {
        global $argv;

        if (is_array($argv))
        {
            $l_pos_dir = array_search('-h', $argv);
            if ($l_pos_dir !== false)
            {
                $this->usage(false);
            } // if

            $l_pos_dir = array_search('-r', $argv);
            if ($l_pos_dir !== false)
            {
                $this->m_report_id = $argv[(int) $l_pos_dir + 1];
            }
            else
            {
                $this->usage();
            } // if

            $l_pos_dir = array_search('-d', $argv);
            if ($l_pos_dir !== false)
            {
                $this->m_directory = $argv[(int) $l_pos_dir + 1];
                if (!is_dir($this->m_directory))
                {
                    verbose('Directory "' . $this->m_directory . '" does not exist.');
                    die;
                }
            }
            else
            {
                $this->usage();
            } // if

            $l_pos_dir = array_search('-f', $argv);
            if ($l_pos_dir !== false)
            {
                $this->m_file_name = $argv[(int) $l_pos_dir + 1];
            } // if

            $l_pos_dir = array_search('-t', $argv);
            if ($l_pos_dir !== false)
            {
                $this->m_export_type = $argv[(int) $l_pos_dir + 1];
            } // if
        }
        else
        {
            $this->usage();
        } // if

        if (defined("C__ENABLE__LICENCE") && C__ENABLE__LICENCE)
        {
            $this->m_report_dao = new isys_module_report_pro();

            return true;
        } // if
        return false;
    } // function

    /**
     * Creates the report export
     *
     * @throws Exception
     */
    private function create_export()
    {
        verbose('Creating Export.', true);
        try
        {
            $l_report_data     = $this->m_report_dao->get_dao()
                ->get_report($this->m_report_id);
            $l_report_new_data = [
                "report_id"   => $l_report_data["isys_report__id"],
                "type"        => $l_report_data["isys_report__type"],
                "title"       => $l_report_data["isys_report__title"],
                "description" => $l_report_data["isys_report__description"],
                "query"       => $l_report_data["isys_report__query"],
                "mandator"    => $l_report_data["isys_report__mandator"],
                "datetime"    => $l_report_data["isys_report__datetime"],
                "last_edited" => $l_report_data["isys_report__last_edited"]
            ];

            $report = new \idoit\Module\Report\Report(
                new isys_component_dao(isys_application::instance()->database),
                $l_report_data["isys_report__query"],
                $l_report_data["isys_report__title"],
                $l_report_data["isys_report__id"],
                $l_report_data["isys_report__type"]
            );

            switch ($this->m_export_type)
            {
                case 'pdf':
                    $l_report = new isys_report_pdf($l_report_new_data);
                    break;
                case 'xml':
                    $l_report = new isys_report_xml($l_report_new_data);
                    break;
                case 'txt':
                    \idoit\Module\Report\Export\TxtExport::factory($report)
                        ->export()
                        ->write($this->m_directory . DS . $this->m_file_name . '.' . $this->m_export_type);
                    die;

                    break;
                case 'csv':
                default:
                    \idoit\Module\Report\Export\CsvExport::factory($report)
                        ->export()
                        ->write($this->m_directory . DS . $this->m_file_name . '.' . $this->m_export_type);
                    die;

                    break;
            } // switch

            if (isset($l_report))
            {
                if ($this->m_file_name)
                {
                    $l_report->setTitle($this->m_file_name);
                }
                else
                {
                    $this->m_file_name = $l_report->getTitle();
                } // if

                $l_report::$m_as_download = false;

                $l_report->export();
                if ($this->m_export_type === 'pdf')
                {
                    $l_report->get_export_output()
                        ->Output($this->m_directory . DS . $this->m_file_name . ".pdf", "F");
                }
                else
                {
                    $l_file_handler = fopen($this->m_directory . DS . $this->m_file_name . '.' . $this->m_export_type, 'w+');
                    fwrite($l_file_handler, $l_report->get_export_output());
                    fclose($l_file_handler);
                } // if
            }

        }
        catch (Exception $e)
        {
            verbose('Error with the following message: ' . $e->getMessage());
        } // try
    } // function
} // class