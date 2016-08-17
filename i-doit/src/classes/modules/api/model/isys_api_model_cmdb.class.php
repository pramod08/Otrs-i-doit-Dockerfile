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
 * CMDB API model.
 *
 * @package     i-doit
 * @subpackage  API
 * @author      Dennis Stücken <dstuecken@synetics.de>, Benjamin Heisig <bheisig@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_api_model_cmdb extends isys_api_model
{
    /**
     * CMDB DAO.
     *
     * @var  isys_cmdb_dao
     */
    protected $m_dao;

    /**
     * Creates new data.
     *
     * @param   array $p_params Parameters (depends on data method).
     *
     * @return  null
     */
    public function create($p_params)
    {
        return null;
    } // function

    /**
     * Updates data.
     *
     * @param   array $p_params Parameters (depends on data method).
     *
     * @return  null
     */
    public function update($p_params)
    {
        return null;
    } // function

    /**
     * Deletes data.
     *
     * @param   array $p_params Parameters (depends on data method).
     *
     * @return  null
     */
    public function delete($p_params)
    {
        return null;
    } // function

    /* ---------------------------------------------------------------------- */
    /* Helper methods ------------------------------------------------------- */
    /* ---------------------------------------------------------------------- */

    /**
     * Checks parameter 'category' for valid category types. Helper method.
     *
     * @param   mixed $p_category Int, string or array of ints or strings.
     *
     * @return  array
     * @throws  isys_exception_api
     */
    protected function check_category_type($p_category)
    {
        $l_return = [];
        if (is_numeric($p_category))
        {
            $l_category            = intval($p_category);
            $l_return[$l_category] = [];
        }
        else if (is_string($p_category))
        {
            $l_type = null;
            if (stripos($p_category, 'global') !== false)
            {
                $l_type = C__CMDB__CATEGORY__TYPE_GLOBAL;
            }
            else if (stripos($p_category, 'specific') !== false)
            {
                $l_type = C__CMDB__CATEGORY__TYPE_SPECIFIC;
            }
            else if (stripos($p_category, 'custom') !== false)
            {
                $l_type = C__CMDB__CATEGORY__TYPE_CUSTOM;
            }
            else
            {
                throw new isys_exception_api('unkown category type [naming]');
            } // if

            $l_return[$l_type] = [];
        }
        else
        {
            throw new isys_exception_api('unkown category type [format]');
        } // if

        return $l_return;
    } // function

    /* ---------------------------------------------------------------------- */
    /* Generic methods ------------------------------------------------------ */
    /* ---------------------------------------------------------------------- */

    /**
     * Formats data array by mapping and encodes data to UTF-8.
     *
     * @param   array $p_mapping The mapping itself
     * @param   array $p_row     Data array
     *
     * @return  array  Formatted data array
     */
    protected function format_by_mapping(array $p_mapping, $p_row)
    {
        $l_return = [];

        try
        {
            foreach ($p_mapping as $l_key => $l_map)
            {
                if (isset($p_row[$l_key]) || is_array($l_map))
                {
                    if (is_array($l_map))
                    {
                        if (is_callable($l_map[0]))
                        {
                            if (strpos($l_map[0], 'str') === 0 || $l_map[0] == '_L')
                            {
                                $l_return[$l_map[1]] = @call_user_func($l_map[0], @$p_row[$l_key]);
                            }
                            else
                            {
                                $l_return[$l_map[1]] = @call_user_func_array(
                                    $l_map[0],
                                    [
                                        @$p_row[$l_key],
                                        $p_row
                                    ]
                                );
                            }
                        }
                    }
                    elseif (is_scalar($l_map))
                    {
                        $l_return[$l_map] = $p_row[$l_key];
                    } // if
                } // if
            } // foreach
        }
        catch (Exception $e)
        {
            ;
        }

        return $l_return;
    } // function

    /* ---------------------------------------------------------------------- */
    /* Helping methods ------------------------------------------------------ */
    /* ---------------------------------------------------------------------- */

    /**
     * Api success message. Used as default return message for deleting, updating or creating entries.
     *
     * @param   boolean $p_result
     * @param   string  $p_message
     * @param   integer $p_mysql_id
     *
     * @return  array
     */
    protected function api_success($p_result, $p_message, $p_mysql_id = null)
    {
        return [
            'success' => $p_result,
            'id'      => $p_mysql_id,
            'message' => $p_message
        ];
    } //function

    /**
     * Reads data.
     *
     * @param   string $p_method Data method.
     * @param   array  $p_params Parameters (depends on data method).
     *
     * @return  isys_api_model_cmdb  Returns itself.
     * @throws  isys_exception_api
     */
    public function route($p_method, $p_params)
    {
        // Build model class
        $l_modelclass = 'isys_api_model_cmdb_' . $p_method;

        // Call data method and format data:
        if (class_exists($l_modelclass))
        {
            if (!is_object($this->m_db))
            {
                throw new isys_exception_api('Database not loaded. Your login may did not work!');
            } // if

            // Initialize DAO:
            $this->m_dao = new isys_cmdb_dao($this->m_db, null);

            $l_model = new $l_modelclass($this->m_dao);

            if (isset($p_params['option']) && in_array(
                    $p_params['option'],
                    [
                        'read',
                        'create',
                        'update',
                        'delete',
                        'quickpurge'
                    ]
                )
            )
            {
                $l_method = $p_params['option'];
            }
            else
            {
                $l_method = 'read';
            } // if

            // Check for mandatory parameters.
            $l_validation = $l_model->get_validation();

            if (isset($l_validation[$l_method]) && is_array($l_validation[$l_method]))
            {
                foreach ($l_validation[$l_method] as $l_validate)
                {
                    if ($l_validate && !isset($p_params[$l_validate]))
                    {
                        throw new isys_exception_api('Mandatory parameter "' . $l_validate . '" not found in your request.', -32602);
                    } // if
                } // foreach
            } // if

            if (method_exists($l_model, $l_method))
            {
                $this->m_log->info('Method: ' . $l_method);
                $this->format($l_model->$l_method($p_params));
            } // if
        }
        else
        {
            $this->m_log->error('Method "' . $p_method . '" does not exit.');
            throw new isys_exception_api('API Method "' . $p_method . '" (' . $l_modelclass . ') does not exist.', -32601);
        } // if

        return $this;
    } // function

    /**
     * Compares mapped result by 'title'. Used by usort().
     *
     * @param   array $p_arr1
     * @param   array $p_arr2
     *
     * @return  integer
     */
    protected function sort_by_title($p_arr1, $p_arr2)
    {
        return strcmp($p_arr1['title'], $p_arr2['title']);
    } // function

    /**
     * Constructor
     */
    public function __construct()
    {
        if (is_object($this->m_dao))
        {
            $this->m_db = $this->m_dao->get_database_component();
        }
        else
        {
            global $g_comp_database;
            $this->m_db = $g_comp_database;
        } // if

        parent::__construct();
    } // function
} // class