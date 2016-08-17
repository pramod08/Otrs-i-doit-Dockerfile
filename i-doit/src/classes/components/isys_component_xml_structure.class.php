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
 * Build xml structure using the  headerinformation main node and optional header information.
 *
 * @package     i-doit
 * @subpackage  Components_XML
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_component_xml_structure
{
    /**
     * @var  array
     */
    private $arrXmlHeaderAttribute;

    /**
     * @var  isys_component_xml_node
     */
    private $objMainNode;

    /**
     * @var  string
     */
    private $strEncoding = "UTF-8";

    /**
     * @param  $p_strConstEncoding
     */
    function set_encoding($p_strConstEncoding)
    {
        //$p_strConstEncoding
        $this->p_arrXmlHeaderAttribute['encoding'] = $p_strConstEncoding;
        $this->strEncoding                         = $this->p_arrXmlHeaderAttribute['encoding'];
    } // function

    /**
     * Encode the given parameter.
     *
     * @param   string $p_strValue
     *
     * @return  string
     */
    function encode($p_strValue)
    {
        $l_strReturn = "";

        switch ($this->strEncoding)
        {
            case "UTF-8":
                $l_strReturn = $p_strValue;

            default:
        } // switch

        return $l_strReturn;
    } // function

    /**
     * Each class which extends isys_component_xml_object has to override this methode.
     *
     * @return  string
     */
    function output()
    {
        $l_strHeader = "";

        foreach ($this->arrXmlHeaderAttribute as $l_key => $l_value)
        {
            $l_strHeader .= "$l_key=\"$l_value\" ";
        } // foreach

        return "<?xml " . $l_strHeader . "?>" . $this->encode($this->objMainNode->get_object());
    } // function

    /**
     * Contruct isys_component_xml_structure.
     *
     * @param  isys_component_xml_node $p_obj_node
     * @param  array                   $p_arrXmlHeaderAttribute
     */
    function __construct($p_obj_node, $p_arrXmlHeaderAttribute = [
        "version"  => "1.0",
        "encoding" => "UTF-8"
    ])
    {
        // start node
        $this->objMainNode = $p_obj_node;

        // header information
        $this->arrXmlHeaderAttribute = $p_arrXmlHeaderAttribute;
    } // function
} // class