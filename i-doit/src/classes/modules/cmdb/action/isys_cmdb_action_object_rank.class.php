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
 * Action: Object delete
 *
 * @package     i-doit
 * @subpackage  CMDB
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_action_object_rank implements isys_cmdb_action
{
    /**
     * Handle method for ranking action.
     *
     * @param   isys_cmdb_dao $p_dao
     * @param   array         $p_data
     *
     * @throws  isys_exception_cmdb
     */
    public function handle(isys_cmdb_dao $p_dao, &$p_data)
    {
        $p_direction = $p_data[0] ?: C__CMDB__RANK__DIRECTION_DELETE;
        $p_objects   = $p_data[1] ?: [];

        if (is_array($p_objects))
        {
            /**
             * Removing duplicates from array
             *
             * @fixes ID-1541
             */
            $p_objects = array_unique($p_objects);

            if (count($p_objects) > 0)
            {
                // Check, if the user is allowed to rank the object.
                foreach ($p_objects as $l_object)
                {
                    $l_current_status = $p_dao->get_object_by_id($l_object)
                        ->get_row_value('isys_obj__status');
                    $l_archive_action = ($l_current_status == C__RECORD_STATUS__NORMAL && $p_direction == C__CMDB__RANK__DIRECTION_DELETE) || ($l_current_status == C__RECORD_STATUS__ARCHIVED && $p_direction == C__CMDB__RANK__DIRECTION_RECYCLE);

                    if ($l_archive_action)
                    {
                        try
                        {
                            isys_auth_cmdb::instance()
                                ->check(isys_auth::ARCHIVE, 'OBJ_ID/' . $l_object);
                        }
                        catch (Exception $e)
                        {
                            isys_auth_cmdb::instance()
                                ->check(isys_auth::DELETE, 'OBJ_ID/' . $l_object);
                        } // try
                    }
                    else
                    {
                        isys_auth_cmdb::instance()
                            ->check(isys_auth::DELETE, 'OBJ_ID/' . $l_object);
                    } // if
                } // foreach

                isys_component_signalcollection::get_instance()
                    ->emit("mod.cmdb.beforeObjectRank", $p_dao, $p_direction, $p_objects);

                if (!$p_dao->rank_records($p_objects, $p_direction, "isys_obj"))
                {
                    throw new isys_exception_cmdb(
                        "Could not delete objects (#" . implode(', #', $p_objects) . ") (isys_cmdb_dao->rank_records())", C__CMDB__ERROR__ACTION_PROCESSOR
                    );
                } // if

                // Objects have been changed update their update date
                $p_dao->object_changed($p_objects);
                isys_component_signalcollection::get_instance()
                    ->emit("mod.cmdb.afterObjectRank", $p_dao, $p_direction, $p_objects);
            } // if
        } // if
    } // function
} // class