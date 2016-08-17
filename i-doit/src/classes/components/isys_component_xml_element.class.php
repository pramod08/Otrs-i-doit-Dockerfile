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
 * @subpackage Components_XML
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */

/**
 * elements are atomic xml object which can be parts of nodes
 * elements are represented by their names
 * elements may have one value
 * elements may have multiple atrributes
 * example: <element id="1">value</element>
 */
class isys_component_xml_element extends isys_component_xml_object
{

    private $arrAttribute;
    private $strName;
    private $strValue; // assoziatiat

    /**
     *
     * store attribute datapairs to the start tag of an element
     *
     * @param associative array $p_arrPara ("key"=>value)
     *                    example: array("id"=>"1"; "creator"=>"oliver")
     *                    result: <element id="1" creator="oliver">value of element<element>
     */
    function setAttribute($p_arrPara)
    {
        $this->arrAttribute = $p_arrPara;
    }

    /**
     * output the element data
     *
     * @return string
     */
    function get_object()
    {
        $l_strAttr = ""; // extract  attribute to string
        if ($this->strValue == ISYS_NULL || trim($this->strValue) == "") $this->strValue = "NULL";
        foreach ($this->arrAttribute as $key => $value)
        {
            $l_strAttr .= " " . $key . "=\"" . $value . "\" ";
        }

        return "<" . $this->strName . $l_strAttr . ">" . $this->strValue . "</" . $this->strName . ">";
    }

    function __construct($p_strName, $p_value, $p_arrAttribute = ISYS_NULL)
    {
        $this->strName  = $p_strName;
        $this->strValue = $p_value;
        //$this->arrAttribute = $p_arrAttribute;
        if ($p_arrAttribute != ISYS_NULL) $this->setAttribute($p_arrAttribute);
    }
}

?>