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
namespace idoit\Module\Report\Worker;

use idoit\Module\Report\Protocol\Worker;
use League\Csv\Writer;

/**
 * Report CSV Export
 *
 * @package     idoit\Module\Report\Export
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       1.7.1
 */
class CsvWorker extends FileExport implements Worker
{

    /**
     * @var Writer
     */
    private $csvWriter;

    /**
     * @var int
     */
    private $index = 0;

    /**
     * @param array $row
     */
    public function work(array $row)
    {
        if ($this->index === 0)
        {
            $this->csvWriter->insertOne(array_keys($row));
            $this->index++;
        }

        $this->csvWriter->insertOne(array_values($row));
    }

    /**
     * Send Csv data to browser
     *
     * @param string $filename
     *
     * @return void
     */
    public function output($filename = null)
    {
        $this->csvWriter->output($filename);
    }

    /**
     * Return Csv Data
     *
     * @return string
     */
    public function export()
    {
        return $this->csvWriter->__toString();
    }

    /**
     * Csv constructor.
     *
     * @param Writer|null $csvWriter
     */
    public function __construct(Writer $csvWriter = null)
    {
        $this->csvWriter = $csvWriter
            ->setDelimiter(\isys_tenantsettings::get('report.csv-export-delimiter', ';'));
    }

}