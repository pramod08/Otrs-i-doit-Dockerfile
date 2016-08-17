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
 * @subpackage  Components_XML
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_component_xml_commentary extends isys_component_xml_object
{
    /**
     * @var  string
     */
    private $strCommentary = null;

    /**
     * Output commentary.
     *
     * @return  string
     */
    function get_object()
    {
        return "<!--" . $this->strCommentary . "-->";
    } // function

    /**
     * Constructor.
     *
     * @param  string $p_strCommentary
     */
    function __construct($p_strCommentary)
    {
        $this->strCommentary = $p_strCommentary;
    } // function
} // class