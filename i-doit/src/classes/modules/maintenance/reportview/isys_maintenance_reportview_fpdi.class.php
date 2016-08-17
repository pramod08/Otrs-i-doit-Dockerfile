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
 * Maintenance FPDI class.
 *
 * @package     modules
 * @subpackage  maintenance
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.2
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.5.0
 */
class isys_maintenance_reportview_fpdi extends FPDI
{
    /**
     * This variable will hold the filepath to the header logo.
     *
     * @var  string
     */
    protected $m_logo_filepath = '';
    /**
     * This variable will hold the maintenance data.
     *
     * @var  array
     */
    protected $m_maintenance_data = [];
    /**
     * This variable will hold the translated months.
     *
     * @var  array
     */
    protected $m_months = [];
    /**
     * This variable will hold the object data.
     *
     * @var  array
     */
    protected $m_object_data = [];
    /**
     * This variable will hold the filename of the exported PDF.
     *
     * @var  string
     */
    protected $m_output_filename = '';

    /**
     * Method for returning the PDFs filename.
     *
     * @return  string
     */
    public function get_pdf_filename()
    {
        return $this->m_output_filename;
    } // function

    /**
     * Method for setting the logo filepath.
     *
     * @param   string $p_filepath
     *
     * @return  isys_maintenance_reportview_fpdi
     */
    public function set_logo_filepath($p_filepath)
    {
        $this->m_logo_filepath = $p_filepath;

        return $this;
    } // function

    /**
     * Method for setting the maintenance data.
     *
     * @param   array $p_maintenance_data
     *
     * @return  isys_maintenance_reportview_fpdi
     */
    public function set_maintenance_data(array $p_maintenance_data)
    {
        $this->m_maintenance_data = $p_maintenance_data;

        return $this;
    } // function

    /**
     * Method for setting the object data.
     *
     * @param   array $p_object_data
     *
     * @return  isys_maintenance_reportview_fpdi
     */
    public function set_object_data(array $p_object_data)
    {
        $this->m_object_data = $p_object_data;

        return $this;
    } // function

    /**
     * Method for rendering the first page (with the maintenance details).
     *
     * @return  isys_maintenance_reportview_fpdi
     */
    public function first_page()
    {
        return $this->AddPage()
            ->defaults();
    } // function

    /**
     * Render the colored table.
     *
     * @return  isys_maintenance_reportview_fpdi
     */
    public function fill_report()
    {
        global $g_loc;

        $l_empty_value = isys_tenantsettings::get('gui.empty_value', '-');
        $l_last_month  = $l_last_year = $i = 0;

        $this->SetFillColor(128)
            ->SetDrawColor(96)
            ->SetTextColor(255)
            ->Cell(15, 6, 'ID', 'B', 0, 'C', true)
            ->Cell(60, 6, _L('LC_UNIVERSAL__OBJECT'), 'B', 0, 'C', true)
            ->Cell(40, 6, _L('LC__MAINTENANCE__PLANNING__DATE_FROM'), 'B', 0, 'C', true)
            ->Cell(40, 6, _L('LC__MAINTENANCE__PDF__PERSON') . ' (' . _L('LC__MAINTENANCE__PDF__PERSON_ROLE') . ')', 'B', 0, 'C', true)
            ->Cell(35, 6, _L('LC__MAINTENANCE__PDF__FINISHED'), 'B', 1, 'C', true)
            ->SetFillColor(220);

        foreach ($this->m_maintenance_data as $l_maintenance_id => $l_maintenance_data)
        {
            $l_month = (int) date('n', strtotime($l_maintenance_data['isys_maintenance__date_from']));
            $l_year  = (int) date('Y', strtotime($l_maintenance_data['isys_maintenance__date_from']));

            if ($l_last_month != $l_month || $l_last_year != $l_year)
            {
                $this->SetFillColor(160)
                    ->SetTextColor(255)
                    ->Cell(0, 6, $this->m_months[$l_month] . ' ' . $l_year, 'TB', 1, 'C', true);
            } // if

            foreach ($l_maintenance_data['objects'] as $l_obj_id)
            {
                $l_obj_data = $this->m_object_data[$l_obj_id];

                $l_odd = !!($i % 2);

                $this->SetFillColor(($l_odd ? 255 : 230))
                    ->SetTextColor(0)
                    ->Cell(15, 6, $l_obj_data['isys_obj__id'], 'BT', 0, 'C', true)
                    ->Cell(60, 6, _L($l_obj_data['isys_obj_type__title']) . ' > ' . $l_obj_data['isys_obj__title'], 'BT', 0, '', true)
                    ->Cell(
                        40,
                        6,
                        $g_loc->fmt_date($l_maintenance_data['isys_maintenance__date_from']) . ' - ' . $g_loc->fmt_date($l_maintenance_data['isys_maintenance__date_to']),
                        'BT',
                        0,
                        'C',
                        true
                    )
                    ->Cell(40, 6, $l_obj_data['contact_role'], 'BT', 0, ($l_obj_data['contact_role'] == $l_empty_value ? 'C' : ''), true);

                if ($l_maintenance_data['isys_maintenance__finished'])
                {
                    $this->SetTextColor(0, 99, 0)
                        ->Cell(35, 6, $g_loc->fmt_datetime($l_maintenance_data['isys_maintenance__finished']), 'BT', 1, 'C', true);
                }
                else
                {
                    $this->SetTextColor(170, 0, 0)
                        ->Cell(35, 6, _L('LC__UNIVERSAL__NO'), 'BT', 1, 'C', true);
                } // if

                $i++;
            } // foreach

            $l_last_month = $l_month;
            $l_last_year  = $l_year;
        } // foreach

        return $this->SetTextColor(0);
    } // function

    /**
     * Method for saving the created PDF to the temp directory.
     *
     * @param  string $p_filename
     */
    public function save_to_temp($p_filename = null)
    {
        if ($p_filename === null)
        {
            $p_filename = date("Y-m-d_H-i-s") . '_idoit-maintenance-report.pdf';
        } // if

        $this->Output(BASE_DIR . 'temp' . DS . $p_filename, 'F');

        $this->m_output_filename = $p_filename;
    } // function

    /**
     * Overwriting the "AddPage" method for method chaining.
     *
     * @param   string $p_orientation
     * @param   string $p_size
     *
     * @return  isys_maintenance_reportview_fpdi
     */
    public function AddPage($p_orientation = '', $p_size = '')
    {
        parent::AddPage($p_orientation, $p_size);

        return $this;
    } // function

    /**
     * Overwriting the "Cell" method for method chaining.
     *
     * @param   integer $p_width
     * @param   integer $p_height
     * @param   string  $p_text
     * @param   integer $p_border
     * @param   integer $p_line
     * @param   string  $p_align
     * @param   boolean $p_fill
     * @param   string  $p_link
     *
     * @return  isys_maintenance_reportview_fpdi
     */
    public function Cell($p_width = 0, $p_height = 0, $p_text = '', $p_border = 0, $p_line = 0, $p_align = '', $p_fill = false, $p_link = '')
    {
        if (FPDF_VERSION == '1.7')
        {
            parent::Cell($p_width, $p_height, $p_text, $p_border, $p_line, $p_align, $p_fill, $p_link);
        }
        else
        {
            parent::Cell($p_width, $p_height, $p_text, $p_border, $p_line, $p_align, (int) $p_fill, $p_link);
        } // if

        return $this;
    } // function

    /**
     * Overwriting the "Footer" method.
     */
    public function Footer()
    {
        parent::Footer();

        // Position at 15mm from bottom.
        $this->SetY(-15);
        $this->SetFont('helvetica', '', 7);
        $this->Cell(0, 5, _L('LC__MAINTENANCE__PDF__PAGE') . ' ' . $this->PageNo() . ' / {:ptp:}', 0, 0, 'C');
    } // function

    /**
     * Overwriting the "Header" method.
     */
    public function Header()
    {
        parent::Header();

        // Create the logo (if selected)
        $l_logo_height = 0;

        if (!empty($this->m_logo_filepath) && file_exists($this->m_logo_filepath))
        {
            $this->Image($this->m_logo_filepath, 0, 0);

            $l_logo_height = ($this->images[$this->m_logo_filepath]['h'] / $this->k);
        } // if

        $this->SetTopMargin((int) ($l_logo_height + 5));
        // $this->SetY((int) ($l_logo_height + 5));
        $this->Ln(5);

        // Helvetica bold 15.
        $this->SetDrawColor(96)
            ->SetTextColor(96)
            ->SetFont('helvetica', 'b', 15);

        // Title.
        $this->Cell(0, 8, $this->title, 1, 2, 'C')
            ->defaults()
            ->Ln(5);

        $this->SetTopMargin((int) ($l_logo_height + 25));
    } // function

    /**
     * Overwriting the "SetDrawColor" method for method chaining.
     *
     * @param   integer $p_r
     * @param   integer $p_g
     * @param   integer $p_b
     *
     * @return  isys_maintenance_reportview_fpdi
     */
    public function SetDrawColor($p_r, $p_g = null, $p_b = null)
    {
        if (FPDF_VERSION != '1.7')
        {
            $p_g = ($p_g !== null) ? $p_g : -1;
            $p_b = ($p_b !== null) ? $p_b : -1;
        } // if

        parent::SetDrawColor($p_r, $p_g, $p_b);

        return $this;
    } // function

    /**
     * Overwriting the "SetFillColor" method for method chaining.
     *
     * @param   integer $p_r
     * @param   integer $p_g
     * @param   integer $p_b
     *
     * @return  isys_maintenance_reportview_fpdi
     */
    public function SetFillColor($p_r, $p_g = null, $p_b = null)
    {
        if (FPDF_VERSION != '1.7')
        {
            $p_g = ($p_g !== null) ? $p_g : -1;
            $p_b = ($p_b !== null) ? $p_b : -1;
        } // if

        parent::SetFillColor($p_r, $p_g, $p_b);

        return $this;
    } // function

    /**
     * Overwriting the "SetTextColor" method for method chaining.
     *
     * @param   integer $p_r
     * @param   integer $p_g
     * @param   integer $p_b
     *
     * @return  isys_maintenance_reportview_fpdi
     */
    public function SetTextColor($p_r, $p_g = null, $p_b = null)
    {
        if (FPDF_VERSION != '1.7')
        {
            $p_g = ($p_g !== null) ? $p_g : -1;
            $p_b = ($p_b !== null) ? $p_b : -1;
        } // if

        parent::SetTextColor($p_r, $p_g, $p_b);

        return $this;
    } // function

    /**
     * Overwriting the "SetTitle" method for method chaining.
     *
     * @param   string  $p_title
     * @param   boolean $p_is_utf8
     *
     * @return  isys_maintenance_reportview_fpdi
     */
    public function SetTitle($p_title, $p_is_utf8 = false)
    {
        parent::SetTitle($p_title, $p_is_utf8);

        return $this;
    } // function

    /**
     * Restores the default font.
     *
     * @return  isys_maintenance_reportview_fpdi
     */
    protected function defaults()
    {
        $this->SetDrawColor(0)
            ->SetTextColor(0)
            ->SetFillColor(255)
            ->SetFont('helvetica', '', 9);

        return $this;
    } // function

    /**
     * Create a new "isys_maintenance_reportview_fpdi" instance.
     *
     * @param  string $p_orientation
     * @param  string $p_unit
     * @param  string $p_format
     */
    public function __construct($p_orientation = 'P', $p_unit = 'mm', $p_format = 'A4')
    {
        parent::__construct($p_orientation, $p_unit, $p_format);

        $this->SetTitle(_L('LC__MAINTENANCE__PDF__TITLE'))
            ->defaults();

        $this->m_months = [
            1  => _L('LC__UNIVERSAL__CALENDAR__MONTHS_JANUARY'),
            2  => _L('LC__UNIVERSAL__CALENDAR__MONTHS_FEBRUARY'),
            3  => _L('LC__UNIVERSAL__CALENDAR__MONTHS_MARCH'),
            4  => _L('LC__UNIVERSAL__CALENDAR__MONTHS_APRIL'),
            5  => _L('LC__UNIVERSAL__CALENDAR__MONTHS_MAY'),
            6  => _L('LC__UNIVERSAL__CALENDAR__MONTHS_JUNE'),
            7  => _L('LC__UNIVERSAL__CALENDAR__MONTHS_JULY'),
            8  => _L('LC__UNIVERSAL__CALENDAR__MONTHS_AUGUST'),
            9  => _L('LC__UNIVERSAL__CALENDAR__MONTHS_SEPTEMBER'),
            10 => _L('LC__UNIVERSAL__CALENDAR__MONTHS_OCTOBER'),
            11 => _L('LC__UNIVERSAL__CALENDAR__MONTHS_NOVEMBER'),
            12 => _L('LC__UNIVERSAL__CALENDAR__MONTHS_DECEMBER')
        ];
    } // function
} // class