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
 * CMDB DAO Extension for jdisc
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_jdisc extends isys_cmdb_dao
{
    /**
     * Remember all really created object ids of current session
     *
     * @var array
     */
    private static $m_created_objects = [];

    /**
     * @param $p_object_id
     *
     * @return mixed
     */
    public static function object_created_in_current_session($p_object_id)
    {
        return isset(self::$m_created_objects[$p_object_id]);
    }

    /**
     * Custom object creation function used by the jdisc import
     *
     * @param int  $p_obj_type_id
     * @param bool $p_set_obj_virtual
     * @param null $p_strTitle
     * @param null $p_strSYSID
     * @param int  $p_record_status
     * @param null $p_hostname
     * @param null $p_scantime
     * @param bool $p_import_date
     * @param null $p_created
     * @param null $p_created_by
     * @param null $p_updated
     * @param null $p_updated_by
     * @param null $p_category
     * @param null $p_purpose
     * @param null $p_cmdb_status
     * @param null $p_description
     *
     * @return int|null
     */
    public function insert_new_obj($p_obj_type_id, $p_set_obj_virtual, $p_strTitle = null, $p_strSYSID = null, $p_record_status = C__RECORD_STATUS__BIRTH, $p_hostname = null, $p_scantime = null, $p_import_date = false, $p_created = null, $p_created_by = null, $p_updated = null, $p_updated_by = null, $p_category = null, $p_purpose = null, $p_cmdb_status = null, $p_description = null)
    {
        $l_id = false;

        if (isys_settings::get('jdisc.prevent-duplicates', true) && isys_tenantsettings::get('cmdb.unique.object-title', false))
        {
            if (!empty($p_strTitle))
            {
                $l_id = $this->get_obj_id_by_title($p_strTitle);
            } // if
        } // if

        if (!$l_id)
        {
            $l_id                           = parent::insert_new_obj(
                $p_obj_type_id,
                $p_set_obj_virtual,
                $p_strTitle,
                $p_strSYSID,
                $p_record_status,
                $p_hostname,
                $p_scantime,
                $p_import_date,
                $p_created,
                $p_created_by,
                $p_updated,
                $p_updated_by,
                $p_category,
                $p_purpose,
                $p_cmdb_status,
                $p_description
            );
            self::$m_created_objects[$l_id] = true;
        } // if

        return $l_id;
    }
}