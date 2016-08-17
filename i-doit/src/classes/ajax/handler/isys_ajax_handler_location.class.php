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
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       1.4.7
 */
class isys_ajax_handler_location extends isys_ajax_handler
{
    /**
     * Init method, which gets called from the framework.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function init()
    {
        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json');

        $l_return = [
            'success' => true,
            'data'    => null,
            'message' => null
        ];

        try
        {
            switch ($_GET['func'])
            {
                case 'get_logical_physical_path':
                    $l_return['data'] = $this->get_logical_physical_path($_POST[C__CMDB__GET__OBJECT]);
                    break;
                case 'get_location_path':
                    $l_return['data'] = $this->get_location_path($_POST[C__CMDB__GET__OBJECT]);
                    break;
            } // switch
        }
        catch (Exception $e)
        {
            $l_return['success'] = false;
            $l_return['message'] = $e->getMessage();
        }

        echo isys_format_json::encode($l_return);

        $this->_die();
    } // function

    /**
     * This method will return the logical physical path by object IDs.
     *
     * @param $p_obj_id
     *
     * @return mixed
     * @throws isys_exception_general
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_logical_physical_path($p_obj_id)
    {
        return isys_cmdb_dao_category_g_logical_unit::instance($this->m_database_component)
            ->get_logical_physical_path($p_obj_id);
    } // function

    /**
     * This method will return the location path by object IDs. For example [512, 42, 532, 34, 20, 1]
     *
     * @param   integer $p_obj_id
     *
     * @return  array
     */
    public function get_location_path($p_obj_id)
    {
        return isys_cmdb_dao_category_g_location::instance($this->m_database_component)
            ->get_location_path($p_obj_id);
    } // function
} // class