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
 * Class for xml node nodes are containers which may contain:
 * a.) other nodes
 * b.) elements like <key id="4">value</key>
 * c.) commentaies like <!-- this is a commentary -->
 *
 * use the method addXmlObj to add one xml object of the list above to a given node
 *
 * @package     i-doit
 * @subpackage  Components_XML
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_component_xml_node extends isys_component_xml_object
{
    private $arrAttribute;
    private $arrObjValue;
    private $intCountValue;
    private $strName;

    /**
     * Name a node.
     *
     * @param  string $p_strPara
     */
    function setName($p_strPara)
    {
        $this->strName = $p_strPara;
    } // function

    /**
     * Add other xml_object to the given node.
     *
     * @param  mixed $p_objPara
     */
    function addXmlObj($p_objPara)
    {
        $this->arrObjValue[$this->intCountValue] = $p_objPara;
        $this->intCountValue += 1;
    } // function

    /**
     * Store attribute datapairs to a node.
     *
     * @param  array $p_arrPara
     */
    function setAttribute($p_arrPara)
    {
        $this->arrAttribute = $p_arrPara;
    } // function

    /**
     * Output of node data.
     *
     * @return  string
     */
    function get_object()
    {
        $l_value = "";

        for ($l_i = 0;$l_i < $this->intCountValue;$l_i++)
        {
            $l_strAttr = ""; // string for attribute
            foreach ($this->arrAttribute as $key => $value)
            {
                $l_strAttr .= " " . $key . "=\"" . $value . "\" ";
            } // foreach

            $l_value .= $this->arrObjValue[$l_i]->get_object();
        } // for

        return "<" . $this->strName . $l_strAttr . ">" . $l_value . "</" . $this->strName . ">";
    } // function

    /**
     * Constructor.
     *
     * @param  string $p_strName
     */
    function __construct($p_strName = null)
    {
        if ($p_strName != null)
        {
            $this->setName($p_strName);
        } // if

        // a counter for each added xml object
        $this->intCountValue = 0;
        $this->strAttribute  = [];
    } // function
} // class