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
 * @package     i-doit
 * @subpackage  Workflow
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_workflow_request extends isys_workflow
{
    /**
     * @var int
     */
    private $m_from;
    /**
     * @var  array
     */
    private $m_meta;
    /**
     * @var  array
     */
    private $m_request;
    /**
     * @var  string
     */
    private $m_to;
    /**
     * @var  integer
     */
    private $m_workflow_type;

    /**
     * Returns the request.
     *
     * @return  array
     */
    public function get_request()
    {
        return $this->m_request;
    } // function

    /**
     * Returns the "from" contact ID.
     *
     * @return  integer
     */
    public function get_from()
    {
        return $this->m_from;
    } // function

    /**
     * Returns the "to" contact ID.
     *
     * @return  integer
     */
    public function get_to()
    {
        return $this->m_to;
    } // function

    /**
     * Returns the meta information.
     *
     * @return  array
     */
    public function get_meta()
    {
        return $this->m_meta;
    } // function

    /**
     * Returns the workflow type.
     *
     * @return  integer
     */
    public function get_workflow_type()
    {
        return $this->m_workflow_type;
    } // function

    /**
     *
     * @return  boolean
     */
    public function format_request()
    {
        global $g_comp_database;

        $l_request    = $this->m_request;
        $this->m_meta = $l_request;

        if (!empty($this->m_workflow_type))
        {
            $l_dao_wf_tpl         = new isys_workflow_dao_template($g_comp_database);
            $l_template_parameter = $l_dao_wf_tpl->get_template_parameter($this->m_workflow_type);

            foreach ($l_template_parameter as $l_value)
            {
                $l_param = $l_value["key"];

                // 4 = DATETIME
                if ($l_value["type"] <> 4)
                {
                    $l_new_request[$l_param] = $l_request[$l_param];
                }
                else
                {
                    $l_new_request[$l_param] = $l_request[$l_param . "__HIDDEN"];
                } // if
            } // foreach
        }
        else
        {
            return false;
        } // if

        $this->m_request = $l_new_request;

        return true;
    } // function

    /**
     * @param   integer $p_user_id
     *
     * @return  integer
     */
    public function set_initiator($p_user_id)
    {
        global $g_comp_database;

        $l_dao_reference = new isys_contact_dao_reference($g_comp_database);
        $l_dao_reference->insert_data_item($p_user_id, C__CONTACT__DATA_ITEM__PERSON_INTERN);

        if ($l_dao_reference->save())
        {
            $l_contact_id = $l_dao_reference->get_id();
        } // if

        $this->m_from = $l_contact_id;

        return $l_contact_id;
    } // function

    /**
     * Workflow-type setter.
     *
     * @param  integer $p_workflow_type
     */
    public function set_workflow_type($p_workflow_type)
    {
        $this->m_workflow_type = $p_workflow_type;
    } // function

    /**
     * Constructor.
     *
     * @param  array   $p_request
     * @param  integer $p_from
     * @param  integer $p_to
     */
    public function __construct($p_request, $p_from, $p_to)
    {
        $this->m_request = $p_request;
        $this->m_to      = $p_to;

        $this->set_initiator($p_from);
    } // function
} // class