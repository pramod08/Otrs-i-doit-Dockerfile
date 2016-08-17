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
 * Validation DAO
 *
 * @package     i-doit
 * @subpackage  CMDB_Low-Level_API
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @version     1.5
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_validation extends isys_cmdb_dao
{
    /**
     * Retrieve contents from isys_validation_config.
     *
     * @param   integer $p_config_id
     * @param   integer $p_cat_id
     * @param   string  $p_cat_type
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_data($p_config_id = null, $p_cat_id = null, $p_cat_type = 'g')
    {
        $l_sql = 'SELECT * FROM isys_validation_config WHERE TRUE ';

        if ($p_config_id !== null)
        {
            $l_sql .= 'AND isys_validation_config__id = ' . $this->convert_sql_id($p_config_id) . ' ';
        } // if

        if ($p_cat_id !== null)
        {
            $l_sql .= 'AND isys_validation_config__isysgui_cat' . $p_cat_type . '__id = ' . $this->convert_sql_id($p_cat_id) . ' ';
        } // if

        return $this->retrieve($l_sql . ';');
    } // function

    /**
     * Method for resetting the complete validation configuration.
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function truncate()
    {
        return ($this->update('TRUNCATE isys_validation_config;') && $this->apply_update());
    } // function

    /**
     * Method for creating a new validation config in the database.
     *
     * @param   array $p_data
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function create(array $p_data)
    {
        $l_json = isys_format_json::encode($p_data['config']);

        // Create.
        $l_sql = 'INSERT INTO isys_validation_config SET
			isys_validation_config__isysgui_catg__id = ' . $this->convert_sql_id($p_data['catg']) . ',
			isys_validation_config__isysgui_cats__id = ' . $this->convert_sql_id($p_data['cats']) . ',
			isys_validation_config__isysgui_catg_custom__id = ' . $this->convert_sql_id($p_data['catc']) . ',
			isys_validation_config__json = ' . $this->convert_sql_text($l_json) . ';';

        return ($this->update($l_sql) && $this->apply_update());
    } // function
} // class