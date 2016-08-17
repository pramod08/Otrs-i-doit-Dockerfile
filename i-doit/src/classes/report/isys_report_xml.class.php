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
class isys_report_xml extends isys_report
{
    /**
     * Define the content-type.
     *
     * @var  string
     */
    private $m_contentType = "text/xml";

    /**
     * Define the file extension.
     *
     * @var  string
     */
    private $m_fileExtension = "xml";

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
        try
        {
            if (self::$m_as_download)
            {
                $this->output($this->toString());
            }
            else
            {
                $this->set_export_output($this->toString());
            } // if
        }
        catch (Exception $e)
        {
            throw new Exception($e->getMessage());
        } // try
    } // function

    /**
     * Transfers the export string to the client with the given content-type information.
     *
     * @param  string $p_data
     */
    protected function output($p_data)
    {
        // Strip every "not-word" character.
        $l_title = strtolower(preg_replace("/\W+/", "_", $this->getTitle()));

        header("Content-Type: " . $this->m_contentType);
        header("Expires: " . date("D, d M Y H:i:s") . " GMT");
        header("Content-Disposition: attachment; filename=" . date("ymd") . "-idoit-report-" . $l_title . "." . $this->m_fileExtension);
        header("Pragma: no-cache");

        echo $p_data;
        die;
    } // function

    /**
     * Returns the report as an xml-String.
     *
     * @return String The report query result in xml format
     * @throws Exception
     */
    protected function toString()
    {
        global $g_comp_template_language_manager;

        $l_report = $this->query(true, false);

        $l_xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
        $l_xml .= "<report>\n" . "   <title>\n" . "      " . $this->getTitle() . "\n" . "   </title>\n" . "   <description>\n" . "      " . $this->getDescription(
            ) . "\n" . "   </description>\n";

        if (is_array($l_report["headers"]))
        {
            $l_xml .= "   <headers>\n";

            foreach ($l_report["headers"] as $l_value)
            {
                $l_xml .= "      <header>\n";
                $l_xml .= "         " . $l_value . "\n";
                $l_xml .= "      </header>\n";
            } // foreach

            $l_xml .= "   </headers>\n";
        }
        else
        {
            throw new Exception("Error processing report headers");
        } // if

        if (is_array($l_report["content"]))
        {
            $l_xml .= "   <content>\n\n";

            foreach ($l_report["content"] as $l_data)
            {
                $l_xml .= "      <row>\n";

                if (is_array($l_data))
                {
                    foreach ($l_data as $l_table => $l_value)
                    {
                        // Replace <script>-Tags with content generated by QuickInfo-Tooltips.
                        $l_value = preg_replace('/<script[^>]*>[^<]*<[^>]script>/  ', '', $l_value);

                        // Remove other tags
                        $l_value = strip_tags(str_replace('"', '\'', $l_value));
                        if (!preg_match("/^__[\w]+__$/i", $l_table))
                        {
                            $l_xml .= "         <element>\n";
                            $l_xml .= "            " . $g_comp_template_language_manager->get($l_value) . "\n";
                            $l_xml .= "         </element>\n\n";
                        } // if
                    } // foreach
                } // if

                $l_xml .= "      </row>\n\n";
            } // foreach

            $l_xml .= "   </content>\n";
        } // if

        $l_xml .= "</report>";

        return $l_xml;
    } // function
} // class