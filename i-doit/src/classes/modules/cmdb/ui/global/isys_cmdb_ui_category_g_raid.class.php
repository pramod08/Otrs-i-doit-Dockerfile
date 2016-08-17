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
 * CMDB Drive: Global category
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @version     Dennis Bluemer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_cmdb_ui_category_g_raid extends isys_cmdb_ui_category_global
{
    /**
     * Process method.
     *
     * @global   array                         $index_includes
     *
     * @param    isys_cmdb_dao_category_g_raid $p_cat
     *
     * @author   Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_gets                = isys_module_request::get_instance()
            ->get_gets();
        $l_object_id           = $l_gets[C__CMDB__GET__OBJECT];
        $l_catdata             = $p_cat->get_general_data();
        $l_num_disks           = $l_max_capacity = $l_min_capacity = 0;
        $l_driveAll            = $l_driveSelected = $l_arDevices = $l_driveAlloc = $l_arHDAlloc = $l_arHDAll = $l_arHDSelected = [];
        $l_calculate_raid_size = true;

        if ($l_catdata["isys_catg_raid_list__id"] > 0)
        {
            $l_raid_id = $l_catdata["isys_catg_raid_list__id"];
        }
        else
        {
            $l_raid_id = -1;
        } // if

        $l_dao_stor  = new isys_cmdb_dao_category_g_stor($this->get_database_component());
        $l_dao_drive = new isys_cmdb_dao_category_g_drive($this->get_database_component());

        $l_res = $l_dao_stor->get_controller_by_object_id($l_object_id);

        if (count($l_res))
        {
            $l_controllers = [];

            while ($l_row = $l_res->get_row())
            {
                $l_controllers[$l_row["isys_catg_controller_list__id"]] = $l_row["isys_catg_controller_list__title"];
            } // while

            $l_rules["C__CATG__RAID_CONTROLLER"]["p_arData"] = serialize($l_controllers);
        } // if

        // Show hard disks connected to a specific RAID pool.
        if ($l_catdata["isys_catg_raid_list__isys_raid_type__id"] == 1 || empty($l_catdata))
        {
            $l_res = $l_dao_stor->get_devices(null, $l_object_id, null);

            if (count($l_res))
            {
                while ($l_row = $l_res->get_row())
                {
                    // Added ID to prevent duplicate titles for the dialog list.
                    $l_arHDAll[$l_row["isys_catg_stor_list__id"]] = $l_row["isys_catg_stor_list__title"] . ' (ID:' . $l_row["isys_catg_stor_list__id"] . ')';
                } // while
            } // if

            $l_res = $l_dao_stor->get_devices(null, $l_object_id, $l_raid_id);

            $l_num_disks = count($l_res);

            if ($l_num_disks > 0)
            {
                while ($l_row = $l_res->get_row())
                {
                    if ($l_row["isys_catg_stor_list__hotspare"] == "1")
                    {
                        $l_num_disks--;
                        $l_arHDSelected[$l_row["isys_catg_stor_list__id"]] = $l_row["isys_catg_stor_list__title"] . " (Hotspare," . ' ID:' . $l_row["isys_catg_stor_list__id"] . ')';
                    }
                    else
                    {
                        $l_arHDSelected[$l_row["isys_catg_stor_list__id"]] = $l_row["isys_catg_stor_list__title"] . ' (ID:' . $l_row["isys_catg_stor_list__id"] . ')';
                    } // if

                    if ($l_min_capacity === 0 || isys_convert::memory(
                            $l_row["isys_catg_stor_list__capacity"],
                            "C__MEMORY_UNIT__GB",
                            C__CONVERT_DIRECTION__BACKWARD
                        ) <= $l_min_capacity
                    )
                    {
                        $l_min_capacity = isys_convert::memory($l_row["isys_catg_stor_list__capacity"], "C__MEMORY_UNIT__GB", C__CONVERT_DIRECTION__BACKWARD);
                    } // if

                    $l_max_capacity += isys_convert::memory($l_row["isys_catg_stor_list__capacity"], "C__MEMORY_UNIT__GB", C__CONVERT_DIRECTION__BACKWARD);
                } // while
            } // if

            // Change the arrays for the dialogue list.
            if (count($l_arHDAll))
            {
                foreach ($l_arHDAll as $key => $val)
                {
                    $l_arHDAlloc[] = [
                        "id"  => $key,
                        "val" => $val,
                        "sel" => 0,
                        "url" => ""
                    ];
                } // foreach
            } // if

            if (count($l_arHDSelected))
            {
                foreach ($l_arHDSelected as $key => $val)
                {
                    $l_arHDAlloc[] = [
                        "id"  => $key,
                        "val" => $val,
                        "sel" => 1,
                        "url" => ''
                    ];
                } // foreach
            } // if
        } // if

        if ($l_catdata["isys_catg_raid_list__isys_raid_type__id"] == 2 || empty($l_catdata))
        {
            $l_res = $l_dao_drive->get_drives(null, $l_object_id, null);

            if (count($l_res) > 0)
            {
                while ($l_row = $l_res->get_row())
                {
                    $l_driveAll[$l_row["isys_catg_drive_list__id"]] = $l_row["isys_catg_drive_list__title"];
                } // while
            } // if

            $l_res = $l_dao_drive->get_drives($l_raid_id, $l_object_id);

            $l_num_disks = count($l_res);

            if ($l_num_disks > 0)
            {
                while ($l_row = $l_res->get_row())
                {
                    $l_driveSelected[$l_row["isys_catg_drive_list__id"]] = $l_row["isys_catg_drive_list__title"];

                    if ($l_min_capacity === 0 || isys_convert::memory(
                            $l_row["isys_catg_drive_list__capacity"],
                            "C__MEMORY_UNIT__GB",
                            C__CONVERT_DIRECTION__BACKWARD
                        ) <= $l_min_capacity
                    )
                    {
                        $l_min_capacity = isys_convert::memory($l_row["isys_catg_drive_list__capacity"], "C__MEMORY_UNIT__GB", C__CONVERT_DIRECTION__BACKWARD);
                    } // if

                    $l_max_capacity += isys_convert::memory($l_row["isys_catg_drive_list__capacity"], "C__MEMORY_UNIT__GB", C__CONVERT_DIRECTION__BACKWARD);
                } // while
            } // if

            // Change the arrays for the dialogue list.
            if (count($l_driveAll))
            {
                foreach ($l_driveAll as $key => $val)
                {
                    $l_driveAlloc[] = [
                        "id"  => $key,
                        "val" => $val,
                        "sel" => 0,
                        "url" => ''
                    ];
                } // foreach
            } // if

            if (count($l_driveSelected))
            {
                foreach ($l_driveSelected as $key => $val)
                {
                    $l_driveAlloc[] = [
                        "id"  => $key,
                        "val" => $val,
                        "sel" => 1,
                        "url" => ""
                    ];
                } // foreach
            } // if

            $l_res = $l_dao_stor->get_devices(null, $l_object_id, null, 1);

            if (count($l_res) > 0)
            {
                while ($l_row = $l_res->get_row())
                {
                    $l_arDevices[$l_row["isys_catg_stor_list__id"]] = $l_row["isys_catg_stor_list__title"];
                } // while
            } // if
        } // if

        $l_rules["C__CMDB__RAID_TYPE"]["p_strTable"]                                                                  = "isys_raid_type";
        $l_rules["C__CMDB__RAID_TYPE"]["p_strSelectedID"]                                                             = $l_catdata["isys_catg_raid_list__isys_raid_type__id"];
        $l_rules["C__CATG__RAID_CONNECTION"]["p_arData"]                                                              = serialize($l_arHDAlloc);
        $l_rules["C__CATG__RAID_LEVEL"]["p_strTable"]                                                                 = "isys_stor_raid_level";
        $l_rules["C__CATG__RAID_LEVEL"]["p_strSelectedID"]                                                            = $l_catdata["isys_catg_raid_list__isys_stor_raid_level__id"];
        $l_rules["C__CATG__RAID_TITLE"]["p_strValue"]                                                                 = $l_catdata["isys_catg_raid_list__title"];
        $l_rules["C__CATG__RAID_CONTROLLER"]["p_strSelectedID"]                                                       = $l_catdata["isys_catg_raid_list__isys_catg_controller_list__id"];
        $l_rules["C__CATG__RAID_DRIVE_CONNECTION"]["p_arData"]                                                        = serialize($l_driveAlloc);
        $l_rules["C__CATG__RAID_DEVICES"]["p_arData"]                                                                 = serialize($l_arDevices);
        $l_rules["C__CATG__RAID_DEVICES"]["p_strSelectedID"]                                                          = $l_catdata["isys_catg_raid_list__isys_catg_stor_list__id"];
        $l_rules["C__CATG__RAID_TOTALCAPACITY_REAL"]["p_strValue"]                                                    = $l_max_capacity . " " . _L(
                "LC__CMDB__MEMORY_UNIT__GB"
            );
        $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $l_catdata["isys_catg_raid_list__description"];

        if (isys_glob_is_edit_mode())
        {
            $l_rules["C__CATG__RAID_CONNECTION"]["p_bDisabled"]       = 0;
            $l_rules["C__CATG__RAID_DRIVE_CONNECTION"]["p_bDisabled"] = 0;
        }
        else
        {
            $l_rules["C__CATG__RAID_CONNECTION"]["p_bDisabled"]       = 1;
            $l_rules["C__CATG__RAID_DRIVE_CONNECTION"]["p_bDisabled"] = 1;
        } // if

        if ($l_catdata["isys_stor_raid_level__const"] == "C__STOR_RAID_LEVEL__JBOD")
        {
            $l_rules["C__CATG__RAID_TOTALCAPACITY"]["p_strValue"] = $l_max_capacity . " " . _L("LC__CMDB__MEMORY_UNIT__GB");
            $l_calculate_raid_size                                = false;
        } // if

        $this->get_template_component()
            ->assign("raid_id", $l_raid_id)
            ->assign(
                "raid",
                [
                    "numdisks" => $l_num_disks,
                    "each"     => $l_min_capacity,
                    "level"    => $l_catdata["isys_stor_raid_level__title"]
                ]
            )
            ->assign("raid_type", $l_catdata["isys_catg_raid_list__isys_raid_type__id"])
            ->assign("calculate_raid", $l_calculate_raid_size)
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    } // function
} // class