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
 *
 * @package    i-doit
 * @subpackage Workflow
 * @author     Dennis Stücken <dstuecken@i-doit.org>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.i-doit.com/license
 */
class isys_workflow_action_parameter extends isys_workflow
{ // isys_workflow, because the inherited members from isys_workflow_action are not necessary here

    private $m_key;
    private $m_template_parameter;
    private $m_type;
    private $m_value;

    public function get_key()
    {
        return $this->m_key;
    }

    public function get_value()
    {
        return $this->m_value;
    }

    public function get_type()
    {
        return $this->m_type;
    }

    public function get_template_parameter()
    {
        return $this->m_template_parameter;
    }

    public function set_key($p_key)
    {
        $this->m_key = $p_key;
    }

    public function set_value($p_value)
    {
        $this->m_value = $p_value;
    }

    public function set_type($p_type)
    {
        $this->m_type = $p_type;
    }

    public function set_template_parameter($p_template_parameter)
    {
        return $this->m_template_parameter = $p_template_parameter;
    }

    /**
     * @desc sets a pair of key and value for this object
     *
     * @param string $p_key
     * @param string $p_value
     */
    public function __construct($p_key, $p_value, $p_type, $p_template_parameter, $p_id)
    {
        $this->set_key($p_key);
        $this->set_value($p_value);
        $this->set_type($p_type);
        $this->set_template_parameter($p_template_parameter);

        $this->m_id = $p_id;
    }
}

?>