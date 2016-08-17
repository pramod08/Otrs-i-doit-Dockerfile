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
 * @package     i-doit
 * @subpackage  Modules
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.1
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_report_export_fpdi extends FPDI
{
    /**
     * PDF default font.
     *
     * @var  string
     */
    protected $m_defaultFont = 'helvetica';
    /**
     * Variable defines the default orientation: L: Landscape, P: Portrait.
     *
     * @var  string
     */
    protected $m_defaultPageOrientation = 'L';
    /**
     * Default page unit.
     *
     * @var  string
     */
    protected $m_defaultPageUnit = 'mm';

    public static function factory($p_orientation = 'P', $p_unit = 'mm', $p_format = 'A4', $p_unicode = true, $p_encoding = 'UTF-8', $p_diskcache = false, $p_pdfa = false)
    {
        return new self($p_orientation, $p_unit, $p_format, $p_unicode, $p_encoding, $p_diskcache, $p_pdfa);
    } // function

    /**
     * Draw footer
     */
    public function Footer()
    {
        $this->SetY(-15);

        $this->SetFont($this->m_defaultFont, 'I', 8);

        $this->writeHTML('{:pnp:} / {:ptp:}', true, false, true, false, 'C');
    } // function

    /**
     * Render header.
     */
    public function Header()
    {
        $this->SetFont($this->m_defaultFont, 'B', 15);

        $this->SetY(15);

        $this->Cell(0, 10, 'i-doit Report - ' . $this->title, 1, 2, 'C');
    } // function

    /**
     * Initialize formatter
     *
     * @param   array $p_options
     *
     * @return  $this
     */
    public function initialize($p_options)
    {
        // Page orientation
        $this->setPageOrientation($this->m_defaultPageOrientation);

        // Set default page unit
        $this->setPageUnit($this->m_defaultPageUnit);

        // Default margins
        $this->SetMargins(20, 30, 20, true);

        // Set PDF title
        if (isset($p_options['pdf.title']))
        {
            $this->SetTitle($p_options['pdf.title']);
        } // if

        // Set PDF subject
        if (isset($p_options['pdf.subject']))
        {
            $this->SetSubject($p_options['pdf.subject']);
        } // if

        $this->AddPage();

        return $this;
    } // function

    /**
     * Render the colored table.
     *
     * @param   array $p_header
     * @param   array $p_data
     *
     * @return  $this
     */
    public function reportTable($p_header, $p_data)
    {
        $this->SetFont($this->m_defaultFont, '', 10);

        $l_dom        = new DOMDocument('1.0', 'utf-8');
        $l_html_table = $l_dom->createElement('table');
        $l_html_table->setAttribute('style', 'border:2px solid #888;');
        $l_html_table->setAttribute('cellspacing', '0');
        $l_html_table->setAttribute('cellpadding', '0');

        // Create the table-header.
        $l_thead = $l_dom->createElement('thead');

        $l_tr = $l_dom->createElement('tr');

        foreach ($p_header as $l_head)
        {
            $l_th = $l_dom->createElement('th', isys_glob_htmlentities($l_head));
            $l_th->setAttribute('style', 'background-color:#ccc; text-align:center; font-weight:bold;');
            $l_tr->appendChild($l_th);
        } // foreach

        $l_thead->appendChild($l_tr);
        $l_html_table->appendChild($l_thead);

        $l_tbody = $l_dom->createElement('tbody');

        foreach ($p_data as $i => $l_row)
        {
            $l_tr = $l_dom->createElement('tr');

            foreach ($l_row as $l_key => $l_data)
            {
                if (substr($l_key, 0, 2) == '__' && substr($l_key, -2) == '__')
                {
                    continue;
                } // if

                $l_td = $l_dom->createElement('td', trim(isys_glob_htmlentities(_L(strip_tags(preg_replace('/<script[^>]*>[^<]*<[^>]script>/  ', '', $l_data))))));
                $l_td->setAttribute('style', 'background-color:#' . (($i % 2) ? 'eee' : 'fff') . ';');
                $l_tr->appendChild($l_td);
            } // foreach

            $l_tbody->appendChild($l_tr);
        } // foreach

        $l_html_table->appendChild($l_tbody);

        $l_dom->appendChild($l_html_table);

        // Write our DOM to the PDF.
        $this->writeHTML(trim($l_dom->saveHTML()));

        return $this;
    } // function
} // class