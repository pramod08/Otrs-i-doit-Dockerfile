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
 * @package    i-doit
 * @subpackage Export
 * @author     Dennis Stücken <dstuecken@i-doit.org>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
abstract class isys_export_type
{

    /**
     * Export encoding
     *
     * @var string
     */
    protected $m_encoding = "utf-8";
    /**
     * Holds the export in array structure
     *
     * @var array
     */
    protected $m_export = [];
    /**
     * Export in Text Format (XML/CSV/..)
     *
     * @var string
     */
    protected $m_export_formatted = "";
    /**
     * File extension
     *
     * @var string
     */
    protected $m_extension = "txt";

    abstract public function parse($p_array);

    /**
     * Returns the file extension
     */
    public function get_extension()
    {
        return $this->m_extension;
    }

    /**
     * Returns the Export in Text Format
     *
     * @return string
     */
    public function get_export()
    {
        return $this->m_export_formatted;
    }

    /**
     * Returns the unformatted export (array)
     *
     * @return array
     */
    public function get_unformatted_export()
    {
        return $this->m_export;
    }

    /* <abstract methods> */

    /**
     * Set formatted export
     *
     * @param string $p_string
     */
    protected function set_formatted_export($p_string)
    {
        $this->m_export_formatted = $p_string;
    }    // Parses ARRAY and formats into XML/CSV/Whatever

    /* </abstract methods> */

    public function __construct()
    {

    }
}

?>