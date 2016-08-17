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
 * Settings DAO.
 *
 * @package     i-doit
 * @subpackage  Components
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @version     1.3
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_component_dao_tenant_settings extends isys_component_dao
{
    /**
     * Tenant ID.
     *
     * @var  integer
     */
    protected $m_tenant_id = null;

    /**
     * Tenant ID getter.
     *
     * @return  integer
     */
    public function get_tenant_id()
    {
        return $this->m_tenant_id;
    } // function

    /**
     * Tenant ID setter.
     *
     * @param   integer $m_tenant_id
     *
     * @return  $this
     */
    public function set_tenant_id($m_tenant_id)
    {
        $this->m_tenant_id = $m_tenant_id;

        return $this;
    } // function

    /**
     * Return all settings as an array.
     *
     * @return  array
     */
    public function get_settings()
    {
        $l_settings  = [];
        $l_condition = '';

        if ($this->m_tenant_id > 0)
        {
            $l_condition .= 'WHERE isys_settings__isys_mandator__id = ' . $this->convert_sql_id($this->m_tenant_id);
        } // if

        $l_res = $this->retrieve('SELECT * FROM isys_settings ' . $l_condition . ' ORDER BY isys_settings__key ASC;');

        if (count($l_res))
        {
            while ($l_row = $l_res->get_row())
            {
                if (isys_format_json::is_json($l_row['isys_settings__value']))
                {
                    $l_settings[$l_row['isys_settings__key']] = isys_format_json::decode($l_row['isys_settings__value'], true);
                }
                else
                {
                    $l_settings[$l_row['isys_settings__key']] = $l_row['isys_settings__value'];
                } // if
            } // while
        } // if

        return $l_settings;
    } // function

    /**
     * Save key and value to database.
     *
     * @param   string $p_key
     * @param   mixed  $p_value
     *
     * @return  isys_component_dao_settings
     */
    public function set($p_key, $p_value)
    {
        if ($p_value === true)
        {
            $p_value = 1;
        } // if

        if ($p_value === false)
        {
            $p_value = 0;
        } // if

        if (is_array($p_value) || is_object($p_value))
        {
            $p_value = isys_format_json::encode($p_value);
        } // if

        $l_sql          = 'SELECT * FROM isys_settings WHERE isys_settings__key = ' . $this->convert_sql_text($p_key);
        $l_objectUpdate = $l_objectCondition = '';

        if ($this->m_tenant_id > 0)
        {
            $l_objectCondition = ' AND isys_settings__isys_mandator__id = ' . $this->convert_sql_id($this->m_tenant_id);
            $l_objectUpdate    = 'isys_settings__isys_mandator__id = ' . $this->convert_sql_id($this->m_tenant_id) . ', ';

            $l_sql .= $l_objectCondition;
        } // if

        if (count($this->retrieve($l_sql)) > 0)
        {
            $l_sql = 'UPDATE isys_settings SET isys_settings__value = ' . $this->convert_sql_text($p_value) . ' WHERE isys_settings__key = ' . $this->convert_sql_text(
                    $p_key
                ) . $l_objectCondition . ';';
        }
        else
        {
            $l_sql = 'INSERT INTO isys_settings SET isys_settings__value = ' . $this->convert_sql_text(
                    $p_value
                ) . ', ' . $l_objectUpdate . 'isys_settings__key = ' . $this->convert_sql_text($p_key) . ';';
        } // if

        $this->update($l_sql . ';');

        return $this;
    } // function

    /**
     * Save settings.
     *
     * @param   array $p_settings
     *
     * @return  boolean
     */
    public function save($p_settings)
    {
        if (is_array($p_settings) && count($p_settings) > 0)
        {
            $this->begin_update();

            foreach ($p_settings as $l_key => $l_value)
            {
                if ($l_key)
                {
                    if ($l_value === true)
                    {
                        $l_value = '1';
                    } // if

                    if ($l_value === false)
                    {
                        $l_value = '0';
                    } // if

                    $this->set($l_key, $l_value);
                } // if
            } // foreach

            return $this->apply_update();
        } // if

        return false;
    } // function

    /**
     * isys_component_dao_tenant_settings constructor.
     *
     * @param  isys_component_database $p_db
     * @param  integer                 $p_tenant_id
     */
    public function __construct(isys_component_database $p_db, $p_tenant_id = null)
    {
        $this->m_tenant_id = $p_tenant_id ?: isys_component_session::instance()
            ->get_mandator_id();

        parent::__construct($p_db);
    } // function
} // class