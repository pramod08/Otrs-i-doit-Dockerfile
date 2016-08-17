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
 * @subpackage  Export CMDB
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
abstract class isys_export_csv
{
    /**
     * @var array
     */
    protected $m_data = [];
    /**
     * @var array
     */
    protected $m_header = [];
    /**
     * @var string
     */
    protected $m_title = "";

    /**
     * Database component.
     *
     * @var  isys_component_database
     */
    private $m_database;

    /**
     * Should export as ARRAY.
     *
     * @param   mixed $p_object_ids
     *
     * @return  array
     */
    abstract function export($p_object_ids);

    /**
     * Gets header mapping.
     *
     * @return  array
     */
    public function get_header()
    {
        return $this->m_header;
    } // function

    /**
     * Get database component.
     *
     * @return  isys_component_database
     */
    public function get_database()
    {
        return $this->m_database;
    } // function

    /**
     * Get Title of CSV import.
     *
     * @return  string
     */
    public function get_title()
    {
        return _L($this->m_title);
    } // function

    /**
     * Gets connected contact.
     *
     * @param   integer $p_id
     *
     * @return  string
     */
    public function get_contact($p_id)
    {
        if ($p_id > 0)
        {
            return isys_cmdb_dao::factory($this->get_database())
                ->retrieve(
                    "SELECT isys_obj__title FROM isys_contact_2_isys_obj INNER JOIN isys_obj ON isys_obj__id = isys_contact_2_isys_obj__isys_obj__id WHERE isys_contact_2_isys_obj__isys_contact__id = '" . $p_id . "';"
                )
                ->get_row_value('isys_obj__title');
        } // if
    } // function

    /**
     * Gets title from table.
     *
     * @param   integer $p_id
     * @param   string  $p_table
     *
     * @return  string
     */
    public function get_unit($p_id, $p_table = null)
    {
        if ($p_id > 0)
        {
            return isys_cmdb_dao::factory($this->get_database())
                ->retrieve("SELECT " . $p_table . "__title FROM " . $p_table . " WHERE " . $p_table . "__id = '" . $p_id . "';")
                ->get_row_value($p_table . '__title');
        } // if
    } // function

    /**
     * Formats date.
     *
     * @param   string $p_date
     * @param   string $p_format
     *
     * @return  string
     */
    public function format_date($p_date, $p_format = "d.m.y")
    {
        return date($p_format, strtotime($p_date));
    } // function

    /**
     * Gets object title by connection id.
     *
     * @param   integer $p_id
     *
     * @return  string
     */
    public function connection($p_id)
    {
        if ($p_id > 0)
        {
            return isys_cmdb_dao::factory($this->get_database())
                ->retrieve(
                    "SELECT isys_obj__title FROM isys_obj INNER JOIN isys_connection ON isys_connection__isys_obj__id = isys_obj__id WHERE isys_connection__id = '" . $p_id . "';"
                )
                ->get_row_value('isys_obj__title');
        } // if
    } // function

    /**
     * Sets header mapping.
     *
     * @param  array $p_header
     */
    protected function set_header($p_header)
    {
        $this->m_header = $p_header;
    } // function

    /**
     * Constructor.
     *
     * @param   isys_component_database $p_database
     *
     * @throws  isys_exception_general
     */
    public function __construct(isys_component_database &$p_database = null)
    {
        // Store database component.
        if (is_null($p_database) && !is_object($p_database))
        {
            global $g_comp_database;
            $l_database = $g_comp_database;
        }
        else
        {
            $l_database = $p_database;
        } // if

        $this->m_database = $l_database;

        // Check if database component is fine.
        if (is_null($this->m_database))
        {
            global $g_comp_session;

            if (!$g_comp_session->is_logged_in())
            {
                throw new isys_exception_general("Your session is expired. You need to re-login!");
            }
            else
            {
                throw new isys_exception_general("Database component is NULL. Can't work with it! \n");
            } // if
        } // if
    } // function
} // class