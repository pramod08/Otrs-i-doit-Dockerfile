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
 * CMDB Action Processor
 *
 * @package     i-doit
 * @subpackage  CMDB
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_action_category
{
    /**
     * Instance of isys_component_dao_lock.
     *
     * @var  isys_component_dao_lock
     */
    protected $m_dao_lock;

    /**
     * Callback function called by rank_records.
     *
     * @param   $p_object_id
     * @param   $p_category_const
     *
     * @return  boolean
     * @author  Selcuk Kekec <skekec@i-doit.com>
     */
    public function check_right($p_object_id, $p_category_const)
    {
        return isys_auth_cmdb::instance()
            ->has_rights_in_obj_and_category(isys_auth::EDIT, $p_object_id, $p_category_const);
    } // function

    /**
     * Checks if the object is locked. Returns true, if object is locked for the current user - false, if not.
     *
     * @return  boolean
     */
    protected function object_is_locked()
    {
        if ($this->m_dao_lock->check_lock($_GET[C__CMDB__GET__OBJECT]))
        {
            isys_component_template_infobox::instance()
                ->set_message("<b>ERROR! - " . _L("LC__OBJECT_LOCKED") . " (Lock-Timeout: " . C__LOCK__TIMEOUT . "s)</b>", null, null, null, C__LOGBOOK__ALERT_LEVEL__3);

            return true;
        } // if

        return false;
    } // function

    /**
     * Constructor.
     */
    public function __construct()
    {
        global $g_comp_database;

        $this->m_dao_lock = new isys_component_dao_lock($g_comp_database);
    } // function
} // class