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
 * API model
 *
 * @package    i-doit
 * @subpackage API
 * @author     Dennis Stücken <dstuecken@synetics.de>, Benjamin Heisig <bheisig@synetics.de>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */

/**
 * API model base class
 */
abstract class isys_api_model extends isys_api
{

    /**
     * Model data
     *
     * @var array Associative array
     */
    protected $m_data = [];
    /**
     * Mandator database
     *
     * @var isys_component_database
     */
    protected $m_db;
    /**
     * Parameter validation
     *
     * @var array
     */
    protected $m_validation = [];

    /**
     * Method router
     *
     * @param string $p_method Data method
     * @param array  $p_params Parameters (depends on data method)
     *
     * @return isys_api_model Returns itself.
     */
    abstract public function route($p_method, $p_params); // function

    /**
     * Gets validation.
     *
     * @return array
     */
    public function get_validation()
    {
        return $this->m_validation;
    }

    /**
     * Gets model data.
     *
     * @return mixed
     */
    public function get_data()
    {
        return $this->m_data;
    } // function

    /**
     * Sets mandator database.
     *
     * @param isys_component_database &$p_database
     */
    public function set_database(isys_component_database &$p_database)
    {
        $this->m_db = $p_database;
    } // function

    /**
     * @return isys_component_database
     */
    public function get_database()
    {
        return $this->m_db;
    }

    /**
     * Decides wheather the model needs a login or not.
     *
     * @return bool Defaults to true.
     */
    public function needs_login()
    {
        return true;
    } // function

    /**
     * Formats data from DAO result to array (if needed).
     *
     * @param mixed $p_data Model data (array) or DAO result (isys_component_dao_result)
     *
     * @return array Formatted data
     */
    protected function format($p_data)
    {
        $this->m_log->info(
            'Data: ' . var_export(
                $this->m_data,
                true
            )
        );

        if (is_a(
            $p_data,
            'isys_component_dao_result'
        ))
        {
            $this->m_data = $p_data->__as_array();
        }
        else if (is_array($p_data))
        {
            $this->m_data = $p_data;
        }
        else
        {
            $this->m_data = $p_data;
        }

        return $this->m_data;
    }

    /**
     * Api success message. Used as default return message for deleting, updating or creating entries.
     *
     * @param bool   $p_result
     * @param string $p_message
     * @param int    $p_mysql_id
     *
     * @return array
     */
    protected function api_success($p_result, $p_message, $p_mysql_id = null)
    {
        return [
            'success' => $p_result,
            'id'      => $p_mysql_id,
            'message' => $p_message
        ];
    } // function

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::init();
    } // function

} // class