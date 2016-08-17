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
 * i-doit Report Manager.
 *
 * @package     i-doit
 * @subpackage  Reports
 * @author      Dennis Bluemer <dbluemer@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_report_pdf extends isys_report
{
    /**
     * Define the content-type.
     *
     * @var  string
     */
    private $m_contentType = "application/pdf";

    /**
     * Define the file extension.
     *
     * @var  string
     */
    private $m_fileExtension = "pdf";

    /**
     * Content-type getter.
     *
     * @return  string
     */
    public function getContentType()
    {
        return $this->m_contentType;
    } // function

    /**
     * This method will export the report to the desired format.
     *
     * @throws  Exception
     */
    public function export()
    {
        $l_pdf = $this->toPDF();

        $l_title = strtolower(preg_replace("/\W+/", "_", $this->getTitle()));

        if (self::$m_as_download)
        {
            $l_pdf->output(date("ymd") . "-idoit-report-" . $l_title . "." . $this->m_fileExtension, "d");
            die;
        }
        else
        {
            $this->set_export_output($l_pdf);
        } // if
    } // function

    /**
     * Returns the report as an isys_report_fpdf-object.
     *
     * @return  isys_report_fpdf
     */
    private function toPDF()
    {
        // Query the report.
        $l_report = $this->query();

        // Create new PDF.
        return isys_report_export_fpdi::factory('L')
            ->initialize(
                [
                    'pdf.title'   => $this->getTitle(),
                    'pdf.subject' => $this->getDescription()
                ]
            )
            ->reportTable($l_report["headers"], $l_report["content"]);
    } // function
} // class