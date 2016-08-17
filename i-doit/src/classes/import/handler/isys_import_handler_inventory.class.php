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
 * @package     i-doit
 * @subpackage
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.i-doit.com/license
 */
class isys_import_handler_inventory extends isys_import_handler
{

    /**
     * Parses an hinventory export.
     * Sets:
     *  $this->m_data
     *  $this->m_hostname
     *  $this->m_scantime
     *
     * @param string $p_xml_data
     *
     * @return array|false
     */
    public function parse($p_xml_data = null)
    {
        if (empty($p_xml_data))
        {
            $l_xml_data = $this->get_xml_data();
        }
        else
        {
            $l_xml_data = $p_xml_data;
        }

        $l_cat_id = null;

        $l_computer = $l_xml_data["computer"];
        unset($l_xml_data);

        $this->m_hostname = $l_computer["hostname"];
        $this->m_scantime = $l_computer["datetime"];

        if (strstr($this->m_scantime, "/"))
        {
            $l_scantmp_1 = explode(" ", $this->m_scantime);
            $l_date      = $l_scantmp_1[0];
            $l_time      = $l_scantmp_1[1];

            $l_scantmp_2 = explode("/", $l_date);

            $this->m_scantime = $l_scantmp_2[0] . "." . $l_scantmp_2[1] . "." . $l_scantmp_2[2] . " " . $l_time;
        }

        if (count($l_computer) > 0)
        {

            foreach ($l_computer as $l_key => $l_value)
            {

                if (is_array($l_value))
                {

                    foreach ($l_value as $l_child)
                    {

                        if ($l_cat_id != $l_child["type"])
                        {
                            $l_cat_id = $l_child["type"];
                        }

                        $l_fine = false;
                        $l_name = $l_child["name"];

                        unset($l_child["type"]);
                        unset($l_child["name"]);

                        if (is_array($l_child))
                        {

                            $l_attribute = [];
                            $l_cat_id    = strtolower($l_cat_id);

                            foreach ($l_child as $l_attributes)
                            {
                                $l_attr = $l_attributes["attr"];

                                /* Format to lowercase for easier array handling */
                                $l_at_formatted = strtolower($l_attr["name"]);

                                if ($l_at_formatted == 'name')
                                {
                                    $l_at_formatted = 'contact';
                                } // if

                                /* Add to data component */
                                if (array_key_exists($l_at_formatted, $l_attribute) && $l_cat_id == 'network adapter')
                                {
                                    // Special case for network adapter.
                                    // Because it is possible to have more than one IP
                                    $l_puffer = $l_attribute[$l_at_formatted];
                                    unset($l_attribute[$l_at_formatted]);
                                    $l_attribute[$l_at_formatted][] = $l_attr["value"];
                                    if (is_array($l_puffer))
                                    {
                                        $l_attribute[$l_at_formatted] = array_merge($l_attribute[$l_at_formatted], $l_puffer);
                                    }
                                    else
                                    {
                                        $l_attribute[$l_at_formatted][] = $l_puffer;
                                    } // if
                                }
                                else
                                {
                                    $l_attribute[$l_at_formatted] = $l_attr["value"];
                                } // if

                                unset($l_attr);
                                unset($l_at_formatted);
                            } // foreach

                            $l_attribute["name"] = $l_name;

                            /* Format h-inventory's clear-text assignments */
                            switch ($l_cat_id)
                            {
                                case "printer":
                                    $l_cat_id = C__CATG__UNIVERSAL_INTERFACE;
                                    $l_fine   = C__IMPORT__UI__PRINTER;
                                    break;
                                case "pointing device":
                                    $l_cat_id = C__CATG__UNIVERSAL_INTERFACE;
                                    $l_fine   = C__IMPORT__UI__MOUSE;
                                    break;
                                case "keyboard":
                                    $l_cat_id = C__CATG__UNIVERSAL_INTERFACE;
                                    $l_fine   = C__IMPORT__UI__KEYBOARD;

                                    $this->m_data[C__CMDB__CATEGORY__TYPE_SPECIFIC][C__CATS__CLIENT]["keyboard"] = $l_attribute;

                                    break;
                                case "desktop monitor":
                                    $l_cat_id = C__CATG__UNIVERSAL_INTERFACE;
                                    $l_fine   = C__IMPORT__UI__MONITOR;
                                    break;
                                case "cpu":
                                    //case "PhysicalCPU":
                                    $l_cat_id = C__CATG__CPU;
                                    break;
                                case "battery":
                                    $this->m_data[C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__MODEL]["battery"] = $l_attribute;

                                    break;
                                case "model":
                                    $l_cat_id = C__CATG__MODEL;

                                    $this->m_data[C__CMDB__CATEGORY__TYPE_SPECIFIC][C__CATS__CLIENT]["type"] = $l_attribute["systemtype"];

                                    $l_model = strtolower($l_attribute["model"]);
                                    if (strstr($l_model, "vmware") || strstr($l_model, "virtual") || strstr($l_model, "parallels") || strstr($l_model, "innotek") || strstr(
                                            $l_model,
                                            "qemu"
                                        )
                                    )
                                    {
                                        $this->m_data[C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__VIRTUAL_MACHINE] = [
                                            "type" => $l_attribute["model"]
                                        ];
                                    }

                                    if (!empty($l_bios))
                                    {
                                        $l_attribute["bios"] = $l_bios;
                                    }
                                case "bios":

                                    if (is_array($this->m_data[C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__MODEL]))
                                    {
                                        $this->m_data[C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__MODEL]["bios"] = $l_attribute;
                                    }
                                    else
                                    {
                                        $l_bios = $l_attribute;
                                    }

                                    break;
                                case "physical memory":
                                    $l_cat_id = C__CATG__MEMORY;
                                    break;
                                case "graphic card":
                                    $l_cat_id = C__CATG__GRAPHIC;
                                    break;
                                case "cd/dvd drive":
                                    $l_fine   = "cd";
                                    $l_cat_id = C__CMDB__SUBCAT__STORAGE__DEVICE;
                                    break;
                                case "floppy":
                                    $l_fine   = "floppy";
                                    $l_cat_id = C__CMDB__SUBCAT__STORAGE__DEVICE;
                                    break;
                                case "hard disk":
                                    $l_fine   = "hd";
                                    $l_cat_id = C__CMDB__SUBCAT__STORAGE__DEVICE;
                                    break;
                                case "ide controller":
                                    $l_fine   = "IDE";
                                    $l_cat_id = C__CATG__CONTROLLER;
                                    break;
                                case "scsi controller":
                                    $l_fine   = "SCSI";
                                    $l_cat_id = C__CATG__CONTROLLER;
                                    break;
                                case "network adapter":
                                    $l_cat_id = C__CATG__NETWORK;
                                    break;
                                case "audio card":
                                    $l_cat_id = C__CATG__SOUND;
                                    break;
                                case "application":
                                    $l_attribute["type"] = C__OBJTYPE__APPLICATION;
                                    $l_cat_id            = C__CATG__APPLICATION;
                                    break;
                                case "operating system":
                                    $l_attribute["type"] = C__OBJTYPE__OPERATING_SYSTEM;
                                    $l_cat_id            = C__CATG__OPERATING_SYSTEM;
                                    break;
                                case "audit":
                                    if (strtolower($l_attribute["name"]) == 'loginuser')
                                    {
                                        //$l_attribute["type"] = C__OBJTYPE__PERSON;
                                        //$l_cat_id            = C__CATG__CONTACT;

                                        $this->m_data[C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__LAST_LOGIN_USER]["user"] = $l_attribute['contact'];
                                    }
                                    elseif (strtolower($l_attribute["name"]) == 'Uptime')
                                    {
                                        $this->m_data[C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__MODEL]["uptime"] = $l_attribute;
                                    }
                                    elseif (strtolower($l_attribute["name"]) == 'ProductKey')
                                    {
                                        $this->m_data[C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__OPERATING_SYSTEM]["licence"] = $l_attribute['serialnumber'];
                                    }
                                    elseif (strtolower($l_attribute["name"]) == 'ProductID')
                                    {
                                        $this->m_data[C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__OPERATING_SYSTEM]["productid"] = $l_attribute['serialnumber'];
                                    }
                                    elseif (isset($l_attribute["filesystem"]))
                                    {
                                        $this->m_data[C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__DRIVE][] = $l_attribute;
                                    }
                                    else
                                    {
                                        $l_fine = false;
                                    }
                                    break;
                                default:
                                    $l_fine = false;
                                    break;
                            }

                            if (!is_array($l_attribute)) $l_attribute = [];

                            if (is_numeric($l_cat_id))
                            {
                                if ($l_fine)
                                {
                                    $this->m_data[C__CMDB__CATEGORY__TYPE_GLOBAL][$l_cat_id][$l_fine][] = $l_attribute;
                                }
                                else
                                {
                                    $this->m_data[C__CMDB__CATEGORY__TYPE_GLOBAL][$l_cat_id][] = $l_attribute;
                                } // if
                            } // if

                            unset($l_attribute);
                        }
                    }

                }

            }
        }
        else
        {
            return false;
        }

        /* Cmdb specific workaround */
        $_POST[C__CMDB__GET__CATLEVEL] = -1;

        return $this->m_data;
    }

    /**
     * Import
     *
     * @param   integer $p_objtype_id
     * @param   boolean $p_force_overwrite
     * @param   integer $p_object_id
     *
     * @return  boolean
     * @throws  Exception
     * @throws  isys_exception_cmdb
     */
    public function import($p_objtype_id, $p_force_overwrite = null, $p_object_id = null)
    {
        /**
         * Disconnect the onAfterCategoryEntrySave event to not always reindex the object in every category
         * This is extremely important!
         *
         * An Index is done for all objects at the end of the request, if enabled via checkbox.
         */
        \idoit\Module\Cmdb\Search\IndexExtension\Signals::instance()
            ->disconnectOnAfterCategoryEntrySave();

        $l_dao = new isys_cmdb_dao(isys_application::instance()->database);
        /**
         * @var $l_dao_identifier isys_cmdb_dao_category_g_identifier
         */
        $l_dao_identifier = isys_cmdb_dao_category_g_identifier::instance(isys_application::instance()->database);

        $l_em     = isys_event_manager::getInstance();
        $l_serial = null;
        $l_objid  = false;

        if (is_numeric($p_object_id) && $p_object_id > 0)
        {
            $l_objid = $p_object_id;
        }
        else
        {

            if (isset($this->m_data[C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__MODEL]["bios"]["serialnumber"]))
            {
                $l_serial = $this->m_data[C__CMDB__CATEGORY__TYPE_GLOBAL][C__CATG__MODEL]["bios"]["serialnumber"];
            } // if

            if ($l_serial)
            {
                $l_objid = $l_dao_identifier->get_object_id_by_key_value(C__CATG__IDENTIFIER_TYPE__H_INVENTORY, 'serial', $l_serial);
            } // if

            if (!$l_objid)
            {
                // Retrieve all MAC-Addresses
                $l_mac_addresses = $this->extract_unique_mac_address();

                // Check object only with Serial
                if ($l_serial)
                {
                    $l_objid = $l_dao->get_object_by_hostname_serial_mac(null, $l_serial);
                } // if

                if (!$l_objid && count($l_mac_addresses) && $this->m_hostname !== '')
                {
                    // Check object with MAC-Addresses and Object title
                    $l_objid = $l_dao->get_object_by_hostname_serial_mac(null, null, $l_mac_addresses, $this->m_hostname);
                } // if
            } // if

            // Fallback if no serial or mac has been found or if the object has been imported with no data
            if (!$l_objid && $this->m_hostname != '')
            {
                $l_res = $l_dao->get_object_by_hostname($this->m_hostname);
                if ($l_res->num_rows())
                {
                    $l_objid = $l_res->get_row_value('isys_obj__id');
                } // if
            } // if
        } // if

        /**
         * @desc starts the import, if scantime of current import is higher then the existing one,
         *         if nothing exists, a new object will be created, of course. ;)
         */
        if (!$this->check_scantime($l_objid, $this->m_scantime) || $p_force_overwrite)
        {

            if (!is_null($this->m_hostname))
            {
                verbose("Importing host: " . $this->m_hostname);
            }
            verbose("Scantime: " . $this->m_scantime);

            if ($l_objid < 0 || empty($l_objid))
            {
                $l_cmdb_status = isys_tenantsettings::get('import.hinventory.default_status', C__CMDB_STATUS__IN_OPERATION);

                verbose("Creating object..", true, "+");
                $l_objid = $l_dao->insert_new_obj(
                    $p_objtype_id,
                    false,
                    $this->m_hostname,
                    ISYS_NULL,
                    C__RECORD_STATUS__BIRTH,
                    $this->m_hostname,
                    $this->m_scantime,
                    true,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    $l_cmdb_status
                );

                if (method_exists($l_em, "triggerCMDBEvent"))
                {
                    $l_em->triggerCMDBEvent(
                        'C__LOGBOOK_EVENT__OBJECT_CREATED',
                        "Inventory import",
                        $l_objid,
                        $p_objtype_id
                    );
                }

                verbose("Created object-id: " . $l_objid);
            }
            else
            {
                if ($p_force_overwrite)
                {
                    verbose("Found obect-id: " . $l_objid);

                    $this->edit_scantime($l_objid, $this->m_scantime, $this->m_hostname);

                    if (method_exists($l_em, "triggerCMDBEvent"))
                    {
                        $l_em->triggerCMDBEvent(
                            'C__LOGBOOK_EVENT__OBJECT_CHANGED',
                            "Inventory update",
                            $l_objid,
                            $p_objtype_id,
                            null,
                            null,
                            "Inventory"
                        );
                    }

                }
                else
                {

                    verbose(
                        "Import already existing and force-mode disabled! (Object-Id: {$l_objid}, Hostname: " . $this->m_hostname . ", Scantime: " . $this->m_scantime . ")"
                    );

                    verbose("Try --force at the end of your parameter list.");

                    return false;
                }
            }

            if ($l_objid != -1)
            {
                $l_dao->set_object_status(
                    $l_objid,
                    C__RECORD_STATUS__NORMAL
                );

                $l_dao->object_changed($l_objid);

                verbose("Category mode");
                $this->categorize($l_objid, "import", C__CMDB__CATEGORY__TYPE_GLOBAL);

                if ($this->m_data[C__CMDB__CATEGORY__TYPE_SPECIFIC])
                {
                    $this->categorize($l_objid, "import", C__CMDB__CATEGORY__TYPE_SPECIFIC);
                }

                verbose("\n\n", true, "");

                if ($l_objid && $l_serial !== null)
                {
                    if ($l_serial != 'To Be Filled By O.E.M.')
                    {
                        // Add serial to category custom_identifier
                        $l_dao_identifier->set_identifier($l_objid, C__CATG__IDENTIFIER_TYPE__H_INVENTORY, 'serial', $l_serial, '', '', $this->m_scantime);
                    }
                } // if

                return true;
            }
            else
            {
                if (method_exists($l_em, "triggerCMDBEvent"))
                {
                    $l_em->triggerCMDBEvent(
                        'C__LOGBOOK_EVENT__OBJECT_CREATED__NOT',
                        "",
                        ISYS_NULL,
                        ISYS_NULL
                    );
                }

                throw new Exception("Could not create object");
            }
        }
        else
        {
            verbose(
                "Import already existing! (Object-Id: {$l_objid}, Hostname: " . $this->m_hostname . ", Scantime: " . $this->m_scantime . ")"
            );

        }

        return false;
    } // function

    /**
     * Retrieve all mac addresses
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function extract_unique_mac_address()
    {
        $l_return = [];
        if (isset($this->m_data[C__CATG__NETWORK]) && count($this->m_data[C__CATG__NETWORK]))
        {
            foreach ($this->m_data[C__CATG__NETWORK] AS $l_port)
            {
                if (!array_search($l_port['mac'], $l_return)) $l_return[] = $l_port['mac'];
            } // foreach
        } // if
        return $l_return;
    }

}