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
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @version     1.1
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_ajax_handler_sanitize_field extends isys_ajax_handler
{
    /**
     * Init method, which gets called from the framework.
     *
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function init()
    {
        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json');

        $l_return = '';

        if ($_POST['field_value'] == '') $this->_die();

        if (isys_tenantsettings::get('cmdb.registry.sanitize_input_data', 1))
        {
            switch ($_POST['type'])
            {
                case 'text':
                    $l_return = $this->sanitize('text', $_POST['field_value']);
                    break;
                case 'float':
                    $l_return = $this->sanitize('float', $_POST['field_value']);
                    break;
                case 'int':
                    $l_return = $this->sanitize('int', $_POST['field_value']);
                    break;
            } // switch
        }
        else
        {
            $l_return = $_POST['field_value'];
        }

        // Output the result.
        echo isys_format_json::encode($l_return);

        // And die, since this is a ajax request.
        $this->_die();
    } // function

    /**
     * Method for validating a value of
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    protected function sanitize($p_type, $p_value)
    {
        $l_return = '';

        switch ($p_type)
        {
            case 'text':
                $l_return = isys_helper::sanitize_text($p_value);
                break;
            case 'float':
                $l_return = isys_helper::filter_number($p_value);
                break;
            case 'int':
                $l_return = filter_var($p_value, FILTER_SANITIZE_NUMBER_INT);
                break;
        }

        return $l_return;
    } // function
} // class
?>