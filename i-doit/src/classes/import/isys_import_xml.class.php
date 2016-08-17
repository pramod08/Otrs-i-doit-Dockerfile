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
 * Handler for XML imports
 *
 * @package    i-doit
 * @subpackage Import-Handlers
 * @author     Dennis Stücken <dstuecken@i-doit.org>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_import_xml extends isys_import
{

    /**
     * Holds the XML data as array
     *
     * @var array
     */
    private $m_xml_data = [];

    /**
     * Holds XML data as SimpleXMLElement object
     *
     * @var SimpleXMLElement
     */
    private $m_xml_object = null;

    /**
     * Sets XML data as array.
     *
     * @param array $p_xml_data
     */
    public function set_xml_data($p_xml_data)
    {
        $this->m_xml_data = $p_xml_data;
    }

    /**
     * Gets XML data as array.
     *
     * @return array
     */
    public function get_xml_data()
    {
        return $this->m_xml_data;
    }

    /**
     * Sets XML data as SimpleXMLElement object.
     *
     * @param SimpleXMLElement $p_object
     */
    public function set_xml_object($p_object)
    {
        $this->m_xml_object = $p_object;
    }

    /**
     * Gets XML data as SimpleXMLElement object.
     *
     * @return SimpleXMLElement
     */
    public function get_xml_object()
    {
        return $this->m_xml_object;
    }

    /**
     * Formats SimpleXMLElement to array.
     *
     * @param array  $p_result
     * @param string $p_root
     * @param string $p_rootname (optional) Root name. Defaults to 'computer'.
     */
    public function to_array(&$p_result, &$p_root, $p_rootname = 'computer')
    {
        $n = count($p_root->children());

        if ($n > 0)
        {
            foreach ($p_root->children() as $l_child)
            {
                $l_name = $l_child->getName();

                if (count($l_child) == 0)
                {
                    $p_result[$p_rootname][$l_name] = (string) $l_child;
                }
                else
                {

                    $this->to_array($p_result[$p_rootname][], $l_child, $l_name);
                }

            }
        }
        else
        {
            if (count($p_root) > 0)
            {
                $p_result[$p_rootname] = (array) $p_root;
            }
            else
            {
                $p_result[$p_rootname] = (string) $p_root;
            }

        }

    }

    /**
     * Loads, parses and formats the given xml file.
     *
     * @deprecated Use load_xml_[data|file]() instead.
     *
     * @param string $p_file
     *
     * @return boolean Success?
     */
    public function load_import($p_file)
    {
        isys_import_log::add('Loading import "' . $p_file . '"');
        $l_xml_data = [];
        try
        {
            $l_filedata         = str_replace('<value></value>', '', isys_glob_utf8_encode(file_get_contents($p_file)));
            $l_sxml_element     = new isys_library_xml($l_filedata, LIBXML_NOCDATA);
            $this->m_xml_object = $l_sxml_element;
            $this->to_array($l_xml_data, $l_sxml_element);
        }
        catch (Exception $e)
        {
            echo($e->getMessage() . "\n\nOccured in: " . __FILE__ . ':' . __LINE__);
        }
        isys_import_log::add('Import formatting completed.');
        if (!is_array($l_xml_data))
        {
            return false;
        }
        $this->set_xml_data($l_xml_data);

        return true;
    }

    /**
     * Loads XML data and transforms it into a Simple XML object.
     *
     * @param string $p_data XML data
     *
     * @return bool Success?
     */
    public function load_xml_data($p_data)
    {
        assert('is_string($p_data) && !empty($p_data)');
        $l_xml_data         = [];
        $l_sxml_element     = new isys_library_xml(
            str_replace(
                '<value></value>',
                '',
                $p_data
            ), LIBXML_NOCDATA
        );
        $this->m_xml_object = $l_sxml_element;
        $this->to_array($l_xml_data, $l_sxml_element);
        if (!is_array($l_xml_data))
        {
            return false;
        }
        $this->set_xml_data($l_xml_data);

        return true;
    } //function

    /**
     * Loads XML file content and transforms it into a Simple XML object.
     *
     * @param string $p_file Path to XML file
     *
     * @return bool Success?
     */
    public function load_xml_file($p_file)
    {
        assert('is_readable($p_file)');
        $l_data = file_get_contents($p_file);

        return $this->load_xml_data($l_data);
    } //function

}

?>