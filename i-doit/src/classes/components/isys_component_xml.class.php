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
 * XML Interface
 *
 * Provides basic XML functionalities, this is a wrapper to PHPs internal XML Interpreter.
 *
 * @package     i-doit
 * @subpackage  Components_XML
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_component_xml extends isys_component
{
    /**
     * @var  resource
     */
    private $m_res_parser;

    /**
     * Reads and parses $p_filename, converting the resulting array to an array with a tree.
     *
     * @param   string $p_filename
     *
     * @return  array
     * @throws  isys_exception_xml
     */
    public function parse_file($p_filename)
    {
        if (file_exists($p_filename))
        {
            $l_contents = file_get_contents($p_filename);

            if (!empty($l_contents))
            {
                $l_xmlstruct = [];
                $l_xmlindex  = [];

                // Returns 0 if failed (invalid XML), 1 if succeeded.
                if (xml_parse_into_struct($this->m_res_parser, $l_contents, $l_xmlstruct, $l_xmlindex) == 1)
                {
                    // Start parsing.
                    $l_parsed = [];
                    $this->parse_internal($l_xmlstruct, $l_parsed);

                    return $l_parsed;
                }
                else
                {
                    throw new isys_exception_xml("XML Parser failed (" . $this->get_error() . ").", $this->get_error_code());
                } // if
            }
            else
            {
                throw new isys_exception_xml("File empty (" . $p_filename . ")", $this->get_error_code());
            } // if
        }
        else
        {
            throw new isys_exception_xml("File does not exist (" . $p_filename . ")", $this->get_error_code());
        } // if
    } // function

    /**
     * Return "simple_xml" parsed file.
     *
     * @param   string $p_file
     *
     * @return  SimpleXMLElement
     */
    public function simple_xml_parse($p_file)
    {
        return simplexml_load_file($p_file);
    } // function

    /**
     * Returns the last error code of the XML Parser.
     *
     * @return  integer
     */
    public function get_error_code()
    {
        if ($this->m_res_parser)
        {
            return xml_get_error_code($this->m_res_parser);
        } // if
    } // function

    /**
     * Returns the error string of the XML Parser.
     *
     * @return  string
     */
    public function get_error()
    {
        if ($this->m_res_parser)
        {
            return xml_error_string($this->get_error_code());
        } // if
    } // function

    /**
     * Destructor.
     */
    public function __destruct()
    {
        if ($this->m_res_parser)
        {
            xml_parser_free($this->m_res_parser);
        } // if
    } // function

    /**
     * Sets the current array key to the last field for use in the internal parser.
     *
     * @param   array $p_array
     *
     * @return  mixed
     */
    private function parse_internal_last(&$p_array)
    {
        if (!count($p_array))
        {
            return null;
        } // if

        end($p_array);

        return $p_array[key($p_array)];
    } // function

    /**
     * Iterates $p_values (which has to be an array generated by xml_parse_into_struct) and transform it into an array with the XML tree. The output is written to $p_out.
     *
     * @param   array $p_values
     * @param   array $p_out
     *
     * @return  void
     */
    private function parse_internal(&$p_values, &$p_out)
    {
        /*
         * Code to convert the XML output to a tree
         * From: http://de2.php.net/manual/de/function.xml-parse-into-struct.php
         * By:   gleber at mapnet dot pl
         */
        do
        {
            $l_curr = current($p_values);

            switch ($l_curr['type'])
            {
                case 'open':
                    if (isset($p_out[$l_curr['tag']]))
                    {
                        $l_tmp = $p_out[$l_curr['tag']];

                        if (!$l_tmp['__multi'])
                        {
                            $p_out[$l_curr['tag']] = [
                                '__multi' => true,
                                $l_tmp
                            ];
                        } // if

                        array_push($p_out[$l_curr['tag']], []);
                        $l_new =& $this->parse_internal_last($p_dom[$l_curr['tag']]);
                    }
                    else
                    {
                        $p_out[$l_curr['tag']] = [];
                        $l_new                 =& $p_out[$l_curr['tag']];
                    } // if

                    next($p_values);
                    $this->parse_internal($p_values, $l_new);
                    break;

                case 'complete':
                    if (!isset($p_out[$l_curr['tag']]))
                    {
                        $p_out[$l_curr['tag']] = $l_curr['value'];
                    }
                    else
                    {
                        if (is_array($p_out[$l_curr['tag']]))
                        {
                            array_push($p_out[$l_curr['tag']], $l_curr['value']);
                        }
                        else
                        {
                            array_push($p_out[$l_curr['tag']] = [$p_out[$l_curr['tag']]], $l_curr['value']);
                        } // if
                    } // if
                    break;

                default:
                case 'cdata':
                case 'close':
                    return;
            } // switch
        } while (next($p_values) !== false);
    } // function

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->m_res_parser = xml_parser_create();
    } // function
} // class