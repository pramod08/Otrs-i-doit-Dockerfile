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
class TxtExport extends CsvExport implements Exportable
{
    /**
     * TxtExport constructor.
     *
     * @param Report $report
     */
    public function __construct(Report $report)
    {
        parent::__construct($report);
        $this->writer->setDelimiter("\t");
    }
}