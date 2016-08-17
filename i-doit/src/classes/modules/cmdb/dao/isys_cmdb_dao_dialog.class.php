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
 * Dialog DAO.
 *
 * @package     i-doit
 * @subpackage  CMDB_Low-Level_API
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_dialog extends isys_cmdb_dao
{
    /**
     * The static cache variable.
     *
     * @var  array
     */
    protected $m_cache = [];

    /**
     * Cache which contains parent tables
     *
     * @var array
     */
    protected $m_cache_parent_tables = [];

    /**
     * The dialogs table-name.
     *
     * @var  string
     */
    protected $m_table = '';

    /**
     * Retrieves data with the specified title and parent id
     *
     * @param string $p_title
     * @param int    $p_parent_id
     *
     * @return bool
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_data_by_parent($p_title, $p_parent_id)
    {
        if (!isset($this->m_cache_parent_tables[$this->m_table]))
        {
            $this->m_cache_parent_tables[$this->m_table] = isys_cmdb_dao_dialog_admin::instance($this->m_db)
                ->get_parent_table($this->m_table);
        } // if
        $l_title_lower = trim(strtolower($p_title));
        if (isset($this->m_cache[$this->m_table]))
        {
            foreach ($this->m_cache[$this->m_table] AS $l_data)
            {
                $l_data_lower_title = isset($l_data['title_lower'])? $l_data['title_lower']: strtolower((isset($l_data['title'])? $l_data['title']: $l_data[$this->m_table . '__title']));
                if ($l_data[$this->m_table . '__' . $this->m_cache_parent_tables[$this->m_table] . '__id'] == $p_parent_id && $l_data_lower_title === $l_title_lower
                )
                {
                    return $l_data;
                } // if
            } // foreach
        } // if
        return false;
    } // function

    /**
     * Method for retrieving data from a dialog-table.
     *
     * @param   mixed  $p_id Can be the numeric ID or a constant as string.
     * @param   string $p_title
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_data($p_id = null, $p_title = null)
    {
        // This should never fail, but we want to go sure.
        if (array_key_exists($this->m_table, $this->m_cache))
        {
            if ($p_id !== null)
            {
                if (is_numeric($p_id))
                {
                    if (isset($this->m_cache[$this->m_table]) && isset($this->m_cache[$this->m_table][$p_id]))
                    {
                        return $this->m_cache[$this->m_table][$p_id];
                    } // if
                }
                else
                {
                    // Find the entry by constant.
                    foreach ($this->m_cache[$this->m_table] as $l_data)
                    {
                        if (is_string($p_id) && strtolower($l_data[$this->m_table . '__const']) == strtolower($p_id))
                        {
                            return $l_data;
                        } // if
                    } // foreach
                } // if

                return false;
            } // if

            if ($p_title !== null)
            {
                $p_title = strtolower($p_title);
                foreach ($this->m_cache[$this->m_table] as $l_data)
                {
                    if (strtolower($l_data[$this->m_table . '__title']) == $p_title)
                    {
                        return $l_data;
                    } // if
                } // foreach

                // If we can't find the given title, we return false.
                return false;
            } // if

            return $this->m_cache[$this->m_table];
        } // if

        return false;
    } // function

    /**
     * Method for retrieving the raw data from a dialog-table.
     *
     * @param   integer $p_id
     * @param   string  $p_title
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_data_raw($p_id = null, $p_title = null)
    {
        // We cache the data, as soon as this class is instanced.
        return $this->get_dialog($this->m_table, $p_id, $p_title);
    } // function

    /**
     * Method for (re-)loading the dialog-data.
     *
     * @return  isys_cmdb_dao_dialog
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function load()
    {
        // We cache the data, as soon as this class is instanced.
        $l_res = $this->get_dialog($this->m_table);

        while ($l_row = $l_res->get_row())
        {
            $this->m_cache[$this->m_table][$l_row[$this->m_table . '__id']]          = $l_row;
            $this->m_cache[$this->m_table][$l_row[$this->m_table . '__id']]['title'] = _L(trim($l_row[$this->m_table . '__title']));
            $this->m_cache[$this->m_table][$l_row[$this->m_table . '__id']]['title_lower'] = strtolower($this->m_cache[$this->m_table][$l_row[$this->m_table . '__id']]['title']);
        } // while

        return $this;
    } // function

    /**
     * Method for resetting and reloading the dialog-data.
     *
     * @return  isys_cmdb_dao_dialog
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function reset()
    {
        unset($this->m_cache[$this->m_table], $this->m_cache_parent_tables[$this->m_table]);

        return $this->load();
    } // function

    /**
     * Setter Method which sets the current table
     *
     * @param $p_table
     *
     * @return $this
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function set_table($p_table)
    {
        $this->m_table = $p_table;

        return $this;
    } // function

    /**
     * Getter method which retrieves the current table
     *
     * @return string
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_table()
    {
        return $this->m_table;
    } // function

    /**
     * Constructor.
     *
     * @param   isys_component_database $p_db
     * @param   string                  $p_table
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function __construct(isys_component_database &$p_db, $p_table = null)
    {
        parent::__construct($p_db);

        if ($p_table !== null)
        {
            $this->m_table = $p_table;

            // Immediately load the dialog-data.
            $this->load();
        } // if
    } // function

} // class