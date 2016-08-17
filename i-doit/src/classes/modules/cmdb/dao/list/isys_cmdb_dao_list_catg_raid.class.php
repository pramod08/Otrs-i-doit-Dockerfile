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
 * DAO: Gloabl category 'drive'
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Bluemer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_dao_list_catg_raid extends isys_cmdb_dao_list
{
    /**
     * Return constant of category.
     *
     * @return  integer
     */
    public function get_category()
    {
        return C__CATG__RAID;
    } // function

    /**
     * Return constant of category type.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    } // function

    /**
     *
     * @param   string  $p_table
     * @param   integer $p_object_id
     * @param   integer $p_cRecStatus
     *
     * @return  isys_component_dao_result
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function get_result($p_table = null, $p_object_id, $p_cRecStatus = null)
    {
        $l_sql = "SELECT * FROM isys_catg_raid_list
			LEFT JOIN isys_stor_raid_level ON isys_catg_raid_list__isys_stor_raid_level__id = isys_stor_raid_level__id
			LEFT JOIN isys_catg_controller_list ON isys_catg_raid_list__isys_catg_controller_list__id = isys_catg_controller_list__id
			LEFT JOIN isys_raid_type ON isys_catg_raid_list__isys_raid_type__id = isys_raid_type__id
			WHERE isys_catg_raid_list__isys_obj__id = " . $this->convert_sql_id($p_object_id);

        $l_cRecStatus = empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus;

        if (!empty($l_cRecStatus))
        {
            $l_sql .= " AND isys_catg_raid_list__status = " . $l_cRecStatus;
        } // if

        return $this->retrieve($l_sql . ';');
    } // function

    /**
     * @param  array &$p_arrRow
     */
    public function modify_row(&$p_arrRow)
    {
        // Compute total capacity of RAID group.
        global $g_comp_database, $g_comp_template_language_manager;
        $l_dao_stor  = new isys_cmdb_dao_category_g_stor($g_comp_database);
        $l_dao_drive = new isys_cmdb_dao_category_g_drive($g_comp_database);

        $p_arrRow["isys_title"]        = $p_arrRow["isys_catg_raid_list__title"];
        $p_arrRow["isys_level__title"] = $p_arrRow["isys_stor_raid_level__title"];
        $p_arrRow["isys_type__title"]  = $p_arrRow["isys_raid_type__title"];

        if ($p_arrRow["isys_catg_raid_list__isys_raid_type__id"] == 1)
        {
            $l_res      = $l_dao_stor->get_devices(null, $_GET[C__CMDB__GET__OBJECT], $p_arrRow["isys_catg_raid_list__id"]);
            $l_numDisks = $l_res->num_rows();

            if ($l_res->num_rows() > 0)
            {
                $l_row = $l_res->get_row();
                $l_id  = $l_row["isys_catg_stor_list__id"];
                $l_lo  = isys_convert::memory($l_row["isys_catg_stor_list__capacity"], "C__MEMORY_UNIT__GB", C__CONVERT_DIRECTION__BACKWARD);

                $l_max_capacity = isys_convert::memory($l_row["isys_catg_stor_list__capacity"], "C__MEMORY_UNIT__GB", C__CONVERT_DIRECTION__BACKWARD);

                while ($l_row = $l_res->get_row())
                {
                    if (isys_convert::memory($l_row["isys_catg_stor_list__capacity"], "C__MEMORY_UNIT__GB", C__CONVERT_DIRECTION__BACKWARD) < $l_lo)
                    {
                        $l_lo = isys_convert::memory($l_row["isys_catg_stor_list__capacity"], "C__MEMORY_UNIT__GB", C__CONVERT_DIRECTION__BACKWARD);
                    } // if

                    if ($l_row["isys_catg_stor_list__hotspare"] == "1")
                    {
                        $l_numDisks--;
                    } // if

                    $l_max_capacity += isys_convert::memory($l_row["isys_catg_stor_list__capacity"], "C__MEMORY_UNIT__GB", C__CONVERT_DIRECTION__BACKWARD);
                } // while

                if ($p_arrRow["isys_stor_raid_level__const"] == "C__STOR_RAID_LEVEL__JBOD")
                {
                    $p_arrRow["isys_capacity"] = $l_max_capacity . " " . $g_comp_template_language_manager->get("LC__CMDB__MEMORY_UNIT__GB");
                }
                else
                {
                    $p_arrRow["isys_capacity"] = isys_cmdb_dao_category_g_stor::instance($this->m_db)
                            ->raidcalc($l_numDisks, $l_lo, $p_arrRow["isys_stor_raid_level__title"]) . ' GB';
                } // if
            } // if
        }
        elseif ($p_arrRow["isys_catg_raid_list__isys_raid_type__id"] == 2)
        {
            $l_res      = $l_dao_drive->get_drives($p_arrRow["isys_catg_raid_list__id"]);
            $l_numDisks = $l_res->num_rows();

            if ($l_res->num_rows() > 0)
            {
                $l_row = $l_res->get_row();

                $l_id = $l_row["isys_catg_drive_list__id"];

                $l_lo = isys_convert::memory($l_row["isys_catg_drive_list__capacity"], "C__MEMORY_UNIT__GB", C__CONVERT_DIRECTION__BACKWARD);

                $l_max_capacity = isys_convert::memory($l_row["isys_catg_drive_list__capacity"], "C__MEMORY_UNIT__GB", C__CONVERT_DIRECTION__BACKWARD);

                while ($l_row = $l_res->get_row())
                {
                    if (isys_convert::memory($l_row["isys_catg_drive_list__capacity"], "C__MEMORY_UNIT__GB", C__CONVERT_DIRECTION__BACKWARD) < $l_lo)
                    {
                        $l_lo = isys_convert::memory($l_row["isys_catg_drive_list__capacity"], "C__MEMORY_UNIT__GB", C__CONVERT_DIRECTION__BACKWARD);
                    } // if

                    if ($l_row["isys_catg_stor_list__hotspare"] == "1")
                    {
                        $l_numDisks--;
                    } // if

                    $l_max_capacity += isys_convert::memory($l_row["isys_catg_drive_list__capacity"], "C__MEMORY_UNIT__GB", C__CONVERT_DIRECTION__BACKWARD);
                } // while

                if ($p_arrRow["isys_stor_raid_level__const"] == "C__STOR_RAID_LEVEL__JBOD")
                {
                    $p_arrRow["isys_capacity"] = $l_max_capacity . " " . $g_comp_template_language_manager->get("LC__CMDB__MEMORY_UNIT__GB");
                }
                else
                {
                    $p_arrRow["isys_capacity"] = isys_cmdb_dao_category_g_stor::instance($this->m_db)
                            ->raidcalc($l_numDisks, $l_lo, $p_arrRow["isys_stor_raid_level__title"]) . ' GB';
                } // if
            } // if
        } // if
    } // function

    /**
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_fields()
    {
        return [
            'isys_title'        => 'LC__CATG__RAID_TITLE',
            'isys_level__title' => 'LC__CATD__DRIVE_RAIDLEVEL',
            'isys_type__title'  => 'LC__CMDB__RAID_TYPE',
            'isys_capacity'     => 'LC__CATG__STORAGE_CAPACITY',
        ];
    } // function
} // class