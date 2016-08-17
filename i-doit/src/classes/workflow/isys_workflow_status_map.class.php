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
class isys_workflow_status_map extends isys_workflow
{

    /**
     * @desc status description as language constants
     * @var array
     */
    private $m_description = [];
    /**
     * @desc status ids
     * @var array
     */
    private $m_status;

    public function build_map()
    {
        global $g_comp_database;
        global $g_comp_template_language_manager;

        $l_workflow_dao = new isys_workflow_dao_action($g_comp_database);
        $l_status       = $l_workflow_dao->get_status();
        while ($l_row = $l_status->get_row())
        {
            $l_id    = $l_row["isys_workflow_status__id"];
            $l_title = $g_comp_template_language_manager->get($l_row["isys_workflow_status__title"]);

            $this->set($l_id, $l_title);
        }

        return true;
    }

    public function get($p_id)
    {
        if (array_key_exists($p_id, $this->m_description))
        {
            return $this->m_description[$p_id];
        }
        else return false;
    }

    public function set($p_id, $p_description)
    {
        if (!array_key_exists($p_id, $this->m_description))
        {
            $this->m_description[$p_id] = $p_description;
        }
        else return false;
    }

}