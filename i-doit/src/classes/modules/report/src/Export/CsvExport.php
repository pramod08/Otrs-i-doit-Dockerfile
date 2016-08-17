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
namespace idoit\Module\Report\Export;

use idoit\Module\Report\Protocol\Exportable;
use idoit\Module\Report\Report;
use League\Csv\Writer;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

/**
 * i-doit
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class CsvExport extends Export implements Exportable
{
    /**
     * @var Writer
     */
    protected $writer;

    /**
     * @param string $filename
     *
     * @return $this
     */
    public function write($filename)
    {
        if (!file_exists(dirname($filename)))
        {
            throw new FileNotFoundException(sprintf('Error: Directory %s does not exist.', dirname($filename)));
        }

        if (!is_writeable(dirname($filename)))
        {
            throw new \Exception(sprintf('Error: The directory you are trying to write the csv file into is not writeable (%s)', dirname($filename)));
        }

        $fileHandle = fopen($filename, 'w+');
        fwrite($fileHandle, $this->writer);
        fclose($fileHandle);

        return $this;
    }

    /**
     * Output to browser
     *
     * @param string $filename
     *
     * @return void
     */
    public function output($filename = null)
    {
        $worker = $this->report->getWorker();

        if ($worker)
        {
            $this->writer->output('report-' . $this->report->getId() . '.csv');
        }
        else
        {
            throw new \Exception('Export was not processed correctly.');
        }
    }

    /**
     * CsvExport constructor.
     *
     * @param Report $report
     */
    public function __construct(Report $report)
    {
        /* Alternative writer instance:
        $this->filePath  = '/temp/' . md5(\isys_application::instance()->session->get_user_id() . microtime()) . '.csv';
        $this->csvWriter = Writer::createFromFileObject(
            new \SplFileObject(\isys_application::instance()->app_path . $this->filePath, 'w+')
        );
        */
        $this->writer = Writer::createFromFileObject(new \SplTempFileObject(0));

        $this->report = $report->setWorker(
            new \idoit\Module\Report\Worker\CsvWorker(
                $this->writer
            )
        );

        parent::__construct();
    }
}