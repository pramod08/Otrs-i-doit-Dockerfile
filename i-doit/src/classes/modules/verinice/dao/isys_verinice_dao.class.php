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
 * Module DAO for verinice
 *
 * @package    i-doit
 * @subpackage Modules
 * @author     Dennis Stücken <dstuecken@i-doit.org>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 *
 */
class isys_verinice_dao extends isys_module_dao
{

    private $m_xml = [
        'header'  => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<syncMapping xmlns="http://www.sernet.de/sync/mapping">
',
        'footer'  => '</syncMapping>',
        'content' => '    <mapObjectType intId="%intid%" extId="%extid%">
        <mapAttributeType intId="%intid%_name" extId="idoit-title"/>
        <mapAttributeType intId="%intid%_erlaeuterung" extId="idoit-description"/>
        <mapAttributeType intId="%intid%_kuerzel" extId="idoit-abbrev"/>
        <mapAttributeType intId="%intid%_tag" extId="idoit-tag"/>
    </mapObjectType>'
    ];

    /**
     * Retrieves all templates
     *
     * @param int $p_obj_id
     *
     * @return isys_component_dao_result
     */
    public function get_data($p_type_id = null)
    {

        $l_sql = "SELECT * FROM isys_verinice_types WHERE TRUE ";

        if (!empty($p_type_id))
        {
            $l_sql .= " AND (isys_verinice_types__id = " . $this->convert_sql_int($p_type_id) . ")";
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Save verinice mapping to isys_obj_type
     *
     * @param int $p_obj_type_id
     * @param int $p_verinice_type
     *
     * @return boolean
     */
    public function save($p_obj_type_id, $p_verinice_type)
    {

        $l_sql = 'UPDATE isys_obj_type SET ' . 'isys_obj_type__isys_verinice_types__id = ' . $this->convert_sql_id(
                $p_verinice_type
            ) . ' ' . 'WHERE isys_obj_type__id = ' . $this->convert_sql_int($p_obj_type_id);

        return $this->update($l_sql) && $this->apply_update();
    }

    /**
     * Retrieve all configured object types
     *
     * @return isys_component_dao_result
     */
    public function get_export_data()
    {

        $l_sql = "SELECT isys_obj_type__id, isys_obj_type__title, isys_verinice_types__title, isys_verinice_types__const " . "FROM isys_obj_type " . "INNER JOIN isys_verinice_types ON isys_obj_type__isys_verinice_types__id = isys_verinice_types__id ";

        return $this->retrieve($l_sql);

    }

    /**
     * Return XML Mapping for verinice
     *
     * @param isys_component_dao_result $p_data
     *
     * @return string
     */
    public function format_verinice_mapping($p_data)
    {

        if (is_object($p_data))
        {
            $l_xml = $this->m_xml['header'];

            while ($l_data = $p_data->get_row())
            {

                $l_xml .= str_replace(
                    [
                        '%intid%',
                        '%extid%'
                    ],
                    [
                        $l_data['isys_verinice_types__const'],
                        _L($l_data['isys_obj_type__title'])
                    ],
                    $this->m_xml['content']
                );

            }

            return $l_xml . "\n" . $this->m_xml['footer'];

        }

        throw new Exception('Error: Wrong data format provided');
    }

}

?>