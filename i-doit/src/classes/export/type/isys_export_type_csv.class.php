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
 * @subpackage  Export
 * @author      Dennis Stuecken <dstuecken@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_export_type_csv extends isys_export_type
{
    /**
     * @var  string
     */
    protected $m_extension = "csv";

    /**
     * @var  string
     */
    private $m_max_line = "";

    /**
     * Parses an array and returns a copy of $this.
     *
     * @param   array  $p_array
     * @param   string $p_export_format
     *
     * @throws  isys_exception_general
     * @return  string
     * @todo    FELDTRENNER ÜBER GUI SETZEN
     */
    public function parse($p_array, $p_export_format = null)
    {
        if (is_array($p_array))
        {
            $l_string = '';
            $this->set_max_line($p_array);

            foreach ($p_array as $l_column)
            {
                for ($l_counter = 0;$l_counter <= $this->m_max_line;$l_counter++)
                {
                    $l_string .= $l_column[$l_counter] . ";";
                } // for

                $l_string .= "\n";
            } // foreach

            $this->set_formatted_export($l_string);

            return $this;
        }
        else
        {
            throw new isys_exception_general("Input not an array. (isys_export_type_csv->parse())");
        } // if
    } // function

    /**
     * Sets max columns per line
     *
     * @param array $p_array
     */
    private function set_max_line($p_array)
    {
        for ($l_i = 0;$l_i < 5;$l_i++)
        {
            if ($this->m_max_line < count($p_array[$l_i]))
            {
                $this->m_max_line = count($p_array[$l_i]);
            } // if
        } // for
    } // function

    /**
     * Constructor.
     *
     * @param  string $p_encoding
     */
    public function __construct($p_encoding = null)
    {
        if (!is_null($p_encoding))
        {
            $this->m_encoding = $p_encoding;
        } // if
    } // function
} // class