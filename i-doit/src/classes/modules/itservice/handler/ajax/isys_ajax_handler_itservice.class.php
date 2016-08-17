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
 * AJAX controller
 *
 * @package     modules
 * @subpackage  itservice
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 * @since       i-doit 1.4.3
 */
class isys_ajax_handler_itservice extends isys_ajax_handler
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
            'message' => null,
            'data'    => null
        ];

        try
        {
            switch ($_GET['func'])
            {
                case 'get-filter-css-classes':
                    $l_return['data'] = $this->get_filter_css_classes($_POST['filter']);
                    break;
            } // switch
        }
        catch (isys_exception $e)
        {
            $l_return['success'] = false;
            $l_return['message'] = $e->getMessage();
        } // try

        echo isys_format_json::encode($l_return);

        $this->_die();
    } // function

    /**
     * This method defines, if the hypergate needs to be included for this request.
     *
     * @static
     * @return  boolean
     */
    public static function needs_hypergate()
    {
        return true;
    } // function

    /**
     * This method is used by the analytics module. We filter GUI elements by the returned CSS classes.
     *
     * @param   integer $p_filter
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function get_filter_css_classes($p_filter)
    {
        $l_result = [];
        $l_filter = isys_itservice_dao_filter_config::instance($this->m_database_component)
            ->get_data($p_filter);

        // This currently just works for "level" and "object-type".
        if (isset($l_filter['formatted__data']['level']))
        {
            $l_levels = range(($l_filter['formatted__data']['level'] + 1), 15);

            foreach ($l_levels as $l_level)
            {
                $l_result[] = 'level-' . $l_level;
            } // foreach
        } // if

        if (isset($l_filter['formatted__data']['object-type']) && is_array($l_filter['formatted__data']['object-type']) && count($l_filter['formatted__data']['object-type']))
        {
            $l_object_types = $l_filter['formatted__data']['object-type'];

            foreach ($l_object_types as $l_object_type)
            {
                if (is_numeric($l_object_type))
                {
                    $l_result[] = 'obj-type-id-' . $l_object_type;
                }
                else if (defined($l_object_type))
                {
                    $l_result[] = 'obj-type-id-' . constant($l_object_type);
                } // if
            } // foreach
        } // if

        return $l_result;
    } // function
} // class