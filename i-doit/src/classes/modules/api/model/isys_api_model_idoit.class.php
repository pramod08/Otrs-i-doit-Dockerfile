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
 * CMDB API model
 *
 * @package    i-doit
 * @subpackage API
 * @author     Dennis Stücken <dstuecken@synetics.de>, Benjamin Heisig <bheisig@synetics.de>
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_api_model_idoit extends isys_api_model
{

    public function route($p_method, $p_params)
    {
        // Build model class
        $l_modelclass = 'isys_api_model_idoit_' . $p_method;

        // Call data method and format data:
        if (class_exists($l_modelclass))
        {
            /**
             * @var isys_api_model_idoit
             */
            $l_model = new $l_modelclass();

            if (method_exists($l_model, 'read'))
            {
                $this->m_data = $l_model->read($p_params);
            }
            else
            {
                throw new Exception('Could not read from model ' . $p_method);
            }
        }
        else
        {
            throw new Exception('Method \'idoit.' . $p_method . '\' does not exist');
        }

        return $this;
    }

} //class

?>